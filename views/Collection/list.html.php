<?php
/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

/**
 * @var \Pimcore\Templating\PhpEngine $this
 * @var \Pimcore\Templating\PhpEngine $view
 * @var \Pimcore\Templating\GlobalVariables $app
 * @var \Pimcore\Bundle\DamBundle\Model\Collection[] $list
 * @var \Pimcore\Bundle\DamBundle\Model\Collection[] $listShared
 */
$list = $this->list;
$listShared = $this->shared;

$saveAction = $this->path('pimcore_dam_collection_save');

$this->headScript()->appendFile('/bundles/pimcoredam/vendor/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js');
$this->headScript()->appendFile('/bundles/pimcoredam/vendor/jquery-plugins/jquery.tablesorter.min.js');

$this->headLink()->appendStylesheet('/bundles/pimcoredam/vendor/jquery-plugins/jquery.tablesorter.css');
$this->headLink()->appendStylesheet('/bundles/pimcoredam/vendor/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css');

$this->headScript()->appendFile('/bundles/pimcoredam/vendor/selectize-js/selectize.min.js');
$this->headLink()->appendStylesheet('/bundles/pimcoredam/vendor/selectize-js/selectize.css');

$this->headScript()->appendFile('/bundles/pimcoredam/vendor/jquery-ui-1.11.0.custom/jquery-ui.js');

$this->extend('PimcoreDamBundle:layout:default.html.php');
?>

