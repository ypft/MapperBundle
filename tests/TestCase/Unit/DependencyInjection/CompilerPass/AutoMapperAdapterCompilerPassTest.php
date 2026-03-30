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

    public function testProcessWithoutParameterDoesNothing(): void
    {
        $container = new ContainerBuilder();

        $compilerPass = new AutoMapperAdapterCompilerPass();
        $compilerPass->process($container);

        self::assertFalse($container->hasAlias(AutoMapperAdapterInterface::class));
    }
}
