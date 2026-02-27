<?php

declare(strict_types=1);

namespace MapperBundle\Tests\TestCase\Integration\Mapper;

use MapperBundle\Adapter\JoliCodeAdapter;
use MapperBundle\Mapper\Mapper;
use MapperBundle\PreLoader\NullPreLoader;
use MapperBundle\Tests\TestCase\Unit\Stubs\DTO\DefaultMappingOutputDto;
use PHPUnit\Framework\TestCase;

class JoliCodeMapperTest extends TestCase
{
    private Mapper $mapper;

    protected function setUp(): void
    {
        if (!class_exists(\AutoMapper\AutoMapper::class)) {
            $this->markTestSkipped('JoliCode AutoMapper is not installed');
        }

        $adapter = new JoliCodeAdapter();

        $this->mapper = new Mapper(
            $adapter,
            new NullPreLoader(),
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

    public function testConvertToArray(): void
    {
        $source = new DefaultMappingOutputDto(
            null,
            [],
            new \DateTime('2023-01-01 00:00:00'),
            new \DateTime('2023-01-01 00:00:00'),
        );

        $result = $this->mapper->convertToArray($source);

        self::assertIsArray($result);
        self::assertArrayHasKey('createdAt', $result);
        self::assertArrayHasKey('updatedAt', $result);
    }

    public function testConvertCollection(): void
    {
        $sources = [
            ['createdAt' => '2023-01-01 00:00:00', 'updatedAt' => '2023-01-01 00:00:00', 'object' => null, 'collection' => []],
            ['createdAt' => '2023-01-02 00:00:00', 'updatedAt' => '2023-01-02 00:00:00', 'object' => null, 'collection' => []],
        ];

        $result = $this->mapper->convertCollection($sources, DefaultMappingOutputDto::class);

        self::assertIsArray($result);
        self::assertCount(2, $result);
        self::assertInstanceOf(DefaultMappingOutputDto::class, $result[0]);
        self::assertInstanceOf(DefaultMappingOutputDto::class, $result[1]);
    }
}
