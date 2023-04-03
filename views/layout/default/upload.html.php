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
 */
$this->headScript()->appendFile('/bundles/pimcoredam/vendor/dropzone.min.js');
$this->headScript()->appendFile('/bundles/pimcoredam/js/upload.js');

$uploadUrl = $view->router()->path('pimcore_dam_asset_upload');

// get meta fields to show
$arrMetaFields = [];
$predefined = \Pimcore\Model\Metadata\Predefined\Listing::getByTargetType('asset', null);
foreach ($predefined as $_item) {
    /* @var \Pimcore\Model\Metadata\Predefined $_item */
    $arrMetaFields[$_item->getName()] = [
        'type' => $_item->getType(),
        'language' => ($_item->getLanguage() != ''),
        'config' => $_item->getConfig()
    ];
}

$arrMetaFields = \Pimcore\Bundle\DamBundle\Dam\Helper::processRequiredSettings($arrMetaFields);

$arrGroups = [];

$tmp = \Pimcore\Bundle\DamBundle\PimcoreDamBundle::getConfig()['backend']['upload']['metadata']['asset'];
if ($tmp) {
    foreach ($tmp as $field) {
        if (array_key_exists($field, $arrMetaFields)) {
            $listMetaDataFields[$field] = $arrMetaFields[$field];
        }
    }
} else {
    $listMetaDataFields = [];
}

/**
 * @param string $name
 * @param array $field
 */
$printMetaField = function ($name, array $field, $ignoreRequirementAttribute = false) {
    $arrLang = $field['language']
        ? \Pimcore\Tool::getValidLanguages()
        : [null];

    //avoid printing not editable attributes
    if (!($field['type'] == 'input' || $field['type'] == 'textarea' || $field['type'] == 'date' || $field['type'] == 'checkbox' || $field['type'] == 'select')) {
        return '';
    } ?>
    <div class="form-group form-group-sm">
        <label class="col-sm-4 control-label"><?= $this->translate('dam.metadata.label.'.$name) ?></label>
        <div class="col-sm-8">
            <?php if ($field['language']) {
        ?>
                <div class="input-group">
            <?php
    } ?>

            <?php foreach ($arrLang as $lang):
                $id = sprintf('metadata$%s$%s$%s', $name, $field['type'], $lang) . '[]';
    $hide = $lang != $this->language && $lang !== null;

    $class = $hide ? 'hide' : '';
    $class .= $lang ? ' input-group-element' : '';
    $class .= ' type-' . $field['type'];

    $requirementAttributes = '';
    if (!$ignoreRequirementAttribute && $field['required']) {
        $requirementAttributes = 'required';
    } elseif ($field['requiredWhen']) {
        $requirementAttributes = "data-parsley-validate-if-empty data-parsley-conditionalRequired='[" .
                        '"' . '#form-metadata' . '","' . $field['requiredWhen'] . '[]","' . $field['requiredWhenValue'] . '"' .
                        "]'";
    } ?>

                <div class="<?= $class ?>" data-language="<?= $lang ?>">
                    <?php if ($field['type'] == 'input'): ?>
                        <input name="<?= $id ?>" type="text" class="form-control js-form-control" <?= $requirementAttributes ?> >
                    <?php elseif ($field['type'] == 'textarea'): ?>
                        <textarea name="<?= $id ?>" class="form-control js-form-control" <?= $requirementAttributes ?> ></textarea>
                    <?php elseif ($field['type'] == 'date'): ?>
                        <div class="input-group date" style="max-width: 150px;">
                            <input name="<?= $id ?>" type="text" class="form-control js-form-control" data-format="DD.MM.YYYY" <?= $requirementAttributes ?> >
                            <span class="input-group-addon">
                                <span class="glyphicon glyphicon-calendar"></span>
                            </span>
                        </div>
                    <?php elseif ($field['type'] == 'checkbox'): ?>
                        <div class="checkbox">
                            <label>
                                <input class="js-form-control" name="<?= $id ?>" value="1" type="checkbox" <?= $requirementAttributes ?> />
                            </label>
                        </div>
                    <?php elseif ($field['type'] == 'select'): ?>
                        <select name="<?= $id ?>" class="form-control js-form-control" <?= $requirementAttributes ?> >
                            <option value=""></option>
                            <?php foreach (explode(',', $field['config']) as $option) {
        ?>
                                <option value="<?= $option ?>"><?= $option ?></option>
                            <?php
    } ?>
                        </select>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <?php if ($field['language']): ?>
                    <div class="input-group-btn">
                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><?= strtoupper($this->language) ?> <span class="caret"></span></button>
                        <ul class="language-switch dropdown-menu dropdown-menu-right" role="menu">
                            <?php foreach (\Pimcore\Tool::getValidLanguages() as $_lang): ?>
                                <li><a href="#" data-language="<?= $_lang ?>"><?= strtoupper($_lang) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php
} ?>

