<?php

namespace Aspro\Max\Smartseo\Generator\Handlers;

class SiteUrlHandler extends AbstractUrlHandler
{

    protected $siteLid = null;
    protected $site = null;

    public function __construct($siteLid)
    {
        $this->siteLid = $siteLid;
    }

    public function getReplacements()
    {
        return [
            '#SITE_ID#' => 'LID',
            '#SITE_DIR#' => 'DIR',
            '#SITE_SERVER_NAME#' => 'SERVER_NAME',
        ];
    }

    public function getTokens()
    {
        return array_keys($this->getReplacements());
    }

    public function validateInitialParams()
    {
        if (!$this->siteLid) {
            $this->addError('SiteUrlHandler: Requered params LID not value or not found');

            return false;
        }

        return true;
    }

    public function generateResult(&$results)
    {
        $site = $this->getSite();

        $patterns = array_map(function($token) {
            return '/' . $token . '/';
        }, $this->getTokens());

        $replacements = array_map(function($field) use ($site) {
            return $site[$field];
        }, $this->getReplacements());

        $newResult = [];
        $i = 0;
        foreach ($results as $result) {
            $newResult[$i]['URL_PAGE'] = preg_replace($patterns, $replacements, $result['URL_PAGE']);
            $newResult[$i]['URL_SMART_FILTER'] = preg_replace($patterns, $replacements, $result['URL_SMART_FILTER']);
            $newResult[$i]['URL_SECTION'] = preg_replace($patterns, $replacements, $result['URL_SECTION']);

            $newResult[$i]['PARAMS']['SITE'] = $site;

            $i++;
        }

        $results = $newResult;
    }

    protected function getSite()
    {
        if ($this->site) {
            return $this->site;
        }

        $this->site = \Bitrix\Main\SiteTable::getRow([
              'select' => [
                  'LID',
                  'DIR',
                  'SERVER_NAME',
              ],
              'filter' => [
                  'LID' => $this->siteLid,
              ],
        ]);

        return $this->site;
    }

}
