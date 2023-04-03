<?php

/**
 * Pimcore DAM
 *
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Dam\Filter;

use Pimcore\Bundle\DamBundle\Model\Asset;
use Pimcore\Bundle\DamBundle\Dam\Facade;
use Pimcore\Db;

class MarketSku extends AbstractFilter
{
    public function __construct()
    {
        $this->setId('marketSku');
        $this->setName('Market Sku');
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->getParam('filter_market_sku') ? true : false;
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return 'glyphicon glyphicon-barcode';
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
        // filter market SKU
        $filterMarketSKU = $this->getParam('filter_market_sku');
        $filterMarketSKU = is_array($filterMarketSKU) ? $filterMarketSKU : explode(',', $filterMarketSKU);

        if ($filterMarketSKU) {
            $tmp = [];
            foreach ($filterMarketSKU as $marketsku) {
                $tmp[] = $list->quote(trim($marketsku));
            }

            $list->addConditionParam(sprintf('id IN(
                SELECT OCAPP.image 
                    FROM `object_collection_AssetsProperty_PROD` AS OCAPP 
                        WHERE OCAPP.o_id IN (
                            SELECT OP.oo_id FROM `object_PROD` AS OP WHERE OP.marketSKU IN (%s)
                        )
                )', implode(',', $tmp)), '');
        }
    }

    /**
     * The method prepares the list of options for a dropdown list of market SKUs
     * 
     * @return array
     */
    public static function getMarketSKUOptions()
    {
        $currentUser = Facade::getUser();
        $currentUserName = $currentUser->getName();

        $organizationId = '';

        if ($currentUserName == 'mannington-user') {
            $organizationId = 29; // id of mannington organization
        } elseif ($currentUserName == 'phenix-user') {
            $organizationId = 30; // id of phenix organization
        }

        $db = Db::get();
        $sql = 'SELECT `marketSKU` FROM `object_PROD` ';
        $sql .= (!empty($organizationId)) ? 'WHERE `organization__id` = ' . $organizationId : '';
        $ids = $db->fetchCol($sql);

        $items = [];

        foreach ($ids as $id) {
            if (!empty($id) || $id != '') {
                $items[] = $id;
                // ($items);
            }
        }
        return $items;
    }
    /**
     * @return array
     */
    public function getConfig()
    {
        return [
            'filter_market_sku' => $this->getParam('filter_market_sku'),
        ];
    }
}
