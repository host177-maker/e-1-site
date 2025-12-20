<?php

namespace Aspro\Max\Smartseo\General;

class SmartseoEngine
{
    const PREFIX_SNIPPET = 'ASPRO_SMARTSEO_';

    static public function urlRewrite()
    {
        if(!Smartseo::validateModules()) {
            return;
        }

        global $APPLICATION;

        $context = \Bitrix\Main\Application::getInstance()->getContext();

        $request = $context->getRequest();

        $server = $context->getServer();

        $requestUriParam = self::getPrepareRequestUri($request->getRequestUri());

        $dataScope = \Aspro\Max\Smartseo\Models\SmartseoFilterConditionUrlTable::getDataScopeByUrl($requestUriParam['URL_FILTER'], SITE_ID);

        if (!$dataScope || !$dataScope['URL_REAL'] || !$dataScope['URL_NEW']) {
            return;
        }

        Smartseo::disallowNoindexRule(true);

        if ($dataScope['URL_REAL'] == $requestUriParam['URL'] || $dataScope['URL_REAL'] == $requestUriParam['URL_FILTER']) {
            $_urlRedirect = implode('?', array_filter([$dataScope['URL_NEW'], $requestUriParam['OTHER_QUERY']]));

            LocalRedirect($_urlRedirect, false, '301 Moved Permanently');
        }

        $requestNoFriendlyUriParam = self::getPrepareRequestUri($dataScope['URL_REAL']);

        if ($requestNoFriendlyUriParam['ALL_QUERY_PARAMS']) {
            foreach ($requestNoFriendlyUriParam['ALL_QUERY_PARAMS'] as $code => $value) {
                $_GET[$code] = $value;
            }
        }

        $requestUri = $dataScope['URL_REAL'];
        if($requestUriParam['OTHER_QUERY_PARAMS']) {
            if($requestNoFriendlyUriParam['FILTER_QUERY_PARAMS']) {
                $requestUri = $requestNoFriendlyUriParam['URL'] . '?' . http_build_query(
                    array_merge($requestNoFriendlyUriParam['FILTER_QUERY_PARAMS'], $requestUriParam['OTHER_QUERY_PARAMS'])
                );
            } else {
                $requestUri = $requestUri . '?' . http_build_query(
                    $requestUriParam['OTHER_QUERY_PARAMS']
                );
            }
        }

        $serverArray = $server->toArray();

        $_SERVER['REQUEST_URI'] = $requestUri;

        $serverArray['REQUEST_URI'] = $requestUri;

        $server->set($serverArray);

        $context->initialize(new \Bitrix\Main\HttpRequest($server, $_GET, [], [], $_COOKIE), $context->getResponse(), $server);

        $APPLICATION->reinitPath();

        Smartseo::setCurrentData($dataScope);
    }

    static public function loadSeoPropertyForPage()
    {
        if(!Smartseo::validateModules()) {
            return;
        }

        if (!\Bitrix\Main\Loader::includeModule('iblock')) {
            return;
        }

        if (!Smartseo::getCurrentData()) {
            return;
        }

        $dataScope = Smartseo::getCurrentData();

        $seoTemplates = \Aspro\Max\Smartseo\Models\SmartseoSeoTemplateTable::getDataSeoTemplates([
              \Aspro\Max\Smartseo\Models\SmartseoSeoTemplateTable::ENTITY_TYPE_FILTER_RULE => $dataScope['RULE_ID'],
              \Aspro\Max\Smartseo\Models\SmartseoSeoTemplateTable::ENTITY_TYPE_FILTER_CONDITION => $dataScope['CONDITION_ID'],
              \Aspro\Max\Smartseo\Models\SmartseoSeoTemplateTable::ENTITY_TYPE_FILTER_URL => $dataScope['URL_ID'],
        ]);

        if (!$seoTemplates) {
            return;
        }

        $element = new \Aspro\Max\Smartseo\Template\Entity\FilterRuleUrl($dataScope['URL_ID']);

        $currentSeoProperty = Smartseo::getCurrentSeoProperty();
        foreach ($seoTemplates as $seoProperty) {
            if (!$seoProperty['TEMPLATE']) {
                $currentSeoProperty[$code] = '';
                continue;
            }

            $currentSeoProperty[$seoProperty['CODE']] = \Bitrix\Main\Text\HtmlFilter::encode(
                \Bitrix\Iblock\Template\Engine::process($element, $seoProperty['TEMPLATE'])
            );
        }

        Smartseo::setCurrentSeoProperty($currentSeoProperty);
    }

