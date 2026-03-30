<?php

declare(strict_types=1);

namespace MapperBundle\Tests\TestCase\Unit\DependencyInjection;

use MapperBundle\DependencyInjection\MapperExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MapperExtensionTest extends TestCase
{
    public function testServicesYamlIsLoaded(): void
    {
        $container = new ContainerBuilder();

        $extension = new MapperExtension();
        $extension->load([], $container);

        $this->assertTrue($container->has('mapper.preloader'));
        $this->assertSame(
            'MapperBundle\PreLoader\NullPreLoader',
            $container->getDefinition('mapper.preloader')->getClass(),
        );
    }

    public function testDefaultAutomapperParameter(): void
    {
        $container = new ContainerBuilder();
        $extension = new MapperExtension();
        $extension->load([], $container);

        $this->assertTrue($container->hasParameter('mapper.automapper'));
        $this->assertSame('automapper_plus', $container->getParameter('mapper.automapper'));
    }

    public function testJolicodeAutomapperParameter(): void
    {
        $container = new ContainerBuilder();
        $extension = new MapperExtension();
        $extension->load([['automapper' => 'jolicode']], $container);

        $this->assertTrue($container->hasParameter('mapper.automapper'));
        $this->assertSame('jolicode', $container->getParameter('mapper.automapper'));
    }
}
