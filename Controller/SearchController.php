<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Controller;

use Pimcore\Bundle\DamBundle\Dam;
use Pimcore\Controller\FrontendController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Request;

class SearchController extends FrontendController
{
    use ControllerAware {
        ControllerAware::onKernelController as __onKernelController;
    }

    /**
     * list all saved searches
     *
     * @Route("/search/list")
     */
    public function listAction(Request $request)
    {
        // init
        $this->view->list = Dam\Facade::getSearchList();
    }

    /**
     * save search config
     *
     * @Route("/search/save")
     */
    public function saveAction(Request $request)
    {
        // init
        $list = Dam\Facade::getSearchList();

        // update
        if ($request->isMethod('POST')) {
            $names = $request->get('name');
            foreach ($list as $search) {
                $search->setName($names[$search->getId()]);
                $search->save();
            }
        }

        // redirect
        $url = $this->generateUrl('pimcore_dam_asset_list');

        return $this->redirect($url);
    }

    /**
     * delete saved search
     *
     * @Route("/search/delete")
     */
    public function deleteAction(Request $request)
    {
        // init
        $list = Dam\Facade::getSearchList();
        $list->addConditionParam(sprintf('id = %d', $request->get('id')), '');

        $search = $list->current();
        if ($search) {
            $search->delete();
        }

        // redirect
        $url = $this->generateUrl('pimcore_dam_asset_list');

        return $this->redirect($url);
    }
}
