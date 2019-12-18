<?php

namespace SuperV\Platform\Domains\Resource\Resource;

use Closure;
use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesFilter;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface;
use SuperV\Platform\Domains\Resource\Resource;

class Fields
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Resource
     */
    protected $resource;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $fields;

    public function __construct(Resource $resource, $fields)
    {
        $this->resource = $resource;
        $this->fields = $fields instanceof Closure ? $fields($resource) : $fields;
    }

    public function getAll()
    {
        return $this->fields;
    }

    public function sort()
    {
        $this->fields = $this->fields->sortBy(function (FieldInterface $field) {
            return $field->getConfigValue('sort_order', 100);
        });

        return $this;
    }

    public function has($name)
    {
        return ! is_null($this->find($name));
    }

    public function find($name): ?FieldInterface
    {
        return $this->fields->first(
            function (FieldInterface $field) use ($name) {
                return $field->getHandle() === $name;
            });
    }

    public function get($name): ?FieldInterface
    {
        return $this->find($name);
//
//        if (! $field = $this->find($name)) {
//            PlatformException::fail("Field not found: [{$name}]");
//        }
//
//        return $field;
    }

    public function getEntryLabelField()
    {
        return $this->find($this->resource->config()->getEntryLabelField());
    }

    public function showOnIndex($name): FieldInterface
    {
        return $this->get($name)->showOnIndex();
    }

    public function keyByName(): Collection
    {
        return $this->fields->keyBy(function (FieldInterface $field) {
            return $field->getHandle();
        });
    }

    public function withFlag($flag): Collection
    {
        return $this->fields->filter(function (FieldInterface $field) use ($flag) {
            return $field->hasFlag($flag);
        });
    }

    public function getFilters(): Collection
    {
        $filters = $this->fields
            ->filter(function (FieldInterface $field) {
                return $field->hasFlag('filter');
            })->map(function (FieldInterface $field) {
                if ($field->getFieldType() instanceof ProvidesFilter) {
                    return $field->getFieldType()->makeFilter($field->getConfigValue('filter'));
                }
            });

        return $filters->filter();
    }

    public function getHeaderImage(): ?FieldInterface
    {
        return $this->withFlag('header.show')->first();
    }
}
