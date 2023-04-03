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


class SubCollection extends AbstractFilter
{
    public function __construct()
    {
        $this->setId('subCollection');
        $this->setName('Subcollection');
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return is_iterable($this->getParam('filter_subcollection')) ? count($this->getParam('filter_subcollection')) > 0 : false;
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return 'glyphicon glyphicon-paperclip';
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
        // filter product collection
        $filterSubCollection = $this->getParam('filter_subcollection');
        if ($filterSubCollection) {
            $tmp = [];
            foreach ($filterSubCollection as $subCollection) {
                $tmp[] = $list->quote($subCollection);
            }
            $list->addConditionParam(sprintf('id IN(
                SELECT OCAPP.image 
                FROM `object_collection_AssetsProperty_PROD` AS OCAPP 
                WHERE OCAPP.o_id IN (
                    SELECT OBQHP.o_id FROM `object_brick_query_hardwoodAttributes_PROD` AS OBQHP
                        WHERE OBQHP.subCollection IN (%s)
                )
            )', implode(',', $tmp)), '');
        }
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return [
            'filter_subcollection' => $this->getParam('filter_subcollection'),
        ];
    }


    /**
     * The method prepares the list of options for a dropdown list of subcollections
     * 
     * @return array
     */
    //     public function getSubCollectionOptions()
    //     {
    //         $productClass = ClassDefinition::getById('PROD');
    //         $arrSubCollectionOptions = $productClass->getFieldDefinition("subCollection");
    //         $subCollections = $arrSubCollectionOptions->options;

    //         $arrSubCollection = [];

    //         foreach ($subCollections as $option) {
    //             $arrSubCollection[] = $option['value'];
    //         }
    //         return $arrSubCollection;
    //     }
    // }

    public function getSubCollectionOptions()
    {
        $ObjectBrickDefinition = Definition::getByKey('hardwoodAttributes');
        $subCollections = $ObjectBrickDefinition->layoutDefinitions->childs[0]->childs[0]->childs[1]->childs[1]->options;

        // echo ('<pre>');
        // print_r($ObjectBrickDefinition->layoutDefinitions->childs[0]->childs[0]->childs[1]);
        // exit;

        $arrSubCollection = [];

        foreach ($subCollections as $option) {
            $arrSubCollection[] = $option['value'];
        }
        return $arrSubCollection;
    }
}
