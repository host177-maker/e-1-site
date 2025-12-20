<?php

namespace Absteam;

use Absteam\Admin\ProductsAdmin;

class Handlers
{
    public const updateProperties = [
        48 => 1041,
        49 => 1040
    ];

    private static function getRegionFolders(): array
    {
        return [
            '/',
            '/catalog/',
            '/contacts/'
        ];
    }

    private static function getUriPath(): string
    {
        $request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
        $uri     = new \Bitrix\Main\Web\Uri($request->getRequestUri());

        return $uri->getPath();
    }

    private static function getRegionUrl(): array
    {
        $request          = \Bitrix\Main\Context::getCurrent()->getRequest();
        if ($request->isAdminSection() || $request->isAjaxRequest()) {
            return [];
        }

        $path             = self::getUriPath();
        $arRegionFolders  = self::getRegionFolders();
        $pattern          = '#(' . implode('|', array_map(function ($item) {
            return $item === '/' ? '^/$' : preg_quote($item, '#');
        }, $arRegionFolders)) . ')#';

        // dump($pattern);

        return preg_grep($pattern, [$path]);
    }

    private static function regionalRedirects()
    {
        global $arRegion;

        $isRegionalFolder = self::getRegionUrl();

        // dd($isRegionalFolder);

        if ($isRegionalFolder && $arRegion && $arRegion['CODE'] && $arRegion['CODE'] !== 'moskva') {
            if (stripos($isRegionalFolder[0], $arRegion['CODE']) === false) {
                $additional = '';
                if (!empty($_GET)) {
                    $additional = '?' . http_build_query($_GET);
                }
                LocalRedirect('/' . $arRegion['CODE'] . $isRegionalFolder[0] . $additional);
            }
        }
    }

    private static function regionalCookie()
    {
        if (\Bitrix\Main\Loader::includeModule('aspro.max')) {
            $isRegionalFolder = self::getRegionUrl();

            // dd($isRegionalFolder);

            if (empty($isRegionalFolder)) {
                return;
            }

            $parts     = explode('/', trim($isRegionalFolder[0], '/'));
            $arRegions = \CMaxRegionality::getRegions();

            if (empty($arRegions)) {
                return;
            }

            $rightRegion  = array_filter($arRegions, function ($item) use ($parts) {
                return $item['CODE'] === $parts[0];
            });

            if (!empty($parts) && !empty($rightRegion)) {
                $GLOBALS['arRegion'] = end($rightRegion);

                $_COOKIE['current_region']      = $GLOBALS['arRegion']['ID']; // потому что где-то пишется прямо в глобальный массив
                $_COOKIE['current_region_code'] = $GLOBALS['arRegion']['CODE']; // потому что где-то пишется прямо в глобальный массив
            }
        }
    }

    public static function OnPageStartHandler()
    {
        self::regionalCookie();
    }

    public static function BeforePrologHandler()
    {
        self::regionalRedirects();
    }

    public static function EpilogHandler()
    {
        global $APPLICATION, $arRegion;

        if ($arRegion && $arRegion['CODE'] && $arRegion['CODE'] !== 'moskva') {
            $APPLICATION->AddHeadString('<link href="' . (\CMain::IsHTTPS() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $APPLICATION->GetCurPage(false) . '" rel="canonical" />', true);
        }
    }

    public static function OnBeforeIBlockElementUpdateHandler(&$arFields)
    {
        if (array_key_exists($arFields['IBLOCK_ID'], self::updateProperties) && !empty($arFields['PROPERTY_VALUES'])) { // если PROPERTY_VALUES пусто - значит что-то глобально пошло не так, поэтому ничего не делаем
            $arFields['PROPERTY_VALUES'][self::updateProperties[$arFields['IBLOCK_ID']]] = 1;
        }
    }

    /**
     * @param $list
     *
     * @return void
     */
    public static function OnAdminListDisplayHandler(&$list)
    {
        $tbl = $list->table_id;

        // Добавляем массовое действие установки/снятия ярлыка Скидка для товаров
        if (preg_match('#^tbl_iblock_element#', $tbl) && ($_REQUEST['IBLOCK_ID'] == E1_IBLOCK_CATALOG)) {
            $list->arActions['e1_set_label_skidka']   = 'Проставить ярлык Скидка';
            $list->arActions['e1_unset_label_skidka'] = 'Снять ярлык Скидка';
        }
    }

    /**
     * 1. Установка/снятие ярлыка Скидка
     * 2. ...
     *
     * @return void
     */
    public static function OnBeforeProlog_OnAdminListDisplayHandler()
    {
        $page = $GLOBALS['APPLICATION']->GetCurPage();

        if ($page == '/bitrix/admin/iblock_element_admin.php'
            && (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST')
            && is_array($_POST['ID'])
            //&& check_bitrix_sessid()
            && ($GLOBALS['APPLICATION']->GetGroupRight('iblock') == 'W')
            && ($action = $_POST['action'])
            && ($arAction = is_array($action) ? $action : [$action])
        ) {
            if (!check_bitrix_sessid()) {
                return; //TODO корректно здесь вывести ошибку.
                //throw new \Exception('Сеесия устарела. Обновите старницу.');
            }

            // Установка ярлыка Скидка
            if ('e1_set_label_skidka' == array_values($arAction)[0]) {
                ProductsAdmin::SetLabelSkidka($_POST['ID'], true);
            }

            //Снятие ярлыка Скидка
            if ('e1_unset_label_skidka' == array_values($arAction)[0]) {
                ProductsAdmin::SetLabelSkidka($_POST['ID'], false);
            }
        }
    }

    public static function OnEndBufferContentHandler(&$content)
    {
        global $arRegion;

        $arRegionFolders = self::getRegionFolders();

        if ($arRegion && $arRegion['CODE'] && $arRegion['CODE'] !== 'moskva') {
            // Заменяем ссылки на региональные
            $linkPattern = '#(href|action)=["\'](/(?:' . implode('|', array_map('preg_quote', $arRegionFolders)) . '))(.*)["\']#i';
            $content     = preg_replace($linkPattern, "$1=\"/{$arRegion['CODE']}$2$3\"", $content);

            // Добавляем региональную строку к атрибутам title и alt в разделе /catalog/
            if (strpos($_SERVER['REQUEST_URI'], '/catalog/') !== false && !empty($arRegion['PROPERTY_REGION_NAME_DECLINE_PP_VALUE'])) {
                $regionString = ' в ' . $arRegion['PROPERTY_REGION_NAME_DECLINE_PP_VALUE'];

                // Обработка title в img
                $titlePattern = '#(<img[^>]*title=["\'])([^"\']*)(["\'][^>]*>)#i';
                $content      = preg_replace_callback($titlePattern, function ($matches) use ($regionString) {
                    return $matches[1] . $matches[2] . $regionString . $matches[3];
                }, $content);

                // Обработка alt в img
                $altPattern = '#(<img[^>]*alt=["\'])([^"\']*)(["\'][^>]*>)#i';
                $content    = preg_replace_callback($altPattern, function ($matches) use ($regionString) {
                    return $matches[1] . $matches[2] . $regionString . $matches[3];
                }, $content);
            }
        }
    }
}
