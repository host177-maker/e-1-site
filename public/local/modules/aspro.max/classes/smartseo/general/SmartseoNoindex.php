<?php

namespace Aspro\Max\Smartseo\General;

use Aspro\Max\Smartseo;

class SmartseoNoindex
{

    static public function isDisallow($originUrl, $siteId)
    {
        $pageUrl = self::getSectionPageUrl($originUrl, $siteId);

        $noindexRules = Smartseo\Models\SmartseoNoindexRuleTable::findByUrl($pageUrl, $siteId);

        foreach ($noindexRules as $noindexRule) {
            if(self::checkDisallow($originUrl, $noindexRule)) {
                return true;
            }
        }

        return false;
    }

    static private function getSectionPageUrl($originUrl, $siteId)
    {
        $filterUrlPattern = self::getSmartfilterUrlPattern($siteId);

        $url = preg_replace(['|([/]+)|s', $filterUrlPattern, '|\/\?.*|'], '/', $originUrl);

        return $url;
    }

    static private function getSmartfilterUrlPattern($siteId)
    {
        $urlTemplate = \Aspro\Max\Smartseo\General\Smartseo::getSettingObject()->site($siteId)->getUrlSmartFilterTemplate(false);

        $urlTemplate = str_replace([
            '#SECTION_CODE_PATH#',
            '#SECTION_ID#',
            '#SECTION_CODE#'
        ], '', $urlTemplate);

        $urlTemplate = str_replace([
            '#SMART_FILTER_PATH#',
        ], '(.*)', $urlTemplate);

        return '#' . $urlTemplate . '#';
    }

    static private function checkDisallow($originUrl, $noindexRule)
    {
        foreach ($noindexRule['CONDITIONS'] as $condition) {
            switch ($condition['TYPE']) {
                case Smartseo\Models\SmartseoNoindexConditionTable::TYPE_EXCEPTION_BY_COUNT :
                    if(self::checkExceptionByCount($originUrl, $noindexRule['URL'], $condition['VALUE'])) {
                       return true;
                    }

                    break;
                case Smartseo\Models\SmartseoNoindexConditionTable::TYPE_EXCEPTION_BY_VALUES :
                    if(self::checkExceptionByValues($originUrl, $noindexRule['URL'], $condition['VALUE'])) {
                        return true;
                    }

                    break;

                case Smartseo\Models\SmartseoNoindexConditionTable::TYPE_EXCEPTION_BY_PROPERTIES :
                    if(self::checkExceptionByProperty($originUrl, $noindexRule['URL'], $condition['PROPERTIES'])) {
                        return true;
                    }

                    break;

                default:
                    break;
            }
        }

        return false;
    }

    static private function checkExceptionByCount($originUrl, $pageUrl, $maxCount)
    {
        preg_match_all('#(' . $pageUrl . ')|([\w\d%-]+-(is|from|to))#', $originUrl, $matches);

        $sectionUrl = array_filter($matches[1]);
        $filterProperties = array_filter($matches[2]);

        if($sectionUrl && count($filterProperties) > $maxCount) {
            return true;
        }

        return false;
    }

    static private function checkExceptionByValues($originUrl, $pageUrl, $maxCount)
    {
        preg_match_all('#(' . $pageUrl . ')|([\w\d]+-is-([\w\d%-]+))#', $originUrl, $matches);

        $sectionUrl = array_filter($matches[1]);
        $filterProperties = array_filter($matches[2]);
        $filterValues = array_filter($matches[3]);

        if(!$sectionUrl || !$filterProperties) {
            return false;
        }

        foreach ($filterValues as $value) {
            $_values = explode('-or-', $value);

            if(count($_values) > $maxCount) {
                return true;
            }
        }

        return false;
    }

    static private function checkExceptionByProperty($originUrl, $pageUrl, array $properties)
    {
        $patterns = [];
        foreach ($properties as $property) {
            if($property['PROPERTY_TYPE'] == 'PRICE') {
                $patterns[] = mb_strtolower('price-' . $property['PROPERTY_CODE'] . '-(from|to)');
            } elseif($property['PROPERTY_TYPE'] == 'N') {
                $patterns[] = mb_strtolower($property['PROPERTY_CODE'] . '-(from|to)');
            } else {
                $patterns[] = mb_strtolower($property['PROPERTY_CODE'] . '-is');
            }
        }

        preg_match_all('#(' . $pageUrl . ')|(' . implode('|', $patterns) . ')#', $originUrl, $matches);

        $sectionUrl = array_filter($matches[1]);
        $filterProperties = array_filter($matches[2]);

        if(!$sectionUrl) {
            return false;
        }

        if($filterProperties) {
            return true;
        }

        return false;
    }
}
