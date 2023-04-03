<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Controller;

use Pimcore\Bundle\DamBundle\Dam\Facade;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

trait ControllerAware
{
    /**
     * rechte prüfen
     *
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        parent::onKernelController($event);

        $this->initLanguage($event);

        if ($this->getUser()) {
            $this->view->user = $this->getUser()->getUser();
        }
    }

    /**
     * @param FilterControllerEvent $event
     */
    protected function initLanguage(FilterControllerEvent $event)
    {
        // init
        $request = $event->getRequest();

        $fallbackLanguage = $request->getLocale();

        if($this->getUser() && $this->getUser()->getUser()) {
            $fallbackLanguage = $this->getUser()->getUser()->getLanguage();
        }
        $language = $request->get('lang', Facade::getFallbackLanguage($fallbackLanguage));

        $config = \Pimcore\Bundle\DamBundle\PimcoreDamBundle::getConfig();
        if ($config['backend']['languageMapping']) {
            $languageMapping = $config['backend']['languageMapping'];
            if (isset($languageMapping[$language])) {
                $language = $languageMapping[$language];
            } elseif (isset($languageMapping['fallback'])) {
                $language = $languageMapping['fallback'];
            }
        }

        $this->view->language = $language;
        $this->get('pimcore.locale')->setLocale($language);
    }
}
