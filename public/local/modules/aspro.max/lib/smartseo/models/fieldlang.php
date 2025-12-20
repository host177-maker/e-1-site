<?php

namespace Aspro\Max\Smartseo\Models;

use Aspro\Max\Smartseo\Admin\Settings\SettingSmartseo,
    Bitrix\Main,
    Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class FieldLangTable
 *
 * Fields:
 * <ul>
 * <li> USER_FIELD_ID int mandatory
 * <li> LANGUAGE_ID string(2) mandatory
 * <li> EDIT_FORM_LABEL string(255) optional
 * <li> LIST_COLUMN_LABEL string(255) optional
 * <li> LIST_FILTER_LABEL string(255) optional
 * <li> ERROR_MESSAGE string(255) optional
 * <li> HELP_MESSAGE string(255) optional
 * </ul>
 *
 * @package Aspro\Max\Smartseo
 * */
class FieldLangTable extends Main\Entity\DataManager
{

    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_user_field_lang';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return array(
            'USER_FIELD_ID' => array(
                'data_type' => 'integer',
                'primary' => true,
                'title' => Loc::getMessage('FIELD_LANG_ENTITY_USER_FIELD_ID_FIELD'),
            ),
            'LANGUAGE_ID' => array(
                'data_type' => 'string',
                'primary' => true,
                'validation' => array(__CLASS__, 'validateLanguageId'),
                'title' => Loc::getMessage('FIELD_LANG_ENTITY_LANGUAGE_ID_FIELD'),
            ),
            'EDIT_FORM_LABEL' => array(
                'data_type' => 'string',
                'validation' => array(__CLASS__, 'validateEditFormLabel'),
                'title' => Loc::getMessage('FIELD_LANG_ENTITY_EDIT_FORM_LABEL_FIELD'),
            ),
            'LIST_COLUMN_LABEL' => array(
                'data_type' => 'string',
                'validation' => array(__CLASS__, 'validateListColumnLabel'),
                'title' => Loc::getMessage('FIELD_LANG_ENTITY_LIST_COLUMN_LABEL_FIELD'),
            ),
            'LIST_FILTER_LABEL' => array(
                'data_type' => 'string',
                'validation' => array(__CLASS__, 'validateListFilterLabel'),
                'title' => Loc::getMessage('FIELD_LANG_ENTITY_LIST_FILTER_LABEL_FIELD'),
            ),
            'ERROR_MESSAGE' => array(
                'data_type' => 'string',
                'validation' => array(__CLASS__, 'validateErrorMessage'),
                'title' => Loc::getMessage('FIELD_LANG_ENTITY_ERROR_MESSAGE_FIELD'),
            ),
            'HELP_MESSAGE' => array(
                'data_type' => 'string',
                'validation' => array(__CLASS__, 'validateHelpMessage'),
                'title' => Loc::getMessage('FIELD_LANG_ENTITY_HELP_MESSAGE_FIELD'),
            ),
        );
    }

    /**
     * Returns validators for LANGUAGE_ID field.
     *
     * @return array
     */
    public static function validateLanguageId()
    {
        return array(
            new Main\Entity\Validator\Length(null, 2),
        );
    }

    /**
     * Returns validators for EDIT_FORM_LABEL field.
     *
     * @return array
     */
    public static function validateEditFormLabel()
    {
        return array(
            new Main\Entity\Validator\Length(null, 255),
        );
    }

    /**
     * Returns validators for LIST_COLUMN_LABEL field.
     *
     * @return array
     */
    public static function validateListColumnLabel()
    {
        return array(
            new Main\Entity\Validator\Length(null, 255),
        );
    }

    /**
     * Returns validators for LIST_FILTER_LABEL field.
     *
     * @return array
     */
    public static function validateListFilterLabel()
    {
        return array(
            new Main\Entity\Validator\Length(null, 255),
        );
    }

    /**
     * Returns validators for ERROR_MESSAGE field.
     *
     * @return array
     */
    public static function validateErrorMessage()
    {
        return array(
            new Main\Entity\Validator\Length(null, 255),
        );
    }

    /**
     * Returns validators for HELP_MESSAGE field.
     *
     * @return array
     */
    public static function validateHelpMessage()
    {
        return array(
            new Main\Entity\Validator\Length(null, 255),
        );
    }

    public static function getCacheTtl()
    {
        $ttl = SettingSmartseo::getInstance()->getCacheTable();

        return $ttl ?: self::DEFAULT_CACHE_TTL;
    }

}
