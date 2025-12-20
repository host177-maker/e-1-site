<?php

namespace Aspro\Max\Smartseo\Models;

use Aspro\Max\Smartseo\Admin\Settings\SettingSmartseo,
    Bitrix\Main,
    Bitrix\Main\Localization\Loc,
	Bitrix\Main\ORM\Data\DataManager,
	Bitrix\Main\ORM\Fields\BooleanField,
	Bitrix\Main\ORM\Fields\DatetimeField,
	Bitrix\Main\ORM\Fields\IntegerField,
	Bitrix\Main\ORM\Fields\StringField,
	Bitrix\Main\ORM\Fields\TextField,
	Bitrix\Main\ORM\Fields\Validators\LengthValidator,
    Bitrix\Main\ORM\Fields\Relations\Reference,
    Bitrix\Main\ORM\Fields\Relations\OneToMany,
    Bitrix\Main\ORM\Query\Join;

Loc::loadMessages(__FILE__);

/**
 * Class SmartseoNoindexRuleTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> NAME string(255) optional
 * <li> ACTIVE bool ('N', 'Y') optional default 'Y'
 * <li> SECTION_ID int optional default 0
 * <li> SITE_ID string(4) mandatory
 * <li> IBLOCK_TYPE_ID string(255) optional
 * <li> IBLOCK_ID int optional
 * <li> IBLOCK_SECTION_ALL bool ('N', 'Y') optional default 'Y'
 * <li> URL_TEMPLATE text mandatory
 * <li> DATE_CREATE datetime optional
 * <li> DATE_CHANGE datetime optional
 * <li> DATE_LAST_RUN datetime optional
 * <li> SORT int optional default 500
 * <li> CREATED_BY int optional
 * <li> MODIFIED_BY int optional
 * </ul>
 *
 * @package Bitrix\Aspro
 **/

