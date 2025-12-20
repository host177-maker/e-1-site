<?php

namespace Aspro\Max\Smartseo\Models;

use Aspro\Max\Smartseo\Entity as SmartseoEntity,
    Aspro\Max\Smartseo\Admin\Settings\SettingSmartseo,
    Bitrix\Main,
    Bitrix\Main\Localization\Loc,
    Bitrix\Main\ORM\Fields\Relations\Reference,
    Bitrix\Main\ORM\Fields\Relations\OneToMany,
    Bitrix\Main\ORM\Query\Join;

Loc::loadMessages(__FILE__);

/**
 * Class SmartseoFilterConditionTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> FILTER_RULE_ID int mandatory
 * <li> ACTIVE bool optional default 'Y'
 * <li> NAME string(255) optional
 * <li> URL_STRICT_COMPLIANCE bool optional default 'Y'
 * <li> URL_CLOSE_INDEXING bool optional default 'N'
 * <li> URL_TYPE_GENERATE string(2) mandatory default 'MR'
 * <li> URL_TEMPLATE string mandatory
 * <li> DATE_CREATE datetime optional
 * <li> DATE_CHANGE datetime optional
 * <li> PRIORITY int optional default 500
 * <li> SORT int optional default 500
 * <li> CREATED_BY int optional
 * <li> MODIFIED_BY int optional
 * <li> CONDITION_TREE string mandatory
 * </ul>
 *
 * @package Bitrix\Aspro
 * */
class SmartseoFilterConditionTable extends Main\Entity\DataManager
{

    const DEFAULT_CACHE_TTL = 0;
    const URL_TYPE_GENERATE_MERGE = 'MR';
    const URL_TYPE_GENERATE_COMBO = 'CM';

