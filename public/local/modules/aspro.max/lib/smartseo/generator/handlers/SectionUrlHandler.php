<?php

namespace Aspro\Max\Smartseo\Generator\Handlers;

class SectionUrlHandler extends AbstractUrlHandler
{
    const DEFAULT_CACHE_TTL = 3600;

    protected $sectionIds = [];
    protected $isGetSubsection = false;
    protected $iblockId = null;

    private $sections = [];

    public function __construct($iblockId, $sectionIds, $isGetSubsection = false)
    {
        $this->setIblockId($iblockId);
        $this->setSectionIds($sectionIds);
        $this->isGetSubsection = $isGetSubsection;
    }

    public function setSectionIds(array $values)
    {
        $this->sectionIds = $values;
    }

    public function setIblockId($value)
    {
        $this->iblockId = $value;
    }

    public function getReplacements()
    {
        return [
            '#SECTION_ID#' => 'ID',
            '#SECTION_CODE#' => 'CODE',
            '#SECTION_CODE_PATH#' => 'CODE_PATH',
            '#ID#' => 'ID',
            '#CODE#' => 'CODE',

        ];
    }

    public function getTokens()
    {
        return array_keys($this->getReplacements());
    }

    public function validateInitialParams()
    {
        if (!$this->sectionIds) {
            $this->addError('SectionUrlHandler: Requered params SECTION IDS not value or not found');

            return false;
        }

        if(!$this->iblockId) {
            $this->addError('SectionUrlHandler: Requered params IBLOCK ID not value or not found');

            return false;
        }

        return true;
    }

    public function generateResult(&$results)
    {
        $sections = $this->getSections();

        $patterns = array_map(function($token){
            return '/' . $token . '/';
        }, $this->getTokens());

        $newResult = [];
        $i = 0;
        foreach ($sections as $section) {
            $replacements = array_map(function($field) use ($section) {
                return $section[$field];
            }, $this->getReplacements());

            foreach ($results as $result) {
                if (!$result['PARAMS']['SITE']) {
                    continue;
                }

                $newResult[$i]['URL_PAGE'] = preg_replace($patterns, $replacements, $result['URL_PAGE']);
                $newResult[$i]['URL_SMART_FILTER'] = preg_replace($patterns, $replacements, $result['URL_SMART_FILTER']);
                $newResult[$i]['URL_SECTION'] = preg_replace($patterns, $replacements, $result['URL_SECTION']);

                $newResult[$i]['PARAMS']['SECTION'] = $section;
                $newResult[$i]['PARAMS']['IBLOCK'] = [
                    'ID' => $this->iblockId,
                ];

                $i++;
            }
        }

        $results = $newResult;
    }

    protected function getSections()
    {
        if($this->sections) {
            return $this->sections;
        }

        $rows = \Bitrix\Iblock\SectionTable::getList([
            'select' => [
                'ID',
                'NAME',
                'CODE',
                'LEFT_MARGIN',
                'RIGHT_MARGIN',
                'DEPTH_LEVEL',
            ],
            'filter' => [
                'ID' => $this->sectionIds,
                'IBLOCK_ID' => $this->iblockId,
            ],
            'order' => [
                'DEPTH_LEVEL' => 'ASC'
            ]
        ])->fetchAll();

        $sections = [];
        $subSectionMargins = [];

        if($this->isGetSubsection) {
            foreach ($rows as $row) {
                $subSectionMargins[] = [
                    'LEFT_MARGIN' => $row['LEFT_MARGIN'],
                    'RIGHT_MARGIN' => $row['RIGHT_MARGIN'],
                ];
            }
            if($this->isGetSubsection && $subSectionMargins) {
                $sections = $this->getAllLevelsSections($subSectionMargins);
            }
        } else {
           $sections = $rows;
        }

        foreach ($sections as $section) {
            $result[] = [
                'ID' => $section['ID'],
                'CODE' => $section['CODE'],
                'NAME' => $section['NAME'],
                'CODE_PATH' => $this->getSectionPathByMargin($section['LEFT_MARGIN'], $section['RIGHT_MARGIN']),
                'LEFT_MARGIN' => $section['LEFT_MARGIN'],
                'RIGHT_MARGIN' => $section['RIGHT_MARGIN'],
            ];
        }

        $this->sections = $result;

        return $this->sections;
    }

    protected function getAllLevelsSections(array $subSectionMargins)
    {
        $query = \Bitrix\Iblock\SectionTable::query();

        $query->setSelect([
            'ID',
            'NAME',
            'CODE',
            'LEFT_MARGIN',
            'RIGHT_MARGIN',
            'DEPTH_LEVEL',
        ]);

        $query->setFilter([
            'IBLOCK_ID' => $this->iblockId,
        ]);

        $query->setOrder([
            'LEFT_MARGIN' => 'ASC',
            'DEPTH_LEVEL' => 'ASC'
        ]);

        $querySubsections = \Bitrix\Main\Entity\Query::filter();

        $querySubsections->logic('or');

        foreach ($subSectionMargins as $margin) {
            $querySubsections->where(
              \Bitrix\Main\Entity\Query::filter()->where([
                  ['LEFT_MARGIN', '>=', $margin['LEFT_MARGIN']],
                  ['RIGHT_MARGIN', '<=', $margin['RIGHT_MARGIN']],
              ])
            );
        }

        $query->where($querySubsections);

        $query->setCacheTtl(self::DEFAULT_CACHE_TTL);

        return $query->exec()->fetchAll();
    }

    protected function getSectionPathByMargin($leftMargin, $rightMargin)
    {
        $rows = \Bitrix\Iblock\SectionTable::getList([
              'select' => [
                  'CODE',
              ],
              'filter' => [
                  '<=LEFT_MARGIN' => $leftMargin,
                  '>=RIGHT_MARGIN' => $rightMargin,
                  'IBLOCK_ID' => $this->iblockId,
              ],
              'order' => [
                  'DEPTH_LEVEL' => 'ASC',
              ]
          ])->fetchAll();

        return implode('/', array_column($rows, 'CODE'));
    }

}
