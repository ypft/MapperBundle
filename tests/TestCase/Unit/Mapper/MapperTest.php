<?php

declare(strict_types=1);

namespace MapperBundle\Tests\TestCase\Unit\Mapper;

use AutoMapperPlus\Configuration\MappingInterface;
use AutoMapperPlus\DataType;
use AutoMapperPlus\Exception\UnregisteredMappingException;
use MapperBundle\Adapter\AutoMapperAdapterInterface;
use MapperBundle\Configuration\AutoMapperConfig;
use MapperBundle\Mapper\Mapper;
use MapperBundle\PreLoader\PreloaderInterface;
use MapperBundle\Tests\TestCase\Unit\Stubs\DTO\ExampleDto;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MapperTest extends TestCase
{
    private Mapper $mapper;
    private AutoMapperAdapterInterface|MockObject $adapter;
    private PreloaderInterface|MockObject $preLoader;

    protected function setUp(): void
    {
        $this->adapter = $this->createMock(AutoMapperAdapterInterface::class);
        $this->preLoader = $this->createMock(PreloaderInterface::class);

        $this->mapper = new Mapper(
            $this->adapter,
            $this->preLoader,
        );
    }

    public function testConvertToObject(): void
    {
        $source = ['sourceProperty' => 'value'];
        $destination = new \stdClass();

        $this->adapter
            ->expects($this->once())
            ->method('mapToObject')
            ->with($source, $destination)
            ->willReturn($destination)
        ;

        $result = $this->mapper->convertToObject($source, $destination);

        $this->assertSame($destination, $result);
    }

    public function testConvertToArray(): void
    {
        $source = new \stdClass();

        $this->adapter
            ->expects($this->once())
            ->method('mapToArray')
            ->with($source)
            ->willReturn(['key' => 'value'])
        ;

        $result = $this->mapper->convertToArray($source);

        $this->assertSame(['key' => 'value'], $result);
    }

    public function testConvertCollectionWithoutPreLoader(): void
    {
        $sources = [new \stdClass(), new \stdClass()];
        $destination = DataType::ARRAY;
        $mapped = ['result1', 'result2'];

        $config = $this->createMock(AutoMapperConfig::class);
        $config->method('usePreLoad')->willReturn(false);

        $this->adapter
            ->method('getConfiguration')
            ->willReturn($config)
        ;

        $this->adapter
            ->expects($this->once())
            ->method('mapMultiple')
            ->with($sources, $destination)
            ->willReturn($mapped)
        ;

        $result = $this->mapper->convertCollection($sources, $destination);

        $this->assertSame($mapped, $result);
    }

    public function testConvertCollectionWithPreLoader(): void
    {
        $sources = [new \stdClass()];
        $destination = DataType::ARRAY;
        $mapped = ['preloadedResult'];

        $config = $this->createMock(AutoMapperConfig::class);
        $config->method('usePreLoad')->willReturn(true);

        $mapping = $this->createMock(MappingInterface::class);
        $mapping->method('getRegisteredMappingOperations')->willReturn([]);

        $config->method('getMappingFor')->willReturn($mapping);

        $this->adapter->method('getConfiguration')->willReturn($config);

        $this->preLoader
            ->expects($this->once())
            ->method('preLoad')
            ->with($sources, [])
            ->willReturn($sources)
        ;

        $this->adapter
            ->expects($this->once())
            ->method('mapMultiple')
            ->with($sources, $destination)
            ->willReturn($mapped)
        ;

        $result = $this->mapper->convertCollection($sources, $destination);

        $this->assertSame($mapped, $result);
    }

    public function testConvertTriggersCreateSchemaForMapping(): void
    {
        $source = ['createdAt' => '2024-01-01T00:00:00'];
        $destinationClass = ExampleDto::class;

        $this->adapter
            ->expects($this->once())
            ->method('map')
            ->with($source, $destinationClass)
            ->willReturn(new ExampleDto())
        ;

        $result = $this->mapper->convert($source, $destinationClass);
        $this->assertInstanceOf($destinationClass, $result);
    }

    public function testConvertThrowsUnregisteredMappingException(): void
    {
        $this->expectException(UnregisteredMappingException::class);

        $source = new \stdClass();
        $destination = DataType::ARRAY;

        $this->adapter
            ->expects($this->once())
            ->method('map')
            ->willThrowException(new UnregisteredMappingException('Mapping not found'))
        ;

        $this->mapper->convert($source, $destination);
    }

    public function testConvertCollectionWithEmptySource(): void
    {
        $result = $this->mapper->convertCollection([], DataType::ARRAY);

        $this->assertSame([], $result);
    }

    public function testConvertCollectionWithPreLoaderReturnsEmptyIfEmptySource(): void
    {
        $result = $this->mapper->convertCollectionWithPreLoader([], DataType::ARRAY);

        $this->assertSame([], $result);
    }
}
