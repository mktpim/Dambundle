<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Model;

/**
 * Class Terms
 *
 * @package Pimcore\Bundle\DamBundle\Model
 *
 * @method Terms\Dao getDao()
 */
class Terms extends \Pimcore\Model\AbstractModel
{
    /**
     * @param string $lang
     *
     * @return $this
     */
    public static function getByLang(string $lang)
    {
        $self = new self();

        return $self->getDao()->getByLang($lang);
    }

    /**
     * @var string
     */
    protected $lang;

    /**
     * @var string
     */
    protected $terms;

    /**
     * @return string
     */
    public function getLang(): string
    {
        return $this->lang;
    }

    /**
     * @param string $lang
     *
     * @return $this
     */
    public function setLang(string $lang)
    {
        $this->lang = $lang;

        return $this;
    }

    /**
     * @return string
     */
    public function getTerms(): string
    {
        return $this->terms;
    }

    /**
     * @param string $terms
     *
     * @return $this
     */
    public function setTerms(string $terms)
    {
        $this->terms = $terms;

        return $this;
    }
}
