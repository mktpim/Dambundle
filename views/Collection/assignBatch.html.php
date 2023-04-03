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
 * @var \Pimcore\Model\User $user
 * @var Pimcore\Bundle\DamBundle\Model\Collection[] $list
 * @var \Pimcore\Bundle\DamBundle\Dam\Item\AbstractItem[] $listItems
 */
$user = $this->user;
$list = $this->list;
$listItems = $this->listItems;

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
<div class="modal fade in show" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content collection-assign">
            <?php
            $urlSave = $this->router()->path('pimcore_dam_collection_assignbatch');
            ?>
            <form action="<?= $urlSave ?>" method="post">
                <input type="hidden" name="selectedItems" value="<?= $this->getParam('selectedItems') ?>" />

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><?= $this->translate('dam.assign.collection') ?></h4>
                </div>
                <div class="modal-body">

                    <div class="row">
                        <?php foreach ($collectionSelf as $i => $item): ?>
                            <?php if ($i > 0 && $i % 4 == 0): ?>
                                </div><div class="row">
                            <?php endif; ?>
                            <div class="col-xs-12 col-sm-4">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="collection[]" value="<?= $item->getId() ?>">
                                        
                                        <?php if ($item->hasColor()): ?>
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
                                            <input type="checkbox" name="collection[]" value="<?= $item->getId() ?>"
                                                <?php if (!$item->isEditable()) {
                print 'disabled';
            }  ?>>
                                            <span class="collection" style="background-color: <?= $item->getColor() ?>;"></span>
                                            <?= $item->getName() ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <hr/>
                    <?php if (count($listItems) <= 12): ?>
                        <div class="row">
                            <?php
                            $size = count($listItems) <= 12 ? floor(12 / count($listItems)) : 1;
                            foreach ($listItems as $item): ?>
                            <div class="col-xs-<?= $size ?> thumbnail">
                                <?php
                                $src = (string)$this->damThumbnail($item->hasThumbnail() ? $item->getThumbnail('dam_list') : '');
                                ?>
                                <img class="type-<?= $item->getType() ?>" title="<?= $item->getCaption() ?>" alt="<?= $item->getCaption() ?>" src="<?= $src ?>" width="250" height="250" />
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <?php foreach ($listItems as $item): ?>
                        <code><?= $item->getCaption() ?></code>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?= $this->translate('dam.close.dialog') ?></button>

                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary" name="append"><?= $this->translate('dam.save.append-collection') ?></button>

                        <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                            <span class="caret"></span>
                            <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu" role="menu">
                            <li><input type="submit" class="btn btn-link" name="replace" value="<?= $this->translate('dam.save.replace-collection') ?>" /></li>
                            <li role="presentation" class="divider"></li>
                            <li><input type="submit" class="btn btn-link" name="append-recursive" value="<?= $this->translate('dam.save.append-collection-recursive') ?>" /></li>
                            <li><input type="submit" class="btn btn-link" name="replace-recursive" value="<?= $this->translate('dam.save.replace-collection-recursive') ?>" /></li>
                        </ul>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
