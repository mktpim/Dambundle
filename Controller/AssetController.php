<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Controller;

use Carbon\Carbon;
use Pimcore\Bundle\DamBundle\Dam;
use Pimcore\Bundle\DamBundle\Event;
use Pimcore\Bundle\DamBundle\Model\Collection;
use Pimcore\Bundle\DamBundle\Model\Search;
use Pimcore\Bundle\DamBundle\PimcoreDamBundle;
use Pimcore\Controller\FrontendController;
use Pimcore\Model\Asset;
use Pimcore\Translation\Translator;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Routing\Annotation\Route;
use Pimcore\Model\Element\Recyclebin;

class AssetController extends FrontendController
{
    use ControllerAware {
        ControllerAware::onKernelController as __onKernelController;
    }

    /**
     * list aller assets auf die der user zugreifen kann
     *
     * @var \Pimcore\Bundle\DamBundle\Model\Asset\Listing|Asset[]
     */
    protected $list;

    /**
     * @var Translator
     */
    protected $translator;

    /**
     * AssetController constructor.
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * rechte prüfen
     *
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        parent::onKernelController($event);

        $this->__onKernelController($event);
        $this->list = Dam\Facade::getAssetList();
    }

    /**
     * show asset grid
     *
     * @Route("/asset/list", name="pimcore_dam_asset_list")
     */
    public function listAction(Request $request)
    {
        // init
        $grid = $this->getGrid();
        $grid->setView($request->get('view', 'gallery'));
        $viewCaption = 'DamListener';   // wird u.a. für den download der aktuellen ansicht verwendet
        $this->view->writeable = false;

        // sidebar active?
        $sidebarOpenByDefault = PimcoreDamBundle::getConfig()['backend']['ui']['filter_sidebar']['open_by_default'];
        $this->view->sidebarActive = $sidebarOpenByDefault;

        // define root
        if ($request->get('collection')) {
            // root ist eine kollektion

            $request->request->set('filter_collection', [$request->get('collection')]);
            $grid->setFlatView(true);
            $cl = Dam\Facade::getCollectionList();
            $cl->addConditionParam(sprintf('id = %d', $request->get('collection')), '');
            $collection = $cl->current();

            $viewCaption = 'Collection ' . $collection->getName();
        } elseif ($request->get('token')) {
            $request->query->set('filter_share', $request->get('token'));
            $grid->setFlatView(true);

            $viewCaption = 'Token ' . $request->get('token');
        }

        // create grid
        $list = $this->createGridFromParams($request->query->all());

        $filterActive = $list->getFilterActive();
        $this->view->filter = $list->getFilters();

        if ($filterActive) {
            $grid->setFlatView(true);
        }


        if ($currentFolder = $list->getCurrentFolder()) {
            $this->view->folderPath = $this->getFolderPath($currentFolder, $request);
            $this->view->writeable = $currentFolder->isAllowed('create');
            $this->view->uploadParent = $currentFolder->getId();
            $viewCaption = $currentFolder->getFilename();
        }

        // create paging
        $paginator = new \Zend\Paginator\Paginator($list);
        $paginator->setItemCountPerPage(30);
        $paginator->setCurrentPageNumber($request->get('page', 1));
        $this->view->paginator = $paginator;

        // create view list
        foreach ($paginator as $item) {
            $asset = Asset::getById($item->getId());
            if ($asset) {
                $item = Dam\Item\AbstractItem::createInstance($asset);

                // set collections
                $list = clone Dam\Facade::getCollectionList();
                $list->addConditionParam(sprintf('assigned like "%%,%d,%%"', $item->getId()), '');
                $item->setCollectionList($list);

                $this->setItemRights($item);

                $grid->addItem($item);
            }
        }

        // save search
        if ($request->get('save-search')) {
            // save current filter settings
            $search = new Search();
            $search->setName($request->get('save-search'));
            $search->setUser($this->getUser()->getUser());
        } elseif ($request->get('update-search')) {
            // update existing search with the current settings

            $searchList = Dam\Facade::getSearchList();
            $searchList->addConditionParam(sprintf('id = %d', $request->get('update-search')), '');

            if ($searchList->count() == 1) {
                $search = $searchList->current();
            }
        }

        // save search
        if ($search instanceof Search) {
            $active = [];
            foreach ($this->view->filter as $filter) {
                if ($filter->isActive()) {
                    $active[] = $filter;
                }
            }

            $search->setFilter($active);
            $search->save();
        }

        /* Ensure there is a zip filename for the download. */
        $viewCaption = $viewCaption ?: 'Download';

        if ($request->get('download')) {
            $zipFile = $grid->createZipFile($viewCaption);

            $response = new BinaryFileResponse($zipFile);
            $response->headers->set('Content-Type', \Pimcore\Tool\Mime::detect($zipFile));
            $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $viewCaption . '.zip');
            $response->deleteFileAfterSend(true);

            return $response;
        } else {
            $this->view->grid = $grid;
            $this->view->filterActive = $filterActive;
        }
    }

    /**
     * show asset details
     *
     * @Route("/asset/detail")
     */
    public function detailAction(Request $request)
    {
        // init
        $this->view->sidebarActive = true;
        $this->view->editable = false;

        // create list
        $gridList = $this->createGridFromParams($request->query->all());

        // load asset
        $asset = \Pimcore\Model\Asset::getById($request->get('id'));
        if (!$asset || !$asset->isAllowed('view')) {
            throw new \Exception('not allowed');
        }

        $item = Dam\Item\AbstractItem::createInstance($asset);
        $this->setItemRights($item);
        $this->view->editable = $item->getAllowUpdateMetaData();

        // load prev / next items
        if ($request->get('page') && $request->query->has('o')) {
            $this->view->offsetParam = $request->get('o');

            $offset = ($request->get('page') - 1) * 30 + $this->view->offsetParam;
            $gridList->addConditionParam('id != ?', $request->get('id'));
            $gridList->setOffset($offset - 1);
            $gridList->setLimit(2);

            $idList = $gridList->loadIdList();

            // handle 1. item in list
            if ($offset == 0) {
                $idList[1] = $idList[0];
                $idList[0] = null;
            }

            list($prevItem, $nextItem) = $idList;

            // allow only folder
            $prevAsset = Asset::getById($prevItem);
            $nextAsset = Asset::getById($nextItem);

            if ($prevAsset && $prevAsset->getType() != 'folder') {
                $this->view->prevItem = $prevItem;
            }
            if ($nextAsset && $nextAsset->getType() != 'folder') {
                $this->view->nextItem = $nextItem;
            }
        }

        // load and set assigned collections
        $list = Dam\Facade::getCollectionList();
        $list->addConditionParam(sprintf('assigned like "%%,%d,%%"', $item->getId()), '');
        $item->setCollectionList($list);
        $this->view->item = $item;

        // current folder path
        $folderPath = $this->getFolderPath($item->getAsset()->getParent(), $request);
        $this->view->folderPath = $folderPath;

        // get meta fields to show
        $arrMetaFields = Dam\Helper::getEditableMetaFields($item);
        $allMetaFields = $arrMetaFields;
        $arrGroups = [];

        $damConfig = $this->container->getParameter('pimcore_dam_config');
        $cnfGroup = $damConfig['backend']['metadata']['group'];

        foreach ($cnfGroup as $config) {
            $fields = is_array($config['field']) ? $config['field'] : [$config['field']];
            foreach ($fields as $name) {
                if (array_key_exists($name, $allMetaFields)) {
                    $arrGroups[$config['title']][$name] = $allMetaFields[$name];
                }
                if (array_key_exists($name, $arrMetaFields)) {
                    unset($arrMetaFields[$name]);
                }
            }
        }

        // alle felder die nicht gruppiert wurden in einen allgemeinen tab laden
        if (count($arrMetaFields) > 0 && !$damConfig['backend']['metadata']['hideCommon']) {
            $arrGroups = array_merge(['common' => $arrMetaFields], $arrGroups);
        }

        $arrGroups = $this->cleanUpEmptyGroups($item, $arrGroups, $damConfig['backend']['metadata']['readonly']);

        $this->view->metafieldGroups = $arrGroups;
        $this->view->readonlyMetaFields = Dam\Helper::withPermissionsHook()->getReadOnlyFields($item, $damConfig['backend']['metadata']['readonly']);
        $this->view->imageEditorEnabled = $damConfig['extension']['imageEditor']['enabled'];
        $this->view->onlyPresetDownload = $damConfig['download']['onlyPresetDownload'];

        // save data
        if ($request->isMethod('POST') && $this->view->editable) {
            $asset = $item->getAsset();
            $metadataAssocList = [];
            foreach ($asset->getMetadata() as $md) {
                $nameKeys = [$md['name']];
                if (!empty($md['language'])) {
                    $nameKeys[] = $md['language'];
                }
                $metadataAssocList[join('-', $nameKeys)] = $md;
            }

            foreach ($request->request->all() as $name => $value) {
                if (substr($name, 0, 9) == 'metadata$') {
                    list(, $name, $type, $lang) = explode('$', $name);

                    $nameKeys = [$name];
                    if (!empty($lang)) {
                        $nameKeys[] = $lang;
                    }

                    $key = join('-', $nameKeys);

                    if (!empty($value)) {
                        if ($type == 'date') {
                            $value = Carbon::parse($value)->getTimestamp();
                        } elseif ($type == 'textarea' && $value) {
                            $value = trim($value);
                        }

                        $metadataAssocList[$key] = [
                            'name' => $name,
                            'language' => $lang,
                            'type' => $type,
                            'data' => $value
                        ];
                    } else {
                        unset($metadataAssocList[$key]);
                    }
                }
            }

            $asset->setMetadata($metadataAssocList);
            $asset->save();
        }
    }

    /**
     * download asset binary
     *
     * @Route("/asset/downloadOriginal")
     */
    public function downloadOriginalAction(Request $request)
    {
        $asset = Asset::getById($request->get('id'));

        if ($asset->isAllowed('view')) {

            $eventDownload = new Event\Download(Dam\Item\AbstractItem::createInstance($asset));
            \Pimcore::getEventDispatcher()->dispatch(Dam\Events::DOWNLOAD_ASSET, $eventDownload);

            $response = new BinaryFileResponse($asset->getFileSystemPath());
            $response->headers->set('Content-Type', $asset->getMimetype());
            $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $asset->getFilename());

            return $response;
        }
    }

    /**
     * create asset download
     *
     * @Route("/asset/download")
     */
    public function downloadAction(Request $request)
    {
        // init
        $listAssets = [];
        $ids = [0];
        $downloadType = null;
        set_time_limit(0);
        $damConfig = $this->container->getParameter('pimcore_dam_config');

        // get ids
        if ($request->get('id')) {
            $ids = array_merge($ids, [$request->get('id')]);
        }
        if ($request->get('selectedItems')) {
            $ids = array_merge($ids, explode(',', $request->get('selectedItems')));
        }

        // get assets
        $list = clone $this->list;
        $list->addConditionParam(sprintf('id in (%s)', implode(',', $ids)), '');
        if ($list->count() == 0) {
            throw new \Exception('not allowed');
        }

        // create list for preview
        foreach ($list as $asset) {
            /* @var Asset $asset */
            $item = Dam\Item\AbstractItem::createInstance($asset);
            $this->setItemRights($item);
            $listAssets[] = $item;

            $downloadType = $downloadType === null || $downloadType == $item->getType()
                ? $item->getType()
                : false;
        }
        $downloadType = in_array($downloadType, ['Image', 'Video']) ? $downloadType : false;
        $this->view->downloadType = $downloadType;
        $this->view->listAssets = $listAssets;

        if ($request->isMethod('POST')) {
            $downloadFile = null;
            $downloadName = '';

            $unlink = false;
            if (count($listAssets) == 1) {
                // ohne zip
                $item = reset($listAssets);
                /* @var Dam\Item\AbstractItem $item */

                $asset = $item->getAsset();

                $eventDownload = new Event\Download($item);
                \Pimcore::getEventDispatcher()->dispatch(Dam\Events::DOWNLOAD_ASSET, $eventDownload);

                if ($request->get('type') == 'original') {
                    $downloadFile = $item->getAsset()->getFileSystemPath();
                    $downloadName = $item->getAsset()->getFilename();
                } else {
                    switch ($downloadType) {
                        case 'Image':
                            // create thumb
                            $config = null;

                            if ($request->get('preset')) {
                                $config = $request->get('preset');
                            } else {
                                $targetWidth = $request->get('imageratio') ? $asset->getWidth() : $request->get('width');
                                $targetHeight = $request->get('imageratio') ? $asset->getHeight() : $request->get('height');

                                $w = $request->get('unit', 'px') == 'cm'
                                    ? $targetWidth / 2.54 * $request->get('dpi')
                                    : $targetWidth;
                                $h = $request->get('unit', 'px') == 'cm'
                                    ? $targetHeight / 2.54 * $request->get('dpi')
                                    : $targetHeight;

                                $config = [
                                    'width' => (int)$w,
                                    'height' => (int)$h,
                                    'aspectratio' => $damConfig['frontend']['customize']['onlyPresetDownload'] ? 1 : (bool)$request->get('aspectratio'),
                                    'quality' => $request->get('quality'),
                                    'format' => $request->get('format'),
                                ];
                            }

                            $thumb = $item->getThumbnail($config);
                            /* @var Asset\Image\Thumbnail $thumb */
                            if (!file_exists($thumb->getFileSystemPath())) {
                                $thumb->generate(false);
                            }

                            $downloadName = basename($thumb->getFileSystemPath());
                            $downloadFile = $thumb->getFileSystemPath();

                            // convert to cmyk
                            if ($request->get('colorspace') == 'cmyk') {
                                $i = new \Imagick();
                                $i->readimage($downloadFile);

                                $imageColorspace = $i->getImageColorspace();

                                if (in_array($imageColorspace, [\Imagick::COLORSPACE_RGB, \Imagick::COLORSPACE_SRGB])) {
                                    if (\Pimcore\Image\Adapter\Imagick::getCMYKColorProfile() && \Pimcore\Image\Adapter\Imagick::getRGBColorProfile()) {
                                        $profiles = $i->getImageProfiles('*', false);
                                        // we're only interested if ICC profile(s) exist
                                        $has_icc_profile = (array_search('icc', $profiles) !== false);
                                        // if it doesn't have a CMYK ICC profile, we add one
                                        if ($has_icc_profile === false) {
                                            $i->profileImage('icc', \Pimcore\Image\Adapter\Imagick::getRGBColorProfile());
                                        }
                                        // then we add an RGB profile
                                        $i->profileImage('icc', \Pimcore\Image\Adapter\Imagick::getCMYKColorProfile());
                                        $i->setImageColorspace(\Imagick::COLORSPACE_CMYK);
                                    }
                                }

                                $i->writeimage();
                            }

                            break;

                        case 'Video':
                            $thumb = $item->getAsset()->getThumbnail($request->get('preset'));
                            if (array_key_exists($request->get('format'), $thumb['formats'])) {
                                $file = $thumb['formats'][$request->get('format')];
                                $downloadName = preg_replace('#^(.+)\..{3,}$#i', '\1', $item->getAsset()->getFilename()) . '.' . $request->get('format');
                                $downloadFile = PIMCORE_TEMPORARY_DIRECTORY . '/video-thumbnails' . $file;
                            }
                            break;
                    }
                }
            } else {
                // mit zip

                $downloadName = 'Download.zip';
                $downloadFile = PIMCORE_SYSTEM_TEMP_DIRECTORY . '/download-zip-' . uniqid('DamListener') . '.zip';
                $zip = new \ZipArchive();
                if ($zip->open($downloadFile, \ZipArchive::CREATE)) {
                    foreach ($listAssets as $item) {
                        $asset = $item->getAsset();

                        /* @var Asset\Video $video */

                        if ($request->get('type') == 'original') {
                            $eventDownload = new Event\Download($item);
                            \Pimcore::getEventDispatcher()->dispatch(Dam\Events::DOWNLOAD_ASSET, $eventDownload);

                            $zip->addFile($asset->getFileSystemPath(), 'original/' . $asset->getFilename());
                        } else {
                            switch ($downloadType) {
                                case 'Image':
                                    $config = null;

                                    if ($request->get('preset')) {
                                        $config = $request->get('preset');
                                        $folder = $request->get('preset');
                                    } else {
                                        $targetWidth = $request->get('imageratio') ? $asset->getWidth() : $request->get('width');
                                        $targetHeight = $request->get('imageratio') ? $asset->getHeight() : $request->get('height');

                                        $w = $request->get('unit', 'px') == 'cm'
                                            ? $targetWidth / 2.54 * $request->get('dpi')
                                            : $targetWidth;
                                        $h = $request->get('unit', 'px') == 'cm'
                                            ? $targetHeight / 2.54 * $request->get('dpi')
                                            : $targetHeight;

                                        $config = [
                                            'width' => (int)$w,
                                            'height' => (int)$h,
                                            'aspectratio' => $damConfig['frontend']['customize']['onlyPresetDownload'] ? 1 : (bool)$request->get('aspectratio'),
                                            'quality' => $request->get('quality'),
                                            'format' => $request->get('format')
                                        ];

                                        $folder = $this->translator->trans('dam.download.convert.custom');
                                    }

                                    $thumb = $item->getThumbnail($config);
                                    /* @var Asset\Image\Thumbnail $thumb */

                                    $name = basename($thumb->getFileSystemPath());
                                    $zip->addFile($thumb->getFileSystemPath(), $folder . '/' . $name);

                                    $eventDownload = new Event\Download($item);
                                    \Pimcore::getEventDispatcher()->dispatch(Dam\Events::DOWNLOAD_ASSET, $eventDownload);
                                    break;

                                case 'Video':
                                    $thumb = $asset->getThumbnail($request->get('preset'));
                                    if (array_key_exists($request->get('format'), $thumb['formats'])) {
                                        $file = $thumb['formats'][$request->get('format')];
                                        $name = preg_replace('#^(.+)\..{3,}$#i', '\1', $asset->getFilename()) . '.' . $request->get('format');
                                        $zip->addFile(PIMCORE_SYSTEM_TEMP_DIRECTORY . '/video-image-cache' . $file, $name);

                                        $eventDownload = new Event\Download($item);
                                        \Pimcore::getEventDispatcher()->dispatch(Dam\Events::DOWNLOAD_ASSET, $eventDownload);
                                    }
                                    break;
                            }
                        }
                    }
                }

                $zip->close();
                $unlink = true;
            }

            // download dialog erzeugen
            $response = new BinaryFileResponse($downloadFile);
            $response->headers->set('Content-Type', \Pimcore\Tool\Mime::detect($downloadFile));
            $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $downloadName);

            if ($unlink) {
                $response->deleteFileAfterSend(true);
            }

            return $response;
        }
    }

    /**
     * delete a asset
     *
     * @Route("/asset/delete")
     */
    public function deleteAction(Request $request)
    {
        // init
        $listDelete = [];
        $ids = [0];

        // get ids for delete
        if ($request->get('id')) {
            $ids = array_merge($ids, [$request->get('id')]);
        }
        if ($request->get('selectedItems')) {
            $ids = array_merge($ids, explode(',', $request->get('selectedItems')));
        }

        // get assets
        $list = clone $this->list;
        $list->addConditionParam(sprintf('id in (%s)', implode(',', $ids)), '');
        if ($list->count() == 0) {
            throw new \Exception('not allowed');
        }

        // create list for preview
        foreach ($list as $asset) {
            $item = Dam\Item\AbstractItem::createInstance($asset);
            $this->setItemRights($item);
            $listDelete[] = $item;
        }
        $this->view->listDelete = $listDelete;

        // delete asset
        if ($request->get('confirm') && $this->getUser()) {
            // wenn der user kein admin ist dann die rechte auf löschen nochmals prüfen
            foreach ($listDelete as $item) {
                $asset = $item->getAsset();
                if ($asset->isAllowed('delete')) {
                    // move to trash
                    $list = new \Pimcore\Model\Asset\Listing();
                    $list->setCondition("path LIKE '" . $asset->getRealFullPath() . "/%'");
                    $children = $list->getTotalCount();

                    if ($children <= 100) {
                        Recyclebin\Item::create($asset, $this->getUser()->getUser());
                    }

                    // delete
                    $asset->delete();
                }
            }

            // weiterleiten
            $url = $this->generateUrl('pimcore_dam_asset_list', ['pid' => $request->get('pid')]);

            return $this->redirect($url);
        }
    }

    /**
     * relocate assets
     *
     * @Route("/asset/relocate")
     */
    public function relocateAction(Request $request)
    {
        // init
        $user = $this->getUser()->getUser();
        $ids = [0];
        $pid = $request->get('pid');

        // parent
        $parent = Asset::getById($pid);
        $this->view->parent = $parent;

        $this->view->folderTreeList = $this->getFullFolderTree($pid);
        $this->view->folderTree = $this->getFolderTree($pid, 0, 1);

        // get ids
        if ($id = $request->get('id')) {
            $ids = array_merge($ids, [$id]);
        }
        if ($request->get('selectedItems')) {
            $ids = array_merge($ids, explode(',', $request->get('selectedItems')));
        }

        // get assets
        $list = clone $this->list;
        $list->addConditionParam(sprintf('id in (%s)', implode(',', $ids)), '');
        if ($list->count() == 0) {
            throw new \Exception('not allowed');
        }
        $this->view->listAssets = $list;

        // relocate asset
        if ($request->get('target') && $user) {
            // load parent
            $tmp = clone $this->list;
            $tmp->addConditionParam('type = ?', 'folder');
            $tmp->addConditionParam('id = ?', $request->get('target'));
            $targetFolder = $tmp->current();

            // rechte prüfen
            if (!$user->isAdmin()) {
                // FIXME sollte eigentlich aus dem asset gelesen werden
                $srcFolder = Asset\Folder::getById($request->get('pid'));

                if (!$targetFolder->isAllowed('create')) {
                    throw new \Exception('target not allowed');
                } elseif (!$srcFolder->isAllowed('delete')) {
                    throw new \Exception('source not allowed');
                }
            }

            // move assets if we have a valid folder
            if ($targetFolder instanceof Asset\Folder) {
                /* @var Asset[] $list */
                foreach ($list as $asset) {
                    $asset->setParent($targetFolder);
                    $filename = $asset->getFilename();
                    // check for duplicate full path
                    while (\Pimcore\Model\Asset::getByPath((string)$targetFolder . '/' . $filename)) {
                        $filename = explode('.', $filename);
                        $extension = $filename[sizeof($filename) - 1];
                        unset($filename[sizeof($filename) - 1]);
                        $filename = implode('.', $filename);
                        $filename .= '_copy';
                        $filename .= '.' . $extension;
                    }
                    $asset->setFilename($filename);
                    $asset->save();
                }

                // weiterleiten
                $pid = $request->get('follow')
                    ? $request->get('target')
                    : $request->get('pid');

                $url = $this->generateUrl('pimcore_dam_asset_list', ['pid' => $pid]);

                return $this->redirect($url);
            }
        }
    }

    /**
     * @param $ids
     *
     * @return $this
     *
     * @deprecated in die facade verschieben !
     */
    private function getCollectionList($ids)
    {
        $ids = explode(',', $ids);
        foreach ($ids as $i => $id) {
            $ids[$i] = (int)$id;
        }
        $ids[] = 0;
        $listCollection = Dam\Facade::getCollectionList();

        return $listCollection->addConditionParam(sprintf('id IN(%s)', implode(',', $ids)), '');
    }

    /**
     * upload assets
     *
     * @Route("/asset/upload")
     */
    public function uploadAction(Request $request)
    {
        // init
        $list = $this->list;
        $json = [];

        // get upload folder
        $list->addConditionParam(sprintf('id = %d', $request->get('pid')), '');
        $folder = $list->current();

        // save
        if ($folder instanceof Asset\Folder && is_iterable($_FILES) && count($_FILES) > 0 && $folder->isAllowed('create')) {
            // collection laden die zugewiesen werden sollen
            $listCollection = $this->getCollectionList($request->get('collection'));

            if ($request->get('archive') == '1') {
                // zip archive upload > extract

                $zipFile = $_FILES['file']['tmp_name'];
                $tmpDir = PIMCORE_SYSTEM_TEMP_DIRECTORY . '/zip-import';
                $zip = new \ZipArchive;

                if ($zip->open($zipFile) === true) {
                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        $path = $zip->getNameIndex($i);

                        if ($path !== false) {
                            if ($zip->extractTo($tmpDir . '/', $path)) {
                                $tmpFile = $tmpDir . '/' . preg_replace('@^/@', '', $path);

                                $filename = \Pimcore\File::getValidFilename(basename($path));

                                $relativePath = '';
                                if (dirname($path) != '.') {
                                    $relativePath = dirname($path);
                                }

                                $parentPath = $folder->getFullPath() . '/' . preg_replace('@^/@', '', $relativePath);
                                $parent = Asset\Service::createFolderByPath($parentPath);

                                // check for duplicate filename
                                $filename = $this->getSafeFilename($parent->getFullPath(), $filename);

                                if ($parent->isAllowed('create')) {
                                    $asset = Asset::create($parent->getId(), [
                                        'filename' => $filename,
                                        'sourcePath' => $tmpFile,
                                        'userOwner' => $this->getUser()->getId()
                                    ]);

                                    $json[$_FILES['file']['name']]['id'] = $asset->getId();
                                    $json[$_FILES['file']['name']]['name'] = $asset->getFilename();

                                    // assign collection
                                    foreach ($listCollection as $collection) {
                                        $collection->append($asset);
                                    }

                                    @unlink($tmpFile);
                                }
                            }
                        }
                    }
                    $zip->close();
                }
            } else {
                // single - multi file upload

                foreach ($_FILES as $item) {
                    $name = \Pimcore\File::getValidFilename($request->get('filename', $item['name']));
                    $name = $this->getSafeFilename($folder->getFullPath(), $name);

                    $asset = Asset::create($folder->getId(), [
                        'filename' => $name,
                        'sourcePath' => $item['tmp_name'],
                        'userOwner' => $this->getUser()->getId()
                    ]);

                    // save metadata
                    $metadata = [];
                    $fileIndex = 0;
                    $fileIndexBatch = 1;
                    foreach ($request->request->all() as $name => $value) {
                        if (substr($name, 0, 9) == 'metadata$' && ($value[$fileIndex] || $value[$fileIndexBatch])) {
                            list(, $name, $type, $lang) = explode('$', $name);

                            $value = $value[$fileIndex] ?: $value[$fileIndexBatch];
                            if ($type == 'date' && $value) {
                                $value = Carbon::parse($value)->getTimestamp();
                            }

                            if ($type == 'textarea' && $value) {
                                $value = trim($value);
                            }

                            // assign collection
                            if ($type == 'input' && $name == 'collection' && $value) {
                                $additionalListCollection = $this->getCollectionList($value);
                                foreach ($additionalListCollection as $collection) {
                                    $collection->append($asset);
                                    $collection->save();
                                }
                            }

                            $metadata[] = [
                                'name' => $name,
                                'language' => $lang,
                                'type' => $type,
                                'data' => $value
                            ];
                        }
                    }

                    if (count($metadata) > 0) {
                        $asset->setMetadata($metadata);
                        $asset->save();
                    }

                    // assign collection
                    foreach ($listCollection as $collection) {
                        $collection->append($asset);
                    }

                    $json[$item['name']]['id'] = $asset->getId();
                    $json[$item['name']]['name'] = $asset->getFilename();
                }
            }

            // save collection
            foreach ($listCollection as $collection) {
                $collection->save();
            }
        }

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse($json);
        } else {
            $url = $this->generateUrl('pimcore_dam_asset_list', ['pid' => $request->get('pid')]);

            return $this->redirect($url);
        }
    }

    /**
     * import asset from remote server
     *
     * @Route("/asset/uploadUrl")
     */
    public function uploadUrlAction(Request $request)
    {
        // init
        $list = $this->list;

        // get upload folder
        $list->addConditionParam(sprintf('id = %d', $request->get('pid')), '');
        $folder = $list->current();

        if ($request->get('url')) {
            // get binary
            $content = file_get_contents($request->get('url'));

            // get name
            $name = $request->get('name');
            if ($name) {
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->buffer($content);

                $name .= '.' . explode('/', $mime)[1];
            } else {
                $name = basename($request->get('url'));
            }

            if ($content) {
                $asset = Asset::create($folder->getId(), ['userOwner' => $this->getUser()->getId()], false);

                $name = $this->getSafeFilename($folder->getFullPath(), \Pimcore\File::getValidFilename($name));
                $asset->setFilename($name);
                $asset->setData($content);

                $asset->save();
            }

            $url = $this->generateUrl('pimcore_dam_asset_list', ['pid' => $request->get('pid')]);

            return $this->redirect($url);
        }
    }

    /**
     * replace asset
     *
     * @Route("/asset/replace")
     */
    public function replaceAction(Request $request)
    {
        // load asset
        $list = clone $this->list;
        $list->addConditionParam('id = ?', $request->get('id'));
        if ($list->count() == 0) {
            throw new \Exception('not allowed');
        }
        $asset = $list->current();
        /* @var Asset $asset */

        // replace
        $stream = fopen($_FILES['file']['tmp_name'], 'r+');
        $asset->setStream($stream);
        $asset->setCustomSetting('thumbnails', null);
        $asset->setUserModification($this->getUser()->getId());

        if ($asset->isAllowed('publish')) {
            $asset->save();
        } else {
            throw new \Exception('missing permission');
        }

        $url = $this->generateUrl('pimcore_dam_asset_detail', ['id' => $asset->getId()]);

        return $this->redirect($url);
    }

    /**
     * create new folder
     *
     * @Route("/asset/createfolder")
     */
    public function createFolderAction(Request $request)
    {
        // init
        $list = $this->list;

        // get upload folder
        $list->addConditionParam(sprintf('id = %d', $request->get('pid')), '');
        $parent = $list->current();
        $this->view->parent = $parent;

        if ($request->isMethod('POST') && $request->get('name')) {
            if ($parent instanceof Asset\Folder && $parent->isAllowed('create')) {
                $folder = Asset\Folder::create($parent->getId(), [], false);
                $name = \Pimcore\File::getValidFilename($request->get('name'));
                $folder->setFilename($name);
                $folder->setType('folder');
                $folder->save();
            }

            $url = $this->generateUrl('pimcore_dam_asset_list', ['pid' => $request->get('pid')]);

            return $this->redirect($url);
        }
    }

    /**
     * Retrieve the asset folder tree for a given asset folder id.
     *
     * @param $id               the folder id
     * @param bool $toParent exclude the current folder in tree
     *
     * @return array            array of asset folders
     */
    protected function getFolderTree($id, $toParent = false, $getIds = false)
    {
        if ($currentFolder = \Pimcore\Model\Asset\Folder::getById($id)) {
            $tempParent = $currentFolder->getParent();
            $tree = [];
            if (!$toParent) {
                $tree[] = $getIds ? $currentFolder->getId() : $currentFolder;
            }
            while ($tempParent) {
                $tree[] = $getIds ? $tempParent->getId() : $tempParent;
                $tempParent = $tempParent->getParent();
            }

            return array_reverse($tree);
        }
    }

    /**
     * Load full folder tree hierarchy and pass to view.
     *
     * @Route("/asset/folderTree")
     */
    public function folderTreeAction(Request $request)
    {
        $target = $request->get('target', 1);

        $this->view->folderTreeList = $this->getFullFolderTree($target);

        $this->view->folderTree = $this->getFolderTree($target, 0, 1);
    }

    /**
     * Retrieve a lightweight list of an asset folder's folder-childs.
     *
     * @param int $id
     *
     * @return array
     */
    protected function getFolderList(int $id)
    {

        // get targets
        $listTarget = clone $this->list;
        $listTarget->addConditionParam('type = ?', 'folder');
        $listTarget->addConditionParam('parentId = ?', $id);
        $listTarget->setOrderKey('filename');
        $listTarget->setLimit(50);

        $list = [];
        foreach ($listTarget as $folder) {
            /* @var Asset\Folder $folder */
            $item = new \stdClass;
            $item->id = $folder->getId();
            $item->name = $folder->getFilename();
            $item->path = $folder->getFullPath();
            $item->create = $folder->isAllowed('create');
            $item->delete = $folder->isAllowed('delete');

            $list[] = $item;
        }

        return $list;
    }

    /**
     * Retrieve a lightweight list of an asset folder's folder-childs
     * and return as json.
     * @Route("/asset/getFolderList")
     */
    public function getFolderListAction(Request $request)
    {
        $list = $this->getFolderList($request->get('target', 1));
        return new JsonResponse($list);
    }

    /**
     * For every folder in a hierarchy get a two dimensional array of
     * child folders.
     *
     * [ {id} => {[...]},
     *   {id} => {[...]} ]
     */
    private function getFullFolderTree($target)
    {
        $folderTree = $this->getFolderTree($target, 0, 1);

        $list = [];

        foreach ($folderTree as $folderId) {
            $list[$folderId] = $this->getFolderList($folderId);
        }

        return $list;
    }

    /**
     * @Route("/asset/batchUpdate")
     */
    public function batchUpdateAction(Request $request)
    {
        // init
        $listAssets = [];
        $ids = [0];
        $downloadType = null;
        set_time_limit(0);

        // get ids
        if ($request->get('selectedItems')) {
            $ids = array_merge($ids, explode(',', $request->get('selectedItems')));
        }

        // get assets
        $list = clone $this->list;
        $list->addConditionParam(sprintf('id in (%s)', implode(',', $ids)), '');
        if ($list->count() == 0) {
            throw new \Exception('not allowed');
        }

        // create list for preview
        foreach ($list as $asset) {
            $item = Dam\Item\AbstractItem::createInstance($asset);

            // set rights
            $this->setItemRights($item);

            // set collections
            $list = clone Dam\Facade::getCollectionList();
            $list->addConditionParam(sprintf('assigned like "%%,%d,%%"', $item->getId()), '');
            $item->setCollectionList($list);

            $listAssets[$item->getId()] = $item;
        }
        $this->view->listAssets = $listAssets;

        $collectionList = Dam\Facade::getCollectionList(\Pimcore\Bundle\DamBundle\Dam\Collection\Permissions::EDIT);
        $this->view->availableCollections = $collectionList;


        // save the url where the user come from
        $samePageRegex = '/batchUpdate/';
        $session = $this->get('session');
        if (preg_match($samePageRegex, $_SERVER['HTTP_REFERER']) != 1) {
            $session->set('backUrl', $_SERVER['HTTP_REFERER']);
            $session->save();
        }
        $this->view->backUrl = $session->get('backUrl');



        // save
        if ($request->isMethod('POST')) {
            // extract data
            $metadata = [];
            $itemData = [];

            $params = $request->request->all();

            foreach ($params as $name => $value) {
                list(, $name, $id, $type, $lang) = explode('$', $name);
                if ($type == 'checkbox' && $id == '0') {
                    foreach ($ids as $id) {
                        $params['metadata$' . $name . '$' . $id . '$' . $type . '$'] = ['0' => '1'];
                    }
                }
            }

            foreach ($params as $name => $value) {
                if (substr($name, 0, 9) == 'metadata$') {
                    list(, $name, $id, $type, $lang) = explode('$', $name);
                    $value = $value[0];

                    // get batch data
                    $batchKey = implode('$', ['metadata', $name, 0, $type, $lang]);
                    $batchValue = $request->get($batchKey)[0];
                    if ((!$value || !in_array($name, Dam\Helper::getSelectableFields())) && $batchValue) {
                        $value = $batchValue;
                    } else {
                        $batchMethod = $request->get(implode('$', [$name, 'selectmethod']));

                        if ($batchMethod == 'replace') {
                            $value = $batchValue;
                        } else {
                            $value = !$batchValue ? $value : $value . ',' . $batchValue;
                        }
                    }

                    if ($type == 'date' && $value) {
                        $value = Carbon::parse($value)->getTimestamp();
                    }

                    if ($type == 'textarea' && $value) {
                        $value = trim($value);
                    }

                    $metadata[$id][] = [
                        'name' => $name,
                        'language' => $lang,
                        'type' => $type,
                        'data' => $value
                    ];
                }
                if (substr($name, 0, 5) == 'item$') {
                    list(, $id, $type) = explode('$', $name);

                    switch ($type) {
                        case 'collection':
                            // get batch collections
                            $batchValue = explode(',', $request->get('batch$collection'));
                            // get item collections
                            $itemValue = explode(',', $request->get('item$' . $id . '$collection'));
                            // get append method
                            $batchMethod = $request->get('batch$collection$selectmethod');

                            if ($batchMethod == 'replace') {
                                $value = $batchValue;
                            } else {
                                $value = !$batchValue ? $batchValue : array_merge($itemValue, $batchValue);
                            }

                            break;
                    }

                    $itemData[$id][$type] = $value;
                }
            }

            // save
            foreach ($listAssets as $item) {
                /* @var Dam\Item\AbstractItem $item */
                $asset = $item->getAsset();

                // set metadata
                if (array_key_exists($item->getId(), $metadata)) {
                    $asset->setMetadata($metadata[$item->getId()]);
                }

                // relocate
                $pid = $itemData[$item->getId()]['pid'] ?: $request->get('batch$pid');
                if ($pid) {
                    $srcFolder = $item->getAsset()->getParent();
                    $targetFolder = Asset\Folder::getById($pid);

                    if (!$targetFolder->isAllowed('create')) {
                        throw new \Exception('target not allowed');
                    } elseif (!$srcFolder->isAllowed('delete')) {
                        throw new \Exception('source not allowed');
                    }

                    $asset->setParent($targetFolder);
                }

                $asset->save();
            }

            // assign collection
            foreach ($collectionList as $collection) {
                /* @var Collection $collection */

                foreach ($itemData as $id => $types) {
                    foreach ($types as $type => $data) {
                        if ($type == 'collection') {
                            if (!$collection->isEditable()) {
                                continue;
                            }

                            $asset = $listAssets[$id]->getAsset();
                            if (in_array($collection->getId(), $data)) {
                                $collection->append($asset);
                            } else {
                                $collection->remove($asset);
                            }
                        }
                    }
                }

                $collection->save();
            }
        }
    }

    /**
     * @param Asset $asset
     *
     * @return array
     */
    protected function getFolderPath(Asset $asset, Request $request)
    {
        // current folder and breadcrumb
        $breadCrumb = [];
        $folder = $asset;
        do {
            $sibling = [];
            if ($folder->getParent()) {
                $params = $request->query->all();
                unset($params['pid']);
                $l = Dam\Facade::getAssetList();
                $l->filterParams($params);
                $l->setOrderKey('filename');
                $l->addConditionParam(sprintf('type like "folder" AND parentId = %d AND id != %d', $folder->getParent()->getId(), $folder->getId()), '');

                $sibling = $l->getItems(0, 30);
            }

            $item = [
                'folder' => $folder,
                'sibling' => $sibling
            ];

            array_unshift($breadCrumb, $item);
        } while (($folder = $folder->getParent()) && $folder->getId() != 1);

        return $breadCrumb;
    }

    /**
     * @param Dam\Item\AbstractItem $item
     *
     * @return Dam\Item\AbstractItem
     */
    protected function setItemRights(Dam\Item\AbstractItem $item)
    {
        $asset = $item->getAsset();

        // rechte setzen
        $item->setAllowShare($this->getUser()->getUser()->isAllowed('plugin_dam_share'));
        $item->setAllowCollection($this->getUser()->getUser()->isAllowed('plugin_dam_collection'));
        $item->setAllowDownload(true);
        $item->setAllowDelete($asset->isAllowed('delete'));
        $item->setAllowUpdateMetaData($asset->isAllowed('publish'));

        $event = new Event\ItemRights($item);
        \Pimcore::getEventDispatcher()->dispatch(Dam\Events::ITEM_RIGHTS, $event);

        return $item;
    }

    /**
     * @param string $targetPath
     * @param string $filename
     *
     * @return string
     */
    protected function getSafeFilename($targetPath, $filename)
    {
        $originalFilename = $filename;
        $count = 1;

        if ($targetPath == '/') {
            $targetPath = '';
        }

        while (true) {
            if (Asset\Service::pathExists($targetPath . '/' . $filename)) {
                $filename = str_replace('.' . \Pimcore\File::getFileExtension($originalFilename), '_' . $count . '.' . \Pimcore\File::getFileExtension($originalFilename), $originalFilename);
                $count++;
            } else {
                return $filename;
            }
        }
    }

    protected function getGrid()
    {
        return new Dam\Grid();
    }
    /**
     * @param array $params
     *
     * @return \Pimcore\Bundle\DamBundle\Model\Asset\Listing
     */
    protected function createGridFromParams(array $params)
    {
        // init
        $list = clone $this->list;

        // filter params
        $list->filterParams($params);

        // set order, folder, files
        $list->setOrderKey('if(type = "folder", 1, 0) desc, path, filename asc', false);


        /* If parent id given, enable persisted sort in uiconfig, else use temporary sort. */
        $uiConfig = \Pimcore\Bundle\DamBundle\Model\UIConfiguration::createOrUpdate($params['pid'] ?: 1, $params);

        /* enable temporary view if in collection or search view */
        $enableTemporarySort = !$params['view'] && !$params['collection'];

        if ($uiConfig && $enableTemporarySort) {
            $sort = $uiConfig->getSort();
            $order = $uiConfig->getOrder();
        } else {
            $sort = $params['reset'] == 'sort' || !$params['sort'] ? '' : Dam\Helper::getValidSortCriteria($params['sort']);
            $order = $params['reset'] == 'order' || !$params['order'] ? '' : Dam\Helper::getValidOrderCriteria($params['order']);
        }

        /* Set view options for sort and order criteria */
        foreach ($this->getSortingOptions($sort, $order) as $name => $config) {
            $this->view->$name = $config;
        }

        // sort folders before other types ascending by filename,
        // sort the remaining elements via given sort criteria $sort
        $sort = Dam\Helper::getValidSortCriteria($sort);
        $order = Dam\Helper::getValidOrderCriteria($order);
        $orderKey = <<<SQL
          path,
          if(type="folder", 1, 0 ) desc,
          if(type="folder", filename, null) asc,
          if(type!="folder",$sort,null)  $order
SQL;

        $list->setOrder(null);
        $list->setOrderKey($orderKey, false);


        // set current sort & ordering to view
        $this->view->sort = $sort;
        $this->view->order = $order;


        return $list;
    }

    /**
     * Get and array of view specific options for the sorting and
     * order criterias in the asset list.
     *
     * @param $currentSort
     * @param $currentOrder
     *
     * @return array
     */
    protected function getSortingOptions($currentSort, $currentOrder)
    {
        $options = ['unsetKey' => 'unset'];

        $options['defaultOrder'] = 'asc';
        $options['defaultSort'] = 'creationDate';

        foreach (Dam\Helper::getAssetSortOptions() as $sortOption) {
            if (\Pimcore\Bundle\DamBundle\Model\Asset\Listing::hasOrderKey($sortOption['criteria'])) {
                $default = $sortOption['default'];
                if ($default) {
                    $options['defaultSort'] = $sortOption['criteria'];
                }
                $options['sortOptions'][$sortOption['criteria']] = ['icon' => (string)$sortOption['iconClass']];
            }
        }

        // include unset options only if sort is set
        if ($currentSort) {
            $options['sortOptions'] = array_merge([$options['unsetKey'] => ['icon' => '']], $options['sortOptions']);
        }

        $options['orderOptions'] = [
            'asc' => ['icon' => 'glyphicon-sort-by-attributes'],
            'desc' => ['icon' => 'glyphicon-sort-by-attributes-alt']
        ];
        // include unset options only if order is set
        if ($currentOrder) {
            $options['orderOptions'] = array_merge([$options['unsetKey'] => ['icon' => '']], $options['orderOptions']);
        }

        return $options;
    }

    /**
     * Removes all metadata-groups/tabs from an asset that do only have empty fields
     * @param $item
     * @param $metafieldGroups
     * @param $readonlyMetaFields
     * @return array
     */
    protected function cleanUpEmptyGroups($item, $metafieldGroups, $readonlyMetaFields)
    {
        $groups = [];
        foreach ($metafieldGroups as $group => $fields) {
            $groupEmtpy = true;
            foreach ($fields as $name => $field) {
                $arrLang = $field['language']
                    ? \Pimcore\Tool::getValidLanguages()
                    : [null];
                $isEditable = $field['type'] == 'input' || $field['type'] == 'textarea' || $field['type'] == 'date' || $field['type'] == 'checkbox' || $field['type'] == 'select';
                if ($isEditable && !in_array($name, $readonlyMetaFields)) {
                    $groupEmtpy = false;
                } else {
                    //avoid printing not editable attributes with no value set
                    if (!$isEditable || in_array($name, $readonlyMetaFields)) {
                        foreach ($arrLang as $lang) {
                            if ($item->getAsset()->getMetadata($name, $lang)) {
                                $groupEmtpy = false;
                                break;
                            }
                        }
                    }
                }

                if (!$groupEmtpy) {
                    break;
                }
            }
            if (!$groupEmtpy) {
                $groups[$group] = $fields;
            }
        }
        return $groups;
    }
}
