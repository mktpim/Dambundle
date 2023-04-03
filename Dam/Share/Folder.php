<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Dam\Share;

use Pimcore\Bundle\DamBundle\Dam\Item\AbstractItem;
use Pimcore\Bundle\DamBundle\Model\Asset\Listing;

class Folder extends AbstractShare
{
    /**
     * @return AbstractItem[]
     */
    public function getAssets()
    {
        // load collection
        $list = new Listing();
        $list->setCondition(sprintf('parentId in( %s ) AND type != "folder"', trim($this->share->getReferenceIds(), ',')));

        // load items
        $items = [];
        foreach ($list as $asset) {
            $items[] = AbstractItem::createInstance($asset);
        }

        return $items;
    }
}
