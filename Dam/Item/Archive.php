<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Dam\Item;

class Archive extends AbstractItem
{
    /**
     * @var array|null
     */
    protected $extendedMetaData;

    public function getMetaData()
    {
        // init
        $add = [];
        $data = parent::getMetaData();

        try {
            switch ($this->getAsset()->getMimetype()) {
                case 'application/zip':
                    $zip = new \ZipArchive();
                    $zip->open($this->getAsset()->getFileSystemPath());

                    $add = [
                        'archiveFiles' => $zip->numFiles,
//                    'archiveComment' => $zip->comment
                    ];

                    $zip->close();
                    break;
            }
        } catch (\Exception $e) {
        }

        return array_merge($data, $add);
    }

    public function hasThumbnail()
    {
        return false;
    }

    /**
     * @return array
     */
    public function getExtendedMetaData()
    {
        if ($this->extendedMetaData === null) {
            // init
            $extended = [];
            $asset = $this->getAsset();
            /* @var \Asset_Archive $asset */

            try {
                switch ($this->getAsset()->getMimetype()) {
                    case 'application/zip':
                        $zip = new \ZipArchive();
                        $zip->open($asset->getFileSystemPath());

                        $extended = [
                            'archiveFiles' => $zip->numFiles,
                            //                    'archiveComment' => $zip->comment
                        ];

                        $zip->close();
                        break;
                }
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
        return 'glyphicon glyphicon-book';
    }
}