    public static function getObjectClass()
    {
        return SmartseoEntity\FilterCondition::class;
    }

    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_aspro_max_smartseo_filter_condition';
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
                'title' => Loc::getMessage('SMARTSEO_FILTER_CONDITION_ENTITY_ID_FIELD'),
            ),
            'FILTER_RULE_ID' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => Loc::getMessage('SMARTSEO_FILTER_CONDITION_ENTITY_FILTER_RULE_ID_FIELD'),
            ),
            'ACTIVE' => array(
                'data_type' => 'boolean',
                'values' => array('N', 'Y'),
                'title' => Loc::getMessage('SMARTSEO_FILTER_CONDITION_ENTITY_ACTIVE_FIELD'),
                'default_value' => 'Y',
            ),
            'NAME' => array(
                'data_type' => 'string',
                'validation' => array(__CLASS__, 'validateName'),
                'title' => Loc::getMessage('SMARTSEO_FILTER_CONDITION_ENTITY_NAME_FIELD'),
            ),
            'URL_STRICT_COMPLIANCE' => array(
                'data_type' => 'boolean',
                'values' => array('N', 'Y'),
                'title' => Loc::getMessage('SMARTSEO_FILTER_CONDITION_ENTITY_URL_STRICT_COMPLIANCE_FIELD'),
                'default_value' => 'Y',
            ),
            'URL_CLOSE_INDEXING' => array(
                'data_type' => 'boolean',
                'values' => array('N', 'Y'),
                'title' => Loc::getMessage('SMARTSEO_FILTER_CONDITION_ENTITY_URL_CLOSE_INDEXING_FIELD'),
                'default_value' => 'N',
            ),
            'URL_TYPE_GENERATE' => array(
                'data_type' => 'string',
                'required' => true,
                'validation' => array(__CLASS__, 'validateUrlTypeGenerate'),
                'title' => Loc::getMessage('SMARTSEO_FILTER_CONDITION_ENTITY_URL_TYPE_GENERATE_FIELD'),
                'default_value' => self::URL_TYPE_GENERATE_MERGE,
            ),
            'URL_TEMPLATE' => array(
                'data_type' => 'text',
                'required' => true,
                'title' => Loc::getMessage('SMARTSEO_FILTER_CONDITION_ENTITY_URL_TEMPLATE_FIELD'),
            ),
            'DATE_CREATE' => array(
                'data_type' => 'datetime',
                'title' => Loc::getMessage('SMARTSEO_FILTER_CONDITION_ENTITY_DATE_CREATE_FIELD'),
                'default_value' => new Main\Type\DateTime,
            ),
            'DATE_CHANGE' => array(
                'data_type' => 'datetime',
                'title' => Loc::getMessage('SMARTSEO_FILTER_CONDITION_ENTITY_DATE_CHANGE_FIELD'),
                'default_value' => new Main\Type\DateTime,
            ),
            'PRIORITY' => array(
                'data_type' => 'integer',
                'title' => Loc::getMessage('SMARTSEO_FILTER_CONDITION_ENTITY_PRIORITY_FIELD'),
                'default_value' => 500,
            ),
            'SORT' => array(
                'data_type' => 'integer',
                'title' => Loc::getMessage('SMARTSEO_FILTER_CONDITION_ENTITY_SORT_FIELD'),
                'default_value' => 500,
            ),
            'CREATED_BY' => array(
                'data_type' => 'integer',
                'title' => Loc::getMessage('SMARTSEO_FILTER_CONDITION_ENTITY_CREATED_BY_FIELD'),
            ),
            'MODIFIED_BY' => array(
                'data_type' => 'integer',
                'title' => Loc::getMessage('SMARTSEO_FILTER_CONDITION_ENTITY_MODIFIED_BY_FIELD'),
            ),
            'CONDITION_TREE' => array(
                'data_type' => 'text',
                'required' => true,
                'title' => Loc::getMessage('SMARTSEO_FILTER_CONDITION_ENTITY_CONDITION_TREE_FIELD'),
            ),
              (new Reference('FILTER_RULE', SmartseoFilterRuleTable::class, Join::on('this.FILTER_RULE_ID', 'ref.ID')))
              ->configureJoinType('left'),
              (new OneToMany('FILTER_CONDITION_URL', SmartseoFilterConditionUrlTable::class, 'FILTER_CONDITION'))
              ->configureJoinType('left'),
              (new Reference('FILTER_SITEMAP', SmartseoFilterSitemapTable::class, Join::on('ref.FILTER_CONDITION_ID', 'this.ID')))
              ->configureJoinType('left'),
        );
    }

    /**
     * Returns validators for NAME field.
     *
     * @return array
     */
    public static function validateName()
    {
        return array(
            new Main\Entity\Validator\Length(null, 255),
        );
    }

	public static function validateUrlTypeGenerate()
	{
		return array(
			new Main\Entity\Validator\Length(null, 2),
            function ($value) {
                if($value == self::URL_TYPE_GENERATE_COMBO || $value == self::URL_TYPE_GENERATE_MERGE) {
                    return true;
                }

                return Loc::getMessage('SMARTSEO_FILTER_CONDITION_VALIDATE_TYPE_GENERATE');
            }
		);
	}

    public static function getTypeGenerateList()
    {
        return [
            self::URL_TYPE_GENERATE_COMBO => Loc::getMessage('SMARTSEO_FILTER_CONDITION_URL_TYPE_GENERATE_COMBO'),
            self::URL_TYPE_GENERATE_MERGE => Loc::getMessage('SMARTSEO_FILTER_CONDITION_URL_TYPE_GENERATE_MERGE'),
        ];
    }

    public static function OnAfterDelete(Main\Entity\Event $event)
    {
        $result = new Main\Entity\EventResult;

        static::cascadeDeleteSeoTemplates($event, $result);

        static::cascadeDeleteConditionUrls($event, $result);

        static::cascadeDeleteFilterSitemap($event, $result);

        static::cascadeDeleteFilterTags($event, $result);

        return $result;
    }

    private static function cascadeDeleteSeoTemplates(Main\Entity\Event $event, Main\Entity\EventResult &$result)
    {
        $data = $event->getParameter('primary');

        if (!$data['ID'] || !intval($data['ID'])) {
            return;
        }

        $sql = 'DELETE FROM ' . SmartseoSeoTemplateTable::getTableName()
          . ' WHERE ENTITY_TYPE = "FC" AND ENTITY_ID = ' . (int) $data['ID'];

        try {

            $connection = \Bitrix\Main\Application::getConnection();
            $connection->queryExecute($sql);
        } catch (Exception $e) {
            $result->addError(new Main\Entity\FieldError(
              $event->getEntity()->getField('ID'), SmartseoFilterIblockSectionsTable::getTableName() . ' FILTER_RULE_ID [' . $data['ID'] . '] - ' . $e->getMessage()
            ));
        }
    }

    private static function cascadeDeleteConditionUrls(Main\Entity\Event $event, Main\Entity\EventResult &$result)
    {
        $data = $event->getParameter('primary');

        if (!$data['ID'] || !intval($data['ID'])) {
            return;
        }

        $sql = 'DELETE FROM ' . SmartseoFilterConditionUrlTable::getTableName()
          . ' WHERE FILTER_CONDITION_ID = ' . (int) $data['ID'];

        try {

            $connection = \Bitrix\Main\Application::getConnection();
            $connection->queryExecute($sql);
        } catch (Exception $e) {
            $result->addError(new Main\Entity\FieldError(
              $event->getEntity()->getField('ID'), SmartseoFilterConditionUrlTable::getTableName() . ' FILTER_CONDITION_ID [' . $data['ID'] . '] - ' . $e->getMessage()
            ));
        }
    }

    private static function cascadeDeleteFilterSitemap(Main\Entity\Event $event, Main\Entity\EventResult &$result)
    {
        $data = $event->getParameter('primary');

        if (!$data['ID'] || !intval($data['ID'])) {
            return;
        }

        $sql = 'DELETE FROM ' . SmartseoFilterSitemapTable::getTableName()
          . ' WHERE FILTER_CONDITION_ID = ' . (int) $data['ID'];

        try {

            $connection = \Bitrix\Main\Application::getConnection();
            $connection->queryExecute($sql);
        } catch (Exception $e) {
            $result->addError(new Main\Entity\FieldError(
              $event->getEntity()->getField('ID'), SmartseoFilterSitemapTable::getTableName() . ' FILTER_CONDITION_ID [' . $data['ID'] . '] - ' . $e->getMessage()
            ));
        }
    }

    private static function cascadeDeleteFilterTags(Main\Entity\Event $event, Main\Entity\EventResult &$result)
    {
        $data = $event->getParameter('primary');

        if (!$data['ID'] || !intval($data['ID'])) {
            return;
        }

        $sql = 'DELETE FROM ' . SmartseoFilterTagTable::getTableName()
          . ' WHERE FILTER_CONDITION_ID = ' . (int) $data['ID'] . ' OR PARENT_FILTER_CONDITION_ID = ' . (int) $data['ID'];

        try {

            $connection = \Bitrix\Main\Application::getConnection();
            $connection->queryExecute($sql);
        } catch (Exception $e) {
            $result->addError(new Main\Entity\FieldError(
              $event->getEntity()->getField('ID'), SmartseoFilterTagTable::getTableName() . ' FILTER_CONDITION_ID [' . $data['ID'] . '] - ' . $e->getMessage()
            ));
        }
    }

    public static function getCacheTtl()
    {
        $ttl = SettingSmartseo::getInstance()->getCacheTable();

        return $ttl ?: self::DEFAULT_CACHE_TTL;
    }

}
