<?php

namespace Aspro\Max\Smartseo\Admin\UI;

use \Aspro\Max\Smartseo,
    \Aspro\Max\Smartseo\Admin\Helper,
    \Aspro\Max\Smartseo\Admin\Traits\BitrixCoreEntity,
    \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class NoindexRuleConditionAdminUI extends AbstractAdminUI
{

    const PREFIX_COLUMN = 'NIC_';

    private $noindexRuleId;

    use BitrixCoreEntity;

    function __construct($noindexRuleId = null)
    {
       $this->noindexRuleId = $noindexRuleId;
    }

    public function getFilterId()
    {
        return 'filter_noindex_rule_conditions';
    }

    public function getGridId()
    {
        return 'grid_noindex_rule_conditions';
    }

    public function getColumnGridPrefix()
    {
        return self::PREFIX_COLUMN;
    }

    public function getFilterFields()
    {
        return [];
    }

    public function getContextMenu($urlParams = [])
    {
        return [];
    }

    public function getGridColumns()
    {
        return [
            [
                'id' => self::PREFIX_COLUMN . 'ACTIVE',
                'field' => 'ACTIVE',
                'title' => Loc::getMessage('SMARTSEO__NRCA_UI__ENTITY__ACTIVE'),
                'name' => Loc::getMessage('SMARTSEO__NRCA_UI__ENTITY__ACTIVE'),
                'sort' => '',
                'width' => 80,
                'default' => true,
            ],
            [
                'id' => self::PREFIX_COLUMN . 'CONDITION',
                'field' => 'CONDITION',
                'title' => Loc::getMessage('SMARTSEO__NRCA_UI__ENTITY__CONDITION'),
                'name' => Loc::getMessage('SMARTSEO__NRCA_UI__ENTITY__CONDITION'),
                'sort' => '',
                'default' => true,
            ],
            [
                'id' => self::PREFIX_COLUMN . 'VALUE',
                'field' => 'VALUE',
                'title' => Loc::getMessage('SMARTSEO__NRCA_UI__ENTITY__VALUE'),
                'name' => Loc::getMessage('SMARTSEO__NRCA_UI__ENTITY__VALUE'),
                'sort' => '',
                'default' => true,
            ],
            [
                'id' => 'ID',
                'field' => 'ID',
                'title' => Loc::getMessage('SMARTSEO__NRCA_UI__ENTITY__ID'),
                'name' => Loc::getMessage('SMARTSEO__NRCA_UI__ENTITY__ID'),
                'sort' => '',
                'width' => 80,
                'default' => false,
            ],
        ];
    }

}
