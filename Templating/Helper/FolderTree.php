<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Templating\Helper;

use Pimcore\Templating\Helper\TemplatingEngineAwareHelperInterface;
use Pimcore\Templating\Helper\Traits\TemplatingEngineAwareHelperTrait;
use Symfony\Component\Templating\Helper\Helper;

/**
 *
 * Build up a folderTree template to given folder hierarchy
 *
 * Class FolderTree
 *
 * @package DAM\View\Helper
 */
class FolderTree extends Helper implements TemplatingEngineAwareHelperInterface
{
    use TemplatingEngineAwareHelperTrait;

    const TPL_TYPE_RELOCATE = 'relocate';
    const TPL_TYPE_TREE = 'folder-tree';

    /**
     * @var string
     */
    protected $template;
    protected $url;
    protected $type;

    public function getName()
    {
        return 'damFolderTree';
    }

    public function __invoke($url, $type = self::TPL_TYPE_TREE)
    {
        $this->url = $url;
        $this->type = $type;
        $this->template = '';

        return $this;
    }

    /**
     *  Get the whole folder tree template.
     *
     * @param $folderId - the target folder id
     * @param $folderTree - array of folders in the hierarchy
     * @param $folderTreeList - folder list for the hierarchy
     *
     * @return string
     */
    public function getTreeTemplate($folderId, $folderTree, $folderTreeList)
    {
        if (!array_key_exists($folderId, $folderTreeList)) {
            return '';
        }

        $this->template .= '<ul class="folderTree">';

        foreach ($folderTreeList[$folderId] as $folder) {
            if (in_array($folder->id, $folderTree)) {
                $this->template .= $this->getTreeItemTemplate($folder, true, (bool)count($folderTreeList[$folder->id]));
                $this->template = $this->getTreeTemplate($folder->id, $folderTree, $folderTreeList);
                $this->template .= '</li>';
            } else {
                $this->template .= $this->getTreeItemTemplate($folder);
            }
        }

        $this->template .= '</ul>';

        return $this->template;
    }

    /**
     * Get JavaScript Template for loading branches asynchronously.
     *
     * @return string
     */
    public function getJsTemplate()
    {
        $url = $this->getTplTypeUrl();

        return '<li class="folderTree-folder childs"><span class="glyphicon glyphicon-plus-sign open-folder"></span>&nbsp;<a class="btn btn-default btn-xs" data-id="{id}" data-path="{path}" _data-create href="' . $url . '">{name}</a></li>';
    }

    protected function getTplTypeUrl($folder = null)
    {
        switch ($this->type) {
            case self::TPL_TYPE_RELOCATE: {

                if ($folder) {
                    $url = $this->url . '&target=' . $folder->id;
                } else {
                    $url = $this->url . '&target={id}';
                }
                break;
            }
            case self::TPL_TYPE_TREE: {
                if ($folder) {
                    $url = $this->url . '&pid=' . $folder->id;
                } else {
                    $url = $this->url . '&pid={id}';
                }
                break;
            }
            default: {
                $url = '';
            }
        }

        return $url;
    }

    /**
     * @param $folder
     * @param bool $isOpen
     * @param bool $isNotEmptyParent
     *
     * @return string
     */
    protected function getTreeItemTemplate($folder, $isOpen = false, $isNotEmptyParent = true)
    {
        $extraIcon = $isNotEmptyParent ? '' : 'glyphicon-folder-close';

        $url = $this->getTplTypeUrl($folder);

        $open = '<li class="folderTree-folder childs open"><span class="glyphicon glyphicon-minus-sign ' . $extraIcon . ' open-folder"></span>&nbsp;<a class="btn btn-default btn-xs" data-id="' . $folder->id . '" data-path="' . $folder->path . '" href="' . $url . '">' . $folder->name . '</a>';
        $closed = '<li class="folderTree-folder childs"><span class="glyphicon glyphicon-plus-sign open-folder"></span>&nbsp;<a class="btn btn-default btn-xs" data-id="' . $folder->id . '" data-path="' . $folder->path . '"  href="' . $url . '">' . $folder->name . '</a></li>';

        return $isOpen ? $open : $closed;
    }
}
