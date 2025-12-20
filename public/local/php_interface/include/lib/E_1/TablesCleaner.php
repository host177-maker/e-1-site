<?php

namespace E_1;

class TablesCleaner
{
    static public function truncateCaptchaTable()
    {
        global $DB;

        $strSql = "TRUNCATE TABLE b_captcha";

        $res = $DB->Query($strSql, false, "File: ".__FILE__."Line: ".__LINE__);

        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/clear_tables_log.txt', print_r([
            'Дата' => date("Y-m-d H:i:s"),
            'Таблица' => 'b_captcha',
            'Результат' => $res
        ], true), FILE_APPEND);
    }

    static public function clearViewedProductsTable()
    {
        global $DB;

        $viewed_time = \COption::GetOptionString("sale", "viewed_time", "90");
        $viewed_time = IntVal($viewed_time);

        $strSql =
            "DELETE ".
            "FROM b_sale_viewed_product ".
            "WHERE TO_DAYS(DATE_VISIT) < (TO_DAYS(NOW()) - ".$viewed_time.")";

        $res = $DB->Query($strSql, false, "File: ".__FILE__."Line: ".__LINE__);

        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/clear_tables_log.txt', print_r([
            'Дата' => date("Y-m-d H:i:s"),
            'Таблица' => 'b_sale_viewed_product',
            'Результат' => $res
        ], true), FILE_APPEND);
    }
}