<?php

namespace SuperV\Platform\Domains\UI\Form;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use SuperV\Platform\Domains\Entry\EntryModel;
use Symfony\Component\PropertyAccess\Exception;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

class PropertyAccessor implements PropertyAccessorInterface
{
    /**
     * Sets the value at the end of the property path of the object graph.
     *
     * Example:
     *
     *     use Symfony\Component\PropertyAccess\PropertyAccess;
     *
     *     $propertyAccessor = PropertyAccess::createPropertyAccessor();
     *
     *     echo $propertyAccessor->setValue($object, 'child.name', 'Fabien');
     *     // equals echo $object->getChild()->setName('Fabien');
     *
     * This method first tries to find a public setter for each property in the
     * path. The name of the setter must be the camel-cased property name
     * prefixed with "set".
     *
     * If the setter does not exist, this method tries to find a public
     * property. The value of the property is then changed.
     *
     * If neither is found, an exception is thrown.
     *
     * @param object|array                 $objectOrArray The object or array to modify
     * @param string|PropertyPathInterface $propertyPath  The property path to modify
     * @param mixed                        $value         The value to set at the end of the property path
     *
     * @throws Exception\InvalidArgumentException If the property path is invalid
     * @throws Exception\AccessException          If a property/index does not exist or is not public
     * @throws Exception\UnexpectedTypeException  If a value within the path is neither object nor array
     */
    public function setValue(&$objectOrArray, $propertyPath, $value)
    {
        /** @var EntryModel $entry */
        $entry = $objectOrArray;
        $field = (string) $propertyPath;

        if ($entry instanceof EntryModel) {
            $relationships = $entry->getRelationships();
            if (in_array($field, $relationships)) {
                $relation = $entry->{$field}();
                if ($relation instanceof HasOne) {
                    $entry->setAttribute("{$field}_id", $value);
                } elseif ($relation instanceof BelongsToMany) {
                    if (!$entry->exists) {
                        $entry->onCreate(function ($entry) use ($field, $value) {
                            $entry->{$field}()->sync($value);
                        });
                    } else {
                        $entry->{$field}()->sync($value);
                    }
                }
            } else {
                $entry->setAttribute($field, $value);
            }
        }
    }

    /**
     * Returns the value at the end of the property path of the object graph.
     *
     * Example:
     *
     *     use Symfony\Component\PropertyAccess\PropertyAccess;
     *
     *     $propertyAccessor = PropertyAccess::createPropertyAccessor();
     *
     *     echo $propertyAccessor->getValue($object, 'child.name);
     *     // equals echo $object->getChild()->getName();
     *
     * This method first tries to find a public getter for each property in the
     * path. The name of the getter must be the camel-cased property name
     * prefixed with "get", "is", or "has".
     *
     * If the getter does not exist, this method tries to find a public
     * property. The value of the property is then returned.
     *
     * If none of them are found, an exception is thrown.
     *
     * @param object|array                 $objectOrArray The object or array to traverse
     * @param string|PropertyPathInterface $propertyPath  The property path to read
     *
     * @throws Exception\InvalidArgumentException If the property path is invalid
     * @throws Exception\AccessException          If a property/index does not exist or is not public
     * @throws Exception\UnexpectedTypeException  If a value within the path is neither object
     *                                            nor array
     *
     * @return mixed The value at the end of the property path
     */
    public function getValue($objectOrArray, $propertyPath)
    {
        $field = (string) $propertyPath;
        if ($objectOrArray instanceof EntryModel) {
            $relationships = $objectOrArray->getRelationships();
            if (in_array($field, $relationships)) {
                $relation = $objectOrArray->{$field}();

                if ($relation instanceof HasOne) {
                    return $objectOrArray->getAttribute("{$field}_id");
                } elseif ($relation instanceof BelongsToMany || $relation instanceof HasMany) {
                    $related = $relation->getRelated();
                    $value = $objectOrArray->{$field}()
                                           ->pluck(
                                               $related->getQualifiedKeyName(), $related->getTitleColumn()
                                           )->toArray();

                    return $value;
                }
            }

            return $objectOrArray->getAttribute($field);
        }
    }

    /**
     * Returns whether a value can be written at a given property path.
     *
     * Whenever this method returns true, {@link setValue()} is guaranteed not
     * to throw an exception when called with the same arguments.
     *
     * @param object|array                 $objectOrArray The object or array to check
     * @param string|PropertyPathInterface $propertyPath  The property path to check
     *
     * @throws Exception\InvalidArgumentException If the property path is invalid
     *
     * @return bool Whether the value can be set
     */
    public function isWritable($objectOrArray, $propertyPath)
    {
    }

    /**
     * Returns whether a property path can be read from an object graph.
     *
     * Whenever this method returns true, {@link getValue()} is guaranteed not
     * to throw an exception when called with the same arguments.
     *
     * @param object|array                 $objectOrArray The object or array to check
     * @param string|PropertyPathInterface $propertyPath  The property path to check
     *
     * @throws Exception\InvalidArgumentException If the property path is invalid
     *
     * @return bool Whether the property path can be read
     */
    public function isReadable($objectOrArray, $propertyPath)
    {
    }
}
