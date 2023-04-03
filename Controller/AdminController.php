<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Controller;

use Pimcore\Bundle\DamBundle\Model\Terms;
use Pimcore\Controller\FrontendController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class AdminController extends FrontendController
{
    /**
     * @Route("/admin/terms")
     */
    public function termsAction(Request $request)
    {
        if ($request->isMethod('POST')) {
            foreach ($request->get('terms') as $lang => $terms) {
                $model = Terms::getByLang($lang);
                if (!$model) {
                    $model = new Terms();
                    $model->setLang($lang);
                }

                $model->setTerms($terms);
                $model->save();
            }

            // weiterleiten
            $url = $this->generateUrl('pimcore_dam_admin_terms');

            return $this->redirect($url);
        }

        $list = new Terms\Listing();

        $terms = [];
        foreach ($list as $term) {
            $terms[$term->getLang()] = $term->getTerms();
        }
        $this->view->terms = $terms;
    }
}
