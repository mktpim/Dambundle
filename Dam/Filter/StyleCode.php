<?php

/**
 * Pimcore DAM
 *
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Dam\Filter;

use Pimcore\Bundle\DamBundle\Model\Asset;
use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\DataObject\Objectbrick\Definition;

class StyleCode extends AbstractFilter
{
    public function __construct()
    {
        $this->setId('styleCode');
        $this->setName('Style Code');
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return is_iterable($this->getParam('filter_style_code')) ? count($this->getParam('filter_style_code')) > 0 : false;
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
        // filter style code
        $filterStyleCode = $this->getParam('filter_style_code');
        if ($filterStyleCode) {
            $tmp = [];
            foreach ($filterStyleCode as $styleCode) {
                $tmp[] = $list->quote($styleCode);
            }
            $list->addConditionParam(sprintf('id IN(
                SELECT OCAPP.image 
                FROM `object_collection_AssetsProperty_PROD` AS OCAPP 
                WHERE OCAPP.o_id IN (
                    SELECT OBQLAP.o_id FROM `object_brick_query_laminateAttributes_PROD` AS OBQLAP
                        WHERE OBQLAP.styleCode in (%s)
                    UNION
                        SELECT OBQHAP.o_id FROM `object_brick_query_hardwoodAttributes_PROD` AS OBQHAP
                            WHERE OBQHAP.styleCode in (%s)
                    UNION
                        SELECT OBQLVTP.o_id FROM `object_brick_query_lvtAttributes_PROD` AS OBQLVTP
                            WHERE OBQLVTP.styleCode in (%s)
                    UNION
                        SELECT OBQVSAP.o_id FROM `object_brick_query_vinylSheetAttributes_PROD` AS OBQVSAP
                            WHERE OBQVSAP.styleCode in (%s)
                )
            )', implode(',', $tmp), implode(',', $tmp), implode(',', $tmp), implode(',', $tmp)), '');
        }
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return [
            'filter_style_code' => $this->getParam('filter_style_code'),
        ];
    }

    /**
     * The method prepares the list of options for a dropdown list of style code names
     * 
     * @return array
     */
    public function getStyleCodeOptions()
    {
        $productClass = Definition::getByKey('hardwoodAttributes');
        $arrStyleCodeOptions = $productClass->getFieldDefinition("styleCode");
        $styleCodes = $arrStyleCodeOptions->options;
        $productClasslaminate = Definition::getByKey('laminateAttributes');
        $lamStyleCodeOptions = $productClasslaminate->getFieldDefinition("styleCode");
        $styleCodeslam = $lamStyleCodeOptions->options;
        $results =  array_merge($styleCodes, $styleCodeslam);
        $productClasslvt = Definition::getByKey('lvtAttributes');
        $lvtStyleCodeOptions = $productClasslvt->getFieldDefinition("styleCode");
        $styleCodeslvt = $lvtStyleCodeOptions->options;
        $results = array_merge($results, $styleCodeslvt);
        $productClassvinyl = Definition::getByKey('vinylSheetAttributes');
        $vinylStyleCodeOptions = $productClassvinyl->getFieldDefinition("styleCode");
        $styleCodesvinyl = $vinylStyleCodeOptions->options;
        $results = array_merge($results, $styleCodesvinyl);
        $arrStyleCode = [];
        foreach ($results as $option) {
            if (!in_array($option['value'], $arrStyleCode)) {
                $arrStyleCode[] = $option['value'];
            }
        }
        return $arrStyleCode;
    }
}
