<?php

namespace Aspro\Max\Smartseo\Admin\UI;

use Aspro\Max\Smartseo,
    \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class FilterRuleUrlAdminUI extends AbstractAdminUI
{
    const PREFIX_COLUMN = 'FRU_';

    private $filterRuleId;

    function __construct($filterRuleId = null)
    {
        $this->filterRuleId = $filterRuleId;
    }

    public function getGridId()
    {
        return 'grid_filter_rule_urls';
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

        return 'filter_grid_urls_' . $this->filterRuleId;
    }

    public function getFilterFields()
    {
        return [
            [
                'id' => 'ID',
                'name' => Loc::getMessage('SMARTSEO_URL_UI_ENTITY_ID'),
                'type' => 'number',
            ],
            [
                'id' => 'FILTER_CONDITION_ID',
                'name' => Loc::getMessage('SMARTSEO_URL_UI_ENTITY_FILTER_CONDITION'),
                'type' => 'custom_entity',
                'params' => [
                    'multiple' => 'Y'
                ],
                'default' => true,
            ],
            [
                'id' => 'REAL_URL',
                'name' => Loc::getMessage('SMARTSEO_URL_UI_ENTITY_REAL_URL'),
                'type' => 'text',
                'quickSearch' => '',
                'default' => true,
            ],
            [
                'id' => 'NEW_URL',
                'name' => Loc::getMessage('SMARTSEO_URL_UI_ENTITY_NEW_URL'),
                'type' => 'text',
                'default' => true,
            ],
            [
                'id' => 'SECTION_ID',
                'name' => Loc::getMessage('SMARTSEO_URL_UI_ENTITY_SECTION'),
                'type' => 'custom_entity',
                'params' => [
                    'multiple' => 'Y'
                ]
            ],
            [
                'id' => 'STATE_MODIFIED',
                'name' => Loc::getMessage('SMARTSEO_URL_UI_ENTITY_STATE_MODIFIED'),
                'type' => 'list',
                'items' => [
                    'Y' => Loc::getMessage('SMARTSEO_URL_UI_VALUE_Y'),
                    'N' => Loc::getMessage('SMARTSEO_URL_UI_VALUE_N'),
                ]
            ],
        ];
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
                'title' => Loc::getMessage('SMARTSEO_URL_UI_ENTITY_ACTIVE'),
                'name' => Loc::getMessage('SMARTSEO_URL_UI_ENTITY_ACTIVE'),
                'sort' => 'ACTIVE',
                'default' => true,
            ],
             [
                'id' => self::PREFIX_COLUMN . 'NEW_URL',
                'field' => 'NEW_URL',
                'title' => Loc::getMessage('SMARTSEO_URL_UI_ENTITY_NEW_URL'),
                'name' => Loc::getMessage('SMARTSEO_URL_UI_ENTITY_NEW_URL'),
                'sort' => '',
                'width' => 400,
                'default' => true,
            ],
            [
                'id' => self::PREFIX_COLUMN . 'SECTION',
                'field' => 'SECTION',
                'title' => Loc::getMessage('SMARTSEO_URL_UI_ENTITY_SECTION'),
                'name' => Loc::getMessage('SMARTSEO_URL_UI_ENTITY_SECTION'),
                'sort' => 'SECTION_ID',
                'width' => 200,
                'default' => true,
            ],
            [
                'id' => self::PREFIX_COLUMN . 'PROPERTIES',
                'field' => 'PROPERTIES',
                'title' => Loc::getMessage('SMARTSEO_URL_UI_ENTITY_PROPERTIES'),
                'name' => Loc::getMessage('SMARTSEO_URL_UI_ENTITY_PROPERTIES'),
                'sort' => '',
                'width' => 400,
                'default' => true,
            ],
            [
                'id' => self::PREFIX_COLUMN . 'FILTER_CONDITION',
                'field' => 'FILTER_CONDITION',
                'title' => Loc::getMessage('SMARTSEO_URL_UI_ENTITY_FILTER_CONDITION'),
                'name' => Loc::getMessage('SMARTSEO_URL_UI_ENTITY_FILTER_CONDITION'),
                'sort' => '',
                'width' => 200,
                'default' => true,
            ],
            [
                'id' => self::PREFIX_COLUMN . 'REAL_URL',
                'field' => 'REAL_URL',
                'title' => Loc::getMessage('SMARTSEO_URL_UI_ENTITY_REAL_URL'),
                'name' => Loc::getMessage('SMARTSEO_URL_UI_ENTITY_REAL_URL'),
                'sort' => '',
                'width' => 400,
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

    private function getFilterConditionItems()
    {
        if (!$this->filterRuleId) {
            return [];
        }

        $rows = Smartseo\Models\SmartseoFilterConditionTable::getList([
              'select' => [
                  'ID',
                  'NAME',
              ],
              'filter' => [
                  'FILTER_RULE.ID' => $this->filterRuleId
              ],
              'order' => [
                  'ID' => 'ASC',
              ],
              'cache' => [
                  'ttl' => Smartseo\Models\SmartseoFilterConditionTable::getCacheTtl(),
              ],
          ])->fetchAll();

        $result = [];
        $result[''] = Loc::getMessage('SMARTSEO_URL_UI_VALUE_ANY2');
        foreach ($rows as $row) {
            $result[$row['ID']] = $row['NAME']
              ? '[' . $row['ID'] . '] '  . $row['NAME']
              : Loc::getMessage('SMARTSEO_URL_UI_VALUE_CONDITION_DEFAUL', [
                  '#ID#' => $row['ID'],
            ]);
        }

        return $result;
    }

    private function getSectionItems()
    {
        if (!$this->filterRuleId) {
            return [];
        }

        $rows = Smartseo\Models\SmartseoFilterConditionUrlTable::getList([
              'select' => [
                  'SECTION_ID',
                  'SECTION_NAME' => 'SECTION.NAME',
              ],
              'filter' => [
                  'FILTER_CONDITION.FILTER_RULE.ID' => $this->filterRuleId
              ],
              'group' => [
                  'SECTION_ID',
                  'SECTION_NAME',
              ],
              'cache' => [
                  'ttl' => Smartseo\Models\SmartseoFilterConditionUrlTable::getCacheTtl(),
              ],
          ])->fetchAll();

        $result = [];
        $result[''] = Loc::getMessage('SMARTSEO_URL_UI_VALUE_ANY');
        foreach ($rows as $row) {
            $result[$row['SECTION_ID']] = '[' . $row['SECTION_ID'] . '] ' . $row['SECTION_NAME'];
        }

        return $result;
    }

}
