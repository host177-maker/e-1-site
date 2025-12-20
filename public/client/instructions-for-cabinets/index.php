<?php

use Cosmos\Config;

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Инструкции");
$APPLICATION->SetPageProperty('description', 'Инструкция по сборке шкафов от фабрики «E-1» доступна для скачивания на этой странице. Ознакомьтесь с подробным описанием и соберите вашу мебель правильно.');
?>
    <div class="detail">
	<div class="instructions">
        <h2 class="instructions-title">
            <?php
            $APPLICATION->IncludeComponent(
                "bitrix:main.include",
                "",
                array(
                    "AREA_FILE_SHOW" => "file",
                    "AREA_FILE_SUFFIX" => "",
                    "EDIT_TEMPLATE" => "",
                    "PATH" => SITE_DIR . "include/instruction.php",
                )
            );
            ?>
        </h2>

        <?php
        $APPLICATION->IncludeComponent(
            "bitrix:news.list",
            "instructions",
            array(
                "CACHE_TIME" => "36000000",
                "CACHE_TYPE" => "A",
                "IBLOCK_ID" => Config::getInstance()->getIblockIdByCode('instructions'),
                "IBLOCK_TYPE" => "content",
                "NEWS_COUNT" => "999",
                "FIELD_CODE" => array("NAME", "PREVIEW_TEXT", "DETAIL_PICTURE", ""),
                "PROPERTY_CODE" => array("SORT_MAIN"),
                "SET_BROWSER_TITLE" => "N",
                "SET_LAST_MODIFIED" => "N",
                "SET_META_DESCRIPTION" => "N",
                "SET_META_KEYWORDS" => "N",
                "SET_TITLE" => "N",
                "SORT_BY1" => "SORT",
                "SORT_BY2" => "ID",
                "SORT_ORDER1" => "ASC",
                "SORT_ORDER2" => "ASC",
                "FILTER_NAME" => "",
                "INCLUDE_IBLOCK_INTO_CHAIN" => "N",
                "ADD_SECTIONS_CHAIN" => "N",
            )
        );
        ?>
    </div>
</div>
<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>