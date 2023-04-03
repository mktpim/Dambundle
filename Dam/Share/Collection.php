<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Dam\Share;

use Pimcore\Bundle\DamBundle\Dam\Facade;
use Pimcore\Bundle\DamBundle\Dam\Item\AbstractItem;

class Collection extends AbstractShare
{
    /**
     * @var
     */
    private $assets;

    /**
     * @return AbstractItem[]
     */
    public function getAssets()
    {
        if (!$this->assets) {
            // load collection
            $list = Facade::getCollectionList(false);
            $list->addConditionParam(sprintf('id in( %s )', trim($this->share->getReferenceIds(), ',')));

            // load items
            $items = [];

            if ($collection = $list->current()) {
                foreach ($collection as $asset) {
                    /* @var Asset $asset */
                    if ($asset->getType() != 'folder') {
                        $items[] = AbstractItem::createInstance($asset);
                    }
                }
            }

            $this->assets = $items;
        }

        return $this->assets;
    }
}
