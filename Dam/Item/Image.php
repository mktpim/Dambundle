<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Dam\Item;

class Image extends AbstractItem
{
    /**
     * @var array|null
     */
    protected $extendedMetaData;

    public function getConvertAble()
    {
        return true;
    }

    public function getIcon()
    {
        return 'glyphicon glyphicon-picture';
    }

    public function getExtendedMetaData()
    {
        $asset = $this->getAsset();
        /* @var \Asset_Image $asset */

        if ($this->extendedMetaData === null) {
            // read exif data
            $exif = [];
            if (function_exists('exif_read_data') && is_file($asset->getFileSystemPath())) {
                $supportedTypes = [IMAGETYPE_JPEG, IMAGETYPE_TIFF_II, IMAGETYPE_TIFF_MM];

                if (in_array(exif_imagetype($asset->getFileSystemPath()), $supportedTypes)) {
                    $exif = @exif_read_data($asset->getFileSystemPath());
                    if (is_array($exif)) {
                        foreach ($exif as $name => $value) {
                            if ((is_string($value) && strlen($value) < 50) || is_numeric($value)) {
                                // this is to ensure that the data can be converted to json (must be utf8)
                                if (mb_check_encoding($value, 'UTF-8')) {
                                    $exif[$name] = $value;
                                }
                            }
                        }
                    }
                }
            }

            $this->extendedMetaData = [
                'width' => $asset->getWidth(),
                'height' => $asset->getHeight(),
            ];

            // get color space from image
            $colorSpace = $this->getColorSpaceString();
            if ($colorSpace) {
                $this->extendedMetaData['colorSpace'] = $colorSpace;
            }

            if (array_key_exists('Make', $exif) || array_key_exists('Model', $exif)) {
                $this->extendedMetaData['camera'] = $exif['Make'].' '.$exif['Model'];
            }
            if (array_key_exists('DateTime', $exif)) {
                $this->extendedMetaData['DateTime'] = $exif['DateTime'];
            }
        }

        return $this->extendedMetaData;
    }

    public function hasExtendedMetaData()
    {
        return true;
    }

    /**
     * @param int $numColors
     * @param int $granularity
     *
     * @return array|bool
     */
    public function getColorPalette($numColors, $granularity = 5)
    {
        // init
        $asset = $this->getAsset();
        /* @var \Asset_Image $asset */
        $granularity = max(1, abs((int)$granularity));
        $colors = [];

        // optimize size
        $size = [640, 480];

        // resize image for better performance
        $org = imagecreatefromstring($asset->getData());
        $img = imagecreatetruecolor($size[0], $size[1]);
        imagecopyresized($img, $org, 0, 0, 0, 0, $size[0], $size[1], $asset->getWidth(), $asset->getHeight());

        // get colors
        for ($x = 0; $x < $size[0]; $x += $granularity) {
            for ($y = 0; $y < $size[1]; $y += $granularity) {
                $thisColor = imagecolorat($img, $x, $y);
                $rgb = imagecolorsforindex($img, $thisColor);
                $red = round(round(($rgb['red'] / 0x33)) * 0x33);
                $green = round(round(($rgb['green'] / 0x33)) * 0x33);
                $blue = round(round(($rgb['blue'] / 0x33)) * 0x33);
                $thisRGB = sprintf('%02X%02X%02X', $red, $green, $blue);
                if (array_key_exists($thisRGB, $colors)) {
                    $colors[$thisRGB]++;
                } else {
                    $colors[$thisRGB] = 1;
                }
            }
        }
        arsort($colors);

        return array_slice(array_keys($colors), 0, $numColors);
    }

    /**
     * @return string|null
     */
    protected function getColorSpaceString()
    {
        if (!file_exists($this->getAsset()->getFileSystemPath())) {
            return '';
        }
        // init
        $image = new \Imagick($this->getAsset()->getFileSystemPath());
        $colorSpace = $image->getImageColorspace();
        $fooClass = new \ReflectionClass('Imagick');
        $constants = $fooClass->getConstants();

        // search
        foreach ($constants as $name => $value) {
            if (substr($name, 0, 11) == 'COLORSPACE_' && $value == $colorSpace) {
                return substr($name, 11);
            }
        }
    }
}
