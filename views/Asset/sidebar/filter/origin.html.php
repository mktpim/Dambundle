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
<div id="filter-config-<?= $filter->getId() ?>" class="panel panel-default panel-filter <?= $filter->isActive() ? 'active' : 'hide' ?>" data-filter="origin">
    <div class="panel-heading">
        <h3 class="panel-title"><span class="glyphicon glyphicon-globe"></span> <?= ('Origin') ?></h3>

        <button type="button" class="btn btn-default btn-xs remove-filter"><span class="glyphicon glyphicon-remove"></span></button>
    </div>
    <div class="panel-body row">
        <?php $arrOrigin = $filter->getOriginOptions(); ?>
        <?php
        $filterOrigin = $filter->getParam('filter_origin', []);
        foreach ($arrOrigin as $origin) :
            $active = in_array(strtolower($origin), $filterOrigin);
        ?>
            <div class="col-sm-6">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="filter_origin[]" value="<?= strtolower($origin) ?>" <?= $active ? 'checked' : '' ?>>
                        <?= ($origin) ?>
                    </label>
                </div>
            </div>
        <?php endforeach ?>
    </div>
</div>