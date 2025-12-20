<?php

namespace Aspro\Max\Smartseo\Models;

use Bitrix\Main,
    Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class SmartseoSettingTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> CODE string(255) mandatory
 * <li> VALUE string(255) optional
 * <li> SITE_ID string(4) optional
 * </ul>
 *
 * @package Bitrix\Aspro
 * */
class SmartseoSettingTable extends Main\Entity\DataManager
{

    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_aspro_max_smartseo_setting';
    }

    public static function getMapEntityCode()
    {
        return [
            'CACHE_TABLE' => [
                'data_type' => 'integer',
                'default_value' => 86400,
                'group' => 'module',
            ],
            'CACHE_TEMPLATE_ENTITY' => [
                'data_type' => 'integer',
                'default_value' => 86400,
                'group' => 'module',
            ],
            'CACHE_CONDITION_CONTROL' => [
                'data_type' => 'integer',
                'default_value' => 86400,
                'group' => 'module',
            ],
            'FILTER_RULE_IS_ONLY_CATALOG' => [
                'data_type' => 'boolean',
                'default_value' => 'Y',
                'group' => 'module',
            ],
            'PAGE_IS_REPLACE_META_TAGS' => [
                'data_type' => 'boolean',
                'default_value' => 'Y',
                'group' => 'module',
            ],
            'PAGE_IS_REPLACE_TITLE' => [
                'data_type' => 'boolean',
                'default_value' => 'Y',
                'group' => 'module',
            ],
            'PAGE_IS_REPLACE_SNIPPET' => [
                'data_type' => 'boolean',
                'default_value' => 'Y',
                'group' => 'module',
            ],
            'PAGE_IS_REPLACE_URL' => [
                'data_type' => 'boolean',
                'default_value' => 'Y',
                'group' => 'module',
            ],
            'URL_TEMPLATE_SMARTFILTER' => [
                'data_type' => 'string',
                'default_value' => '#SECTION_CODE_PATH#/filter/#SMART_FILTER_PATH#/apply/',
                'group' => 'site',
            ],
            'URL_SEF_FOLDER' => [
                'data_type' => 'string',
                'default_value' => '#IBLOCK_LIST_PAGE_URL#',
                'group' => 'site',
            ],
            'URL_SECTION' => [
                'data_type' => 'string',
                'default_value' => '#SECTION_CODE_PATH#/',
                'group' => 'site',
            ],
            'SMARTFILTER_FRIENDLY' => [
                'data_type' => 'string',
                'default_value' => 'Y',
                'group' => 'site',
            ],
            'SMARTFILTER_FILTER_NAME' => [
                'data_type' => 'string',
                'default_value' => 'arrFilter',
                'group' => 'site',
            ],
            'NEW_URL_SECTION' => [
                'data_type' => 'string',
                'default_value' => '#IBLOCK_SECTION_PAGE_URL#',
                'group' => 'site',
            ],
            'NEW_URL_PROPERTY_LIST_CODE' => [
                'data_type' => 'list',
                'default_value' => 'VALUE',
                'group' => 'site',
                'list' => [
                    'VALUE' => Loc::getMessage('SMARTSEO_SETTING_OPTION_VALUE'),
                    'XML_ID' => Loc::getMessage('SMARTSEO_SETTING_OPTION_XML_ID'),
                ]
            ],
            'NEW_URL_PROPERTY_ELEMENT_CODE' => [
                'data_type' => 'list',
                'default_value' => 'CODE',
                'group' => 'site',
                'list' => [
                    'NAME' => Loc::getMessage('SMARTSEO_SETTING_OPTION_NAME'),
                    'CODE' => Loc::getMessage('SMARTSEO_SETTING_OPTION_CODE'),
                    'XML_ID' => Loc::getMessage('SMARTSEO_SETTING_OPTION_XML_ID'),
                ]
            ],
            'NEW_URL_PROPERTY_DIRECTORY_CODE' => [
                'data_type' => 'list',
                'default_value' => 'UF_XML_ID',
                'group' => 'site',
                'list' => [
                    'UF_NAME' => Loc::getMessage('SMARTSEO_SETTING_OPTION_NAME'),
                    'UF_XML_ID' => Loc::getMessage('SMARTSEO_SETTING_OPTION_XML_ID'),
                ]
            ],
            'NEW_URL_SEPARATOR_PROPERTY_VALUES' => [
                'data_type' => 'string',
                'default_value' => '-',
                'group' => 'site',
            ],
            'NEW_URL_REPLACE_PROPERTY_SPACE' => [
                'data_type' => 'string',
                'default_value' => '-',
                'group' => 'site',
            ],
            'NEW_URL_REPLACE_PROPERTY_OTHER' => [
                'data_type' => 'string',
                'default_value' => '-',
                'group' => 'site',
            ],
        ];
    }

    public static function getMapModuleEntityFields()
    {
        $result = [];

        foreach (self::getMapEntityCode() as $code => $value) {
            if ($value['group'] == 'module') {
                $result[$code] = $value;
            }
        }

        return $result;
    }

    public static function getMapSiteEntityFields()
    {
        $result = [];

        foreach (self::getMapEntityCode() as $code => $value) {
            if ($value['group'] == 'site') {
                $result[$code] = $value;
            }
        }

        return $result;
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return array(
            'ID' => array(
                'data_type' => 'integer',
                'primary' => true,
                'autocomplete' => true,
                'title' => Loc::getMessage('SMARTSEO_SETTING_ENTITY_ID_FIELD'),
            ),
            'CODE' => array(
                'data_type' => 'string',
                'required' => true,
                'validation' => array(__CLASS__, 'validateCode'),
                'title' => Loc::getMessage('SMARTSEO_SETTING_ENTITY_CODE_FIELD'),
            ),
            'VALUE' => array(
                'data_type' => 'string',
                'validation' => array(__CLASS__, 'validateValue'),
                'title' => Loc::getMessage('SMARTSEO_SETTING_ENTITY_VALUE_FIELD'),
            ),
            'SITE_ID' => array(
                'data_type' => 'string',
                'validation' => array(__CLASS__, 'validateSiteId'),
                'title' => Loc::getMessage('SMARTSEO_SETTING_ENTITY_SITE_ID_FIELD'),
            ),
        );
    }

    /**
     * Returns validators for CODE field.
     *
     * @return array
     */
    public static function validateCode()
    {
        return array(
            new Main\Entity\Validator\Length(null, 255),
            function ($value) {
                if (!in_array($value, array_keys(static::getMapEntityCode()))) {
                    return Loc::getMessage('SMARTSEO_SETTING_VALIDATE_CODE', [
                        '#CODE#' => $value,
                    ]);
                }

                return true;
            }
        );
    }

    /**
     * Returns validators for VALUE field.
     *
     * @return array
     */
    public static function validateValue()
    {
        return array(
            new Main\Entity\Validator\Length(null, 255),
        );
    }

    /**
     * Returns validators for SITE_ID field.
     *
     * @return array
     */
    public static function validateSiteId()
    {
        return array(
            new Main\Entity\Validator\Length(null, 4),
        );
    }

    public static function getGeneralSettings()
    {
        $rows = self::getList([
            'select' => [
                'CODE',
                'VALUE'
            ],
            'filter' => [
                '==SITE_ID' => '',
            ],
        ])->fetchAll();

        $result = [];

        $fields = self::getMapModuleEntityFields();

        foreach ($fields as $code => $field) {
            $result[$code] = $field['default_value'];
        }

        foreach ($rows as $row) {
            if (isset($result[$row['CODE']])) {
                $result[$row['CODE']] = $row['VALUE'];
            }
        }

        return $result;
    }

    public static function getSiteSettings()
    {
        $rows = self::getList([
            'select' => [
                'CODE',
                'VALUE',
                'SITE_ID',
            ],
            'filter' => [
                '!==SITE_ID' => null,
            ],
        ])->fetchAll();

        $result = [];

        $fields = self::getMapSiteEntityFields();

        foreach ($fields as $code => $field) {
            $result['default'][$code] = $field['default_value'];
        }

        foreach ($rows as $row) {
            if (isset($result['default'][$row['CODE']])) {
                $result[$row['SITE_ID']][$row['CODE']] = $row['VALUE'];
            }
        }

        return $result;
    }

    public function getPropertyListReplaceOptions()
    {
        $mapEntityCode = self::getMapEntityCode();

        return $mapEntityCode['NEW_URL_PROPERTY_LIST_CODE']['list'];
    }

    public function getPropertyElementReplaceOptions()
    {
        $mapEntityCode = self::getMapEntityCode();

        return $mapEntityCode['NEW_URL_PROPERTY_ELEMENT_CODE']['list'];
    }

    public function getPropertyDirectoryReplaceOptions()
    {
        $mapEntityCode = self::getMapEntityCode();

        return $mapEntityCode['NEW_URL_PROPERTY_DIRECTORY_CODE']['list'];
    }

}
