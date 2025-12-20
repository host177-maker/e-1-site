<?php

namespace Aspro\Max\Smartseo\Admin\UI;

use Aspro\Max\Smartseo,
    \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class FilterRuleTagAdminUI extends AbstractAdminUI
{
    const PREFIX_COLUMN = 'FRT_';

    private $filterRuleId;

    function __construct($filterRuleId = null)
    {
        $this->filterRuleId = $filterRuleId;
    }

    public function getGridId()
    {
        return 'grid_filter_rule_tags';
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
                'title' => Loc::getMessage('SMARTSEO_TAG_UI_ACTIVE'),
                'name' => Loc::getMessage('SMARTSEO_TAG_UI_ACTIVE'),
                'sort' => 'ACTIVE',
                'default' => true,
            ],
            [
                'id' => self::PREFIX_COLUMN . 'TYPE',
                'field' => 'TYPE',
                'title' => Loc::getMessage('SMARTSEO_TAG_UI_TYPE'),
                'name' => Loc::getMessage('SMARTSEO_TAG_UI_TYPE'),
                'sort' => '',
                'default' => true,
                'width' => 300,
            ],
            [
                'id' => self::PREFIX_COLUMN . 'FILTER_CONDITION',
                'field' => 'FILTER_CONDITION',
                'title' => Loc::getMessage('SMARTSEO_TAG_UI_FILTER_CONDITION'),
                'name' => Loc::getMessage('SMARTSEO_TAG_UI_FILTER_CONDITION'),
                'sort' => 'FILTER_CONDITION_ID',
                'default' => true,
            ],
            [
                'id' => self::PREFIX_COLUMN . 'TEMPLATE',
                'field' => 'TEMPLATE',
                'title' => Loc::getMessage('SMARTSEO_TAG_UI_TEMPLATE'),
                'name' => Loc::getMessage('SMARTSEO_TAG_UI_TEMPLATE'),
                'sort' => 'TEMPLATE',
                'default' => true,
            ],
            [
                'id' => 'ID',
                'field' => 'ID',
                'title' => Loc::getMessage('SMARTSEO_TAG_UI_ENTITY_ID'),
                'name' => Loc::getMessage('SMARTSEO_TAG_UI_ENTITY_ID'),
                'sort' => 'ID',
                'default' => false,
            ],
        ];
    }

}
