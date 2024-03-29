<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Exceptions;

use Exception;
use Somnambulist\Components\ApiClient\Client\Query\QueryBuilder;
use function sprintf;

class NoResultsException extends Exception
{
    private QueryBuilder $query;

    public function __construct(string $class, QueryBuilder $queryBuilder)
    {
        parent::__construct(sprintf('Could not match any records for %s', $class));

        $this->query = clone $queryBuilder;
    }

    public static function noResultsForQuery(string $class, QueryBuilder $queryBuilder): self
    {
        return new self($class, $queryBuilder);
    }

    public function getQuery(): QueryBuilder
    {
        return $this->query;
    }
}
