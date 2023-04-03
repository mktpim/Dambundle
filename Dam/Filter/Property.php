<?php

/**
 * Pimcore DAM
 *
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Dam\Filter;

use Pimcore\Bundle\DamBundle\Model\Asset;
use Pimcore\Model\DataObject\Fieldcollection\Definition;

class Property extends AbstractFilter
{
    public function __construct()
    {
        $this->setId('property');
        $this->setName('Property');
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return is_iterable($this->getParam('filter_property')) ? count($this->getParam('filter_property')) > 0 : false;
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return 'glyphicon glyphicon-file';
    }

    /**
     * @return bool
     */
    public function hasFrontend()
    {
        return true;
    }

    /**
     * @param Asset\Listing $list
     *
     * @return void
     */
    public function apply(Asset\Listing $list)
    {
        // filter property
        $filterProperty = $this->getParam('filter_property');
        if ($filterProperty) {
            $tmp = [];
            foreach ($filterProperty as $property) {
                $tmp[] = $list->quote($property);
            }
            $list->addConditionParam(sprintf('id IN(
                SELECT OCAPP.image
                FROM `object_collection_AssetsProperty_PROD` AS OCAPP 
                WHERE OCAPP.imageProperty IN (%s)
            )', implode(',', $tmp)), '');
        }
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return [
            'filter_property' => $this->getParam('filter_property'),
        ];
    }

    /**
     * The method prepares the list of options for a dropdown list of image properties
     * 
     * @return array
     */
    public function getPropertyOptions()
    {
        $fieldCollectionDefinition = Definition::getByKey('AssetsProperty');
        $properties = $fieldCollectionDefinition->layoutDefinitions->childs[0]->childs[1]->options;

        $arrProperty = [];

        foreach ($properties as $option) {
            $arrProperty[] = $option['value'];
        }
        return $arrProperty;
    }
}
