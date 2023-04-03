<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Dam\Filter;

use Carbon\Carbon;
use Pimcore\Bundle\DamBundle\Model\Asset;

class Modify extends AbstractFilter
{
    public function __construct()
    {
        $this->setId('modify');
        $this->setName('Modify');
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->getParam('filter_from') != '' || $this->getParam('filter_till') != '';
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return 'glyphicon glyphicon-calendar';
    }

    /**
     * @return bool
     */
    public function hasFrontend()
    {
        return true;
    }

    /**
     * @param Asset\Listing $list
     *
     * @return void
     */
    public function apply(Asset\Listing $list)
    {
        $from = $this->getParam('filter_from') ? (Carbon::parse($this->getParam('filter_from')))->getTimestamp() : null;
        $till = $this->getParam('filter_till') ? (Carbon::parse($this->getParam('filter_till')))->getTimestamp() : null;
        if ($from || $till) {
            $list->addConditionParam($from && $till
                    ? sprintf('modificationDate between %d AND %d', $from, $till)
                    :
                    ($from
                        ? sprintf('modificationDate >= %d', $from)
                        : sprintf('modificationDate <= %d', $till)
                    ), '');
        }
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return [
            'filter_from' => $this->getParam('filter_from'),
            'filter_till' => $this->getParam('filter_till')
        ];
    }
}
