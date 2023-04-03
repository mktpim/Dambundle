<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Dam\Item;

use Carbon\Carbon;
use Pimcore\Model\Asset;

class Folder extends AbstractItem
{
    /**
     * @var array|null
     */
    protected $extendedMetaData;

    /**
     * @return array
     */
    public function getExtendedMetaData()
    {
        if ($this->extendedMetaData === null) {
            $list = new Asset\Listing();
            $list->setCondition('path like ?', $this->getAsset()->getFullPath().'%');

            $this->extendedMetaData = [
                'children' => $list->count()
            ];
        }

        return $this->extendedMetaData;
    }

    public function getSystemMetaData()
    {
        $created = new Carbon('@'.$this->getAsset()->getCreationDate());
        $modified = new Carbon('@'.$this->getAsset()->getModificationDate());

        return [
            'fileCreated' => $created->toFormattedDateString(),
            'fileModified' => $modified->toFormattedDateString()
        ];
    }

    public function getAlternateThumbnails($name = null)
    {

        // init
        $name = !$name ? $this->thumbName : $name;
        $folder = new Asset\Listing();
        $folder->addConditionParam('parentId = ?', $this->getAsset()->getId());
        $folder->setLimit(10);
        $thumbs = [];

        // collect thumbs
        foreach ($folder as $child) {
            if ($child instanceof Asset\Image || $child instanceof Asset\Video || $child instanceof Asset\Document) {
                $item = AbstractItem::createInstance($child);
                $thumbs[] = $item->getThumbnail($name);
            }
        }

        return count($thumbs) > 0 ? $thumbs : null;
    }

    public function getListViewMetaData()
    {
        return [];
    }

    public function getIcon()
    {
        return 'glyphicon glyphicon-folder-close';
    }

    public function hasThumbnail()
    {
        return false;
    }
}
