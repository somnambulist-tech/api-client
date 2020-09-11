<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Relationships;

use Somnambulist\Collection\Contracts\Collection;
use Somnambulist\Components\ApiClient\AbstractModel;
use Somnambulist\Components\ApiClient\Exceptions\ModelRelationshipException;
use Somnambulist\Components\ApiClient\Model;
use function get_class;
use function is_null;

/**
 * Class BelongsTo
 *
 * @package    Somnambulist\Components\ApiClient\Relationships
 * @subpackage Somnambulist\Components\ApiClient\Relationships\BelongsTo
 */
class BelongsTo extends AbstractRelationship
{

    private string $identityKey;
    private bool $nullOnNotFound;

    public function __construct(AbstractModel $parent, AbstractModel $related, string $attributeKey, string $identityKey, bool $nullOnNotFound = true)
    {
        parent::__construct($parent, $related, $attributeKey);

        if (!$related instanceof Model) {
            throw ModelRelationshipException::valueObjectNotAllowedForRelationship(get_class($parent), self::class, get_class($related));
        }

        $this->query          = $related->newQuery();
        $this->identityKey    = $identityKey;
        $this->nullOnNotFound = $nullOnNotFound;
    }

    public function addRelationshipResultsToModels(Collection $models, string $relationship): self
    {
        $models->each(function (AbstractModel $loaded) use ($relationship) {
            if (null === $data = $loaded->getRawAttribute($this->attributeKey)) {
                $data = $this->query->with($relationship)->wherePrimaryKey($loaded->getRawAttribute($this->identityKey))->fetchRaw();

                if (isset($data[$this->attributeKey])) {
                    $data = $data[$this->attributeKey];
                }

                if (empty($data)) {
                    $data = null;
                }
            }

            $related = is_null($data) ? ($this->nullOnNotFound ? null : $this->related->new()) : $this->related->new($data);

            $loaded->setRelationshipValue($this->attributeKey, $relationship, $related);
        });

        return $this;
    }
}