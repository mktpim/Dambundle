<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Templating\Helper;

use Pimcore\Templating\Helper\TemplatingEngineAwareHelperInterface;
use Pimcore\Templating\Helper\Traits\TemplatingEngineAwareHelperTrait;
use Symfony\Component\Templating\Helper\Helper;

class Url extends Helper implements TemplatingEngineAwareHelperInterface
{
    use TemplatingEngineAwareHelperTrait;

    public function getName()
    {
        return 'damUrl';
    }

    /**
     * @param array $urlOptions
     * @param null $name
     * @param bool $reset
     * @param bool $encode
     *
     * @return string|void
     *
     * @throws \Exception
     */
    public function url(array $urlOptions = [], $name = null, $reset = false, $encode = true)
    {
        $url = parent::url($urlOptions, $name, $reset, $encode);

        if ($reset || !$_SERVER['QUERY_STRING']) {
            return $url;
        }

        return $url . '?' . $_SERVER['QUERY_STRING'];
    }
}
