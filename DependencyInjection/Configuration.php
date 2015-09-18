<?php

namespace Mykees\CommentBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('mykees_comment');

	    $rootNode
		    ->children()
		        ->scalarNode('comment_class')->isRequired()->cannotBeEmpty()->end()
		        ->scalarNode('depth')->end()
		        ->arrayNode('akismet')
		            ->children()
		                ->scalarNode('api_key')->end()
		                ->scalarNode('website')->end()
		            ->end()
		        ->end()
		        ->scalarNode('success_message')->end()
		    ->end()
	    ;

        return $treeBuilder;
    }
}
