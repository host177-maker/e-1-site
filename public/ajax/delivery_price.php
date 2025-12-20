<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

\Bitrix\Main\Loader::includeModule('catalog');
\Bitrix\Main\Loader::includeModule('iblock');

$context = \Bitrix\Main\Context::getCurrent();
$request = $context->getRequest();

$cityName = $request->get('cityname');


try {
    //&& $arRegions = \CMaxRegionality::getRegions()
    if (!empty($cityName)) {
        $arSelect = array("ID", "IBLOCK_ID", "NAME", "PROPERTY_REGION_TAG_DELIVERY_REGION", "PROPERTY_CITY_HOME");
        $rsRegion = \CIBlockElement::GetList(array(), array("IBLOCK_ID" => \Cosmos\Config::getInstance()->getIblockIdByCode('aspro_max_regions'), "NAME" => $cityName), false, false, $arSelect);
        $arRegion = $rsRegion->Fetch();

        if (!empty($arRegion)) {
            die(json_encode($arRegion));
        }
        die(json_encode(['NotFound' => $cityName]));
    }
} catch (\Throwable $th) {
    die(json_encode(['Error' => $th->getMessage()]));
}


