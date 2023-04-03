<?php
/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */
/**
 * @var \Pimcore\Bundle\DamBundle\Dam\Filter\AbstractFilter $filter
 */
$filter = $this->filter;
?>
<div id="filter-config-<?= $filter->getId() ?>" class="panel panel-default panel-filter <?= $filter->isActive() ? 'active' : 'hide' ?>" data-filter="<?= $filter->getId() ?>">
    <div class="panel-heading">
        <h3 class="panel-title"><span class="<?=$filter->getIcon()?>"></span> <?= $this->translate('dam.filter.'.$filter->getId().'.label') ?></h3>

        <button type="button" class="btn btn-default btn-xs remove-filter"><span class="glyphicon glyphicon-remove"></span></button>
    </div>
    <div class="panel-body row">
        <?php
        $arrTypes = $filter->getOptions();
        ?>

        <?php
        $filterTypes = $filter->getParam('filter_'.$filter->getId(), []);
        foreach ($arrTypes as $type):
            $active = in_array(strtolower($type), $filterTypes);
            ?>
            <div class="<?=$this->singleColumn ? 'col-sm-12' : 'col-sm-6'?>">
                <div class="checkbox<?=$this->singleColumn ? ' mb5 mt5' : ''?>">
                    <label>
                        <input type="checkbox" name="filter_<?= $filter->getId() ?>[]" value="<?= strtolower($type) ?>" <?= $active ? 'checked' : '' ?>>
                        <?= $this->translate('dam.filter.' . $filter->getId() . '.value-' . $type) ?>
                    </label>
                </div>
            </div>
        <?php endforeach ?>
    </div>
</div>