<?php

namespace Absteam\Helpers;

use Bitrix\Main\Application;
use Bitrix\Main\Context;
use Bitrix\Main\SiteTable;

/**
 *
 */
class Site
{

    /**
     * Возвращает идентификатор текущего сайта
     *
     * @return string
     */
    public static function getSiteId(): string
    {
        return Application::getInstance()->getContext()->getSite() ?? 's1';
    }

    /**
     * HTTP | HTTPS
     *
     * @param bool $addSlash
     * @return string
     */
    public static function getProtocol(bool $addSlash = false): string
    {
        return (Context::getCurrent()->getRequest()->isHttps() ? 'https' : 'http') . ($addSlash ? '://' : '');
    }

    /**
     * Возвращает полный url относительно указанного сайта
     *
     * @param string $appendix
     * @param string|null $siteId
     * @return string
     */
    public static function getSiteUrl(string $appendix = '', ?string $siteId = null): string
    {
        return self::getProtocol(true) . (self::getSiteInfo($siteId)['SERVER_NAME'] ?? '') . $appendix;
    }

    /**
     * @param string|null $siteId
     * @return array
     */
    public static function getSiteInfo(?string $siteId = null): array
    {
        if (!$siteId) {
            $siteId = self::getSiteId();
        }

        $site = SiteTable::getList([
            'filter' => [
                'LID' => $siteId
            ],
            'select' => [
                'SITE_NAME',
                'SERVER_NAME',
            ],
            'limit' => 1,
        ])->fetch();
        if (!$site) {
            return [];
        }

        return $site;
    }

}