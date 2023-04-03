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
$urlPost = $view->path('pimcore_dam_asset_createfolder', ['pid' => $this->getParam('pid')]);
?>
<div id="dialog-create-folder" class="modal fade in" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: block; opacity: 1; top: 200px;">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="create-folder" action="<?= $urlPost ?>" method="post">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><?= $this->translate('dam.create.folder') ?></h4>
                </div>
                <div class="modal-body">
                    <div class="input-group">
                        <span class="input-group-addon">Name</span>
                        <input type="text" name="name" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?= $this->translate('dam.close.dialog') ?></button>
                    <button type="submit" class="btn btn-primary"><?= $this->translate('dam.create') ?></button>
                </div>

            </form>
        </div>
    </div>

    <script>
        $(function(){

            setTimeout(function() {
                $('#dialog-create-folder input[name=name]').focus();
            }, 500);

        });
    </script>
</div>