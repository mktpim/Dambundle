<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Event;

use Pimcore\Bundle\DamBundle\Dam\Item\AbstractItem;

class Download extends AEvent
{
    /**
     * @var AbstractItem
     */
    protected $item;

    /**
     * Download constructor.
     *
     * @param AbstractItem $item
     */
    public function __construct(AbstractItem $item)
    {
        $this->item = $item;
    }
}
