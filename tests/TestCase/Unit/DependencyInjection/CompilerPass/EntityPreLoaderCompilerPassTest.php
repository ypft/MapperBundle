<?php

declare(strict_types=1);

namespace MapperBundle\Tests\TestCase\Unit\DependencyInjection\CompilerPass;

use MapperBundle\DependencyInjection\CompilerPass\EntityPreLoaderCompilerPass;
use MapperBundle\PreLoader\ODMPreLoader;
use MapperBundle\PreLoader\ORMPreLoader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class EntityPreLoaderCompilerPassTest extends TestCase
{
    public function testSetsOrmPreloaderIfDoctrineIsAvailable(): void
    {
        $container = new ContainerBuilder();

        $container->setDefinition('doctrine', new Definition());
        $container->setDefinition('mapper.preloader', new Definition());

        (new EntityPreLoaderCompilerPass())->process($container);

        $this->assertSame(
            ORMPreLoader::class,
            $container->getDefinition('mapper.preloader')->getClass(),
        );
    }

    public function testSetsOdmPreloaderIfOnlyMongoDbIsAvailable(): void
    {
        $container = new ContainerBuilder();

        $container->setDefinition('doctrine_mongodb', new Definition());
        $container->setDefinition('mapper.preloader', new Definition());

        (new EntityPreLoaderCompilerPass())->process($container);

        $this->assertSame(
            ODMPreLoader::class,
            $container->getDefinition('mapper.preloader')->getClass(),
        );
    }

    public function testDoesNotChangeAnythingIfNeitherDoctrineNorMongoDbAreAvailable(): void
    {
        $container = new ContainerBuilder();

        $container->setDefinition('mapper.preloader', new Definition('OriginalClass'));

        (new EntityPreLoaderCompilerPass())->process($container);

        $this->assertSame(
            'OriginalClass',
            $container->getDefinition('mapper.preloader')->getClass(),
        );
    }
}
