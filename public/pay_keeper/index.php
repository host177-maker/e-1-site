<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("robots", "noindex, nofollow");
?>

<?php 
  echo file_get_contents("https://2e-1.server.paykeeper.ru/form/inline/");
?>
<div class="page-top">
	<div>
		<img class="pay-system" alt="pay system" src="/images/HorizontalLogos.png"/>
	</div>
</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>