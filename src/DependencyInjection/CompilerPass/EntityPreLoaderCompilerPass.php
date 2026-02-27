<?php

declare(strict_types=1);

namespace MapperBundle\DependencyInjection\CompilerPass;

use MapperBundle\PreLoader\ORMPreLoader;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class EntityPreLoaderCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (true === $container->hasDefinition('doctrine')) {
            $container
                ->findDefinition('mapper.preloader')
                ->setClass(ORMPreLoader::class)
            ;

            return;
        }

        if (true === $container->hasDefinition('doctrine_mongodb')) {
            $container
                ->findDefinition('mapper.preloader')
                ->setClass(ORMPreLoader::class)
            ;
        }
    }
}
