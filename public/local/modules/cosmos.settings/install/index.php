<?php

class cosmos_settings extends CModule
{
    var $MODULE_ID = "cosmos.settings";
    var $MODULE_NAME = "Общие настройки сайта";
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_DESCRIPTION;
    var $PARTNER_NAME;
    var $PARTNER_URI;
    var $MODULE_GROUP_RIGHTS = "Y";

    function cosmos_settings()
    {
        $arModuleVersion = [];

        include(__DIR__ . "/version.php");

        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

        $this->MODULE_NAME = "Cosmos settings";
        $this->MODULE_DESCRIPTION = "Общие настройки сайта";

        $this->PARTNER_NAME = "Космос-Веб";
        $this->PARTNER_URI = "https://www.cosmos-web.ru/";
    }

    function InstallFiles()
    {
        CopyDirFiles(__DIR__ . "/admin", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin", true, true);

        return true;
    }

    function UnInstallFiles()
    {
        DeleteDirFiles(__DIR__ . "/admin", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin");

        return true;
    }

    function DoInstall()
    {
        RegisterModule($this->MODULE_ID);

        $this->InstallFiles();
    }

    function DoUninstall()
    {

        UnRegisterModule($this->MODULE_ID);

        $this->UnInstallFiles();
    }
}
