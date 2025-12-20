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
        '=SERIYA_SHKAFA.VALUE' => 510 // нужна только серия Оптим
    ];

    // получаем все элементы серии Оптим (включая неактивные)
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

    // для каждого элемента Оптимы получаем его ТП
    $sku = [];
    foreach ($catalog as $key => $element) {
        $offers = $skuClass::getList([
            'select' => [
                'ID',
                'IBLOCK_ID',
                'HOUSING_LAYOUT.VALUE',
                'DEPTH.VALUE',
                'CML2_LINK.VALUE'
            ],
            'filter' => [
                'CML2_LINK.VALUE' => $element['ID'],
                'DEPTH.VALUE' => 493 // глубина = 450
            ],
        ])->fetchAll();

        $sku[$key] = $offers;
        // echo "\e[1;32m[" . date("d.m.Y H:i:s") . "] выбрано " . count($sku[$key]) . " элементов для " . $element['ID'] . " \e[0m\n";
        // ob_end_flush();
    }

    $exchangeTable = [
        // "К - 2.1-45",
        "54aa172b-e26e-11eb-bb9b-00155d28f204" => "UkF0nFiX",
        // "К - 2.2-45",
        "ca7dd91f-e26e-11eb-bb9b-00155d28f204" => "ca7dd91f-e26e-",
        // "К - 2.3-45",
        "47f7e150-e26f-11eb-bb9b-00155d28f204" => "47f7e150-e26f",
        // "К - 2.4-45",
        "c0907e66-e26f-11eb-bb9b-00155d28f204" => "c0907e66-e26f",
        // "К - 2.5-45",
        "39d43c65-e270-11eb-bb9b-00155d28f204" => "39d43c65-e270",
        // "К - 3.1-45",
        "afbfdb7c-e2e6-11eb-bb9b-00155d28f204" => "afbfdb7c-e2e6",
        // "К - 3.2-45",
        "e92f794c-e2e8-11eb-bb9b-00155d28f204" => "e92f794c-e2e8",
        // "К - 3.3-45",
        "0584c542-e2e9-11eb-bb9b-00155d28f204" => "0584c542-e2e9",
        // "К - 3.4-45",
        "23226ded-e2e9-11eb-bb9b-00155d28f204" => "23226ded-e2e9",
        // "К - 3.5-45",
        "3c88deff-e2e9-11eb-bb9b-00155d28f204" => "3c88deff-e2e9",
        // "К - 3.6-45",
        "5ea150e0-e2e9-11eb-bb9b-00155d28f204" => "5ea150e0-e2e9",
        // "К - 3.7-45",
        "7c5ea07f-e2e9-11eb-bb9b-00155d28f204" => "7c5ea07f-e2e9",
        // "К - 3.8-45",
        "97de4851-e2e9-11eb-bb9b-00155d28f204" => "97de4851-e2e9",
        // "К - 3.9-45"
        "b26d3aaf-e2e9-11eb-bb9b-00155d28f204" => "b26d3aaf-e2e9"
    ];
    foreach ($sku as $key => $offers) {
        foreach ($offers as $k => $v) {
            if (!empty($v['IBLOCK_ELEMENTS_ELEMENT_OFFERS_HOUSING_LAYOUT_VALUE'])) {
                $newVal = empty($exchangeTable[$v['IBLOCK_ELEMENTS_ELEMENT_OFFERS_HOUSING_LAYOUT_VALUE']])
                    ? false
                    : $exchangeTable[$v['IBLOCK_ELEMENTS_ELEMENT_OFFERS_HOUSING_LAYOUT_VALUE']];

                if ($newVal) {
                    echo "\e[1;32m Было: " . $v['IBLOCK_ELEMENTS_ELEMENT_OFFERS_HOUSING_LAYOUT_VALUE'] . " Стало: " . $newVal . " \e[0m\n";
                    \CIBlockElement::SetPropertyValues($v['ID'], offersIB, $newVal, 'HOUSING_LAYOUT');
                }
            }

            echo "\e[1;32m[" . date("d.m.Y H:i:s") . "] " . ": Смена компановки для: " . $v['ID'] . " \e[0m\n";
            ob_end_flush();
        }
    }
}
