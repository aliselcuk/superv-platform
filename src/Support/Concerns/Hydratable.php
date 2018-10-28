<?php

namespace SuperV\Platform\Support\Concerns;

trait Hydratable
{
    public function hydrate(array $parameters)
    {
        if (empty($parameters)) {
            return $this;
        }

        if (isset($this->hydratables)) {
            $parameters = array_intersect_key($parameters, array_flip($this->hydratables));
        }

        foreach ($parameters as $parameter => $value) {
            if (is_null($value)) {
                continue;
            }

            if (method_exists($this, $method = camel_case('set_'.$parameter))) {
                $this->{$method}($value);
            } else {
                if (property_exists($this, $parameter)) {
                    $this->$parameter = $value;
                }
            }
        }

        return $this;
    }
}