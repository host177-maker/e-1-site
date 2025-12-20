<?php
use E_1\Sitemap;

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);

$_SERVER["DOCUMENT_ROOT"] = dirname(dirname(__FILE__)) . "/public";
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
set_time_limit(0);

while(ob_get_level()) {
    ob_end_flush();
}

echo "\e[1;32m[" . date("d.m.Y H:i:s") . " Начало генерации карты сайта" . " \e[0m\n";
$sMask = "/bitrix/*;/404.php;/upload/*;/auth/*;*/search/*;*/tags*;/personal/*;/e-store/affiliates/*;/content/*/my/*;/examples/*;/map.php;*/detail.php;/communication/voting/*;/club/index.php;/codegen_DELETE*;*.ru?oid=*;/_old_/*;/include/*;/order/*;/form/*;/basket/*;*/.hg/*;*/.svn/*;*/.git/*;*/cgi-bin/*;/bitrix_personal/*;/local/*;/makeup/*;/_ajax/*;/personal/*;*cabinet*;/favorites/*;/action/*;/detail.php*;/partners/*";
try {
	// var_dump(SITE_ID);die;
    $objSitemap = new Sitemap();
    $bSitemapCreateRes = $objSitemap->create('s1', 1, 2000, $sMask);
    echo "\e[1;32m[ Генерация карты прошла успешно ] \e[0m\n";	
} catch (Exception $e) {
    echo "\e[1;31m[ " . $e->getMessage() . " ] \e[0m\n";
}

echo "\e[1;32m[" . date("d.m.Y H:i:s") . " Конец генерации карты сайта" . " \e[0m\n";