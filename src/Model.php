<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient;

use InvalidArgumentException;
use Somnambulist\Components\ApiClient\Client\Connection\Decoders\SimpleJsonDecoder;
use Somnambulist\Components\ApiClient\Client\Contracts\QueryEncoderInterface;
use Somnambulist\Components\ApiClient\Client\Contracts\ResponseDecoderInterface;
use Somnambulist\Components\ApiClient\Client\Query\Encoders\SimpleEncoder;
use Somnambulist\Components\ApiClient\Exceptions\EntityNotFoundException;
use function array_key_exists;

/**
 * Represents an API resource that can be queried independently
 *
 * "Model" in this case encapsulates a singular endpoint that can additionally have included data (sub-objects)
 * that will respond to a request of the type `/some/resource/{some_id}`. By default, the response handling uses
 * a basic JSON decoder. This can be replaced with a decoder that implements e.g. JsonAPI or OpenAPI etc.
 *
 * Models can be queried for via the query methods. This is entirely API dependent and not all features may
 * work. Be sure to review the APIs documentation you are integrating with.
 */
abstract class Model extends AbstractModel
{
    /**
     * The primary key for the model
     *
     * This is the name of the field used to store the primary identifier for this Model
     * as it appears in the server response.
     */
    protected string $primaryKey = 'id';

    /**
     * The QueryEncoder to use when making requests to the API endpoint
     *
     * Use one of the built-in encoders, or add your own that can create an array of
     * query arguments as needed by your API.
     */
    protected string $queryEncoder = SimpleEncoder::class;

    /**
     * The response decoder to use to create the internal array structures
     *
     * The default handles only a basic JSON structure as defined in the docs.
     * For other response formats, implement a decoder to convert to the simpler
     * array syntax expected.
     */
    protected string $responseDecoder = SimpleJsonDecoder::class;

    /**
     * The route names to use for searching / loading this Model.
     *
     * The route name should be configured in the ApiRouter on the connection associated
     * with this Model type. The main routes needed are one to search / access a list of
     * resources, and one to fetch a single resource.
     */
    protected array $routes = [
        'search' => null,
        'view'   => null,
    ];

    /**
     * The relationships to eager load on every request
     */
    protected array $include = [];

    /**
     * @param mixed $id
     *
     * @return Model|null
     */
    public static function find(mixed $id): ?Model
    {
        return static::query()->find($id);
    }

    /**
     * @param mixed $id
     *
     * @return Model
     * @throws EntityNotFoundException
     */
    public static function findOrFail(mixed $id): Model
    {
        return static::query()->findOrFail($id);
    }

    /**
     * Eager load the specified relationships on this model
     *
     * Allows dot notation to load related.related objects.
     *
     * @param string ...$relations
     *
     * @return ModelBuilder
     */
    public static function include(...$relations): ModelBuilder
    {
        return static::query()->include(...$relations);
    }

    /**
     * Starts a new query builder process without any constraints
     *
     * @return ModelBuilder
     */
    public static function query(): ModelBuilder
    {
        return (new static)->newQuery();
    }

    public function newQuery(): ModelBuilder
    {
        $builder = new ModelBuilder($this);
        $builder->include(...$this->include);

        return $builder;
    }

    public function getQueryEncoder(): QueryEncoderInterface
    {
        return new $this->queryEncoder;
    }

    public function getResponseDecoder(): ResponseDecoderInterface
    {
        return new $this->responseDecoder;
    }

    public function getRoute(string $type = 'search'): string
    {
        if (!array_key_exists($type, $this->routes)) {
            throw new InvalidArgumentException(
                sprintf('No route has been configured for "%s", add it to %s::$routes', $type, static::class)
            );
        }

        return $this->routes[$type];
    }

    public function getPrimaryKeyName(): string
    {
        return $this->primaryKey;
    }

    public function getPrimaryKey(): mixed
    {
        return $this->attributes[$this->primaryKey] ?? null;
    }
}
