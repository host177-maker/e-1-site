<?php

namespace Aspro\Max\Smartseo\Admin\Traits;

use \Aspro\Max\Smartseo\Entity\FilterSection,
    \Aspro\Max\Smartseo\Models\SmartseoFilterSectionTable;

trait FilterChainSectionTree
{

    protected function getChainSections($sectionId)
    {
        $section = FilterSection::wakeUp($sectionId);

        if (!$section) {
            return [];
        }

        $section->fill(['NAME', 'PARENT_ID', 'DEPTH_LEVEL']);

        if (!$section->getId()) {
            return [];
        }

        $result = [
            [
                'ID' => $section->getId(),
                'NAME' => $section->getName(),
                'DEPTH_LEVEL' => $section->getDepthLevel(),
            ],
        ];

        $this->chainSection($section->getParentId(), $result);

        krsort($result);

        return $result;
    }

    protected function chainSection($sectionId, &$result)
    {
        if (!$sectionId) {
            return [];
        }

        $section = FilterSection::wakeUp($sectionId);

        if (!$section) {
            return;
        }

        $section->fill(['NAME', 'PARENT_ID', 'DEPTH_LEVEL']);

        $result[] = [
            'ID' => $section->getId(),
            'NAME' => $section->getName(),
            'DEPTH_LEVEL' => $section->getDepthLevel(),
        ];

        if (!$section->getParentId()) {
            return;
        }

        $this->chainSection($section['PARENT_ID'], $result);
    }

    protected function getTreeSections($depthLevel = 1, $parentId = 0)
    {
        $sections = SmartseoFilterSectionTable::getList([
              'select' => ['ID', 'NAME', 'PARENT_ID', 'DEPTH_LEVEL'],
              'order' => ['DEPTH_LEVEL' => 'ASC', 'SORT' => 'DESC', 'NAME' => 'ASC'],
              'filter' => array_filter([
                  '>=DEPTH_LEVEL' => $depthLevel,
              ]),
              'cache' => [
                  'ttl' => SmartseoFilterSectionTable::getCacheTtl(),
              ]
          ])->fetchAll();

        $link = [];
        $link[$parentId] = &$treeSections;

        foreach ($sections as $section) {
            $link[intval($section['PARENT_ID'])]['CHILD'][$section['ID']] = $section;
            $link[$section['ID']] = &$link[intval($section['PARENT_ID'])]['CHILD'][$section['ID']];
        }

        return $treeSections['CHILD'];
    }

    protected function getRowsByTreeSections($treeSections)
    {
        $result = [];

        foreach ($treeSections as $treeSection) {
            $_treeSection = $treeSection;
            unset($_treeSection['CHILD']);
            $result[] = $_treeSection;

            if ($treeSection['CHILD']) {
                foreach ($this->getRowsByTreeSections($treeSection['CHILD']) as $section) {
                    $result[] = $section;
                }
            }
        }

        return $result;
    }

}
