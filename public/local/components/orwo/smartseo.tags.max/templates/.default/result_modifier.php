<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

foreach($arResult['INFO'] as $sInfoKey => $arInfo) {
	$arNewAr = array();
	foreach($arInfo['ITEMS'] as $key => $arItem) {
		$arNewAr[$arItem['NAME']] = $arItem;
	}
	ksort($arNewAr, SORT_NATURAL);
	$arResult['INFO'][$sInfoKey]['ITEMS'] = $arNewAr;
}
?>