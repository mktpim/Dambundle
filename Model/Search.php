<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Model;

use Pimcore\Bundle\DamBundle\Dam\Filter\AbstractFilter;
use Pimcore\Model\User;
use Pimcore\Templating\PhpEngine;

/**
 * Class Search
 *
 * @package Pimcore\Bundle\DamBundle\Model
 *
 * @method \Pimcore\Bundle\DamBundle\Model\Search\Dao getDao()
 */
class Search extends \Pimcore\Model\AbstractModel
{
    /**
     * @param int $id
     *
     * @return $this
     */
    public static function getById($id)
    {
        $self = new self();

        return $self->getDao()->getById($id);
    }

    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $creationDate;

    /**
     * @var int
     */
    protected $userId;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $config;

    /**
     * construct
     */
    public function __construct()
    {
        $this->setCreationDate(time());
    }

    /**
     * @param int $creationDate
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }

    /**
     * @return int
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param string $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->userId = $user->getId();
    }

    /**
     * @param PhpEngine $view
     *
     * @return null|string
     */
    public function getLink(PhpEngine $view)
    {
        // init
        $config = json_decode($this->getConfig(), true);
        $url = null;

        if ($config) {
            $params = [];
            foreach ($config as $items) {
                foreach ($items as $name => $value) {
                    $params[$name] = $value;
                }
            }

            $url = $view->router()->path('pimcore_dam_asset_list', $params);
        }

        return $url;
    }

    /**
     * @param AbstractFilter[] $list
     */
    public function setFilter(array $list)
    {
        $config = [];
        foreach ($list as $filter) {
            $cnf = $filter->getConfig();
            if ($cnf !== null) {
                $config[$filter->getId()] = $cnf;
            }
        }

        $this->setConfig(json_encode($config));
    }
}
