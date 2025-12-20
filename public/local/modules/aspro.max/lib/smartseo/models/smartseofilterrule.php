<?php

namespace Aspro\Max\Smartseo\Models;

use Aspro\Max\Smartseo\Entity as SmartseoEntity,
    Aspro\Max\Smartseo\Models\SmartseoFilterIblockSectionsTable,
    Aspro\Max\Smartseo\Models\SmartseoSeoTemplateTable,
    Aspro\Max\Smartseo\Admin\Settings\SettingSmartseo,
    Bitrix\Main,
    Bitrix\Main\Localization\Loc,
    Bitrix\Main\ORM\Fields\Relations\Reference,
    Bitrix\Main\ORM\Fields\Relations\OneToMany,
    Bitrix\Main\ORM\Query\Join;

Loc::loadMessages(__FILE__);

/**
 * Class SmartseoFilterRuleTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> ACTIVE bool optional default 'Y'
 * <li> NAME string(255) optional
 * <li> URL_STRICT_COMPLIANCE bool optional default 'Y'
 * <li> URL_CLOSE_INDEXING bool optional default 'N'
 * <li> SITE_ID string(4) mandatory
 * <li> IBLOCK_TYPE_ID string(255) mandatory
 * <li> IBLOCK_ID int mandatory
 * <li> IBLOCK_SECTION_IDS string optional
 * <li> IBLOCK_INCLUDE_SUBSECTIONS bool optional default 'Y'
 * <li> DATE_CREATE datetime optional
 * <li> DATE_CHANGE datetime optional
 * <li> PRIORITY int optional default 500
 * <li> SORT int optional default 500
 * <li> CREATED_BY int optional
 * <li> MODIFIED_BY int optional
 * <li> SECTION_ID int optional
 * </ul>
 *
 * @package Aspro\Max\Smartseo
 * */
class SmartseoFilterRuleTable extends Main\Entity\DataManager
{
    const DEFAULT_CACHE_TTL = 0;

    public static function getObjectClass()
    {
        return SmartseoEntity\FilterRule::class;
    }

