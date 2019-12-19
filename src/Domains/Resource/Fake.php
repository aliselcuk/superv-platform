<?php

namespace SuperV\Platform\Domains\Resource;

use Closure;
use Faker\Generator;
use Illuminate\Http\UploadedFile;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface;

class Fake
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Resource
     */
    protected $resource;

    /**
     * @var array
     */
    protected $overrides;

    /** @var \Faker\Generator */
    protected $faker;

    /** @var array */
    protected $attributes = [];

    public function __construct()
    {
        $this->faker = app(Generator::class);
    }

    public function __invoke(Resource $resource, array $overrides = [])
    {
        $this->resource = $resource;
        $this->overrides = $this->attributes = $overrides;

        $resource->getFields()
                 ->map(function (FieldInterface $field) {
                     if ($this->shouldFake($field)) {
                         $this->attributes[$field->getColumnName()] = $this->fake($field);
                     }
                 })->filter()
                 ->toAssoc()
                 ->all();

        return array_filter_null(array_merge($this->attributes, $overrides));
    }

    public static function field(FieldInterface $field)
    {
        return (new static)->fake($field);
    }

    public static function create(Resource $resource, array $overrides = [], Closure $callback = null)
    {
        $entry = $resource->create(static::make($resource, $overrides));

        if ($callback) {
            $callback($entry);
        }

        return $entry;
    }

    public static function make(Resource $resource, array $overrides = [])
    {
        return (new static())->__invoke($resource, $overrides);
    }

    protected function shouldFake(FieldInterface $field)
    {
        return ! $field->isHidden()
//            && ! $field->hasFlag('form.hide')
            && ! $field->doesNotInteractWithTable();
    }

    protected function fake(FieldInterface $field)
    {
        if ($value = array_get($this->overrides, $field->getColumnName())) {
            if (is_callable($value)) {
                return $value();
            }

            return $value;
        }
        if ($faker = $field->getFieldType()->resolveFaker()) {
            return $faker->fake($this->resource, $field);
        }

        if (method_exists($this, $method = camel_case('fake_'.$field->getType()))) {
            return $this->$method($field);
        }

        return $this->faker->text;
    }

    protected function fakeBelongsTo(FieldInterface $field)
    {
        $relatedResource = ResourceFactory::make($field->getConfigValue('related_resource'));

        if ($relatedResource->count() === 0) {
            if ($relatedResource->getIdentifier() === $this->resource->getIdentifier()) {
                return rand(1, 5); // otherwise causes dead recursion
            } else {
                $relatedResource->fake([]);
            }
        }

        return $relatedResource->newQuery()->inRandomOrder()->value('id');
    }

    protected function fakeFile()
    {
        if (defined('SV_TEST_BASE')) {
            return new UploadedFile(SV_TEST_BASE.'/__fixtures__/square.png', 'square.png');
        }
    }

    protected function fakeText(FieldInterface $field)
    {
        $fieldName = $field->getHandle();

        if (in_array($fieldName, [
                'email',
                'currency_code',
                'first_name',
                'last_name']) && $fake = $this->faker->__get(camel_case($fieldName))) {
            return $fake;
        }

        if (in_array($fieldName, [
                'color',
                'domain']) && $fake = $this->faker->__get(camel_case($fieldName.'_name'))) {
            return $fake;
        }

        if ($fieldName === 'slug') {
            return str_slug($this->faker->unique()->name, '_');
        }

        if (ends_with($fieldName, '_name') || $fieldName === 'name') {
            return $this->faker->name;
        }

        if (ends_with($fieldName, '_at')) {
            return $this->faker->dateTime;
        }

        if (ends_with($fieldName, '_phone') || $fieldName === 'phone' || $fieldName === 'cell') {
            return $this->faker->phoneNumber;
        }

        if (ends_with($fieldName, '_number') || $fieldName === 'number') {
            return $this->faker->randomNumber(8);
        }

        if (ends_with($fieldName, '_address') || $fieldName === 'address') {
            return $this->faker->address;
        }

        if (ends_with($fieldName, '_url') || $fieldName === 'website') {
            return $this->faker->url;
        }

        if (ends_with($fieldName, '_rate') || $fieldName === 'rate') {
            return $this->faker->randomFloat(2, 0.5, 10);
        }

        if (ends_with($fieldName, '_price') || $fieldName === 'price') {
            return $this->faker->randomFloat(2, 0.5, 100);
        }

        if (ends_with($fieldName, '_count') || $fieldName === 'quantity' || $fieldName === 'qty' || $fieldName === 'count') {
            return $this->faker->randomNumber(2);
        }

        return $this->faker->word;
    }

    protected function fakeTextarea()
    {
        return $this->faker->paragraph;
    }

    protected function fakeDictionary()
    {
        return $this->faker->rgbColorAsArray;
    }

    protected function fakeSelect(FieldInterface $field)
    {
        return array_random(wrap_array($field->getConfigValue('options')));
    }

    protected function fakeNumber(FieldInterface $field)
    {
        if ($field->getConfigValue('type') === 'decimal') {
            $max = $field->getConfigValue('total', 5) - 2;

            return $this->faker->randomFloat($field->getConfigValue('places', 2), 0, $max);
        }

        return $field->getHandle() === 'age' ? $this->faker->numberBetween(10, 99) : $this->faker->randomNumber();
    }

    protected function fakeEmail()
    {
        return $this->faker->safeEmail;
    }

    protected function fakeBoolean()
    {
        return $this->faker->boolean;
    }

    protected function fakeDatetime(FieldInterface $field)
    {
        return $field->getConfigValue('time') ? $this->faker->dateTime : $this->faker->date();
    }
}
