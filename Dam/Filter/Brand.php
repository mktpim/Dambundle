<?php

/**
 * Pimcore DAM
 *
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Dam\Filter;

use Pimcore\Bundle\DamBundle\Model\Asset;
use Pimcore\Model\DataObject\Brand as DataObjectBrand;

class Brand extends AbstractFilter
{
    public function __construct()
    {
        $this->setId('brand');
        $this->setName('Brand');
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return is_iterable($this->getParam('filter_brand')) ? count($this->getParam('filter_brand')) > 0 : false;
        // return true;
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return 'glyphicon glyphicon-th-large';
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
        // filter brand
        $filterBrand = $this->getParam('filter_brand');
        if ($filterBrand) {
            $tmp = [];
            foreach ($filterBrand as $brand) {
                $tmp[] = $list->quote($brand);
            }

            // echo ('<pre>');
            // print_r($tmp);
            // exit;

            $list->addConditionParam(sprintf('id IN(
                    SELECT OCAPP.image 
                        FROM `object_collection_AssetsProperty_PROD` AS OCAPP 
                        WHERE OCAPP.o_id IN (
                            SELECT OBQLVTP.o_id FROM `object_brick_query_lvtAttributes_PROD` AS OBQLVTP
                                WHERE OBQLVTP.brand__id in (%s)
                            UNION
                            SELECT OBQCBP.o_id FROM `object_brick_query_carpetBroadloomAttributes_PROD` AS OBQCBP
                                WHERE OBQCBP.brand__id in (%s)
                            UNION
                            SELECT OBQCTP.o_id FROM `object_brick_query_carpetTileAttributes_PROD` AS OBQCTP
                                WHERE OBQCTP.brand__id in (%s)
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
            'filter_brand' => $this->getParam('filter_brand'),
        ];
    }

    /**
     * The method prepares the list of options for a dropdown list of manufacturers
     * 
     * @return array
     */
    public function getBrandOptions()
    {
        $manufactures = new DataObjectBrand\Listing();
        $manufactures->load();

        $arrBrand = [];

        foreach ($manufactures as $brand) {
            $arrBrand[$brand->getId()] = $brand->getBrandTitle();
        }
        return $arrBrand;
    }
}
