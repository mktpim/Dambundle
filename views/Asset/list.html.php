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

/* @var \Pimcore\Bundle\DamBundle\Dam\Grid $grid */
$grid = $this->grid;

/* @var \Pimcore\Model\User $user */
$user = $this->user;

$currentSort = $this->sort;
$currentOrder = $this->order;

/* @var \Zend\Paginator\Paginator */
$paginator = $this->paginator;

/**
 * Get the current url without the specified parameter,
 * user for unset sort/order options in list.
 * If key equals the specified unset-key the param
 * gets ignored in url.
 *
 * @param $view
 * @param $param        i.e. sort,order
 * @param $key          i.e. creationDate
 *
 * @return mixed
 */
$getParamUrl = function ($view, $param, $key) use ($getParamUrl) {
    $params = $view->getAllParams();
    $route = $this->getParam('_route');

    if ($key != $view->unsetKey) {
        unset($params['reset']);

        $url = $this->router()->path($route, array_merge($params, [$param => $key]));
    } else {
        unset($params[$param]);
        $url = $this->router()->path($route, array_merge($params, ['reset' => $param]));
    }

    return $url;
};

$this->extend('PimcoreDamBundle:layout:default.html.php');

$this->headScript()->appendFile('/bundles/pimcoredam/vendor/jquery-ui-1.10.4.custom.min.js');
?>
<div class="toolbar">

    <?= $this->template('PimcoreDamBundle:Asset:shared/folder-path.html.php', ['folderPath' => $this->folderPath]); ?>

    <div class="options">

        <!-- upload / create folder -->
        <?php if ($this->writeable) : ?>
            <div class="btn-group">
                <!-- new folder -->
                <?php
                $urlCreateFolder = $this->path('pimcore_dam_asset_createfolder', ['pid' => $this->getParam('pid', 1)], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_PATH);
                ?>
                <a id="btn-upload-url" class="btn btn-default btn-sm ajax-dialog" href="<?= $urlCreateFolder ?>">
                    <span class="glyphicon glyphicon-folder-close"></span> <?= $this->translate('dam.create.folder') ?>
                </a>

                <!-- Upload Buttons -->
                <div class="btn-group">
                    <button id="btn-upload" type="button" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-upload"></span> <?= $this->translate('dam.upload') ?></button>
                    <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only"></span>
                    </button>
                    <ul class="dropdown-menu" role="menu">
                        <li>
                            <?php
                            $urlUploadUrl = $this->path('pimcore_dam_asset_uploadurl', ['pid' => $this->getParam('pid', 1)]);
                            ?>
                            <a id="btn-upload-url" class="ajax-dialog" href="<?= $urlUploadUrl ?>"><span class="glyphicon glyphicon-globe"></span> <?= $this->translate('dam.upload.url') ?></a>
                        </li>
                        <li>
                            <a id="btn-upload-archive" href="#"><span class="glyphicon glyphicon-file"></span> <?= $this->translate('dam.upload.archive') ?></a>
                        </li>
                    </ul>
                </div>
            </div>
        <?php endif; ?>


        <!-- sidebar filter -->
        <button id="btn-toggle-sidebar" type="button" class="btn btn-default btn-sm <?= $this->sidebarActive ? 'active' : ''; ?>" title="<?= $this->translate('dam.show.sidebar') ?>">
            <span class="glyphicon glyphicon-filter"></span> Filter
        </button>

        <?php
        $sortTitle = $currentSort ?: $this->defaultSort;
        $orderTitle = $currentOrder ?: $this->defaultOrder;

        ?>

        <!-- sort & order -->
        <div class="btn-group">
            <div class="btn-group">
                <button title="<?= $this->t('dam.sort.title-' . $sortTitle) ?>" type="button" class="btn btn-default btn-sm dropdown-toggle <?= $currentSort ? 'active' : '' ?>" data-toggle="dropdown">
                    <span class="glyphicon <?= $currentSort ? $this->sortOptions[$currentSort]['icon'] : $this->sortOptions[$this->defaultSort]['icon'] ?>">
                </button>

                <ul class="dropdown-menu" role="menu">
                    <?php foreach ($this->sortOptions as $key => $sortKey) {
                        $url = $getParamUrl($this, 'sort', $key, $this->unsetKey); ?>
                        <li class="<?= $currentSort == $key ? 'active' : '' ?>">
                            <a href="<?= $url ?>">
                                <span class="glyphicon <?= $sortKey['icon'] ?>"></span>
                                <?= $this->translate('dam.sort.' . $key) ?>
                            </a>
                        </li>
                    <?php
                    } ?>
                </ul>
            </div>
            <div class="btn-group">
                <button title="<?= $this->t('dam.order.title-' . $orderTitle) ?>" type="button" class="btn btn-default btn-sm dropdown-toggle <?= $currentOrder ? 'active' : '' ?>" data-toggle="dropdown">
                    <span class="glyphicon <?= $currentOrder ? $this->orderOptions[$currentOrder]['icon'] : $this->orderOptions[$this->defaultOrder]['icon'] ?>"></span>
                </button>
                <ul class="dropdown-menu" role="menu">
                    <?php
                    foreach ($this->orderOptions as $key => $orderOption) {
                        $url = $getParamUrl($this, 'order', $key, $this->unsetKey); ?>
                        <li class="<?= $currentOrder == $key ? 'active' : '' ?>">
                            <a href="<?= $url ?>">
                                <span class="glyphicon <?= $orderOption['icon'] ?>"></span>
                                <?= $this->translate('dam.order.' . $key) ?>
                            </a>
                        </li>
                    <?php
                    } ?>
                </ul>
            </div>
        </div>


        <!-- extra options -->
        <?php
        $linkDownload = $this->path('pimcore_dam_asset_list', array_merge($view->getAllParams(), ['download' => 'zip']));
        ?>
        <a class="btn btn-default btn-sm" href="<?= $linkDownload ?>" title="<?= $this->translate('dam.download.archive.zip') ?>">
            <span class="glyphicon glyphicon-cloud-download"></span>
        </a>


        <!-- view types -->
        <div class="btn-group">
            <?php
            $arrViews = [
                'gallery' => 'glyphicon-th',
                'details' => 'glyphicon-th-list',
                'list' => 'glyphicon-list-alt',
            ];

            $query = http_build_query($view->getAllParams());
            foreach ($arrViews as $v => $icon) :
                $active = $this->getParam('view', 'gallery') == $v;

                $params = array_merge($this->getAllParams(), ['view' => $v]);
                $link = $view->path('pimcore_dam_asset_list', $params);

                if ($v == 'gallery') :
                    $subViews = [
                        'small' => 'glyphicon-zoom-out',
                        'normal' => 'glyphicon-search',
                        'large' => 'glyphicon-zoom-in',
                    ];
            ?>
                    <div class="btn-group">
                        <a class="btn btn-default btn-sm <?= $active ? 'active' : '' ?>" href="<?= $link ?>"><span class="glyphicon <?= $icon ?>"></span></a>
                        <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
                            <span class="caret"></span>
                            <span class="sr-only">...</span>
                        </button>
                        <ul class="dropdown-menu" role="menu">
                            <?php foreach ($subViews as $subView => $_icon) :
                                $active = $this->getParam('subView', 'normal') == $subView;
                                $link = $view->router()->path(
                                    'pimcore_dam_asset_list',
                                    array_merge($this->getAllParams(), ['view' => $v, 'subView' => $subView])
                                );
                            ?>
                                <li class="<?= $active ? 'active' : '' ?>"><a href="<?= $link ?>"><span class="glyphicon <?= $_icon ?>"></span> <?= $this->translate('dam.size.' . $subView) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php else : ?>
                    <a class="btn btn-default btn-sm <?= $active ? 'active' : '' ?>" href="<?= $link ?>"><span class="glyphicon <?= $icon ?>"></span></a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>


