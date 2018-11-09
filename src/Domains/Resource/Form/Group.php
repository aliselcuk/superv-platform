<?php

namespace SuperV\Platform\Domains\Resource\Form;

use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Field\Types\FieldType;
use SuperV\Platform\Domains\Resource\Field\Watcher;

class Group
{
    /** @var string */
    protected $handle;
    /**
     * @var Watcher
     */
    protected $watcher;

    /** @var \Illuminate\Support\Collection */
    protected $fields;

    /** @var \Illuminate\Support\Collection */
    protected $types;

    public function __construct(string $handle, Watcher $watcher = null, $fields)
    {
        $this->handle = $handle;
        $this->watcher = $watcher;
        $this->fields = is_array($fields) ? collect($fields) : $fields;
    }

    public function getWatcher(): ?Watcher
    {
        return $this->watcher;
    }

    public function getFields(): Collection
    {
        return $this->fields;
    }

    public function build()
    {
        $this->types = collect();

        $this->fields = $this->fields
            ->map(function ($field) {
                if (! $field instanceof Field) {
                    $field = FieldFactory::createFromEntry($field);
                }

                if ($this->watcher) {
                    $field->setWatcher($this->watcher);
                    $field->setValueFromWatcher();
                }

                $this->types->push($type = FieldType::fromField($field));

                return $field;
            });
    }

    public function getHandle(): string
    {
        return $this->handle;
    }
}