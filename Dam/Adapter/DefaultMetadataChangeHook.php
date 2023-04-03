<?php
/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Dam\Adapter;

use Pimcore\Bundle\DamBundle\Dam\Item\AbstractItem;

class DefaultMetadataChangeHook implements MetadataChangeHookInterface
{
    public function getReadOnlyFields(AbstractItem $item = null, $arrMetaFields) : array {
        return $arrMetaFields;
    }

    public function getEditableMetaFields(AbstractItem $item = null, $arrMetaFields) {
        return $arrMetaFields;
    }
}