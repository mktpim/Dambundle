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
 * @var \DAM\Share\Asset[] $listAssets
 */
$listAssets = $this->listAssets;

$downloadType = $this->downloadType;

$convertAble = true;
$selectedIds = [];
foreach ($listAssets as $item) {
    $selectedIds[] = $item->getId();
    if (!$item->getConvertAble()) {
        $convertAble = false;
    }
}

$item = $listAssets[0];
$configPreset = null;

if ($convertAble) {
    switch ($item->getType()) {
        case 'Image':
            $configPreset = \Pimcore\Bundle\DamBundle\PimcoreDamBundle::getConfig()['download']['image'];
            break;

        case 'Video':
            $configPreset = \Pimcore\Bundle\DamBundle\PimcoreDamBundle::getConfig()['download']['video'];
            $convertAble = $configPreset !== null;
            break;
    }
}

$headline = $downloadType ? sprintf('dam.download.%s', strtolower($downloadType)) : 'dam.download';
?>
<div id="dialog-download" class="modal fade download-container" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?= $this->translate($headline) ?></h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-xs-12">
                            <?= $this->template('PimcoreDamBundle:Asset:shared/selected-items.html.php', ['selectedItems' => $listAssets]) ?>
                        </div>

                        <?php
                        $url = $this->path('pimcore_dam_asset_download');
                        ?>
                        <form target="_blank" action="<?= $url ?>" method="post" role="form" class="form-horizontal">
                            <input type="hidden" name="type" value="original" />
                            <input type="hidden" name="selectedItems" value="<?= implode(',', $selectedIds) ?>" />

                            <?php if ($convertAble && $downloadType == 'Image') :
                                if ($configPreset) : ?>
                                    <div class="col-xs-5">
                                        <div class="form-group">
                                            <h5><?= $this->translate('dam.download.convert.presets') ?></h5>
                                            <select name="preset" class="form-control input-sm">
                                                <option></option>
                                                <?php foreach ($configPreset as $preset) :
                                                    $config = \Pimcore\Model\Asset\Image\Thumbnail\Config::getByName($preset);

                                                    $translationKey = 'dam.thumbnail.' . $preset;
                                                    $label = $this->translate($translationKey);
                                                    if ($label == $translationKey) {
                                                        $label = '(' . $preset . ') ' . $config->getDescription();
                                                    }

                                                ?>
                                                    <option value="<?= $preset ?>"><?= $label ?></option>
                                                <?php endforeach ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-xs-1"></div>

                                <?php endif; ?>

                                <div class="col-xs-6" data-convert-custome>

                                    <h5><?= $this->translate('dam.download.convert.custom') ?></h5>

                                    <div class="form-group" data-convert-custome>
                                        <div class="col-md-6">
                                            <select name="unit" class="form-control input-sm">
                                                <option value="px">Pixel</option>
                                                <option value="cm">cm</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">

                                            <div class="input-group hide">
                                                <span class="input-group-addon">DPI</span>
                                                <input name="dpi" type="text" class="form-control input-sm" value="300" placeholder="300">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="col-md-6">
                                            <div class="input-group">
                                                <span class="input-group-addon"><?= $this->translate('dam.asset.data.system.width') ?></span>
                                                <input name="width" type="text" class="form-control input-sm" value="<?= $item->getAsset()->getWidth() ?>" placeholder="Width Px">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="input-group">
                                                <span class="input-group-addon"><?= $this->translate('dam.asset.data.system.height') ?></span>
                                                <input name="height" type="text" class="form-control input-sm" value="<?= $item->getAsset()->getHeight() ?>" placeholder="Height Px">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="col-md-6">
                                            <div class="input-group">
                                                <span class="input-group-addon"><?= $this->translate('dam.quality') ?></span>
                                                <input name="quality" type="number" class="form-control input-sm" value="75" placeholder="Quality" maxlength="2" size="2">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <select name="format" class="form-control input-sm">
                                                <option>PNG</option>
                                                <option>GIF</option>
                                                <option>JPEG</option>
                                                <option>TIFF</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group image-ratio">
                                        <div class="col-md-6">
                                            <div class="checkbox">
                                                <label>
                                                    <input name="aspectratio" type="checkbox" value="1" checked>
                                                    <?= $this->translate('dam.download.convert.custom.aspect-ratio') ?>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group aspect-ratio">
                                        <div class="col-md-12">
                                            <div class="checkbox">
                                                <label>
                                                    <input name="imageratio" type="checkbox">
                                                    <?= $this->translate('image-ratio') ?>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php elseif ($convertAble && $downloadType == 'Video') : ?>
                                <div class="row">
                                    <div class="col-xs-7">
                                        <div class="form-group">
                                            <label class="col-sm-4 control-label"><?= $this->translate('dam.download.convert.presets') ?></label>
                                            <div class="col-sm-8">
                                                <select name="preset" class="form-control input-sm">
                                                    <?php foreach ($configPreset as $preset) :
                                                        $config = \Pimcore\Model\Asset\Video\Thumbnail\Config::getByName($preset);
                                                        $translationKey = 'dam.thumbnail.' . $preset;
                                                        $label = $this->translate($translationKey);
                                                        if ($label == $translationKey) {
                                                            $label = '(' . $preset . ') ' . $config->getDescription();
                                                        }

                                                    ?>
                                                        <option value="<?= $preset ?>"><?= $label ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-5">
                                        <div class="form-group">
                                            <label class="col-sm-4 control-label"><?= $this->translate('dam.download.convert.format') ?></label>
                                            <div class="col-sm-8">
                                                <select name="format" class="form-control input-sm">
                                                    <option value="mp4">MP4</option>
                                                    <option value="webm">WebM</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= $this->translate('dam.close.dialog') ?></button>
                <a id="download-original" data-type="original" class="btn btn-default" href="#" data-loading-text="Saving..."><span class="glyphicon glyphicon-download-alt"></span> <?= $this->translate('dam.download.original') ?></a>
                <?php if ($convertAble && $downloadType) : ?>
                    <a id="download-converted" data-type="converted" class="btn btn-primary" href="#" data-loading-text="Saving..."><span class="glyphicon glyphicon-cloud-download"></span> <?= $this->translate('dam.download.converted') ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        $(function() {
            var $modal = $('#dialog-download');
            var form = $modal.find('form');

            $('#download-original, #download-converted').on('click', function() {
                form.find('input[name=type]').val($(this).data('type'));
                form.submit();
                $modal.modal('hide');
            });


            var dam = DAM.getInstance();
            dam.initDownload(
                <?= $item instanceof \Pimcore\Bundle\DamBundle\Dam\Item\Image
                    ? '{width: ' . $item->getAsset()->getWidth() . ', height: ' . $item->getAsset()->getHeight() . '}'
                    : '' ?>);

        });
    </script>
</div>