<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Dam\Collection;

use Pimcore\Bundle\DamBundle\Model\Collection;
use Pimcore\Bundle\DamBundle\PimcoreDamBundle;
use Pimcore\Model\User;
use Pimcore\Model\User\Role;

class Accessor
{
    /**
     * Get condition for querying collection list by permission.
     *
     * @param string              $permission
     * @param \Pimcore\Model\User $user
     *
     * @return string
     */
    public static function getListCondition($permission, \Pimcore\Model\User $user)
    {
        $condition = [];

        //if admin condition include only owned permissions
        $condition[] = sprintf('userId = %1s', $user->getId());

        if ($permission == Permissions::EDIT) {
            $condition[] = sprintf('edit LIKE %1s', "'%," . $user->getId() . ",%'");
        }
        if ($permission == Permissions::VIEW) {
            $condition[] = sprintf('edit LIKE %1s', "'%," . $user->getId() . ",%'");
            $condition[] = sprintf('share LIKE %1$s', "'%," . $user->getId() . ",%'");
        }

        foreach ($user->getRoles() as $role) {
            if ($permission == Permissions::EDIT) {
                $condition[] = sprintf('edit LIKE %1s', "'%," . $role . ",%'");
            }
            if ($permission == Permissions::VIEW) {
                $condition[] = sprintf('edit LIKE %1s', "'%," . $role . ",%'");
                $condition[] = sprintf('share LIKE %1$s', "'%," . $role . ",%'");
            }
        }

        return '(' .implode(' OR ', $condition) . ')';
    }

    /**
     * Check if a collection allows for a specific permission concerning an user.
     * The functionality is hierarchical, so a user with admin rights has also view and edit rights.
     *
     * @param Collection $collection
     * @param $user
     * @param $permission
     *
     * @return bool
     */
    public static function check(Collection $collection, \Pimcore\Model\User\AbstractUser $user, $permission)
    {
        switch ($permission) {
            case Permissions::ADMIN: {
                return $collection->getUserId() == $user->getId();
            }
            case Permissions::EDIT: {

                $allowed = self::check($collection, $user, Permissions::ADMIN) || in_array($user->getId(), $collection->getEdit(true));

                if ($user instanceof \Pimcore\Model\User) {
                    foreach ($user->getRoles() as $role) {
                        $allowed = $allowed || self::check($collection, $user, Permissions::ADMIN) || in_array($role, $collection->getEdit(true));
                    }
                }

                return $allowed;

            }
            case Permissions::VIEW: {
                $allowed = self::check($collection, $user, Permissions::EDIT || in_array($user->getId(), $collection->getShare(true)));

                if ($user instanceof \Pimcore\Model\User) {
                    foreach ($user->getRoles() as $role) {
                        $allowed = $allowed || self::check($collection, $user, Permissions::EDIT) || in_array($role, $collection->getEdit(true));
                    }
                }

                return $allowed;
            }
        }

        return false;
    }

    /**
     * @return User\Listing|User[]
     */
    public static function createShareUserList()
    {
        // condition for users with groups having DAM permission
        $condition = [];

        $rolesList = self::createShareUserRoleList();
        $roles = $rolesList->getRoles();

        foreach ($roles as $role) {
            $condition[] = "CONCAT(',', roles, ',') LIKE '%," . $role->getId() . ",%'";
        }

        // get available user
        $list = new User\Listing();

        $condition[] = 'admin = 1';
        $list->addConditionParam("((CONCAT(',', permissions, ',') LIKE ? ) OR " . implode(' OR ', $condition) . ')', '%,plugin_dam,%');

        // exclude hidden user
        $hideUser = PimcoreDamBundle::getConfig()['backend']['collection']['share']['user']['hide'];
        foreach ($hideUser as $user) {
            $list->addConditionParam(sprintf('name != %s', $list->quote($user)));
        }

        return $list;
    }

    /**
     * Returns all user roles that have permission on DAM
     *
     * @return Role\Listing|Role[]
     */
    public static function createShareUserRoleList()
    {
        $rolesList = new Role\Listing();
        $rolesList->addConditionParam("CONCAT(',', permissions, ',') LIKE ?", '%,plugin_dam,%');

        // exclude hidden user
        $hideUser = PimcoreDamBundle::getConfig()['backend']['collection']['share']['user']['hide'];
        foreach ($hideUser as $user) {
            $rolesList->addConditionParam(sprintf('name != %s', $rolesList->quote($user)));
        }

        return $rolesList;
    }
}
