<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Model\Search\Listing;

use Pimcore\Bundle\DamBundle\Model\Search;
use Pimcore\Model;

class Dao extends Model\Listing\Dao\AbstractDao
{
    protected function getTableName()
    {
        return Search\Dao::TABLE_NAME;
    }

    public function load()
    {
        $sql = 'SELECT id FROM ' . $this->getTableName() . $this->getCondition() . $this->getOrder() . $this->getOffsetLimit();
        $ids = $this->db->fetchCol($sql, $this->model->getConditionVariables());

        $items = [];
        foreach ($ids as $id) {
            $items[] = Search::getById($id);
        }

        return $items;
    }

    public function getTotalCount()
    {
        return (int) $this->db->fetchOne('SELECT COUNT(*) as amount FROM ' . $this->getTableName() . ' '. $this->getCondition(), $this->model->getConditionVariables());
    }
}
