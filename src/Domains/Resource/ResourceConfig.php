<?php

namespace SuperV\Platform\Domains\Resource;

use Event;
use SuperV\Platform\Contracts\Arrayable;
use SuperV\Platform\Domains\Resource\Events\ResourceConfigResolvedEvent;
use SuperV\Platform\Exceptions\PlatformException;
use SuperV\Platform\Support\Concerns\Hydratable;

class ResourceConfig
{
    use Hydratable;

    protected $name;

    protected $namespace;

    protected $hasUuid;

    protected $label;

    protected $singularLabel;

    protected $model;

    protected $resourceKey;

    protected $ownerKey;

    protected $keyName;

    protected $entryLabelField;

    protected $entryLabel;

    protected $nav;

    protected $attributes;

    protected $restorable;

    protected $sortable;

    /** @var \SuperV\Platform\Domains\Resource\ResourceDriver */
    protected $driver;

    protected $userTypeConfig;

    public static $__cache = [];

    protected function __construct(array $attributes = [], $overrideDefault = true)
    {
        if (! empty($attributes)) {
            if ($driver = array_get($attributes, 'driver')) {
                $attributes['driver'] = new ResourceDriver($driver);
            }
            $this->hydrate($attributes, $overrideDefault);
        }
    }

    public function getResourceKey()
    {
        if ($this->resourceKey) {
            return $this->resourceKey;
        }

        if ($this->getName()) {
            return str_singular($this->getName());
        }

        return null;
    }

    public function resourceKey($resourceKey)
    {
        $this->resourceKey = $resourceKey;

        return $this;
    }

    public function getIdentifier()
    {
        return $this->getNamespace().'.'.$this->getName();
    }

    public function setIdentifier($identifier)
    {
        list($this->namespace, $this->name) = explode('.', $identifier);

        return $this;
    }

    public function identifier($identifier)
    {
        return $this->setIdentifier($identifier);
    }

    public function hasUserWithRole($role, array $userTypeConfig = [])
    {
        $this->userTypeConfig = array_merge($userTypeConfig, compact('role'));

        return $this;
    }

    public function getHandle()
    {
        return $this->getIdentifier();
    }

    public function label($label)
    {
        $this->label = $label;

        if (! $this->resourceKey) {
            $this->resourceKey(str_slug(str_singular($label), '_'));
        }
    }

    public function fill(array $attributes = [])
    {
        $this->hydrate($attributes);

//        foreach ($attributes as $key => $value) {
//            $this->attributes[$key] = $value;
//        }
    }

    public function getKeyName($default = 'id')
    {
        return $this->keyName ?? $default;
    }

    public function keyName($keyName)
    {
        $this->keyName = $keyName;

        return $this;
    }

    public function getLabel()
    {
        return $this->label ?? ucwords(str_replace('_', ' ', $this->getName()));
    }

    public function getModel()
    {
        return $this->model;
    }

    public function model($model)
    {
        $this->model = $model;

        return $this;
    }

    public function entryLabel($entryLabel)
    {
        $this->entryLabel = $entryLabel;

        return $this;
    }

    public function getEntryLabel($default = null)
    {
        return $this->entryLabel ?? $default;
    }

    public function entryLabelField($fieldName)
    {
        $this->entryLabelField = $fieldName;

        return $this;
    }

    public function getEntryLabelField()
    {
        return $this->entryLabelField;
    }

    public function hasUuid(): bool
    {
        return $this->hasUuid ?? false;
    }

    public function setHasUuid(bool $hasUuid): ResourceConfig
    {
        $this->hasUuid = $hasUuid;

        return $this;
    }

    public function nav($nav)
    {
        $this->nav = $nav;

        return $this;
    }

    public function isRestorable(): bool
    {
        return $this->restorable ?? false;
    }

    public function isSortable(): bool
    {
        return $this->sortable ?? false;
    }

    public function getNav()
    {
        return $this->nav;
    }

    public function restorable(bool $restorable = true): ResourceConfig
    {
        $this->restorable = $restorable;

        return $this;
    }

    public function sortable(bool $sortable): ResourceConfig
    {
        $this->sortable = $sortable;

        return $this;
    }

    public function singularLabel($singularLabel)
    {
        $this->singularLabel = $singularLabel;

        return $this;
    }

    public function getSingularLabel()
    {
        return $this->singularLabel;
    }

    public function ownerKey($ownerKey)
    {
        $this->ownerKey = $ownerKey;

        return $this;
    }

    public function getOwnerKey()
    {
        return $this->ownerKey;
    }

    public function getDriver(): ?ResourceDriver
    {
        return $this->driver;
    }

    public function getDriverParam($key)
    {
        return $this->getDriver()->getParam($key);
    }

    public function getTable()
    {
        return $this->getDriver()->getParam('table');
    }

    public function getConnection()
    {
        return $this->getDriver()->getParam('connection');
    }

    public function connection($connection)
    {
        return $this->getDriver()->setParam('connection', $connection);
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function setNamespace($namespace): self
    {
        $this->namespace = $namespace;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }

    public function name($name)
    {
        return $this->setName($name);
    }

    public function getUserTypeConfig()
    {
        return $this->userTypeConfig;
    }

    public function merge(string $otherClass)
    {
        $other = new $otherClass;

        $this->fill($other->toArray());
    }

    public function toArray(): array
    {
        $attributes = [];
        foreach ($this as $key => $value) {
            if ($value instanceof Arrayable) {
                $value = $value->toArray();
            }
            $getter = 'get'.studly_case(snake_case($key));
            if (! $value && method_exists($this, $getter)) {
                $value = $this->{$getter}();
            }
            $attributes[snake_case($key)] = $value;
        }

        return array_except($attributes, 'resource');
    }

    public static function make(array $config = [], $overrideDefault = true)
    {
        $config = (new static($config, $overrideDefault));

        ResourceConfigResolvedEvent::fire($config);

        Event::dispatch(sprintf("%s.events:config_resolved", $config->getIdentifier()), $config);

        return $config;
    }

    public static function find($identifier): ResourceConfig
    {
        if (! $identifier) {
            PlatformException::runtime('Identifier can not be null');
        }

        if (! isset(static::$__cache[$identifier])) {
            $resourceEntry = ResourceModel::query()->where('identifier', $identifier)->first();

            if (is_null($resourceEntry)) {
                PlatformException::runtime("Resource config not found for identifier: ".$identifier);
            }

            static::$__cache[$identifier] = static::make($resourceEntry->config);
        }

        return static::$__cache[$identifier];
    }
}
