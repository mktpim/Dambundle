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
<div id="filter-config-<?= $filter->getId() ?>" class="panel panel-default panel-filter <?= $filter->isActive() ? 'active' : 'hide' ?>" data-filter="type">
    <div class="panel-heading">
        <h3 class="panel-title"><span class="glyphicon glyphicon-picture"></span> <?= $this->translate('dam.filter.type.label') ?></h3>

        <button type="button" class="btn btn-default btn-xs remove-filter"><span class="glyphicon glyphicon-remove"></span></button>
    </div>
    <div class="panel-body row">
        <?php
        $arrTypes = ['Image', 'Text', 'Audio', 'Video', 'Document', 'Archive'];
        ?>
        <?php
        $filterTypes = $filter->getParam('filter_type', []);
        foreach ($arrTypes as $type) :
            $active = in_array(strtolower($type), $filterTypes);
        ?>
            <div class="col-sm-6">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="filter_type[]" value="<?= strtolower($type) ?>" <?= $active ? 'checked' : '' ?>>
                        <?= $this->translate('dam.filter.type.type-' . $type) ?>
                    </label>
                </div>
            </div>
        <?php endforeach ?>
    </div>
</div>