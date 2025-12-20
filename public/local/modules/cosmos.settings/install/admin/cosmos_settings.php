<?php

if (is_file($_SERVER["DOCUMENT_ROOT"] . "/local/modules/cosmos.settings/admin/cosmos_settings.php")) {
    /** @noinspection PhpIncludeInspection */
    require($_SERVER["DOCUMENT_ROOT"] . "/local/modules/cosmos.settings/admin/cosmos_settings.php");
} else {
    /** @noinspection PhpIncludeInspection */
    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/cosmos.settings/admin/cosmos_settings.php");
}
