<?php

namespace Aspro\Max\Smartseo\Admin\UI;

use Aspro\Max\Smartseo,
    \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class FilterRuleSearchAdminUI extends AbstractAdminUI
{
    const PREFIX_COLUMN = 'FRS_';

    private $filterRuleId;

    function __construct($filterRuleId = null)
    {
        $this->filterRuleId = $filterRuleId;
    }

    public function getGridId()
    {
        return 'grid_filter_rule_search';
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

        return 'filter_grid_tag_' . $this->filterRuleId;
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
                'title' => Loc::getMessage('SMARTSEO_SEARCH_UI_ACTIVE'),
                'name' => Loc::getMessage('SMARTSEO_SEARCH_UI_ACTIVE'),
                'sort' => 'ACTIVE',
                'default' => true,
            ],
            [
                'id' => self::PREFIX_COLUMN . 'FILTER_CONDITION',
                'field' => 'FILTER_CONDITION',
                'title' => Loc::getMessage('SMARTSEO_SEARCH_UI_FILTER_CONDITION'),
                'name' => Loc::getMessage('SMARTSEO_SEARCH_UI_FILTER_CONDITION'),
                'sort' => 'FILTER_CONDITION_ID',
                'default' => true,
            ],
            [
                'id' => 'ID',
                'field' => 'ID',
                'title' => Loc::getMessage('SMARTSEO_SEARCH_UI_ENTITY_ID'),
                'name' => Loc::getMessage('SMARTSEO_SEARCH_UI_ENTITY_ID'),
                'sort' => 'ID',
                'default' => false,
            ],
        ];
    }

}
