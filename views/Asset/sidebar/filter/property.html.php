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
<div id="filter-config-<?= $filter->getId() ?>" class="panel panel-default panel-filter <?= $filter->isActive() ? 'active' : 'hide' ?>" data-filter="property">
    <div class="panel-heading">
        <h3 class="panel-title"><span class="glyphicon glyphicon-file"></span> <?= ('Image Property') ?></h3>

        <button type="button" class="btn btn-default btn-xs remove-filter"><span class="glyphicon glyphicon-remove"></span></button>
    </div>
    <div class="panel-body row">
        <?php $arrProperties = $filter->getPropertyOptions(); ?>
        <?php
            $filterProperties = $filter->getParam('filter_property', []);
                foreach ($arrProperties as $property):
                    $active = in_array(strtolower($property), $filterProperties);
        ?>
            <div class="col-sm-6">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="filter_property[]" value="<?= strtolower($property) ?>" <?= $active ? 'checked' : '' ?>>
                        <?= ( $property) ?>
                    </label>
                </div>
            </div>
        <?php endforeach ?>
    </div>
</div>