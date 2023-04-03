<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Model\Collection;

use Pimcore\Bundle\DamBundle\Model\Collection;

class Listing extends \Pimcore\Model\Listing\AbstractListing implements \Iterator, \Countable
{
    /**
     * @var Collection[]
     */
    protected $list = [];

    /**
     * @var bool
     */
    protected $validate;

    /**
     * @var array
     */
    public $objects = [];

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
        return in_array($key, ['priority', 'name', 'sort']);
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
            $this->list = $this->load();
        }

        return $this->list;
    }

    /**
     * Add condition to list which filters ba passed permission.
     *
     * If no user is passed authenticated user is used.
     *
     * @param $permission
     * @param \Pimcore\Model\User|null $user
     *
     * @return $this
     */
    public function filterByPermission($permission, \Pimcore\Model\User $user = null)
    {

        // if no user passed take authenticated user
        if (!$user) {
            $user = \Pimcore\Tool\Authentication::authenticateSession();
        }

        if ($condition = \Pimcore\Bundle\DamBundle\Dam\Collection\Accessor::getListCondition($permission, $user)) {
            $this->addConditionParam($condition);
        }

        return $this;
    }

    public function count()
    {
        return count($this->getList());
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
        $this->getList();

        return current($this->list);
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
        $this->getList();
        next($this->list);
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
        $this->getList();

        return key($this->list);
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
        $this->getList();

        return key($this->list) !== null;
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
        $this->getList();
        reset($this->list);
    }
}
