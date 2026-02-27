<?php

declare(strict_types=1);

namespace MapperBundle\Adapter;

use AutoMapperPlus\AutoMapperInterface;
use AutoMapperPlus\DataType;
use AutoMapperPlus\MappingOperation\Operation;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\PropertyInfo\Type;
use Symfony\Component\TypeInfo\Type\CollectionType;
use Symfony\Component\TypeInfo\Type\NullableType;
use Symfony\Component\TypeInfo\Type\ObjectType;

class AutoMapperPlusAdapter implements AutoMapperAdapterInterface
{
    private AutoMapperInterface $autoMapper;
    private PropertyInfoExtractor $extractor;

    public function __construct(
        AutoMapperInterface $autoMapper,
        PropertyInfoExtractor $extractor,
    ) {
        $this->autoMapper = $autoMapper;
        $this->extractor = $extractor;
    }

    public function map($source, $destination)
    {
        $this->autoConfiguration($source, $destination);
        if (is_object($destination)) {
            return $this->autoMapper->mapToObject($source, $destination);
        }

        return $this->autoMapper->map($source, $destination);
    }

    public function mapToObject($source, $destination): object
    {
        return $this->map($source, $destination);
    }

    public function mapToArray(object $source): array
    {
        return $this->map($source, DataType::ARRAY);
    }

    public function mapMultiple(iterable $sources, string $destination): iterable
    {
        if (empty($sources)) {
            return [];
        }

        $this->autoConfiguration(end($sources), $destination);

        return $this->autoMapper->mapMultiple($sources, $destination);
    }

    public function getConfiguration()
    {
        return $this->autoMapper->getConfiguration();
    }

    /**
     * @param array|object        $source
     * @param array|object|string $destination
     */
    private function autoConfiguration($source, $destination): void
    {
        $destination = is_object($destination) ? $destination::class : $destination;
        if (
            !is_array($source)
            || $this->autoMapper->getConfiguration()->hasMappingFor('array', $destination)
        ) {
            return;
        }

        // check for version symfony/property-info (v6.0.0) compatibility
        if (!class_exists(PropertyInfoExtractor::class)) {
            $this->createSchemaForMappingOld($destination);

            return;
        }

        $this->createSchemaForMapping($destination);
    }

    private function createSchemaForMapping(string $destination): void
    {
        $config = $this->autoMapper->getConfiguration();
        if (null !== $config->getMappingFor(DataType::ARRAY, $destination)) {
            return;
        }
        $mapping = $config->registerMapping(DataType::ARRAY, $destination);

        $props = $this->extractor->getProperties($destination);

        if (null === $props) {
            return;
        }

        foreach ($props as $property) {
            $type = $this->extractor->getType($destination, $property);

            if (null === $type) {
                continue;
            }

            if ($type instanceof NullableType) {
                $type = $type->getWrappedType();
            }

            if ($type instanceof CollectionType) {
                $valueType = $type->getCollectionValueType();

                if ($valueType instanceof ObjectType) {
                    $innerClass = $valueType->getClassName();

                    $this->createSchemaForMapping($innerClass);
                    $mapping->forMember($property, Operation::mapTo($innerClass));
                }
            } elseif ($type instanceof ObjectType) {
                $innerClass = $type->getClassName();

                if (is_a($innerClass, \DateTimeInterface::class, true)) {
                    $mapping->forMember($property, $this->getDateTimeMappingOperation($property, $innerClass));
                } else {
                    $this->createSchemaForMapping($innerClass);
                    $mapping->forMember($property, Operation::mapTo($innerClass, true));
                }
            }
        }
    }

    private function createSchemaForMappingOld(string $destination): void
    {
        $config = $this->autoMapper->getConfiguration();
        if (null !== $config->getMappingFor(DataType::ARRAY, $destination)) {
            return;
        }

        $mapping = $config->registerMapping(DataType::ARRAY, $destination);

        $props = $this->extractor->getProperties($destination);

        foreach ($props as $property) {
            /** @var Type[]|null $types */
            $types = $this->extractor->getTypes($destination, $property);
            if (!$types) {
                continue;
            }

            $propertyInfo = $types[0];
            $innerClass = false;
            if ($types = $propertyInfo->getCollectionValueTypes()) {
                $innerClass = $types[0]->getClassName();
                $this->createSchemaForMappingOld($innerClass);
                $mapping->forMember($property, Operation::mapTo($innerClass));
            } elseif (is_a($propertyInfo->getClassName(), \DateTimeInterface::class, true)) {
                $innerClass = $propertyInfo->getClassName();
                $mapping->forMember($property, $this->getDateTimeMappingOperation($property, $innerClass));
            } elseif ('object' === $propertyInfo->getBuiltinType()) {
                $innerClass = $propertyInfo->getClassName();
                $this->createSchemaForMappingOld($innerClass);
                $mapping->forMember($property, Operation::mapTo($innerClass, true));
            }
        }
    }

    private function getDateTimeMappingOperation(string $property, string $destinationClass): callable
    {
        return static function ($source) use ($destinationClass, $property) {
            if (null === $source[$property]) {
                return null;
            }

            return \DateTimeImmutable::class === $destinationClass
                ? new \DateTimeImmutable($source[$property])
                : new \DateTime($source[$property]);
        };
    }
}
