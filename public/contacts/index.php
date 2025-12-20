<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("description", "Адреса мебельных салонов E-1.");
$APPLICATION->SetPageProperty("title", "Контакты");
//$APPLICATION->SetTitle("Контакты " . IN_CITY);
$APPLICATION->SetTitle("Контакты");?>

<?CMax::ShowPageType('page_contacts');?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>