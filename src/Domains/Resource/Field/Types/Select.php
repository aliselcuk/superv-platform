<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

class Select extends FieldType
{
    public function build(): FieldType
    {
        if (array_has($this->config, 'options')) {
            $this->setConfigValue('options', Select::parseOptions($this->getConfigValue('options')));
        }

        return parent::build();
    }

    public function setOptions(array $options)
    {
        $this->setConfigValue('options', $options);

        return $this;
    }

    public static function parseOptions(array $options = [])
    {
        if (! empty($options) && ! is_array(array_first($options))) {
            return array_map(function ($value) use ($options) {
                return ['value' => $value, 'text' => $options[$value]];
            }, array_keys($options));
        }

        return $options;
    }
}