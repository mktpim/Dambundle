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
<div id="filter-config-<?= $filter->getId() ?>" class="panel panel-default panel-filter <?= $filter->isActive() ? 'active' : 'hide' ?>" data-filter="roomscene">
    <div class="panel-heading">
        <h3 class="panel-title"><span class="glyphicon glyphicon-file"></span> <?= ('Room Scene') ?></h3>

        <button type="button" class="btn btn-default btn-xs remove-filter"><span class="glyphicon glyphicon-remove"></span></button>
    </div>
    <div class="panel-body row">
        <?php $arrProperties1 = $filter->getProperty1Options(); ?>
        <?php
        $filterProperties1 = $filter->getParam('filter_roomscene', []);
        foreach ($arrProperties1 as $roomscene) :
            $active = in_array(strtolower($roomscene), $filterProperties1);
        ?>
            <div class="col-sm-6">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="filter_roomscene[]" value="<?= strtolower($roomscene) ?>" <?= $active ? 'checked' : '' ?>>
                        <?= ($roomscene) ?>
                    </label>
                </div>
            </div>
        <?php endforeach ?>
    </div>
</div>