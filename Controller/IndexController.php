<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Controller;

use Pimcore\Bundle\DamBundle\Dam;
use Pimcore\Controller\FrontendController;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends FrontendController
{
    use ControllerAware {
        ControllerAware::onKernelController as __onKernelController;
    }

    /**
     * @Route("/", name="pimcore_dam_index")
     */
    public function indexAction()
    {
        return $this->redirectToRoute('pimcore_dam_asset_list');
    }

    /**
     * start / login screen
     *
     * @Route("/login", name="pimcore_dam_login")
     */
    public function loginAction(Request $request)
    {
        // init
        $user = null;
        // $this->view->publicAccess = false;
        $this->view->publicAccess = true;

        // TODO shortcut for token view
        //        if($request->get('t'))
        //        {
        //            $url = $this->view->path(['action' => 'token-list',
        //                                     'controller' => 'share',
        //                                     'module' => 'DamListener',
        //                                     'token' => $this->getParam('t')], 'plugin', true);
        //            $this->redirect( $url );
        //        }

        // public access erlaubt?
        if (Dam\Facade::getPublicUser('mannington-user')) {
            $this->view->publicAccess = true;
        } elseif (Dam\Facade::getPublicUser('phenix-user')) {
            $this->view->publicAccess = true;
        }
    }

    /**
     * Logout
     *
     * @Route("/logout", name="pimcore_dam_logout")
     */
    public function logoutAction()
    {
        // this route will never be matched, but will be handled by the logout handler
    }

    /**
     * Dummy route used to check authentication
     *
     * @Route("/login/login", name="pimcore_dam_login_check")
     *
     * @see AdminAuthenticator for the security implementation
     */
    public function loginCheckAction()
    {
        // just in case the authenticator didn't redirect
        return new RedirectResponse($this->generateUrl('pimcore_dam_login'));
    }
}
