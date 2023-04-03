<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Controller;

use Pimcore\Bundle\DamBundle\Dam;
use Pimcore\Bundle\DamBundle\Model;
use Pimcore\Controller\FrontendController;
use Pimcore\Model\Asset;
use Pimcore\Model\User;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

class CollectionController extends FrontendController
{
    use ControllerAware {
        ControllerAware::onKernelController as __onKernelController;
    }

    /**
     * @var Model\Collection\Listing|Model\Collection[]
     */
    protected $list;

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

        // check permission
        if (!$this->getUser()->getUser()->isAllowed('plugin_dam_collection')) {
            throw new \Exception('not allowed');
        }

        $this->list = Dam\Facade::getCollectionList(Dam\Collection\Permissions::ADMIN);
    }

    /**
     * add asset to a collection
     */
    public function addAction(Request $request)
    {
        // init
        $collection = Model\Collection::getById($request->get('id'));
        $asset = Asset::getById($request->get('asset'));

        $collection->append($asset);
        $collection->save();

        $this->_helper->json(['success' => true]);
    }

    /**
     * remove asset from a collection
     */
    public function removeAction()
    {
        // init
        $collection = Model\Collection::getById($request->get('id'));
        $asset = Asset::getById($request->get('asset'));

        $collection->remove($asset);
        $collection->save();

        $this->_helper->json(['success' => true]);
    }

    /**
     * add or remove asset from a collection
     */
    public function toggleAction()
    {
        // init
        $collection = Model\Collection::getById($request->get('id'));
        $asset = Asset::getById($request->get('asset'));
        $json = [];

        if ($collection->exists($asset)) {
            $collection->remove($asset);
            $json['added'] = false;
        } else {
            $collection->append($asset);
            $json['added'] = true;
        }

        $collection->save();
        $this->_helper->json($json);
    }

    /**
     * list all defined collections
     *
     * @Route("/collection/list")
     */
    public function listAction(Request $request)
    {
        // init
        $this->view->list = $this->list->getList();

        //show shared collections for this user
        $sharedList = Dam\Facade::getCollectionList(Dam\Collection\Permissions::VIEW);
        $sharedList->addConditionParam('userId != ?', $this->getUser()->getId());

        $this->view->shared = $sharedList;

        $this->view->allowShare = $this->getUser()->getUser()->isAllowed('plugin_dam_share');
    }

    /**
     * save changes to a collection
     *
     * @Route("/collection/save")
     */
    public function saveAction(Request $request)
    {
        if ($request->isMethod('POST')) {
            // init
            $nameList = $request->get('name', []);
            $colorList = $request->get('color', []);
            $sortIndex = array_keys($nameList);

            // bestehende updaten
            foreach ($this->list as $collection) {
                /* @var $collection Model\Collection */
                if (array_key_exists($collection->getId(), $nameList)) {
                    if (!$collection->isEditable()) {
                        throw new \Exception('not allowed: ' . Dam\Collection\Permissions::EDIT);
                    }

                    $collection->setName($nameList[$collection->getId()]);
                    $collection->setColor($colorList[$collection->getId()]);
                    $collection->setSort(array_search($collection->getId(), $sortIndex));

                    $collection->save();

                    unset($nameList[$collection->getId()]);
                    unset($colorList[$collection->getId()]);
                }
            }

            // neue hinzufügen
            foreach ($nameList as $id => $name) {
                if ($name != '') {
                    $collections = new Model\Collection\Listing();
                    $collections->setOrderKey('sort');
                    $collections->setOrder('DESC');
                    $collections->setLimit(1);
                    $sortIndex = 0;
                    if ($collection = $collections->current()) {
                        $sortIndex = $collection->getSort();
                    }

                    $collection = new Model\Collection();
                    $collection->setUserId($this->getUser()->getId());
                    $collection->setName($name);
                    $collection->setColor($colorList[$id]);
                    $collection->setSort(++$sortIndex);

                    $collection->save();
                }
            }
        }

        $url = $this->generateUrl('pimcore_dam_collection_list');

        return $this->redirect($url);
    }

    /**
     * delete a collection
     *
     * @Route("/collection/delete")
     */
    public function deleteAction(Request $request)
    {
        // init
        $list = $this->list;
        $list->addConditionParam(sprintf('id = %d', $request->get('id')), '');
        $collection = $list->current();
        $this->view->collection = $collection;

        /* @var $collection Model\Collection */
        if (!$collection->isOwnedBy()) {
            throw new \Exception('not allowed: ' . Dam\Collection\Permissions::ADMIN);
        }

        if ($collection && $request->get('confirm')) {
            /* delete collection */
            $collection->delete();

            $url = $this->generateUrl('pimcore_dam_collection_list');

            return $this->redirect($url);
        }
    }

    /**
     * assign a asset to a collection
     *
     * @deprecated
     *
     * @Route("/collection/assign")
     */
    public function assignAction(Request $request)
    {
        // init
        $collectionList = Dam\Facade::getCollectionList();

        $this->view->list = $collectionList;

        // daten laden
        $this->view->user = $this->getUser();
        $asset = Asset::getById($request->get('asset'));
        $assignIds = $request->get('collection', []);

        $item = Dam\Item\AbstractItem::createInstance($asset);

        // collectionen die zugewiesen sind laden
        $collectionAssign = clone Dam\Facade::getCollectionList();
        $collectionAssign->addConditionParam("assigned like '%,".$item->getId().",%'");

        $item->setCollectionList($collectionAssign);
        $this->view->item = $item;

        // add
        if ($request->isMethod('POST')) {
            foreach ($collectionList as $collection) {

                /* @var Model\Collection $collection */

                if (!$collection->isEditable()) {
                    continue;
                }

                if (in_array($collection->getId(), $assignIds)) {
                    $collection->append($asset);
                } else {
                    $collection->remove($asset);
                }

                $collection->save();
            }
        }
    }

    /**
     * batch assign a assets to a collection
     *
     * @Route("/collection/assignBatch")
     */
    public function assignBatchAction(Request $request)
    {
        // init
        $collectionList = Dam\Facade::getCollectionList();

        $this->view->list = $collectionList;

        // daten laden
        $this->view->user = $this->getUser();

        $this->view->listItems = [];
        $ids = explode(',', $request->get('selectedItems'));
        $listItems = [];
        foreach ($ids as $id) {
            $asset = Asset::getById($id);
            if ($asset) {
                $listItems[] = Dam\Item\AbstractItem::createInstance($asset);
            }
        }
        $this->view->listItems = $listItems;

        // add
        if ($request->isMethod('POST')) {
            $assignIds = $request->get('collection', []);

            // load recursive list
            if ($request->get('append-recursive') || $request->get('replace-recursive')) {
                $rList = Dam\Facade::getAssetList();
                $rPath = [];

                foreach ($listItems as $item) {
                    $rPath[] = sprintf('concat(path,filename) like %s', $rList->quote($item->getAsset()->getFullPath().'%'));
                }

                $rList->addConditionParam('(' . implode(' OR ', $rPath) . ')', '');

                $listItems = [];
                foreach ($rList as $asset) {
                    $listItems[] = Dam\Item\AbstractItem::createInstance($asset);
                }
            }

            foreach ($collectionList as $collection) {
                /* @var Model\Collection $collection */

                if (!$collection->isEditable()) {
                    continue;
                }

                // asset
                if (in_array($collection->getId(), $assignIds)) {
                    foreach ($listItems as $item) {
                        $collection->append($item->getAsset());
                    }
                } elseif ($request->get('replace') || $request->get('replace-recursive')) {
                    foreach ($listItems as $item) {
                        $collection->remove($item->getAsset());
                    }
                }

                $collection->save();
            }
        }
    }

    /**
     * share collection with other users
     *
     * @Route("/collection/share")
     */
    public function shareAction(Request $request)
    {
        // recht auf freigabe prüfen
        if (!$this->getUser()->getUser()->isAllowed('plugin_dam_share')) {
            throw new \Exception('sharing not allowed');
        }

        // load collection
        /* @var Model\Collection $collection */
        $list = $this->list;
        $list->addConditionParam(sprintf('id = %d', $request->get('id')), '');
        $collection = $list->current();
        $this->view->collection = $collection;

        // create user list for sharing
        $list = DAM\Collection\Accessor::createShareUserList();
        $list->addConditionParam('id != ?', $this->getUser()->getUser()->getId());
        $list->load();
        $userList = $list->getItems();

        // add user groups for sharing

        $list = DAM\Collection\Accessor::createShareUserRoleList();
        $list->load();
        $userList = array_merge($userList, $list->getItems());

        $this->view->userList = $userList;
        /* @var User[] $userList */

        if ($request->isMethod('POST')) {
            if ($collection->isOwnedBy()) {
                // check users
                $ids = explode(',', $request->get('userList'));
                $permissions = $request->get('permissions');

                $groupedUserIds = [];
                foreach ($userList as $user) {
                    if (in_array($user->getId(), $ids)) {
                        $groupedUserIds[$permissions[$user->getId()]][] = $user->getId();
                    }
                }

                foreach (Dam\Collection\Permissions::getAll() as $permission) {
                    $collection->setUsers($permission, $groupedUserIds[$permission] ? $groupedUserIds[$permission] : []);
                }
                $collection->save();
                //nothing to return in order to make the popup close.
                exit;
            } else {
                echo 'ERROR - not allowed';
                exit;
            }
        }
    }

    /**
     * Link zur Kollektion via email senden
     *
     * @deprecated
     */
    public function sendEmailAction()
    {
        // load collection
        /* @var Model\Collection $collection */
        $list = $this->list;
        $list->addConditionParam(sprintf('id = %d', $request->get('id')), '');
        $collection = $list->current();
        $this->view->collection = $collection;

        if ($request->isMethod('POST')) {
            // TODO email erstellen und senden

            $url = $this->view->path(['action' => 'list', 'controller' => 'collection', 'module' => 'DamListener'], 'plugin', true);
            $this->redirect($url);
        }
    }
}
