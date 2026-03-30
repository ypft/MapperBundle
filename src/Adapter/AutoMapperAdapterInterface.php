<?php

declare(strict_types=1);

namespace MapperBundle\Adapter;

interface AutoMapperAdapterInterface
{
    /**
     * @param array|object        $source
     * @param array|object|string $destination
     *
     * @return array|object|null
     */
    public function map($source, $destination);

    /**
     * @param array|object  $source
     * @param string|object $destination
     */
    public function mapToObject($source, $destination): object;

    public function mapToArray(object $source): array;

    public function mapMultiple(iterable $sources, string $destination): iterable;

    /**
     * Get the underlying configuration object.
     */
    public function getConfiguration();
}
