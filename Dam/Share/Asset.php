<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Dam\Share;

use Pimcore\Bundle\DamBundle\Dam\Item\AbstractItem;

class Asset extends AbstractShare
{
    /**
     * @return AbstractItem[]
     */
    public function getAssets()
    {
        $ids = explode(',', trim($this->share->getReferenceIds(), ','));
        $items = [];

        foreach ($ids as $id) {
            $asset = \Pimcore\Model\Asset::getById($id);
            $items[] = AbstractItem::createInstance($asset);
        }

        return $items;
    }
}
