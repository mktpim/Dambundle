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
<div id="filter-config-<?= $filter->getId() ?>" class="panel panel-default panel-filter <?= $filter->isActive() ? 'active' : 'hide' ?>" data-filter="subCollection">
    <div class="panel-heading">
        <h3 class="panel-title"><span class="glyphicon glyphicon-paperclip"></span> <?= ('Subcollection') ?></h3>

        <button type="button" class="btn btn-default btn-xs remove-filter"><span class="glyphicon glyphicon-remove"></span></button>
    </div>
    <div class="panel-body row">
        <?php $arrSubCollection = $filter->getSubCollectionOptions(); ?>
        <?php
        $filterSubCollection = $filter->getParam('filter_subcollection', []);
        foreach ($arrSubCollection as $subCollection) :
            $active = in_array(strtolower($subCollection), $filterSubCollection);
        ?>
            <div class="col-sm-6">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="filter_subcollection[]" value="<?= strtolower($subCollection) ?>" <?= $active ? 'checked' : '' ?>>
                        <?= ($subCollection) ?>
                    </label>
                </div>
            </div>
        <?php endforeach ?>
    </div>
</div>