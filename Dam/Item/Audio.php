<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Dam\Item;

class Audio extends AbstractItem
{
    /**
     * @var string[]|null
     */
    protected $extendedMetaData;

    /**
     * @return null|string
     */
    public function getIcon()
    {
        return 'glyphicon glyphicon-volume-up';
    }

    /**
     * @return bool
     */
    public function hasThumbnail()
    {
        return false;
    }

    /**
     * @return \string[]|null
     */
    public function getExtendedMetaData()
    {
        if (!$this->extendedMetaData) {
            // get data from ffmpeg
            $cmd = sprintf('ffmpeg -i %s 2>&1', $this->getAsset()->getFileSystemPath());
            $output = shell_exec($cmd);

            $output = substr($output, strpos($output, 'Input #0'));
            if (preg_match_all('#(?<name>[a-z]+): (?<value>[^,\n]+)#ism', $output, $matches)) {
                $websiteTranslations = \Zend_Registry::get('Zend_Translate');
                $data = [
                    'audioDuration' => null, 'audioBitrate' => null, 'audioAudio' => null
                ];

                foreach ($matches['name'] as $i => $name) {
                    $key = 'audio' . ucfirst($name);
                    if (array_key_exists($key, $data)) {
                        $data[$key] = $matches['value'][$i];
                    }
                }

                // format output
                if ($data['audioDuration']) {
                    $data['audioDuration'] = strtok($data['audioDuration'], '.');
                }

                $this->extendedMetaData = $data;
            }
        }

        return $this->extendedMetaData;
    }
}
