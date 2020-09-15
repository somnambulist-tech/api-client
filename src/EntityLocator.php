<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient;

use Pagerfanta\Pagerfanta;
use Somnambulist\Collection\Contracts\Collection;

/**
 * Class EntityLocator
 *
 * @package    Somnambulist\Components\ApiClient
 * @subpackage Somnambulist\Components\ApiClient\EntityLocator
 */
class EntityLocator
{

    private Manager $manager;
    private string $class;
    private array $with = [];

    public function __construct(Manager $manager, string $class)
    {
        $this->manager = $manager;
        $this->class   = $class;
    }

    public function with(string ...$include): self
    {
        $this->with = $include;

        return $this;
    }

    public function getClassName(): string
    {
        return $this->class;
    }

    public function find($id): ?object
    {
        return $this->query()->find($id);
    }

    public function findBy(array $criteria = [], array $orderBy = [], int $limit = null, int $offset = null): Collection
    {
        return $this->query()->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function findOneBy(array $criteria = [], array $orderBy = []): ?object
    {
        return $this->query()->findOneBy($criteria, $orderBy);
    }

    public function findByPaginated(array $criteria = [], array $orderBy = [], int $page = 1, int $perPage = 30): Pagerfanta
    {
        $qb = $this->query();

        foreach ($criteria as $field => $value) {
            $qb->andWhere($qb->expr()->eq($field, $value));
        }
        foreach ($orderBy as $field => $dir) {
            $qb->addOrderBy($field, $dir);
        }

        return $qb->paginate($page, $perPage);
    }

    private function query(): ModelBuilder
    {
        $qb = $this->class::with($this->with);

        $this->with = [];

        return $qb;
    }
}