<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Relationships;

use Somnambulist\Collection\Contracts\Collection;
use Somnambulist\Components\ApiClient\AbstractModel;
use Somnambulist\Components\ApiClient\Exceptions\ModelRelationshipException;
use Somnambulist\Components\ApiClient\Model;
use function get_class;

/**
 * Class HasMany
 *
 * @package    Somnambulist\Components\ApiClient\Relationships
 * @subpackage Somnambulist\Components\ApiClient\Relationships\HasMany
 */
class HasMany extends AbstractRelationship
{

    private ?string $indexBy;

    public function __construct(AbstractModel $parent, AbstractModel $related, string $attributeKey, ?string $indexBy = null, bool $lazyLoading = true)
    {
        parent::__construct($parent, $related, $attributeKey, $lazyLoading);

        if (!$parent instanceof Model) {
            throw ModelRelationshipException::valueObjectNotAllowedForRelationship(get_class($parent), $attributeKey, get_class($related));
        }

        $this->query   = $parent->newQuery();
        $this->indexBy = $indexBy;
    }

    public function fetch(): Collection
    {
        return $this->buildCollection($this->callApi($this->parent, $this->attributeKey));
    }

    public function addRelationshipResultsToModels(Collection $models, string $relationship): self
    {
        $models->each(function (AbstractModel $loaded) use ($relationship) {
            if (null === $data = $loaded->getRawAttribute($this->attributeKey)) {
                $data = [];

                if ($this->lazyLoading) {
                    $data = $this->callApi($loaded, $relationship);
                }
            }

            $loaded->setRelationshipValue($this->attributeKey, $relationship, $this->buildCollection($data));
        });

        return $this;
    }

    private function callApi(Model $model, string $relationship): array
    {
        $data = $this->parent->getResponseDecoder()->object(
            $this->query->with($relationship)->wherePrimaryKey($model->getPrimaryKey())->fetchRaw()
        );

        if (isset($data[$this->attributeKey])) {
            $data = $data[$this->attributeKey];
        }

        return $data;
    }

    private function buildCollection(array $data): Collection
    {
        $children = $this->related->getCollection();

        foreach ($data as $row) {
            $child = $this->related->new($row);

            if ($this->indexBy) {
                $children->set($child->getRawAttribute($this->indexBy), $child);
            } else {
                $children->add($child);
            }
        }

        return $children;
    }
}
