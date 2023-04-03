<?php
/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

/**
 * @var \Pimcore\Bundle\DamBundle\Dam\Filter\AbstractFilter $filter
 */
$this->headScript()->appendFile('/bundles/pimcoredam/vendor/bootstrap-treeview/bootstrap-treeview.js');
$this->headLink()->appendStylesheet('/bundles/pimcoredam/vendor/bootstrap-treeview/bootstrap-treeview.min.css');

/**
 * @var \Pimcore\Bundle\DamBundle\Dam\Filter\Tag $filter
 */
$filter = $this->filter;
?>
<div id="filter-config-<?= $filter->getId() ?>" class="panel panel-default panel-filter <?= $filter->isActive() ? 'active' : 'hide' ?>" data-filter="tag">
    <div class="panel-heading">
        <h3 class="panel-title"><span class="glyphicon glyphicon-tags"></span> <?= $this->translate('dam.filter.tag.label') ?></h3>
        <button type="button" class="btn btn-default btn-xs remove-filter"><span class="glyphicon glyphicon-remove"></span></button>
    </div>
    <div class="panel-body">

        <?php if (sizeof($this->tagTree)) {
    ?>
            <div class="checkbox mt0">
                <label>
                    <input type="checkbox" name="considerChildTags" value="true"<?php if ($filter->getParam('considerChildTags') == 'true') {
        ?> checked="checked"<?php
    } ?>> <?= $this->translate('dam.filter.tag.consider-child-tags') ?>
                </label>
            </div>
        <?php
} else {
        ?>
            <?=$this->translate('dam.filter.tag.no-tags-found')?>
        <?php
    } ?>

        <div id="filter-tag-tree"></div>

        <div id="js-filter-tag-values">
            <?php if ($tagIds = $filter->getParam('filter_tag')) {
        ?>
                <?php foreach ($tagIds as $id) {
            ?>
                    <input type="hidden" name="filter_tag[]" class="js-filter-tag-value" value="<?=$id?>"/>
                <?php
        } ?>
            <?php
    }?>
        </div>
    </div>
</div>


<script>
    <?php $this->headScript()->captureStart(); ?>

    $(function(){

        var $tree = $('#filter-tag-tree');
        $tree.treeview({
            data: <?=json_encode($this->tagTree)?>
            , showCheckbox: true
            , levels: 1
            , highlightSelected: false
        });

        $tree.on('nodeUnchecked', function(e, node){
            $('.js-filter-tag-value[value='+node.id+']').remove();
        });

        $tree.on('nodeChecked', function(e, node){
            $('#js-filter-tag-values').append('<input type="hidden" name="filter_tag[]" class="js-filter-tag-value" value="' + node.id + '"/>');
        });

        $tree.on('nodeSelected', function(e, node){
            $tree.treeview('toggleNodeChecked', [ node.nodeId ]);
        });
    });

    <?php $this->headScript()->captureEnd(); ?>
</script>
