<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */
/** @var \Pimcore\Bundle\DamBundle\Dam\Filter\AbstractFilter $filter
 */
$filter = $this->filter;
?>
<div id="filter-config-<?= $filter->getId() ?>" class="panel panel-default panel-filter <?= $filter->isActive() ? 'active' : 'hide' ?>" data-filter="flooringType">
    <div class="panel-heading">
        <h3 class="panel-title"><span class="glyphicon glyphicon-file"></span> <?= ('Flooring Type') ?></h3>

        <button type="button" class="btn btn-default btn-xs remove-filter"><span class="glyphicon glyphicon-remove"></span></button>
    </div>
    <div class="panel-body row">
        <?php $arrFlooringType = $filter->getFlooringTypeOptions(); ?>
        <?php
        $filterFlooringType = $filter->getParam('filter_flooring_type', []);
        foreach ($arrFlooringType as $flooringType) :
            $active = in_array(strtolower($flooringType), $filterFlooringType);
        ?>
            <div class="col-sm-6">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="filter_flooring_type[]" value="<?= strtolower($flooringType) ?>" <?= $active ? 'checked' : '' ?>>
                        <?= ($flooringType) ?>
                    </label>
                </div>
            </div>
        <?php endforeach ?>
    </div>
</div>