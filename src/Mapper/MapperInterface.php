<?php

declare(strict_types=1);

namespace MapperBundle\Mapper;

interface MapperInterface
{
    /**
     * @template T of object
     *
     * @param array|object             $source
     * @param T|class-string<T>|string $destination
     *
     * @return T|array
     */
    public function convert($source, $destination);

    /**
     * @template T of object
     *
     * @param T|class-string<T> $destination
     *
     * @return T
     */
    public function convertToObject(array|object $source, string|object $destination): object;

    public function convertToArray(object $source): array;

    /**
     * @template T of object
     *
     * @param class-string<T> $destination
     *
     * @return iterable<T>
     */
    public function convertCollection(iterable $sources, string $destination): iterable;

    /**
     * @template T of object
     *
     * @param class-string<T> $destination
     *
     * @return iterable<T>
     */
    public function convertCollectionWithPreLoader(iterable $sources, string $destination): iterable;
}
