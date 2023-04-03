<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Model\Asset;

use Pimcore\Bundle\DamBundle\Dam;
use Pimcore\Model\Asset;
use Pimcore\Model\User;

class Listing extends Asset\Listing
{
    protected $currentFolder;
    protected $filters;
    protected $filterActive;

    public function init()
    {
        $this->filterWorkspaces();
    }

    public function filterParams($params)
    {
        $params = $this->handleSynonymParams($params);
        $this->applyUserFilters($params);
        $this->filterPid($params);
    }

    protected function handleSynonymParams($params)
    {
        if ($params['collection'] && !$params['filter_collection']) {
            $params['filter_collection'] = $params['collection'];
        }

        return $params;
    }

    protected function filterWorkspaces()
    {
        // init

        $workspaces = [];

        // workspaces optimieren
        foreach (Dam\Facade::getUser()->getRoles() as $roleId) {
            $role = User\Role::getById($roleId);
            foreach ($role->getWorkspacesAsset() as $workspace) {
                /* @var \User_Workspace_Asset $workspace */
                $workspaces[$workspace->getCpath()] = $workspace;
            }
        }
        foreach (Dam\Facade::getUser()->getWorkspacesAsset() as $workspace) {
            /* @var \User_Workspace_Asset $workspace */
            $workspaces[$workspace->getCpath()] = $workspace;
        }
        krsort($workspaces);

        if (!Dam\Facade::getUser()->isAdmin()) {
            // workspaces zum select hinzufügen
            $condAllow = [];
            $condDenided = [];
            foreach ($workspaces as $workspace) {
                if ($workspace->getList()) {
                    $c = [];
                    $r = '';
                    foreach (explode('/', $workspace->getCpath()) as $p) {
                        $r .= '/' . $p;
                        $c[] = sprintf('CONCAT(path,filename) like "%s"', substr($r, 1));
                    }

                    $rootPath = preg_replace('#(/.+?)/.*#i', '\1', $workspace->getCpath());
                    $c[] = sprintf('(CONCAT(path,filename) like "%s" OR CONCAT(path,filename) like "%s%%")', $rootPath, $workspace->getCpath());
                    //                $condAllow[] = sprintf('(CONCAT(path,filename) like "%s" OR CONCAT(path,filename) like "%s%%")', $rootPath, $workspace->getCpath());
                    $condAllow[] = '(' . implode(' OR ', $c) . ')';
                } else {
                    $condDenided[] = sprintf('CONCAT(path,filename) not like "%s%%"', $workspace->getCpath());
                }
            }
            if (count($condAllow) > 0) {
                $this->addConditionParam('(' . implode(' OR ', $condAllow) . ')', '');

                if (count($condDenided) > 0) {
                    $this->addConditionParam('(' . implode(' AND ', $condDenided) . ')', '');
                }
            } elseif (!Dam\Facade::getUser()->isAdmin()) {
                // verhindern das auf alle assets zugegriffen werden kann
                $this->addConditionParam('1 = 0', '');
            }
        }
    }

    /**
     * @param array $params
     */
    protected function filterPid(array $params)
    {
        if (!($pid = $params['pid'])) {
            $pid = 1;
        }
        $folder = Asset::getById($pid);
        if (!$folder || !$folder->isAllowed('list')) {
            throw new \Exception('not allowed');
        }

        $this->currentFolder = $folder;

        if ($this->filterActive) {
            if ($folder->getId() != 1) {
                $this->addConditionParam('type != "folder" and path like "' . $folder->getFullPath() . '/%"');
            }
        } else {
            $this->addConditionParam("parentId = '" . $folder->getId() . "'");
        }
    }

    protected function applyUserFilters($params)
    {
        list($filters, $filterActive) = Dam\FilterService::applyFilters($this, $params);

        $this->filters = $filters;
        $this->filterActive = $filterActive;
    }

    public static function hasOrderKey($key)
    {
        $temp = new self();

        return $temp->isValidOrderKey($key);
    }

    /**
     * @return \Pimcore\Model\Asset
     */
    public function getCurrentFolder()
    {
        return $this->currentFolder;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @return bool
     */
    public function getFilterActive()
    {
        return $this->filterActive;
    }
}
