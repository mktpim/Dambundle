<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Dam\Item;

use Pimcore\Model\Asset;
use Pimcore\Model\Asset\Image\Thumbnail;

/**
 * Class Video
 *
 * @package Pimcore\Bundle\DamBundle\Dam\Item
 *
 * @method Asset\Video getAsset()
 */
class Video extends AbstractItem
{
    /**
     * @var array
     */
    protected $extendedMetaData;

    public function getExtendedMetaData()
    {
        // init
        $extended = [];
        $asset = $this->getAsset();

        if ($this->extendedMetaData === null) {
            // $this->get("translator")->trans("legal_notice");
            try {
//                $websiteTranslations = \Zend_Registry::get("Zend_Translate");
                $extended['videoDuration'] = round($asset->getDuration()); // . " " . $websiteTranslations->translate("dam.data.sec");
            } catch (\Exception $e) {
            }

            $this->extendedMetaData = $extended;
        }

        return $this->extendedMetaData;
    }

    public function getConvertAble()
    {
        return true;
    }

    public function getThumbnail($name = null)
    {
        // init
        $name = !$name ? $this->thumbName : $name;
        $asset = $this->getAsset();

        return $asset->getImageThumbnail($name);
    }

    /**
     * @param string $name
     *
     * @return Thumbnail[]
     */
    public function getAlternateThumbnails($name = null)
    {
        // init
        $name = !$name ? $this->thumbName : $name;
        $asset = $this->getAsset();
        $thumbs = [];
        $maxImages = 10;

        return [];
        $duration = floor($asset->getDuration());

        for ($i = 1; $i <= $maxImages; $i++) {
            $sec = floor($duration / $maxImages) * $i;
            $thumbs[] = $asset->getImageThumbnail($name, $sec);
        }

        return count($thumbs) > 0 ? $thumbs : null;
    }

    /**
     * @return null|string
     */
    public function getIcon()
    {
        return 'glyphicon glyphicon-film';
    }
}
