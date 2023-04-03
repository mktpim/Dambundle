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
$parent = $this->parent;
$debug = !$this->getRequest()->isXmlHttpRequest();

// style="display: block; opacity: 1; top: 200px;"

$url = $view->path('pimcore_dam_asset_list') . '?'

?>
<div id="dialog-asset-tree" class="folder-tree-modal modal fade in show" tabindex="-1" role="dialog" aria-labelledby="AssetTree" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-body">
                <div class="row" id="folder-tree">

                    <?php
                    if ($rootFolder = $this->folderTree[0]) {
                        echo $this->damFolderTree($url)->getTreeTemplate($rootFolder, $this->folderTree, $this->folderTreeList);
                    }
                    ?>

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
            <?php if ($debug) {
                        $this->headScript()->captureStart();
                    } ?>

            var dam = DAM.getInstance();
            dam.initFolderTree('<?= $this->damFolderTree($url)->getJsTemplate()?>');

            <?php if ($debug) {
                        $this->headScript()->captureEnd();
                    } ?>

        </script>
    </div>
</div>