class SmartseoNoindexRuleTable extends DataManager
{
    const DEFAULT_CACHE_TTL = 0;

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_aspro_max_smartseo_noindex_rule';
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
					'title' => Loc::getMessage('SMARTSEO_NOINDEX_RULE_ENTITY_ID_FIELD')
				]
			),
			new StringField(
				'NAME',
				[
					'validation' => [__CLASS__, 'validateName'],
					'title' => Loc::getMessage('SMARTSEO_NOINDEX_RULE_ENTITY_NAME_FIELD')
				]
			),
			new BooleanField(
				'ACTIVE',
				[
					'values' => array('N', 'Y'),
					'default' => 'Y',
					'title' => Loc::getMessage('SMARTSEO_NOINDEX_RULE_ENTITY_ACTIVE_FIELD')
				]
			),
			new IntegerField(
				'SECTION_ID',
				[
					'default' => 0,
					'title' => Loc::getMessage('SMARTSEO_NOINDEX_RULE_ENTITY_SECTION_ID_FIELD')
				]
			),
			new StringField(
				'SITE_ID',
				[
					'required' => true,
					'validation' => [__CLASS__, 'validateSiteId'],
					'title' => Loc::getMessage('SMARTSEO_NOINDEX_RULE_ENTITY_SITE_ID_FIELD')
				]
			),
			new StringField(
				'IBLOCK_TYPE_ID',
				[
                    'required' => true,
					'validation' => [__CLASS__, 'validateIblockTypeId'],
					'title' => Loc::getMessage('SMARTSEO_NOINDEX_RULE_ENTITY_IBLOCK_TYPE_ID_FIELD')
				]
			),
			new IntegerField(
				'IBLOCK_ID',
				[
                    'required' => true,
					'title' => Loc::getMessage('SMARTSEO_NOINDEX_RULE_ENTITY_IBLOCK_ID_FIELD')
				]
			),
			new BooleanField(
				'IBLOCK_SECTION_ALL',
				[
					'values' => array('N', 'Y'),
					'default' => 'N',
					'title' => Loc::getMessage('SMARTSEO_NOINDEX_RULE_ENTITY_IBLOCK_SECTION_ALL_FIELD')
				]
			),
			new TextField(
				'URL_TEMPLATE',
				[
					'required' => true,
					'title' => Loc::getMessage('SMARTSEO_NOINDEX_RULE_ENTITY_URL_TEMPLATE_FIELD')
				]
			),
			new DatetimeField(
				'DATE_CREATE',
				[
                    'default' => new \Bitrix\Main\Type\DateTime,
					'title' => Loc::getMessage('SMARTSEO_NOINDEX_RULE_ENTITY_DATE_CREATE_FIELD')
				]
			),
			new DatetimeField(
				'DATE_CHANGE',
				[
                    'default' => new \Bitrix\Main\Type\DateTime,
					'title' => Loc::getMessage('SMARTSEO_NOINDEX_RULE_ENTITY_DATE_CHANGE_FIELD')
				]
			),
			new DatetimeField(
				'DATE_LAST_RUN',
				[
                    'default' => new \Bitrix\Main\Type\DateTime,
					'title' => Loc::getMessage('SMARTSEO_NOINDEX_RULE_ENTITY_DATE_LAST_RUN_FIELD')
				]
			),
			new IntegerField(
				'SORT',
				[
					'default' => 500,
					'title' => Loc::getMessage('SMARTSEO_NOINDEX_RULE_ENTITY_SORT_FIELD')
				]
			),
			new IntegerField(
				'CREATED_BY',
				[
					'title' => Loc::getMessage('SMARTSEO_NOINDEX_RULE_ENTITY_CREATED_BY_FIELD')
				]
			),
			new IntegerField(
				'MODIFIED_BY',
				[
					'title' => Loc::getMessage('SMARTSEO_NOINDEX_RULE_ENTITY_MODIFIED_BY_FIELD')
				]
			),
            (new OneToMany('IBLOCK_SECTIONS', SmartseoNoindexIblockSectionsTable::class, 'NOINDEX_RULE'))
              ->configureJoinType('left'),
            (new Reference('IBLOCK_TYPE', \Bitrix\Iblock\TypeLanguageTable::class, Join::on('this.IBLOCK_TYPE_ID', 'ref.IBLOCK_TYPE_ID')))
              ->configureJoinType('left'),
            (new Reference('IBLOCK', \Bitrix\Iblock\IblockTable::class, Join::on('this.IBLOCK_ID', 'ref.ID')))
              ->configureJoinType('left'),
            (new Reference('SITE', \Bitrix\Main\SiteTable::class, Join::on('this.SITE_ID', 'ref.LID')))
              ->configureJoinType('left'),
            (new OneToMany('NOINDEX_URLS', SmartseoNoindexUrlTable::class, 'NOINDEX_URLS'))
              ->configureJoinType('left'),
		];
	}

	/**
	 * Returns validators for NAME field.
	 *
	 * @return array
	 */
	public static function validateName()
	{
		return [
			new LengthValidator(null, 255),
		];
	}

	/**
	 * Returns validators for SITE_ID field.
	 *
	 * @return array
	 */
	public static function validateSiteId()
	{
		return [
			new LengthValidator(null, 4),
		];
	}

	/**
	 * Returns validators for IBLOCK_TYPE_ID field.
	 *
	 * @return array
	 */
	public static function validateIblockTypeId()
	{
		return [
			new LengthValidator(null, 255),
		];
	}

    public static function findByUrl($url, $siteId)
    {
        $urls = SmartseoNoindexUrlTable::getDataByUrl($url, $siteId);

        $result = [];
        if ($urls) {
            foreach ($urls as $dataUrl) {
                $dataConditions = SmartseoNoindexConditionTable::getListByRule($dataUrl['NOINDEX_RULE_ID']);

                $result[] = [
                    'URL' => $dataUrl['URL'],
                    'IBLOCK_ID' => $dataUrl['IBLOCK_ID'],
                    'SECTION_ID' => $dataUrl['SECTION_ID'],
                    'CONDITIONS' => $dataConditions,
                ];
            }
        }

        return $result;
    }

    public static function getMapFields() {
        $result = [];

        foreach (static::getMap() as $field) {
            $result[] = $field->getName();
        }

        return $result;
    }

    public static function hasMapField($code)
    {
        return in_array($code, self::getMapFields());
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

        $rows = SmartseoNoindexIblockSectionsTable::getList([
              'select' => ['ID', 'SECTION_ID'],
              'filter' => [
                  'NOINDEX_RULE_ID' => $id
              ],
              'cache' => [
                  'ttl' => SmartseoNoindexIblockSectionsTable::getCacheTtl(),
              ]
          ])->fetchAll();

        $data['IBLOCK_SECTIONS'] = array_column($rows, 'SECTION_ID');

        return $data;
    }

    public static function OnAfterDelete(Main\Entity\Event $event)
    {
        $result = new Main\Entity\EventResult;

        static::cascadeDeleteIblockSections($event, $result);

        static::cascadeDeleteConditions($event, $result);

        static::cascadeDeleteUrls($event, $result);

        return $result;
    }

    private static function cascadeDeleteIblockSections(Main\Entity\Event $event, Main\Entity\EventResult &$result)
    {
        $data = $event->getParameter('primary');

        if (!$data['ID'] || !intval($data['ID'])) {
            return;
        }

        $sql = 'DELETE FROM ' . SmartseoNoindexIblockSectionsTable::getTableName()
          . ' WHERE NOINDEX_RULE_ID = ' . (int) $data['ID'];

        try {
            $connection = \Bitrix\Main\Application::getConnection();
            $connection->queryExecute($sql);
        } catch (Exception $e) {
            $result->addError(new Main\Entity\FieldError(
              $event->getEntity()->getField('ID'), SmartseoNoindexIblockSectionsTable::getTableName() . ' SEO_TEXT_ID [' . $data['ID'] . '] - ' . $e->getMessage()
            ));
        }
    }

    private static function cascadeDeleteConditions(Main\Entity\Event $event, Main\Entity\EventResult &$result)
    {
        $data = $event->getParameter('primary');

        if (!$data['ID'] || !intval($data['ID'])) {
            return;
        }

        $sql = 'DELETE FROM ' . SmartseoNoindexConditionTable::getTableName()
          . ' WHERE NOINDEX_RULE_ID = ' . (int) $data['ID'];

        try {
            $connection = \Bitrix\Main\Application::getConnection();
            $connection->queryExecute($sql);
        } catch (Exception $e) {
            $result->addError(new Main\Entity\FieldError(
              $event->getEntity()->getField('ID'), SmartseoNoindexConditionTable::getTableName() . ' SEO_TEXT_ID [' . $data['ID'] . '] - ' . $e->getMessage()
            ));
        }
    }

    private static function cascadeDeleteUrls(Main\Entity\Event $event, Main\Entity\EventResult &$result)
    {
        $data = $event->getParameter('primary');

        if (!$data['ID'] || !intval($data['ID'])) {
            return;
        }

        $sql = 'DELETE FROM ' . SmartseoNoindexUrlTable::getTableName()
          . ' WHERE NOINDEX_RULE_ID = ' . (int) $data['ID'];

        try {
            $connection = \Bitrix\Main\Application::getConnection();
            $connection->queryExecute($sql);
        } catch (Exception $e) {
            $result->addError(new Main\Entity\FieldError(
              $event->getEntity()->getField('ID'), SmartseoNoindexUrlTable::getTableName() . ' SEO_TEXT_ID [' . $data['ID'] . '] - ' . $e->getMessage()
            ));
        }
    }
}