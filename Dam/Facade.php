<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Dam;

use Pimcore\Bundle\DamBundle\Model;
use Pimcore\Bundle\DamBundle\PimcoreDamBundle;
use Pimcore\Model\User;
use Pimcore\Tool\Authentication;
use Pimcore\Db;

class Facade
{
    /**
     * @var User
     */
    protected static $user;

    /**
     * @return User|null
     */
    public static function getUser()
    {
        if (!self::$user) {
            self::$user = \Pimcore\Tool\Authentication::authenticateSession();
        }

        return self::$user;
    }

    /**
     * @param User $user
     */
    public static function setUser(User $user)
    {
        self::$user = $user;
    }

    /**
     * Get a list of collection.
     *
     * If a permission parameter is passed, get all collections the current user has the rights to
     * access in this scope. If the $exclusively parameter is set to true only those collection are
     * returned for which to user has the concrete passed permission. If $permission === false
     * permissions will be complete ignored.
     *
     * @param string $permission
     *
     * @return Model\Collection\Listing|Model\Collection[]
     */
    public static function getCollectionList($permission = Collection\Permissions::VIEW)
    {
        $list = new Model\Collection\Listing();

        if ($permission !== false) {
            $list = $list->filterByPermission($permission);
        }

        $list->setOrderKey('sort');
        $list->setOrder('asc');

        return $list;
    }

    /**
     * gibt eine liste von assets zurück auf die der aktuelle user zugriff hat
     *
     * @return \Pimcore\Bundle\DamBundle\Model\Asset\Listing
     */
    public static function getAssetList($params = null)
    {
        $list = new Model\Asset\Listing();
        $list->init();

        return $list;
    }

    /**
     * @return Model\Search\Listing
     */
    public static function getSearchList()
    {
        // init
        $list = new Model\Search\Listing();
        $list->addConditionParam(sprintf('userId = %d', self::getUser()->getId()), '');

        return $list;
    }

    /**
     * @return User|null
     */
    public static function getPublicUser($publicUser)
    {
        // $user = User::getByName(PimcoreDamBundle::getConfig()['backend']['user']['guest']);
        $user = User::getByName($publicUser);
        return Authentication::isValidUser($user) && $user->isAllowed('plugin_dam')
            ? $user
            : null;
    }

    /**
     * @return null|string
     */
    public static function getFallbackLanguage(string $locale)
    {
        // set locale
        $fallbackLanguage = '';

        foreach (\Pimcore\Tool::getValidLanguages() as $language) {
            if ($language == $locale) {
                $fallbackLanguage = $language;
            }
        }

        if (!$fallbackLanguage) {
            foreach (\Pimcore\Tool::getValidLanguages() as $language) {
                if ($language == $locale) {
                    $fallbackLanguage = $language;
                    break;
                }
            }

            if (!$fallbackLanguage) {
                foreach (\Pimcore\Tool::getValidLanguages() as $language) {
                    $_language = substr($language, 0, 2);
                    if ($_language == $locale) {
                        $fallbackLanguage = $language;
                        break;
                    }
                }
            }
        }

        if (!$fallbackLanguage) {
            $fallbackLanguage = \Pimcore\Tool::getDefaultLanguage();
        }

        return $fallbackLanguage;
    }
}
