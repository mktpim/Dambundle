<?php
/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

/**
 * @var \DAM\Item\AbstractItem $item
 */
$item = $this->item;
?>
<?php foreach ($item->getCollectionList() as $collection): ?>
    <?php if ($collection->getColor() != ''): ?>
    <span class="collection" style="background-color: <?= $collection->getColor() ?>;" title="<?= $collection->getName() ?>">
        <span class="label name" style="background-color: <?= $collection->getColor() ?>;"><?= $collection->getName() ?></span>
    </span>
    <?php endif; ?>
<?php endforeach; ?>