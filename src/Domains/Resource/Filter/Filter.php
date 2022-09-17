<?php

namespace SuperV\Platform\Domains\Resource\Filter;

use Closure;
use SuperV\Platform\Domains\Resource\Contracts\Filter\Filter as FilterContract;
use SuperV\Platform\Domains\Resource\Contracts\Filter\ProvidesField;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Support\Concerns\FiresCallbacks;

abstract class Filter implements FilterContract, ProvidesField
{
    use FiresCallbacks;

    /**
     * Main identifier for the filter
     *
     * @var null
     */
    protected $identifier;

    /**
     * Corresponding entry attribute if not same with the identifier
     *
     * @var string
     */
    protected $attribute;

    /**
     * Filter type
     *
     * @var string
     */
    protected $type;

    /**
     * Filter label
     *
     * @var string
     */
    protected $label;

    /**
     * Filter placeholder
     *
     * @var string
     */
    protected $placeholder;

    /**
     * Default value
     *
     * @var mixed
     */
    protected $defaultValue;

    /**
     * Resource that filter belongs to
     *
     * @var \SuperV\Platform\Domains\Resource\Resource
     */
    protected $resource;

    protected $callback;

    public function __construct($identifier = null, $label = null)
    {
        if ($identifier) {
            $this->identifier = $identifier;
        }

        if ($label) {
            $this->label = $label;
        }

        $this->boot();
    }

    protected function boot() { }

    public function applyQuery($query, $value)
    {
        if ($this->callback) {
            return ($this->callback)($query, $value);
        }

        if (method_exists($this, 'apply')) {
            return $this->apply($query, $value);
        }

        if (\Str::contains($this->getIdentifier(), '.')) {
            return $this->applyRelationQuery($query, $this->getIdentifier(), $value);
        }

        $query->where($this->getAttribute(), '=', $value);
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getPlaceholder()
    {
        return $this->placeholder ?? $this->getLabel();
    }

    public function setResource(Resource $resource): FilterContract
    {
        $this->resource = $resource;

        return $this;
    }

    public function getLabel()
    {
        return $this->label ?? str_unslug($this->identifier);
    }

    /**
     * @param mixed $callback
     * @return Filter
     */
    public function setApplyCallback(Closure $callback): FilterContract
    {
        $this->callback = $callback;

        return $this;
    }

    public function onFieldBuilt(FieldInterface $field) { }

    public function makeField(): FieldInterface
    {
        $field = FieldFactory::createFromArray([
            'revision_id' => uuid(),
            'type'        => $this->getType(),
            'identifier'  => $this->getIdentifier(),
            'handle'      => $this->getIdentifier(),
            'placeholder' => __($this->getPlaceholder()),
            'value'       => $this->getDefaultValue(),

        ]);

        $this->fire('field.built', ['field' => $field]);

        return $field;
    }

    public function getAttribute()
    {
        return $this->attribute ?? $this->getIdentifier();
    }

    public function setAttribute($attribute)
    {
        $this->attribute = $attribute;

        return $this;
    }

    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }

    public static function make($identifier = null, $label = null)
    {
        return new static($identifier, $label);
    }

    protected function applyRelationQuery($query, $slug, $value, $operator = '=', $method = 'whereHas')
    {
        [$relation, $column] = explode('.', $slug);
        $query->{$method}($relation, function ($query) use ($column, $value, $operator) {
            $query->where($column, $operator, $value);
        });
    }
}
