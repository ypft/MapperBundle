<?php

declare(strict_types=1);

namespace MapperBundle\Adapter\Transformer;

use AutoMapper\Generator\UniqueVariableScope;
use AutoMapper\Metadata\PropertyMetadata;
use AutoMapper\Transformer\TransformerInterface;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;

/**
 * Flexible DateTime transformer that accepts various date formats.
 * Uses new DateTime() constructor instead of strict createFromFormat().
 */
final readonly class FlexibleDateTimeTransformer implements TransformerInterface
{
    public function __construct(
        private string $className = \DateTime::class,
    ) {
    }

    public function transform(Expr $input, Expr $target, PropertyMetadata $propertyMapping, UniqueVariableScope $uniqueVariableScope, Expr\Variable $source, ?Expr $existingValue = null): array
    {
        $className = \DateTimeInterface::class === $this->className ? \DateTimeImmutable::class : $this->className;

        // Generate: new \DateTime($input) ?: false
        return [
            new Expr\Ternary(
                new Expr\New_(
                    new Name\FullyQualified($className),
                    [new Arg($input)],
                ),
                null,
                new Expr\ConstFetch(new Name('false')),
            ),
            [],
        ];
    }
}
