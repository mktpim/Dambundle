<?php
/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */
?>

<!-- Modal -->
<div id="operatorInfo" class="modal fade in" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style=" opacity: 1; top: 200px;">
    <div class="modal-dialog">

        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?= $this->translate('operator-info-header') ?></h4>
            </div>
            <div class="modal-body">
                <p>"+" <?= $this->translate('operator-info-plus') ?></p>
                <p>"-" <?= $this->translate('operator-info-minus') ?></p>
                <p>" " <?= $this->translate('operator-info-spaces') ?></p>
                <p>"*" <?= $this->translate('operator-info-wildcard') ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= $this->translate('dam.close.dialog') ?></button>
            </div>
        </div>

    </div>
</div>
