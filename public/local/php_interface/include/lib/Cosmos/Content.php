<?php

namespace Cosmos;

use Bitrix\Main\Loader;
use Bitrix\Sale\Internals\BusinessValueTable;
use Bitrix\Main\Web;
use Bitrix\Sale\Payment;

class Content
{

    /**
     * Функция форматирует вывод размера файла
     * @param $bytes
     * @param int $decimals
     * @return string
     */
    static function getHumanFilesize($bytes, int $decimals = 2): string
    {
        $size = array("Б", "КБ", "МБ", "ГБ", "ТБ", "ПБ", "ЭБ", "ЗБ", "ЙБ");
        $factor = floor((strlen($bytes) - 1) / 3);
        return number_format($bytes / pow(1024, $factor), $decimals, ",", "") . " " . $size[$factor];
    }

    public static function getPaymentQrCode($orderId, $mdOrder)
    {
        if (!$mdOrder) {
            return '';
        }

        $paymentSettings = [];

        $url = 'https://pay.alfabank.ru/payment/rest/sbp/c2b/qr/dynamic/get.do';

        Loader::includeModule('sale');

        $payment = Payment::loadForOrder($orderId);

        $result = BusinessValueTable::getList([
            'select' => ['CODE_KEY', 'CONSUMER_KEY', 'PERSON_TYPE_ID', 'PROVIDER_KEY', 'PROVIDER_VALUE'],
            'filter' => [
                'CODE_KEY' => ['ALFABANK_GATE_LOGIN', 'ALFABANK_GATE_PASSWORD'],
                '!PROVIDER_VALUE' => false,
            ],
        ]);

        while ($row = $result->fetch()) {
            $paymentSettings[$row['CODE_KEY']] = $row['PROVIDER_VALUE'];
        }

        $data = [
            'userName' => $paymentSettings['ALFABANK_GATE_LOGIN'],
            'password' => $paymentSettings['ALFABANK_GATE_PASSWORD'],
            'mdOrder' => $mdOrder,
            'qrFormat' => 'image',
            'qrHeight' => 100,
            'qrWidth' => 100,
        ];

        $http = new Web\HttpClient();
        $http->setCharset("utf-8");
        $http->disableSslVerification();
        $http->post($url, $data);

        $res =  $http->getResult();

        $res =  Web\Json::decode($res);

        return $res['renderedQr'];
    }

    /**
    * Метод получает варианты значений свойства инфоблока типа список по его символьному коду в виде ['VALUE'] = ['ID']
    * @param $sPropertyCode
    * @return array
    */
    public static function getIblockPropertyEnumListToArray($sPropertyCode, $iIblockId = false)
    {
            $arFilter = Array( 'PROPERTY_ID' => $sPropertyCode, );

            if($iIblockId) {
                $arFilter['IBLOCK_ID'] = $iIblockId;
            }

            //получим значения доп.классов
            $oResPropertyEnum = \CIBlockPropertyEnum::GetList([], $arFilter);
            $aEnumClassList = array();
            while ($aEnum = $oResPropertyEnum->Fetch()) {
                $aEnumClassList [$aEnum['VALUE']] = $aEnum['ID'];
            }
            return $aEnumClassList;
    }

    public static function getCityRegionForIDs($idRegions = []) {
        $arResult = [];
        if (!empty($idRegions)) {
                $arSelect = Array("ID", "IBLOCK_ID", "NAME", 'CODE');
                $arFilter = Array("IBLOCK_ID"=> \Cosmos\Config::getInstance()->getIblockIdByCode('aspro_max_regions'), "ID" => $idRegions, "ACTIVE_DATE"=> "Y", "ACTIVE"=>"Y");
                $res = \CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
                while ($arRegionCity = $res->Fetch()){
                    $arResult[$arRegionCity['ID']] = $arRegionCity;
                }
        }
        return $arResult;
    }

}
