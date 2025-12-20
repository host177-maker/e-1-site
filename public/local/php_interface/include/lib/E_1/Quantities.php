<?php
namespace E_1;

use \Cosmos\Config;
use \PhpOffice\PhpSpreadsheet\Reader\Xlsx as ReaderXlsx;

class Quantities {

    protected static $quantities;

    public static function GetAllQuantities()
    {
        $cache = \Bitrix\Main\Data\Cache::createInstance();
        if ($cache->initCache(60*10, 'bi_quantities'))
        {
            $quantities = $cache->getVars();
        }
        elseif ($cache->startDataCache()) {
            $arSelect = array("ID", "NAME", "PROPERTY_QUANTITY", "PROPERTY_PRODUCTS", "PROPERTY_CITY_COUNT");
            $arFilter = array(
                "IBLOCK_ID" => Config::getInstance()->getIblockIdByCode('products_quantity'),
                "ACTIVE" => "Y",
                'PROPERTY_CITY_COUNT' => \CMaxRegionality::getCurrentRegionId()
            );
            $res = \CIBlockElement::GetList(array(), $arFilter, false, array(), $arSelect);
            while ($el = $res->Fetch()) {
                $quantities[$el['PROPERTY_PRODUCTS_VALUE']] = $el['PROPERTY_CITY_COUNT_VALUE'][1];
            }
            $cache->endDataCache($quantities);
        }

        self::$quantities = $quantities;
    }

    public static function GetProductQuantity($entity, $isDelivery = false)
    {
        global $arRegion;

        if (!self::$quantities) {
            self::GetAllQuantities();
        }

        if (is_array($entity)) {
            foreach ($entity as $offer) {
                if ($offer['PROPERTY_BASIC_CONFIGURATION_VALUE'] == 'Да' || !isset($quantity)) {
                    $id = $offer['ID'];
                    $quantity = self::$quantities[$id];
                }
            }
        } else {
            $id = $entity;
            $quantity = self::$quantities[$id];
        }

        $html = $text = $region = '';

        if(!empty($arRegion)) {
            $region = " в $arRegion[PROPERTY_REGION_NAME_DECLINE_PP_VALUE]";
        }

        if (isset($quantity)) {
            $html .= '<div data-id="'.$id.'">
                        <div class="basket-delivery" style="color:#5FA800" >
                            <span class="basket-delivery-availability"> В наличии'.$region.': '.$quantity.' </span>
                            ' . ($isDelivery ? "<span >Доставка от 1 дня</span>" : "") . '
                        </div>
                      </div>';
            $text .= 'В наличии: ' . $quantity;
            $isFound = true;
        } else {
            $html .= '<div data-id="'.$id.'">
                        <div class="basket-delivery" style="color:#1A36D4">
                            <span class="basket-delivery-availability-blue">Под заказ'.$region.' </span>
                        </div>
                      </div>';
            $text .= 'Под заказ';
            $isFound = false;
        }

        return ['HTML' => $html, 'TEXT' => $text, "IS_FOUND" => $isFound];
    }

    public static function importQuantitiesFromXlsx($file)
    {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        if ($ext !== "xlsx") {
            echo \CAdminMessage::ShowMessage('Неверное расширение файла. Загрузите файл в формате xlsx.');
            return;
        }

        set_time_limit(0);

        global $USER;
        $el = new \CIBlockElement;

        $ibQuantities = [];
        $arSelect = array('ID', 'EXTERNAL_ID');
        $arFilter = array('IBLOCK_ID' => Config::getInstance()->getIblockIdByCode('products_quantity'));
        $rsElements = \CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);
        while ($arElement = $rsElements->Fetch()) {
            $ibQuantities[$arElement['EXTERNAL_ID']] = $arElement['ID'];
        }

        $ibProducts = [];
        $arSelect = array('ID', 'EXTERNAL_ID', 'NAME');
        $arFilter = array('IBLOCK_ID' => Config::getInstance()->getIblockIdByCode('offers'));
        $rsElements = \CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);
        while ($arElement = $rsElements->Fetch()) {
            $ibProducts[$arElement['EXTERNAL_ID']] = [
                    'ID' => $arElement['ID'],
                    'NAME' => $arElement['NAME'],
                ];
        }

        $ibRegions = [];
        $arSelect = array('ID', 'NAME');
        $arFilter = array('IBLOCK_ID' => Config::getInstance()->getIblockIdByCode('aspro_max_regions'));
        $rsElements = \CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);
        while ($arElement = $rsElements->Fetch()) {
            $ibRegions[$arElement['NAME']] = $arElement['ID'];
        }

        $reader = new ReaderXlsx();
        $spreadsheet = $reader->load($file['tmp_name']);
        $worksheet = $spreadsheet->getActiveSheet();

        $productData = [];
        foreach ($worksheet->getRowIterator() as $row) {

            if ($row->getRowIndex() == 1) {
                continue;
            }

            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(true);

            $rowData = [];
            foreach ($cellIterator as $cell) {
                switch ($cell->getColumn()) {
                    case 'A':
                        $rowData['city'] = $cell->getValue();
                        break;
                    case 'B':
                        $rowData['PRODUCT_EXTERNAL_ID'] = $cell->getValue();
                        break;
                    case 'C':
                        $rowData['quantity'] = $cell->getValue();
                        break;
                }
            }

            if ($productData['PRODUCT_EXTERNAL_ID'] == '' || $rowData['PRODUCT_EXTERNAL_ID'] == $productData['PRODUCT_EXTERNAL_ID']) {
                $productData['PRODUCT_ID'] = $ibProducts[$rowData['PRODUCT_EXTERNAL_ID']]['ID'];
                $productData['PRODUCT_EXTERNAL_ID'] = $rowData['PRODUCT_EXTERNAL_ID'];
                $productData['quantities'][] = [
                    $ibRegions[$rowData['city']], $rowData['quantity'],
                ];
            } else {

                $PROP = array();
                $PROP['PRODUCTS'] = $productData['PRODUCT_ID'];
                $PROP['CITY_COUNT'] = $productData['quantities'];
                $arLoadProductArray = Array(
                    "MODIFIED_BY"    => $USER->GetID(),
                    "IBLOCK_SECTION_ID" => false,
                    "IBLOCK_ID"      => Config::getInstance()->getIblockIdByCode('products_quantity'),
                    "PROPERTY_VALUES"=> $PROP,
                    "NAME"           => $ibProducts[$productData['PRODUCT_EXTERNAL_ID']]['NAME'],
                    "ACTIVE"         => "Y",
                    "EXTERNAL_ID"    => $productData['PRODUCT_EXTERNAL_ID'],
                );

                if (isset($ibQuantities[$productData['PRODUCT_EXTERNAL_ID']])) {
                    $el->update($ibQuantities[$productData['PRODUCT_EXTERNAL_ID']], $arLoadProductArray);
                } else {
                    $el->Add($arLoadProductArray);
                }

                //начинаем добавлять для другого продукта количества
                $productData = [];
                $productData['PRODUCT_ID'] = $ibProducts[$rowData['PRODUCT_EXTERNAL_ID']]['ID'];
                $productData['PRODUCT_EXTERNAL_ID'] = $rowData['PRODUCT_EXTERNAL_ID'];
                $productData['quantities'][] = [
                    $ibRegions[$rowData['city']], $rowData['quantity'],
                ];
            }
        }

        \CAdminMessage::ShowMessage(array(
            "MESSAGE" => "Количество товаров успешно изменено.",
            "TYPE" => "OK",
        ));
    }

}