<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle;

use Pimcore\Db;
use Pimcore\Extension\Bundle\Installer\AbstractInstaller;
use Pimcore\Model\User\Permission\Definition;

class Installer extends AbstractInstaller
{
    public function install()
    {
        $this->installThumbConfigs();
        $this->installDatabaseTables();
        $this->installPermissions();
        $this->installTranslations();

        return true;
    }

    public function isInstalled()
    {
        $db = Db::get();
        try {
            $db->fetchOne('SELECT id FROM plugin_dam_collection LIMIT 1');

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function canBeInstalled()
    {
        return !$this->isInstalled();
    }

    public function canBeUninstalled()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function needsReloadAfterInstall()
    {
        return true;
    }

    public function uninstall()
    {
        // drop dam tables
        Db::get()->query('DROP TABLE IF EXISTS `plugin_dam_collection`');
        Db::get()->query('DROP TABLE IF EXISTS `plugin_dam_search`');
        Db::get()->query('DROP TABLE IF EXISTS `plugin_dam_share`');
        Db::get()->query('DROP TABLE IF EXISTS `plugin_dam_terms`');
        Db::get()->query('DROP TABLE IF EXISTS `plugin_dam_ui_configuration`');

        // remove permission definitions
        Db::get()->query('DELETE FROM `users_permission_definitions` WHERE `key` like "plugin_dam%" ');

    }

    public function installPermissions()
    {
        // user can access DAM Frontend in general
        Definition::create('plugin_dam');

        // user can access collections
        Definition::create('plugin_dam_collection');

        // user can share content
        Definition::create('plugin_dam_share');

        // user can edit DAM settings
        Definition::create('plugin_dam_settings');
    }

    public function installThumbConfigs()
    {
        // init
        $sourceInstallPath = __DIR__ . '/../../../../install';

        // image pipelines
        $images = file_get_contents($sourceInstallPath . '/imagepipelines/images.json');
        $images = json_decode($images, true);

        foreach ($images as $name => $values) {
            $thumbnail = \Pimcore\Model\Asset\Image\Thumbnail\Config::getByName($name);

            if (!$thumbnail) {
                $thumbnail = new \Pimcore\Model\Asset\Image\Thumbnail\Config();
            }

            $thumbnail->setName($name);
            $thumbnail->setValues($values);
            $thumbnail->save();
        }

        // video pipelines
        $videos = file_get_contents($sourceInstallPath . '/videopipelines/videos.json');
        $videos = json_decode($videos, true);

        foreach ($videos as $name => $values) {
            $thumbnail = \Pimcore\Model\Asset\Video\Thumbnail\Config::getByName($name);

            if (!$thumbnail) {
                $thumbnail = new \Pimcore\Model\Asset\Video\Thumbnail\Config();
            }

            $thumbnail->setName($name);
            $thumbnail->setValues($values);
            $thumbnail->save();
        }
    }

    public function installDatabaseTables()
    {
        // create collection table
        Db::get()->query(<<<SQL
            CREATE TABLE `plugin_dam_collection` (
                `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `userId` INT(10) NOT NULL,
                `creationDate` INT(10) NOT NULL,
                `name` CHAR(50) NULL DEFAULT NULL,
                `color` CHAR(10) NULL DEFAULT NULL,
                `assigned` TEXT NULL,
                `share` TEXT NULL,
                `edit` TEXT NULL,
                `sort` INT(10) UNSIGNED NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                INDEX `userId` (`userId`)
            )
            COLLATE='utf8_general_ci'
            ENGINE=InnoDB
            AUTO_INCREMENT=0;
SQL
        );

        // create saved search table
        Db::get()->query(<<<SQL
            CREATE TABLE `plugin_dam_search` (
                `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `createDate` INT(10) NULL DEFAULT NULL,
                `userId` INT(10) UNSIGNED NULL DEFAULT NULL,
                `name` CHAR(255) NULL DEFAULT NULL,
                `config` TEXT NULL,
                PRIMARY KEY (`id`),
                INDEX `userId` (`userId`)
            )
            COLLATE='utf8_general_ci'
            ENGINE=InnoDB
            AUTO_INCREMENT=0;
SQL
        );

        // create share table
        Db::get()->query(<<<SQL
            CREATE TABLE `plugin_dam_share` (
                `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'share id',
                `userId` INT(10) NOT NULL,
                `token` CHAR(10) NOT NULL,
                `creationDate` INT(11) NOT NULL,
                `expire` INT(11) NULL DEFAULT NULL COMMENT 'valid until',
                `type` CHAR(50) NULL DEFAULT NULL COMMENT 'asset or collection',
                `referenceIds` CHAR(255) NOT NULL,
                `config` TEXT NULL COMMENT 'json configuration',
                `name` CHAR(50) NULL DEFAULT NULL,
                `termsComment` TEXT,
                PRIMARY KEY (`id`),
                UNIQUE INDEX `token` (`token`)
            )
            COLLATE='utf8_general_ci'
            ENGINE=InnoDB
            AUTO_INCREMENT=0;
SQL
        );

        // create terms table
        Db::get()->query(<<<SQL
            CREATE TABLE `plugin_dam_terms` (
              `lang` char(5) NOT NULL,
              `terms` text,
              PRIMARY KEY (`lang`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQL
        );

        // create user interface configuration table
        Db::get()->query(<<<SQL
           CREATE TABLE `plugin_dam_ui_configuration` (
              `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
              `parentId` INT(10) UNSIGNED NULL DEFAULT NULL,
              `userId` INT(10) UNSIGNED NULL DEFAULT NULL,
              `order` CHAR(100) NULL DEFAULT NULL COMMENT 'asc/desc',
              `sort` CHAR(100) NULL DEFAULT NULL COMMENT 'sorting criteria',
              PRIMARY KEY (`id`),
              UNIQUE INDEX `parentIdUserId` (`parentId`,`userId`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQL
        );
    }

    public function installTranslations()
    {
        \Pimcore\Model\Translation\Website::importTranslationsFromFile(__DIR__ . '/../../../../install/frontend.csv', true, ['de', 'en']);
    }
}
