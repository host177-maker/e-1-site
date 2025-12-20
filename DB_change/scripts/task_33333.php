<?php
/**
* @date 27.06.2022 
* @author Nikita Vorobyov
* @see #33333
**/

$_SERVER["DOCUMENT_ROOT"] = dirname(dirname(dirname(__FILE__))) . "/public";
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

/**
 * Добавление пользовательского свойства
 */
$oUserTypeEntity = new CUserTypeEntity();
 
$aUserFields = array(
    'ENTITY_ID' => 'CAT_STORE',
    'FIELD_NAME' => 'UF_SHOW',
    'USER_TYPE_ID' => 'boolean',
    'XML_ID' => '',
    'SORT' => 100,
    'MULTIPLE' => 'N',
    'MANDATORY' => 'N',
    'SHOW_FILTER' => 'N',
    'SHOW_IN_LIST' => '',
    'EDIT_IN_LIST' => '',
    'IS_SEARCHABLE' => 'N',
    'SETTINGS' => array (
        'LABEL' => array (
            "MAIN_YES" => "Да",
            "MAIN_NO" => "Нет",
        )
    ),
    'EDIT_FORM_LABEL' => array(
        'ru' => 'Отображать на карте',
        'en' => 'Display in the map',
    ),
    'LIST_COLUMN_LABEL' => array(
        'ru' => '',
        'en' => '',
    ),
    'LIST_FILTER_LABEL' => array(
        'ru' => '',
        'en' => '',
    ),
    'ERROR_MESSAGE' => array(
        'ru' => '',
        'en' => '',
    ),
    'HELP_MESSAGE' => array(
        'ru' => '',
        'en' => '',
    ),
);
 
$iUserFieldId = $oUserTypeEntity->Add($aUserFields);

?>