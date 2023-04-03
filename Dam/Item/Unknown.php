<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Dam\Item;

class Unknown extends AbstractItem
{
    public function getThumbnail($name = null)
    {
        return '/pimcore/static/img/filetype-not-supported.png';
    }

    /**
     * @return bool
     */
    public function hasThumbnail()
    {
        return false;
    }
}
