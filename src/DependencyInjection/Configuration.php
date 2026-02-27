<?php

declare(strict_types=1);

namespace MapperBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('mapper');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->enumNode('automapper')
                    ->values(['automapper_plus', 'jolicode'])
                    ->defaultValue('automapper_plus')
                    ->info('Choose which automapper library to use')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
