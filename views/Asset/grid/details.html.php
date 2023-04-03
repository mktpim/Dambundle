<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *
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
<div class="col-xs-6 col-md-4 grid-item" data-id="<?= $item->getId() ?>" data-type="<?= $item->isFolder() ? 'folder' : 'file' ?>">
    <div class="row">
        <div class="col-md-5 asset-preview">
            <a style="position: relative;" href="<?= $this->linkDetail ?>" class="thumbnail">
                <?php if (!$item->hasThumbnail() || $item->isFolder()) : ?>
                    <span class="item-indicator-type">
                        <span class="<?= $item->getIcon() ?>"></span>
                    </span>
                <?php endif; ?>

                <?php
                $thumb = $item->hasThumbnail()
                    ? sprintf('style="background-image: url(%s);"', $item->getThumbnail('dam_list'))
                    : '';
                ?>
                <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" alt="<?= $item->getCaption() ?>" width="250" height="250" <?= $thumb ?> />
            </a>

            <?php if (($alternate = $item->getAlternateThumbnails())) : ?>
                <ol class="hide asset-preview-alternates">
                    <?php foreach ($alternate as $thumb) : ?>
                        <li><img src="<?= $thumb ?>" width="250" height="250" /></li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>


            <div class="asset-options">
                <?= $this->template('PimcoreDamBundle:Asset:/grid/item-options.html.php', ['item' => $item]) ?>

                <?php if (!$item->isFolder()) : ?>
                    <div class="btn-group" style="display: none;">
                        <?php
                        $bookmark = $item->getBookmark();
                        //                    $linkBookmark = $this->url(array('action' => 'toggle',
                        //                                                     'controller' => 'collection',
                        //                                                     'module' => 'DAM',
                        //                                                     'id' => 1,
                        //                                                     'asset' => $item->getId()), 'plugin', true);
                        $icon = $bookmark ? 'glyphicon-star' : 'glyphicon-star-empty';
                        ?>
                        <a href="<?= $linkBookmark ?>" data-action="bookmark" class="btn btn-default btn-sm" title="Merkliste"><span class="glyphicon <?= $icon ?>"></span></a>

                        <?php
                        //                    $linkCollection = $this->url(array('action' => 'list',
                        //                                                     'controller' => 'collection',
                        //                                                     'module' => 'DAM',
                        //                                                     'asset' => $item->getId()), 'plugin', true);
                        ?>
                        <a href="<?= $linkCollection ?>" data-action="collection" class="btn btn-default btn-sm" title="Zu einer Kollektion hinzufügen"><span class="glyphicon glyphicon-paperclip"></span></a>

                        <button class="btn btn-default btn-sm" title="Download"><span class="glyphicon glyphicon-download-alt"></span></button>
                        <button class="btn btn-default btn-sm" title="Freigabe URL erstellen"><span class="glyphicon glyphicon-share-alt"></span></button>
                        <button class="btn btn-default btn-sm" title="Löschen"><span class="glyphicon glyphicon-trash"></span></button>
                    </div>
                <?php endif; ?>
            </div>

        </div>
        <div class="col-md-7 asset-info">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th colspan="2">
                            <span class="item-caption"><?= $item->getCaption() ?></span>

                            <div class="asset-collection">
                                <?= $this->template('PimcoreDamBundle:Asset:/grid/item-collections.html.php', ['item' => $item]) ?>
                            </div>
                        </th>
                    </tr>
                </thead>
                <?php foreach ($item->getSystemMetaData() as $label => $value) : ?>
                    <tr>
                        <th class="text-left"><?= $this->translate('dam.asset.data.system.' . $label) ?></th>
                        <td class="text-left"><?= $value ?></td>
                    </tr>
                <?php endforeach; ?>

                <?php foreach ($item->getListViewMetaData() as $label => $value) : ?>
                    <tr>
                        <th class="text-left"><?= $label ?></th>
                        <td class="text-left"><?= $value ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</div>