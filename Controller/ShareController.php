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
use Pimcore\Bundle\DamBundle\Model;
use Pimcore\Controller\FrontendController;
use Pimcore\Db;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ShareController extends FrontendController
{
    use ControllerAware {
        ControllerAware::onKernelController as __onKernelController;
    }

    /**
     * @var Dam\Share\AbstractShare
     */
    protected $share;

    /**
     * rechte prüfen
     *
     * @param FilterControllerEvent $event
     *
     * @return null|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        parent::onKernelController($event);

        $this->__onKernelController($event);
        $this->initPublicShare($event);
    }

    protected function getShareRoutes(): array
    {
        return ['pimcore_dam_share_tokenlist', 'pimcore_dam_share_tokendetail', 'pimcore_dam_share_tokeninvalid', 'pimcore_dam_share_tokengetimage'];
    }

    /**
     * @param FilterControllerEvent $event
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|null
     *
     * @throws \Exception
     */
    protected function initPublicShare(FilterControllerEvent $event)
    {
        $request = $event->getRequest();
        $currentRoute = $request->get('_route');

        if (in_array($currentRoute, $this->getShareRoutes())) {
            // load terms
            $terms = Model\Terms::getByLang($this->view->language);
            $this->view->terms = $terms ? $terms->getTerms() : null;

            // load token
            $list = new Model\Share\Listing();
            $list->addConditionParam('(expire is null OR expire >= UNIX_TIMESTAMP())', '');
            $list->addConditionParam(sprintf('token = %s', $list->quote($request->get('t'))), '');

            if ($list->count() == 0) {
                if ($currentRoute != 'pimcore_dam_share_tokeninvalid') {
                    $url = $this->generateUrl('pimcore_dam_share_tokeninvalid', ['t' => $request->get('t')]);

                    $event->setController(function () use ($url) {
                        return $this->redirect($url);
                    });
                }
            } else {
                $this->share = Dam\Share\AbstractShare::createInstance($list->current());
                $this->view->share = $this->share;
            }
        } else {
            // check permission
            if (!$this->getUser()->getUser()->isAllowed('plugin_dam_share')) {
                throw new \Exception('not allowed');
            }
        }
    }

    /**
     * alle freigaben anzeigen
     *
     * @Route("/share/list")
     */
    public function listAction(Request $request)
    {
        $list = new Model\Share\Listing();
        if (!$this->getUser()->getUser()->isAdmin()) {
            $list->addConditionParam(sprintf('userId = %d', $this->getUser()->getId()), '');
        }

        $list->setOrderKey(['expire', 'createdDate']);
        $list->setOrder(['desc', 'asc']);

        $listExpire = [];
        $listStatic = [];
        foreach ($list as $item) {
            if ($item->getExpire()) {
                $listExpire[] = $item;
            } else {
                $listStatic[] = $item;
            }
        }
        $this->view->listExpire = $listExpire;
        $this->view->listStatic = $listStatic;
    }

    /**
     * freigabe bearbeiten
     *
     * @Route("/share/edit")
     */
    public function editAction(Request $request)
    {
        // edit existing share?
        if ($request->get('id')) {
            $this->view->id = $request->get('id');

            $list = new Model\Share\Listing();
            if (!$this->getUser()->getUser()->isAdmin()) {
                $list->addConditionParam(sprintf('userId = %d', $this->getUser()->getId()), '');
            }
            $list->addConditionParam(sprintf('id = %d', $request->get('id')), '');

            if ($list->count() == 0) {
                throw new\Exception('not allowed');
            }

            $share = $list->current();
        } else {
            // set defaults
            $share = new Model\Share();
            $share->setUserId($this->getUser()->getId());
            $share->setCreationDate(time());

            $expire = new Carbon();
            $expire->addMonth(1);
            $share->setExpire($expire->getTimestamp());

            $damConfig = $this->container->getParameter('pimcore_dam_config');
            $configTermsPermissionActive = $this->view->configTermsPermissionActive = $damConfig['extension']['shareAcceptTerms']['enabled'];

            if ($request->get('asset') || $request->get('selectedItems')) {
                // get ids
                $ids = [];
                if ($request->get('asset')) {
                    $ids = array_merge($ids, [$request->get('asset')]);
                }
                if ($request->get('selectedItems')) {
                    $ids = array_merge($ids, explode(',', $request->get('selectedItems')));
                }

                // get assets
                $list = Dam\Facade::getAssetList();
                $list->addConditionParam(sprintf('id in (%s)', implode(',', $ids)), '');
                if ($list->count() == 0) {
                    throw new\Exception('not allowed');
                }

                // create list
                $ids = [];
                $listItems = [];
                $referenceType = null;
                foreach ($list as $asset) {
                    if ($asset->getType() != 'folder') {
                        $item = Dam\Item\AbstractItem::createInstance($asset);
                        $listItems[] = $item;
                        $ids[] = $item->getId();

                        if ($configTermsPermissionActive) {
                            $this->checkShareCopyrightInformation('asset', $damConfig, $share, $asset);
                        }

                        $referenceType = $referenceType === null || $referenceType == $item->getType()
                            ? $item->getType()
                            : false;
                    }
                }

                $referenceType = 'asset' . strtolower(in_array($referenceType, ['Image', 'Video']) ? '-' . $referenceType : '');
                $share->setType($referenceType);
                $share->setReferenceIds(',' . implode(',', $ids) .  ',');
            } elseif ($request->get('folder')) {
                // get folder
                $list = Dam\Facade::getAssetList();
                $list->addConditionParam(sprintf('id = %d', $request->get('folder')), '');
                if ($list->count() == 0) {
                    throw new\Exception('not allowed');
                }

                // create list
                $ids = [];
                foreach ($list as $asset) {
                    if ($asset->getType() == 'folder') {
                        $item = Dam\Item\AbstractItem::createInstance($asset);
                        $ids[] = $item->getId();

                        if ($configTermsPermissionActive) {
                            $this->checkShareCopyrightInformation('folder', $damConfig, $share, $asset);
                        }
                    }
                }

                $share->setType('folder');
                $share->setReferenceIds(',' . implode(',', $ids) .  ',');
            } elseif ($request->get('collection')) {
                // get collection
                $list = Dam\Facade::getCollectionList(Dam\Collection\Permissions::ADMIN);
                $list->addConditionParam(sprintf('id = %d', $request->get('collection')), '');
                if ($list->count() == 0) {
                    throw new\Exception('not allowed');
                }

                $collection = $list->current();
                $share->setType('collection');
                $share->setReferenceIds(',' . $collection->getId() . ',');

                if ($configTermsPermissionActive) {
                    $this->checkShareCopyrightInformation('collection', $damConfig, $share, $collection);
                }
            }

            // create token or get it from the params
            $token = $share->getReferenceIds() . time() . 'v%H§nf#vu?' . $this->getUser()->getId();
            $token = $request->get('token', hash('crc32', $token));
            $share->setToken($token);
        }

        // view
        $this->view->share = $share;

        // save
        if ($request->isMethod('POST')) {
            // set expire date
            $expire = null;
            if ($request->get('expire-active')) {
                $expire = Carbon::parse($request->get('expire'))->getTimestamp();
            }
            $share->setExpire($expire);

            // set config
            $config = [];
            foreach ($request->request->all() as $key => $value) {
                if (strtok($key, '_') == 'config') {
                    $config[substr($key, 7)] = $value;
                }
            }
            $share->setConfig(count($config) > 0 ? json_encode($config) : null);

            $share->setName(strip_tags($request->get('name')));
            $share->setTermsComment(strip_tags($request->get('terms-comment')));

            // save
            $share->save();

            $this->view->showSavedDialog = true;
        }
    }

    /**
     * @param             $type
     * @param             $damConfig
     * @param Model\Share $share
     * @param             $element
     */
    protected function checkShareCopyrightInformation($type, $damConfig, Model\Share $share, $element)
    {
        $copyrightKey = $damConfig['extension']['shareAcceptTerms']['metaDataKeyCopyright'];
        $copyrightMandatoryKey = $damConfig['extension']['shareAcceptTerms']['metaDataKeyCopyrightMandatory'];

        switch ($type) {
            case 'asset':
                $copyright = '';
                if ($copyrightKey) {
                    $copyright = $element->getMetadata($copyrightKey);
                }
                if ($copyrightMandatoryKey) {
                    $copyrightMandatory = !empty($element->getMetadata($copyrightMandatoryKey));
                }

                if ($copyrightMandatory) {
                    $share->setTermsComment($share->getTermsComment() . "\n" . $copyright);
                    $shareConfig = json_decode($share->getConfig(), true);
                    $shareConfig['accept-termsconditions'] = true;
                    $share->setConfig(json_encode($shareConfig));
                }

                break;

            case 'folder':

                if ($copyrightMandatoryKey) {
                    $db = Db::get();
                    $copyrightTexts = $db->fetchCol('SELECT data FROM assets_metadata WHERE cid IN
                    (SELECT cid FROM assets_metadata WHERE cid IN
                        (SELECT id FROM assets WHERE path LIKE ?)
                        AND name = ? AND data = 1)
                    AND name = ? LIMIT 10;', [$element->getFullPath() . '/%', $copyrightMandatoryKey, $copyrightKey]);

                    if ($copyrightTexts) {
                        $shareConfig = json_decode($share->getConfig(), true);
                        $shareConfig['accept-termsconditions'] = true;
                        $share->setConfig(json_encode($shareConfig));

                        foreach ($copyrightTexts as $text) {
                            $share->setTermsComment($share->getTermsComment() . "\n" . $text);
                        }
                    }
                }

                break;

            case 'collection':

                if ($copyrightMandatoryKey) {
                    $db = Db::get();
                    $copyrightTexts = $db->fetchCol('SELECT data FROM assets_metadata WHERE cid IN
                    (SELECT cid FROM assets_metadata WHERE cid IN
                        (SELECT id FROM assets WHERE id IN (' . implode(',', $element->getItemIds()) . '))
                        AND name = ? AND data = 1)
                    AND name = ? LIMIT 10;', [$copyrightMandatoryKey, $copyrightKey]);

                    if ($copyrightTexts) {
                        $shareConfig = json_decode($share->getConfig(), true);
                        $shareConfig['accept-termsconditions'] = true;
                        $share->setConfig(json_encode($shareConfig));

                        foreach ($copyrightTexts as $text) {
                            $share->setTermsComment($share->getTermsComment() . "\n" . $text);
                        }
                    }
                }

                break;
        }
    }

    /**
     * delete token
     *
     * @Route("/share/delete")
     */
    public function deleteAction(Request $request)
    {
        // init
        $share = Model\Share::getById($request->get('id'));
        $this->view->share = $share;

        if ($share && $request->get('confirm')) {
            // rechte prüfen
            if (!$this->getUser()->getUser()->isAdmin()) {
                if ($share->getUserId() != $this->getUser()->getId()) {
                    throw new \Exception('not allowed');
                }
            }

            // delete
            $share->delete();

            $url = $this->generateUrl('pimcore_dam_share_list');

            return $this->redirect($url);
        }
    }

    /**
     * alle assets anzeigen die zu diesem token gehören
     *
     * @Route("/share/token/list")
     */
    public function tokenListAction(Request $request)
    {
        if ($request->get('download')) {
            // init
            $config = (array)$this->share->getConfig();

            if (!$config || !$config['enable-download']) {
                throw new\Exception('download not allowed');
            }

            // settings
            $viewCaption = \Pimcore\File::getValidFilename($this->share->getToken());
            $zipFile = PIMCORE_SYSTEM_TEMP_DIRECTORY . '/download-zip-' . uniqid('DamListener') . '.zip';

            $zip = new \ZipArchive();
            if ($zip->open($zipFile, \ZipArchive::CREATE)) {

                //specify download-format
                $downloadOriginal =
                    ($this->share->getType() != 'asset-image' && $this->share->getType() != 'asset-video') ||
                    (array_key_exists('download-format', $config) && $config['download-format'] == 'original');

                foreach ($this->share->getAssets() as $item) {
                    $asset = $item->getAsset();

                    $eventDownload = new Event\Download($item);
                    \Pimcore::getEventDispatcher()->dispatch(Dam\Events::DOWNLOAD_ASSET, $eventDownload);

                    if ($downloadOriginal) {

                        //add original file to zip
                        $zip->addFile($asset->getFileSystemPath(), $viewCaption . '/original/' . $asset->getId() . '-' . $asset->getFilename());
                    } elseif ($this->share->getType() == 'asset-image') {
                        $cnf = array_key_exists('asset-image-preset', $config) && $config['asset-image-preset']
                            ? $config['asset-image-preset']
                            : [
                                'width' => $config['asset-image-width'],
                                'height' => $config['asset-image-height'],
                                'aspectratio' => (bool)$config['asset-image-aspectratio'],
                                'quality' => $config['asset-image-quality'],
                                'format' => $config['asset-image-format']
                            ];

                        $thumb = $item->getThumbnail($cnf);

                        $zip->addFile($thumb->getFileSystemPath(), $viewCaption . '/' . $asset->getId() . '-' . basename($thumb->getFileSystemPath()));
                    } elseif ($this->share->getType() == 'asset-video') {
                        $thumb = $asset->getThumbnail($config['asset-video-preset']);
                        if (array_key_exists($config['asset-video-format'], $thumb['formats'])) {
                            $file = $thumb['formats'][$config['asset-video-format']];
                            $zip->addFile(PIMCORE_SYSTEM_TEMP_DIRECTORY . '/video-image-cache' . $file, $viewCaption . '/' . $asset->getId() . '-' . basename($file));
                        }
                    } else {
                        throw new \Exception('invalid download options');
                    }
                }
            }
            $zip->close();

            $response = new BinaryFileResponse($zipFile);
            $response->headers->set('Content-Type', 'application/zip');
            $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $viewCaption . '.zip');

            return $response;
        }
    }

    /**
     * asset detail anzeigen
     *
     * @Route("/share/token/detail")
     */
    public function tokenDetailAction(Request $request)
    {
        // init
        $item = $this->share->getAssetById($request->get('id'));
        $this->view->item = $item;

        if (!$item) {
            throw new\Exception('item not found');
        }

        // get meta fields to show
        $arrMetaFields = Dam\Helper::getEditableMetaFields($item);
        $arrGroups = [];

        $damConfig = $this->container->getParameter('pimcore_dam_config');
        $cnfGroup = $damConfig['backend']['metadata']['group'];

        foreach ($cnfGroup as $config) {
            $fields = is_array($config['field']) ? $config['field'] : [$config['field']];
            foreach ($fields as $name) {
                $arrGroups[$config['title']][$name] = $arrMetaFields[$name];
                unset($arrMetaFields[$name]);
            }
        }

        // alle felder die nicht gruppiert wurden in einen allgemeinen tab laden
        if (count($arrMetaFields) > 0 && !$damConfig['backend']['metadata']['hideCommon']) {
            $arrGroups = array_merge(['common' => $arrMetaFields], $arrGroups);
        }

        $this->view->metafieldGroups = $arrGroups;
        $this->view->readonlyMetaFields = Dam\Helper::withPermissionsHook()->getReadOnlyFields($item, $damConfig['backend']['metadata']['readonly']);
    }

    /**
     * token not valid error
     *
     * @Route("/share/token/invalid")
     */
    public function tokenInvalidAction()
    {
    }

    /**
     * passthru thumbnail
     *
     * @Route("/share/token/get-image")
     */
    public function tokenGetImageAction(Request $request)
    {
        $path = urldecode($request->get('path'));

        // do not allow PHP and .htaccess files
        $fullPath = str_replace(['..'], '', $path);
        if (preg_match("@\.ph(p[345]?|t|tml|ps)$@i", $fullPath) || basename($fullPath) == '.htaccess') {
            throw new \Exception('invalid file type');
        }

        // TODO für video thumbs anpassen!
        $fullPath = PIMCORE_TEMPORARY_DIRECTORY . '/image-thumbnails' . $fullPath;

        // PIMCORE_TEMPORARY_DIRECTORY
        // /image-thumbnails
        // /video-thumbnails

        if (!file_exists($fullPath)) {
            $subRequest = new Request();
            $subRequest->attributes->set(
                '_controller',
                'PimcoreCoreBundle:PublicServices:thumbnail'
            );

            if (preg_match('#/(?<prefix>.*)image-thumb__(?<assetId>\d+)__(?<thumbnailName>[a-zA-Z0-9_\-]+)/(?<filename>.*)#', $path, $match)) {
                $subRequest->query->set('assetId', $match['assetId']);
                $subRequest->query->set('thumbnailName', $match['thumbnailName']);
                $subRequest->query->set('filename', $match['filename']);
                $subRequest->query->set('prefix', $match['prefix']);
            }

            $httpKernel = $this->container->get('http_kernel');
            $response = $httpKernel->handle(
                $subRequest,
                HttpKernelInterface::SUB_REQUEST
            );

            /* @var \Symfony\Component\HttpFoundation\Response $response */
            return $response;
        }

        $response = new BinaryFileResponse($fullPath);

        return $response;
    }
}
