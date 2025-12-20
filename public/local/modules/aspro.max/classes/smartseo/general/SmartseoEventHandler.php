<?php

namespace Aspro\Max\Smartseo\General;

class SmartseoEventHandler
{

    static public function onPageStart()
    {
        global $APPLICATION;

        Smartseo::init();

        if (\CSite::InDir('/bitrix/')) {
            Smartseo::fixBitrixCoreAjaxAuth();

            return;
        }

        SmartseoEngine::urlRewrite();
        SmartseoEngine::loadSeoPropertyForPage();
    }

    static public function onEpilog()
    {
        if (\CSite::InDir('/bitrix/')) {
            return;
        }

        SmartseoEngine::disallowPageIndexUrl();
        SmartseoEngine::replaceSeoPropertyOnPage();
    }

    static public function onEndBufferContent(&$bufferContent)
    {
        if (\CSite::InDir('/bitrix/')) {
            return;
        }

        if(Smartseo::getSettingObject()->isReplaceSnippetPage()) {
            SmartseoEngine::replaceSnippetInContent($bufferContent);
        }

        if(Smartseo::getSettingObject()->isReplaceUrlPage()) {
            SmartseoEngine::replaceUrlInContent($bufferContent);
        }
    }

    static public function onBeforeRestartBuffer()
    {
        if (\CSite::InDir('/bitrix/')) {
            return;
        }
    }

    static public function onAfterEpilog()
    {

    }

    static public function onTemplateGetFunctionClass($event)
    {
        return \Aspro\Max\Smartseo\Template\Functions\Fabric::getFunctionClass($event);
    }

    static public function onSearchReindex($currentStep, $callback, $callbackMethod)
    {
        return SmartseoEngine::searchReindex($currentStep, $callback, $callbackMethod);
    }

    static public function onSearchAfterIndexAdd($id, $fields)
    {
        SmartseoEngine::searchAfterIndex($id, $fields);
    }

    static public function onSearchBeforeIndex($fields)
    {
         return SmartseoEngine::searchBeforeIndex($fields);
    }

    static public function onSitemapAfterUpdate(\Bitrix\Main\Entity\Event $event)
    {
        SmartseoEngine::sitemapUpdateIndexFile($event);
    }

    static public function onAfterUpdateUrlEngine($result)
    {
        SmartseoEngine::sitemapUpdateFileByFilterConditionId($result['FILTER_CONDITION_ID']);
    }

}
