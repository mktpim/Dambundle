<?php
/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

/**
 * @var \Pimcore\Model\User $user
 * @var \Pimcore\Bundle\DamBundle\Model\Collection\Listing|\Pimcore\Bundle\DamBundle\Model\Collection[] $list
 * @var \Pimcore\Bundle\DamBundle\Dam\Item\AbstractItem $item
 */
$user = $this->user;
$list = $this->list;
$item = $this->item;

// get assigned collections
$assignedList = [];
foreach ($item->getCollectionList() as $collection) {
    $assignedList[] = $collection->getId();
}

$collectionSelf = [];
$collectionPublic = [];

foreach ($list as $collection) {
    if ($collection->isEditable()) {
        $collectionSelf[] = $collection;
    } else {
        $collectionPublic[] = $collection;
    }
}

?>
<div class="modal fade in" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content collection-assign">
            <?php
            $urlSave = $this->path('pimcore_dam_collection_assign');
            ?>
            <form action="<?= $urlSave ?>" method="post">
                <input type="hidden" name="asset" value="<?= $item->getId() ?>" />

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><?= $item->getCaption() ?> <small><?= $this->translate('dam.assign.collection') ?></small></h4>
                </div>
                <div class="modal-body">

                    <div class="row">
                        <div class="col-xs-4 thumbnail">
                            <?php
                            $thumb = $item->getThumbnail('dam_list');
                            ?>
                            <img src="<?= $thumb ?>" alt="<?= $item->getCaption() ?>" width="250" height="250" />
                        </div>
                        <div class="col-xs-8">
                            <div class="row">
                                <?php foreach ($collectionSelf as $item): ?>
                                    <div class="col-xs-12 col-sm-6">
                                        <div class="checkbox">
                                            <label>
                                                <?php
                                                $checked = in_array($item->getId(), $assignedList) ? 'checked' : '';
                                                ?>
                                                <input type="checkbox" name="collection[]" value="<?= $item->getId() ?>" <?= $checked ?>>
                                                <?php if ($collection->hasColor()): ?>
                                                    <span class="collection" style="background-color: <?= $item->getColor() ?>;"></span>
                                                <?php endif; ?>
                                                <?= $item->getName() ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if ($collectionPublic): ?>
                            <hr/>
                            <h5><?= $this->translate('dam.public.collection') ?></h5>
                            <div class="row">
                                <?php foreach ($collectionPublic as $item): ?>
                                    <div class="col-xs-12 col-sm-6">
                                        <div class="checkbox">
                                            <label>
                                                <?php
                                                $checked = in_array($item->getId(), $assignedList) ? 'checked' : '';
                                                ?>
                                                <input type="checkbox" name="collection[]" value="<?= $item->getId() ?>" <?= $checked ?>
                                                    <?= !$item->isEditable() ? 'disabled' : '' ?>>
                                                <?php if ($collection->hasColor()): ?>
                                                    <span class="collection" style="background-color: <?= $item->getColor() ?>;"></span>
                                                <?php endif; ?>
                                                <?= $item->getName() ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?= $this->translate('dam.close.dialog') ?></button>
                    <button type="submit" class="btn btn-primary"><?= $this->translate('dam.save') ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
