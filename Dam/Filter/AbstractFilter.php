<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Dam\Filter;

use Pimcore\Bundle\DamBundle\Model\Asset;
use Pimcore\Templating\PhpEngine;

abstract class AbstractFilter
{
    /**
     * @var array
     */
    protected $params;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $fieldName;

    /**
     * @param $name
     *
     * @return AbstractFilter
     */
    public static function createInstance($name)
    {
        $class = '\\Pimcore\\Bundle\\DamBundle\\Dam\\Filter\\' . ucfirst($name);

        return new $class();
    }

    public function __construct()
    {
        $this->setId(get_class($this));
        $this->setName(get_class($this));
    }

    /**
     * @param array $params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param string $name
     * @param null   $default
     *
     * @return mixed
     */
    public function getParam($name, $default = null)
    {
        if (array_key_exists($name, $this->params)) {
            return $this->params[$name];
        } else {
            return $default;
        }
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
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
     * @return bool
     */
    abstract public function isActive();

    /**
     * @param Asset\Listing $list
     *
     * @return void
     */
    abstract public function apply(Asset\Listing $list);

    /**
     * @return bool
     */
    abstract public function hasFrontend();

    /**
     * @param PhpEngine $view
     *
     * @return string
     */
    public function getFrontend(PhpEngine $view)
    {
        return $view->template(sprintf('PimcoreDamBundle:Asset:sidebar/filter/%s.html.php', strtolower($this->getName())), ['filter' => $this]);
    }

    /**
     * @return null
     */
    public function getIcon()
    {
        return null;
    }

    /**
     * return config
     *
     * @return null|array
     */
    public function getConfig()
    {
        return null;
    }

    /**
     * @return string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * @param string $fieldName
     */
    public function setFieldName($fieldName)
    {
        $this->fieldName = $fieldName;
    }
}
