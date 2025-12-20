<?php
IncludeModuleLangFile(__FILE__);

if ($APPLICATION->GetGroupRight("search")!="D") {
    $aMenu = array(
        "parent_menu" => "global_menu_settings",
        "section" => "sitemap",
        "sort" => 200,
        "text" => GetMessage("mnu_sitemap"),
        "title" => GetMessage("mnu_sitemap_title"),
        "url" => "sitemap_index.php?lang=".LANGUAGE_ID,
        "icon" => "search_menu_icon",
        "page_icon" => "sitemap_page_icon",
        "items_id" => "menu_sitemap",
        "items" => array(
            array(
                "text" => GetMessage("mnu_sitemap"),
                "url" => "sitemap_sitemap.php?lang=".LANGUAGE_ID,
                "more_url" => array("sitemap_sitemap.php"),
                "title" => GetMessage("mnu_sitemap_alt"),
            ),
        )
    );
    return $aMenu;
}
return false;
