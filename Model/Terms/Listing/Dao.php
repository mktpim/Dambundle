<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Model\Terms\Listing;

use Pimcore\Bundle\DamBundle\Model\Terms;
use Pimcore\Model;

class Dao extends Model\Listing\Dao\AbstractDao
{
    protected function getTableName()
    {
        return Terms\Dao::TABLE_NAME;
    }

    public function load()
    {
        $sql = 'SELECT lang FROM ' . $this->getTableName() . $this->getCondition() . $this->getOrder() . $this->getOffsetLimit();
        $ids = $this->db->fetchCol($sql, $this->model->getConditionVariables());

        $items = [];
        foreach ($ids as $id) {
            $items[] = Terms::getByLang($id);
        }

        return $items;
    }

    public function getTotalCount()
    {
        return (int) $this->db->fetchOne('SELECT COUNT(*) as amount FROM ' . $this->getTableName() . ' '. $this->getCondition(), $this->model->getConditionVariables());
    }
}
