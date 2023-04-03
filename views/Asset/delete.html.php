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
 * @var \Pimcore\Bundle\DamBundle\Dam\Item\AbstractItem[] $listItems
 */
$listItems = $this->listDelete;

?>
<div class="modal fade in" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?= $this->translate('dam.really-delete.asset') ?></h4>
            </div>
            <div class="modal-body">
                <?= $this->template('PimcoreDamBundle:Asset:shared/selected-items.html.php', ['selectedItems' => $listItems]) ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= $this->translate('dam.close.dialog') ?></button>
                <?php
                $ids = [];
                foreach ($listItems as $item) {
                    $ids[] = $item->getId();
                }
                $urlDelete = $this->path('pimcore_dam_asset_delete', [
                    'pid' => $this->getParam('pid'), 'confirm' => 1, 'selectedItems' => implode(',', $ids)
                ]);
                ?>
                <a class="btn btn-danger" href="<?= $urlDelete ?>"><?= $this->translate('dam.delete.asset') ?></a>
            </div>
        </div>
    </div>
</div>