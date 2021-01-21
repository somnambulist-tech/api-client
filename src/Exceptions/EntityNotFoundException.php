<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Exceptions;

use Exception;
use function sprintf;

/**
 * Class EntityNotFoundException
 *
 * @package    Somnambulist\Components\ApiClient\Exceptions
 * @subpackage Somnambulist\Components\ApiClient\Exceptions\EntityNotFoundException
 */
class EntityNotFoundException extends Exception
{

    public static function noMatchingRecordFor(string $class, string $key, mixed ...$id): self
    {
        return new self(sprintf('Could not find a record for %s with %s and %s', $class, $key, implode(':', $id)));
    }
}
