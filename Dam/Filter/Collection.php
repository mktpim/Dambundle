<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Dam\Filter;

use Pimcore\Bundle\DamBundle\Model\Asset;

class Collection extends AbstractFilter
{
    public function __construct()
    {
        $this->setId('collection');
        $this->setName('Collection');
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->getParam('filter_collection') ? true : false;
    }

    /**
     * @return bool
     */
    public function hasFrontend()
    {
        return true;
    }

    public function getIcon()
    {
        return 'glyphicon glyphicon-paperclip';
    }

    /**
     * @param Asset\Listing $list
     *
     * @return void
     */
    public function apply(Asset\Listing $list)
    {
        // filter collection
        $collectionIds = $this->getParam('filter_collection');
        $collectionIds = is_array($collectionIds) ? $collectionIds : explode(',', $collectionIds);

        if ($collectionIds) {
            $tmp = [];
            foreach ($collectionIds as $id) {
                $collection = \Pimcore\Bundle\DamBundle\Model\Collection::getById($id);
                if ($collection) {
                    // OR
                    //                    $tmp = array_merge($tmp, $collection->getAssignedIds());

                    // AND
                    $tmp = count($tmp) == 0
                        ? (count($collection->getAssignedIds()) == 0
                            ? [0]
                            : $collection->getAssignedIds())
                        : array_intersect($tmp, $collection->getAssignedIds());

                    if (count($tmp) == 0) {
                        break;
                    }
                }
            }
            $tmp = array_unique($tmp);
            $tmp[] = 0;
            $list->addConditionParam(sprintf('id IN(%s)', implode(',', $tmp)), '');
            
        }
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return [
            'filter_collection' => $this->getParam('filter_collection')
        ];
    }
}
