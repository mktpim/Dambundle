<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Controller;

use Pimcore\Bundle\DamBundle\Dam\Facade;
use Pimcore\Bundle\DamBundle\Dam\Item\AbstractItem;
use Pimcore\Controller\FrontendController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class DashboardController extends FrontendController
{
    use ControllerAware {
        ControllerAware::onKernelController as __onKernelController;
    }

    /**
     * dashboard anzeige
     *
     * @Route("/dashboard/index")
     */
    public function indexAction(Request $request)
    {
        // liste von der geänderten assets laden
        $assetChanged = [];

        $list = Facade::getAssetList();
        $list->addConditionParam('type != ?', 'folder');
        $list->setOrderKey(['modificationDate']);
        $list->setOrder('DESC');
        $list->setLimit(12);
        foreach ($list as $asset) {
            $assetChanged[] = AbstractItem::createInstance($asset);
        }

        $this->view->assetChanged = $assetChanged;
    }
}
