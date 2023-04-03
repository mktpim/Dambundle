<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Dam\Item;

use Carbon\Carbon;
use Pimcore\Bundle\DamBundle\Model\Collection;
use Pimcore\Bundle\DamBundle\PimcoreDamBundle;
use Pimcore\Model\Asset;

abstract class AbstractItem
{
    const ITEM_TYPE_IMAGE = 'image';

    /**
     * @var Asset
     */
    protected $asset;

    /**
     * @var string
     */
    protected $thumbName;

    /**
     * @var Collection
     */
    protected $bookmark;

    /**
     * @var Collection
     */
    protected $collectionList = [];

    /**
     * @var bool
     */
    protected $allowDelete = false;

    /**
     * @var bool
     */
    protected $allowShare = false;

    /**
     * @var bool
     */
    protected $allowCollection = false;

    /**
     * @var bool
     */
    protected $allowDownload = false;

    /**
     * @var bool
     */
    protected $allowBookmark = false;

    /**
     * @var bool
     */
    protected $allowUpdateMetaData = false;

    /**
     * @var bool
     */
    protected $convertAble = false;

    /**
     * @param Asset $asset
     *
     * @return AbstractItem
     */
    public static function createInstance(Asset $asset)
    {
        $class = PimcoreDamBundle::getConfig()['assets']['classes'][$asset->getType()];
        if ($class === null) {
            $class = '\\Pimcore\\Bundle\\DamBundle\\Dam\\Item\\' . ucfirst($asset->getType());
        }
        return new $class($asset);
    }

    /**
     * @param Asset $asset
     */
    public function __construct(Asset $asset)
    {
        $this->asset = $asset;
    }

    /**
     * @param $name
     *
     * @return Asset\Image\Thumbnail
     */
    public function getThumbnail($name = null)
    {
        $name = !$name ? $this->thumbName : $name;
        if ($this->asset instanceof \Pimcore\Model\Asset\Image) {
            //consider class overrides for images (consider this for videos as well?)
            return $this->asset->getThumbnail($name, true);
        } else{
            $thumb = new Asset\Image\Thumbnail($this->asset, $name, true);
            return $thumb;
        }
    }

    /**
     * @return bool
     */
    public function hasThumbnail()
    {
        return true;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return ucfirst($this->asset->getType());
    }

    /**
     * @return bool
     */
    public function isFolder()
    {
        return $this->getType() == 'Folder';
    }

    /**
     * @return Asset
     */
    public function getAsset()
    {
        return $this->asset;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->asset->getId();
    }

    /**
     * @param string $thumbName
     */
    public function setThumbName($thumbName)
    {
        $this->thumbName = $thumbName;
    }

    /**
     * get complete metadata
     *
     * @return array
     */
    public function getMetaData()
    {
        $data = $this->getSystemMetaData();

        if ($this->hasUserMetaData()) {
            $data = array_merge($data, $this->getUserMetaData());
        }

        if ($this->hasExtendedMetaData()) {
            $data = array_merge($data, $this->getExtendedMetaData());
        }

        return $data;
    }

    /**
     * internal metadata
     *
     * @return array
     */
    public function getSystemMetaData()
    {
        $created = new Carbon('@'.$this->getAsset()->getCreationDate());
        $modified = new Carbon('@'.$this->getAsset()->getModificationDate());

        $x = ['KB', 'MB', 'GB'];
        $size = $this->getAsset()->getFileSize();
        while (($size /= 1024) >= 1024) {
            next($x);
        }

        return [
            'fileCreated' => $created->toFormattedDateString(),
            'fileModified' => $modified->toFormattedDateString(),
            'fileSize' => round($size, 2) . ' ' . current($x),
            'mimeType' => $this->getAsset()->getMimetype()
        ];
    }

    /**
     * data related metadata
     *
     * @return array
     */
    public function getExtendedMetaData()
    {
        return [];
    }

    /**
     * @return bool
     */
    public function hasExtendedMetaData()
    {
        return is_array($this->getExtendedMetaData());
    }

    /**
     * user metadata
     *
     * @return array|null
     */
    public function getUserMetaData()
    {
        return null;
    }

