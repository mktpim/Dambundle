<?php

/**
 * Pimcore DAM
 *
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Dam\Filter;

use Pimcore\Bundle\DamBundle\Model\Asset;
use Pimcore\Model\DataObject\Fieldcollection\Definition;

class RoomScene extends AbstractFilter
{
    public function __construct()
    {
        $this->setId('roomscene');
        $this->setName('RoomScene');
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return is_iterable($this->getParam('filter_roomscene')) ? count($this->getParam('filter_roomscene')) > 0 : false;
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
        $filterRS = $this->getParam('filter_roomscene');
        if ($filterRS) {
            $tmp = [];
            foreach ($filterRS as $roomscene) {
                $tmp[] = $list->quote($roomscene);
            }
            $list->addConditionParam(sprintf('id IN(
                SELECT OCAPP.image
                FROM `object_collection_AssetsProperty_PROD` AS OCAPP 
                WHERE OCAPP.roomSceneFilters IN (%s)
            )', implode(',', $tmp)), '');
        }
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return [
            'filter_roomscene' => $this->getParam('filter_roomscene'),
        ];
    }

    /**
     * The method prepares the list of options for a dropdown list of image properties
     * 
     * @return array
     */
    public function getProperty1Options()
    {
        $fieldCollectionDefinition = Definition::getByKey('AssetsProperty');
        $properties1 = $fieldCollectionDefinition->layoutDefinitions->childs[0]->childs[2]->options;

        $arrProperty1 = [];

        foreach ($properties1 as $option) {
            $arrProperty1[] = $option['value'];
        }
        return $arrProperty1;
    }
}
