<?php

namespace Aspro\Max\Smartseo\Engines;

use Aspro\Max\Smartseo;

class UrlEngine extends \Aspro\Max\Smartseo\Engines\Engine
{

    private $filterConditionId = null;
    private $filterCondition = null;
    private $filterRule = null;

    function __construct($filterConditionId)
    {
        $this->filterConditionId = $filterConditionId;
        $this->loadData();
    }

    public function update()
    {
        $generatedUrls = $this->getGeneratedUrls();

        if (!$generatedUrls) {
            Smartseo\Models\SmartseoFilterConditionUrlTable::deleteAllUrls($this->filterConditionId);

            $this->setResult([
                'FILTER_CONDITION_ID' => $this->filterConditionId,
                'COUNT' => 0,
            ]);

            return true;
        }

        Smartseo\Models\SmartseoFilterConditionUrlTable::deleteNotModifiedUrls($this->filterConditionId);

        $modifiedPairHashUrls = Smartseo\Models\SmartseoFilterConditionUrlTable::getModifiedPairHashUrls($this->filterConditionId);

        $urlCollection = new Smartseo\Entity\FilterConditionUrls();

        $count = 0;
        foreach ($generatedUrls as $item) {
            $count++;

            $_hash = md5($item['URL_SMART_FILTER']);

            if (array_key_exists($_hash, $modifiedPairHashUrls)) {
                unset($modifiedPairHashUrls[$_hash]);
                continue;
            }

            $url = new Smartseo\Entity\FilterConditionUrl();

            $url->setFilterConditionId($this->filterConditionId);
            $url->setActive('Y');
            $url->setRealUrl($item['URL_SMART_FILTER']);
            $url->setHash($_hash);
            $url->setNewUrl($item['URL_PAGE']);
            $url->setIblockId($item['PARAMS']['IBLOCK']['ID']);
            $url->setSectionId($item['PARAMS']['SECTION']['ID']);
            $url->setProperties(serialize($item['PARAMS']['PROPERTIES']));

            $urlCollection[] = $url;
        }

        $resultSave = $urlCollection->save(true);

        $this->setResult([
            'FILTER_CONDITION_ID' => $this->filterConditionId,
            'COUNT' => $count,
        ]);

        if (!$resultSave->isSuccess()) {
            $this->addError($resultSave->getErrorMessages());

            return false;
        }

        if($modifiedPairHashUrls) {
            Smartseo\Models\SmartseoFilterConditionUrlTable::deleteModifiedUrls($this->filterConditionId, array_values($modifiedPairHashUrls));
        }

        $this->onAfterUpdate();

        return true;
    }

    protected function getGeneratedUrls()
    {
        if ($this->hasErrors()) {
            return false;
        }

        $setting = Smartseo\Admin\Settings\SettingSmartseo::getInstance();

        // Create url handlers

        $iblockUrlHandler = new Smartseo\Generator\Handlers\IblockUrlHandler($this->filterRule['IBLOCK_ID']);

        $siteUrlHandler = new Smartseo\Generator\Handlers\SiteUrlHandler($this->filterRule['SITE_ID']);

        $sectionUrlHandler = new Smartseo\Generator\Handlers\SectionUrlHandler(
          $this->filterRule['IBLOCK_ID'], $this->filterRule['IBLOCK_SECTIONS']
        );

        $propertyUrlHandler = new Smartseo\Generator\Handlers\PropertyUrlHandler(
          $this->filterRule['IBLOCK_ID'],
          $this->filterCondition['CONDITION_TREE'],
          $this->filterCondition['URL_TYPE_GENERATE'],
          $this->filterRule['IBLOCK_INCLUDE_SUBSECTIONS'] == 'Y'
        );
        $propertyUrlHandler->setSettings($setting->getSite($this->filterRule['SITE_ID'])->getParametersGeneratingUrl());

        // Init generate class

        $urlGenerator = new Smartseo\Generator\UrlGenerator();

        $urlGenerator->setPageUrlTemplate($this->filterCondition['URL_TEMPLATE']);

        $urlGenerator->setSmartFilterUrlTemplate(
          $setting->site($this->filterRule['SITE_ID'])->getUrlSmartFilterTemplate()
        );

        $urlGenerator->setSectionUrlTemplate(
          $setting->site($this->filterRule['SITE_ID'])->getUrlSection()
        );

        // Add url handlers to generate class

        $urlGenerator->addHandler($iblockUrlHandler);
        $urlGenerator->addHandler($siteUrlHandler);
        $urlGenerator->addHandler($sectionUrlHandler);
        $urlGenerator->addHandler($propertyUrlHandler);

        try {
            return $urlGenerator->generate();
        } catch (Exception $e) {
            $this->addError($e->getMessage());

            return null;
        }

        return true;
    }

    protected function loadData()
    {
        $filterCondition = Smartseo\Models\SmartseoFilterConditionTable::getRow([
              'select' => [
                  'FILTER_RULE_ID',
                  'URL_STRICT_COMPLIANCE',
                  'URL_TEMPLATE',
                  'URL_TYPE_GENERATE',
                  'CONDITION_TREE',
                  'ACTIVE',
              ],
              'filter' => [
                  '=ID' => $this->filterConditionId
              ]
        ]);

        if (!$filterCondition) {
            $this->addError('Filter condition element not found');

            return false;
        }

        $filterRule = Smartseo\Models\SmartseoFilterRuleTable::getRow([
              'select' => [
                  'ID',
                  'SITE_ID',
                  'IBLOCK_ID',
                  'IBLOCK_SECTIONS',
                  'IBLOCK_INCLUDE_SUBSECTIONS',
                  'ACTIVE'
              ],
              'filter' => [
                  'ID' => $filterCondition['FILTER_RULE_ID'],
              ]
        ]);

        if (!$filterRule) {
            $this->addError('Filter rule element not found');

            return false;
        }

        $iblockSectionRows = Smartseo\Models\SmartseoFilterIblockSectionsTable::getList([
              'select' => [
                  'SECTION_ID',
              ],
              'filter' => [
                  'FILTER_RULE_ID' => $filterRule['ID']
              ]
          ])->fetchAll();

        foreach ($iblockSectionRows as $section) {
            $filterRule['IBLOCK_SECTIONS'][] = $section['SECTION_ID'];
        }

        $this->filterCondition = $filterCondition;
        $this->filterRule = $filterRule;
    }

    protected function onAfterUpdate()
    {
        $events = \Bitrix\Main\EventManager::getInstance()->findEventHandlers(
          Smartseo\General\Smartseo::MODULE_ID, 'onAfterUpdateUrlEngine'
        );

        $result = $this->getResult();

        foreach ($events as $event) {
			ExecuteModuleEventEx($event, [$result]);
		}
    }

}
