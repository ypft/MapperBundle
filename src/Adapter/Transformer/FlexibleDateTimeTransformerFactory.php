<?php

declare(strict_types=1);

namespace MapperBundle\Adapter\Transformer;

use AutoMapper\Metadata\MapperMetadata;
use AutoMapper\Metadata\SourcePropertyMetadata;
use AutoMapper\Metadata\TargetPropertyMetadata;
use AutoMapper\Transformer\AbstractUniqueTypeTransformerFactory;
use AutoMapper\Transformer\PrioritizedTransformerFactoryInterface;
use AutoMapper\Transformer\TransformerInterface;
use Symfony\Component\PropertyInfo\Type;

/**
 * Factory for flexible DateTime transformer.
 * Higher priority than default DateTimeTransformerFactory to override strict RFC3339 parsing.
 */
final class FlexibleDateTimeTransformerFactory extends AbstractUniqueTypeTransformerFactory implements PrioritizedTransformerFactoryInterface
{
    protected function createTransformer(Type $sourceType, Type $targetType, SourcePropertyMetadata $source, TargetPropertyMetadata $target, MapperMetadata $mapperMetadata): ?TransformerInterface
    {
        // Only handle string to DateTime conversion
        if ('string' === $sourceType->getBuiltinType()
            && 'object' === $targetType->getBuiltinType()
            && is_a($targetType->getClassName(), \DateTimeInterface::class, true)
        ) {
            return new FlexibleDateTimeTransformer($targetType->getClassName());
        }

        return null;
    }

    public function getPriority(): int
    {
        return 100; // Higher than DateTimeTransformerFactory (0)
    }
}
