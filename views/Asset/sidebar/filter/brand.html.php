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
<div id="filter-config-<?= $filter->getId() ?>" class="panel panel-default panel-filter <?= $filter->isActive() ? 'active' : 'hide' ?>" data-filter="brand">
    <div class="panel-heading">
        <h3 class="panel-title"><span class="glyphicon glyphicon-th-large"></span> <?= ('Brand') ?></h3>

        <button type="button" class="btn btn-default btn-xs remove-filter"><span class="glyphicon glyphicon-remove"></span></button>
    </div>
    <div class="panel-body row">
        <?php $arrBrand = $filter->getBrandOptions(); ?>
        <?php
        $filterBrand = $filter->getParam('filter_brand', []);
        foreach ($arrBrand as $key => $brand) :
            $active = in_array($key, $filterBrand);
        ?>
            <div class="col-sm-6">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="filter_brand[]" value="<?= $key ?>" <?= $active ? 'checked' : '' ?>>
                        <?= ($brand) ?>
                    </label>
                </div>
            </div>
        <?php endforeach ?>
    </div>
</div>