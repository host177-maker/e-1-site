<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();?>
<?
$arParams['FILTER_PROP_CODE'] = ($arParams['FILTER_PROP_CODE'] ? $arParams['FILTER_PROP_CODE'] : 'FAVORIT_ITEM');
$arParams['SALE_STICKER'] = ($arParams['SALE_STICKER'] ? $arParams['SALE_STICKER'] : 'SALE_TEXT');
$arParams['STIKERS_PROP'] = ($arParams['STIKERS_PROP'] ? $arParams['STIKERS_PROP'] : 'HIT');

$GLOBALS[$arParams['FILTER_NAME']]['PROPERTY_'.$arParams['FILTER_PROP_CODE'].'_VALUE'] = 'Y';
?>