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
<div id="filter-config-<?= $filter->getId() ?>" class="panel panel-default panel-filter <?= $filter->isActive() ? 'active' : 'hide' ?>" data-filter="product">
    <div class="panel-heading">
        <h3 class="panel-title"><span class="glyphicon glyphicon-book"></span> <?= ('Product') ?></h3>

        <button type="button" class="btn btn-default btn-xs remove-filter"><span class="glyphicon glyphicon-remove"></span></button>
    </div>
    <div class="panel-body row">
        <?php $arrProduct = $filter->getProductOptions(); ?>
        <?php
        $filterProduct = $filter->getParam('filter_product', []);
        foreach ($arrProduct as $product) :
            $active = in_array(strtolower($product), $filterProduct);
        ?>
            <div class="col-sm-6">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="filter_product[]" value="<?= strtolower($product) ?>" <?= $active ? 'checked' : '' ?>>
                        <?= ($product) ?>
                    </label>
                </div>
            </div>
        <?php endforeach ?>
    </div>
</div>