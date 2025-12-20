<?php
global $APPLICATION;

use Bitrix\Main\Loader;

if ($APPLICATION->GetGroupRight('cosmos.settings') == 'D') {
    return false;
}

if (!Loader::includeModule('cosmos.settings')) {
    return false;
}

$aMenu = [
    'parent_menu' => 'global_menu_settings',
    'section' => 'Cosmos',
    'sort' => 50,
    'text' => 'Дополнительные настройки сайта',
    'icon' => 'sys_menu_icon',
    'page_icon' => 'sys_page_icon',
    'items_id' => 'cosmos_settings',
    'url' => 'cosmos_settings.php?' . http_build_query([
        'lang' => LANGUAGE_ID,
    ]),
    'items' => [],
];

return $aMenu;
