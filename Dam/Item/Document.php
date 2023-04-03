<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Dam\Item;

use Pimcore\Model\Asset;

/**
 * Class Document
 *
 * @package Pimcore\Bundle\DamBundle\Dam\Item
 *
 * @method Asset\Document getAsset()
 */
class Document extends AbstractItem
{
    /**
     * @var array|null
     */
    protected $extendedMetaData;

    /**
     * @param null $name
     *
     * @return array|Asset\Image\Thumbnail[]|null
     */
    public function getAlternateThumbnails($name = null)
    {
        // init
        $name = !$name ? $this->thumbName : $name;
        $asset = $this->getAsset();
        $thumbs = [];
        $pageCount = $asset->getPageCount() > 10 ? 10 : $asset->getPageCount();

        for ($i = 1; $i <= $pageCount; $i++) {
            $thumbs[] = $asset->getImageThumbnail($name, $i, true);
        }

        return $asset->getPageCount() > 1 ? $thumbs : null;
    }

    /**
     * @param string $name
     *
     * @return mixed|string
     */
    public function getThumbnail($name = null)
    {
        $name = !$name ? $this->thumbName : $name;

        return $this->getAsset()->getImageThumbnail($name, 1, true);
    }

    /**
     * @return array
     */
    public function getExtendedMetaData()
    {
        // init
        $asset = $this->getAsset();

        if ($this->extendedMetaData === null) {
            $extended = [];

            try {
                $extended['pageCount'] = $asset->getPageCount();
            } catch (\Exception $e) {
            }

            $this->extendedMetaData = $extended;
        }

        return $this->extendedMetaData;
    }

    /**
     * @return null|string
     */
    public function getIcon()
    {
        return 'glyphicon glyphicon-file';
    }

    public function hasExtendedMetaData()
    {
        true;
    }
}
