<?php
/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

/**
 * @var \Pimcore\Bundle\DamBundle\Model\Collection $collection
 */
$collection = $this->collection;
$collection->getAssigned()

// style="display: block; opacity: 1; top: 200px;"
?>
<div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">
                    <span class="label name" style="background-color: <?= $collection->getColor() ?>;"> </span>&nbsp;
                    <?= $collection->getName() ?> <small><?= $this->translate('dam.really-delete.collection') ?></small>
                </h4>
            </div>
            <div class="modal-body">
                <p><?= sprintf($this->translate('dam.collection.assets-assigned-label'), $collection->getAssignedCount()) ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= $this->translate('dam.close.dialog') ?></button>
                <?php
                $urlDelete = $this->path('pimcore_dam_collection_delete', ['id' => $collection->getId(), 'confirm' => 1]);
                ?>
                <a class="btn btn-danger" href="<?= $urlDelete ?>"><?= $this->translate('dam.delete.collection') ?></a>
            </div>
        </div>
    </div>
</div>