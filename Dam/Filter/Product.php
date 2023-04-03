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

class Product extends AbstractFilter
{
    public function __construct()
    {
        $this->setId('product');
        $this->setName('Product');
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return is_iterable($this->getParam('filter_product')) ? count($this->getParam('filter_product')) > 0 : false;
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return 'glyphicon glyphicon-book';
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
        // filter product
        $filterProduct = $this->getParam('filter_product');
        if ($filterProduct) {

            // hardwood, lvt, laminate
            // get all object ids of products, which has not empty specific objectBrick
            $productObjectIds = [];

            foreach ($filterProduct as $productType) {

                $products = \Pimcore\Model\DataObject\Product::getList(["unpublished" => true]);
                $products->load();

                foreach ($products as $product) {

                    $productId = NULL;

                    switch ($productType) {
                        case 'hardwood':
                            $hardwoodObjecBrick = $product->getProductInfo()->getHardwoodAttributes();
                            $productId = (!empty($hardwoodObjecBrick)) ? $product->getId() : NULL;
                            if ($productId) {
                                $productObjectIds[] = $list->quote($productId);
                            }
                            break;
                        case 'laminate':
                            $laminateObjecBrick = $product->getProductInfo()->getLaminateAttributes();
                            $productId = (!empty($laminateObjecBrick)) ? $product->getId() : NULL;
                            if ($productId) {
                                $productObjectIds[] = $list->quote($productId);
                            }
                            break;
                        case 'lvt':
                            $lvtObjecBrick = $product->getProductInfo()->getLvtAttributes();
                            $productId = (!empty($lvtObjecBrick)) ? $product->getId() : NULL;
                            if ($productId) {
                                $productObjectIds[] = $list->quote($productId);
                            }
                            break;
                        case 'vinylsheet':
                            $vinylSheetObjecBrick = $product->getProductInfo()->getVinylSheetAttributes();
                            $productId = (!empty($vinylSheetObjecBrick)) ? $product->getId() : NULL;
                            if ($productId) {
                                $productObjectIds[] = $list->quote($productId);
                            }
                            break;
                        case 'carpetbroadloom':
                            $carpetBroadloomObjecBrick = $product->getProductInfo()->getCarpetBroadloomAttributes();
                            $productId = (!empty($carpetBroadloomObjecBrick)) ? $product->getId() : NULL;
                            if ($productId) {
                                $productObjectIds[] = $list->quote($productId);
                            }
                            break;
                        case 'carpettile':
                            $carpetTileObjecBrick = $product->getProductInfo()->getCarpetTileAttributes();
                            $productId = (!empty($carpetTileObjecBrick)) ? $product->getId() : NULL;
                            if ($productId) {
                                $productObjectIds[] = $list->quote($productId);
                            }
                            break;
                    }
                }
            }
            // $list->addConditionParam(sprintf('id IN (%s)', implode(',', $productObjectIds)), '');
            $list->addConditionParam(sprintf('id IN(
                SELECT OCAPP.image 
                    FROM `object_collection_AssetsProperty_PROD` AS OCAPP 
                        WHERE OCAPP.o_id IN (%s)
                )', implode(',', $productObjectIds)), '');
        }
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return [
            'filter_product' => $this->getParam('filter_product'),
        ];
    }

    /**
     * The method prepares the list of options for a dropdown list of products
     * 
     * @return array
     */
    public function getProductOptions()
    {

        $products = array("Hardwood", "Laminate", "LVT", "vinylSheet", "CarpetBroadLoom", "CarpetTile");

        $arrProduct = [];

        foreach ($products as $option) {
            $arrProduct[] = $option;
        }
        return $arrProduct;
    }
}
