<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Dam\Filter;

use Pimcore\Bundle\DamBundle\Dam\Facade;
use Pimcore\Bundle\DamBundle\Model\Asset;
use Pimcore\Bundle\DamBundle\Model\Share\Listing;

class Token extends AbstractFilter
{
//    public function __construct()
//    {
//        $this->setId( 'token' );
//        $this->setName( 'Token' );
//    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return array_key_exists('filter_share', $this->getParams());
    }

    /**
     * @return bool
     */
    public function hasFrontend()
    {
        return false;
    }

    /**
     * @param Asset\Listing $list
     *
     * @return void
     */
    public function apply(Asset\Listing $list)
    {
        $user = Facade::getUser();

        $sl = new Listing();
        $sl->addConditionParam(sprintf('token = %s', $sl->quote($this->getParam('filter_share'))));
        if (!$user->isAdmin()) {
            $sl->addConditionParam('userId = ?', $user->getId());
        }

        $share = \Pimcore\Bundle\DamBundle\Dam\Share\AbstractShare::createInstance($sl->current());
        $ids = [];
        foreach ($share->getAssets() as $a) {
            $ids[] = $a->getId();
        }

        $list->addConditionParam(sprintf('id IN(%s)', implode(',', $ids) ?: "''"), '');
    }
}
