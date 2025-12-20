<?php

namespace Aspro\Max\Smartseo\Condition\Controls;

use Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Admin\Settings,
    Aspro\Max\Smartseo\LazyLoader\ElementProperty,
    Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class CatalogGroupBuildControls extends \CCatalogCondCtrlComplex implements BuildControlsInterface
{

    private $filter = [];
    private static $controlList = [];
    private static $cacheTtl = 0;

    public function __construct()
    {

    }

    public function getBuild()
    {
        self::$cacheTtl = Settings\SettingSmartseo::getInstance()->getCacheConditionControls();

        return [
            'COMPLEX' => 'Y',
            'SORT' => 500,
            'CONTROLS' => static::GetControls(false, $this->filter),
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

    public static function getControls($controlId = false)
    {
        if (self::$controlList) {
            return static::searchControl(self::$controlList, $controlId);
        }

        $catalogGroups = self::getCatalogGroupList();

        foreach ($catalogGroups as $catalogGroup) {
            self::$controlList['CondCatalogGroup:' . $catalogGroup['ID']] = [
                'ID' => 'CondCatalogGroup:' . $catalogGroup['ID'],
                'PARENT' => false,
                'EXIST_HANDLER' => 'Y',
                'MODULE_ID' => Smartseo\General\Smartseo::MODULE_ID,
                'MODULE_ENTITY' => 'catalog',
                'ENTITY' => 'CATALOG_GROUP',
                'ENTITY_NAME' => Loc::getMessage('SMARTSEO_CONDITION_CONTROLS_CG_CATEGORY'),
                'FIELD' => 'CATALOG_GROUP_' . $catalogGroup['ID'],
                'CATALOG_GROUP_ID' => $catalogGroup['ID'],
                'CATALOG_GROUP_NAME' => $catalogGroup['NAME'],
                'CATALOG_GROUP_BASE' => $catalogGroup['BASE'],
                'CATALOG_GROUP_XML_ID' => $catalogGroup['XML_ID'],
                'FIELD_TABLE' => '',
                'FIELD_TYPE' => 'double',
                'MULTIPLE' => 'N',
                'GROUP' => 'N',
                'SEP' => 'N',
                'SEP_LABEL' => '',
                'LABEL' => $catalogGroup['NAME'],
                'PREFIX' => Loc::getMessage('SMARTSEO_CONDITION_CONTROLS_CG_PREFIX', [
                    '#NAME#' => $catalogGroup['NAME'],
                ]),
                'LOGIC' => static::GetLogic([
                    BT_COND_LOGIC_EGR,
                    BT_COND_LOGIC_ELS
                ]),
                'JS_VALUE' => [
                    'type' => 'input'
                ],
                'PHP_VALUE' => ''
            ];
        }

        return static::searchControl(self::$controlList, $controlId);
    }

    public static function getControlShow($params)
    {
        $result = [
            'controlgroup' => true,
            'group' => false,
            'label' => Loc::getMessage('SMARTSEO_CONDITION_CONTROLS_CG_CATEGORY'),
            'showIn' => static::getShowIn($params['SHOW_IN_GROUPS']),
            'children' => []
        ];

        foreach (static::getControls() as $control) {
            $result['children'][] = [
                'controlId' => $control['ID'],
                'group' => false,
                'label' => $control['LABEL'],
                'showIn' => static::getShowIn($params['SHOW_IN_GROUPS']),
                'control' => [
                    [
                        'id' => 'prefix',
                        'type' => 'prefix',
                        'text' => $control['PREFIX']
                    ],
                    static::getLogicAtom($control['LOGIC']),
                    static::getValueAtom($control['JS_VALUE'])
                ]
            ];
        }
        unset($control);

        return $result;
    }

    public static function applyValues($condition, $control)
	{
		$result = [];
		$values = false;

		$logics = [
            BT_COND_LOGIC_EGR,
            BT_COND_LOGIC_ELS
		];

		if (is_string($control)) {
			$control = static::getControls($control);
		}

        $values = static::check($condition, $condition, $control, false);

        if ($values === false) {
            return false;
        }

        $logic = static::searchLogic($values['logic'], $control['LOGIC']);

        $result = null;

        if (in_array($logic['ID'], $logics)) {
            $result = [
                'ID' => $control['ID'],
                'FIELD' => $control['FIELD'],
                'FIELD_TYPE' => $control['FIELD_TYPE'],
                'ENTITY' => $control['ENTITY'],
                'ENTITY_NAME' => $control['ENTITY_NAME'],
                'CATALOG_GROUP_ID' => $control['CATALOG_GROUP_ID'],
                'CATALOG_GROUP_NAME' => $control['CATALOG_GROUP_NAME'],
                'CATALOG_GROUP_BASE' => $control['CATALOG_GROUP_BASE'],
                'VALUES' => (is_array($values['value']) ? $values['value'] : [$values['value']]),
                'LOGIC' => $logic['VALUE'],
                'FILED_LOGIC' => $logic['LABEL'],
            ];

            if($result['VALUES']) {
                $result['DISPLAY_VALUES'] = $result['VALUES'];
            }
        }

        return $result ?: false;
	}

    public static function getApplyControl($condition, $control)
	{
		$result = [];
		$values = false;

		$logics = [
            BT_COND_LOGIC_EGR,
            BT_COND_LOGIC_ELS
		];

		if (is_string($control)) {
			$control = static::getControls($control);
		}

        $values = static::check($condition, $condition, $control, false);

        if ($values === false) {
            return false;
        }

        $logic = static::searchLogic($values['logic'], $control['LOGIC']);

        $result = null;

        if (in_array($logic['ID'], $logics)) {
            $result = [
                'ID' => $control['CATALOG_GROUP_ID'],
                'CLASS_ID' => $control['ID'],
                'ENTITY' => $control['ENTITY'],
                'CATALOG_GROUP_ID' => $control['CATALOG_GROUP_ID'],
                'CATALOG_GROUP_NAME' => $control['CATALOG_GROUP_NAME'],
                'CATALOG_GROUP_BASE' => $control['CATALOG_GROUP_BASE'],
                'LOGIC' => [
                    'OPERATOR' => $logic['VALUE'],
                    'VALUE' => (is_array($values['value']) ? $values['value'] : $values['value']),
                    'LABEL' => $logic['LABEL'],
                ]
            ];
        }

        return $result ?: false;
	}

    protected static function getCatalogGroupList()
    {
        $rows = \Bitrix\Catalog\GroupTable::getList(array_filter([
              'select' => [
                  'ID',
                  'NAME',
                  'BASE',
                  'XML_ID'
              ],
              'order' => [
                  'BASE' => 'ASC',
                  'SORT' => 'ASC',
              ]
          ]))->fetchAll();

        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'ID' => $row['ID'],
                'NAME' => $row['NAME'],
                'BASE' => $row['BASE'],
                'XML_ID' => $row['XML_ID'],
            ];
        }

        return $result;
    }

}
