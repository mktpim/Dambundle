<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Model\Terms;

use Pimcore\Bundle\DamBundle\Model\Dao\AbstractDao;

class Dao extends AbstractDao
{
    const TABLE_NAME = 'plugin_dam_terms';

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    /**
     * @param string $lang
     *
     * @return null
     */
    public function getByLang(string $lang)
    {
        $classRaw = $this->db->fetchRow('SELECT * FROM ' . self::TABLE_NAME . ' WHERE lang=' . $this->db->quote($lang));
        if (empty($classRaw)) {
            return null;
        }
        $this->assignVariablesToModel($classRaw);

        return $this->model;
    }

    /**
     * @return $this
     */
    public function save()
    {
        $data = $this->getValidStorageValues();
        unset($data['modificationDate']);
        unset($data['creationDate']);

        $this->db->insertOrUpdate($this->getTableName(), $data);

        return $this->getByLang($this->model->getLang());
    }
}
