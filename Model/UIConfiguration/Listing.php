<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Model\UIConfiguration;

use Pimcore\Bundle\DamBundle\Model\UIConfiguration;

class Listing extends \Pimcore\Model\Listing\AbstractListing
{
    /**
     * @var UIConfiguration[]
     */
    protected $list;

    /**
     * @var bool
     */
    protected $validate;

    /**
     * @param bool $state
     */
    public function setValidation($state)
    {
        $this->validate = (bool)$state;
    }

    /**
     * @param $key
     *
     * @return bool
     */
    public function isValidOrderKey($key)
    {
        return in_array($key, ['id', 'parentId']);
    }

    /**
     * @param array $list
     */
    public function setList(array $list)
    {
        $this->list = $list;
    }

    /**
     * @return Listing
     */
    public function getList()
    {
        // load rules if not loaded yet
        if (empty($this->list)) {
            $this->load();
        }

        return $this->list;
    }

    /**
     * @param \Pimcore\Model\User|null $user
     *
     * @return $this
     */
    public function filterByUser(\Pimcore\Model\User $user = null)
    {
        // if no user passed take authenticated user
        if (!$user) {
            $user = \Pimcore\Tool\Authentication::authenticateSession();
        }

        $this->addConditionParam('userId = ?', $user->getId());

        return $this;
    }

    /**
     * @param $parentId
     *
     * @return $this
     */
    public function filterByParentId($parentId)
    {
        $this->addConditionParam('parentId = ?', $parentId);

        return $this;
    }
}
