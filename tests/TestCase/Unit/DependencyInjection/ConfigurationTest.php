<?php

declare(strict_types=1);

namespace MapperBundle\Tests\TestCase\Unit\DependencyInjection;

use MapperBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
{
    public function testDefaultConfiguration(): void
    {
        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, []);

        $this->assertSame('automapper_plus', $config['automapper']);
    }

    public function testAutomapperPlusConfiguration(): void
    {
        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, [
            ['automapper' => 'automapper_plus'],
        ]);

        $this->assertSame('automapper_plus', $config['automapper']);
    }

    public function testJolicodeConfiguration(): void
    {
        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, [
            ['automapper' => 'jolicode'],
        ]);

        $this->assertSame('jolicode', $config['automapper']);
    }

    public function testInvalidConfigurationThrowsException(): void
    {
        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);

        $processor = new Processor();
        $configuration = new Configuration();
        $processor->processConfiguration($configuration, [
            ['automapper' => 'invalid'],
        ]);
    }
}
