<?php

namespace Aspro\Max\Smartseo\Condition\Controls;

use Bitrix\Main\Localization\Loc;

class GroupDefaultBuildControls extends \CGlobalCondCtrlGroup implements BuildControlsInterface
{

    public function getBuild()
    {
        return [
            'ID' => static::GetControlID(),
            'GROUP' => 'Y',
            'SORT' => 100,
            'GetControlShow' => [self::class, 'GetControlShow'],
            'GetConditionShow' => [self::class, 'GetConditionShow'],
            'IsGroup' => [self::class, 'IsGroup'],
            'Parse' => [self::class, 'Parse'],
            'Generate' => [self::class, 'Generate'],
            'ApplyValues' => [self::class, 'ApplyValues'],
            'getApplyControl' => [self::class, 'getApplyControl'],
            'InitParams' => [self::class, 'InitParams'],
        ];
    }

    public static function applyValues($arOneCondition, $arControl)
    {
        return true;
    }

    public static function getControlShow($arParams)
    {
        return [
            'controlId' => static::GetControlID(),
            'group' => true,
            'label' => Loc::getMessage('BT_CLOBAL_COND_GROUP_LABEL'),
            'defaultText' => Loc::getMessage('BT_CLOBAL_COND_GROUP_DEF_TEXT'),
            'showIn' => static::GetShowIn($arParams['SHOW_IN_GROUPS']),
            'visual' => static::GetVisual(),
            'control' => array_values(static::GetAtoms())
        ];
    }

    public static function GetAtoms()
    {
        return [
            'All' => [
                'id' => 'All',
                'name' => 'aggregator',
                'type' => 'select',
                'values' => [
                    'AND' => Loc::getMessage('BT_CLOBAL_COND_GROUP_SELECT_ALL'),
                    'OR' => Loc::getMessage('BT_CLOBAL_COND_GROUP_SELECT_ANY')
                ],
                'defaultText' => Loc::getMessage('BT_CLOBAL_COND_GROUP_SELECT_DEF'),
                'defaultValue' => 'AND',
                'first_option' => '...'
            ],
        ];
    }

    public static function GetVisual()
    {
        return [
            'controls' => [
                'All',
            ],
            'values' => [
                [
                    'All' => 'AND',
                ],
                [
                    'All' => 'OR',
                ],
            ],
            'logic' => [
                [
                    'style' => 'condition-logic-and',
                    'message' => Loc::getMessage('BT_CLOBAL_COND_GROUP_LOGIC_AND')
                ],
                [
                    'style' => 'condition-logic-or',
                    'message' => Loc::getMessage('BT_CLOBAL_COND_GROUP_LOGIC_OR')
                ],
            ]
        ];
    }

    public static function getApplyControl($condition, $control)
    {
        return [
            'LOGIC' => [
                'OPERATOR' => $condition['All'],
                'LABEL' => $condition['All'] == 'AND' ? Loc::getMessage('BT_CLOBAL_COND_GROUP_LOGIC_AND') : Loc::getMessage('BT_CLOBAL_COND_GROUP_LOGIC_OR'),
            ]
        ];
    }
}
