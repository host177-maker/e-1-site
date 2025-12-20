<?php

namespace Aspro\Max\Smartseo\General;

use \Bitrix\Main\Application;

class Smartseo
{
    const MODULE_ID = 'aspro.max';

    private static $currentDataScope = [];
    private static $currentSeoProperty = [];
    /** @var \Aspro\Max\Smartseo\Admin\Settings\SettingSmartseo */
    private static $setting = null;

    private static $isParentModule = false;
    private static $enableNoindexRule = true;

    static public function init()
    {
         self::$setting = \Aspro\Max\Smartseo\Admin\Settings\SettingSmartseo::getInstance();
    }

    public function getModulePath($needDocumentRoot = true)
    {
        $dir = '';

        if ($needDocumentRoot) {
            $dir = dirname(__DIR__);
        } else {
            $dir = str_ireplace(Application::getDocumentRoot(), '', dirname(__DIR__));
        }

        $dir = str_ireplace(['classes', 'smartseo'], '', $dir);

        return preg_replace('|([/]+)|s', '/', $dir);
    }

    static public function setCurrentData($dataScope)
    {
        self::$currentDataScope = $dataScope;
    }

    static public function getCurrentData($code = '')
    {
        if(self::$currentDataScope && self::$currentDataScope[$code]) {
            return self::$currentDataScope[$code];
        }

        return self::$currentDataScope;
    }

    static public function setCurrentSeoProperty($seoProperty)
    {
        self::$currentSeoProperty = $seoProperty;
    }

    static public function getCurrentSeoProperty($code = '')
    {
        if(self::$currentSeoProperty && self::$currentSeoProperty[$code]) {
            return self::$currentSeoProperty[$code];
        }

        return self::$currentSeoProperty;
    }

    static public function getSettingObject()
    {
        return self::$setting;
    }

    static public function fixBitrixCoreAjaxAuth()
    {
        global $APPLICATION;

        if (\CSite::InDir('/bitrix/') && preg_match('|' . self::MODULE_ID . '_smartseo.php|', $APPLICATION->GetCurPage(false)) && $_REQUEST['AUTH_FORM'] == 'Y') {
            $_SESSION['SMARTSEO_NEED_RELOAD'] = true;
        }
    }

    static public function disallowNoindexRule(bool $value)
    {
        self::$enableNoindexRule = !$value;
    }

    static public function allowedNoindexRule()
    {
        return self::$enableNoindexRule;
    }

    static public function validateModules()
    {
        if(self::$isParentModule) {
            return self::$isParentModule;
        }

        if (!\Bitrix\Main\Loader::includeModule(self::MODULE_ID)) {
            self::$isParentModule = false;

            return false;
        }

        self::$isParentModule = true;

        return self::$isParentModule;
    }

}
