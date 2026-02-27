<?php

declare(strict_types=1);

namespace MapperBundle;

use MapperBundle\DependencyInjection\CompilerPass\AutoMapperAdapterCompilerPass;
use MapperBundle\DependencyInjection\CompilerPass\EntityPreLoaderCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * MapperBundle.
 */
class MapperBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new EntityPreLoaderCompilerPass());
        $container->addCompilerPass(new AutoMapperAdapterCompilerPass());
    }
}
