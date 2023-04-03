<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Model\Search;

use Pimcore\Bundle\DamBundle\Model\Dao\AbstractDao;

class Dao extends AbstractDao
{
    const TABLE_NAME = 'plugin_dam_search';

    public function getTableName()
    {
        return self::TABLE_NAME;
    }

    /**
     * Save object to database
     *
     * @return $this
     */
    public function save()
    {
        $data = $this->getValidStorageValues();
        unset($data['modificationDate']);
        $data['createDate'] = $data['creationDate'];
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
