<?php
/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */
/**
 * @var \Pimcore\Bundle\DamBundle\Dam\Item\AbstractItem $item
 */
$item = $this->item;
?>
<div class="panel panel-default">
    <table class="table table-striped table-condensed" style="table-layout: fixed; word-wrap: break-word;">
        <tbody>
        <?php foreach ($item->getSystemMetaData() as $name => $value): ?>
            <tr>
                <th width="100"><?= $this->translate('dam.asset.data.system.' . $name) ?></th>
                <td><?= $value ?></td>
            </tr>
        <?php endforeach; ?>
        <?php foreach ($item->getExtendedMetaData() as $name => $value): ?>
            <tr>
                <th><?= $this->translate('dam.asset.data.system.' . $name) ?></th>
                <td><?= $value ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if ($item->getCollectionList()->count() > 0): ?>
    <div class="panel panel-default item-details">
        <div class="panel-heading">
            <h3 class="panel-title"><span
                        class="glyphicon glyphicon-paperclip"></span> <?= $this->translate('dam.collection.assigned') ?>
            </h3>
        </div>
        <div class="panel-body item-collection">
            <?php foreach ($item->getCollectionList() as $collection): ?>
                <span class="collection" style="background-color: <?= $collection->getColor() ?>;"></span>
                <?= $collection->getName() ?>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>


<?php if ($item->getType() == 'Image'): ?>
    <div class="panel panel-default item-details">
        <div class="panel-heading">
            <h3 class="panel-title"><span
                        class="glyphicon glyphicon-print"></span> <?= $this->translate('dam.print-info') ?></h3>
        </div>
        <table class="table table-striped table-condensed">
            <tbody>
            <tr>
                <th class="text-right">300 DPI</th>
                <td>
                    <?php
                    echo sprintf('%s cm x %s cm', round($item->getAsset()->getWidth() / 300 * 2.54, 2), round($item->getAsset()->getHeight() / 300 * 2.54, 2)
                    );
                    ?>
                </td>
            </tr>
            <tr>
                <th class="text-right">72 DPI</th>
                <td>
                    <?php
                    echo sprintf('%s cm x %s cm', round($item->getAsset()->getWidth() / 72 * 2.54, 2), round($item->getAsset()->getHeight() / 72 * 2.54, 2)
                    );
                    ?>
                </td>
            </tr>
            </tbody>
        </table>

        <?php
        /*
        // print color palette
        $a = microtime(true);
        $palette = $item->getColorPalette(10, 4);
        ?>
        <div class="col-sm-12">
        <div class="progress">
            <?php foreach($palette as $color): ?>
            <div class="progress-bar" style="width: 10%; background-color: #<?= $color ?>">
                <span class="sr-only">#<?= $color ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        </div>
        <?php var_dump(microtime(true) - $a); */ ?>
    </div>
<?php endif; ?>


<?php
$configPreset = null;
$convertAble = $item->getConvertAble();

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
} ?>

