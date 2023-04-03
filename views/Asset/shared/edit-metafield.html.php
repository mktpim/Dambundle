<?php
/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

/** @var string $name
 * @var array $field
 * @var bool $ignoreRequirementAttribute
 * @var \Pimcore\Model\Asset $asset
 */

// settings
$name = $this->name ?: '';
$field = $this->field ?: [];
$asset = $this->asset ?: null;
$ignoreRequirementAttribute = (bool)$this->ignoreRequirementAttribute;
$isSelectAbleField = $this->batch && \Pimcore\Bundle\DamBundle\Dam\Helper::isSelectableMetaField($name);
if ($isSelectAbleField) {
    $selectMethodInputName = $name . '$selectmethod';
}

//avoid printing not editable attributes
if (!in_array($field['type'], ['input', 'textarea', 'date', 'checkbox', 'select'])) {
    return;
}

// ...
$arrLang = $field['language']
    ? \Pimcore\Tool::getValidLanguages()
    : [null];

?>
<div class="form-group form-group-sm">
    <label class="col-sm-4 control-label"><?= $this->translate('dam.metadata.label.' . $name) ?></label>
    <div class="col-sm-8">
        <?php if ($field['language']): ?>
            <div class="input-group">
                <?php
                if ($isSelectAbleField) {
                    print $this->template('PimcoreDamBundle:Asset:shared/select-method.html.php', ['name' => $selectMethodInputName]);
                } ?>
            <?php endif; ?>

            <?php foreach ($arrLang as $lang):

                // asset id optional anhängen
                $id = $asset
                    ? sprintf('metadata$%s$%s$%s$%s', $name, $asset->getId(), $field['type'], $lang) . '[]'
                    : sprintf('metadata$%s$%s$%s', $name, $field['type'], $lang) . '[]'
                ;

                // get current value
                $value = $asset ? $asset->getMetadata($name, $lang) : '';

                $hide = $lang != $this->language && $lang !== null;

                $class = $hide ? 'hide' : '';
                $class .= $lang ? ' input-group-element' : '';
                $class .= ' type-' . $field['type'];

                $requirementAttributes = '';
                if (!$ignoreRequirementAttribute && $field['required']) {
                    $requirementAttributes = 'required';
                } elseif ($field['requiredWhen']) {
                    $requirementAttributes = "data-parsley-validate-if-empty data-parsley-conditionalRequired='[" .
                        '"' . '#form-metadata' . '","' . $field['requiredWhen'] . '[]","' . $field['requiredWhenValue'] . '"' .
                        "]'";
                }

                ?>
                <div class="<?= $class ?>" data-language="<?= $lang ?>">
                    <?php if ($field['type'] == 'input'): ?>
                        <?php
                        if ($isSelectAbleField) {
                            if (!$field['language']) {
                                ?>
                                <div class="input-group">
                                <?= $this->template('PimcoreDamBundle:Asset:shared/select-method.html.php', ['name' => $selectMethodInputName])?>
                            <?php
                            }
                        } ?>

                        <input name="<?= $id ?>" type="text" class="form-control js-form-control" <?= $requirementAttributes ?> value="<?= $value ?>">

                        <?php if ($isSelectAbleField && !$field['language']) {
                            ?>
                            </div>
                        <?php
                        } ?>
                    <?php elseif ($field['type'] == 'textarea'): ?>
                        <textarea name="<?= $id ?>" class="form-control js-form-control" <?= $requirementAttributes ?> ><?= $value ?></textarea>
                    <?php elseif ($field['type'] == 'date'): ?>
                        <div class="input-group date" style="max-width: 150px;">
                            <input name="<?= $id ?>" type="text" class="form-control js-form-control" value="" data-date-format="DD.MM.YYYY" <?= $requirementAttributes ?> >
                        <span class="input-group-addon">
                            <span class="glyphicon glyphicon-calendar"></span>
                        </span>
                        </div>
                    <?php elseif ($field['type'] == 'checkbox'): ?>
                        <div class="checkbox">
                            <span>
                                <input name="<?= $id ?>" value="1" class="js-form-control"  <?= $value ? 'CHECKED' : '' ?> type="checkbox" <?= $requirementAttributes ?> />
                            </span>
                        </div>
                    <?php elseif ($field['type'] == 'select'): ?>
                        <select name="<?= $id ?>" class="form-control js-form-control" <?= $requirementAttributes ?> >
                            <option value=""></option>
                            <?php foreach (explode(',', $field['config']) as $option) {
                            ?>
                                <option value="<?= $option ?>"><?= $option ?></option>
                            <?php
                        } ?>
                        </select>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <?php if ($field['language']): ?>
                <div class="input-group-btn">
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><?= strtoupper($this->language) ?> <span class="caret"></span></button>
                    <ul class="language-switch dropdown-menu dropdown-menu-right" role="menu">
                        <?php foreach (\Pimcore\Tool::getValidLanguages() as $_lang): ?>
                            <li><a href="#" data-language="<?= $_lang ?>"><?= strtoupper($_lang) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
