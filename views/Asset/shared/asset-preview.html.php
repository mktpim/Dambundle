<?php
/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */
/**
 * @var \Pimcore\Bundle\DamBundle\Dam\Item\AbstractItem $item
 */
$size = $this->size ?: 250;
$item = $this->item;
$class = $this->class;

?>
<div class="asset-preview <?= $class ?>">
    <?php if (!$item->hasThumbnail() || $item->isFolder()): ?>
    <span class="item-indicator-type">
        <span class="<?= $item->getIcon() ?>"></span>
    </span>
    <?php endif; ?>

    <?php
    $src = !$item->hasThumbnail()
        ? 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'  // transparentes gif
        : $item->getThumbnail('dam_list');
    ?>
    <img src="<?= $src ?>" alt="<?= $item->getCaption() ?>" title="<?= $item->getCaption() ?>" width="<?= $size ?>" height="<?= $size ?>" />
</div>