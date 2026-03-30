<?php

declare(strict_types=1);

namespace MapperBundle\PropertyAccessor;

class MergePropertyAccessor extends PropertyAccessor
{
    public function setProperty($object, string $propertyName, $value): void
    {
        if (null !== $value) {
            parent::setProperty($object, $propertyName, $value);
        }
    }
}
