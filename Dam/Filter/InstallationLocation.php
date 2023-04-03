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

class InstallationLocation extends AbstractFilter
{
    public function __construct()
    {
        $this->setId('installationLocation');
        $this->setName('Installation Location');
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return is_iterable($this->getParam('filter_installation_location')) ? count($this->getParam('filter_installation_location')) > 0 : false;
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return 'glyphicon glyphicon-home';
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
        // filter installation location
        $filterInstallationLocation = $this->getParam('filter_installation_location');
        if ($filterInstallationLocation) {

            $hardwood_where_sql = '';
            $laminate_where_sql = '';
            $lvt_where_sql = '';

            foreach ($filterInstallationLocation as $key => $installationLocation) {
                $formatedInstallationLocation = $list->quote('%' . $installationLocation . '%');
                if ($key == 0) {
                    $hardwood_where_sql .= ' LIKE (' . $formatedInstallationLocation . ')';
                    $laminate_where_sql .= ' LIKE (' . $formatedInstallationLocation . ')';
                    $lvt_where_sql .= ' LIKE (' . $formatedInstallationLocation . ')';
                } else {
                    $hardwood_where_sql .= ' OR OBQHAP.installationLocation LIKE (' . $formatedInstallationLocation . ')';
                    $laminate_where_sql .= ' OR OBQLAP.installationLocation LIKE (' . $formatedInstallationLocation . ')';
                    $lvt_where_sql .= ' OR OBQLVTAP.installationLocation LIKE (' . $formatedInstallationLocation . ')';
                }
            }

            $sql = 'id IN (
                SELECT OCAPP.image FROM `object_collection_AssetsProperty_PROD` AS OCAPP
                    WHERE OCAPP.o_id IN (
                        SELECT OBQHAP.o_id FROM `object_brick_query_hardwoodAttributes_PROD` AS OBQHAP
                            WHERE OBQHAP.installationLocation' . $hardwood_where_sql . '
                        UNION
                        SELECT OBQLAP.o_id FROM `object_brick_query_laminateAttributes_PROD` AS OBQLAP
                            WHERE OBQLAP.installationLocation' . $laminate_where_sql . '
                        UNION
                        SELECT OBQLVTAP.o_id FROM `object_brick_query_lvtAttributes_PROD` AS OBQLVTAP
                            WHERE OBQLVTAP.installationLocation' . $lvt_where_sql . '
                    )    
                )';
            $list->addConditionParam($sql);
        }
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return [
            'filter_installation_location' => $this->getParam('filter_installation_location'),
        ];
    }

    /**
     * The method prepares the list of options for a dropdown list of installation location
     * 
     * @return array
     */
    //     public function getInstallationLocationOptions()
    //     {
    //         $productClass = ClassDefinition::getById('PROD');
    //         $arrInstallationLocationOptions = $productClass->getFieldDefinition("installationLocation");
    //         $installationLocations = $arrInstallationLocationOptions->options;

    //         $arrInstallationLocation = [];

    //         foreach ($installationLocations as $option) {
    //             $arrInstallationLocation[] = $option['value'];
    //         }
    //         return $arrInstallationLocation;
    //     }
    // }

    public function getInstallationLocationOptions()
    {
        $ObjectBrickDefinition = Definition::getByKey('hardwoodAttributes');
        $installationLocations = $ObjectBrickDefinition->layoutDefinitions->childs[0]->childs[9]->childs[0]->childs[6]->childs[7]->options;

        $arrInstallationLocation = [];

        foreach ($installationLocations as $option) {
            $arrInstallationLocation[] = $option['value'];
        }
        return $arrInstallationLocation;
    }
}
