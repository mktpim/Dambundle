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
<div id="filter-config-<?= $filter->getId() ?>" class="panel panel-default panel-filter <?= $filter->isActive() ? 'active' : 'hide' ?>" data-filter="modify">
    <div class="panel-heading">
        <h3 class="panel-title"><span class="glyphicon glyphicon-calendar"></span> <?= $this->translate('dam.filter.modify.label') ?></h3>
        <button type="button" class="btn btn-default btn-xs remove-filter"><span class="glyphicon glyphicon-remove"></span></button>
    </div>
    <div class="panel-body">
        <div class="form-group">
            <div class="input-group date">
                <input type="text" name="filter_from" class="form-control" value="<?= $filter->getParam('filter_from') ?>" />
                <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span>
                </span>
            </div>
        </div>
        <div>
            <div class="input-group date">
                <input type="text" name="filter_till" class="form-control" value="<?= $filter->getParam('filter_till') ?>" />
                <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span>
                </span>
            </div>
        </div>
    </div>
</div>
<script>
    <?php $this->headScript()->captureStart(); ?>

    $(function(){

        var dam = DAM.getInstance();
        dam.requireCalendar(function () {

            $('#filter-config-<?= $filter->getId() ?> .input-group.date > input').datetimepicker({
                language: '<?= $this->language ?>',
                pickTime: false
            });

        });

    });

    <?php $this->headScript()->captureEnd(); ?>
</script>