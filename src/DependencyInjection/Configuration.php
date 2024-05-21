<?php

declare(strict_types=1);

namespace WEM\UtilsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('wem_contao_encryption');
        $treeBuilder->getRootNode()
            ->children()
            ->scalarNode('encryption_key')
            ->cannotBeEmpty()
            ->defaultValue('%kernel.secret%')
            ->end()
            ->scalarNode('truncate_encryption_key')
            ->defaultTrue()
            ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}