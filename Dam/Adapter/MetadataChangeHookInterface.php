<?php
/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Dam\Adapter;

use Pimcore\Bundle\DamBundle\Dam\Item\AbstractItem;
use Pimcore\Model\Asset;
use Pimcore\Model\User;
use Pimcore\Tool\Authentication;

interface MetadataChangeHookInterface
{
    public function getReadOnlyFields(AbstractItem $item = null, $arrMetaFields) : array;

}