    static public function replaceSeoPropertyOnPage()
    {
        if(!Smartseo::validateModules()) {
            return;
        }

        if (!Smartseo::getCurrentData()) {
            return;
        }

        if (!Smartseo::getCurrentSeoProperty()) {
            return;
        }

        $currentData = Smartseo::getCurrentData();
        $seoProperties = Smartseo::getCurrentSeoProperty();

        global $APPLICATION;

        $metaProperties = [
            'title' => 'META_TITLE',
            'description' => 'META_DESCRIPTION',
            'keywords' => 'META_KEYWORDS',
        ];

        if (Smartseo::getSettingObject()->isReplaceMetaPage()) {
            foreach ($metaProperties as $metaTag => $field) {
                if (!$seoProperties[$field]) {
                    continue;
                }
                $APPLICATION->SetPageProperty($metaTag, $seoProperties[$field]);
            }
        }

        if (Smartseo::getSettingObject()->isReplaceTitlePage()) {
            if ($seoProperties['PAGE_TITLE']) {
                $APPLICATION->SetTitle($seoProperties['PAGE_TITLE'], false);
            }
        }

        if ($seoProperties['BREADCRUMB_PAGE']) {
            $APPLICATION->AddChainItem($seoProperties['BREADCRUMB_PAGE']);
        }

        if ($currentData['URL_NEW']) {
            $APPLICATION->SetPageProperty('canonical', $currentData['URL_NEW']);
        }
    }

    static public function disallowPageIndexUrl()
    {
        if(!Smartseo::validateModules()) {
            return;
        }

        global $APPLICATION;

        if(Smartseo::allowedNoindexRule()) {
            if(SmartseoNoindex::isDisallow($_SERVER['REQUEST_URI'], SITE_ID)) {
                $APPLICATION->SetPageProperty('robots', 'noindex, nofollow');
            }
        } else {
            $currentData = Smartseo::getCurrentData();

            if ($currentData['CONDITION_URL_CLOSE_INDEXING'] == 'Y' || $currentData['RULE_URL_CLOSE_INDEXING'] == 'Y') {
                $APPLICATION->SetPageProperty('robots', 'noindex, nofollow');
            }
        }
    }

    static public function replaceSnippetInContent(&$content)
    {
        if(!Smartseo::validateModules()) {
            return;
        }

        $seoProperties = Smartseo::getCurrentSeoProperty();

        $pattern = [];
        $replacement = [];
        foreach ($seoProperties as $code => $value) {
            $pattern[] = '|#' . self::PREFIX_SNIPPET . $code . '#|';
            $replacement[] = htmlspecialchars_decode($value);
        }

        if (!$seoProperties) {
            return;
        }

        $replacedContent = preg_replace($pattern, $replacement, $content);

        if ($replacedContent) {
            $content = $replacedContent;
        }
    }

    static public function replaceUrlInContent(&$content)
    {
        if(!Smartseo::validateModules()) {
            return;
        }

        if(!$currentData = Smartseo::getCurrentData()) {
            return;
        }

        $replacedContent = preg_replace('#' . $currentData['URL_REAL'] . '#', $currentData['URL_NEW'], $content);

        if ($replacedContent) {
            $content = $replacedContent;
        }
    }

    static public function searchReindex($currentStep, $callback, $callbackMethod)
    {
        if(!Smartseo::validateModules()) {
            return;
        }

        if(!\Bitrix\Main\Loader::includeModule('iblock')) {
           return false;
        }

        $searchEngine = new \Aspro\Max\Smartseo\Engines\SearchEngine($currentStep['SITE_ID']);

        $filter = [];

        $currentItemId = $searchEngine->getUrlIdByIndex($currentStep['ID']);

        if($currentStep['MODULE'] == $searchEngine::MODULE_TO && $currentItemId > 0) {
            $filter = [
                '>URL_ID' => $currentItemId,
            ];
        }

        $callback->MODULE = $searchEngine::MODULE_TO;

        $allPages = $searchEngine->getAllPages($filter);

        foreach ($allPages as $page) {
            $result = call_user_func(array($callback, $callbackMethod), $page);

            if(!$result) {
                return $page['ID'];
            }
        }

        return false;
    }

