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
 * @var \Pimcore\Bundle\DamBundle\Dam\Item\AbstractItem $item
 */
$item = $this->item;

$editable = $this->editable;

$layout = $this->layout ?: 'PimcoreDamBundle:layout:default.html.php';
$this->extend($layout);

$this->headScript()->appendFile('/bundles/pimcoredam/vendor/dropzone.min.js');
$this->headLink()->appendStylesheet('/bundles/pimcoredam//css/asset-detail.css');
?>
<div class="toolbar">
    <?php
    if ($this->folderPath) {
        echo $this->template('PimcoreDamBundle:Asset:shared/folder-path.html.php', ['folderPath' => $this->folderPath]);
    }
    ?>

    <div class="options">

        <div class="btn-group">
            <?php
            $urlPrev = $view->router()->path('pimcore_dam_asset_detail', ['id' => $this->prevItem, 'o' => $this->offsetParam - 1, 'page' => $this->getParam('page'), 'pid' => $this->getParam('pid')]);
            $urlNext = $view->router()->path('pimcore_dam_asset_detail', ['id' => $this->nextItem, 'o' => $this->offsetParam + 1, 'page' => $this->getParam('page'), 'pid' => $this->getParam('pid')]);
            ?>
            <a href="<?= $urlPrev ?>" class="btn btn-default btn-sm <?= !$this->prevItem ? 'disabled' : '' ?>">
                <span class="glyphicon glyphicon-chevron-left"></span>
            </a>
            <a href="<?= $urlNext ?>" class="btn btn-default btn-sm <?= !$this->nextItem ? 'disabled' : '' ?>">
                <span class="glyphicon glyphicon-chevron-right"></span>
            </a>
        </div>

    </div>
</div>

