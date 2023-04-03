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

class Pattern extends AbstractFilter
{
    public function __construct()
    {
        $this->setId('pattern');
        $this->setName('Pattern');
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return is_iterable($this->getParam('filter_pattern')) ? count($this->getParam('filter_pattern')) > 0 : false;
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return 'glyphicon glyphicon-retweet';
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
        // filter pattern
        $filterPattern = $this->getParam('filter_pattern');
        if ($filterPattern) {
            $tmp = [];
            foreach ($filterPattern as $pattern) {
                $tmp[] = $list->quote($pattern);
            }
            $list->addConditionParam(sprintf('id IN(
                SELECT OCAPP.image 
                FROM `object_collection_AssetsProperty_PROD` AS OCAPP 
                WHERE OCAPP.o_id IN (
                    SELECT OBQLAP.o_id FROM `object_brick_query_laminateAttributes_PROD` AS OBQLAP
                    WHERE OBQLAP.pattern in (%s)
                    UNION
                    SELECT OBQVSAP.o_id FROM `object_brick_query_vinylSheetAttributes_PROD` AS OBQVSAP
                        WHERE OBQVSAP.pattern in (%s)
                    UNION
                    SELECT OBQLVTP.o_id FROM `object_brick_query_lvtAttributes_PROD` AS OBQLVTP
                        WHERE OBQLVTP.pattern in (%s)
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
            'filter_pattern' => $this->getParam('filter_pattern'),
        ];
    }

    /**
     * The method prepares the list of options for a dropdown list of patterns 
     * 
     * @return array
     */
    public function getPatternOptions()
    // {
    //     $productClass = ClassDefinition::getById('PROD');
    //     $arrPatternOptions = $productClass->getFieldDefinition("pattern");
    //     $patterns = $arrPatternOptions->options;

    //     $arrPattern = [];

    //     foreach ($patterns as $option) {
    //         $arrPattern[] = $option['value'];
    //     }
    //     return $arrPattern;
    // }
    {
        $ObjectBrickDefinition = Definition::getByKey('laminateAttributes');
        $patterns = $ObjectBrickDefinition->layoutDefinitions->childs[0]->childs[0]->childs[2]->childs[0]->childs[0]->childs[4]->options;

        $arrPattern = [];

        foreach ($patterns as $option) {
            $arrPattern[] = $option['value'];
        }
        return $arrPattern;
    }
}
