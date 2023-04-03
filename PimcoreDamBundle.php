<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle;

use Pimcore\Extension\Bundle\AbstractPimcoreBundle;

class PimcoreDamBundle extends AbstractPimcoreBundle
{
    const CONFIG_KEY = 'pimcore_dam_config';

    /**
     * @return array
     */
    public function getCssPaths()
    {
        return [
            '/bundles/pimcoredam/css/admin.css'
        ];
    }

    /**
     * @return array
     */
    public function getJsPaths()
    {
        return [
            '/bundles/pimcoredam/js/startup.js'
        ];
    }

    /**
     * @return Installer
     */
    public function getInstaller()
    {
        return new Installer();
    }

    /**
     * @return string[]
     */
    public static function getConfig()
    {
        return \Pimcore::getContainer()->getParameter(self::CONFIG_KEY);
    }
}
