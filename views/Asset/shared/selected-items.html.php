<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */
/**
 * @var \Pimcore\Bundle\DamBundle\Dam\Item\AbstractItem[] $listItems
 */
$listItems = $this->selectedItems;

$showDependencies = $this->showDependencies;

$itemHasDependency = function ($item) {
    $pimItem = $item->getAsset();
    if ($item->isFolder()) {
        $dependencies = $pimItem->getChilds();
    } else {
        $dependencies = $pimItem ? $pimItem->getDependencies()->getRequiredBy() : [];
    }

    return sizeof($dependencies);
};

if (count($listItems) <= 15) : ?>
    <div class="row">
        <?php
        $size = count($listItems) <= 3 ? floor(12 / count($listItems)) : 4;
        foreach ($listItems as $item) : ?>

            <?php
            $hasDependencies = $itemHasDependency($item);
            ?>
            <div class="col-sm-<?= $size ?> thumbnail text-center grid-item">

                <?php if (!$item->hasThumbnail() || $item->isFolder()) : ?>
                    <span class="item-indicator-type" style="position: absolute;">
                        <span class="<?= $item->getIcon() ?>"></span>
                    </span>
                <?php endif; ?>

                <?php
                $src = !$item->hasThumbnail()
                    ? 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'  // transparentes gif
                    : $item->getThumbnail('dam_list');

                ?>
                <img src="<?= $src ?>" alt="<?= $item->getCaption() ?>" title="<?= $item->getCaption() ?>" width="250" height="250" />
                <?php if ($hasDependencies) {
                ?>
                    <div class="alert alert-warning alert-grid-item">
                        <span class="glyphicon glyphicon-warning-sign"></span>&nbsp;
                        <?= $this->t('It has dependencies') ?>
                    </div>
                <?php
                } ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php else : ?>
    <?php $hasDependencies = false; ?>
    <?php foreach ($listItems as $item) : ?>
        <?php if (!$hasDependencies) {
            $hasDependencies = $itemHasDependency($item);
        } ?>

        <code><?= $item->getCaption() ?></code>
    <?php endforeach; ?>
    <?php if ($hasDependencies) {
    ?>
        <div class="alert alert-warning" style="margin-top: 20px;">
            <span class="glyphicon glyphicon-warning-sign"></span>&nbsp;
            <?= $this->t('dam.asset.delete.bulk-has-dependencies') ?>
        </div>
    <?php
    } ?>
<?php endif; ?>