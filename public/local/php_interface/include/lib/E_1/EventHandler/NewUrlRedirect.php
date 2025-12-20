<?php

namespace E_1\EventHandler;

class NewUrlRedirect
{
    const IBLOCK_CATALOG_ID = 48;
    const CACHE_TIME = 36000000000;

    static function OnBeforePrologHandler()
    {
        global $APPLICATION;
        $page = $APPLICATION->GetCurPage(false);
        //проверяем УРЛ. Редирект со старых УРЛ товаров
        if (stripos($page, '/catalog/') !== false) {
            
            if (preg_match("#\/(\d+)\/#i", $page, $ar_exp) > 0 && $elementId = $ar_exp[1]) {
                $arSelect = array("ID", "IBLOCK_ID", "NAME", "DETAIL_PAGE_URL");
                $filter = array(
                    "IBLOCK_ID" => self::IBLOCK_CATALOG_ID,
                    "ACTIVE_DATE" => "Y",
                    "ACTIVE" => "Y",
                    "ID" => $elementId,
                );
                //Запрос с кешированием
                $cache = new \CPHPCache();
                $cache_time = self::CACHE_TIME;
                $cache_id = 'sc_element_' . serialize($filter);
                $cache_path = '/new_catalog_url/';
                if ($cache_time > 0 && $cache->InitCache($cache_time, $cache_id, $cache_path)) {
                    $res = $cache->GetVars();
                    if (is_array($res["arFields"]) && (count($res["arFields"]) > 0))
                        $arFields = $res["arFields"];
                }
                if (!isset($arFields)) {
                    $res = \CIBlockElement::GetList(array("ID" => "ASC"), $filter, false, array("nTopCoumt" => 1), $arSelect);
                    if ($ob = $res->GetNextElement()) {
                        $arFields = $ob->GetFields();
                    }
                    if ($cache_time > 0) {
                        $cache->StartDataCache($cache_time, $cache_id, $cache_path);
                        $cache->EndDataCache(array("arFields" => $arFields));
                    }
                }
                if (is_array($arFields) && strlen($arFields['DETAIL_PAGE_URL']) > 0) {
                    if ($page != $arFields["DETAIL_PAGE_URL"]) {
                        LocalRedirect($arFields["DETAIL_PAGE_URL"], false, "301 Moved Permanently");
                    }
                }
            }
        }
    }
}