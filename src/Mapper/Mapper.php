<?php

declare(strict_types=1);

namespace MapperBundle\Mapper;

use AutoMapperPlus\Exception\UnregisteredMappingException;
use MapperBundle\Adapter\AutoMapperAdapterInterface;
use MapperBundle\Configuration\AutoMapperConfig;
use MapperBundle\PreLoader\PreloaderInterface;

/**
 * Class Mapper.
 */
class Mapper implements MapperInterface
{
    private AutoMapperAdapterInterface $adapter;
    private PreloaderInterface $preLoader;

    public function __construct(
        AutoMapperAdapterInterface $adapter,
        PreloaderInterface $preLoader,
    ) {
        $this->adapter = $adapter;
        $this->preLoader = $preLoader;
    }

    /**
     * @param array|object        $source
     * @param array|object|string $destination
     *
     * @return array|mixed|object|null
     *
     * @throws UnregisteredMappingException
     */
    public function convert($source, $destination)
    {
        return $this->adapter->map($source, $destination);
    }

    /**
     * @throws UnregisteredMappingException
     */
    public function convertToObject(array|object $source, string|object $destination): object
    {
        return $this->adapter->mapToObject($source, $destination);
    }

    /**
     * @throws UnregisteredMappingException
     */
    public function convertToArray(object $source): array
    {
        return $this->adapter->mapToArray($source);
    }

    public function convertCollection(iterable $sources, string $destination): iterable
    {
        if (empty($sources)) {
            return [];
        }

        $configuration = $this->adapter->getConfiguration();

        if (
            $configuration instanceof AutoMapperConfig
            && true === $configuration->usePreLoad()
        ) {
            return $this->convertCollectionWithPreLoader($sources, $destination);
        }

        return $this->adapter->mapMultiple($sources, $destination);
    }

    public function convertCollectionWithPreLoader(iterable $sources, string $destination): iterable
    {
        if (empty($sources)) {
            return [];
        }

        $configuration = $this->adapter->getConfiguration();

        $sourceClass = true === is_array($sources) ? get_class($sources[0]) : $sources->getTypeClass()->name;
        $mapping = $configuration->getMappingFor($sourceClass, $destination);
        $registeredMappingOperations = $mapping->getRegisteredMappingOperations();

        return $this->adapter->mapMultiple(
            $this->preLoader->preLoad($sources, $registeredMappingOperations),
            $destination,
        );
    }
}
