<?php

define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
ini_set('memory_limit', '2048M');
$_SERVER["DOCUMENT_ROOT"] = dirname(dirname(__FILE__)) . "/public";
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

const catalogIB = 48; // ID ИБ каталога
const offersIB = 49; // ID ИБ торговых предложений

if (\CModule::IncludeModule("iblock") && \CModule::IncludeModule("catalog")) {
    $catalogIblock = \Bitrix\Iblock\Iblock::wakeUp(catalogIB);
    $catalogClass  = $catalogIblock->getEntityDataClass();

    $offersIblock = \Bitrix\Iblock\Iblock::wakeUp(offersIB);
    $skuClass     = $offersIblock->getEntityDataClass();

    $arFilter = [
        '=SERIYA_SHKAFA.VALUE' => [510, 490]
    ];

    // получаем все элементы Оптимы (включая неактивные)
    $catalog = $catalogClass::getList([
        'select' => [
            "ID",
            "IBLOCK_ID",
            'SERIYA_SHKAFA.VALUE'
        ],
        'filter' => $arFilter,
    ])->fetchAll();

    echo "\e[1;32m[" . date("d.m.Y H:i:s") . "] выбрано " . count($catalog) . " элементов каталога" . " \e[0m\n";
    ob_end_flush();

    // для каждого элемента Оптимы получаем его Предложения
    $sku = [];
    foreach ($catalog as $key => $element) {
        $offers = $skuClass::getList([
            'select' => [
                'ID',
                'IBLOCK_ID',
                'PRICE_UM',
                'PRICE_VP',
                'TSENA_UGLOVOY_MODUL_SIBIR',
                'TSENA_VERKHNYAYA_PODSVETKA_SIBIR',
                'CML2_LINK.VALUE'
            ],
            'filter' => [
                'CML2_LINK.VALUE' => $element['ID']
            ],
        ])->fetchAll();

        $sku[$key] = $offers;
        echo "\e[1;32m[" . date("d.m.Y H:i:s") . "] выбрано " . count($sku[$key]) . " элементов для " . $element['ID'] . " \e[0m\n";
        ob_end_flush();
    }

    // обнуляем указанные свойства
    $cnt = 0;
    foreach ($sku as $key => $offers) {
        foreach ($offers as $k => $v) {
            if (intval($v['IBLOCK_ELEMENTS_ELEMENT_OFFERS_PRICE_UM_VALUE']) > 0) {
                \CIBlockElement::SetPropertyValues($v['ID'], offersIB, null, 'PRICE_UM');
            }
            if (intval($v['IBLOCK_ELEMENTS_ELEMENT_OFFERS_PRICE_UM_VALUE']) > 0) {
                \CIBlockElement::SetPropertyValues($v['ID'], offersIB, null, 'PRICE_VP');
            }
            if (intval($v['IBLOCK_ELEMENTS_ELEMENT_OFFERS_PRICE_UM_VALUE']) > 0) {
                \CIBlockElement::SetPropertyValues($v['ID'], offersIB, null, 'TSENA_UGLOVOY_MODUL_SIBIR');
            }
            if (intval($v['IBLOCK_ELEMENTS_ELEMENT_OFFERS_PRICE_UM_VALUE']) > 0) {
                \CIBlockElement::SetPropertyValues($v['ID'], offersIB, null, 'TSENA_VERKHNYAYA_PODSVETKA_SIBIR');
            }
            echo "\e[1;32m[" . date("d.m.Y H:i:s") . "] " . $cnt++ . ":  Clear prices for: " . $v['ID'] . " \e[0m\n";
            ob_end_flush();
        }
    }
}