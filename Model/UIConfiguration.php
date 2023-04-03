<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Model;

use Pimcore\Bundle\DamBundle\Dam\Helper;

/**
 * Class UIConfiguration
 *
 * @method UIConfiguration save() MonitoringItem
 * @method UIConfiguration load() MonitoringItem[]
 * @method \Pimcore\Bundle\DamBundle\Model\UIConfiguration\Dao getDao()
 *
 * @package Pimcore\Bundle\DamBundle\Model
 */
class UIConfiguration extends \Pimcore\Model\AbstractModel implements \Iterator
{
    /**
     * @param int $id
     *
     * @return UIConfiguration
     */
    public static function getById($id)
    {
        $self = new self();

        return $self->getDao()->getById($id);
    }

    /**
     * @param int $id
     *
     * @return UIConfiguration
     */
    public static function getByParent($id, \Pimcore\Model\User $user = null)
    {
        // if no user passed take authenticated user
        if (!$user) {
            $user = \Pimcore\Tool\Authentication::authenticateSession();
        }

        $uiConfig = new self();

        try {
            $uiConfig = $uiConfig->getDao()->getByParent($id, $user);
        } catch (\Exception $ex) {
        }

        return $uiConfig;
    }

    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $userId;

    /**
     * @var int
     */
    protected $parentId;

    /**
     * @var string
     */
    protected $sort;

    /**
     * @var string
     */
    protected $order;

    public function __construct()
    {
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
    public function getUserId()
    {
        return $this->userId;
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
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * @param int $parentId
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;
    }

    /**
     * @return string
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * @param string $sort
     */
    public function setSort($sort)
    {
        $availableSortOptions = Helper::getAssetSortOptions();

        if ($sort == null) {
            $this->sort = null;
        } else {
            foreach ($availableSortOptions as $field) {
                if ($field['criteria'] == $sort) {
                    $this->sort = $sort;
                    break;
                }
            }
        }
    }

    /**
     * @return string
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param string $order
     */
    public function setOrder($order)
    {
        $this->order = $order == null ? null : Helper::getValidOrderCriteria($order);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     *
     * @link http://php.net/manual/en/iterator.current.php
     *
     * @return mixed Can return any type.
     */
    public function current()
    {
        $this->getItems();

        return current($this->items);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     *
     * @link http://php.net/manual/en/iterator.next.php
     *
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->getItems();
        next($this->items);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     *
     * @link http://php.net/manual/en/iterator.key.php
     *
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        $this->getItems();

        return key($this->items);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     *
     * @link http://php.net/manual/en/iterator.valid.php
     *
     * @return bool The return value will be casted to boolean and then evaluated.
     *       Returns true on success or false on failure.
     */
    public function valid()
    {
        $this->getItems();

        return key($this->items) !== null;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     *
     * @link http://php.net/manual/en/iterator.rewind.php
     *
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->getItems();
        reset($this->items);
    }

    /**
     * @param int $parentId
     * @param string[] $params
     * @param \Pimcore\Model\User|null $user
     *
     * @return null|UIConfiguration
     */
    public static function createOrUpdate($parentId, $params, \Pimcore\Model\User $user = null)
    {
        if (!$user) {
            $user = \Pimcore\Tool\Authentication::authenticateSession();
        }

        if (!$parentId) {
            return null;
        }

        $sort = $params['sort'];
        $order = $params['order'];
        $reset = $params['reset'];

        $uiConfig = self::getByParent($parentId, $user);

        if (!$uiConfig) {
            $uiConfig = new self();
            $uiConfig->setParentId($parentId);
            $uiConfig->setUserId($user->getId());
        }

        if ($sort) {
            $uiConfig->setSort($sort);
        }
        if ($order) {
            $uiConfig->setOrder($order);
        }

        if ($reset) {
            $uiConfig->resetParam($reset);
        }

        if ($uiConfig->getUserId() && $uiConfig->getParentId()) {
            $uiConfig->save();
        }

        return $uiConfig;
    }

    /**
     * @param $param
     */
    public function resetParam($param)
    {
        $setter = 'set' . ucfirst($param);

        if (method_exists($this, $setter)) {
            $this->$setter(null);
        }
    }
}
