<?php

namespace Ihsan\SimpleCrudBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ihsan_simple_crud');

        $rootNode
            ->children()
                ->integerNode('per_page')
                    ->defaultValue(10)
                ->end()
                ->scalarNode('identifier')
                    ->defaultValue('id')
                ->end()
                ->scalarNode('menu')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('filter')
                    ->defaultValue('name')
                ->end()
                ->arrayNode('grid_action')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('show')->defaultTrue()->end()
                        ->booleanNode('edit')->defaultTrue()->end()
                        ->booleanNode('delete')->defaultTrue()->end()
                    ->end()
                ->end()
                ->arrayNode('view')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('dashboard')
                            ->defaultValue('IhsanSimpleCrudBundle:Index:index.html.twig')
                        ->end()
                        ->scalarNode('form')
                            ->defaultValue('IhsanSimpleCrudBundle:Crud:new.html.twig')
                        ->end()
                        ->scalarNode('form_theme')
                            ->defaultValue('IhsanSimpleCrudBundle:Form:fields.html.twig')
                        ->end()
                        ->scalarNode('show')
                            ->defaultValue('IhsanSimpleCrudBundle:Crud:show.html.twig')
                        ->end()
                        ->scalarNode('grid')
                            ->defaultValue('IhsanSimpleCrudBundle:Crud:list.html.twig')
                        ->end()
                        ->scalarNode('pagination')
                            ->defaultValue('IhsanSimpleCrudBundle:Layout:pagination.html.twig')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}