    public function getListViewMetaData()
    {
        // init
        $fields = PimcoreDamBundle::getConfig()['backend']['ui']['listview']['listview_metadata'];

        $result = [];
        foreach ($fields as $field) {
            $result[$field] = $this->getAsset()->getMetadata($field);
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function hasUserMetaData()
    {
        return is_array($this->getUserMetaData());
    }

    /**
     * @return string
     */
    public function getCaption()
    {
        return $this->getAsset()->getFilename();
    }

    /**
     * display alternate thumbnails
     * like: items in a folder or document pages
     *
     * @param string $name
     *
     * @return Asset\Image\Thumbnail[]
     */
    public function getAlternateThumbnails($name = null)
    {
    }

    /**
     * @param Collection $bookmark
     */
    public function setBookmark(Collection $bookmark = null)
    {
        $this->bookmark = $bookmark;
    }

    /**
     * @return Collection
     */
    public function getBookmark()
    {
        return $this->bookmark;
    }

    /**
     * @param Collection $collectionList
     */
    public function setCollectionList($collectionList)
    {
        $this->collectionList = $collectionList;
    }

    /**
     * @return Collection
     */
    public function getCollectionList()
    {
        return $this->collectionList;
    }

    /**
     * @param \Pimcore\Templating\PhpEngine $view
     *
     * @return string
     */
    public function getDetailLink(\Pimcore\Templating\PhpEngine $view): string
    {
        return $this->isFolder()
            ? $view->path('pimcore_dam_asset_list', ['pid' => $this->getId()])
            : $view->path('pimcore_dam_asset_detail', ['id' => $this->getId()])
        ;
    }

    /**
     * @param bool $allowBookmark
     */
    public function setAllowBookmark($allowBookmark)
    {
        $this->allowBookmark = $allowBookmark;
    }

    /**
     * @return bool
     */
    public function getAllowBookmark()
    {
        return $this->allowBookmark;
    }

    /**
     * @param bool $allowCollection
     */
    public function setAllowCollection($allowCollection)
    {
        $this->allowCollection = $allowCollection;
    }

    /**
     * @return bool
     */
    public function getAllowCollection()
    {
        return $this->allowCollection;
    }

    /**
     * @param bool $allowDelete
     */
    public function setAllowDelete($allowDelete)
    {
        $this->allowDelete = $allowDelete;
    }

    /**
     * @return bool
     */
    public function getAllowDelete()
    {
        return $this->allowDelete;
    }

    /**
     * @param bool $allowShare
     */
    public function setAllowShare($allowShare)
    {
        $this->allowShare = $allowShare;
    }

    /**
     * @return bool
     */
    public function getAllowShare()
    {
        return $this->allowShare;
    }

    /**
     * @return bool
     */
    public function getAllowUpdateMetaData()
    {
        return $this->allowUpdateMetaData;
    }

    /**
     * @param bool $allowUpdateMetaData
     */
    public function setAllowUpdateMetaData($allowUpdateMetaData)
    {
        $this->allowUpdateMetaData = $allowUpdateMetaData;
    }

    /**
     * @param bool $convertAble
     */
    public function setConvertAble($convertAble)
    {
        $this->convertAble = $convertAble;
    }

    /**
     * @return bool
     */
    public function getConvertAble()
    {
        return $this->convertAble;
    }

    /**
     * @param bool $allowDownload
     */
    public function setAllowDownload($allowDownload)
    {
        $this->allowDownload = $allowDownload;
    }

    /**
     * @return bool
     */
    public function getAllowDownload()
    {
        return $this->allowDownload;
    }

    /**
     * @param \Zend_View $view
     *
     * @return return
     */
    public function getShareLink(\Zend_View $view)
    {
        return $view->url(['action' => 'view-shared',
                           'controller' => 'asset',
                           'module' => 'DamListener',
                           'id' => $this->getId(),
                           'token' => $this->getShareToken()
            ], 'plugin', true);
    }

    /**
     * @return null
     */
    public function getIcon()
    {
        return 'glyphicon glyphicon-file';
    }
}