<form id="dropzone" class="hide active_ processing_ complete_" action="<?= $uploadUrl ?>" method="post" enctype="application/x-www-form-urlencoded">
    <input class="hide" type="hidden" name="pid" value="<?= $this->uploadParent ?>" />
    <input class="hide" type="hidden" name="archive" value="" />
    <input class="hide" type="file" name="asset" />

<div class="upload-info col-xs-12 collapsed">
    <button id="swapUploadTools" class="btn btn-default"><span class="glyphicon glyphicon-chevron-down"></span></button>

    <div class="col-sm-12 _col-md-10 _col-lg-8 panel-upload">

        <div class="tabbable tabs-left row">
            <!-- Nav tabs -->
            <ul class="nav nav-tabs col-sm-2 col-md-1" role="tablist">
                <li class="active"><a href="#upload" role="tab" data-toggle="tab"><?= $this->translate('dam.upload') ?></a></li>
                <li><a href="#collection" role="tab" data-toggle="tab"><?= $this->translate('dam.assign.collection') ?></a></li>
                <li><a href="#metadata" role="tab" data-toggle="tab"><?= $this->translate('dam.meta-data') ?></a></li>
                <li><a href="#rename" role="tab" data-toggle="tab"><?= $this->translate('dam.rename.upload') ?></a></li>
            </ul>

            <!-- Tab panes -->
            <div class="tab-content col-sm-6 col-md-4" style="padding-top: 6px;">
                <!-- upload panel -->
                <div class="tab-pane active" id="upload">
                    <nav class="navbar row" style="top: -12px;">
                        <div class="container-fluid row">
                            <p class="navbar-text"><?= $this->translate('dam.files.upload') ?>: <span id="upload-progress-totalFiles">0</span> (<span id="upload-progress-totalBytes">0</span>)</p>

                            <div class="navbar-form navbar-left">
                                <button class="btn btn-primary upload-start" data-action="upload"><span class="glyphicon glyphicon-upload"></span> <?= $this->translate('dam.start.upload') ?></button>
                                <button class="btn btn-danger upload-cancel" data-action="close"><span class="glyphicon glyphicon-remove"></span> <?= $this->translate('dam.cancel.dialog') ?></button>
                                <button class="btn btn-default upload-close" data-action="close"><?= $this->translate('dam.close.dialog') ?></button>
                                <?php
                                $urlBatchUpdate = $this->path('pimcore_dam_asset_batchupdate');
                                ?>
                                <button class="btn btn-default upload-edit" data-action="edit" data-href="<?= $urlBatchUpdate ?>"><?= $this->translate('dam.batch-edit') ?></button>
                            </div>
                        </div>
                    </nav>

                    <div class="row col-sm-12">
                        <div class="input-group">
                            <span class="input-group-btn">
                                <button id="upload-target" class="btn btn-default" type="button"><span class="glyphicon glyphicon-folder-open"></span></button>
                            </span>
                            <input type="text" class="form-control" value="<?= \Pimcore\Model\Asset\Folder::getById($this->uploadParent) ?>" readonly>
                        </div>
                    </div>

                </div>
                <!-- collection panel -->
                <div class="tab-pane" id="collection">
                    <div class="row">
                        <div class="col-sm-12">
                            <input id="uploadAssetCollection" name="collection" />
                        </div>
                    </div>
                </div>
                <!-- metadata panel -->
                <div class="tab-pane" id="metadata">
                    <div class="row">
                        <div class="col-sm-2">
                            <div class="btn-group" id="metadata-batch-language-switch">
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                    <?= strtoupper($this->language) ?> <span class="caret"></span>
                                </button>
                                <ul class="language-switch dropdown-menu" role="menu">
                                    <?php foreach (\Pimcore\Tool::getValidLanguages() as $lang): ?>
                                        <li><a href="#" data-language="<?= $lang ?>"><?= strtoupper($lang) ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>

                        <div class="col-sm-10">
                            <!-- metadata -->
                            <?php if ($listMetaDataFields): ?>
                                <div class="form-horizontal" id="metadata-batch-input">
                                    <?php
                                    foreach ($listMetaDataFields as $name => $field) {
                                        $printMetaField($name, $field, true);
                                    }
                                    ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <!-- rename panel -->
                <div class="tab-pane" id="rename">
                    <div class="row col-sm-12">
                        <div class="input-group" data-language="de">
                            <span class="input-group-addon"><?= $this->translate('dam.rename.upload') ?></span>
                            <input name="rename" type="text" class="form-control">
                            <span class="input-group-addon">-000</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="upload-progress">
    <!-- template -->
    <div class="upload-progress-template hide">
        <div class="col-sm-12 col-md-6 col-lg-4 dz-preview dz-file-preview">
            <div class="panel panel-default">
                <div class="panel-heading" style="z-index: 9; position: relative;">
                    <div class="progress progress-striped active">
                        <div class="progress-bar progress-bar-success" role="progressbar" data-dz-uploadprogress>
                        </div>
                    </div>

                    <span class="glyphicon glyphicon-ok pull-right"></span>
                    <span class="glyphicon glyphicon glyphicon-exclamation-sign pull-right"></span>
                    <h4 class="panel-title"><span class="glyphicon glyphicon-file"></span> <span data-dz-name></span> <small data-dz-size></small></h4>
                </div>
                <div class="panel-body" style="min-height: 100px;">
                    <div class="upload-preview">
                        <img data-dz-thumbnail src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" width="70" height="70" />
                    </div>

                    <div class="dz-error-message">
                        <span data-dz-errormessage></span>
                    </div>

                    <!-- metadata -->
                    <?php if ($listMetaDataFields): ?>
                        <div class="form-horizontal">
                            <?php
                            foreach ($listMetaDataFields as $name => $field) {
                                $printMetaField($name, $field);
                            }
                            ?>
                            <div class="form-group form-group-sm">
                                <div class="col-sm-4 control-label"><label><?= $this->t('dam.upload.collection-assign') ?></label></div>
                                <div class="col-sm-8">
                                    <input class="js-form-control uploadAssetCollection" name="metadata$collection$input$[]" />
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>

