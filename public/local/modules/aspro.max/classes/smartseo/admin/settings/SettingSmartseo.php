<?php

namespace Aspro\Max\Smartseo\Admin\Settings;

use Aspro\Max\Smartseo,
    Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class SettingSmartseo
{

    private static $instance;
    private $general = null;
    private $sites = null;
    private $currentSite = null;

    function __construct()
    {
        $this->general = Smartseo\Models\SmartseoSettingTable::getGeneralSettings();
        $this->sites = Smartseo\Models\SmartseoSettingTable::getSiteSettings();
        $this->currentSite = $this->sites['default'];
    }

    /**
     *
     * @return $this
     */
    public static function getInstance()
    {
        if (empty(self::$instance)) {
            self::$instance = new SettingSmartseo();
        }

        return self::$instance;
    }

    public function site($siteId)
    {
        $this->currentSite = $this->sites[$siteId];

        foreach ($this->sites['default'] as $option => $value)
        {
            if(!$this->currentSite[$option]) {
                $this->currentSite[$option] = $value;
            }
        }

        return $this;
    }

    public function getSite($siteId)
    {
        $this->currentSite = $this->sites[$siteId];

        foreach ($this->sites['default'] as $option => $value)
        {
            if(!$this->currentSite[$option]) {
                $this->currentSite[$option] = $value;
            }
        }

        return $this;
    }

    public function isOnlyCatalog()
    {
        return true;
    }

    public function getFilterRuleNameTemplate()
    {
        if (isset($this->general['FILTER_RULE_NAME_TEMPLATE']) && $this->general['FILTER_RULE_NAME_TEMPLATE']) {
            return $this->general['FILTER_RULE_NAME_TEMPLATE'];
        }

        return '';
    }

    public function getCacheTable()
    {
        if(isset($this->general['CACHE_TABLE']) && $this->general['CACHE_TABLE']) {
            return (int)$this->general['CACHE_TABLE'];
        }

        return 0;
    }

    public function getCacheSEOTemplate()
    {
        if(isset($this->general['CACHE_TEMPLATE_ENTITY']) && $this->general['CACHE_TEMPLATE_ENTITY']) {
            return (int)$this->general['CACHE_TEMPLATE_ENTITY'];
        }

        return 0;
    }

    public function getCacheConditionControls()
    {
        if(isset($this->general['CACHE_CONDITION_CONTROL']) && $this->general['CACHE_CONDITION_CONTROL']) {
            return (int)$this->general['CACHE_CONDITION_CONTROL'];
        }

        return 0;
    }

    public function getUrlSmartFilterTemplate($needSefFolder = true)
    {
        if ($needSefFolder) {
            return $this->currentSite['URL_SEF_FOLDER'] . $this->currentSite['URL_TEMPLATE_SMARTFILTER'];
        } else {
            return $this->currentSite['URL_TEMPLATE_SMARTFILTER'];
        }
    }

    public function getUrlSection()
    {
        return $this->currentSite['URL_SEF_FOLDER'] . $this->currentSite['URL_SECTION'];
    }

    public function getNewUrlSection()
    {
        return $this->currentSite['NEW_URL_SECTION'];
    }

    public function isReplaceTitlePage()
    {
        if(isset($this->general['PAGE_IS_REPLACE_TITLE']) && $this->general['PAGE_IS_REPLACE_TITLE'] === 'Y') {
            return true;
        }

        return false;
    }

    public function isSmartfilterFriendlyUrl()
    {
        if(isset($this->currentSite['SMARTFILTER_FRIENDLY']) && $this->currentSite['SMARTFILTER_FRIENDLY'] === 'Y') {
            return true;
        }

        return false;
    }

    public function getParametersGeneratingUrl()
    {
        return [
            'SEPARATOR_VALUES' => $this->currentSite['NEW_URL_SEPARATOR_PROPERTY_VALUES'],
            'REPLACE_SPACE' => $this->currentSite['NEW_URL_REPLACE_PROPERTY_SPACE'],
            'REPLACE_OTHER' => $this->currentSite['NEW_URL_REPLACE_PROPERTY_OTHER'],
            'PROPERTY_LIST_CODE' => $this->currentSite['NEW_URL_PROPERTY_LIST_CODE'],
            'PROPERTY_ELEMENT_CODE' => $this->currentSite['NEW_URL_PROPERTY_ELEMENT_CODE'],
            'PROPERTY_DIRECTORY_CODE' => $this->currentSite['NEW_URL_PROPERTY_DIRECTORY_CODE'],
            'SMARTFILTER_FILTER_NAME' =>  $this->currentSite['SMARTFILTER_FILTER_NAME'],
            'IS_FRIENDLY_URL' => $this->isSmartfilterFriendlyUrl(),
        ];
    }

    public function isReplaceMetaPage()
    {
        if(isset($this->general['PAGE_IS_REPLACE_META_TAGS']) && $this->general['PAGE_IS_REPLACE_META_TAGS'] === 'Y') {
            return true;
        }

        return false;
    }

    public function isReplaceSnippetPage()
    {
        if(isset($this->general['PAGE_IS_REPLACE_SNIPPET']) && $this->general['PAGE_IS_REPLACE_SNIPPET'] === 'Y') {
            return true;
        }

        return false;
    }

    public function isReplaceUrlPage()
    {
        if(isset($this->general['PAGE_IS_REPLACE_URL']) && $this->general['PAGE_IS_REPLACE_URL'] === 'Y') {
            return true;
        }

        return false;
    }

}
