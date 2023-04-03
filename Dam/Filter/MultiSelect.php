<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Dam\Filter;

use Pimcore\Bundle\DamBundle\Model\Asset;
use Pimcore\Bundle\DamBundle\PimcoreDamBundle;
use Pimcore\Templating\PhpEngine;

class MultiSelect extends AbstractFilter
{
    /**
     * @return bool
     */
    public function isActive()
    {
        return array_key_exists('filter_' . $this->getId(), $this->getParams());
    }

    /**
     * @return bool
     */
    public function hasFrontend()
    {
        return true;
    }

    /**
     * @param PhpEngine $view
     *
     * @return mixed
     */
    public function getFrontend(PhpEngine $view)
    {
        $config = PimcoreDamBundle::getConfig()['filters'][$this->getId()];

        return $view->template('PimcoreDamBundle:Asset:sidebar/filter/multiselect.html.php', ['filter' => $this, 'singleColumn' => (bool) $config['singleColumn']]);
    }

    public function getIcon()
    {
        $config = PimcoreDamBundle::getConfig()['filters'][$this->getId()];
        if ($config && $config->icon) {
            return (string)$config->icon;
        }

        return 'glyphicon glyphicon-filter';
    }

    /**
     * @param Asset\Listing $list
     *
     * @return void
     */
    public function apply(Asset\Listing $list)
    {
        // filter collection
        $values = $this->getParam('filter_' . $this->getId());
        $values = is_array($values) ? $values : explode(',', $values);

        if ($values) {
            foreach ($values as $key => $value) {
                $values[$key] = $list->quote($value);
            }
            $dataCondition = '(data in ('.implode(',', $values).'))';

            $config = PimcoreDamBundle::getConfig()['filters'][$this->getId()];
            $metadataName = $config && $config['metadataName'] ? (string)$config['metadataName'] : $this->getId();

            $list->addConditionParam("( id in (select cid from assets_metadata where name = '" . $metadataName . "' and " . $dataCondition .'))');
        }
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return [
            'filter_' . $this->getId() => $this->getParam('filter_' . $this->getId())
        ];
    }

    public function getOptions()
    {
        $config = PimcoreDamBundle::getConfig()['filters'][$this->getId()];
        if ($config && !empty($config["options"])) {
            return $config["options"];
        }
        return [];
    }
}
