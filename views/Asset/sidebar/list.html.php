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
 * @var \Pimcore\Bundle\DamBundle\Dam\Filter\AbstractFilter[] $listFilter
 * @var \Pimcore\Bundle\DamBundle\Model\Search\Listing|\Pimcore\Bundle\DamBundle\Model\Search[] $listSearch
 */
$listFilter = $this->filter;
$listSearch = \Pimcore\Bundle\DamBundle\Dam\Facade::getSearchList();

$params = [];
if ($this->getParam('pid') > 1) {
    $params['pid'] = $this->getParam('pid');
}

$linkFilter = $view->path('pimcore_dam_asset_list', $params);
?>
<form class="js-searchform" action="<?= $linkFilter ?>" method="get">
    <!-- add more filters and filter options -->
    <div style="width:298px; color:#f5f5f5; padding-bottom:10px;">
        <div class="btn-group">
            <!-- <div class="btn-group btn-filter-save">
                <button type="button" class="btn btn-default btn-sm" data-toggle="dropdown" title="<?= $this->translate('dam.save.current-search') ?>"><span class="glyphicon glyphicon-floppy-save"></span></button>
                <ul class="dropdown-menu">
                    <?php foreach ($listSearch as $search) : ?>
                        <li>
                            <button class="btn btn-link" type="submit" name="update-search" value="<?= $search->getId() ?>" style="width: 100%; text-align: left;">
                                <span><?= $search->getName() ?></span>
                                <span class="glyphicon glyphicon-refresh hide pull-right"></span>
                            </button>
                        </li>
                    <?php endforeach; ?>
                    <li role="presentation" class="divider"></li>
                    <li class="container-fluid">
                        <div class="input-group">
                            <input type="text" name="save-search" class="form-control input-sm" style="width: 150px;">
                            <span class="input-group-btn">
                                <button class="btn btn-default btn-sm" type="submit"><?= $this->translate('dam.save') ?></button>
                            </span>
                        </div>
                    </li>
                </ul>
            </div> -->

            <div class="btn-group btn-filter-load">
                <!-- <button type="button" class="btn btn-default btn-sm <?= $listSearch->count() == 0 ? 'disabled' : '' ?>" data-toggle="dropdown" title="<?= $this->translate('dam.load.saved-search') ?>"><span class="glyphicon glyphicon-floppy-open"></span></button> -->
                <ul class="dropdown-menu">
                    <?php foreach ($listSearch as $search) : ?>
                        <li>
                            <a href="<?= $search->getLink($this) ?>"><?= $search->getName() ?></a>
                        </li>
                    <?php endforeach; ?>
                    <li role="presentation" class="divider"></li>
                    <li>
                        <?php
                        $urlSavedSearches = $view->path('pimcore_dam_search_list');
                        ?>
                        <a href="<?= $urlSavedSearches ?>" class="ajax-dialog" data-ajax-form="reload"><span class="glyphicon glyphicon-cog"></span> <?= $this->translate('dam.edit.saved-search') ?></a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="btn-group pull-right">
            <button type="button" class="btn btn-default btn-sm" title="<?= $this->translate('dam.filter.add') ?>" data-toggle="dropdown"><span class="glyphicon glyphicon-plus"></span>&nbsp;Add&nbsp;Filter</button>
            <button id="filter-remove" type="button" class="btn btn-default btn-sm" title="<?= $this->translate('dam.filter.remove-all') ?>"><span class="glyphicon glyphicon-remove"></span>&nbsp;Delete&nbsp;Filters</button>
            <button id="filter-apply" type="submit" class="btn btn-default btn-sm"><?= $this->translate('dam.execute.filter') ?> <span class="glyphicon glyphicon-play"></span></button>
            <ul id="filter-add" class="dropdown-menu">
                <?php if (sizeof($listFilter)) {
                ?>
                    <?php foreach ($listFilter as $filter) :
                        if ($filter->hasFrontend()) : ?>
                            <li class="<?= $filter->isActive() ? 'disabled' : '' ?>"><a href="#" data-filter="<?= $filter->getId() ?>"><span class="<?= $filter->getIcon() ?>"></span> <?= $this->translate($filter->getName()) ?></a></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php
                } ?>
            </ul>
        </div>
    </div>

    <div id="filter-list">
        <?php
        $filerActive = false;
        if (sizeof($listFilter)) {
            foreach ($listFilter as $filter) {
                if ($filter->isActive()) {
                    $filerActive = true;
                    break;
                }
            }
        }

        ?>
        <div class="no-active-filter text-center <?= $filerActive ? 'hide' : '' ?>">
            <span class="glyphicon glyphicon-arrow-up"></span>
            <p class="text-muted"><?= $this->translate('dam.no-active-filter') ?></p>
        </div>

        <?php
        // print filter
        if (sizeof($listFilter)) {
            foreach ($listFilter as $filter) {
                if ($filter->hasFrontend()) {
                    echo $filter->getFrontend($this);
                }
            }
        }

        ?>
    </div>
</form>

<script>
    <?php $this->headScript()->captureStart(); ?>

    $(function() {

        var dam = DAM.getInstance();


        /**
         * a filter hase been changed
         */
        var filterChanged = function() {

            // no active filter?
            $('#filter-list .no-active-filter').toggleClass('hide', $('#filter-list [data-filter].active').length > 0);

            // mark filter has changed
            $('#filter-apply').addClass('btn-success');

        };


        $('#sidebar form').on('submit', function(e) {
            e.preventDefault();
            $('#search-form').submit();
        });


        $('#sidebar form [name=update-search]').on('click', function(e) {
            $('#sidebar form').off('submit');
        });


        // add filter
        $('#filter-add [data-filter]').click(function(e) {
            e.preventDefault();

            var menuItem = $(this).parent();

            if (!menuItem.hasClass('disabled')) {
                menuItem.addClass('disabled');
                var list = $('#filter-list');
                var filter = $(this).data('filter');
                var panel = $('#filter-list #filter-config-' + filter);
                panel.removeClass('hide');
                panel.addClass('active');
                panel.find('input, textarea, select').prop('disabled', false);


                filterChanged();
            }
        });

        $('#filter-remove').on('click', function(e) {
            $('#filter-list .panel-filter .remove-filter').click();
        });


        // filter remove button
        $('.panel-filter .remove-filter').click(function(e) {
            e.preventDefault();

            var panel = $(this).parents('.panel-filter');
            panel.addClass('hide');
            panel.removeClass('active');
            panel.find('input, textarea, select').prop('disabled', true);

            $('#filter-add [data-filter=' + panel.data('filter') + ']').parent().removeClass('disabled');


            filterChanged();
        });


        // mark filter has changed
        $('#filter-list input, #filter-list select,  #filter-list textarea').change(function(e) {

            filterChanged();

        });


        // prevent dropdown from closing
        $('.btn-filter-save .dropdown-menu input').click(function(e) {
            e.stopPropagation();
        });

    });

    <?php $this->headScript()->captureEnd(); ?>
</script>