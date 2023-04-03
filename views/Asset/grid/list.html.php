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
?>
<div class="col-md-12 grid-item" data-id="<?= $item->getId() ?>" data-type="<?= $item->isFolder() ? 'folder' : 'file' ?>">
    <a href="<?= $this->linkDetail ?>" style="color: inherit;">
        <span class="item-indicator-type <?= $item->getIcon() ?>"></span>

        <div class="asset-info">
            <table class="table table-hover">
                <tbody>
                    <tr>
                        <td class="asset-info-property property-name text-left">
                            <div class="property-name">
                                <strong><?= $item->getCaption() ?></strong>
                                <div class="asset-collection pull-right">
                                    <?= $this->template('PimcoreDamBundle:Asset:/grid/item-collections.html.php', ['item' => $item]) ?>
                                </div>
                            </div>
                        </td>
                        <?php foreach ($item->getSystemMetaData() as $label => $value) : ?>
                            <td class="visible-md visible-lg asset-info-property property-<?= $label ?> text-right"><span title="<?= $this->translate('dam.asset.data.system.' . $label) ?>"><?= $value ?></span></td>
                        <?php endforeach; ?>
                        <?php foreach ($item->getExtendedMetaData() as $label => $value) : ?>
                            <td class="visible-lg asset-info-property property-<?= $label ?> text-right"><span class="asset-info-property-name"><?= $this->translate('dam.asset.data.system.' . $label) ?>: </span><?= $value ?></td>
                        <?php endforeach; ?>
                        <td class="text-right" style="padding: 4px;">
                            <div class="asset-options">
                                <?= $this->template('PimcoreDamBundle:Asset:/grid/item-options.html.php', ['item' => $item]) ?>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </a>
</div>