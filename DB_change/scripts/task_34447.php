<?php

/**
 * @author Shmakov Fedot
 * @date 01.10.2022
 * @see 34447
 */

use Cosmos\Config;

defined('NO_AGENT_CHECK') || define('NO_AGENT_CHECK', true);
defined('NO_KEEP_STATISTIC') || define('NO_KEEP_STATISTIC', "Y");
defined('NO_AGENT_STATISTIC') || define('NO_AGENT_STATISTIC', "Y");
defined('NOT_CHECK_PERMISSIONS') || define('NOT_CHECK_PERMISSIONS', true);

$_SERVER["DOCUMENT_ROOT"] = dirname(dirname(dirname(__FILE__))) . "/public";
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
$aCosmosConfigIblock = Config::getInstance()->getParam("IBLOCK");

if (!CModule::IncludeModule("iblock")) {
    echo "Не удалось подключить модуль iblock\n";
    die;
}
/**
 * Создаём свойство заказа
 */
$aOrderProperties = array(
    array(
        "NAME" => "Улица",
        "CODE" => "ADDRESS",
        "PERSON_TYPE_ID" => "1",
        "PROPS_GROUP_ID" => "2",
        "TYPE" => "TEXT",
        "MULTILINE" => "N",
        "COLS" => "",
        "ROWS" => "",
        "SORT" => "95"
    ),
    array(
        "NAME" => "Улица",
        "CODE" => "ADDRESS",
        "PERSON_TYPE_ID" => "2",
        "PROPS_GROUP_ID" => "4",
        "TYPE" => "TEXT",
        "MULTILINE" => "N",
        "COLS" => "",
        "ROWS" => "",
        "SORT" => "95"
    ),
    array(
        "NAME" => "Дом",
        "CODE" => "HOUSE",
        "PERSON_TYPE_ID" => "1",
        "PROPS_GROUP_ID" => "2",
        "TYPE" => "TEXT",
        "DESCRIPTION" => "Дом",
        "REQUIED" => "Y",
        "IS_ADDRESS" => "Y"
    ),
    array(
        "NAME" => "Дом",
        "CODE" => "HOUSE",
        "PERSON_TYPE_ID" => "2",
        "PROPS_GROUP_ID" => "4",
        "TYPE" => "TEXT",
        "DESCRIPTION" => "Дом",
        "REQUIED" => "Y",
        "IS_ADDRESS" => "Y"
    ),
    array(
        "NAME" => "Корпус",
        "CODE" => "HOUSING",
        "PERSON_TYPE_ID" => "1",
        "PROPS_GROUP_ID" => "2",
        "TYPE" => "TEXT",
        "DESCRIPTION" => "Корпус",
        "IS_ADDRESS" => "Y"
    ),
    array(
        "NAME" => "Корпус",
        "CODE" => "HOUSING",
        "PERSON_TYPE_ID" => "2",
        "PROPS_GROUP_ID" => "4",
        "TYPE" => "TEXT",
        "DESCRIPTION" => "Корпус",
        "IS_ADDRESS" => "Y"
    ),
    array(
        "NAME" => "Квартира",
        "CODE" => "APARTMENT",
        "PERSON_TYPE_ID" => "1",
        "PROPS_GROUP_ID" => "2",
        "TYPE" => "TEXT",
        "DESCRIPTION" => "Квартира",
        "IS_ADDRESS" => "Y"
    ),
    array(
        "NAME" => "Квартира",
        "CODE" => "APARTMENT",
        "PERSON_TYPE_ID" => "2",
        "PROPS_GROUP_ID" => "4",
        "TYPE" => "TEXT",
        "DESCRIPTION" => "Квартира",
        "IS_ADDRESS" => "Y"
    ),
);
foreach ($aOrderProperties as $aFields) {
    $aFilter = array(
        "CODE" => $aFields["CODE"], "PERSON_TYPE_ID" => $aFields["PERSON_TYPE_ID"],
        "PROPS_GROUP_ID" => $aFields["PROPS_GROUP_ID"],
    );
    $aSelect = array("ID");
    $oDbRes = CSaleOrderProps::GetList(array(), $aFilter, false, false, $aSelect);
    if ($aDbRes = $oDbRes->fetch()) {
        if (CSaleOrderProps::Update($aDbRes["ID"], $aFields)) {
            echo "\e[1;32m Успешно обновлено свойство заказа \"".$aFields["NAME"]."\" \e[0m\n";
        } else {
            echo "\e[1;31m Не удалось обновить свойство заказа \"".$aFields["NAME"]."\" \e[0m\n";
            if ($oException = $APPLICATION->GetException()) {
                echo "\e[1;31m Error: ".$oException->GetString()." \e[0m\n";
            }
        }
        //echo "\e[1;33m Свойство заказа \"" . $aFields["NAME"] . "\" уже существует \e[0m\n";
    } else {
        if (CSaleOrderProps::Add($aFields)) {
            echo "\e[1;32m Успешно добавлено свойство заказа \"" . $aFields["NAME"] . "\" \e[0m\n";
        } else {
            echo "\e[1;31m Не удалось добавить свойство заказа \"" . $aFields["NAME"] . "\" \e[0m\n";
            if ($oException = $APPLICATION->GetException()) {
                echo "\e[1;31m Error: " . $oException->GetString() . " \e[0m\n";
            }
        }
    }
}