    public static function getCollectionClass()
    {
        return SmartseoEntity\FilterRules::class;
    }

    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_aspro_max_smartseo_filter_rule';
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
                'title' => Loc::getMessage('SMARTSEO_FILTER_RULE_ENTITY_ID_FIELD'),
            ),
            'ACTIVE' => array(
                'data_type' => 'boolean',
                'values' => array('N', 'Y'),
                'title' => Loc::getMessage('SMARTSEO_FILTER_RULE_ENTITY_ACTIVE_FIELD'),
                'default_value' => 'Y',
            ),
            'NAME' => array(
                'data_type' => 'string',
                'validation' => array(__CLASS__, 'validateName'),
                'title' => Loc::getMessage('SMARTSEO_FILTER_RULE_ENTITY_NAME_FIELD'),
            ),
            'URL_STRICT_COMPLIANCE' => array(
                'data_type' => 'boolean',
                'values' => array('N', 'Y'),
                'title' => Loc::getMessage('SMARTSEO_FILTER_RULE_ENTITY_URL_STRICT_COMPLIANCE_FIELD'),
                'default_value' => 'Y',
            ),
            'URL_CLOSE_INDEXING' => array(
                'data_type' => 'boolean',
                'values' => array('N', 'Y'),
                'title' => Loc::getMessage('SMARTSEO_FILTER_RULE_ENTITY_URL_CLOSE_INDEXING_FIELD'),
                'default_value' => 'N',
            ),
            'SITE_ID' => array(
                'data_type' => 'string',
                'required' => true,
                'validation' => array(__CLASS__, 'validateSiteId'),
                'title' => Loc::getMessage('SMARTSEO_FILTER_RULE_ENTITY_SITE_ID_FIELD'),
            ),
            'IBLOCK_TYPE_ID' => array(
                'data_type' => 'string',
                'required' => true,
                'validation' => array(__CLASS__, 'validateIblockType'),
                'title' => Loc::getMessage('SMARTSEO_FILTER_RULE_ENTITY_IBLOCK_TYPE_FIELD'),
            ),
            'IBLOCK_ID' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => Loc::getMessage('SMARTSEO_FILTER_RULE_ENTITY_IBLOCK_ID_FIELD'),
            ),
            'IBLOCK_SECTION_IDS' => array(
                'data_type' => 'text',
                'title' => Loc::getMessage('SMARTSEO_FILTER_RULE_ENTITY_IBLOCK_SECTION_IDS_FIELD'),
            ),
            'IBLOCK_INCLUDE_SUBSECTIONS' => array(
                'data_type' => 'boolean',
                'values' => array('N', 'Y'),
                'title' => Loc::getMessage('SMARTSEO_FILTER_RULE_ENTITY_IBLOCK_INCLUDE_SUBSECTIONS_FIELD'),
                'default_value' => 'Y',
            ),
            'DATE_CREATE' => array(
                'data_type' => 'datetime',
                'title' => Loc::getMessage('SMARTSEO_FILTER_RULE_ENTITY_DATE_CREATE_FIELD'),
                'default_value' => new Main\Type\DateTime,
            ),
            'DATE_CHANGE' => array(
                'data_type' => 'datetime',
                'title' => Loc::getMessage('SMARTSEO_FILTER_RULE_ENTITY_DATE_CHANGE_FIELD'),
                'default_value' => new Main\Type\DateTime,
            ),
            'PRIORITY' => array(
                'data_type' => 'integer',
                'title' => Loc::getMessage('SMARTSEO_FILTER_RULE_ENTITY_PRIORITY_FIELD'),
            ),
            'SORT' => array(
                'data_type' => 'integer',
                'title' => Loc::getMessage('SMARTSEO_FILTER_RULE_ENTITY_SORT_FIELD'),
                'default_value' => 500,
            ),
            'CREATED_BY' => array(
                'data_type' => 'integer',
                'title' => Loc::getMessage('SMARTSEO_FILTER_RULE_ENTITY_CREATED_BY_FIELD'),
            ),
            'MODIFIED_BY' => array(
                'data_type' => 'integer',
                'title' => Loc::getMessage('SMARTSEO_FILTER_RULE_ENTITY_MODIFIED_BY_FIELD'),
            ),
            'SECTION_ID' => array(
                'data_type' => 'integer',
                'title' => Loc::getMessage('SMARTSEO_FILTER_RULE_ENTITY_SECTION_ID_FIELD'),
            ),
            (new OneToMany('IBLOCK_SECTIONS', SmartseoFilterIblockSectionsTable::class, 'FILTER_RULE'))
              ->configureJoinType('left'),
            (new Reference('SECTION', SmartseoFilterSectionTable::class, Join::on('this.SECTION_ID', 'ref.ID')))
              ->configureJoinType('left'),
            (new Reference('IBLOCK_TYPE', \Bitrix\Iblock\TypeLanguageTable::class, Join::on('this.IBLOCK_TYPE_ID', 'ref.IBLOCK_TYPE_ID')))
              ->configureJoinType('left'),
            (new Reference('IBLOCK', \Bitrix\Iblock\IblockTable::class, Join::on('this.IBLOCK_ID', 'ref.ID')))
              ->configureJoinType('left'),
            (new Reference('SITE', \Bitrix\Main\SiteTable::class, Join::on('this.SITE_ID', 'ref.LID')))
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

    /**
     * Returns validators for SITE_ID field.
     *
     * @return array
     */
    public static function validateSiteId()
    {
        return array(
            new Main\Entity\Validator\Length(null, 4),
        );
    }

    /**
     * Returns validators for IBLOCK_TYPE field.
     *
     * @return array
     */
    public static function validateIblockType()
    {
        return array(
            new Main\Entity\Validator\Length(null, 255),
        );
    }

    public static function getCacheTtl()
    {
        $ttl = SettingSmartseo::getInstance()->getCacheTable();

        return $ttl ?: self::DEFAULT_CACHE_TTL;
    }

    /**
     * @inheritdoc
     */
    public static function getRowById($id) {
        $data = parent::getRowById($id);

        if(!$data) {
            return [];
        }

        $rows = SmartseoFilterIblockSectionsTable::getList([
              'select' => ['ID', 'SECTION_ID'],
              'filter' => [
                  'FILTER_RULE_ID' => $id
              ],
              'cache' => [
                  'ttl' => SmartseoFilterIblockSectionsTable::DEFAULT_CACHE_TTL,
              ]
          ])->fetchAll();

        $data['IBLOCK_SECTIONS'] = array_column($rows, 'SECTION_ID');

        return $data;
    }

    public static function OnAfterDelete(Main\Entity\Event $event)
    {
        $result = new Main\Entity\EventResult;

        static::cascadeDeleteIblockSections($event, $result);

        static::cascadeDeleteSeoTemplates($event, $result);

        static::cascadeDeleteConditions($event, $result);

        return $result;
    }

    private static function cascadeDeleteIblockSections(Main\Entity\Event $event, Main\Entity\EventResult &$result)
    {
        $data = $event->getParameter('primary');

        if (!$data['ID'] || !intval($data['ID'])) {
            return;
        }

        $sql = 'DELETE FROM ' . SmartseoFilterIblockSectionsTable::getTableName()
          . ' WHERE FILTER_RULE_ID = ' . (int) $data['ID'];

        try {

            $connection = \Bitrix\Main\Application::getConnection();
            $connection->queryExecute($sql);
        } catch (Exception $e) {
            $result->addError(new Main\Entity\FieldError(
              $event->getEntity()->getField('ID'), SmartseoFilterIblockSectionsTable::getTableName() . ' FILTER_RULE_ID [' . $data['ID'] . '] - ' . $e->getMessage()
            ));
        }
    }

    private static function cascadeDeleteSeoTemplates(Main\Entity\Event $event, Main\Entity\EventResult &$result)
    {
        $data = $event->getParameter('primary');

        if (!$data['ID'] || !intval($data['ID'])) {
            return;
        }

        $sql = 'DELETE FROM ' . SmartseoSeoTemplateTable::getTableName()
          . ' WHERE ENTITY_TYPE = "FR" AND ENTITY_ID = ' . (int) $data['ID'];

        try {

            $connection = \Bitrix\Main\Application::getConnection();
            $connection->queryExecute($sql);
        } catch (Exception $e) {
            $result->addError(new Main\Entity\FieldError(
              $event->getEntity()->getField('ID'), SmartseoSeoTemplateTable::getTableName() . ' FILTER_RULE_ID [' . $data['ID'] . '] - ' . $e->getMessage()
            ));
        }
    }

    private static function cascadeDeleteConditions(Main\Entity\Event $event, Main\Entity\EventResult &$result)
    {
        $data = $event->getParameter('primary');

        if (!$data['ID'] || !intval($data['ID'])) {
            return;
        }

        $rows = SmartseoFilterConditionTable::getList([
            'select' => [
                'ID',
            ],
            'filter' => [
                'FILTER_RULE_ID' => $data['ID']
            ]
        ])->fetchAll();

        try {
            foreach ($rows as $row) {
                $_result = SmartseoFilterConditionTable::delete($row['ID']);

                if (!$_result->isSuccess()) {
                    throw new \Exception(implode('<br>', $_result->getErrorMessages()));
                }
            }
        } catch (Exception $e) {
            $result->addError(new Main\Entity\FieldError(
              $event->getEntity()->getField('ID'), SmartseoFilterConditionTable::getTableName() . ' FILTER_RULE_ID [' . $data['ID'] . '] - ' . $e->getMessage()
            ));
        }
    }

    public static function getMaxID()
    {
        $result = static::getRow([
              'select' => [
                  new Main\Entity\ExpressionField('MAX', 'MAX(%s)', ['ID'])
              ],
          ]);

        return $result['MAX'];
    }

}
