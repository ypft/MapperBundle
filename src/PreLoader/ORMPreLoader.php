<?php

declare(strict_types=1);

namespace MapperBundle\PreLoader;

use Doctrine\ORM\EntityManagerInterface;

readonly class ORMPreLoader implements PreloaderInterface
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function preLoad($sourceCollection, array $registeredMappingOperations): array
    {
        if (true === empty($sourceCollection)) {
            return $sourceCollection;
        }

        $reflection = new \ReflectionClass($sourceCollection[0]);
        $sourceProperties = $reflection->getProperties();

        foreach ($this->collectRelations($sourceCollection, $sourceProperties, $registeredMappingOperations) as $group) {
            $this->eagerLoadCollections($group['items'], $group['mapping']);
        }

        return $sourceCollection;
    }

    private function collectRelations($sourceCollection, array $sourceProperties, array $registeredMappingOperations): array
    {
        $relations = [];
        $sourceClass = $this->determineSourceClass($sourceCollection);

        foreach ($sourceCollection as $source) {
            foreach ($registeredMappingOperations as $mappingPropertyName => $mappingOperation) {
                foreach ($sourceProperties as $sourceProperty) {
                    if ($sourceProperty->getName() === $mappingPropertyName) {
                        $collection = $sourceProperty->getValue($source);
                        if (false === is_iterable($collection) || true === $collection->isInitialized()) {
                            continue;
                        }

                        $collectionMapping = $collection->getMapping();
                        $key = sprintf('%s#%s', $collectionMapping['sourceEntity'], $collectionMapping['fieldName']);

                        if (false === isset($relations[$key])) {
                            $relations[$key] = [
                                'items' => [],
                                'mapping' => $collectionMapping,
                            ];
                        }

                        $owner = $collection->getOwner();
                        $idHash = $this->generateIdHash($sourceClass, $owner);

                        $relations[$key]['items'][$idHash] = $collection;
                    }
                }
            }
        }

        return $relations;
    }

    private function eagerLoadCollections(array $collections, array $mapping): void
    {
        $mappedBy = $mapping['mappedBy'];
        if (null === $mappedBy) {
            return;
        }

        $targetEntity = $mapping['targetEntity'];
        $class = $this->em->getClassMetadata($mapping['sourceEntity']);
        $batchSize = $this->em->getConfiguration()->getEagerFetchBatchSize();

        foreach (array_chunk($collections, $batchSize, true) as $collectionBatch) {
            $entities = array_map(static fn ($collection) => $collection->getOwner(), $collectionBatch);
            $found = $this->em->getRepository($targetEntity)->findBy([$mappedBy => $entities]);
            $targetClass = $this->em->getClassMetadata($targetEntity);
            $targetProperty = $targetClass->getReflectionProperty($mappedBy);

            foreach ($found as $targetValue) {
                $sourceEntity = $targetProperty->getValue($targetValue);
                $idHash = $this->generateIdHash($class->getName(), $sourceEntity);
                $collectionBatch[$idHash]->add($targetValue);
            }
        }

        $this->initializeCollections($collections);
    }

    private function determineSourceClass($sourceCollection): string
    {
        if (true === is_array($sourceCollection)) {
            return get_class($sourceCollection[0]);
        }

        return $sourceCollection->getTypeClass()->name;
    }

    private function initializeCollections(array $collections): void
    {
        foreach ($collections as $association) {
            $association->setInitialized(true);
            $association->takeSnapshot();
        }
    }

    private function generateIdHash(string $sourceClass, $owner): string
    {
        $id = $this->em->getClassMetadata($sourceClass)->getIdentifierValues($owner);

        return implode(' ', $id);
    }
}
