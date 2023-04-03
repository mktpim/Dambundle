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

class ColorName extends AbstractFilter
{
    public function __construct()
    {
        $this->setId('colorName');
        $this->setName('Color Name');
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->getParam('filter_color_name') ? true : false;
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return 'glyphicon glyphicon-pencil';
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
        $filterColorName = $this->getParam('filter_color_name');
        $filterColorName = is_array($filterColorName) ? $filterColorName : explode(',', $filterColorName);

        if ($filterColorName) {
            $tmp = [];
            foreach ($filterColorName as $colorName) {
                $tmp[] = $list->quote(trim($colorName));
            }

            $list->addConditionParam(sprintf('id IN(
                SELECT OCAPP.image 
                    FROM `object_collection_AssetsProperty_PROD` AS OCAPP 
                        WHERE OCAPP.o_id IN (
                            SELECT OP.oo_id FROM `object_PROD` AS OP WHERE OP.colorName IN (%s)
                        )
                )', implode(',', $tmp)), '');
        }
    }

    /**
     * The method prepares the list of options for a dropdown list of market SKUs
     * 
     * @return array
     */
    public static function getColorNameOptions()
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
        $sql = 'SELECT DISTINCT `colorName` FROM `object_PROD` ';
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
            'filter_color_name' => $this->getParam('filter_color_name'),
        ];
    }
}
