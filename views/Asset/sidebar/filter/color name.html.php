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
<div id="filter-config-<?= $filter->getId() ?>" class="panel panel-default panel-filter <?= $filter->isActive() ? 'active' : 'hide' ?>" data-filter="colorName">
    <div class="panel-heading">
        <h3 class="panel-title"><span class="glyphicon glyphicon-pencil"></span> <?= ('Color Name') ?></h3>

        <button type="button" class="btn btn-default btn-xs remove-filter"><span class="glyphicon glyphicon-remove"></span></button>
    </div>
    <div class="panel-body row">
        <?php $arrColorName = $filter->getColorNameOptions(); ?>
        <?php
        $filterColorName = $filter->getParam('filter_color_name', []);
        foreach ($arrColorName as $colorName) :
            $active = in_array(strtolower($colorName), $filterColorName);
        ?>
            <div class="col-sm-6">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="filter_color_name[]" value="<?= strtolower($colorName) ?>" <?= $active ? 'checked' : '' ?>>
                        <?= ($colorName) ?>
                    </label>
                </div>
            </div>
        <?php endforeach ?>
    </div>
</div>