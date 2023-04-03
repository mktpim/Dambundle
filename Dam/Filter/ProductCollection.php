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

class ProductCollection extends AbstractFilter
{
    public function __construct()
    {
        $this->setId('productCollection');
        $this->setName('Product Collection');
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return is_iterable($this->getParam('filter_product_collection')) ? count($this->getParam('filter_product_collection')) > 0 : false;
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return 'glyphicon glyphicon-shopping-cart';
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
        $filterProductCollection = $this->getParam('filter_product_collection');
        if ($filterProductCollection) {
            $tmp = [];
            foreach ($filterProductCollection as $productCollection) {
                $tmp[] = $list->quote($productCollection);
            }
            $list->addConditionParam(sprintf('id IN(
                SELECT OCAPP.image 
                FROM `object_collection_AssetsProperty_PROD` AS OCAPP 
                WHERE OCAPP.o_id IN (
                    SELECT OBQLAP.o_id FROM `object_brick_query_laminateAttributes_PROD` AS OBQLAP
                        WHERE OBQLAP.collection in (%s)
                    UNION
                        SELECT OBQHAP.o_id FROM `object_brick_query_hardwoodAttributes_PROD` AS OBQHAP
                            WHERE OBQHAP.collection in (%s)
                    UNION
                        SELECT OBQLVTP.o_id FROM `object_brick_query_lvtAttributes_PROD` AS OBQLVTP
                            WHERE OBQLVTP.collection in (%s)
                    UNION
                        SELECT OBQVSAP.o_id FROM `object_brick_query_vinylSheetAttributes_PROD` AS OBQVSAP
                            WHERE OBQVSAP.collection in (%s)
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
            'filter_product_collection' => $this->getParam('filter_product_collection'),
        ];
    }

    /**
     * The method prepares the list of options for a dropdown list of product collection
     * 
     * @return array
     */
    public function getProductCollectionOptions()
    // {
    //     $productClass = ClassDefinition::getById('PROD');
    //     $arrProductCollectionptions = $productClass->getFieldDefinition("collection");
    //     $productCollections = $arrProductCollectionptions->options;

    //     $arrProductCollection = [];

    //     foreach ($productCollections as $option) {
    //         $arrProductCollection[] = $option['value'];
    //     }
    //     return $arrProductCollection;
    // }
    {
        $ObjectBrickDefinition = Definition::getByKey('laminateAttributes');
        $productCollections = $ObjectBrickDefinition->layoutDefinitions->childs[0]->childs[0]->childs[1]->childs[0]->options;

        $productCollection = [];

        foreach ($productCollections as $option) {
            $productCollection[] = $option['value'];
        }

        return $productCollection;
    }
}
