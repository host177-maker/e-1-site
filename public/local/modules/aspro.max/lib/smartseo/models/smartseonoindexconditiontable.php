<?php

namespace Aspro\Max\Smartseo\Models;

use Aspro\Max\Smartseo\Admin\Settings\SettingSmartseo,
    Bitrix\Main\Localization\Loc,
	Bitrix\Main\ORM\Data\DataManager,
	Bitrix\Main\ORM\Fields\BooleanField,
	Bitrix\Main\ORM\Fields\IntegerField,
	Bitrix\Main\ORM\Fields\StringField,
	Bitrix\Main\ORM\Fields\TextField,
	Bitrix\Main\ORM\Fields\Validators\LengthValidator,
    Bitrix\Main\ORM\Fields\Relations\Reference,
    Bitrix\Main\ORM\Query\Join;

Loc::loadMessages(__FILE__);

/**
 * Class SmartseoNoindexConditionTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> NOINDEX_RULE_ID int mandatory
 * <li> TYPE string(2) mandatory
 * <li> VALUE int optional default 0
 * <li> ACTIVE bool ('N', 'Y') optional default 'Y'
 * <li> PRIORITY int optional default 500
 * <li> SORT int optional default 500
 * <li> PROPERTIES text optional
 * </ul>
 *
 * @package Bitrix\Aspro
 **/

class SmartseoNoindexConditionTable extends DataManager
{
    const DEFAULT_CACHE_TTL = 0;

    const TYPE_EXCEPTION_BY_COUNT = 'EC';
    const TYPE_EXCEPTION_BY_VALUES = 'EV';
    const TYPE_EXCEPTION_BY_PROPERTIES = 'EP';

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_aspro_max_smartseo_noindex_condition';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			new IntegerField(
				'ID',
				[
					'primary' => true,
					'autocomplete' => true,
					'title' => Loc::getMessage('SMARTSEO_NOINDEX_CONDITION_ENTITY_ID_FIELD')
				]
			),
			new IntegerField(
				'NOINDEX_RULE_ID',
				[
					'required' => true,
					'title' => Loc::getMessage('SMARTSEO_NOINDEX_CONDITION_ENTITY_NOINDEX_RULE_ID_FIELD')
				]
			),
			new StringField(
				'TYPE',
				[
					'required' => true,
					'validation' => [__CLASS__, 'validateType'],
					'title' => Loc::getMessage('SMARTSEO_NOINDEX_CONDITION_ENTITY_TYPE_FIELD')
				]
			),
            new IntegerField(
				'VALUE',
				[
					'default' => 0,
					'title' => Loc::getMessage('SMARTSEO_NOINDEX_CONDITION_ENTITY_VALUE_FIELD')
				]
			),
			new BooleanField(
				'ACTIVE',
				[
					'values' => array('N', 'Y'),
					'default' => 'Y',
					'title' => Loc::getMessage('SMARTSEO_NOINDEX_CONDITION_ENTITY_ACTIVE_FIELD')
				]
			),
			new IntegerField(
				'PRIORITY',
				[
					'default' => 500,
					'title' => Loc::getMessage('SMARTSEO_NOINDEX_CONDITION_ENTITY_PRIORITY_FIELD')
				]
			),
			new IntegerField(
				'SORT',
				[
					'default' => 500,
					'title' => Loc::getMessage('SMARTSEO_NOINDEX_CONDITION_ENTITY_SORT_FIELD')
				]
			),
			new TextField(
				'PROPERTIES',
				[
					'title' => Loc::getMessage('SMARTSEO_NOINDEX_CONDITION_ENTITY_PROPERTIES_FIELD')
				]
			),
            (new Reference('NOINDEX_RULE', SmartseoNoindexRuleTable::class, Join::on('this.NOINDEX_RULE_ID', 'ref.ID')))
                ->configureJoinType('left'),
		];
	}

	/**
	 * Returns validators for TYPE field.
	 *
	 * @return array
	 */
	public static function validateType()
	{
		return [
			new LengthValidator(null, 2),
            function($value) {
                if(!in_array($value, [self::TYPE_EXCEPTION_BY_COUNT, self::TYPE_EXCEPTION_BY_VALUES, self::TYPE_EXCEPTION_BY_PROPERTIES])) {
                    return Loc::getMessage('SMARTSEO_NOINDEX_VALIDATE_TYPE', [
                        '#TYPE#' => $value,
                    ]);
                }

                return true;
            },

            function($value, $primary, $fields) {
                $row = self::getRow([
                    'select' => [
                        'ID'
                    ],
                    'filter' => [
                        '!ID' => $primary['ID'],
                        'TYPE' => $value,
                        'NOINDEX_RULE_ID' => $fields['NOINDEX_RULE_ID'],
                    ]
                ]);

                if($row) {
                   return Loc::getMessage('SMARTSEO_NOINDEX_VALIDATE_EXIST_TYPE');
                }

                return true;
            }
		];
	}

    public static function getTypeParams()
    {
        return [
            self::TYPE_EXCEPTION_BY_COUNT => Loc::getMessage('SMARTSEO_NOINDEX_TYPE_EXCEPTION_BY_COUNT'),
            self::TYPE_EXCEPTION_BY_VALUES => Loc::getMessage('SMARTSEO_NOINDEX_TYPE_EXCEPTION_BY_VALUES'),
            self::TYPE_EXCEPTION_BY_PROPERTIES  => Loc::getMessage('SMARTSEO_NOINDEX_TYPE_EXCEPTION_BY_PROPERTIES'),
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getRowById($id) {
        $data = parent::getRowById($id);

        if($data['PROPERTIES']) {
            $data['PROPERTIES'] = unserialize($data['PROPERTIES'], ['allowed_classes' => false]);
        } else {
            $data['PROPERTIES'] = [];
        }

        return $data;
    }

    public static function getListByRule($noindexId)
    {
        $rows = self::getList([
              'select' => [
                  'ID',
                  'NOINDEX_RULE_ID',
                  'TYPE',
                  'VALUE',
                  'PROPERTIES',
              ],
              'filter' => [
                  'ACTIVE' => 'Y',
                  'NOINDEX_RULE_ID' => $noindexId
              ],
              'order' => [
                  'PRIORITY' => 'ASC',
              ],
              'cache' => [
                  'ttl' => 0,
              ]
          ])->fetchAll();

        $result = [];

        foreach ($rows as $row) {
            if ($row['PROPERTIES']) {
                $row['PROPERTIES'] = unserialize($row['PROPERTIES'], ['allowed_classes' => false]);
            }

            $result[] = $row;
        }

        return $result;
    }

    public static function getCacheTtl()
    {
        $ttl = SettingSmartseo::getInstance()->getCacheTable();

        return $ttl ?: self::DEFAULT_CACHE_TTL;
    }
}