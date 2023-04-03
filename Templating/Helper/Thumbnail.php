<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Templating\Helper;

use Pimcore\Bundle\DamBundle\PimcoreDamBundle;
use Pimcore\Templating\Helper\TemplatingEngineAwareHelperInterface;
use Pimcore\Templating\Helper\Traits\TemplatingEngineAwareHelperTrait;
use Symfony\Component\Templating\Helper\Helper;

class Thumbnail extends Helper implements TemplatingEngineAwareHelperInterface
{
    use TemplatingEngineAwareHelperTrait;

    /**
     * @var string
     */
    protected $pixelGif = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

    /**
     * @var string|null
     */
    protected $thumbnail;

    public function getName()
    {
        return 'damThumbnail';
    }

    public function __invoke(string $thumbnail)
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }

    public function __toString()
    {
        if (!$this->thumbnail) {
            // transparentes gif
            $src = $this->pixelGif;
        } else {
            $passThrough = PimcoreDamBundle::getConfig()['extension']['passAssetsThroughController'];

            $inShareFrontend = $this->templatingEngine->getViewParameter('inShareFrontend');
            if ($inShareFrontend && $passThrough) {
                $src = $this->templatingEngine->router()->path('pimcore_dam_share_tokengetimage', [
                    'path' => $this->thumbnail, 't' => $this->templatingEngine->getParam('t')
                ]);
            } else {
                $src = $this->thumbnail;
            }
        }

        return $src;
    }
}
