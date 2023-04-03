<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Model;

use Pimcore\Bundle\DamBundle\Dam\Collection\Accessor;
use Pimcore\Bundle\DamBundle\Dam\Collection\Permissions;
use Pimcore\Bundle\DamBundle\Dam\Facade;
use Pimcore\Model\Asset;

/**
 * Class Collection
 *
 * @package Pimcore\Bundle\DamBundle\Model
 *
 * @method \Pimcore\Bundle\DamBundle\Model\Collection\Dao getDao()
 */
class Collection extends \Pimcore\Model\AbstractModel implements \Iterator
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
    protected $userId;

    /**
     * @var int
     */
    protected $creationDate;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $color;

    /**
     * @var string
     */
    protected $assigned;

    /**
     * @var string
     */
    protected $share;

    /**
     * @var string
     */
    protected $edit;

    /**
     * @var int
     */
    protected $sort;

    /**
     * @var Asset[]
     */
    protected $items = [];

    /**
     * @var int[]
     */
    protected $itemIds = [];

    /**
     * construct
     */
    public function __construct()
    {
        $this->setCreationDate(time());
    }

    /**
     * @param string $assigned
     */
    public function setAssigned($assigned)
    {
        $assigned = trim($assigned, ',');
        if ($assigned) {
            $currentUser = Facade::getUser();
            if($currentUser) {
                $assetList = Facade::getAssetList();
                $assetList->addConditionParam(sprintf('id IN(%s)', $assigned));
                $allowed = $assetList->loadIdList();
                foreach ($allowed as $id) {
                    $this->itemIds[$id] = $id;
                }
            } else {
                foreach (explode(',', $assigned) as $id) {
                    $this->itemIds[$id] = $id;
                }
            }
        }
    }

    public function getItems()
    {
        if ($this->items == null) {
            foreach ($this->itemIds as $id) {
                $asset = Asset::getById($id);
                if ($asset) {
                    $this->items[] = $asset;
                    $this->itemIds[$asset->getId()] = $asset->getId();
                }
            }
        }

        return $this->items;
    }

    public function getItemIds()
    {
        return $this->itemIds;
    }

    /**
     * @return string
     */
    public function getAssigned()
    {
        return ',' . implode(',', $this->itemIds)  . ',';
    }

    /**
     * @return int[]
     */
    public function getAssignedIds()
    {
        return array_values($this->itemIds);
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
     * @param string $color
     */
    public function setColor($color)
    {
        $this->color = $color;
    }

    /**
     * @return string
     */
    public function getColor()
    {
        return $this->color;
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
     * @return int
     */
    public function getAssignedCount()
    {
        return count($this->itemIds);
    }

    /**
     * @param string $share
     */
    public function setShare($share)
    {
        $this->share = $share;
    }

    /**
     * @param bool $getArray
     *
     * @return array|string
     */
    public function getShare($getArray = false)
    {
        if ($getArray) {
            return array_filter(explode(',', $this->share));
        }

        return $this->share;
    }

    /**
     * @param bool $getArray
     *
     * @return array|string
     */
    public function getEdit($getArray = false)
    {
        if ($getArray) {
            return array_filter(explode(',', $this->edit));
        }

        return $this->edit;
    }

    public function getUsers($getArray = false, $grouped = false)
    {
        if ($getArray) {
            if ($grouped) {
                return [
                    Permissions::EDIT => $this->getEdit(true),
                    Permissions::VIEW => $this->getShare(true)
                ];
            }

            return array_unique(array_merge($this->getEdit(true), $this->getShare(true)));
        }

        return rtrim($this->getEdit(), ',') . $this->getShare();
    }

    /**
     * @param $permission
     * @param array $userIds
     */
    public function setUsers($permission, $userIds)
    {
        switch ($permission) {
            case Permissions::EDIT:{
                $this->setEdit(',' . implode(',', $userIds) . ',');
                break;
            }
            case Permissions::VIEW:{
                $this->setShare(',' . implode(',', $userIds) . ',');
                break;
            }
        }
    }

    /**
     * @param string $edit
     */
    public function setEdit($edit)
    {
        $this->edit = $edit;
    }

    /**
     * @param int $sort
     */
    public function setSort($sort)
    {
        $this->sort = $sort;
    }

    /**
     * @return int
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * @return bool
     */
    public function hasColor()
    {
        return $this->getColor() != '';
    }

    /**
     * @param Asset $asset
     */
    public function append(Asset $asset)
    {
        // check for duplicates
        if (!$this->exists($asset)) {
            $this->getItems();
            $this->items[] = $asset;
            $this->itemIds[$asset->getId()] = $asset->getId();
        }
    }

    /**
     * @param Asset $asset
     */
    public function remove(Asset $asset)
    {
        unset($this->itemIds[$asset->getId()]);

        if ($items = $this->getItems()) {
            // check for duplicates
            foreach ($items as $key => $a) {
                if ($a->getId() === $asset->getId()) {
                    unset($this->items[$key]);
                }
            }
        }
    }

    /**
     * check if the given asset is already in the collection
     *
     * @param Asset $asset
     *
     * @return bool
     */
    public function exists(Asset $asset)
    {
        return isset($this->itemIds[$asset->getId()]);
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

    public function isEditable($user = null)
    {
        return $this->allows(Permissions::EDIT, $user);
    }

    public function isViewable($user = null)
    {
        return $this->allows(Permissions::VIEW, $user);
    }

    public function isOwnedBy($user = null)
    {
        return $this->allows(Permissions::ADMIN, $user);
    }

    /**
     * Check if the user has a specific permission for a collection.
     * If no user is passed the authenticated user is used.
     *
     * @param $permission - minimum permission
     * @param \Pimcore\Model\User $user
     *
     * @return bool
     */
    public function allows($permission, \Pimcore\Model\User\AbstractUser $user = null)
    {
        // valid permission type passed
        if (!Permissions::exists($permission)) {
            return false;
        }

        // if no user passed take authenticated user
        if (!$user) {
            $user = \Pimcore\Tool\Authentication::authenticateSession();
        }

        return Accessor::check($this, $user, $permission);
    }
}