<?php if ($this->filterActive && $paginator) : ?>
    <div class="container-fluid">
        <div class="alert alert-info"><?= sprintf($this->translate('dam.found.search'), $paginator->getTotalItemCount()) ?></div>
    </div>
<?php endif; ?>


<?php if ($paginator && $paginator->getPages()->pageCount > 1) : ?>
    <div class="text-center">
        <?= $this->render('PimcoreDamBundle:includes:pagination/default.html.php', get_object_vars($paginator->getPages('Sliding'))); ?>
    </div>
<?php endif; ?>


<!-- Grid -->
<div id="grid-asset" class="row grid-asset grid-view-<?= $grid->getView() ?>">
    <?php
    $lastFolder = '';
    foreach ($grid as $offset => $item) {
        /* @var \Pimcore\Bundle\DamBundle\Dam\Item\AbstractItem $item */
        $asset = $item->getAsset();

        if ($grid->getFlatView() && $lastFolder != $asset->getPath()) {
            $lastFolder = $asset->getPath();
            $folderPath = explode('/', trim($asset->getPath(), '/'));
            $path = ''; ?>
            <div class="row section">
                <div class="col-xs-12 sticky">
                    <ol class="folder-path">
                        <?php for ($i = 0; $i < count($folderPath); $i++) :
                            $folder = $folderPath[$i];

                            $f = \Pimcore\Bundle\DamBundle\Dam\Item\AbstractItem::createInstance(\Pimcore\Model\Asset\Folder::getByPath($path .= '/' . $folder));
                            if ($f->getId() != 1) :
                        ?>
                                <li>
                                    <a href="<?= $f->getDetailLink($this) ?>"><?= $f->getCaption() ?></a>
                                </li>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </ol>
                </div>
            </div>
    <?php
        }

        $linkDetail = $item->isFolder()
            ? \Pimcore\Bundle\DamBundle\Dam\Helper::getFolderLink($this, $item->getId())
            : $view->router()->path(
                'pimcore_dam_asset_detail',
                [
                    'pid' => $this->getParam('pid'), 'id' => $item->getId(), 'page' => $paginator->getCurrentPageNumber(), 'o' => $offset
                ]
            );

        echo $this->template('PimcoreDamBundle:Asset:/grid/' . $grid->getView() . '.html.php', [
            'grid' => $grid,
            'item' => $item,
            'user' => $this->user,
            'linkDetail' => $linkDetail,
            'subView' => $this->getParam('subView', 'normal')
        ]);
    }
    ?>
</div>

<?php if ($paginator && $paginator->getPages()->pageCount > 1) : ?>
    <div class="text-center">
        <?= $this->render('PimcoreDamBundle:includes:pagination/default.html.php', get_object_vars($paginator->getPages('Sliding'))); ?>
    </div>
<?php endif; ?>

<script>
    <?php $this->headScript()->captureStart(); ?>

    // init system
    var dam = DAM.getInstance();
    dam.init();
    dam.initGrid($('#grid-asset'));


    // keyboard shortcut
    $(document).keyup(function(e) {

        // esc
        if (e.keyCode == 27) {
            $('#grid-asset .grid-item.selected').toggleClass('selected');
        }

        // delete
        if (e.keyCode == 46) {
            $('#grid-asset .grid-item.selected [data-action=delete]').first().click();
        }

    });


    // keyboard select all shortcut
    $(document).keydown(function(e) {

        // select all
        if (e.ctrlKey && e.keyCode == 65) {
            e.preventDefault();

            var e = jQuery.Event("click");
            e.ctrlKey = true;
            $('#grid-asset').find('.grid-item').trigger(e);
        }

    });



    <?php $this->headScript()->captureEnd(); ?>
</script>