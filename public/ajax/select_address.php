<?define("STATISTIC_SKIP_ACTIVITY_CHECK", "true");?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");?>
<?
if(isset($_POST['address']) && !empty($_POST['address'])){
	$server = \Bitrix\Main\Context::getCurrent()->getServer();
	$request = \Bitrix\Main\Context::getCurrent()->getRequest();
	$oNewForm = new \E_1\KladrApi();
	$aSendApi = $oNewForm->getAddressApi(['query' => $_POST['address'], 'cityId' =>$_POST['city']]);
	foreach ($aSendApi['result'] as $key => $value) {
		$arResult[] = $value['name'];
	}
	if(isset($arResult) && !empty($arResult)){
		$arResult = array_unique($arResult);
		die(json_encode($arResult));
	}
}
?>
