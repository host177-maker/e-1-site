<?php

namespace Aspro\Max\Smartseo\Models;

use Aspro\Max\Smartseo\Admin\Settings\SettingSmartseo,
    Bitrix\Main\Localization\Loc,
	Bitrix\Main\ORM\Data\DataManager,
	Bitrix\Main\ORM\Fields\BooleanField,
	Bitrix\Main\ORM\Fields\IntegerField,
    Bitrix\Main\ORM\Fields\TextField,
	Bitrix\Main\ORM\Fields\StringField,
    Bitrix\Main\ORM\Fields\DatetimeField,
	Bitrix\Main\ORM\Fields\Validators\LengthValidator,
    Bitrix\Main\ORM\Fields\Relations\Reference,
    Bitrix\Main\ORM\Query\Join;

Loc::loadMessages(__FILE__);

/**
 * Class SmartseoFilterSearchTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> FILTER_CONDITION_ID int mandatory
 * <li> ACTIVE bool ('N', 'Y') optional default 'Y'
 * <li> DATE_CREATE datetime optional
 * <li> DATE_CHANGE datetime optional
 * <li> TITLE_TEMPLATE text optional
 * <li> BODY_TEMPLATE text optional
 * <li> STATUS string(2) optional default 'NI' ('NI', 'SI')
 * </ul>
 *
 * @package Bitrix\Aspro
 **/

class SmartseoFilterSearchTable extends DataManager
{
    const DEFAULT_CACHE_TTL = 0;

    const STATUS_NOT_INDEXED = 'NI';
    const STATUS_SUCCESS_INDEXED = 'SI';

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_aspro_max_smartseo_filter_search';
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
					'title' => Loc::getMessage('SMARTSEO_FILTER_SEARCH_ENTITY_ID_FIELD')
				]
			),
			new IntegerField(
				'FILTER_CONDITION_ID',
				[
					'required' => true,
                    'validation' => array(__CLASS__, 'validateFilterCondition'),
					'title' => Loc::getMessage('SMARTSEO_FILTER_SEARCH_ENTITY_FILTER_CONDITION_ID_FIELD')
				]
			),
			new BooleanField(
				'ACTIVE',
				[
					'values' => array('N', 'Y'),
					'default' => 'Y',
					'title' => Loc::getMessage('SMARTSEO_FILTER_SEARCH_ENTITY_ACTIVE_FIELD')
				]
			),
            new DatetimeField(
				'DATE_CREATE',
				[
					'title' => Loc::getMessage('SMARTSEO_FILTER_SEARCH_ENTITY_DATE_CREATE_FIELD'),
                    'default' => new \Bitrix\Main\Type\DateTime,
				]
			),
			new DatetimeField(
				'DATE_CHANGE',
				[
					'title' => Loc::getMessage('SMARTSEO_FILTER_SEARCH_ENTITY_DATE_CHANGE_FIELD'),
                    'default' => new \Bitrix\Main\Type\DateTime,
				]
			),
            new TextField(
				'TITLE_TEMPLATE',
				[
                    'required' => true,
					'title' => Loc::getMessage('SMARTSEO_FILTER_SEARCH_ENTITY_TITLE_TEMPLATE_FIELD')
				]
			),
			new TextField(
				'BODY_TEMPLATE',
				[
					'title' => Loc::getMessage('SMARTSEO_FILTER_SEARCH_ENTITY_BODY_TEMPLATE_FIELD')
				]
			),
			new StringField(
				'STATUS',
				[
					'default' => 'NI',
					'validation' => [__CLASS__, 'validateStatus'],
					'title' => Loc::getMessage('SMARTSEO_FILTER_SEARCH_ENTITY_STATUS_FIELD')
				]
			),
            (new Reference('FILTER_CONDITION', SmartseoFilterConditionTable::class, Join::on('this.FILTER_CONDITION_ID', 'ref.ID')))
                ->configureJoinType('left'),
		];
	}

	/**
	 * Returns validators for STATUS field.
	 *
	 * @return array
	 */
	public static function validateStatus()
	{
		return [
			new LengthValidator(null, 2),
            function($value, $primary, $fields) {
                if(in_array($value, self::getStatusParams())) {
                    return Loc::getMessage('SMARTSEO_FILTER_SEARCH_VALIDATE_STATUS', [
                        '#STATUS#' => $value,
                    ]);
                }

                return true;
            }
		];
	}

    /**
     * Returns validators for FILTER_CONDITION_ID field.
     *
     * @return array
     */
    public static function validateFilterCondition()
    {
        return array(
            function($value, $primary, $fields) {
                if(empty($value)) {
                    return Loc::getMessage('SMARTSEO_FILTER_SEARCH_VALIDATE_FILTER_CONDITION');
                }

                return true;
            },
            function($value, $primary, $fields) {
                if($primary && $value == $fields['FILTER_CONDITION_ID']) {
                    return true;
                }

                $result = self::getRow([
                    'select' => [
                      'ID'
                    ],
                    'filter' => [
                        'FILTER_CONDITION_ID' => $value,
                    ]
                ]);

                if($result) {
                    return Loc::getMessage('SMARTSEO_FILTER_SEARCH_VALIDATE_CONDITION_DUPLICATE');
                }

                return true;
            }
        );
    }

    public static function getStatusParams($status = null)
    {
        $result = [
            self::STATUS_NOT_INDEXED => Loc::getMessage('SMARTSEO_FILTER_SEARCH_STATUS_NOT_INDEXED'),
            self::STATUS_SUCCESS_INDEXED => Loc::getMessage('SMARTSEO_FILTER_SEARCH_STATUS_SUCCESS_INDEXED'),

        ];

        if($status) {
            return $result[$status];
        }

        return $result;
    }

    public static function getCacheTtl()
    {
        $ttl = SettingSmartseo::getInstance()->getCacheTable();

        return $ttl ?: self::DEFAULT_CACHE_TTL;
    }
}