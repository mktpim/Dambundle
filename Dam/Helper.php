<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Dam;

use Pimcore\Bundle\DamBundle\Dam\Adapter\MetadataChangeHookInterface;
use Pimcore\Bundle\DamBundle\Dam\Item\AbstractItem;
use Pimcore\Bundle\DamBundle\PimcoreDamBundle;
use Pimcore\Model\Metadata\Predefined;

class Helper
{
    /**
     * @param $arrMetaFields
     *
     * @return array
     *
     * @throws \Exception
     */
    public static function processRequiredSettings($arrMetaFields)
    {
        // process required fields
        $requirementSettings = PimcoreDamBundle::getConfig()['backend']['metadata']['required'];

        foreach ($requirementSettings as $requirementSetting) {
            if ($requirementSetting['field'] && $arrMetaFields[$requirementSetting['field']]) {
                $fieldConfig = $arrMetaFields[$requirementSetting['field']];

                if ($fieldConfig['language']) {
                    throw new \Exception('Invalid configuration, required for language specific fields not supported yet.');
                }

                if ($requirementSetting['whenFieldName']) {
                    $fieldConfigRemote = $arrMetaFields[$requirementSetting['whenFieldName']];

                    if ($fieldConfigRemote['language']) {
                        throw new \Exception('Invalid configuration, required for language specific fields not supported yet.');
                    }

                    $fieldConfig['requiredWhen'] = sprintf('metadata$%s$%s$', $requirementSetting['whenFieldName'], $fieldConfigRemote['type']);
                    $fieldConfig['requiredWhenValue'] = $requirementSetting['whenFieldNameValue'];
                } else {
                    $fieldConfig['required'] = true;
                }
                $arrMetaFields[$requirementSetting['field']] = $fieldConfig;
            }
        }

        return $arrMetaFields;
    }

    /**
     * @param array $fields
     *
     * @return array
     */
    protected static function removeHiddenMetaFields(array $fields)
    {
        $hiddenFields = PimcoreDamBundle::getConfig()['backend']['metadata']['hidden'];

        if ($hiddenFields) {
            foreach ($fields as $field => $value) {
                if (in_array($field, $hiddenFields)) {
                    unset($fields[$field]);
                }
            }

            return $fields;
        } else {
            return $fields;
        }
    }

    /**
     * Get field names which are selectable by predefined config.
     *
     * @return array
     */
    private static $selectableFields = [];

    public static function getSelectableFields()
    {
        if (!self::$selectableFields) {
            $metadata = PimcoreDamBundle::getConfig()['backend']['metadata']['selectable'];
            $selectableFields = [];

            foreach ($metadata as $data) {
                $selectableFields[] = $data->field;
            }

            self::$selectableFields = $selectableFields;
        }

        return self::$selectableFields;
    }

    public static function getEditableMetaFieldsWithoutNonEditableFields(AbstractItem $item = null) : array {
        $editableMetaFields = self::getEditableMetaFields($item);
        $damConfigReadonly = PimcoreDamBundle::getConfig()['backend']['metadata']['readonly'];
        $readonlyMetaFields = self::withPermissionsHook()->getReadOnlyFields(null, $damConfigReadonly);
        $actualEditableFields = [];
        foreach ($editableMetaFields as $name => $field) {
            if (in_array($name, $readonlyMetaFields)) {
                continue;
            }
            $actualEditableFields[$name] = $field;
        }
        return $actualEditableFields;
    }

    /**
     * Check whether a field is a selectable field.
     *
     * @param $id   string      Whether the full id (name of the meta input field) or just
     *                          the key of the metafield (eg. "keywords", ...)
     *
     * @return bool
     */
    public static function isSelectableMetaField($id)
    {
        $nameParams = explode('$', $id);
        if (sizeof($nameParams) == 1) {
            return in_array($id, self::getSelectableFields());
        }

        return in_array($nameParams[1], self::getSelectableFields());
    }

    /**
     * @param AbstractItem|null $item
     *
     * @return array
     */
    public static function getEditableMetaFields(AbstractItem $item = null)
    {
        $arrMetaFields = [];
        $predefined = Predefined\Listing::getByTargetType('asset', $item ? $item->getAsset()->getType() : null);
        foreach ($predefined as $_item) {
            /* @var Predefined $_item */
            $arrMetaFields[$_item->getName()] = [
                'type' => $_item->getType(),
                'language' => ($_item->getLanguage() != ''),
                'config' => $_item->getConfig()
            ];
        }

        if ($item) {
            foreach ($item->getAsset()->getMetadata() as $_item) {
                if (!array_key_exists($_item['name'], $arrMetaFields)) {
                    $arrMetaFields[$_item['name']] = $_item;
                }
            }
        }

        $arrMetaFields = self::processRequiredSettings($arrMetaFields);
        $arrMetaFields = self::removeHiddenMetaFields($arrMetaFields);
        $arrMetaFields = self::withPermissionsHook()->getEditableMetaFields($item, $arrMetaFields);

        return $arrMetaFields;
    }

    /**
     * @return mixed
     */
    private static $sortOptions = [];

    /**
     * @return array
     */
    public static function getAssetSortOptions()
    {
        if (!self::$sortOptions) {
            $sortOptions = PimcoreDamBundle::getConfig()['backend']['ui']['listview']['sort'];

            if (sizeof($sortOptions)) {
                self::$sortOptions = $sortOptions;
            } else {
                $options = [
                    'filename' => ['icon' => 'glyphicon-font'],
                    'creationDate' => ['icon' => 'glyphicon-asterisk', 'default' => true],
                    'modificationDate' => ['icon' => 'glyphicon-edit']
                ];

                foreach ($options as $criteria => $sortOption) {
                    $option = [];
                    $option['criteria'] = $criteria;
                    $option['iconClass'] = $sortOption['icon'];
                    $option['default'] = (bool)$sortOption['default'];
                    self::$sortOptions[] = $option;
                }
            }
        }

        return self::$sortOptions;
    }

    /**
     * @param string $order
     *
     * @return string
     */
    public static function getValidOrderCriteria($order)
    {
        return strtolower($order) == 'desc' || $order == '' ? 'desc' : 'asc';
    }

    /**
     * @param string $sort
     *
     * @return string
     */
    public static function getValidSortCriteria($sort)
    {
        $default = 'creationDate';
        foreach (self::getAssetSortOptions() as $option) {
            if ($sort == $option['criteria']) {
                return $sort;
            }
            if ($option['default']) {
                $default = $option['criteria'];
            }
        }

        return $default;
    }

    /**
     * Link for folder in grid, to unset specific parameters in url.
     *
     * @param $view
     * @param $folderId
     *
     * @return mixed
     */
    public static function getFolderLink(\Pimcore\Templating\PhpEngine $view, $folderId)
    {
        $params = $view->getAllParams();
        $params['view'] = $view->getParam('view');
        $params['pid'] = $folderId;

        unset($params['sort']);
        unset($params['order']);
        unset($params['reset']);

        return $view->router()->path('pimcore_dam_asset_list', $params);
    }

    public static function withPermissionsHook() : MetadataChangeHookInterface {
        $service = \Pimcore::getContainer()->get("pimcore_dam.metadata_change.hook");
        return $service;
    }
}
