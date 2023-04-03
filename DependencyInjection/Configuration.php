<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('pimcore_dam');

        $rootNode->addDefaultsIfNotSet();

        $rootNode->append($this->buildFrontendNode());
        $rootNode->append($this->buildBackendNode());
        $rootNode->append($this->buildFiltersNode());
        $rootNode->append($this->buildExtensionNode());
        $rootNode->append($this->buildDownloadNode());
        $rootNode->append($this->buildAssetNode());

        return $treeBuilder;
    }

    /**
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition|\Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    private function buildFrontendNode()
    {
        $treeBuilder = new TreeBuilder();

        $frontend = $treeBuilder->root('frontend');
        $frontend
            ->addDefaultsIfNotSet()
            ->info('Settings for Share Frontend via Token Access');

        $frontend
            ->children()
                ->arrayNode('customize')
                    ->info('Options to customize Share frontend')
                    ->children()
                        ->variableNode('css')
                            ->info('Paths to custom CSS files to be injected into DAM frontend, e.g. [/static/css/custom.css, ...]')
                            ->validate()
                                ->ifTrue(function ($v) {
                                    return $v !== null && false === is_string($v) && false === is_array($v);
                                })
                                ->thenInvalid('it must either be of type null, a string or an array')
                            ->end()
                        ->end()
                        ->variableNode('js')
                            ->info('Paths to custom JS files to be injected into DAM frontend, e.g. [/static/js/custom.js, ...]')
                            ->validate()
                                ->ifTrue(function ($v) {
                                    return $v !== null && false === is_string($v) && false === is_array($v);
                                })
                                ->thenInvalid('it must either be of type null, a string or an array')
                            ->end()
                        ->end()
                        ->scalarNode('shareUrl')->info('Optional set special share URL to not use the main domain. Needs to be configured at webserver also.')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $frontend;
    }

    private function buildAssetNode() {
        $treeBuilder = new TreeBuilder();

        $assets = $treeBuilder->root('assets');
        $assets->addDefaultsIfNotSet('Custom Configure Item Classes');
        $assets->children()
            ->arrayNode('classes')
            ->prototype('scalar')
            ->end();

        return $assets;
    }
    /**
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition|\Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    private function buildBackendNode()
    {
        $treeBuilder = new TreeBuilder();

        $backend = $treeBuilder->root('backend');
        $backend
            ->addDefaultsIfNotSet()
                ->info('Settings for DAM Frontend via Pimcore user access');

        $backend
            ->children()
                ->arrayNode('languageMapping')
                    ->info('Optionally map language set in Pimcore user to language available in shared translations.')
                    ->prototype('scalar')->end()
                    ->example(['en_GB' => 'en'])
                    ->defaultValue([])
                ->end()
                ->arrayNode('ui')
                    ->info('General Settings for User Interface')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('listview')
                            ->info('Settings for list view')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('listview_metadata')
                                    ->info('Configure metadata fields to be shown in list view')
                                    ->prototype('scalar')->defaultValue([])->end()
                                ->end()
                                ->arrayNode('sort')
                                    ->info('Configure sorting options')
                                    ->prototype('array')
                                        ->children()
                                            ->scalarNode('iconClass')
                                                ->info('Icon for button')
                                                ->example('glyphicon-font')
                                            ->end()
                                            ->scalarNode('criteria')
                                                ->info('Fieldname to sort after')
                                                ->example('filename')
                                            ->end()
                                            ->booleanNode('default')
                                                ->info('Should it be default sorting')
                                                ->defaultFalse()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('sidebar')
                            ->info('Settings for sidebar of DAM Frontend')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('item')
                                    ->info('Add additional items to menu')
                                    ->defaultValue([])
                                    ->prototype('array')
                                        ->children()
                                            ->scalarNode('permission')->info('Permission to restrict the menu item to.')->end()
                                            ->scalarNode('route')->info('Route for link of menu item')->end()
                                            ->scalarNode('menuIcon')->info('CSS class for icon')->example('glyphicon glyphicon-flag')->end()
                                            ->scalarNode('menuName')->info('Tooltip text for menu item')->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('filter_sidebar')
                            ->info('Settings for filter-sidebar of DAM Frontend')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('open_by_default')
                                ->info('Display filter-sidebar by default')
                                ->defaultFalse()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('user')
                    ->info('Settings for user access to DAM Frontend.')
                    ->children()
                        ->scalarNode('guest')->info('Configure pimcore user name for public access. If not configured, public access is disabled.')->end()
                    ->end()
                ->end()

                ->append($this->buildBackendNodeMetadata())

                ->arrayNode('customize')
                    ->info('Options to customize DAM frontend appearance.')
                    ->children()
                        ->variableNode('css')
                            ->info('Paths to custom CSS files to be injected into DAM backend, e.g. [/static/css/custom.css, ...]')
                            ->validate()
                                ->ifTrue(function ($v) {
                                    return $v !== null && false === is_string($v) && false === is_array($v);
                                })
                                ->thenInvalid('it must either be of type null, a string or an array')
                            ->end()
                        ->end()
                        ->variableNode('js')
                            ->info('Paths to custom JS files to be injected into DAM backend, e.g. [/static/js/custom.js, ...]')
                            ->validate()
                                ->ifTrue(function ($v) {
                                    return $v !== null && false === is_string($v) && false === is_array($v);
                                })
                                ->thenInvalid('it must either be of type null, a string or an array')
                            ->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('upload')
                    ->info('Optional upload settings')
                    ->children()
                        ->arrayNode('metadata')
                            ->children()
                                ->arrayNode('asset')
                                    ->info('Display these metadata fields to enter data directly during upload.')
                                    ->prototype('scalar')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('collection')
                    ->addDefaultsIfNotSet()
                    ->info('Settings for collections')
                    ->children()
                        ->arrayNode('share')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('user')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->arrayNode('hide')
                                            ->defaultValue([])
                                            ->info('Exclude these users from collection sharing')
                                            ->prototype('scalar')->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $backend;
    }

    /**
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition|\Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    private function buildBackendNodeMetadata()
    {
        $treeBuilder = new TreeBuilder();
        $tree = $treeBuilder->root('metadata');

        $tree
            ->info('Configuration of metadata view in detail page.')
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('hidden')
                    ->info('Hide metadata fields in view')
                    ->prototype('scalar')->end()
                ->end()

                ->booleanNode('hideCommon')
                    ->info('Hide common tab')
                    ->defaultFalse()
                ->end()

                ->arrayNode('readonly')
                    ->info('List of metadata fields set to read only')
                    ->prototype('scalar')->end()
                ->end()

                ->arrayNode('group')
                    ->info('Configuration of tabs for showing metadata')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('title')->info('Title of tab')->end()
                            ->arrayNode('field')
                                ->info('List of fields to be shown in tab')
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('selectable')
                    ->info('Create Dropdowns to fill content of input fields. Key is field name.')
                    ->prototype('array')
                        ->info("
                               my_field_name: 
                                   plugin: 'remove_button'
                                   group: 
                                       - title: 'my title'
                                         field: 
                                           - 'my value1'
                                           - 'my value2'
                        ")
                        ->children()
                            ->scalarNode('plugin')
                                ->info('Add Plugin for selectize.js')
                                ->example('dialog_overlay')
                            ->end()
                            ->arrayNode('group')
                                ->info('Group of select values')
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('title')->isRequired()->info('Title of group')->end()
                                        ->arrayNode('field')
                                            ->info('List of values')
                                            ->prototype('scalar')->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('required')
                    ->info('Required metadata fields')
                    ->defaultValue([])
                    ->prototype('array')
                        ->children()
                            ->scalarNode('field')
                                ->info('Fieldname of required field')
                                ->example('date_can-be-used-from')
                            ->end()
                            ->scalarNode('whenFieldName')
                                ->info('Optional: required only when other field has certain value')
                                ->example('license_image')
                            ->end()
                            ->scalarNode('whenFieldNameValue')
                                ->example('1')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $tree;
    }

    /**
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition|\Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    private function buildFiltersNode()
    {
        $treeBuilder = new TreeBuilder();
        $tree = $treeBuilder->root('filters');

        $tree
            ->info('Add array of custom filters for asset listing, array key is field name')
            ->prototype('array')
                ->info("
                    my_field_name:
                        type: 'Pimcore\Bundle\DamBundle\Dam\Filter\MultiSelect'
                        icon: 'glyphicon glyphicon-flag'
                        singleColumn: true
                        options:
                            - 'value1'
                            - 'value2'
                ")
                ->children()
                    ->scalarNode('type')
                        ->isRequired()
                        ->info('Classname of filtertype, see namespace Pimcore\Bundle\DamBundle\Dam\Filter\* or custom implementations')
                    ->end()
                    ->arrayNode('options')
                        ->info('List of available select values')
                        ->prototype('scalar')->end()
                    ->end()
                    ->booleanNode('singleColumn')->info('Show filter select values in single column layout')->end()
                    ->scalarNode('icon')->info('CSS class for icon of filter')->end()
                    ->scalarNode('metadataName')->info('Name of filtered metadata field')->end()
                    ->scalarNode('object')->info('own filter object')->end()
                ->end()
            ->end()
        ;

        return $tree;
    }

    /**
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition|\Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    private function buildExtensionNode()
    {
        $treeBuilder = new TreeBuilder();
        $tree = $treeBuilder->root('extension');

        $tree
            ->info('Configure Extensions to the DAM Frontend')
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('imageEditor')
                    ->info('Enable or disable image editor for images. ')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultFalse()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('shareAcceptTerms')
                    ->addDefaultsIfNotSet()
                    ->info('Configure optional terms accept dialog before downloading assets in Share Frontend.')
                    ->children()
                        ->booleanNode('enabled')
                            ->info('Enable terms accept dialog.')
                            ->defaultFalse()
                        ->end()
                        ->scalarNode('metaDataKeyCopyrightMandatory')
                            ->info('Meta-data key for setting, if copyright acceptance should be activated by default for shares')
                            ->defaultValue('copyright_mandatory')
                        ->end()
                        ->scalarNode('metaDataKeyCopyright')
                            ->info('Meta-data key for copyright text for an asset - if set properly copyright information is copied to share comment automatically when sharing')
                            ->defaultValue('Copyright')
                        ->end()
                    ->end()
                ->end()

                ->booleanNode('passAssetsThroughController')
                    ->info(<<<INFO
                Enable thumbnail delivery in share frontend though controller and not directly with filepath. 
                This makes it possible to exclude certain IP addresses from accessing whole Pimcore, except the share frontend.
                Following urls have to be excluded:
                   - /plugin/DAM/?t=
                   - /plugin/DAM/share
                   - /plugin/DAM/static
INFO
                    )
                    ->defaultTrue()
                ->end()
            ->end()
        ;

        return $tree;
    }

    /**
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition|\Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    private function buildDownloadNode()
    {
        $treeBuilder = new TreeBuilder();
        $tree = $treeBuilder->root('download');

        $tree
            ->info('Configure download options for DAM Frontend.')
            ->children()
                ->arrayNode('image')
                    ->info('Add predefined thumbnail configurations available for download.')
                    ->prototype('scalar')->end()
                    ->defaultValue(['dam_detail'])
                ->end()
                ->arrayNode('video')
                    ->info('Add predefined thumbnail configurations available for download.')
                    ->prototype('scalar')->end()
                    ->defaultValue(['dam_detail'])
                ->end()
                ->booleanNode('onlyPresetDownload')
                    ->info('If set to true, image download is only allowed with preset thumbnail configurations, no free configuration possible.')
                    ->defaultFalse()
                ->end()
            ->end()
        ;

        return $tree;
    }
}
