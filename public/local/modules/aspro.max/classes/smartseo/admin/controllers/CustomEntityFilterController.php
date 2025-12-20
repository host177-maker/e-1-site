<?php

namespace Aspro\Max\Smartseo\Admin\Controllers;

use Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Admin\App\Controller,
    Bitrix\Main\Web\Json,
    Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class CustomEntityFilterController extends Controller
{
    public function getViewFolderName()
    {
        return 'custom_entity_main_ui_filter';
    }

    public function actionGetInnerUrlFilterCondition()
    {
        if (!check_bitrix_sessid()) {
            echo Json::encode([
                'result' => false,
                'message' => 'Bitrix session not found',
            ]);

            return;
        }

        if (!$filterRuleId = $this->request->get('filter_rule')) {
            return;
        }

        $list = Smartseo\Models\SmartseoFilterConditionTable::getList([
            'select' => [
                'ID',
                'NAME',
            ],
            'filter' => [
                'FILTER_RULE_ID' => $filterRuleId
            ],
            'cache' => [
                'ttl' => Smartseo\Models\SmartseoFilterConditionTable::getCacheTtl(),
            ],
          ])->fetchAll();

        $this->render('inner_filter_conditions', [
            'list' => $list
        ]);
    }

    public function actionGetInnerUrlSections()
    {
        if (!check_bitrix_sessid()) {
            echo Json::encode([
                'result' => false,
                'message' => 'Bitrix session not found',
            ]);

            return;
        }

        if (!$filterRuleId = $this->request->get('filter_rule')) {
            return;
        }

        $list = Smartseo\Models\SmartseoFilterConditionUrlTable::getList([
              'select' => [
                  'ITEM_ID' => 'SECTION_ID',
                  'ITEM_NAME' => 'SECTION.NAME',
              ],
              'filter' => [
                  'FILTER_CONDITION.FILTER_RULE.ID' => $filterRuleId
              ],
              'group' => [
                  'ITEM_ID',
                  'ITEM_NAME',
              ],
              'cache' => [
                  'ttl' => Smartseo\Models\SmartseoFilterConditionUrlTable::getCacheTtl(),
              ],
          ])->fetchAll();

        $this->render('inner_filter_select', [
            'list' => $list
        ]);
    }


}
