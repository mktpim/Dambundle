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
<div id="filter-config-<?= $filter->getId() ?>" class="panel panel-default panel-filter <?= $filter->isActive() ? 'active' : 'hide' ?>" data-filter="pattern">
    <div class="panel-heading">
        <h3 class="panel-title"><span class="glyphicon glyphicon-retweet"></span> <?= ('Pattern') ?></h3>

        <button type="button" class="btn btn-default btn-xs remove-filter"><span class="glyphicon glyphicon-remove"></span></button>
    </div>
    <div class="panel-body row">
        <?php $arrPattern = $filter->getPatternOptions(); ?>
        <?php
        $filterPattern = $filter->getParam('filter_pattern', []);
        foreach ($arrPattern as $pattern) :
            $active = in_array(strtolower($pattern), $filterPattern);
        ?>
            <div class="col-sm-6">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="filter_pattern[]" value="<?= strtolower($pattern) ?>" <?= $active ? 'checked' : '' ?>>
                        <?= ($pattern) ?>
                    </label>
                </div>
            </div>
        <?php endforeach ?>
    </div>
</div>