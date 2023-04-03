<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */
/**
 * @var \Pimcore\Bundle\DamBundle\Dam\Item\AbstractItem $item
 * @var \Pimcore\Model\User $user
 */
$item = $this->item;
$pid = $item->getAsset()->getParentId();
$btnSize = $this->btnSize ? $this->btnSize : 'btn-sm';
?>
<div class="btn-group execute-single">
    <?php
    // asset zu einer kollektion zuweisen
    if ($item->getAllowCollection()) :
        if ($item->isFolder()) {
            $linkCollection = $this->path('pimcore_dam_collection_assignbatch');
        } else {
            $linkCollection = $this->path('pimcore_dam_collection_assign', ['asset' => $item->getId()]);
        }
    ?>
        <a href="<?= $linkCollection ?>" data-action="collection" class="btn btn-default <?= $btnSize ?>" title="Assign Collection"><span class="glyphicon glyphicon-paperclip"></span></a>
    <?php endif; ?>

    <?php
    // org. download
    if ($item->getAllowDownload() && !$item->isFolder()) :
        $linkDownload = $this->path('pimcore_dam_asset_download', ['id' => $item->getId()]);
    ?>
        <a href="<?= $linkDownload ?>" data-action="download" class="btn btn-default <?= $btnSize ?>" target="_blank" title="<?= $this->translate('dam.asset.option.download') ?>"><span class="glyphicon glyphicon-cloud-download"></span></a>
    <?php endif; ?>

    <?php
    // freigeben für externe
    if ($item->getAllowShare()) :
        $type = $item->isFolder() ? 'folder' : 'asset';
        $linkShare = $this->path('pimcore_dam_share_edit', [$type => $item->getId()]);
    ?>
        <a href="<?= $linkShare ?>" data-action="share" class="btn btn-default <?= $btnSize ?>" title="<?= $this->translate('dam.asset.option.share') ?>"><span class="glyphicon glyphicon-share-alt"></span></a>
    <?php endif; ?>

    <?php
    // verschieben
    if ($item->getAllowDelete()) :
        $linkRelocate = $this->path('pimcore_dam_asset_relocate', [
            'pid' => $pid, 'id' => $item->getId()
        ]);
    ?>
        <a href="<?= $linkRelocate ?>" data-action="relocate" class="btn btn-default <?= $btnSize ?>" title="<?= $this->translate('dam.asset.option.relocate') ?>"><span class="glyphicon glyphicon-folder-open"></span></a>
    <?php endif; ?>

    <?php
    // löschen
    if ($item->getAllowDelete()) :
        $linkDelete = $this->path('pimcore_dam_asset_delete', [
            'pid' => $pid, 'id' => $item->getId()
        ]);
    ?>
        <a href="<?= $linkDelete ?>" data-action="delete" class="btn btn-default <?= $btnSize ?>" title="<?= $this->translate('dam.asset.option.delete') ?>"><span class="glyphicon glyphicon-trash"></span></a>
    <?php endif; ?>
</div>
<div class="btn-group execute-batch">
    <?php
    // asset zu einer kollektion zuweisen
    if ($item->getAllowCollection()) :
        $linkCollection = $this->path('pimcore_dam_collection_assignbatch');
    ?>
        <a href="<?= $linkCollection ?>" data-action="collection" class="btn btn-success <?= $btnSize ?>" title="<?= $this->translate('.batch') ?>"><span class="glyphicon glyphicon-paperclip"></span></a>
    <?php endif; ?>

    <?php
    // download
    // TODO: hardcoded - $item->getAllowDownload() changed on "true"
    if (true && !$item->isFolder()) :
        $linkDownload = $this->path('pimcore_dam_asset_download');
    ?>
        <a href="<?= $linkDownload ?>" data-action="download" class="btn btn-success <?= $btnSize ?>" target="_blank" title="<?= $this->translate('dam.asset.option.download.batch') ?>"><span class="glyphicon glyphicon-cloud-download"></span></a>
    <?php endif; ?>

    <?php
    // freigeben für externe
    if ($item->getAllowShare() && !$item->isFolder()) :
        $linkShare = $this->path('pimcore_dam_share_edit', ['asset' => '']);
    ?>
        <a href="<?= $linkShare ?>" data-action="share" class="btn btn-success <?= $btnSize ?>" title="<?= $this->translate('dam.asset.option.share.batch') ?>"><span class="glyphicon glyphicon-share-alt"></span></a>
    <?php endif; ?>

    <?php
    // batch edit
    if ($item->getAllowUpdateMetaData() && !$item->isFolder()) :
        $linkEdit = $this->path('pimcore_dam_asset_batchupdate');
    ?>
        <a href="<?= $linkEdit ?>" data-action="batch-edit" class="btn btn-success <?= $btnSize ?>" title="<?= $this->translate('dam.asset.option.edit.batch') ?>"><span class="glyphicon glyphicon-pencil"></span></a>
    <?php endif; ?>

    <?php
    // verschieben
    if ($item->getAllowDelete()) :
        $linkRelocate = $this->path('pimcore_dam_asset_relocate', [
            'pid' => $pid, 'id' => ''
        ]);
    ?>
        <a href="<?= $linkRelocate ?>" data-action="relocate" class="btn btn-success <?= $btnSize ?>" title="<?= $this->translate('dam.asset.option.relocate.batch') ?>"><span class="glyphicon glyphicon-folder-open"></span></a>
    <?php endif; ?>

    <?php
    // löschen
    if ($item->getAllowDelete()) :
        $linkDelete = $this->path('pimcore_dam_asset_delete', ['pid' => $pid]);
    ?>
        <a href="<?= $linkDelete ?>" data-action="delete" class="btn btn-success <?= $btnSize ?>" title="<?= $this->translate('dam.asset.option.delete.batch') ?>"><span class="glyphicon glyphicon-trash"></span></a>
    <?php endif; ?>
</div>