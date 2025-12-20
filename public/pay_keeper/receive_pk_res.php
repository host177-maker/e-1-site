<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
?>
<?
//оставлю, вдруг через запросы будут делаться уведомления
$APPLICATION->IncludeComponent(
	"bitrix:sale.order.payment.receive", 
	"", 
	array(
		"PAY_SYSTEM_ID_NEW" => "13"
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>