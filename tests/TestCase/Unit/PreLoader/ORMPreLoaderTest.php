<?php

declare(strict_types=1);

namespace MapperBundle\Tests\TestCase\Unit\PreLoader;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use MapperBundle\PreLoader\ORMPreLoader;
use MapperBundle\Tests\TestCase\Unit\Stubs\Entity\SourceEntity;
use MapperBundle\Tests\TestCase\Unit\Stubs\Entity\TargetEntity;
use MapperBundle\Tests\TestCase\Unit\Stubs\FakePersistentCollection;
use MapperBundle\Tests\TestCase\Unit\Stubs\FakePersistentCollectionWithNullMappedBy;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ORMPreLoaderTest extends TestCase
{
    private ORMPreLoader $preLoader;
    private EntityManagerInterface|MockObject $em;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);

        $this->preLoader = new ORMPreLoader($this->em);
    }

    public function testPreLoadWithEmptyCollection(): void
    {
        $result = $this->preLoader->preLoad([], []);

        $this->assertSame([], $result);
    }

    public function testPreloadEagerLoadsTargetEntitiesToCollection(): void
    {
        $sourceEntity = new SourceEntity();
        $this->mockEntityManager($sourceEntity);
        $sourceEntity->related = new FakePersistentCollection($sourceEntity);

        $sourceCollection = [$sourceEntity];
        $registered = ['related' => true];

        $this->preLoader->preLoad($sourceCollection, $registered);

        $this->assertCount(2, $sourceEntity->related->values);
        $this->assertTrue($sourceEntity->related->wasInitialized);
        $this->assertTrue($sourceEntity->related->snapshotTaken);
    }

    public function testPreloadSkipsWhenMappedByIsNull(): void
    {
        $sourceEntity = new SourceEntity();
        $sourceEntity->related = new FakePersistentCollectionWithNullMappedBy($sourceEntity);

        $this->mockEntityManager($sourceEntity);

        $sourceCollection = [$sourceEntity];
        $registered = ['related' => true];

        $this->preLoader->preLoad($sourceCollection, $registered);

        $this->assertEmpty($sourceEntity->related->values);
        $this->assertFalse($sourceEntity->related->wasInitialized);
        $this->assertFalse($sourceEntity->related->snapshotTaken);
    }

    private function mockEntityManager(SourceEntity $entity)
    {
        $classMetadataMock = $this->createMock(ClassMetadata::class);
        $classMetadataMock
            ->method('getName')
            ->willReturn(SourceEntity::class)
        ;
        $classMetadataMock
            ->method('getIdentifierValues')
            ->willReturnCallback(static fn ($entity) => ['id' => $entity->id])
        ;
        $classMetadataMock
            ->method('getReflectionProperty')
            ->willReturn(new \ReflectionProperty(TargetEntity::class, 'owner'))
        ;
        $this->em->method('getClassMetadata')->willReturn($classMetadataMock);

        $repositoryMock = $this->createMock(EntityRepository::class);
        $repositoryMock
            ->method('findBy')
            ->willReturn([new TargetEntity($entity), new TargetEntity($entity)])
        ;
        $this->em->method('getRepository')->willReturnCallback(static function ($class) use ($repositoryMock) {
            if (TargetEntity::class === $class) {
                return $repositoryMock;
            }

            throw new \LogicException("Unknown repository for $class");
        });

        $configMock = $this->createMock(Configuration::class);
        $configMock
            ->method('getEagerFetchBatchSize')
            ->willReturn(10)
        ;
        $this->em->method('getConfiguration')->willReturn($configMock);
    }
}
