<?php

/**
 * Pimcore DAM
 *
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Dam;

use Pimcore\Bundle\DamBundle\Event;
use Pimcore\Bundle\DamBundle\Model\Collection;

class Grid implements \Iterator, \Countable
{
    /**
     * @var Item\AbstractItem[]
     */
    protected $assetList;

    /**
     * @var int
     */
    protected $offset = 0;

    /**
     * @var string
     */
    protected $view;

    /**
     * @var Collection
     */
    protected $bookmarkCollection;

    /**
     * inhalt aller unterordner auf einmal anzeigen
     *
     * @var bool
     */
    protected $flatView = false;

    /**
     * @param Item\AbstractItem $item
     */
    public function addItem(Item\AbstractItem $item)
    {
        $this->assetList[] = $item;
    }

    /**
     * @return \DAM_Collection
     */
    public function getBookmarkCollection()
    {
        return $this->bookmarkCollection;
    }

    /**
     * @param \DAM_Collection $bookmarkCollection
     */
    public function setBookmarkCollection($bookmarkCollection)
    {
        $this->bookmarkCollection = $bookmarkCollection;
    }

    /**
     * @return bool
     */
    public function getFlatView()
    {
        return $this->flatView;
    }

    /**
     * @param bool $flatView
     */
    public function setFlatView($flatView)
    {
        $this->flatView = (bool)$flatView;
    }

    /**
     * @return string
     */
    public function getView(): string
    {
        return $this->view;
    }

    /**
     * gallery|list|detail
     *
     * @param string $name
     */
    public function setView(string $name)
    {
        $this->view = $name;
    }

    /**
     * alle items zu einem zip archiv hinzufügen
     */
    public function createZipFile($root)
    {
        $zipFile = PIMCORE_SYSTEM_TEMP_DIRECTORY.'/download-zip-'.uniqid('DamListener').'.zip';

        $zip = new \ZipArchive();
        if ($zip->open($zipFile, \ZipArchive::CREATE)) {
            foreach ($this as $item) {
                /* @var Item\AbstractItem $item */
                $asset = $item->getAsset();

                if (!$item->isFolder()) {
                    $zip->addFile(
                        $asset->getFileSystemPath(),
                        \Pimcore\File::getValidFilename($root).$asset->getFullPath()
                    );

                    $eventDownload = new Event\Download($item);
                    \Pimcore::getEventDispatcher()->dispatch(Events::DOWNLOAD_ASSET, $eventDownload);
                }
            }

            $zip->close();
        }

        return $zipFile;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     *
     * @link http://php.net/manual/en/iterator.current.php
     *
     * @return Item\AbstractItem
     */
    public function current()
    {
        $item = $this->assetList[$this->offset];

        $item->setThumbName('dam_list');

        return $item;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     *
     * @link http://php.net/manual/en/iterator.next.php
     *
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->offset++;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     *
     * @link http://php.net/manual/en/iterator.key.php
     *
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->offset;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     *
     * @link http://php.net/manual/en/iterator.valid.php
     *
     * @return bool The return value will be casted to boolean and then evaluated.
     *       Returns true on success or false on failure.
     */
    public function valid()
    {
        if (!is_iterable($this->assetList)) {
            return false;
        }

        return $this->offset < count($this->assetList);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     *
     * @link http://php.net/manual/en/iterator.rewind.php
     *
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->offset = 0;
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     *
     * @link http://php.net/manual/en/countable.count.php
     *
     * @return int The custom count as an integer.
     *       </p>
     *       <p>
     *       The return value is cast to an integer.
     */
    public function count()
    {
        if (!is_iterable($this->assetList)) {
            return 0;
        }
        return count($this->assetList);
    }
}
