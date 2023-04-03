<?php
/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */
/**
 * @var DAM_Collection $collection
 */
$collection = $this->collection;
?>
<div class="modal fade in_" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style_="display: block; opacity: 1; top: 200px;">
    <div class="modal-dialog">
        <div class="modal-content">
            <?php
            $urlSend = $this->path(['action' => 'sendEmail', 'controller' => 'collection', 'module' => 'DamListener']);
            ?>
            <form action="<?= $urlSend ?>" method="post" class="form-horizontal" role="form">
                <input type="hidden" name="id" value="<?= $collection->getId() ?>" />

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">
                        <span class="label name" style="background-color: <?= $collection->getColor() ?>;"> </span>&nbsp;
                        <?= $collection->getName() ?> <small>Kollektion per E-Mail versenden</small>
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="inputEmail" class="col-sm-2 control-label">An</label>
                        <div class="col-sm-10">
                            <input id="inputEmail" type="email" class="form-control" name="email" placeholder="email@domain.com" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="inputSubject" class="col-sm-2 control-label">Betreff</label>
                        <div class="col-sm-10">
                            <input id="inputSubject" type="text" class="form-control" name="subject" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="inputMessage" class="col-sm-2 control-label">Nachricht</label>
                        <div class="col-sm-10">
                            <textarea id="inputMessage" name="message" class="form-control" rows="5"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?= $this->translate('dam.close.dialog') ?></button>
                    <input type="submit" class="btn btn-primary disabled" value="Senden" data-loading-text="Sending..." />
                </div>
            </form>
        </div>
    </div>
</div>
