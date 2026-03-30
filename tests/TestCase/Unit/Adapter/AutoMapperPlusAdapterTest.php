<?php

declare(strict_types=1);

namespace MapperBundle\Tests\TestCase\Unit\Adapter;

use AutoMapperPlus\AutoMapperInterface;
use AutoMapperPlus\Configuration\AutoMapperConfigInterface;
use AutoMapperPlus\DataType;
use MapperBundle\Adapter\AutoMapperPlusAdapter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;

class AutoMapperPlusAdapterTest extends TestCase
{
    public function testMapCallsAutoMapperMap(): void
    {
        $autoMapper = $this->createMock(AutoMapperInterface::class);
        $extractor = $this->createMock(PropertyInfoExtractor::class);
        $config = $this->createMock(AutoMapperConfigInterface::class);

        $source = ['name' => 'test'];
        $destination = 'TargetClass';

        $config->method('hasMappingFor')->willReturn(true);
        $autoMapper->method('getConfiguration')->willReturn($config);
        $autoMapper->expects($this->once())
            ->method('map')
            ->with($source, $destination)
            ->willReturn(new \stdClass())
        ;

        $adapter = new AutoMapperPlusAdapter($autoMapper, $extractor);
        $adapter->map($source, $destination);
    }

    public function testMapToObjectCallsAutoMapperMapToObject(): void
    {
        $autoMapper = $this->createMock(AutoMapperInterface::class);
        $extractor = $this->createMock(PropertyInfoExtractor::class);
        $config = $this->createMock(AutoMapperConfigInterface::class);

        $source = ['name' => 'test'];
        $destination = new \stdClass();

        $config->method('hasMappingFor')->willReturn(true);
        $autoMapper->method('getConfiguration')->willReturn($config);
        $autoMapper->expects($this->once())
            ->method('mapToObject')
            ->with($source, $destination)
            ->willReturn($destination)
        ;

        $adapter = new AutoMapperPlusAdapter($autoMapper, $extractor);
        $result = $adapter->mapToObject($source, $destination);

        $this->assertSame($destination, $result);
    }

    public function testMapToArrayCallsAutoMapperMapWithArrayType(): void
    {
        $autoMapper = $this->createMock(AutoMapperInterface::class);
        $extractor = $this->createMock(PropertyInfoExtractor::class);

        $source = new \stdClass();
        $expected = ['name' => 'test'];

        $autoMapper->expects($this->once())
            ->method('map')
            ->with($source, DataType::ARRAY)
            ->willReturn($expected)
        ;

        $adapter = new AutoMapperPlusAdapter($autoMapper, $extractor);
        $result = $adapter->mapToArray($source);

        $this->assertSame($expected, $result);
    }

    public function testMapMultipleCallsAutoMapperMapMultiple(): void
    {
        $autoMapper = $this->createMock(AutoMapperInterface::class);
        $extractor = $this->createMock(PropertyInfoExtractor::class);
        $config = $this->createMock(AutoMapperConfigInterface::class);

        $sources = [['name' => 'test1'], ['name' => 'test2']];
        $destination = 'TargetClass';
        $expected = [new \stdClass(), new \stdClass()];

        $config->method('hasMappingFor')->willReturn(true);
        $autoMapper->method('getConfiguration')->willReturn($config);
        $autoMapper->expects($this->once())
            ->method('mapMultiple')
            ->with($sources, $destination)
            ->willReturn($expected)
        ;

        $adapter = new AutoMapperPlusAdapter($autoMapper, $extractor);
        $result = $adapter->mapMultiple($sources, $destination);

        $this->assertSame($expected, $result);
    }

    public function testGetConfigurationReturnsAutoMapperConfiguration(): void
    {
        $autoMapper = $this->createMock(AutoMapperInterface::class);
        $extractor = $this->createMock(PropertyInfoExtractor::class);
        $config = $this->createMock(AutoMapperConfigInterface::class);

        $autoMapper->method('getConfiguration')->willReturn($config);

        $adapter = new AutoMapperPlusAdapter($autoMapper, $extractor);
        $result = $adapter->getConfiguration();

        $this->assertSame($config, $result);
    }
}
