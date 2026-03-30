<?php

declare(strict_types=1);

namespace MapperBundle\DependencyInjection\CompilerPass;

use MapperBundle\Adapter\AutoMapperAdapterInterface;
use MapperBundle\Adapter\AutoMapperPlusAdapter;
use MapperBundle\Adapter\JoliCodeAdapter;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AutoMapperAdapterCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasParameter('mapper.automapper')) {
            return;
        }

        $automapperType = $container->getParameter('mapper.automapper');

        if ('jolicode' === $automapperType) {
            $this->registerJoliCodeAdapter($container);
        } else {
            $this->registerAutoMapperPlusAdapter($container);
        }
    }

    private function registerJoliCodeAdapter(ContainerBuilder $container): void
    {
        if (!interface_exists(\AutoMapper\AutoMapperInterface::class)) {
            throw new \RuntimeException('JoliCode AutoMapper is not installed. Please run: composer require jolicode/automapper');
        }

        $container->register(JoliCodeAdapter::class, JoliCodeAdapter::class)
            ->setArguments([new Reference(\AutoMapper\AutoMapperInterface::class)])
            ->setPublic(true)
        ;

        $container->setAlias(AutoMapperAdapterInterface::class, JoliCodeAdapter::class)
            ->setPublic(true)
        ;
    }

    private function registerAutoMapperPlusAdapter(ContainerBuilder $container): void
    {
        if (!interface_exists(\AutoMapperPlus\AutoMapperInterface::class)) {
            throw new \RuntimeException('AutoMapperPlus is not installed. Please run: composer require mark-gerarts/auto-mapper-plus');
        }

        // AutoMapperPlusAdapter is already registered in services.yaml
        // Just ensure the alias points to it
        $container->setAlias(AutoMapperAdapterInterface::class, AutoMapperPlusAdapter::class)
            ->setPublic(true)
        ;
    }
}
