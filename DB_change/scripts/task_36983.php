<?php
/**
 * @author Kononenko Maxim
 * @date 27.03.2023
 * @see 36943
 */

use Cosmos\Config;

$_SERVER["DOCUMENT_ROOT"] = dirname(dirname(dirname(__FILE__)))."/public";
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");


if (!CModule::IncludeModule("iblock")) {
    echo "Не удалось подключить модуль iblock\n";
    die;
}

/**
 * Создаём инфоблок По вопросам сотрудничества
 */
$oCIBlock = new CIBlock;
$aIblocks = array(
    array(
        "IBLOCK_TYPE_ID" => "aspro_max_content",
        "LID" => "s1",
        "CODE" => "partnership",
        "NAME" => "По вопросам сотрудничества",
        "LIST_PAGE_URL" => "#SITE_DIR#/",
        "SECTION_PAGE_URL" => "#SITE_DIR#/",
        "DETAIL_PAGE_URL" => "#SITE_DIR#/#ELEMENT_CODE#/",
        "INDEX_ELEMENT" => "N",
        "INDEX_SECTION" => "N",
        "GROUP_ID" => array(2 => "R")
    ),
);
foreach ($aIblocks as $aFields) {
    if (Config::getInstance()->getIblockIdByCode($aFields["CODE"])) {
        echo "\e[1;33m Инфоблок \"" . $aFields["NAME"] . "\" уже существует \e[0m\n";
    } else {
        if ($iBlockId = $oCIBlock->Add($aFields)) {
            echo "\e[1;32m Успешно создан инфоблок \"" . $aFields["NAME"] . "\" \e[0m\n";

            if ($aFields['ELEMENTS']) {
                foreach ($aFields['ELEMENTS'] as $element) {
                    $element['IBLOCK_ID'] = $iBlockId;
                    $element['ACTIVE'] = 'Y';

                    $el = new CIBlockElement();
                    $el->Add($element);
                }
            }
        } else {
            echo "\e[1;31m Не удалось создать инфоблок \"" . $aFields["NAME"] . "\" \e[0m\n";
            echo "\e[1;31m Error: " . $oCIBlock->LAST_ERROR . " \e[0m\n";
        }
    }
}
    /**
     * Создаём свойство инфоблока По вопросам сотрудничества
     */

    Config::getInstance()->init();
    $ibId = Config::getInstance()->getIblockIdByCode('partnership');
    $oCIBlockProperty = new CIBlockProperty;
    $oCIBlockPropertyEnum = new CIBlockPropertyEnum;
    $aIblockProperties = array(
        array(
            "IBLOCK_ID" => $ibId,
            "NAME" => "Реклама",
            "CODE" => "ADVERTISEMENT",
            "PROPERTY_TYPE" => "S",
            "IS_REQUIRED" => "N",
        ),
        array(
            "IBLOCK_ID" => $ibId,
            "NAME" => "Аренда недвижимости",
            "CODE" => "REAL_ESTATE_RENT",
            "PROPERTY_TYPE" => "S",
            "IS_REQUIRED" => "N",
        ),
        array(
            "IBLOCK_ID" => $ibId,
            "NAME" => "Закупки",
            "CODE" => "PURCHASES",
            "PROPERTY_TYPE" => "S",
            "IS_REQUIRED" => "N",
        ),
        array(
            "IBLOCK_ID" => $ibId,
            "NAME" => "Логистика",
            "CODE" => "LOGISTICS",
            "PROPERTY_TYPE" => "S",
            "IS_REQUIRED" => "N",
        ),
        array(
            "IBLOCK_ID" => $ibId,
            "NAME" => "Опт",
            "CODE" => "WHOLESALE",
            "PROPERTY_TYPE" => "S",
            "IS_REQUIRED" => "N",
        ),
    );
    foreach ($aIblockProperties as $aFields) {
        $aFilter = array("IBLOCK_ID" => $aFields["IBLOCK_ID"], "CODE" => $aFields["CODE"]);
        $oDbRes = CIBlockProperty::GetList(array(), $aFilter);
        if ($aDbRes = $oDbRes->fetch()) {
            echo "\e[1;33m Свойство \"".$aFields["NAME"]."\" уже существует \e[0m\n";
        } else {
            if ($iPropertyId = $oCIBlockProperty->Add($aFields)) {
                echo "\e[1;32m Успешно создано свойство \"".$aFields["NAME"]."\" \e[0m\n";
    
                if (isset($aFields["ITEMS"]) && !empty($aFields["ITEMS"])) {
                    foreach ($aFields["ITEMS"] as $aEnumFields) {
                        $aEnumFields["PROPERTY_ID"] = $iPropertyId;
                        if ($oCIBlockPropertyEnum->Add($aEnumFields)) {
                            echo "\e[1;32m Успешно создано значение \"".$aEnumFields["VALUE"]."\" свойства \"".$aFields["NAME"]."\" \e[0m\n";
                        } else {
                            echo "\e[1;31m Не удалось создать значение \"".$aEnumFields["VALUE"]."\" свойства \"".$aFields["NAME"]."\" \e[0m\n";
                            echo "\e[1;31m Error: ".$oCIBlockPropertyEnum->LAST_ERROR." \e[0m\n";
                        }
                    }
                }
            } else {
                echo "\e[1;31m Не удалось создать свойство \"".$aFields["NAME"]."\" \e[0m\n";
                echo "\e[1;31m Error: ".$oCIBlockProperty->LAST_ERROR." \e[0m\n";
            }
        }
    }

    $el = new CIBlockElement;

    $arProperties = array();

    $arProperties['ADVERTISEMENT'] = 'market@e-1.ru';
    $arProperties['REAL_ESTATE_RENT'] = 'chistova.a@e-1.ru';
    $arProperties['PURCHASES'] = 'ivashenko.e@e-1.ru';
    $arProperties['LOGISTICS'] = 'logic2@e-1.ru';
    $arProperties['WHOLESALE'] = 'ex.sale@e-1.ru';

    $arLoadProductArray = Array(
        "IBLOCK_SECTION_ID" => false, // В корне или нет
        "IBLOCK_ID" => $ibId,              //  собственно сам id блока куда будем добавлять новый элемент
        "NAME" => 'E-mail адреса',
        "ACTIVE" => "Y", // активен или N не активен
        "CODE" => 'partnership_emails',
        "PROPERTY_VALUES"=> $arProperties,  // Добавим нашему элементу заданные свойства
    );

    $aFilter = array("IBLOCK_ID" => $ibId, "CODE" => $arProperties['OUTER_ID']);

    $oDbRes = CIBlockElement::GetList(array(), $aFilter)->fetch();

    if ($oDbRes["NAME"] == $arLoadProductArray['NAME']) {
        echo "\e[1;33m Элемент \"". $oDbRes["CODE"] ."\" уже существует \e[0m\n";
    } else {
        if ($newElement =$el->Add($arLoadProductArray)) {
            echo "\e[1;32m Успешно создан элемент \"" . $newElement . "\" \e[0m\n";
        } else {
            echo "\e[1;31m Не удалось создать элемент \"" . $newElement . "\" \e[0m\n";
            echo "\e[1;31m Error: " . "Error: " . $el->LAST_ERROR . " \e[0m\n";
        }
    }
