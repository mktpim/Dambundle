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
 */
?>
<ol class="folder-path">
    <li>
        <?php
        $urlTree = $view->path('pimcore_dam_asset_foldertree');
        ?>
        <a id="folder-path-open-folder-tree" href="<?= $urlTree ?>" class="ajax-dialog" data-ajax-param='{"target": <?=$this->getParam('pid')?>}'><span class="glyphicon glyphicon-globe"></span></a>
    </li>
    <li>
        <?php
        $urlRoot = $view->path('pimcore_dam_asset_list');
        if (!$_SERVER['QUERY_STRING']) {
            $urlRoot .= '?' . $_SERVER['QUERY_STRING'];
        }
        ?>
        <a id="folder-path-root" href="<?= $urlRoot ?>">
            <span class="glyphicon glyphicon-hdd"></span>
        </a>
    </li>
    <?php for ($i = 0; $i < count($this->folderPath); $i++):
        $item = $this->folderPath[$i];
        $folder = $item['folder'];
        $siblings = $item['sibling'];
        $active = !($i + 1 < count($this->folderPath));

        if ($folder->getId() != 1): ?>
            <li class="<?= $active ? 'active' : '' ?>">
                <a href="<?= \Pimcore\Bundle\DamBundle\Dam\Helper::getFolderLink($this, $folder->getId()) ?>">
                    <?= $folder->getKey() ?>
                </a>
                <?php if ($siblings): ?>
                    <ol class="folder-path-sibling">
                        <?php foreach ($siblings as $sf):
                            if ($sf->getType() == 'folder') {
                                ?>
                                <li>
                                    <a href="<?= \Pimcore\Bundle\DamBundle\Dam\Helper::getFolderLink($this, $sf->getId()) ?>">
                                        <?= $sf->getKey() ?>
                                    </a>
                                </li>
                            <?php
                            }
                        endforeach; ?>
                    </ol>
                <?php endif; ?>
            </li>
        <?php endif; ?>
    <?php endfor; ?>
</ol>
