<?php

namespace Aspro\Max\Smartseo\Generator\Handlers;

class IblockUrlHandler extends AbstractUrlHandler
{

    protected $iblockId = null;
    protected $iblock = null;

    public function __construct($iblockId)
    {
        $this->iblockId = $iblockId;
    }

    public function getReplacements()
    {
        return [
            '#IBLOCK_LIST_PAGE_URL#' => 'LIST_PAGE_URL',
            '#IBLOCK_SECTION_PAGE_URL#' => 'SECTION_PAGE_URL',
            '#IBLOCK_CODE#' => 'CODE',
            '#IBLOCK_ID#' => 'ID',
            '#IBLOCK_EXTERNAL_ID#' => 'XML_ID',
        ];
    }

    public function getTokens()
    {
        return array_keys($this->getReplacements());
    }

    public function validateInitialParams()
    {
        if (!$this->iblockId) {
            $this->addError('IblockUrlHandler: Requered params IBLOCK_ID not value or not found');

            return false;
        }

        return true;
    }

    public function generateResult(&$results)
    {
        $iblock = $this->getIblock();

        $patterns = array_map(function($token) {
            return '/' . $token . '/';
        }, $this->getTokens());

        $replacements = array_map(function($field) use ($iblock) {
            return $iblock[$field];
        }, $this->getReplacements());

        $newResult = [];
        $i = 0;
        foreach ($results as $result) {
            $newResult[$i]['URL_PAGE'] = preg_replace($patterns, $replacements, $result['URL_PAGE']);
            $newResult[$i]['URL_SMART_FILTER'] = preg_replace($patterns, $replacements, $result['URL_SMART_FILTER']);
            $newResult[$i]['URL_SECTION'] = preg_replace($patterns, $replacements, $result['URL_SECTION']);

            $newResult[$i]['PARAMS']['IBLOCK'] = $iblock;

            $i++;
        }

        $results = $newResult;
    }

    protected function getIblock()
    {
        if ($this->iblock) {
            return $this->iblock;
        }

        $this->iblock = \Bitrix\Iblock\IblockTable::getRow([
            'select' => [
                'LIST_PAGE_URL',
                'SECTION_PAGE_URL',
                'CODE',
                'XML_ID',
                'ID',
            ],
            'filter' => [
                'ID' => $this->iblockId,
            ],
        ]);

        return $this->iblock;
    }

}
