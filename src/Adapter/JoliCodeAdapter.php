<?php

declare(strict_types=1);

namespace MapperBundle\Adapter;

use AutoMapper\AutoMapper;
use MapperBundle\Adapter\Transformer\FlexibleDateTimeTransformerFactory;

class JoliCodeAdapter implements AutoMapperAdapterInterface
{
    private $autoMapper;

    public function __construct($autoMapper = null)
    {
        if (null === $autoMapper) {
            // Create AutoMapper with custom DateTime transformer
            $this->autoMapper = AutoMapper::create(
                transformerFactories: [new FlexibleDateTimeTransformerFactory()],
            );
        } else {
            $this->autoMapper = $autoMapper;
        }
    }

    public function map($source, $destination)
    {
        return $this->autoMapper->map($source, $destination);
    }

    public function mapToObject($source, $destination): object
    {
        return $this->autoMapper->map($source, $destination);
    }

    public function mapToArray(object $source): array
    {
        return $this->autoMapper->map($source, 'array');
    }

    public function mapMultiple(iterable $sources, string $destination): iterable
    {
        if (empty($sources)) {
            return [];
        }

        return $this->autoMapper->mapCollection($sources, $destination);
    }

    public function getConfiguration()
    {
        return null;
    }
}
