<?php

/**
 * Pimcore DAM
 *
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Dam\Filter;

use Pimcore\Bundle\DamBundle\Model\Asset;

class Type extends AbstractFilter
{
    public function __construct()
    {
        $this->setId('type');
        $this->setName('Type');
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return is_iterable($this->getParam('filter_type')) ? count($this->getParam('filter_type')) > 0 : false;
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return 'glyphicon glyphicon-picture';
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
        // filter type
        $filterType = $this->getParam('filter_type');
        if ($filterType) {
            $tmp = [];
            foreach ($filterType as $type) {
                $tmp[] = $list->quote($type);
            }
            $list->addConditionParam(sprintf('type IN(%s)', implode(',', $tmp)), '');
        }
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return [
            'filter_type' => $this->getParam('filter_type'),
        ];
    }
}
