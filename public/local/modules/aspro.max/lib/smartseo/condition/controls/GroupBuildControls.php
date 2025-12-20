<?php

namespace Aspro\Max\Smartseo\Condition\Controls;

use Bitrix\Main\Localization\Loc;

class GroupBuildControls extends \CGlobalCondCtrlGroup implements BuildControlsInterface
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
			'showIn' => '',
			'visual' => static::GetVisual(),
			'control' => array_values(static::GetAtoms())
		];
	}

    public static function getAtoms()
    {
        return [
            'All' => [
                'id' => 'All',
                'name' => 'aggregator',
                'type' => 'select',
                'values' => [
                    'AND' => Loc::getMessage('BT_CLOBAL_COND_GROUP_SELECT_ALL'),
                    //'OR' => Loc::getMessage('BT_CLOBAL_COND_GROUP_SELECT_ANY')
                ],
                'defaultText' => Loc::getMessage('BT_CLOBAL_COND_GROUP_SELECT_DEF'),
                'defaultValue' => 'AND',
                'first_option' => '...'
            ],
        ];
    }

    public static function getVisual()
    {
        return [
            'controls' => [
                'All',
            ],
            'values' => [
                [
                    'All' => 'AND',
                    'True' => 'True'
                ],
                [
                    'All' => 'AND',
                    'True' => 'True'
                ],
            ],
            'logic' => [
                [
                    'style' => 'condition-logic-and',
                    'message' => Loc::getMessage('BT_CLOBAL_COND_GROUP_LOGIC_AND')
                ],
                [
                    'style' => 'condition-logic-and',
                    'message' => Loc::getMessage('BT_CLOBAL_COND_GROUP_LOGIC_AND')
                ],
            ]
        ];
    }

}
