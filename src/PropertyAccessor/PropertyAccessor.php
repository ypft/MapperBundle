<?php

declare(strict_types=1);

namespace MapperBundle\PropertyAccessor;

use AutoMapperPlus\PropertyAccessor\PropertyAccessor as BasePropertyAccessor;

/**
 * Class PropertyAccessor.
 */
class PropertyAccessor extends BasePropertyAccessor
{
    public function setProperty($object, string $propertyName, $value): void
    {
        $setter = 'set'.ucfirst($propertyName);
        if ($this->isSetterExists($object, $setter)) {
            try {
                $object->$setter($value);

                return;
            } catch (\Throwable $exception) {
                // method is private or strict typehint is invalid. Skip this step
            }
        }

        if ($this->isUndefinedExistingValue($object, $propertyName)) {
            try {
                $object->$propertyName = $value;

                return;
            } catch (\Throwable $exception) {
                // property is private or strict typehint is invalid. Skip this step
            }
        }

        parent::setProperty($object, $propertyName, $value);
    }

    private function isSetterExists($object, string $setter): bool
    {
        return method_exists($object, $setter);
    }

    private function isUndefinedExistingValue($object, string $propertyName): bool
    {
        return property_exists($object, $propertyName)
            && !isset($object->$propertyName);
    }
}
