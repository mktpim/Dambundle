<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Model;

use Carbon\Carbon;
use Pimcore\Bundle\DamBundle\Dam\Item\AbstractItem;
use Pimcore\Bundle\DamBundle\PimcoreDamBundle;
use Pimcore\Model\User;

/**
 * Class Share
 *
 * @package Pimcore\Bundle\DamBundle\Model
 *
 * @method \Pimcore\Bundle\DamBundle\Model\Collection\Dao getDao()
 */
class Share extends \Pimcore\Model\AbstractModel implements \Iterator
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
     * @var string
     */
    protected $token;

    /**
     * @var int
     */
    protected $creationDate;

    /**
     * @var int
     */
    protected $expire;

    /**
     * @var string
     */
    protected $referenceIds;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $config;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $termsComment;

    /**
     * @var AbstractItem[]
     */
    protected $items;

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
     * @param string $referenceIds
     */
    public function setReferenceIds($referenceIds)
    {
        $this->referenceIds = $referenceIds;
    }

    /**
     * @return string
     */
    public function getReferenceIds()
    {
        return $this->referenceIds;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param int $expire
     */
    public function setExpire($expire)
    {
        $this->expire = $expire;
    }

    /**
     * @return int
     */
    public function getExpire()
    {
        return $this->expire;
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
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
    public function getTermsComment()
    {
        return $this->termsComment;
    }

    /**
     * @param string $termsComment
     */
    public function setTermsComment($termsComment)
    {
        $this->termsComment = $termsComment;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->userId = $user->getId();
    }

    /**
     * @return Carbon
     */
    public function getExpireDate()
    {
        return Carbon::createFromTimestamp($this->getExpire());
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return $this->getExpire() === null || $this->getExpireDate() && $this->getExpire() > time();
    }

    /**
     * @param \Pimcore\Templating\PhpEngine $view
     *
     * @return string
     */
    public function getLink(\Pimcore\Templating\PhpEngine $view)
    {
        $shareUrl = PimcoreDamBundle::getConfig()['frontend']['customize']['shareUrl'];

        $hostUrl = $shareUrl
            ? $shareUrl . $view->path('pimcore_dam_share_tokenlist', ['t' => $this->getToken()])
            : $view->url('pimcore_dam_share_tokenlist', ['t' => $this->getToken()])
        ;

        return  $hostUrl;
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
}
