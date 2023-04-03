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
<div id="filter-config-<?= $filter->getId() ?>" class="panel panel-default panel-filter <?= $filter->isActive() ? 'active' : 'hide' ?>" data-filter="styleCode">
    <div class="panel-heading">
        <h3 class="panel-title"><span class="glyphicon glyphicon-file"></span> <?= ('Style Code') ?></h3>

        <button type="button" class="btn btn-default btn-xs remove-filter"><span class="glyphicon glyphicon-remove"></span></button>
    </div>
    <div class="panel-body row">
        <?php $arrStyleCode = $filter->getStyleCodeOptions(); ?>
        <?php
            $filterStyleCode = $filter->getParam('filter_style_code', []);
            foreach ($arrStyleCode as $styleCode):
                $active = in_array(strtolower($styleCode), $filterStyleCode);
        ?>
            <div class="col-sm-6">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="filter_style_code[]" value="<?= strtolower($styleCode) ?>" <?= $active ? 'checked' : '' ?>>
                        <?= ( $styleCode) ?>
                    </label>
                </div>
            </div>
        <?php endforeach ?>
    </div>
</div>