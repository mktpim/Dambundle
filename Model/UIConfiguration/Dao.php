<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Model\UIConfiguration;

use Pimcore\Bundle\DamBundle\Model\Dao\AbstractDao;

class Dao extends AbstractDao
{
    const TABLE_NAME = 'plugin_dam_ui_configuration';

    public function getTableName()
    {
        return self::TABLE_NAME;
    }

    /**
     * @param                     $id
     * @param \Pimcore\Model\User $user
     *
     * @return null|\Pimcore\Model\AbstractModel
     */
    public function getByParent($id, \Pimcore\Model\User $user)
    {
        $data = $this->db->fetchRow('SELECT * FROM ' . self::TABLE_NAME . ' WHERE parentId=' . $this->db->quote($id) .' AND userId = '. $this->db->quote($user->getId()));
        if (!$data) {
            return null;
        }
        $data['id'] = (int)$data['id'];
        $this->model->setValues($data);

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

        if (!$data['id']) {
            unset($data['id']);
            $this->db->insert($this->getTableName(), $data);
            $this->model->setId($this->db->lastInsertId($this->getTableName()));
        } else {
            $this->db->update($this->getTableName(), $data, ['id' => $this->model->getId()]);
        }

        return $this->getById($this->model->getId());
    }

    /**
     * Deletes object from database
     *
     * @return void
     */
    public function delete()
    {
        if ($this->model->getId()) {
            $this->db->query('DELETE FROM '.$this->getTableName().' where id='.$this->model->getId());
            $this->model = null;
        }
    }
}
