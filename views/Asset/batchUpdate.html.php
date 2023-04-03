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
 * @var \Pimcore\Bundle\DamBundle\Dam\Item\AbstractItem[] $listAssets
 * @var string $backUrl
 */
$listAssets = $this->listAssets;
$urlSave = $this->path('pimcore_dam_asset_batchupdate');

// get editable fields
$editableMetaFields = \Pimcore\Bundle\DamBundle\Dam\Helper::getEditableMetaFields(null);

$this->extend('PimcoreDamBundle:layout:default.html.php');
?>

<form id="form-metadata" action="<?= $urlSave ?>?selectedItems=<?= $this->getParam('selectedItems') ?>" method="post" enctype="application/x-www-form-urlencoded">

    <div class="container-fluid" style="background-color: #f5f5f5; margin-bottom: 15px; box-shadow: 0 0 3px #000;">

        <div class="col-sm-12 panel-upload">

            <a href="<?= $backUrl ?>" type="button" class="btn btn-default back-button" style="position: absolute; bottom: 10px; right: 10px;">
                <span class="glyphicon glyphicon-chevron-left"></span> <?= $this->translate('dam.back') ?>
            </a>

            <div class="row" style="padding: 10px 0 20px 0">
                <div class="col col-sm-1">
                    <h4 style="margin-left: 0; padding-left: 0"><?= $this->translate('dam.batch-update') ?></h4>
                </div>
                <div class="col-sm-4">
                    <div class="row">
                        <div class="col col-sm-12">
                            <button id="btn-save" class="btn btn-primary" style="width: 100%"><?= $this->translate('dam.save') ?></button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tabbable tabs-left row">
                <!-- Nav tabs -->
                <ul class="nav nav-tabs col-sm-2 col-md-1" role="tablist">
                    <li class="active"><a href="#metadata" role="tab" data-toggle="tab"><?= $this->translate('dam.meta-data') ?></a></li>
                    <li><a href="#collection" role="tab" data-toggle="tab"><?= $this->translate('dam.assign.system-data') ?></a></li>
                </ul>

                <!-- Tab panes -->
                <div class="tab-content col-sm-6 col-md-4" style="padding-top: 6px;">

                    <!-- metadata panel -->
                    <div class="tab-pane active" id="metadata">
                        <?php if ($editableMetaFields): ?>
                            <div class="form-horizontal" id="metadata-batch-input">
                                <?php
                                $item = new \Pimcore\Model\Asset();
                                $item->setId(0);
                                foreach ($editableMetaFields as $name => $field) {
                                    echo $this->template('PimcoreDamBundle:Asset:shared/edit-metafield.html.php', [
                                        'batch' => true, 'name' => $name, 'asset' => $item, 'field' => $field, 'language' => $this->language, 'ignoreRequirementAttribute' => true
                                    ]);
                                }
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- collection panel -->
                    <div class="tab-pane" id="collection">
                        <div class="row form-horizontal" style="padding-right: 15px">

                            <div class=" form-group form-group-sm">
                                <!-- relocate -->
                                <label class="col-sm-3 control-label"><?= $this->translate('dam.asset.relocate') ?></label>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <span class="input-group-btn">
                                            <button data-action="asset-target" class="btn btn-default" type="button"><span class="glyphicon glyphicon-folder-open"></span></button>
                                            <input class="hide" type="hidden" name="batch$pid" />
                                        </span>
                                        <input type="text" class="form-control" value="" readonly />
                                    </div>
                                </div>
                            </div>

                            <div class="form-group form-group-sm">
                                <label class="col-sm-3 control-label"><?= $this->translate('dam.list.collection') ?></label>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <?= $this->template('PimcoreDamBundle:Asset:shared/select-method.html.php', ['name' => 'batch$collection$selectmethod'])?>
                                        <input data-collection-assign name="batch$collection" />
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <?php foreach ($listAssets as $item): ?>
        <div class="col-sm-12 col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title"><span class="<?= $item->getIcon() ?>"></span> <?= $item->getCaption() ?></h4>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <!-- preview -->
                        <div class="col-sm-3">
                            <?= $this->template('PimcoreDamBundle:Asset:shared/asset-preview.html.php', [
                                'item' => $item, 'size' => 150, 'class' => 'thumbnail'
                            ]) ?>
                        </div>

                        <!-- metadata -->
                        <div class="col-sm-9">
                            <?php if ($editableMetaFields): ?>
                                <div class="form-horizontal">
                                    <?php
                                    foreach ($editableMetaFields as $name => $field) {
                                        echo $this->template('PimcoreDamBundle:Asset:shared/edit-metafield.html.php', [
                                            'name' => $name, 'label' => $name, 'field' => $field, 'language' => $this->language, 'asset' => $item->getAsset()
                                        ]);
                                    }
                                    ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="panel-footer">
                    <div class="row">
                        <div class="col-sm-6">
                            <!-- collection -->
                            <div class="input-group form-group">
                                <span class="input-group-addon">
                                    <span class="glyphicon glyphicon-paperclip"></span>
                                </span>
                                <?php
                                $collectionList = $item->getCollectionList();
                                $ids = [];
                                foreach ($collectionList as $collection) {
                                    $ids[] = $collection->getId();
                                }
                                ?>
                                <input type="text" class="form-control" name="<?= sprintf('item$%s$collection', $item->getId()) ?>" data-collection-assign value="<?= implode(',', $ids) ?>">
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <!-- relocate -->
                            <div class="input-group">
                                <span class="input-group-btn">
                                    <button data-action="asset-target" class="btn btn-default" type="button"><span class="glyphicon glyphicon-folder-open"></span></button>
                                    <input class="hide" type="hidden" name="<?= sprintf('item$%s$pid', $item->getId()) ?>" />
                                </span>
                                <input type="text" class="form-control" value="<?= $item->getAsset()->getPath() ?>" readonly>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    <?php endforeach; ?>