    static public function searchAfterIndex($id, $fields)
    {

    }

    static public function searchBeforeIndex($fields)
    {
        return $fields;
    }

    static public function sitemapUpdateIndexFile(\Bitrix\Main\Entity\Event $event)
    {
       if(!Smartseo::validateModules()) {
           return;
       }

       $eventPrimary = $event->getParameter('primary');
       $eventFields = $event->getParameter('fields');


       if(!isset($eventFields['DATE_RUN'])) {
           return;
       }

       $sitemap = \Bitrix\Seo\SitemapTable::getRowById($eventPrimary);

       if(!$sitemap) {
           return;
       }

       \Aspro\Max\Smartseo\Engines\SitemapEngine::fullUpdateSitemapIndex($sitemap['SITE_ID']);
    }

    static public function sitemapUpdateFileByFilterConditionId($filterConditionId)
    {
        if(!Smartseo::validateModules()) {
            return;
        }

        $row = \Aspro\Max\Smartseo\Models\SmartseoFilterSitemapTable::getRow([
            'select' => [
                'SITEMAP_ID'
            ],
            'filter' => [
                'FILTER_CONDITION_ID' => $filterConditionId,
                'SITEMAP.UPDATE_SITEMAP_FILE' => 'Y',
            ]
        ]);

        if(!$row || !$row['SITEMAP_ID']) {
            return;
        }

        $sitemapEngine = new \Aspro\Max\Smartseo\Engines\SitemapEngine($row['SITEMAP_ID']);

        if (!$sitemapEngine->update()) {
            if($sitemapEngine->hasErrors()) {
                throw new \Exception(implode('<br>', $sitemapEngine->getErrors()));
            }

            return false;
        }

        return $sitemapEngine->getResult();
    }

    static private function getPrepareRequestUri($requestUri)
    {
        if(!Smartseo::validateModules()) {
            return;
        }

        $requestUri = urldecode($requestUri);
        if (mb_strtolower(LANG_CHARSET) === 'windows-1251') {
            $requestUri = \Bitrix\Main\Text\Encoding::convertEncoding($requestUri, 'UTF-8', LANG_CHARSET);
        }

        list($url, $query) = explode('?', $requestUri);

        $filterPairs = [];

        $pattern = '/set_filter=[^&]*/';
        if(preg_match($pattern, $query)) {
            $query = preg_replace($pattern, '', $query);

            $pattern = '/[&]?(\w+_\d+_\d{8,10}=Y|\w+_\d+=\d{8,10}|\w+_\d+_MIN=[\d.]+|\w+_P\d+_MIN=[\d.]+)/';
            if(preg_match_all($pattern,  $query, $matches)){
                $query = preg_replace($pattern, '', $query);

                $filterPairs = $matches[1];
            }

            array_unshift($filterPairs, 'set_filter=y');
        }

        $otherPairs = [];

        $pattern = '/[&]?(\w+=[^&]*)/';
        if(preg_match_all($pattern,  $query, $matches)){
            $query = preg_replace($pattern, '', $query);

            $otherPairs = $matches[1];
        }

        $filterParams = [];
        foreach ($filterPairs as $index => $paramPair) {
            list($_code, $_value) = explode('=', $paramPair, 2);
            $filterParams[$_code] = $_value;
        }

        $otherParams = [];
        foreach ($otherPairs as $index => $paramPair) {
            list($_code, $_value) = explode('=', $paramPair, 2);
            $otherParams[$_code] = $_value;
        }

        $allQueryParams = array_merge($filterParams, $otherParams);

        $result = [
            'URL' => $url,
            'URL_FILTER' => $filterPairs ? $url . '?' . implode('&', $filterPairs) : $url,
            'FILTER_QUERY' => $filterPairs ? implode('&', $filterPairs) : '',
            'FILTER_QUERY_PARAMS' => $filterParams,
            'OTHER_QUERY' => $otherPairs ? implode('&', $otherPairs) : '',
            'OTHER_QUERY_PARAMS' => $otherParams,
            'ALL_QUERY_PARAMS' => $allQueryParams
        ];

        return $result;
    }

}
