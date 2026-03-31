<?php

declare(strict_types=1);

namespace MapperBundle\Tests\TestCase\Integration\Mapper;

use AutoMapperPlus\AutoMapper;
use MapperBundle\Adapter\AutoMapperPlusAdapter;
use MapperBundle\Mapper\Mapper;
use MapperBundle\PreLoader\NullPreLoader;
use MapperBundle\Tests\TestCase\Unit\Stubs\DTO\DefaultMappingOutputDto;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyInfo\Extractor\ConstructorExtractor;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;

class MapperTest extends TestCase
{
    private Mapper $mapper;

    protected function setUp(): void
    {
        $extractor = new PropertyInfoExtractor(
            [
                new ReflectionExtractor(),
            ],
            [
                new ConstructorExtractor([new PhpDocExtractor(), new ReflectionExtractor()]),
                new PhpDocExtractor(),
                new ReflectionExtractor(),
            ],
        );

        $adapter = new AutoMapperPlusAdapter(
            new AutoMapper(),
            $extractor,
        );

        $this->mapper = new Mapper(
            new AutoMapper(),
            $extractor,
            new NullPreLoader(),
            $adapter,
        );
    }

    public function testConvertToObjectWithDefaultMapping(): void
    {
        $source = [
            'object' => [
                'object' => [
                    'object' => null,
                    'collection' => [],
                    'createdAt' => '2023-01-01 00:00:00',
                    'updatedAt' => '2023-01-01 00:00:00',
                ],
                'collection' => [],
                'createdAt' => '2023-01-01 00:00:00',
                'updatedAt' => '2023-01-01 00:00:00',
            ],
            'collection' => [
                [
                    'object' => null,
                    'collection' => [],
                    'createdAt' => '2023-01-01 00:00:00',
                    'updatedAt' => '2023-01-01 00:00:00',
                ],
            ],
            'createdAt' => '2023-01-01 00:00:00',
            'updatedAt' => '2023-01-01 00:00:00',
        ];

        $expected = new DefaultMappingOutputDto(
            new DefaultMappingOutputDto(
                new DefaultMappingOutputDto(
                    null,
                    [],
                    new \DateTime('2023-01-01 00:00:00'),
                    new \DateTime('2023-01-01 00:00:00'),
                ),
                [],
                new \DateTime('2023-01-01 00:00:00'),
                new \DateTime('2023-01-01 00:00:00'),
            ),
            [
                new DefaultMappingOutputDto(
                    null,
                    [],
                    new \DateTime('2023-01-01 00:00:00'),
                    new \DateTime('2023-01-01 00:00:00'),
                ),
            ],
            new \DateTime('2023-01-01 00:00:00'),
            new \DateTime('2023-01-01 00:00:00'),
        );

        $actual = $this->mapper->convert($source, DefaultMappingOutputDto::class);

        self::assertEquals($expected, $actual);
    }
}
