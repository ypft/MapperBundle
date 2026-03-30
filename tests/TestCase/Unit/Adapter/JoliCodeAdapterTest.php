<?php

declare(strict_types=1);

namespace MapperBundle\Tests\TestCase\Unit\Adapter;

use MapperBundle\Adapter\JoliCodeAdapter;
use PHPUnit\Framework\TestCase;

// Create a stub interface for testing when JoliCode AutoMapper is not installed
if (!interface_exists(\AutoMapper\AutoMapperInterface::class)) {
    interface AutoMapperInterfaceStub
    {
        public function map(array|object $source, string|array|object $target, array $context = []): array|object|null;

        public function mapCollection(iterable $collection, string $target, array $context = []): array;
    }
} else {
    interface AutoMapperInterfaceStub extends \AutoMapper\AutoMapperInterface
    {
    }
}

class JoliCodeAdapterTest extends TestCase
{
    public function testMapCallsAutoMapperMap(): void
    {
        $autoMapper = $this->createMock(AutoMapperInterfaceStub::class);

        $source = ['name' => 'test'];
        $destination = 'TargetClass';
        $expected = new \stdClass();

        $autoMapper->expects($this->once())
            ->method('map')
            ->with($source, $destination)
            ->willReturn($expected)
        ;

        $adapter = new JoliCodeAdapter($autoMapper);
        $result = $adapter->map($source, $destination);

        $this->assertSame($expected, $result);
    }

    public function testMapToObjectCallsAutoMapperMap(): void
    {
        $autoMapper = $this->createMock(AutoMapperInterfaceStub::class);

        $source = ['name' => 'test'];
        $destination = new \stdClass();

        $autoMapper->expects($this->once())
            ->method('map')
            ->with($source, $destination)
            ->willReturn($destination)
        ;

        $adapter = new JoliCodeAdapter($autoMapper);
        $result = $adapter->mapToObject($source, $destination);

        $this->assertSame($destination, $result);
    }

    public function testMapToArrayCallsAutoMapperMapWithArrayType(): void
    {
        $autoMapper = $this->createMock(AutoMapperInterfaceStub::class);

        $source = new \stdClass();
        $expected = ['name' => 'test'];

        $autoMapper->expects($this->once())
            ->method('map')
            ->with($source, 'array')
            ->willReturn($expected)
        ;

        $adapter = new JoliCodeAdapter($autoMapper);
        $result = $adapter->mapToArray($source);

        $this->assertSame($expected, $result);
    }

    public function testMapMultipleCallsAutoMapperMapCollection(): void
    {
        $autoMapper = $this->createMock(AutoMapperInterfaceStub::class);

        $sources = [['name' => 'test1'], ['name' => 'test2']];
        $destination = 'TargetClass';
        $expected = [new \stdClass(), new \stdClass()];

        $autoMapper->expects($this->once())
            ->method('mapCollection')
            ->with($sources, $destination)
            ->willReturn($expected)
        ;

        $adapter = new JoliCodeAdapter($autoMapper);
        $result = $adapter->mapMultiple($sources, $destination);

        $this->assertSame($expected, $result);
    }

    public function testMapMultipleReturnsEmptyArrayForEmptySources(): void
    {
        $autoMapper = $this->createMock(AutoMapperInterfaceStub::class);

        $autoMapper->expects($this->never())
            ->method('mapCollection')
        ;

        $adapter = new JoliCodeAdapter($autoMapper);
        $result = $adapter->mapMultiple([], 'TargetClass');

        $this->assertSame([], $result);
    }

    public function testGetConfigurationReturnsNull(): void
    {
        $autoMapper = $this->createMock(AutoMapperInterfaceStub::class);

        $adapter = new JoliCodeAdapter($autoMapper);
        $result = $adapter->getConfiguration();

        $this->assertNull($result);
    }
}
