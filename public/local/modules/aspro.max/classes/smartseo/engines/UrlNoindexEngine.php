<?php

namespace Aspro\Max\Smartseo\Engines;

use Aspro\Max\Smartseo;

class UrlNoindexEngine extends \Aspro\Max\Smartseo\Engines\Engine
{

    private $noindexRuleId = null;
    private $noindexRule = null;

    function __construct($noindexRuleId)
    {
        $this->noindexRuleId = $noindexRuleId;
        $this->loadData();
    }

    public function update()
    {
        $urls = $this->generateUrls();

        Smartseo\Models\SmartseoNoindexUrlTable::deleteAllUrls($this->noindexRuleId);

        $noindexUrlCollection = new \Aspro\Max\Smartseo\Models\EO_SmartseoNoindexUrl_Collection();

        $count = 0;
        foreach ($urls as $item) {
            $count++;

            $url = new \Aspro\Max\Smartseo\Models\EO_SmartseoNoindexUrl();

            $url->setNoindexRuleId($this->noindexRuleId);
            $url->setUrl(preg_replace('|([/]+)|s', '/', $item['URL_SECTION']));
            $url->setIblockId($item['PARAMS']['IBLOCK']['ID']);
            $url->setSectionId($item['PARAMS']['SECTION']['ID']);

            $noindexUrlCollection[] = $url;
        }

        $resultSave = $noindexUrlCollection->save(true);

        $this->setResult([
            'COUNT' => $count,
        ]);

        if (!$resultSave->isSuccess()) {
            $this->addError($resultSave->getErrorMessages());

            return false;
        }

        $this->onAfterUpdate();

        return true;
    }

    protected function generateUrls()
    {
        if ($this->hasErrors()) {
            return false;
        }

        $setting = Smartseo\Admin\Settings\SettingSmartseo::getInstance();

        // Create url handlers

        $iblockUrlHandler = new Smartseo\Generator\Handlers\IblockUrlHandler($this->noindexRule['IBLOCK_ID']);

        $siteUrlHandler = new Smartseo\Generator\Handlers\SiteUrlHandler($this->noindexRule['SITE_ID']);

        $sectionUrlHandler = new Smartseo\Generator\Handlers\SectionUrlHandler(
          $this->noindexRule['IBLOCK_ID'], $this->noindexRule['IBLOCK_SECTIONS']
        );

        // Init generate class

        $urlGenerator = new Smartseo\Generator\UrlGenerator();

        $urlGenerator->setPageUrlTemplate($this->noindexRule['URL_TEMPLATE']);

        $urlGenerator->setSectionUrlTemplate(
          $this->noindexRule['URL_TEMPLATE']
        );

        // Add url handlers to generate class

        $urlGenerator->addHandler($iblockUrlHandler);
        $urlGenerator->addHandler($siteUrlHandler);
        $urlGenerator->addHandler($sectionUrlHandler);

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
        $noindexRule = Smartseo\Models\SmartseoNoindexRuleTable::getRow([
              'select' => [
                  'ID',
                  'SITE_ID',
                  'IBLOCK_ID',
                  'IBLOCK_SECTIONS',
                  'IBLOCK_SECTION_ALL',
                  'ACTIVE',
                  'URL_TEMPLATE'
              ],
              'filter' => [
                  'ID' => $this->noindexRuleId,
              ]
        ]);

        if (!$noindexRule) {
            $this->addError('Noindex rule element not found');

            return false;
        }

        if($noindexRule['IBLOCK_SECTION_ALL'] === 'Y') {
            $iblockSectionRows = \Bitrix\Iblock\SectionTable::getList([
                'select' => [
                    'SECTION_ID' => 'ID',
                ],
                'filter' => [
                    'IBLOCK_ID' => $noindexRule['IBLOCK_ID'],
                ],
                'order' => [
                    'DEPTH_LEVEL' => 'ASC',
                ]
            ])->fetchAll();
        } else {
            $iblockSectionRows = Smartseo\Models\SmartseoNoindexIblockSectionsTable::getList([
                  'select' => [
                      'SECTION_ID',
                  ],
                  'filter' => [
                      'NOINDEX_RULE_ID' => $noindexRule['ID']
                  ]
              ])->fetchAll();
        }

        foreach ($iblockSectionRows as $section) {
            $noindexRule['IBLOCK_SECTIONS'][] = $section['SECTION_ID'];
        }

        $this->noindexRule = $noindexRule;
    }

    protected function onAfterUpdate()
    {
        $events = \Bitrix\Main\EventManager::getInstance()->findEventHandlers(
          Smartseo\General\Smartseo::MODULE_ID, 'onAfterUpdateUrlNoindexEngine'
        );

        $result = $this->getResult();

        foreach ($events as $event) {
			ExecuteModuleEventEx($event, [$result]);
		}
    }

}
