<?php
/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */
$name = $this->name;

?>
<div class="input-group-btn selectable-method">
    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
        <span class="select-method-label"><?= $this->translate('dam.save.append') ?></span> <span class="caret"></span>
    </button>
    <input class="non-selectize select-method-input" type="hidden" name="<?= $name ?>" value="append">
    <ul class="dropdown-menu dropdown-menu-right" role="menu">
        <li><a class="btn btn-link js-select-method-btn" href="#"
               data-method="replace"><span class="glyphicon glyphicon-retweet" style="margin-right: 5px;"></span> <?= $this->translate('dam.save.replace') ?></a></li>
        <li><a class="btn btn-link js-select-method-btn" href="#"
               data-method="append"><span class="glyphicon glyphicon-random" style="margin-right: 5px;"></span> <?= $this->translate('dam.save.append') ?></a></li>
    </ul>
</div>