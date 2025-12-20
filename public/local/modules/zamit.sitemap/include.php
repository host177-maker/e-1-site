<?php
if (!defined("CACHED_b_search_tags")) {
    define("CACHED_b_search_tags", 3600);
}
if (!defined("CACHED_b_search_tags_len")) {
    define("CACHED_b_search_tags_len", 2);
}

global $DB;
$db_type = strtolower($DB->type);
CModule::AddAutoloadClasses(
    "zamit.sitemap",
    array(
        "CSiteMapG" => "classes/".$db_type."/sitemap.php",
        "zamit.sitemap" => "install/index.php",
    )
);
/*
function GenerateUniqId($sName)
{
    static $arPostfix = array();

    $sPostfix = rand();
    while(isset($arPostfix[$sPostfix]))
        $sPostfix = rand();

    $arPostfix[$sPostfix] = 1;

    return preg_replace("/\W/", "_", $sName).$sPostfix;
}
*/
$DB_test = CDatabase::GetModuleConnection('zamit.sitemap', true);
if (!is_object($DB_test)) {
    return false;
}