</div>
    <!--hallo welt-->
</form>

<script>
    <?php $this->headScript()->captureStart(); ?>

    // enable date input type
    dam.requireParsley(function () {

        var parsleyOptions = {
            errorClass: 'has-error',
            classHandler : function( _el ){
                return _el.$element.closest('.form-group');
            }
        };

        var form = $('#dropzone').parsley( parsleyOptions );
    });


    dam.requireSelectize(function () {
        configCollectionAssignOptions = {
            options: [
                <?php foreach (\Pimcore\Bundle\DamBundle\Dam\Facade::getCollectionList() as $collection): ?>
                {id: <?= $collection->getId() ?>, name: '<?= $collection->getName() ?>', color: '<?= $collection->getColor() ?: '#999' ?>'},
                <?php endforeach; ?>
            ]
        };

        // enable collection assign
        $('#uploadAssetCollection').selectize({
            plugins: ['remove_button_colored'],
            persist: false,
            maxItems: null,
            valueField: 'id',
            labelField: 'name',
            searchField: ['name'],
            options: configCollectionAssignOptions.options,
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


        <?php
        // enable predefined selectbox
        $selectable = \Pimcore\Bundle\DamBundle\PimcoreDamBundle::getConfig()['backend']['metadata']['selectable'];
        if ($selectable): ?>
            <?php
            foreach ($selectable as $field => $metafield):
                $optgroup = $metafield['group'];
                ?>
                configUploadPredefinedSelectbox['<?= $field ?>'] = {
                    plugins: [<?= preg_replace('#(\w+)#', "'\\1',", $metafield['plugin']) ?> ],
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
                };
            <?php endforeach; ?>


            // init upload panel
            var panel = $('#metadata-batch-input');
            enableUpload.initUploadPanel(panel);

        <?php endif; ?>

    });
    <?php $this->headScript()->captureEnd(); ?>
</script>