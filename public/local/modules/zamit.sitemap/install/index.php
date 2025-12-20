<?php
IncludeModuleLangFile(__FILE__);

class zamit_sitemap extends CModule
{
    public $MODULE_ID = "zamit.sitemap";
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $MODULE_CSS;

    public $errors;

    public function zamit_sitemap()
    {
        $arModuleVersion = array();

        $path = str_replace("\\", "/", __FILE__);
        $path = substr($path, 0, strlen($path) - strlen("/index.php"));
        include($path."/version.php");

        if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        } else {
            $this->MODULE_VERSION = SEARCH_VERSION;
            $this->MODULE_VERSION_DATE = SEARCH_VERSION_DATE;
        }

        $this->PARTNER_NAME = GetMessage("ZAM_IT_PARTNER_NAME");
        $this->PARTNER_URI = 'http://www.zam-it.ru/solutions/';

        $this->MODULE_NAME = GetMessage("SITEMAP_MODULE_NAME");
        $this->MODULE_DESCRIPTION = GetMessage("SITEMAP_MODULE_DESC");
    }

    public function InstallDB($arParams = array())
    {
        RegisterModule("zamit.sitemap");
        return true;
    }

    public function UnInstallDB($arParams = array())
    {
        UnRegisterModule("zamit.sitemap");
        return true;
    }

    public function InstallEvents()
    {
        return true;
    }

    public function UnInstallEvents()
    {
        return true;
    }

    public function InstallFiles($arParams = array())
    {
        if ($_ENV["COMPUTERNAME"]!='BX') {
            CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/zamit.sitemap/install/admin/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
            CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/zamit.sitemap/install/images/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/images/zamit.sitemap", true, true);
        }

        return true;
    }

    public function UnInstallFiles()
    {
        if ($_ENV["COMPUTERNAME"]!='BX') {
            DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/zamit.sitemap/install/admin/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
            DeleteDirFilesEx("/bitrix/themes/.default/icons/zamit.sitemap/");//icons
            DeleteDirFilesEx("/bitrix/images/zamit.sitemap/");//images
        }
        return true;
    }

    public function DoInstall()
    {
        global $DOCUMENT_ROOT, $APPLICATION, $step;
        $step = IntVal($step);
        if ($step < 2) {
            $APPLICATION->IncludeAdminFile(GetMessage("SITEMAP_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/zamit.sitemap/install/step1.php");
        } elseif ($step == 2) {
            $db_install_ok = $this->InstallDB(array(
                "DATABASE" => $_REQUEST["DATABASE"],
            ));
            if ($db_install_ok) {
                $this->InstallEvents();
                $this->InstallFiles();
            }
            $GLOBALS["errors"] = $this->errors;
            $APPLICATION->IncludeAdminFile(GetMessage("SITEMAP_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/zamit.sitemap/install/step2.php");
        }
    }

    public function DoUninstall()
    {
        global $DOCUMENT_ROOT, $APPLICATION, $step;
        $step = IntVal($step);
        if ($step < 2) {
            $APPLICATION->IncludeAdminFile(GetMessage("SITEMAP_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/zamit.sitemap/install/unstep1.php");
        } elseif ($step == 2) {
            $this->UnInstallDB(array(
                "savedata" => $_REQUEST["savedata"],
                "savestat" => $_REQUEST["savestat"],
            ));
            $this->UnInstallFiles();
            $GLOBALS["errors"] = $this->errors;
            $APPLICATION->IncludeAdminFile(GetMessage("SITEMAP_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/zamit.sitemap/install/unstep2.php");
        }
    }

    public function OnGetTableList()
    {
        return array(
            "MODULE" => new search,
            "TABLES" => array(
                "b_search_content" => "ID",
                "b_search_content_right" => "SEARCH_CONTENT_ID",
                "b_search_content_site" => "SEARCH_CONTENT_ID",
                "b_search_content_stem" => "SEARCH_CONTENT_ID",
                "b_search_content_title" => "SEARCH_CONTENT_ID",
                "b_search_content_freq" => "STEM",
                "b_search_custom_rank" => "ID",
                "b_search_tags" => "SEARCH_CONTENT_ID",
                "b_search_suggest" => "ID",
                "b_search_phrase" => "ID",
                "b_search_user_right" => "USER_ID",
                "b_search_content_param" => "SEARCH_CONTENT_ID",
            ),
        );
    }
}
