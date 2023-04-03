<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Dam\Filter;

use Pimcore\Bundle\DamBundle\Model\Asset;
use Pimcore\Templating\PhpEngine;

class Tag extends AbstractFilter
{
    public function __construct()
    {
        $this->setId('tag');
        $this->setName('Tag');
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return array_key_exists('filter_tag', $this->getParams());
    }

    /**
     * @return bool
     */
    public function hasFrontend()
    {
        return true;
    }

    public function getFrontend(PhpEngine $view)
    {
        $tagList = new \Pimcore\Model\Element\Tag\Listing();
        if ($this->getParam('node')) {
            $tagList->setCondition('parentId = ?', intval($this->getParam('node')));
        } else {
            $tagList->setCondition('ISNULL(parentId) OR parentId = 0');
        }
        $tagList->setOrderKey('name');

        $tags = [];
        foreach ($tagList->load() as $tag) {
            $tags[] = $this->convertTagToArray($tag, is_array($this->getParam('filter_tag')) ? $this->getParam('filter_tag') : []);
        }

        return $view->template(sprintf('PimcoreDamBundle:Asset:sidebar/filter/%s.html.php', strtolower($this->getName())), ['filter' => $this, 'tagTree' => $tags]);
    }

    protected function convertTagToArray(\Pimcore\Model\Element\Tag $tag, $assignedTagIds)
    {
        $tagArray = [
            'id' => $tag->getId(),
            'text' => $tag->getName()
        ];

        $state = [];
        $state['checked'] = array_search($tag->getId(), $assignedTagIds) !== false;
        $tagArray['state'] = $state;

        $children = $tag->getChildren();
        foreach ($children as $child) {
            $childrenNodes = $this->convertTagToArray($child, $assignedTagIds);
            if ($this->hasCheckedNodes($childrenNodes)) {
                $tagArray['state']['expanded'] = true;
            }
            $tagArray['nodes'][] = $childrenNodes;
        }

        return $tagArray;
    }

    protected function hasCheckedNodes($nodesArray)
    {
        $it = new \RecursiveIteratorIterator(
            new \ParentIterator(new \RecursiveArrayIterator($nodesArray)),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($it as $key => $value) {
            if ($key == 'state' && $value['checked']) {
                return true;
            }
        }

        return false;
    }

    public function getIcon()
    {
        return 'glyphicon glyphicon-tags';
    }

    /**
     * @param Asset\Listing $list
     *
     * @return void
     */
    public function apply(Asset\Listing $list)
    {
        // filter collection
        $values = $this->getParam('filter_' . $this->getId());
        $values = is_array($values) ? $values : explode(',', $values);

        if ($values) {
            $conditionParts = [];
            foreach ($values as $tagId) {
                if ($this->getParam('considerChildTags') == 'true') {
                    $tag = \Pimcore\Model\Element\Tag::getById($tagId);
                    if ($tag) {
                        $tagPath = $tag->getFullIdPath();
                        $conditionParts[] = "id IN (SELECT cId FROM tags_assignment INNER JOIN tags ON tags.id = tags_assignment.tagid WHERE ctype = 'asset' AND (id = " . intval($tagId) . ' OR idPath LIKE ' . $list->quote($tagPath . '%') . '))';
                    }
                } else {
                    $conditionParts[] = "id IN (SELECT cId FROM tags_assignment WHERE ctype = 'asset' AND tagid = " . intval($tagId) . ')';
                }
            }

            if (count($conditionParts) > 0) {
                $condition = implode(' AND ', $conditionParts);

                //echo $condition; die();
                $list->addConditionParam($condition);
            }
        }
    }
}
