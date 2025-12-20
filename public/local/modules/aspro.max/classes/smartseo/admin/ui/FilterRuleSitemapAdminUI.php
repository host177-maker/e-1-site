<?php

namespace Aspro\Max\Smartseo\Admin\UI;

use Aspro\Max\Smartseo,
    \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class FilterRuleSitemapAdminUI extends AbstractAdminUI
{
    const PREFIX_COLUMN = 'FRSM_';

    private $filterRuleId;

    function __construct($filterRuleId = null)
    {
        $this->filterRuleId = $filterRuleId;
    }

    public function getGridId()
    {
        return 'grid_filter_rule_sitemap';
    }

    public function getColumnGridPrefix()
    {
        return self::PREFIX_COLUMN;
    }

    public function getFilterId()
    {
        if (!$this->filterRuleId) {
            return '';
        }

        return 'filter_grid_sitemap_' . $this->filterRuleId;
    }

    public function getFilterFields()
    {
        return [];
    }

    public function getContextMenu()
    {
        return [];
    }

    public function getGridColumns()
    {
        return [
            [
                'id' => self::PREFIX_COLUMN . 'ACTIVE',
                'field' => 'ACTIVE',
                'title' => Loc::getMessage('SMARTSEO_CSM_UI_ACTIVE'),
                'name' => Loc::getMessage('SMARTSEO_CSM_UI_ACTIVE'),
                'sort' => 'ACTIVE',
                'default' => true,
            ],
            [
                'id' => self::PREFIX_COLUMN . 'SITEMAP',
                'field' => 'SITEMAP',
                'title' => Loc::getMessage('SMARTSEO_CSM_UI_SITEMAP'),
                'name' => Loc::getMessage('SMARTSEO_CSM_UI_SITEMAP'),
                'sort' => 'SITEMAP_ID',
                'default' => true,
            ],
            [
                'id' => self::PREFIX_COLUMN . 'FILTER_CONDITION',
                'field' => 'FILTER_CONDITION',
                'title' => Loc::getMessage('SMARTSEO_CSM_UI_FILTER_CONDITION'),
                'name' => Loc::getMessage('SMARTSEO_CSM_UI_FILTER_CONDITION'),
                'sort' => 'FILTER_CONDITION_ID',
                'default' => true,
            ],
            [
                'id' => self::PREFIX_COLUMN . 'CHANGEFREQ',
                'field' => 'CHANGEFREQ',
                'title' => Loc::getMessage('SMARTSEO_CSM_UI_CHANGEFREQ'),
                'name' => Loc::getMessage('SMARTSEO_CSM_UI_CHANGEFREQ'),
                'sort' => 'CHANGEFREQ',
                'default' => true,
            ],
            [
                'id' => self::PREFIX_COLUMN . 'PRIORITY',
                'field' => 'PRIORITY',
                'title' => Loc::getMessage('SMARTSEO_CSM_UI_PRIORITY'),
                'name' => Loc::getMessage('SMARTSEO_CSM_UI_PRIORITY'),
                'sort' => 'PRIORITY',
                'default' => true,
            ],
            [
                'id' => 'ID',
                'field' => 'ID',
                'title' => Loc::getMessage('SMARTSEO_URL_UI_ENTITY_ID'),
                'name' => Loc::getMessage('SMARTSEO_URL_UI_ENTITY_ID'),
                'sort' => 'ID',
                'default' => false,
            ],
        ];
    }

}
