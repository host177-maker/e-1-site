<?php

namespace Aspro\Max\Smartseo\Admin\UI;

use Aspro\Max\Smartseo,
    \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class SitemapConditionAdminUI extends AbstractAdminUI
{
    const PREFIX_COLUMN = 'SC_';

    private $sitemapId;

    function __construct($sitemapId = null)
    {
        $this->sitemapId = $sitemapId;
    }

    public function getGridId()
    {
        return 'grid_sitemap_conditions';
    }

    public function getColumnGridPrefix()
    {
        return self::PREFIX_COLUMN;
    }

    public function getFilterId()
    {
        if (!$this->sitemapId) {
            return '';
        }

        return 'grid_sitemap_condition_' . $this->sitemapId;
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
                'title' => Loc::getMessage('SMARTSEO_SC_UI_ACTIVE'),
                'name' => Loc::getMessage('SMARTSEO_SC_UI_ACTIVE'),
                'sort' => 'ACTIVE',
                'default' => true,
            ],
            [
                'id' => self::PREFIX_COLUMN . 'FILTER_RULE',
                'field' => 'FILTER_RULE',
                'title' => Loc::getMessage('SMARTSEO_SC_UI_FILTER_RULE'),
                'name' => Loc::getMessage('SMARTSEO_SC_UI_FILTER_RULE'),
                'sort' => 'FILTER_RULE_ID',
                'default' => false,
            ],
            [
                'id' => self::PREFIX_COLUMN . 'FILTER_CONDITION',
                'field' => 'FILTER_CONDITION',
                'title' => Loc::getMessage('SMARTSEO_SC_UI_FILTER_CONDITION'),
                'name' => Loc::getMessage('SMARTSEO_SC_UI_FILTER_CONDITION'),
                'sort' => 'FILTER_CONDITION_ID',
                'default' => true,
            ],
            [
                'id' => self::PREFIX_COLUMN . 'CHANGEFREQ',
                'field' => 'CHANGEFREQ',
                'title' => Loc::getMessage('SMARTSEO_SC_UI_CHANGEFREQ'),
                'name' => Loc::getMessage('SMARTSEO_SC_UI_CHANGEFREQ'),
                'sort' => 'CHANGEFREQ',
                'default' => true,
            ],
            [
                'id' => self::PREFIX_COLUMN . 'PRIORITY',
                'field' => 'PRIORITY',
                'title' => Loc::getMessage('SMARTSEO_SC_UI_PRIORITY'),
                'name' => Loc::getMessage('SMARTSEO_SC_UI_PRIORITY'),
                'sort' => 'PRIORITY',
                'default' => true,
            ],
            [
                'id' => self::PREFIX_COLUMN . 'COUNT_URLS',
                'field' => 'COUNT_URLS',
                'title' => Loc::getMessage('SMARTSEO_SC_UI_COUNT_URLS'),
                'name' => Loc::getMessage('SMARTSEO_SC_UI_COUNT_URLS'),
                'sort' => '',
                'default' => true,
            ],
            [
                'id' => 'ID',
                'field' => 'ID',
                'title' => Loc::getMessage('SMARTSEO_SC_UI_ENTITY_ID'),
                'name' => Loc::getMessage('SMARTSEO_SC_UI_ENTITY_ID'),
                'sort' => 'ID',
                'default' => false,
            ],
        ];
    }

}
