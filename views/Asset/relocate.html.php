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
 * @var \Pimcore\Model\Asset[] $listAssets
 * @var \Pimcore\Model\Asset $parent
 */
$listAssets = $this->listAssets;
$parent = $this->parent;

$selectedIds = [];
foreach ($listAssets as $item) {
    $selectedIds[] = $item->getId();
}

$url = $this->path('pimcore_dam_asset_relocate', [
    'pid' => $this->getParam('pid'), 'selectedItems' => implode(',', $selectedIds),
]);

// style="display: block; opacity: 1; top: 200px;"
?>
<div id="dialog-relocate" class="modal fade folder-tree-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true" style="display: block; opacity: 1; top: 200px;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?= count($listAssets) ?> <?= $this->translate('dam.asset.relocate') ?> <span
                        class="small"><?= $this->translate('dam.asset.relocate-from') ?> <?= $parent->getFullPath() ?></span>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row" id="folder-tree">

                    <?php
                    if ($rootFolder = $this->folderTree[0]) {
                        echo $this->damFolderTree($url, \Pimcore\Bundle\DamBundle\Templating\Helper\FolderTree::TPL_TYPE_RELOCATE)->getTreeTemplate($rootFolder, $this->folderTree, $this->folderTreeList);
                    }
                    ?>
                    
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default"
                        data-dismiss="modal"><?= $this->translate('dam.close.dialog') ?></button>

                <div class="btn-group">
                    <button id="execute" type="submit"
                            class="btn btn-primary"><?= $this->translate('dam.relocate') ?></button>

                    <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu" role="menu">
                        <li><input id="executeFollow" type="submit" class="btn btn-link"
                                   value="<?= $this->translate('dam.relocate.follow') ?>"/></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <style type="text/css" scoped="scoped">
        .folderTree {
            list-style: none;
            padding-left: 15px;
        }

        .folderTree .folderTree-folder {
            margin: 1px;
        }

        .folderTree .folderTree-folder > .folderTree {
            display: none;
        }

        .folderTree .folderTree-folder.open > .folderTree {
            display: block;
        }

        .folderTree .folderTree-folder.childs > .glyphicon {
            cursor: pointer;
        }
    </style>

    <script>
        var dam = DAM.getInstance();
        dam.initFolderTree('<?= $this->damFolderTree($url, \Pimcore\Bundle\DamBundle\Templating\Helper\FolderTree::TPL_TYPE_RELOCATE)->getJsTemplate()?>');


        $(function () {
            var $scope = $('body').find('#dialog-relocate');

            $scope.on('click', '#execute', function () {
                location.href = $(this).closest('.folder-tree-modal').find('.btn.active').prop('href');
            });

            $scope.on('click','#executeFollow', function () {
                location.href = $(this).closest('.folder-tree-modal').find('.btn.active').attr('href') + '&follow=1';
            });

            // click
            $scope.on('click','ul li .btn', function (e) {
                e.preventDefault();

                $scope.find('#folder-tree').find('.btn.active').removeClass('active').removeClass('btn-info');

                $(this).addClass('active');
                $(this).addClass('btn-info');

            });
        });
    </script>
</div>