<div class="container-fluid collection-list">
    <div class="row">
        <div class="col-sm-10">
            <div class="panel-group" id="collection-list-acc" role="tablist" aria-multiselectable="true">
                <form action="<?= $saveAction ?>" method="post">
                    <div class="panel panel-default">
                        <div class="panel-heading" role="tab" id="col-list-heading">
                            <a role="button" class="acc-trigger" data-toggle="collapse" data-parent="#accordion" href="#col-list-collapse" aria-expanded="true" aria-controls="col-list-collapse">
                                <h3 class="panel-title">
                                    <span class="trigger-icon glyphicon glyphicon-collapse-up"></span>
                                    <?= $this->translate('dam.list.collection') ?>
                                </h3>
                            </a>
                        </div>
                        <div id="col-list-collapse" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="col-list-heading">
                            <div class="panel-body">
                                <table class="table table-striped table-sortable tablesorter">
                                    <thead>
                                        <tr>
                                            <th><?= $this->translate('dam.name.collection') ?></th>
                                            <th width="150"><?= $this->translate('dam.color.collection') ?></th>
                                            <th class="text-center"><?= $this->translate('dam.collection.assets-assigned') ?></th>
                                            <th width="200"></th>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr class="add-collection">
                                            <td>
                                                <input type="text" class="form-control" name="name[]" value="" placeholder="<?= $this->translate('dam.add-new.collection') ?>" />
                                            </td>
                                            <td>
                                                <div class="input-group color-picker">
                                                    <input type="text" name="color[]" placeholder="#000000" class="form-control" />

                                                    <span class="input-group-addon">
                                                        <i></i>
                                                    </span>
                                                </div>
                                            </td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                    <tbody>
                                        <?php foreach ($list as $collection): ?>
                                            <tr>
                                                <td>
                                                    <input type="text" class="form-control" name="name[<?= $collection->getId() ?>]" value="<?= $collection->getName() ?>" />
                                                </td>
                                                <td>
                                                    <div class="input-group color-picker">
                                                        <input type="text" name="color[<?= $collection->getId() ?>]" value="<?= $collection->getColor() ?>" class="form-control" />

                                                        <span class="input-group-addon">
                                                            <i></i>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td class="asset-count text-center"><?= $collection->getAssignedCount() ?></td>
                                                <td class="action text-right">
                                                    <div class="btn-group">
                                                        <?php
                                                        // assets der kollektion anzeigen
                                                        $linkView = $this->path('pimcore_dam_asset_list', ['collection' => $collection->getId()]);
                                                        ?>
                                                        <a href="<?= $linkView ?>" class="btn btn-default" title="<?= $this->translate('dam.show.collection') ?>"><span class="glyphicon glyphicon-hdd"></span></a>

                                                        <?php
                                                        // freigeben für andere system user
                                                        if ($this->allowShare):
                                                            $linkShare = $this->path('pimcore_dam_collection_share', ['id' => $collection->getId()]);
                                                            ?>
                                                            <a href="<?= $linkShare ?>" data-action="share" class="btn btn-default" title="<?= $this->translate('dam.share.other-user') ?>"><span class="glyphicon glyphicon-share"></span></a>
                                                        <?php endif; ?>

                                                        <?php
                                                        // freigeben via token für externe
                                                        if ($this->allowShare):
                                                            $linkShare = $this->path('pimcore_dam_share_edit', ['collection' => $collection->getId()]);
                                                            ?>
                                                            <a href="<?= $linkShare ?>" data-action="share" class="btn btn-default" title="<?= $this->translate('dam.share.public') ?>"><span class="glyphicon glyphicon-share-alt"></span></a>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php
                                                    $linkDelete = $this->path('pimcore_dam_collection_delete', ['id' => $collection->getId()]);
                                                    ?>
                                                    <a class="btn btn-warning ajax-dialog" href="<?= $linkDelete ?>" title="<?= $this->translate('dam.delete.collection') ?>"><span class="glyphicon glyphicon-remove"></span></a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>

                            </div>
                            <div class="panel-footer text-right">
                                <div class="btn-group">
                                    <input type="submit" class="btn btn-primary" value="<?= $this->translate('dam.save.collection') ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <?php if (count($listShared) > 0): ?>
                <div class="panel panel-default">
                    <div class="panel-heading" role="tab" id="col-share-list-heading">
                        <a class="acc-trigger" role="button" data-toggle="collapse" data-parent="#accordion" href="#col-share-list-collapse" aria-expanded="true" aria-controls="col-shre-list-collapse">
                            <h3 class="panel-title">
                                <span class="trigger-icon glyphicon glyphicon-collapse-up"></span>
                                <?= $this->translate('dam.public.collection') ?>
                            </h3>
                        </a>
                    </div>
                    <div id="col-share-list-collapse" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="col-share-list-heading">
                        <div class="panel-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th><?= $this->translate('dam.name.collection') ?></th>
                                    <th><?= $this->translate('dam.amount') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($listShared as $collection): ?>
                                <tr>
                                    <td>
                                        <?php
                                        // assets der kollektion anzeigen
                                        $linkView = $this->path('pimcore_dam_asset_list', ['collection' => $collection->getId()]);
                                        ?>
                                        <?php if ($collection->hasColor()): ?>
                                            <span class="collection-bullet" style="background-color: <?= $collection->getColor() ?>;"></span>
                                        <?php endif; ?>
                                        <?= $collection->getName() ?>
                                    </td>
                                    <td>
                                        <?= $collection->getAssignedCount() ?>
                                    </td>
                                    <td class="text-right">
                                        <a href="<?= $linkView ?>" class="btn btn-default" title="<?= $this->translate('dam.show.collection') ?>">
                                            <span class="glyphicon glyphicon-hdd"></span>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    </div>
                </div>
            <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<script>
    <?php $this->headScript()->captureStart(); ?>
    $(function(){

        var dam = DAM.getInstance();
        dam.init();

        var $colAccordion = $('.acc-trigger');
        $colAccordion.on('click', function () {
            $(this).find('.trigger-icon')
                .toggleClass('glyphicon-collapse-up')
                .toggleClass('glyphicon-collapse-down');
        });


        $('.color-picker').colorpicker();

        var addNewLine = function () {

            var last = $('.add-collection:last-child input');

            if(last.val() != '')
            {
                var line = $(this).parents('.add-collection');
                var add = line.clone(false);
                add.find('input').val("").bind('change', addNewLine);

                add.find('.color-picker').colorpicker();

                line.after( add );
            }

        };

        $('.add-collection input').bind('change', addNewLine);


        // row sortable
        $('.table.table-sortable > tbody').sortable({
            items: '> tr',
            helper: 'clone',
            zIndex: 99999,
        });

        /* Get sorting criteria from input field if present */
        function nameSortCriteria(node) {
            var input = $(node).find('input');
            if(input.length > 0)
                return input.val();
            return node.innerText;
        }

        /* Enable sorting table by criteria */
        $('.tablesorter').tablesorter({
            textExtraction: nameSortCriteria,
//            sortList: [[0,0]], // default sorting first column asc
            headers: {
                1: {
                    sorter: false
                },
                3: {
                    sorter: false
                }
            }
        });

    });
    <?php $this->headScript()->captureEnd(); ?>
</script>
