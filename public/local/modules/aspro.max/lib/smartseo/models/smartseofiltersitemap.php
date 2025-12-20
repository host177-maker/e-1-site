<?php

namespace Aspro\Max\Smartseo\Models;

use Aspro\Max\Smartseo\Entity as SmartseoEntity,
    Aspro\Max\Smartseo\Admin\Settings\SettingSmartseo,
    Bitrix\Main\ORM\Fields\Relations\Reference,
    Bitrix\Main\ORM\Fields\Relations\OneToMany,
    Bitrix\Main\ORM\Query\Join,
    Bitrix\Main,
    Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class SmartseoFilterSitemapTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> FILTER_CONDITION_ID int mandatory
 * <li> SITEMAP_ID int mandatory
 * <li> ACTIVE bool optional default 'Y'
 * <li> CHANGEFREQ string(10) mandatory
 * <li> PRIORITY double mandatory
 * </ul>
 *
 * @package Bitrix\Aspro
 * */
class SmartseoFilterSitemapTable extends Main\Entity\DataManager
{
    const DEFAULT_CACHE_TTL = 0;

    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_aspro_max_smartseo_filter_sitemap';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return array(
            'ID' => array(
                'data_type' => 'integer',
                'primary' => true,
                'autocomplete' => true,
                'title' => Loc::getMessage('SMARTSEO_FILTER_SITEMAP_ENTITY_ID_FIELD'),
            ),
            'FILTER_CONDITION_ID' => array(
                'data_type' => 'integer',
                'validation' => array(__CLASS__, 'validateFilterCondition'),
                'required' => true,
                'title' => Loc::getMessage('SMARTSEO_FILTER_SITEMAP_ENTITY_FILTER_CONDITION_ID_FIELD'),
            ),
            'SITEMAP_ID' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => Loc::getMessage('SMARTSEO_FILTER_SITEMAP_ENTITY_SITEMAP_ID_FIELD'),
            ),
            'ACTIVE' => array(
                'data_type' => 'boolean',
                'values' => array('N', 'Y'),
                'title' => Loc::getMessage('SMARTSEO_FILTER_SITEMAP_ENTITY_ACTIVE_FIELD'),
                'default_value' => 'Y',
            ),
            'CHANGEFREQ' => array(
                'data_type' => 'string',
                'required' => true,
                'validation' => array(__CLASS__, 'validateChangefreq'),
                'title' => Loc::getMessage('SMARTSEO_FILTER_SITEMAP_ENTITY_CHANGEFREQ_FIELD'),
                'default_value' => 'always',
            ),
            'PRIORITY' => array(
                'data_type' => 'float',
                'required' => true,
                'title' => Loc::getMessage('SMARTSEO_FILTER_SITEMAP_ENTITY_PRIORITY_FIELD'),
                'default_value' => 0.5,
            ),
            (new Reference('FILTER_CONDITION', SmartseoFilterConditionTable::class, Join::on('this.FILTER_CONDITION_ID', 'ref.ID')))
                ->configureJoinType('left'),
            (new Reference('SITEMAP', SmartseoSitemapTable::class, Join::on('this.SITEMAP_ID', 'ref.ID')))
                ->configureJoinType('left'),
        );
    }

    public static function getChangefreqParams()
    {
        return [
            'always' => Loc::getMessage('SMARTSEO_FILTER_SITEMAP_CHANGEFREQ_ALWAYS'),
            'hourly' => Loc::getMessage('SMARTSEO_FILTER_SITEMAP_CHANGEFREQ_HOURLY'),
            'daily' => Loc::getMessage('SMARTSEO_FILTER_SITEMAP_CHANGEFREQ_DAILY'),
            'weekly' => Loc::getMessage('SMARTSEO_FILTER_SITEMAP_CHANGEFREQ_WEEKLY'),
            'monthly' => Loc::getMessage('SMARTSEO_FILTER_SITEMAP_CHANGEFREQ_MONTHLY'),
            'yearly' => Loc::getMessage('SMARTSEO_FILTER_SITEMAP_CHANGEFREQ_YEARLY'),
            'never' => Loc::getMessage('SMARTSEO_FILTER_SITEMAP_CHANGEFREQ_NEVER'),
        ];
    }

    public static function getPriorityParams()
    {
        return [
            '0.0' => '0.0',
            '0.1' => '0.1',
            '0.2' => '0.2',
            '0.3' => '0.3',
            '0.4' => '0.4',
            '0.5' => '0.5',
            '0.6' => '0.6',
            '0.7' => '0.7',
            '0.8' => '0.8',
            '0.9' => '0.9',
            '1.0' => '1.0',

        ];
    }

    /**
     * Returns validators for CHANGEFREQ field.
     *
     * @return array
     */
    public static function validateChangefreq()
    {
        return array(
            new Main\Entity\Validator\Length(null, 10),
        );
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
                    return Loc::getMessage('SMARTSEO_FILTER_SITEMAP_VALIDATE_FILTER_CONDITION');
                }

                return true;
            },
            function($value, $primary, $fields) {
                if(!$primary && $value && $fields['SITEMAP_ID']) {
                    $result = self::getRow([
                        'select' => [
                          'ID'
                        ],
                        'filter' => [
                            'SITEMAP_ID' => $fields['SITEMAP_ID'],
                            'FILTER_CONDITION_ID' => $value,
                        ]
                    ]);

                    if($result) {
                        return Loc::getMessage('SMARTSEO_FILTER_SITEMAP_VALIDATE_CONDITION_DUPLICATE');
                    }
                }

                return true;
            }
        );
    }

    public static function getCacheTtl()
    {
        $ttl = SettingSmartseo::getInstance()->getCacheTable();

        return $ttl ?: self::DEFAULT_CACHE_TTL;
    }

}
