<?php

namespace Aspro\Max\Smartseo\Engines;

use Aspro\Max\Smartseo,
  Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class SitemapEngine extends \Aspro\Max\Smartseo\Engines\Engine
{
    private $sitemapId = null;
    private $sitemap = null;
    private $sitemapIndexUrl = null;
    private $type = null;

    function __construct($sitemapId, $type = null)
    {
        $this->sitemapId = $sitemapId;
        $this->type = $type;
        $this->loadSitemapData();
    }

    public function update()
    {
        $filterConditionIds = $this->getRelatedFilterConditionIds();

        if ($this->hasErrors()) {
            return false;
        }

        $pages = [];
        foreach ($filterConditionIds as $filterConditionId) {
            $_pages = $this->getPagesByFilterConditionId($filterConditionId);
            $pages = array_merge($pages, $_pages);
        }

        $sitemap = null;

        if($pages) {
            $sitemapFile = $this->updateSitemapFile($pages);
        }

        $result = $this->getResult();

        if(!$result) {
            $this->addError(Loc::getMessage('SMARTSEO_ENGINE_SITEMAP_ERROR_NOT_URL'));
            
            return false;
        }

        $this->removeEntryIndexSitemap($this->getSitemapLastUrl());
        $this->deleteRuleRobotsFile($this->getSitemapLastUrl());

        $this->appendEntryIndexSitemap($sitemapFile);

        if($this->getSitemapFile() != $this->getSitemapLastFile()) {
            $this->deleteSitemapFile($this->getSitemapLastFile());
        }

        if($this->isAppendRobots()) {
            if($this->getSitemapIndexUrl()) {
                $this->addRuleRobotsFile($this->getSitemapIndexUrl());
                $this->deleteRuleRobotsFile($result['FILE_URL']);
            } else {
                $this->addRuleRobotsFile($result['FILE_URL']);
            }
        } else {
            $this->deleteRuleRobotsFile($result['FILE_URL']);
        }

        $this->updateSitemap($result['FILE_URL']);

        return true;
    }

    public function deleteSitemap()
    {
        $this->deleteSitemapFile($this->getSitemapLastFile());
        $this->removeEntryIndexSitemap($this->getSitemapLastUrl());
        $this->deleteRuleRobotsFile($this->getSitemapLastUrl());
    }

    public static function fullUpdateSitemapIndex($siteId)
    {
        $sitemaps = self::getSitemaps([
            'SITE_ID' => $siteId,
            'IN_INDEX_SITEMAP' => 'Y',
            'UPDATE_SITEMAP_INDEX' => 'Y',
        ]);

        if(!$sitemaps) {
            return;
        }

        $sitemapFile = null;
        foreach ($sitemaps as $sitemap) {
            
            $sitemapFile = new Smartseo\Seo\SitemapFile($sitemap['SITEMAP_FILE'], [
                'SITE_ID' => $sitemap['SITE_ID'],
                'PROTOCOL' => $sitemap['PROTOCOL'],
                'DOMAIN' => $sitemap['DOMAIN'],
            ]);

            if(!$sitemapFile->isExists()) {
                continue;
            }
            
            $sitemapIndexFile = new Smartseo\Seo\SitemapIndex($sitemap['INDEX_SITEMAP_FILE'], [
                'SITE_ID' => $sitemap['SITE_ID'],
                'PROTOCOL' => $sitemap['PROTOCOL'],
                'DOMAIN' => $sitemap['DOMAIN'],
            ]);

            $sitemapIndexFile->appendIndexEntry($sitemapFile);
        }
    }

    public static function getSitemaps(array $filter = [])
    {
        return Smartseo\Models\SmartseoSitemapTable::getList([
            'select' => [
                '*'
            ],
            'filter' => $filter
        ])->fetchAll();
    }

    protected function getRelatedFilterConditionIds()
    {
        if ($this->hasErrors()) {
            return [];
        }

        $result = Smartseo\Models\SmartseoFilterSitemapTable::getList([
              'select' => [
                  'FILTER_CONDITION_ID',
              ],
              'filter' => [
                  'SITEMAP_ID' => $this->sitemapId,
                  'ACTIVE' => 'Y'
              ]
          ])->fetchAll();

        if (!$result) {
            $this->addError(Loc::getMessage('SMARTSEO_ENGINE_SITEMAP_ERROR_NOT_CONDITIONS'));

            return false;
        }

        return array_column($result, 'FILTER_CONDITION_ID');
    }

    protected function getPagesByFilterConditionId($filterConditionId)
    {
        $rows = Smartseo\Models\SmartseoFilterSitemapTable::getList([
              'select' => [
                  'URL' => 'FILTER_CONDITION.FILTER_CONDITION_URL.NEW_URL',
                  'CHANGEFREQ',
                  'PRIORITY',
                  'DATE_CHANGE' => 'FILTER_CONDITION.DATE_CHANGE'
              ],
              'filter' => [
                  'FILTER_CONDITION_ID' => $filterConditionId,
                  '!==FILTER_CONDITION.FILTER_CONDITION_URL.NEW_URL' => null,
                  'FILTER_CONDITION.ACTIVE' => 'Y',
                  'FILTER_CONDITION.FILTER_RULE.ACTIVE' => 'Y',
                  'ACTIVE' => 'Y',
              ]
          ])->fetchAll();

        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'URL' => $row['URL'],
                'CHANGEFREQ' => $row['CHANGEFREQ'],
                'PRIORITY' => $row['PRIORITY'],
                'DATE_CHANGE' => $row['DATE_CHANGE'],
            ];
        }

        return array_filter($result);
    }

    protected function appendEntryIndexSitemap($sitemapFile)
    {
        $result = $this->getResult();

        if(!$result['FILE_URL']) {
            return false;
        }
;
        if($this->isAppendSitemapIndex() && $this->getSitemapIndexFile()) {
            $sitemapIndexFile = new Smartseo\Seo\SitemapIndex($this->getSitemapIndexFile(), [
               'SITE_ID' => $this->sitemap['SITE_ID'],
               'PROTOCOL' => $this->sitemap['PROTOCOL'],
               'DOMAIN' => $this->sitemap['DOMAIN'],
            ]);

            if(!$sitemapIndexFile->validateFileSitemapIndex()) {
                $this->addError(Loc::getMessage('SMARTSEO_ENGINE_SITEMAP_ERROR_NOT_SITEMAPINDEX'));

                return false;
            }
            
            $sitemapIndexFile->appendIndexEntry($sitemapFile);

            $this->sitemapIndexUrl = $sitemapIndexFile->getSitemapUrl();
        }
    }

    protected function removeEntryIndexSitemap($url)
    {
        if($this->getSitemapIndexFile()) {
            $sitemapIndexFile = new Smartseo\Seo\SitemapIndex($this->getSitemapIndexFile(), [
               'SITE_ID' => $this->sitemap['SITE_ID'],
               'PROTOCOL' => $this->sitemap['PROTOCOL'],
               'DOMAIN' => $this->sitemap['DOMAIN'],
            ]);

            $sitemapIndexFile->removeEntryByUrl($url);
        }
    }

    protected function updateSitemapFile(array $pages)
    {
        $sitemapFile = new Smartseo\Seo\SitemapFile($this->getSitemapFile(), [
            'SITE_ID' => $this->sitemap['SITE_ID'],
            'PROTOCOL' => $this->sitemap['PROTOCOL'],
            'DOMAIN' => $this->sitemap['DOMAIN'],
        ]);

        if ($sitemapFile->isExists()) {
            $sitemapFile->delete();
        }

        $sitemapFile->addHeader();

        foreach ($pages as $page) {
            $dateChange = new \Bitrix\Main\Type\DateTime;

            if($page['DATE_CHANGE'] && $page['DATE_CHANGE'] instanceof \Bitrix\Main\Type\DateTime) {
                $dateChange = $page['DATE_CHANGE'];
            }

            $sitemapFile->addEntry([
                'XML_LOC' => $this->sitemap['PROTOCOL'] . $this->sitemap['DOMAIN'] . $page['URL'],
                'XML_LASTMOD' => $dateChange->format('c'),
                'XML_CHANGEFREQ' => $page['CHANGEFREQ'],
                'XML_PRIORITY' => $page['PRIORITY'],
            ]);
        }

        $sitemapFile->addFooter();

        $this->setResult([
            'FILE_URL' => $sitemapFile->getUrl(),
            'FILE_PATH' => $sitemapFile->getFilePath()
        ]);

        return $sitemapFile;
    }

    protected function deleteSitemapFile($filePath)
    {
        $sitemapFile = new Smartseo\Seo\SitemapFile($filePath, [
            'SITE_ID' => $this->sitemap['SITE_ID'],
        ]);

        if ($sitemapFile->isExists()) {
            $sitemapFile->delete();
        }
    }

    protected function addRuleRobotsFile($url)
    {
        $robotsFile = new Smartseo\Seo\RobotsFile($this->sitemap['SITE_ID']);
        $robotsFile->addRule([
            \Bitrix\Seo\RobotsFile::SITEMAP_RULE, $url
        ]);
    }

    protected function deleteRuleRobotsFile($url)
    {
        $robotsFile = new Smartseo\Seo\RobotsFile($this->sitemap['SITE_ID']);
        $robotsFile->deleteRule([
            \Bitrix\Seo\RobotsFile::SITEMAP_RULE, $url
        ]);
    }

    protected function updateSitemap($url)
    {
        $sitemap = \Aspro\Max\Smartseo\Models\EO_SmartseoSitemap::wakeUp($this->sitemap['ID']);
        $sitemap->setSitemapUrl($url);
        $sitemap->setSitemapLastUrl($url);
        $sitemap->setSitemapLastFile($this->getSitemapFile());
        $sitemap->setDateLastLaunch(new \Bitrix\Main\Type\DateTime());

        $result = $sitemap->save();

        if (!$result->isSuccess()) {
            throw new \Exception(implode('<br>', $result->getErrorMessages()));
        }

        $sitemapId = $result->getId();

        return $sitemapId;
    }

    protected function loadSitemapData()
    {
        $result = Smartseo\Models\SmartseoSitemapTable::getRowById($this->sitemapId);

        if (!$result) {
            $this->addError('Sitemap not found');

            return false;
        }

        $this->sitemap = $result;
    }

    protected function getSitemapFile()
    {
        return $this->sitemap['SITEMAP_FILE'];
    }

    protected function getSitemapLastFile()
    {
        return $this->sitemap['SITEMAP_LAST_FILE'];
    }

    protected function getSitemapLastUrl()
    {
        return $this->sitemap['SITEMAP_LAST_URL'];
    }

    protected function isAppendSitemapIndex()
    {
        return $this->sitemap['IN_INDEX_SITEMAP'] == 'Y';
    }

    protected function isAppendRobots()
    {
        return $this->sitemap['IN_ROBOTS'] == 'Y';
    }

    protected function getSitemapIndexFile()
    {
        return $this->sitemap['INDEX_SITEMAP_FILE'];
    }

    protected function getSitemapIndexUrl()
    {
        return $this->sitemapIndexUrl;
    }

}
