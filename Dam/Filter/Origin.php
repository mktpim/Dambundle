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

class Origin extends AbstractFilter
{
    public function __construct()
    {
        $this->setId('origin');
        $this->setName('Origin');
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return is_iterable($this->getParam('filter_origin')) ? count($this->getParam('filter_origin')) > 0 : false;
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return 'glyphicon glyphicon-globe';
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
        // filter origin
        $filterOrigin = $this->getParam('filter_origin');
        if ($filterOrigin) {
            $tmp = [];
            foreach ($filterOrigin as $origin) {
                $tmp[] = $list->quote($origin);
            }
            $list->addConditionParam(sprintf('id IN(SELECT OBQLAP.o_id FROM `object_brick_query_laminateAttributes_PROD` AS OBQLAP
            WHERE OBQLAP.origin in (%s)
    UNION
        SELECT OBQHAP.o_id FROM `object_brick_query_hardwoodAttributes_PROD` AS OBQHAP
            WHERE OBQHAP.origin in (%s)
     UNION
        SELECT OBQLVTP.o_id FROM `object_brick_query_lvtAttributes_PROD` AS OBQLVTP
            WHERE OBQLVTP.origin in (%s)
     UNION
        SELECT OBQVSAP.o_id FROM `object_brick_query_vinylSheetAttributes_PROD` AS OBQVSAP
            WHERE OBQVSAP.origin in (%s))', implode(',', $tmp), implode(',', $tmp), implode(',', $tmp), implode(',', $tmp)), '');
        }
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return [
            'filter_origin' => $this->getParam('filter_origin'),
        ];
    }

    /**
     * The method prepares the list of options for a dropdown list of origins 
     * 
     * @return array
     */
    public function getOriginOptions()
    // {
    //     $productClass = ClassDefinition::getById('PROD');
    //     $arrOriginOptions = $productClass->getFieldDefinition("origin");
    //     $origins = $arrOriginOptions->options;

    //     $arrOrigin = [];

    //     foreach ($origins as $option) {
    //         $arrOrigin[] = $option['value'];
    //     }
    //     return $arrOrigin;
    // }

    {
        $ObjectBrickDefinition = Definition::getByKey('hardwoodAttributes');
        $origins = $ObjectBrickDefinition->layoutDefinitions->childs[0]->childs[5]->childs[0]->childs[0]->childs[0]->options;

        $arrOrigin = [];

        foreach ($origins as $option) {
            $arrOrigin[] = $option['value'];
        }
        return $arrOrigin;
    }
}
