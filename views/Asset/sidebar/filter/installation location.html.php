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
<div id="filter-config-<?= $filter->getId() ?>" class="panel panel-default panel-filter <?= $filter->isActive() ? 'active' : 'hide' ?>" data-filter="installationLocation">
    <div class="panel-heading">
        <h3 class="panel-title"><span class="glyphicon glyphicon-home"></span> <?= ('Installation Location') ?></h3>

        <button type="button" class="btn btn-default btn-xs remove-filter"><span class="glyphicon glyphicon-remove"></span></button>
    </div>
    <div class="panel-body row">
        <?php $arrInstallationLocation = $filter->getInstallationLocationOptions(); ?>
        <?php
        $filterInstallationLocation = $filter->getParam('filter_installation_location', []);
        foreach ($arrInstallationLocation as $installationLocation) :
            $active = in_array(strtolower($installationLocation), $filterInstallationLocation);
        ?>
            <div class="col-sm-6">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="filter_installation_location[]" value="<?= strtolower($installationLocation) ?>" <?= $active ? 'checked' : '' ?>>
                        <?= ($installationLocation) ?>
                    </label>
                </div>
            </div>
        <?php endforeach ?>
    </div>
</div>