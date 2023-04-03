<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Event;

use Pimcore\Bundle\DamBundle\Dam\Item\AbstractItem;
use Pimcore\Model\Asset;

/**
 * Class ItemRights
 * @package Pimcore\Bundle\DamBundle\Event
 */
class ItemRights extends AEvent
{
    /**
     * @var AbstractItem
     */
    protected $item;

    /**
     * @param AbstractItem $item
     */
    public function __construct(AbstractItem $item)
    {
        $this->item = $item;
    }

    public function setAllowShare(bool $allow) {
        $this->item->setAllowShare($allow);
    }

    public function setAllowCollection(bool $allow) {
        $this->item->setAllowCollection($allow);
    }

    public function setAllowDownload(bool $allow) {
        $this->item->setAllowDownload($allow);
    }

    public function setAllowDelete(bool $allow) {
        $this->item->setAllowDelete($allow);
    }
    public function setAllowUpdateMetaData(bool $allow) {
        $this->item->setAllowUpdateMetaData($allow);
    }

    public function setAllowBookmark(bool $allow) {
        $this->item->setAllowBookmark($allow);
    }

    public function getItem() : ?AbstractItem {
        return $this->item;
    }
}
