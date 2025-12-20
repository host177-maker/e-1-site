<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
$arCityRegion = [];
foreach ($arResult['ITEMS'] as $key => $arVacancy) {
	if (!empty($arVacancy["PROPERTIES"]["CITY_REGION"]["VALUE"])) {
		if (!in_array($arVacancy["PROPERTIES"]["CITY_REGION"]["VALUE"], $arCityRegion))
		$arCityRegion[] = $arVacancy["PROPERTIES"]["CITY_REGION"]["VALUE"];
	}
}
if (!empty($arCityRegion)) {
	$arResult['CITY'] = \Cosmos\Content::getCityRegionForIDs($arCityRegion);
}

?>