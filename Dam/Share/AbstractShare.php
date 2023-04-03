<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Dam\Share;

use Pimcore\Bundle\DamBundle\Dam\Item\AbstractItem;
use Pimcore\Bundle\DamBundle\Model\Share;
use Pimcore\Model\User;

abstract class AbstractShare
{
    /**
     * @var Share
     */
    protected $share;

    /**
     * @var User
     */
    protected $user;

    /**
     * @param Share $share
     *
     * @return AbstractShare
     */
    public static function createInstance(Share $share)
    {
        $class = '\\Pimcore\\Bundle\\DamBundle\\Dam\\Share\\' . ucfirst(strtok($share->getType(), '-'));

        return new $class($share);
    }

    /**
     * @param Share $share
     */
    public function __construct(Share $share)
    {
        $this->share = $share;
        $this->user = User::getById($share->getUserId());
    }

    /**
     * @return string[]|null
     */
    public function getConfig()
    {
        if ($this->share->getConfig()) {
            return json_decode($this->share->getConfig(), true);
        }
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->share->getToken();
    }

    /**
     * @return \Carbon\Carbon
     */
    public function getExpireDate()
    {
        return $this->share->getExpireDate();
    }

    /**
     * @return AbstractItem[]
     */
    abstract public function getAssets();

    /**
     * @param $id
     *
     * @return AbstractItem[]
     */
    public function getAssetById($id)
    {
        foreach ($this->getAssets() as $item) {
            if ($item->getId() == $id) {
                return $item;
            }
        }
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->share->getType();
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getTermsComment()
    {
        return $this->share->getTermsComment();
    }
}
