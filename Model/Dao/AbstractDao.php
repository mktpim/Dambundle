<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Model\Dao;

abstract class AbstractDao extends \Pimcore\Model\Dao\AbstractDao
{
    protected $validColumns = [];

    /**
     * Get the valid columns from the database
     *
     * @return void
     */
    public function init()
    {
        $tableName = $this->getTableName();
        $this->validColumns = $this->getValidTableColumns($tableName);
    }

    protected function getValidStorageValues()
    {
        $data = [];
        foreach ($this->model->getObjectVars() as $key => $value) {
            if (in_array($key, $this->validColumns)) {
                if (is_array($value) || is_object($value)) {
                    if (is_object($value)) {
                        $value = get_class($value);
                    } else {
                        $value = json_encode($value);
                    }
                } elseif (is_bool($value)) {
                    $value = (int)$value;
                }
                $data[$key] = $value;
            }
        }
        if (!$data['creationDate']) {
            $data['creationDate'] = time();
        }
        if (!$data['modificationDate']) {
            $data['modificationDate'] = time();
        }

        return $data;
    }

    public function getById($id)
    {
        $data = $this->db->fetchRow('SELECT * FROM ' . $this->getTableName() . ' WHERE id= ' . (int)$id);
        if (!$data) {
            return null;
        }
        $data['id'] = (int)$data['id'];
        $this->model->setValues($data);

        return $this->model;
    }
}
