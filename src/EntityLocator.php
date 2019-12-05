<?php declare(strict_types=1);

namespace Somnambulist\ApiClient;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LogLevel;
use Somnambulist\ApiClient\Client\ApiRequestHelper;
use Somnambulist\ApiClient\Behaviours\EntityLocator\CanAppendIncludes;
use Somnambulist\ApiClient\Behaviours\EntityLocator\HydrateAsCollection;
use Somnambulist\ApiClient\Behaviours\EntityLocator\HydrateSingleObject;
use Somnambulist\ApiClient\Contracts\ApiClientInterface;
use Somnambulist\ApiClient\Contracts\EntityLocatorInterface;
use Somnambulist\ApiClient\Mapper\ObjectMapper;
use Somnambulist\Collection\Contracts\Collection;
use Somnambulist\Collection\MutableCollection;
use Symfony\Component\HttpClient\Exception\ClientException;
use function array_merge;
use function sprintf;

/**
 * Class EntityLocator
 *
 * The EntityLocator is a Doctrine EntityRepository like base class that provides some
 * standard find methods for common operations. This includes: find, findBy, findOneBy
 * and findAll.
 *
 * Results are hydrated via the ObjectMapper that can return collections or single object
 * instances.
 *
 * To load additional data during a request; use `with()` to specify which includes should
 * be requested from the API end point. Note that this requires support from the Api
 * end point.
 *
 * @package Somnambulist\ApiClient\Client
 * @subpackage Somnambulist\ApiClient\Client\EntityLocator
 */
class EntityLocator implements LoggerAwareInterface, EntityLocatorInterface
{

    use CanAppendIncludes;
    use HydrateAsCollection;
    use HydrateSingleObject;
    use LoggerAwareTrait;

    /**
     * @var ApiClientInterface
     */
    protected $client;

    /**
     * @var ObjectMapper
     */
    protected $mapper;

    /**
     * @var ApiRequestHelper
     */
    protected $apiHelper;

    /**
     * @var string
     */
    protected $className;

    /**
     * @var string
     */
    protected $classPrimaryKey;

    /**
     * The collection class to return when hydrating collections
     *
     * @var string
     */
    protected $collectionClass = MutableCollection::class;

    /**
     * Constructor
     *
     * @param ApiClientInterface $client
     * @param ObjectMapper       $mapper
     * @param string             $class
     * @param string             $identity
     */
    public function __construct(ApiClientInterface $client, ObjectMapper $mapper, string $class, string $identity = 'id')
    {
        $this->mapper          = $mapper;
        $this->client          = $client;
        $this->className       = $class;
        $this->classPrimaryKey = $identity;

        $this->apiHelper = new ApiRequestHelper();
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function with(string ...$include): self
    {
        $this->includes = $include;

        return $this;
    }

    public function find($id): ?object
    {
        $options = [$this->classPrimaryKey => (string)$id];

        try {
            $response = $this->client->get($this->prefix('view'), $this->appendIncludes($options));

            return $this->hydrateObject($response);
        } catch (ClientException $e) {
            $this->log(LogLevel::ERROR, $e->getMessage(), [
                'route'                => $this->client->route($this->prefix('view'), $this->appendIncludes($options)),
                $this->classPrimaryKey => (string)$id,
            ]);
        }

        return null;
    }

    public function findBy(array $criteria = [], array $orderBy = [], int $limit = null, int $offset = null): Collection
    {
        $options = array_merge(
            $criteria,
            $this->apiHelper->createOrderByRequestArgument($orderBy),
            $this->apiHelper->createPaginationRequestArgumentsFromLimitAndOffset($limit, $offset)
        );

        try {
            $response = $this->client->get($this->prefix('list'), $this->appendIncludes($options));

            return $this->hydrateCollection($response, $this->collectionClass);
        } catch (ClientException $e) {
            $this->log(LogLevel::ERROR, $e->getMessage(), [
                'route' => $this->client->route($this->prefix('list'), $this->appendIncludes($options)),
            ]);
        }

        return new MutableCollection();
    }

    public function findOneBy(array $criteria, array $orderBy = []): ?object
    {
        return $this->findBy($criteria, $orderBy, 1)->first() ?: null;
    }



    protected function prefix(string $route): string
    {
        return sprintf('%s.%s', $this->client->router()->service()->alias(), $route);
    }

    protected function log(string $level, string $message, array $context = []): void
    {
        if ($this->logger) {
            $this->logger->log($level, $message, $context);
        }
    }
}