</form>

<script>
    <?php $this->headScript()->captureStart(); ?>

    // init
    var dam = DAM.getInstance();
    dam.init();


    /**
     * metafield lang switcher
     * @param       element   lang dropdown element
     * @param bool  global    sprache global umschalten
     */
    var metafieldSwitchLang = function (element) {

        // change label
        var lang = element.data('language');
        var label = element.parents('.dropdown-menu').prev('.dropdown-toggle');
        label.html( lang.toUpperCase() + ' <span class="caret"></span>');

        // swtich fields
        var container = element.parents('.form-group');
        container.find('.input-group-element[data-language]:not([data-language=""])').addClass('hide');
        container.find('.input-group-element[data-language=' + lang + ']').removeClass('hide');

        //change label of all field specific language switches
        if (typeof global != 'undefined' && global) {
            container.find('.dropdown-toggle').html(lang.toUpperCase() + ' <span class="caret"></span>');
        }

    };

    $('#main .dropdown-menu a').click(function (e) {
        e.preventDefault();

        metafieldSwitchLang( $(this) );
    });


    // enable date input type
    dam.requireCalendar(function () {

        $('.input-group.date').datetimepicker({
            language: "<?= $this->language ?>",
            pickTime: false
        });

    });

    $('body').on('click', '.js-select-method-btn', function (e) {
        e.preventDefault();
        var $inputGroupParent = $(this).closest('.selectable-method.input-group-btn');
        var newValue = $(this).data('method');
        var newText = $(this).text();

        $inputGroupParent.find('.select-method-label').text(newText);
        $inputGroupParent.find('.select-method-input').val(newValue);

    });

    <?php
    // keyword assign
    $selectable = \Pimcore\Bundle\DamBundle\PimcoreDamBundle::getConfig()['backend']['metadata']['selectable'];
    if ($selectable): ?>
    dam.requireSelectize(function () {
        <?php
        foreach ($selectable as $field => $metafield):
            $optgroup = $metafield['group'];
            ?>

        $(':input[name*=metadata\\$<?= $field ?>\\$]').not('.non-selectize').removeClass('form-control').selectize({
            plugins: ['add_value_to_options', <?= preg_replace('#(\w+)#', "'\\1',", $metafield['plugin']) ?>],
            persist: false,
            create: true,
            maxItems: null,
            valueField: 'name',
            labelField: 'name',
            searchField: ['name'],

            optgroups: [
                <?php foreach ($optgroup as $group): ?>
                {name: '<?= $group['title'] ?>'},
                <?php endforeach; ?>
            ],
            optgroupField: 'group',
            optgroupLabelField: 'name',
            optgroupValueField: 'name',
            optgroupOrder: [
                <?php foreach ($optgroup as $group): ?>
                '<?= $group['label'] ?>',
                <?php endforeach; ?>
            ],

            options: [
                <?php foreach ($optgroup as $group): ?>
                    <?php foreach ($group['field'] as $name): ?>
                    {name: '<?= $name ?>', color: '#999', group: '<?= $group['title'] ?>'},
                    <?php endforeach; ?>
                <?php endforeach; ?>
            ],
            render: {
                item: function(item, escape) {
                    return '<span class="item">' +
                        '<span class="label label-default">' + escape(item.name) + '</span>' +
                        '</span>';
                },
                option: function(item, escape) {
                    return '<div><span class="item">' +
                        '<span class="label label-default">' + escape(item.name) + '</span>' +
                        '</span></div>';
                }
            }
        });
        <?php endforeach; ?>
    });
    <?php endif; ?>


    dam.requireSelectize(function () {

        // enable collection assign
        $('[data-collection-assign]').removeClass('form-control').selectize({
            plugins: ['remove_button_colored'],
            persist: false,
            maxItems: null,
            valueField: 'id',
            labelField: 'name',
            searchField: ['name'],
            options: [
                <?php foreach ($this->availableCollections as $collection): ?>
                {
                    id: <?= $collection->getId() ?>,
                    name: '<?= $collection->getName() ?>',
                    color: '<?= $collection->getColor() ?: '#999' ?>'
                },
                <?php endforeach; ?>
            ],
            render: {
                item: function (item, escape) {
                    return '<span class="collection item">' +
                        '<span class="label name" style="background-color: ' + item.color + ';">' + escape(item.name) + '</span>' +
                        '</span>';
                },
                option: function (item, escape) {
                    return '<div><span class="collection item">' +
                        '<span class="label name" style="background-color: ' + item.color + ';">' + escape(item.name) + '</span>' +
                        '</span></div>';
                }
            }
        });
    });


    // enable date input type
    dam.requireParsley(function () {

        var parsleyOptions = {
            errorClass: 'has-error',
            classHandler : function( _el ){
                return _el.$element.closest('.form-group');
            }
        };

        var form = $('#form-metadata').parsley( parsleyOptions );
    });


    // enable relocate to folder
    $('#main [data-action=asset-target]').click(function () {

        // init
        var input = $(this).next('input');
        var viewPath = $(this).parent().next();


        dam.requireFolderTree(function (dialog) {

            dialog.on('click', '.folderTree a', function (e) {
                e.preventDefault();

                input.val( $(this).data('id') );
                viewPath.val( $(this).data('path') );

                dialog.modal('hide');

            });

        });

    });


    // warning when user is about to leave the page and some data not saved yet
    var warning = false;
    $(':input').on('change', function () {

        if(warning == false)
        {
            $(window).on('beforeunload', function () {

                return "Geänderte daten wurden noch nicht gespeichert";

            });

            $('#btn-save').on('click', function () {

                $(window).off('beforeunload');

            });

            warning = true;
        }

    });


    <?php $this->headScript()->captureEnd(); ?>
</script>