<div class="container-fluid item-details">
    <h1>
        <?= $item->getCaption() ?> <small><span class="<?= $item->getIcon() ?>" title="<?= $item->getType()  ?>"></span> <?= $this->translate('dam.details') ?> <span class="fs16">(ID <?= $item->getId() ?>)</span></small>
    </h1>

    <div class="row">
        <!-- preview -->
        <div class="col-md-12 col-lg-5 ">

            <div class="thumbnail asset-preview">
                <?php
                $src = (string)$this->damThumbnail($item->hasThumbnail() ? $item->getThumbnail('dam_detail') : '');
                $srcFullScreen = (string)$this->damThumbnail($item->hasThumbnail() ? $item->getThumbnail('dam_fullscreen') : '');
                ?>
                <img class="type-<?= $item->getType() ?>" alt="<?= $item->getCaption() ?>" src="<?= $src ?>" style="width: 100%;" data-fullscreen="<?= $srcFullScreen ?>" />

                <?php if ($item->getType() == 'Video') :
                    $media = $item->getAsset();
                    /* @var Pimcore\Model\Asset\Video $media */
                    $thumb = $media->getThumbnail('dam_detail');
                    $poster = (string)$this->damThumbnail($media->getImageThumbnail('dam_detail'));

                    if (count($thumb['formats'])) : ?>
                        <video id="asset-video-preview" class="hide video-js vjs-default-skin" controls preload="auto" poster="<?= $poster ?>">
                            <?php foreach ($thumb['formats'] as $type => $url) : ?>
                                <source src="<?= $url ?>" type="video/<?= $type ?>" />
                            <?php endforeach; ?>
                        </video>
                    <?php elseif ($thumb['status'] == 'inprogress') :
                        $progress = 50;
                    ?>
                        <div class="progress progress-striped active">
                            <div class="progress-bar progress-bar-striped" role="progressbar" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100" style="width: <?= $progress ?>%">
                                <span><?= $this->translate('dam.asset.processing') ?> <?= $progress ?>%</span>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php elseif ($item->getType() == 'Audio') :
                    $linkDownload = $view->router()->path('pimcore_dam_asset_downloadoriginal', ['id' => $item->getId()]);
                ?>
                    <audio controls>
                        <source src="<?= $linkDownload ?>" type="<?= $item->getAsset()->getMimetype() ?>">
                    </audio>

                <?php endif; ?>

                <?php if (($thumbs = $item->getAlternateThumbnails('dam_detail'))) : ?>
                    <ol class="hide asset-preview-alternates">
                        <?php foreach ($thumbs as $thumb) : ?>
                            <li><img src="<?= $this->damThumbnail($thumb) ?>" width="250" height="250" /></li>
                        <?php endforeach; ?>
                    </ol>
                <?php endif; ?>
            </div>


            <div class="item-options text-center">
                <div class="btn-group">
                    <?php
                    // asset zu einer kollektion zuweisen
                    if ($item->getAllowCollection()) :
                        $linkCollection = $view->router()->path('pimcore_dam_collection_assign', ['asset' => $item->getId()]);
                    ?>
                        <a href="<?= $linkCollection ?>" data-action="collection" class="btn btn-default btn-lg" title="Assign Collection"><span class="glyphicon glyphicon-paperclip"></span></a>
                    <?php endif; ?>

                    <?php
                    // org. download
                    if ($item->getAllowDownload()) :
                        $linkDownload = $view->router()->path('pimcore_dam_asset_downloadoriginal', ['id' => $item->getId()]);
                    ?>
                        <a href="<?= $linkDownload ?>" class="btn btn-default btn-lg" target="_blank" title="<?= $this->translate('dam.asset.option.download') ?>"><span class="glyphicon glyphicon-download-alt"></span></a>
                    <?php endif; ?>

                    <?php
                    // freigabe
                    if ($item->getAllowShare()) :
                        $linkShare = $view->router()->path('pimcore_dam_share_edit', ['asset' => $item->getId()]);
                    ?>
                        <a href="<?= $linkShare ?>" data-action="share" class="btn btn-default btn-lg" title="<?= $this->translate('dam.asset.option.share') ?>"><span class="glyphicon glyphicon-share-alt"></span></a>
                    <?php endif; ?>

                    <?php
                    // verschieben
                    if ($item->getAllowDelete()) :
                        $linkRelocate = $view->router()->path('pimcore_dam_asset_relocate', [
                            'pid' => $item->getAsset()->getParentId(), 'id' => $item->getId()
                        ]);
                    ?>
                        <a href="<?= $linkRelocate ?>" data-action="relocate" class="btn btn-default btn-lg" title="<?= $this->translate('dam.asset.option.relocate') ?>"><span class="glyphicon glyphicon-folder-open"></span></a>
                    <?php endif; ?>

                    <?php
                    // edit
                    if ($this->imageEditorEnabled && $item->getAllowDelete() && $item->getType() == 'Image') :
                        $editorUrl = $this->path('pimcore_admin_asset_imageeditor', ['id' => $item->getId()]);
                    ?>
                        <a href="<?= $editorUrl ?>" target="_blank" class="btn btn-default btn-lg" title="<?= $this->translate('dam.asset.option.edit') ?>"><span class="glyphicon glyphicon-pencil"></span></a>
                    <?php endif; ?>

                    <?php
                    // replace
                    if ($item->getAllowDelete()) : ?>
                        <button data-action="replace" data-target="#replaceModal" class="btn btn-default btn-lg" title="<?= $this->translate('dam.asset.option.replace') ?>"><span class="glyphicon glyphicon-refresh"></span></button>
                    <?php endif; ?>

                    <?php
                    // delete
                    if ($item->getAllowDelete()) :
                        $linkDelete = $view->router()->path('pimcore_dam_asset_delete', ['id' => $item->getId()]);
                    ?>
                        <a href="<?= $linkDelete ?>" data-action="delete" class="btn btn-default btn-lg" title="<?= $this->translate('dam.asset.option.delete') ?>"><span class="glyphicon glyphicon-trash"></span></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>


        <!-- metadata -->
        <?php if (count($this->metafieldGroups) > 0) : ?>
            <div class="col-md-12 col-lg-7 item-metadata" id="item-metadata">
                <h3 class="hide"><?= $this->translate('dam.meta-data') ?></h3>

                <!-- Nav tabs -->
                <ul class="nav nav-tabs">
                    <?php foreach ($this->metafieldGroups as $group => $fields) : ?>
                        <li>
                            <a href="#<?= $group ?>" data-toggle="tab"><?= $this->translate('dam.metadata.group.' . $group) ?></a>
                        </li>
                    <?php endforeach; ?>

                    <li class="nav-language pull-right">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?= strtoupper($this->language) ?> <b class="caret"></b></a>
                        <ul class="dropdown-menu" style="right: 0; left: auto;">
                            <?php foreach (\Pimcore\Tool::getValidLanguages() as $lang) :
                                $linkLang = $this->path('pimcore_dam_asset_detail', ['lang' => $lang]);
                            ?>
                                <li><a href="<?= $linkLang ?>" data-language="<?= $lang ?>"><?= strtoupper($lang) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                </ul>

                <!-- Tab panes -->
                <?php
                $postURL = $view->router()->path('pimcore_dam_asset_detail', ['id' => $item->getId()]);
                ?>
                <form id="form-metadata" action="<?= $postURL ?>" class="form-horizontal" role="form" method="post" data-parsley-validate>
                    <div class="tab-content">
                        <br />
                        <?php foreach ($this->metafieldGroups as $group => $fields) : ?>
                            <div class="tab-pane" id="<?= $group ?>">
                                <?php foreach ($fields as $name => $field) :

                                    $arrLang = $field['language']
                                        ? \Pimcore\Tool::getValidLanguages()
                                        : [null];

                                    //avoid printing not editable attributes with no value set
                                    if (!($field['type'] == 'input' || $field['type'] == 'textarea' || $field['type'] == 'date' || $field['type'] == 'checkbox' || $field['type'] == 'select') || in_array($name, $this->readonlyMetaFields)) {
                                        $hasValue = false;
                                        foreach ($arrLang as $lang) {
                                            if ($item->getAsset()->getMetadata($name, $lang)) {
                                                $hasValue = true;
                                            }
                                        }

                                        if (!$hasValue) {
                                            continue;
                                        }
                                    }
                                ?>
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label"><?= $name ?></label>
                                        <div class="col-sm-9">
                                            <?php foreach ($arrLang as $lang) :
                                                $id = sprintf('metadata$%s$%s$%s', $name, $field['type'], $lang);
                                                $hide = $lang != $this->language && $lang !== null;
                                                $value = $item->getAsset()->getMetadata($name, $lang);

                                                $requirementAttributes = '';
                                                if ($field['required']) {
                                                    $requirementAttributes = 'required';
                                                } elseif ($field['requiredWhen']) {
                                                    $requirementAttributes = "data-parsley-validate-if-empty data-parsley-conditionalRequired='[" .
                                                        '"' . '#form-metadata' . '","' . $field['requiredWhen'] . '","' . $field['requiredWhenValue'] . '"' .
                                                        "]'";
                                                }

                                                $sortedField = explode(',', $field['config']);
                                                sort($sortedField);

                                            ?>
                                                <div class="<?= $hide ? 'hide' : '' ?>" data-language="<?= $lang ?>">
                                                    <?php
                                                    $showFieldReadonly = false;
                                                    if ($editable && !in_array($name, $this->readonlyMetaFields)) : ?>
                                                        <?php if ($field['type'] == 'input') : ?>
                                                            <input name="<?= $id ?>" type="text" class="form-control" <?= $requirementAttributes ?> value="<?= $value ?>" />
                                                        <?php elseif ($field['type'] == 'textarea') : ?>
                                                            <textarea name="<?= $id ?>" class="form-control" <?= $requirementAttributes ?>><?= $value ?></textarea>
                                                        <?php elseif ($field['type'] == 'date') : ?>
                                                            <div class="input-group date" style="max-width: 150px;">
                                                                <input name="<?= $id ?>" type="text" class="form-control" <?= $requirementAttributes ?> data-date-format="DD.MM.YYYY" <?php if ($value) {
                                                                                                                                                                                            $date = \Carbon\Carbon::createFromTimestamp($value); ?> value="<?= $date->format('d.m.Y') ?>" <?php
                                                                                                                                                                                                                                                                                        } ?> />
                                                                <span class="input-group-addon">
                                                                    <span class="glyphicon glyphicon-calendar"></span>
                                                                </span>
                                                            </div>
                                                        <?php elseif ($field['type'] == 'checkbox') : ?>
                                                            <div class="checkbox">
                                                                <label>
                                                                    <input name="<?= $id ?>" type="checkbox" <?= $requirementAttributes ?> value="1" <?= $value ? 'CHECKED' : '' ?> />
                                                                </label>
                                                            </div>
                                                        <?php elseif ($field['type'] == 'select') : ?>
                                                            <select name="<?= $id ?>" class="form-control" <?= $requirementAttributes ?>>
                                                                <option value=""></option>
                                                                <?php foreach (explode(',', $field['config']) as $option) {
                                                                ?>
                                                                    <option <?= $value == $option ? 'SELECTED' : ''  ?> value="<?= $option ?>"><?= $option ?></option>
                                                                <?php
                                                                } ?>
                                                            </select>
                                                        <?php elseif ($value) : $showFieldReadonly = true;
                                                        endif; ?>
                                                    <?php elseif ($value) : $showFieldReadonly = true;
                                                    endif; ?>

                                                    <?php if ($showFieldReadonly) : ?>
                                                        <?php
                                                        $detailLink = false;
                                                        if ($value instanceof \Pimcore\Model\Asset) {
                                                            $detailLink = $view->router()->path('pimcore_dam_asset_detail', ['id' => $value->getId()]);
                                                        } ?>
                                                        <?php if ($field['type'] == 'date' && $value) {
                                                            $value = \Carbon\Carbon::createFromTimestamp($value) ?>
                                                            <p class="form-control-static"><?= $value->toDateString() ?></p>
                                                        <?php } elseif ($field['type'] == 'checkbox') { ?>
                                                            <div class="checkbox">
                                                                <label>
                                                                    <input name="<?= $id ?>" disabled="disabled" type="checkbox" <?= $requirementAttributes ?> value="1" <?= $value ? 'CHECKED' : '' ?> />
                                                                </label>
                                                            </div>
                                                        <?php
                                                        } elseif ($value instanceof \Pimcore\Model\Asset\Image) {
                                                        ?>
                                                            <p class="form-control-static">
                                                                <a href="<?= $detailLink ?>">
                                                                    <?= $value->getThumbnail('dam_list')->getHTML(['class' => 'metadata-preview-image']) ?>
                                                                </a>
                                                            </p>
                                                        <?php
                                                        } elseif ($value instanceof \Pimcore\Model\Asset && method_exists($value, 'getImageThumbnail')) {
                                                        ?>
                                                            <p class="form-control-static">
                                                                <a href="<?= $detailLink ?>">
                                                                    <img src="<?= $value->getImageThumbnail('dam_list') ?>" class="metadata-preview-image" />
                                                                </a>
                                                            </p>
                                                        <?php
                                                        } else {
                                                        ?>
                                                            <p class="form-control-static">
                                                                <?php if ($detailLink) {
                                                                ?>
                                                                    <a href="<?= $detailLink ?>">
                                                                    <?php
                                                                } ?>
                                                                    <?= nl2br($value) ?>
                                                                    <?php if ($detailLink) {
                                                                    ?>
                                                                    </a>
                                                                <?php
                                                                    } ?>
                                                            </p>
                                                        <?php
                                                        } ?>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($editable) : ?>
                        <hr />
                        <input type="submit" class="btn btn-primary" value="<?= $this->translate('dam.save') ?>">
                    <?php endif; ?>
                </form>
            </div>
        <?php endif; ?>
    </div>


    <!-- replace asset function -->
    <?php
    $uploadUrl = $view->router()->path('pimcore_dam_asset_replace');
    ?>
    <form id="dropzoneReplace" class="hide" action="<?= $uploadUrl ?>" method="post" enctype="application/x-www-form-urlencoded">
        <input class="hide" type="hidden" name="id" value="<?= $item->getId() ?>" />
        <input class="hide" type="file" name="asset" />
    </form>

    <!-- Modal -->
    <div class="modal fade" id="replaceModal" tabindex="-1" role="dialog" aria-labelledby="replaceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="replaceModalLabel"><?= $this->translate('dam.replace.asset') ?></h4>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-xs-6">
                                <?php
                                $src = (string)$this->damThumbnail($item->hasThumbnail() ? $item->getThumbnail('dam_list') : '');
                                ?>
                                <img class="thumbnail" src="<?= $src ?>" style="width: 100%;" />

                                <table class="table table-striped">
                                    <tbody>
                                        <?php foreach ($item->getSystemMetaData() as $name => $value) : ?>
                                            <tr>
                                                <th><?= $this->translate('dam.asset.data.system.' . $name) ?></th>
                                                <td><?= $value ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-xs-6" id="previews">
                                <div id="template" class="file-row">
                                    <div>
                                        <img class="thumbnail" data-dz-thumbnail src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" style="width: 100%;" />
                                    </div>
                                    <table class="table table-striped">
                                        <tbody>
                                            <tr>
                                                <th><?= $this->translate('dam.asset.data.system.mimetype') ?></th>
                                                <td data-dz-mimetype></td>
                                            </tr>
                                            <tr>
                                                <th><?= $this->translate('dam.asset.data.system.filesize') ?></th>
                                                <td data-dz-size></td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <div class="progress progress-striped active">
                                        <div class="progress-bar" role="progressbar" data-dz-uploadprogress>
                                        </div>
                                    </div>

                                    <div class="dz-error-message">
                                        <span data-dz-errormessage></span>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?= $this->translate('dam.close.dialog') ?></button>
                    <button type="button" class="btn btn-primary" data-upload><?= $this->translate('dam.replace') ?></button>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="fullscreen" class="hide">
    <div class="col-sm-offset-3 col-sm-6 loading">
        <div class="progress progress-striped active">
            <div class="progress-bar progress-bar-striped" role="progressbar" style="width: 100%">
            </div>
        </div>
    </div>
