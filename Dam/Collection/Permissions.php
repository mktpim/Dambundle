<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Dam\Collection;

class Permissions
{
    const ADMIN = 'admin';
    const EDIT = 'edit';
    const VIEW = 'view';

    private static $params = [
        self::EDIT => 'edit',
        self::ADMIN => 'userId',
        self::VIEW => 'share'
    ];

    public static function getAll()
    {
        return [self::EDIT, self::VIEW, self::ADMIN];
    }

    public static function exists($permission)
    {
        return in_array($permission, self::getAll());
    }

    public static function getSetterFor($permission)
    {
        return 'set' . ucfirst(self::$params[$permission]);
    }

    public static function getGetterFor($permission)
    {
        return 'get' . ucfirst(self::$params[$permission]);
    }
}
