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

class ConsumerFriendlyName extends AbstractFilter
{
    public function __construct()
    {
        $this->setId('consumerFriendlyName');
        $this->setName('Consumer Friendly Name');
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->getParam('filter_consumer_friendly_name') ? true : false;
        // return true;
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return 'glyphicon glyphicon-bookmark';
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
        // filter consumer friendly name
        $filterConsumerFriendlyName = $this->getParam('filter_consumer_friendly_name');
        $filterConsumerFriendlyName = is_array($filterConsumerFriendlyName) ? $filterConsumerFriendlyName : explode(',', $filterConsumerFriendlyName);

        if ($filterConsumerFriendlyName) {
            $tmp = [];
            foreach ($filterConsumerFriendlyName as $consumerFriendlyName) {
                $tmp[] = $list->quote(trim($consumerFriendlyName));
            }

            $list->addConditionParam(sprintf('id IN(
                SELECT OCAPP.image 
                    FROM `object_collection_AssetsProperty_PROD` AS OCAPP 
                        WHERE OCAPP.o_id IN (
                            SELECT OP.oo_id FROM `object_PROD` AS OP WHERE OP.consumerFriendlyName IN (%s)
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
            'filter_consumer_friendly_name' => $this->getParam('filter_consumer_friendly_name'),
        ];
    }

    /**
     * The method prepares the list of options for a dropdown list of consumer friendly name
     * 
     * @return array
     */
    public static function getConsumerFriendlyNameOptions()
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
        $sql = 'SELECT `consumerFriendlyName` FROM `object_PROD` ';
        $sql .= (!empty($organizationId)) ? 'WHERE `organization__id` = ' . $organizationId : '';
        $ids = $db->fetchCol($sql);

        $items = [];

        foreach ($ids as $id) {
            if (!empty($id) || $id != '') {
                $items[] = $id;
            }
        }
        return $items;
    }
}
