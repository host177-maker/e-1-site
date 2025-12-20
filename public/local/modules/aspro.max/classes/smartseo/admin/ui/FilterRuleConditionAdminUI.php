<?php

namespace Aspro\Max\Smartseo\Admin\UI;

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class FilterRuleConditionAdminUI extends AbstractAdminUI
{
    const PREFIX_COLUMN = 'FRC_';

    private $filterRuleId;

    function __construct($filterRuleId = null)
    {
        $this->filterRuleId = $filterRuleId;
    }

    public function getGridId()
    {
        return 'grid_filter_rule_conditions';
    }

    public function getColumnGridPrefix()
    {
        return self::PREFIX_COLUMN;
    }

    public function getFilterId()
    {
        if(!$this->filterRuleId) {
            return '';
        }

        return 'filter_grid_filter_rule_conditions_' . $this->filterRuleId;
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
                'id' => 'ID',
                'field' => 'ID',
                'title' => '',
                'content' => '',
                'sort' => '',
                'default' => false,
            ],
            [
                'id' => self::PREFIX_COLUMN . 'CONDITION',
                'field' => 'CONDITION',
                'title' => '',
                'content' => '',
                'sort' => '',
                'default' => true,
            ],
            [
                'id' => self::PREFIX_COLUMN . 'INFO',
                'field' => 'INFO',
                'title' => '',
                'content' => '',
                'sort' => '',
                'default' => true,
            ],
        ];
    }

}