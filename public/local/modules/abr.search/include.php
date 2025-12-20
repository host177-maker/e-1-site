<?php

class CAbrSearch
{
    static function OnBuildGlobalMenu(&$aGlobalMenu, &$aModuleMenu)
    {

    }

    static function OnBeforeProlog()
    {

        if (\Bitrix\Main\Config\Option::get('abr.search', 'USE_ANMARTO') === 'Y') {
            require_once __DIR__ . '/lib/full_text.php';
            require_once __DIR__ . '/lib/custom_all_search.php';
            require_once __DIR__ . '/lib/custom_search.php';
            require_once __DIR__ . '/lib/anmarto.php';
            require_once __DIR__ . '/lib/title.php';
        }
    }
}

?>