<?php if ($convertAble && $item->getAllowDownload()): ?>

    <div class="panel panel-default download-convert download-container">
        <div class="panel-heading">
            <h3 class="panel-title"><span
                        class="glyphicon glyphicon-cloud-download"></span> <?= $this->onlyPresetDownload ? $this->translate('dam.download.download') : $this->translate('dam.download.convert') ?>
            </h3>
        </div>
        <div class="panel-body">
            <?php
            $linkDownload = $this->path('pimcore_dam_asset_download');
            ?>
            <form action="<?= $linkDownload ?>" target="_blank" method="post" class="form-horizontal" role="form">
                <input type="hidden" name="id" value="<?= $item->getId() ?>"/>

                <?php
                if ($item->getType() == 'Image'):
                    if ($this->onlyPresetDownload) {
                        ?>
                        <div class="col-xs-12">
                            <?php foreach ($configPreset as $preset) {
                            ?>
                                <?php
                                    $config = \Pimcore\Model\Asset\Image\Thumbnail\Config::getByName($preset);
                            $translationKey = 'dam.thumbnail.' . $preset;
                            $label = $this->translate($translationKey);
                            if ($label == $translationKey) {
                                $label = '(' . $preset . ') ' . $config->getDescription();
                            } ?>
                                <div class="row">
                                    <button class="btn btn-default btn-block" name="preset" value="<?= $preset ?>">
                                        <div class="pull-left">
                                            <span class="<?= $preset ?>"
                                                  style="padding-left: 10px;">  <?= $label ?> </span>
                                        </div>
                                    </button>
                                    </br>
                                </div>
                            <?php
                        } ?>
                        </div>
                    <?php
                    } else {
                        if ($configPreset !== null): ?>
                            <div class="col-xs-12">
                                <div class="form-group">
                                    <h5><?= $this->translate('dam.download.convert.presets') ?></h5>
                                    <select name="preset" class="form-control">
                                        <option></option>
                                        <?php foreach ($configPreset as $preset):
                                            $config = \Pimcore\Model\Asset\Image\Thumbnail\Config::getByName($preset);
                        $translationKey = 'dam.thumbnail.' . $preset;
                        $label = $this->translate($translationKey);
                        if ($label == $translationKey) {
                            $label = '(' . $preset . ') ' . $config->getDescription();
                        } ?>
                                            <option value="<?= $preset ?>"><?= $label ?></option>
                                        <?php endforeach ?>
                                    </select>
                                </div>
                            </div>
                        <?php endif; ?>

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
                                    <input name="dpi" type="text" class="form-control input-sm" value="300"
                                           placeholder="300">
                                </div>
                            </div>
                        </div>

                        <div class="form-group" data-convert-custome>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span
                                            class="input-group-addon"><?= $this->translate('dam.asset.data.system.width') ?></span>
                                    <input name="width" type="text" class="form-control input-sm" value="800">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span
                                            class="input-group-addon"><?= $this->translate('dam.asset.data.system.height') ?></span>
                                    <input name="height" type="text" class="form-control input-sm" value="600">
                                </div>
                            </div>
                        </div>

                        <div class="form-group" data-convert-custome>
                            <div class="col-md-5">
                                <select name="format" class="form-control input-sm">
                                    <option>JPEG</option>
                                    <option>PNG</option>
                                    <option>GIF</option>
                                    <option>TIFF</option>
                                </select>
                            </div>
                            <div class="col-md-7">
                                <div class="input-group">
                                    <span class="input-group-addon"><?= $this->translate('dam.quality') ?></span>
                                    <select name="quality" class="form-control input-sm">
                                        <?php for ($q = 100; $q >= 30; $q = $q - 5): ?>
                                            <option><?= $q ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group" data-convert-custome>
                            <div class="col-md-6">
                                <select name="colorspace" class="form-control input-sm">
                                    <option value="rgb">RGB</option>
                                    <option value="cmyk">CMYK</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-7">
                                <div class="checkbox" data-convert-custome>
                                    <label>
                                        <input name="aspectratio" type="checkbox" value="1" checked>
                                        <?= $this->translate('dam.download.convert.custom.aspect-ratio') ?>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-5 text-right">
                                <button class="btn btn-default btn-sm"><span
                                            class="glyphicon glyphicon-cloud-download"></span> <?= $this->translate('dam.download.converted') ?>
                                </button>
                            </div>
                        </div>
                    <?php
                    } ?>
                <?php elseif ($item->getType() == 'Video'):
                    if ($configPreset !== null): ?>
                        <div class="form-group">
                            <label
                                    class="col-sm-3 control-label"><?= $this->translate('dam.download.convert.preset') ?></label>
                            <div class="col-sm-9">
                                <select name="preset" class="form-control input-sm">
                                    <?php foreach ($configPreset as $preset):
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
                        <div class="form-group">
                            <label
                                    class="col-sm-3 control-label"><?= $this->translate('dam.download.convert.format') ?></label>
                            <div class="col-sm-9">
                                <select name="format" class="form-control input-sm">
                                    <option value="mp4">MP4</option>
                                    <option value="webm">WebM</option>
                                </select>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="text-right">
                        <button class="btn btn-default btn-sm"><span
                                    class="glyphicon glyphicon-cloud-download"></span> <?= $this->translate('dam.download.converted') ?>
                        </button>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
<?php endif; ?>


<script>
    <?php $this->headScript()->captureStart(); ?>


    var dam = DAM.getInstance();

    dam.initDownload(<?= $item instanceof \Pimcore\Bundle\DamBundle\Dam\Item\Image
        ? '{width: ' . $item->getAsset()->getWidth() . ', height: ' . $item->getAsset()->getHeight() . '}'
        : '' ?>);


    <?php $this->headScript()->captureEnd(); ?>
</script>