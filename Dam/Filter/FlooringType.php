<?php

/**
 * Pimcore DAM
 *
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Dam\Filter;

use Pimcore\Bundle\DamBundle\Model\Asset;
// use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\DataObject\Objectbrick\Definition;

class FlooringType extends AbstractFilter
{
    public function __construct()
    {
        $this->setId('flooringType');
        $this->setName('Flooring Type');
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return is_iterable($this->getParam('filter_flooring_type')) ? count($this->getParam('filter_flooring_type')) > 0 : false;
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
        // filter color name
        $filterFlooringType = $this->getParam('filter_flooring_type');
        if ($filterFlooringType) {
            $tmp = [];
            foreach ($filterFlooringType as $flooringType) {
                $tmp[] = $list->quote($flooringType);
            }
            $list->addConditionParam(sprintf('id IN(
                SELECT OCAPP.image 
                FROM `object_collection_AssetsProperty_PROD` AS OCAPP 
                WHERE OCAPP.o_id IN (
                    SELECT OBQHAP.o_id FROM `object_brick_query_hardwoodAttributes_PROD` AS OBQHAP
                        WHERE OBQHAP.flooringType in (%s)
                    UNION
                    SELECT OBQVSAP.o_id FROM `object_brick_query_vinylSheetAttributes_PROD` AS OBQVSAP
                        WHERE OBQVSAP.flooringType in (%s)
                    UNION
                    SELECT OBQLVTP.o_id FROM `object_brick_query_lvtAttributes_PROD` AS OBQLVTP
                        WHERE OBQLVTP.flooringType in (%s)
            )

     )', implode(',', $tmp), implode(',', $tmp), implode(',', $tmp)), '');
        }
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return [
            'filter_flooring_type' => $this->getParam('filter_flooring_type'),
        ];
    }

    /**
     * The method prepares the list of options for a dropdown list of color names
     * 
     * @return array
     */

    // {
    //     $productClass = ClassDefinition::getById('PROD');
    //     $arrFlooringTypeOptions = $productClass->getFieldDefinition("flooringType");
    //     $flooringTypes = $arrFlooringTypeOptions->options;

    //     $arrFlooringType = [];

    //     foreach ($flooringTypes as $option) {
    //         $arrFlooringType[] = $option['value'];
    //     }
    //     return $arrFlooringType;
    // }
    public function getFlooringTypeOptions()
    {
        $ObjectBrickDefinition = Definition::getByKey('hardwoodAttributes');
        $flooringTypes = $ObjectBrickDefinition->layoutDefinitions->childs[0]->childs[0]->childs[2]->childs[0]->childs[0]->childs[1]->options;

        $arrFlooringType = [];

        foreach ($flooringTypes as $option) {
            $arrFlooringType[] = $option['value'];
        }
        return $arrFlooringType;
    }
}
