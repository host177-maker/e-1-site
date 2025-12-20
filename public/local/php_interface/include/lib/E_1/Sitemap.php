<?php

namespace E_1;

/**
 * Класс для работы с картой сайта (Sitemap)
 */
class Sitemap
{

    function __construct() {
        if (!\CModule::IncludeModule("zamit.sitemap")) {
            throw new \Exception("Не подключен модуль Генерации Sitemap (zamit.sitemap)", 1);
        }
    }


    /**
     * Метод для генерации карты сайта
     * @return bool
     */
    public static function create($siteId = SITE_ID, $iExecutionTime = 5, $iRecordsPerStep = 5000, $sMask = '') 
    {        
        $iFileCounter = 0;
        $oSiteMap = new \CSiteMapG;
        
        if ( isset($sMask) && empty($sMask) ) {
            $sMaskFilter = \COption::GetOptionString("zamit.sitemap", "sm_mask");
        } else {
            $sMaskFilter = $sMask;
        }
                
        $arOptions = array(
            "FORUM_TOPICS_ONLY" => \COption::GetOptionString("zamit.sitemap", "sm_forum_topics_only"),
            "BLOG_NO_COMMENTS" => \COption::GetOptionString("zamit.sitemap", "sm_blog_no_comments"),
            "USE_HTTPS" => \COption::GetOptionString("zamit.sitemap", "sm_use_https"),
            "INC" => 2,
            "MASK" => $sMaskFilter
        );

        $aSiteMap = $oSiteMap->Create($siteId, array($iExecutionTime, $iRecordsPerStep), $NS, $arOptions);
        $iFileCounter++;
        if (!$aSiteMap) {
                throw new \Exception("Произошла ошибка при попытке генерации Sitemap #{$iFileCounter}", 1);
        }

        while ($aSiteMap !== true) {
                $aSiteMap = $oSiteMap->Create($siteId, array($iExecutionTime, $iRecordsPerStep), $aSiteMap, $arOptions);
                $iFileCounter++;

                // Ошибка генерации карты
                if (!$aSiteMap) {
                        throw new \Exception("Произошла ошибка при попытке генерации Sitemap #{$iFileCounter}", 1);
                }
        }

        return true;
    }
}