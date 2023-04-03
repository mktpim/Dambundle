<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Dam\Item;

class Text extends AbstractItem
{
    public function getIcon()
    {
        return 'glyphicon glyphicon-comment';
    }

    public function hasThumbnail()
    {
        return false;
    }
}
