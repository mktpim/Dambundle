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
<div id="filter-config-<?= $filter->getId() ?>" class="panel panel-default panel-filter <?= $filter->isActive() ? 'active' : 'hide' ?>" data-filter="productCollection">
    <div class="panel-heading">
        <h3 class="panel-title"><span class="glyphicon glyphicon-shopping-cart"></span> <?= ('Product Collection') ?></h3>

        <button type="button" class="btn btn-default btn-xs remove-filter"><span class="glyphicon glyphicon-remove"></span></button>
    </div>
    <div class="panel-body row">

        <?php $arrProductCollection = $filter->getProductCollectionOptions(); ?>

        <?php
        $filterProductCollection = $filter->getParam('filter_product_collection', []);
        foreach ($arrProductCollection as $productCollection) :
            $active = in_array(strtolower($productCollection), $filterProductCollection);
        ?>
            <div class="col-sm-6">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="filter_product_collection[]" value="<?= strtolower($productCollection) ?>" <?= $active ? 'checked' : '' ?>>
                        <?= ($productCollection) ?>
                    </label>
                </div>
            </div>
        <?php endforeach ?>
    </div>
</div>