</div>
<script>
    <?php $this->headScript()->captureStart(); ?>

    // init
    var dam = DAM.getInstance();
    dam.init();
    dam.enableExtendedAssetPreview($('.item-details .asset-preview .asset-preview-alternates'));


    <?php if ($item->getType() == 'Image') : ?>
        // enable image fullscreen preview

        // init fullscreen container
        var fullscreen = $('#fullscreen').click(function() {
            $(this).toggleClass('hide');
        }).appendTo('body');


        // show
        $('.item-details .asset-preview > img').on('click', function() {

            fullscreen.toggleClass('hide');

            if (!fullscreen.data('loaded')) {
                $('<img>').attr({
                    src: $(this).data('fullscreen')
                }).load(function() {

                    fullscreen.find('.loading').addClass('hide');
                    fullscreen.css('background-image', 'url(' + $(this).prop('src') + ')');
                    fullscreen.data('loaded', true);

                });
            }

        });
    <?php elseif ($item->getType() == 'Video') : ?>
        // enable video

        $('.item-details .asset-preview > img').on('click', function() {

            var container = $(this);

            // init video player
            $.getScript('//vjs.zencdn.net/4.5/video.js', function() {
                $('head').append($('<link rel="stylesheet" type="text/css" />').attr('href', '//vjs.zencdn.net/4.5/video-js.css'));

                videojs("asset-video-preview", {
                    "width": container.width(),
                    "height": container.height()
                }, function() {
                    // Player (this) is initialized and ready.

                    videojs("asset-video-preview").play();
                });

                container.addClass('hide');
                $('#asset-video-preview').removeClass('hide');

            });

        });
    <?php endif; ?>


    // metadata lang selection
    $('#item-metadata .nav-language .dropdown-menu a').click(function(e) {
        e.preventDefault();

        var lang = $(this).data('language');
        var label = $(this).parents('.dropdown-menu').prev('.dropdown-toggle');
        label.html(lang.toUpperCase() + ' <b class="caret"></b>');

        $('#item-metadata .tab-content [data-language]:not([data-language=""])').addClass('hide');
        $('#item-metadata .tab-content [data-language=' + lang + ']').removeClass('hide');

    });


    // active first tab
    $('#item-metadata .nav.nav-tabs a[data-toggle=tab]').first().click();

    <?php if ($item->getAllowDelete()) {
    ?>
        // enable replace asset
        var previewTemplate = '<div>' + $('#template').html() + '</div>';
        $('#template').remove();
        var myDropzone = new Dropzone('#dropzoneReplace', {
            maxFiles: 1,
            previewsContainer: "#previews",
            previewTemplate: previewTemplate,
            autoProcessQueue: false,
            clickable: ".item-details [data-action=replace]"
        });

        var dialog = $('#replaceModal').modal('hide');

        dialog.on('hidden.bs.modal', function() {
            $(this).find('#previews').html('');
        });

        dialog.find('[data-upload]').click(function(e) {
            e.preventDefault();

            myDropzone.processQueue();
        });

        myDropzone.on("addedfile", function(file) {
            $("#previews [data-dz-mimetype]").html(file.type);

            dialog.modal('show');
        });

        myDropzone.on("complete", function(file) {

            window.location.reload();

        });
    <?php
    } ?>

    // enable date input type
    dam.requireCalendar(function() {

        $('.input-group.date').datetimepicker({
            language: "<?= $this->language ?>",
            pickTime: false
        });

    });

    <?php
    // keyword assign
    $selectable = \Pimcore\Bundle\DamBundle\PimcoreDamBundle::getConfig()['backend']['metadata']['selectable'];
    if ($selectable) : ?>
        dam.requireSelectize(function() {
            <?php
            foreach ($selectable as $field => $metafield) :
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
                        <?php foreach ($optgroup as $group) : ?> {
                                name: '<?= $group['title'] ?>'
                            },
                        <?php endforeach; ?>
                    ],
                    optgroupField: 'group',
                    optgroupLabelField: 'name',
                    optgroupValueField: 'name',
                    optgroupOrder: [
                        <?php foreach ($optgroup as $group) : ?> '<?= $group['label'] ?>',
                        <?php endforeach; ?>
                    ],

                    options: [
                        <?php foreach ($optgroup as $group) : ?>
                            <?php foreach ($group['field'] as $name) : ?> {
                                    name: '<?= $name ?>',
                                    color: '#999',
                                    group: '<?= $group['title'] ?>'
                                },
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


    // enable date input type
    dam.requireParsley(function() {

        var parsleyOptions = {
            errorClass: 'has-error',
            classHandler: function(_el) {
                return _el.$element.closest('.form-group');
            }
        };

        var form = $('#form-metadata').parsley(parsleyOptions);
    });


    <?php $this->headScript()->captureEnd(); ?>
</script>