<?php

declare(strict_types=1);

namespace MapperBundle\Tests\TestCase\Unit\DependencyInjection\CompilerPass;

use MapperBundle\Adapter\AutoMapperAdapterInterface;
use MapperBundle\Adapter\AutoMapperPlusAdapter;
use MapperBundle\DependencyInjection\CompilerPass\AutoMapperAdapterCompilerPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AutoMapperAdapterCompilerPassTest extends TestCase
{
    public function testProcessWithAutoMapperPlus(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('mapper.automapper', 'automapper_plus');

        $compilerPass = new AutoMapperAdapterCompilerPass();
        $compilerPass->process($container);

        $this->assertTrue($container->hasAlias(AutoMapperAdapterInterface::class));
        $alias = $container->getAlias(AutoMapperAdapterInterface::class);
        $this->assertSame(AutoMapperPlusAdapter::class, (string) $alias);
    }

    public function testProcessWithJolicodeThrowsExceptionWhenNotInstalled(): void
    {
        // Skip this test if JoliCode AutoMapper is actually installed
        if (interface_exists(\AutoMapper\AutoMapperInterface::class)) {
            $this->markTestSkipped('JoliCode AutoMapper is installed');
        }

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('JoliCode AutoMapper is not installed');

        $container = new ContainerBuilder();
        $container->setParameter('mapper.automapper', 'jolicode');

        $compilerPass = new AutoMapperAdapterCompilerPass();
        $compilerPass->process($container);
    }

    public function testProcessWithoutParameterDoesNothing(): void
    {
        $container = new ContainerBuilder();

        $compilerPass = new AutoMapperAdapterCompilerPass();
        $compilerPass->process($container);

        // Should not throw any exception
        $this->assertTrue(true);
    }
}
