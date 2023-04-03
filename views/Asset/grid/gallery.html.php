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
 * @var \Pimcore\Bundle\DamBundle\Dam\Grid $grid
 * @var \Pimcore\Bundle\DamBundle\Dam\Item\AbstractItem $item
 */
$grid = $this->grid;
$item = $this->item;

switch ($this->subView) {
    case 'large':
        $class = 'col-xs-6 col-sm-4 col-md-3 col-lg-4';
        $btnSize = 'btn-sm';
        $width = 500;
        $height = 500;
        $thumbName = 'dam_list_big';
        break;

    case 'small':
        $class = 'col-xs-6 col-sm-3 col-md-2 col-lg-1_5';
        $btnSize = 'btn-xs';
        $width = 250;
        $height = 250;
        $thumbName = 'dam_list';
        break;

    case 'normal':
    default:
        $class = 'col-xs-6 col-sm-4 col-md-3 col-lg-2';
        $btnSize = 'btn-sm';
        $width = 250;
        $height = 250;
        $thumbName = 'dam_list';
        break;
}
?>
<div class="grid-item <?= $class ?>" data-id="<?= $item->getId() ?>" data-type="<?= $item->isFolder() ? 'folder' : 'file' ?>">
    <div class="thumbnail" style="position: relative;">
        <div class="asset-collection">
            <?= $this->template('PimcoreDamBundle:Asset:/grid/item-collections.html.php', ['item' => $item]) ?>
        </div>

        <div class="asset-preview">
            <a href="<?= $this->linkDetail ?>">
                <?php if (!$item->hasThumbnail() || $item->isFolder()) : ?>
                    <span class="item-indicator-type">
                        <span class="<?= $item->getIcon() ?>"></span>
                    </span>
                <?php endif; ?>

                <?php
                $thumb = sprintf(
                    'style="background-image: url(%s)"',
                    $this->damThumbnail($item->hasThumbnail() ? $item->getThumbnail($thumbName) : '')
                );
                ?>
                <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" alt="<?= $item->getCaption() ?>" width="<?= $width ?>" height="<?= $height ?>" <?= $thumb ?> />
            </a>

            <?php if (($alternate = $item->getAlternateThumbnails($thumbName))) : ?>
                <ol class="hide asset-preview-alternates">
                    <?php foreach ($alternate as $thumb) : ?>
                        <li><img src="<?= $this->damThumbnail($thumb) ?>" width="<?= $width ?>" height="<?= $height ?>" /></li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>
        </div>

        <div class="asset-options">
            <?= $this->template('PimcoreDamBundle:Asset:/grid/item-options.html.php', ['item' => $item, 'btnSize' => $btnSize]) ?>
        </div>

        <?php
        if ($this->inShareFrontend && false) { // TODO der download kann nur funktionieren wenn man eingeloggt ist. somit im share frontend nicht möglich. nochmals genau prüfen !
        ?>
            <div class="text-center">
                <?php
                $linkDownload = $this->router()->path('pimcore_dam_asset_download', ['id' => $item->getId()]); ?>
                <a href="<?= $linkDownload ?>" class="btn btn-default btn-lg display-inline-block" target="_blank" title="<?= $this->translate('dam.asset.option.download') ?>"><span class="glyphicon glyphicon-download-alt"></span></a>
            </div>
        <?php
        } ?>

    </div>
    <div class="item-caption">
        <span class="label"><?= $item->getCaption() ?></span>
    </div>
</div>