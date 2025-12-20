<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
$arResult['ALPHABET_REGIONS']            = [];
$arResult['ALPHABET_REGIONS']['COUNTER'] = 0;
$arResult['SECTIONS_REGIONS']            = [];
$arSections                              = [];
$arSectionsIds                           = [];

$arResult['MAIN_DOMAIN']   = '';
$arResult['DOMAIN_REGION'] = '';

foreach ($arResult['REGIONS'] as $key => $value) {
    $sName        = str_replace(['п. '], '', $value['NAME']);
    $sFirstLetter = mb_substr($sName, 0, 1);

    $arResult['ALPHABET_REGIONS'][$sFirstLetter]['NAME']    = $sFirstLetter;
    $arResult['ALPHABET_REGIONS'][$sFirstLetter]['ITEMS'][] = $value;
    $arResult['ALPHABET_REGIONS']['COUNTER']++;

    $arSections[$value['IBLOCK_SECTION_ID']][] = $value;
    //$arResult['SECTIONS_REGIONS'][$value['IBLOCK_SECTION_ID']]['ITEMS'][] = $value;
    $arSectionsIds[$value['IBLOCK_SECTION_ID']] = $value['IBLOCK_SECTION_ID'];
    if ($value['PROPERTY_DEFAULT_VALUE'] == 'Y') {
        $arResult['MAIN_DOMAIN'] = $value['PROPERTY_MAIN_DOMAIN_VALUE'];
    }
    if ($value['PROPERTY_MAIN_DOMAIN_VALUE'] == $_SERVER['SERVER_NAME']) {
        $arResult['DOMAIN_REGION'] = $value;
    }
}

$arSectSelect = ['ID', 'NAME'];
$arSectFilter = ['IBLOCK_ID' => \CMaxRegionality::getRegionIBlockID(), 'ACTIVE' => 'Y', 'ID' => $arSectionsIds];
$dbSect       = CIBlockSection::GetList(['NAME' => 'asc'], $arSectFilter, false, $arSectSelect, false);
while ($obSect = $dbSect->GetNextElement()) {
    $arFields                                               = $obSect->GetFields();
    $arResult['SECTIONS_REGIONS'][$arFields['ID']]['NAME']  = $arFields['NAME'];
    $arResult['SECTIONS_REGIONS'][$arFields['ID']]['ITEMS'] = $arSections[$arFields['ID']];
}

if (
    !$arResult['POPUP'] && $arResult['CURRENT_REGION']['PROPERTY_MAIN_DOMAIN_VALUE'] != $_SERVER['SERVER_NAME']
    && $arResult['REGIONS'][$arResult['CURRENT_REGION']['PROPERTY_REGION_TAG_NEAREST_REGION_VALUE']]['PROPERTY_MAIN_DOMAIN_VALUE'] != $_SERVER['SERVER_NAME']
) {
    if (!empty($arResult['DOMAIN_REGION'])) {
        $arResult['REAL_REGION']         = $arResult['DOMAIN_REGION'];
        $arResult['SHOW_REGION_CONFIRM'] = true;
    }
    if (
        is_array($arResult['DOMAIN_REGION']) &&
        $_COOKIE['current_region'] != $arResult['DOMAIN_REGION']['ID']
    ) {
        $arResult['SHOW_REGION_CONFIRM'] = false;
        //$arResult['CURRENT_REGION'] = $arResult['DOMAIN_REGION'];
    }
}

if (!empty($arResult['JS_REGIONS'])) {
    foreach ($arResult['JS_REGIONS'] as $key => $arJsRegion) {
        $arJsFullRegion = $arResult['REGIONS'][$arJsRegion['ID']];
        if (!empty($arJsFullRegion['PROPERTY_MAIN_DOMAIN_VALUE'])) {
            $arResult['JS_REGIONS'][$key]['HREF'] = $arResult['HOST'] . $arJsFullRegion['PROPERTY_MAIN_DOMAIN_VALUE'] . $arJsRegion['HREF'];
        } elseif (!empty($arJsFullRegion['PROPERTY_REGION_TAG_NEAREST_REGION_VALUE']) && !empty($arResult['REGIONS'][$arJsFullRegion['PROPERTY_REGION_TAG_NEAREST_REGION_VALUE']]['PROPERTY_MAIN_DOMAIN_VALUE'])) {
            $arResult['JS_REGIONS'][$key]['HREF'] = $arResult['HOST'] . $arResult['REGIONS'][$arJsFullRegion['PROPERTY_REGION_TAG_NEAREST_REGION_VALUE']]['PROPERTY_MAIN_DOMAIN_VALUE'] . $arJsRegion['HREF'];
        } elseif (
            !empty($arResult['MAIN_DOMAIN']) && is_array($arResult['DOMAIN_REGION'])
            && $arResult['MAIN_DOMAIN'] != $arResult['DOMAIN_REGION']['PROPERTY_MAIN_DOMAIN_VALUE']
        ) {
            $arResult['JS_REGIONS'][$key]['HREF'] = $arResult['HOST'] . $arResult['MAIN_DOMAIN'] . $arJsRegion['HREF'];
        }
    }
}

if (!function_exists('transformRegionHrefs')) {
    /**
     * Преобразует URL в HREF элементах массива регионов, извлекая поддомен из host
     *
     * @param array $regions Массив регионов
     * @return array Преобразованный массив регионов
     *
     * @throws InvalidArgumentException Если входные данные не являются массивом
     */
    function transformRegionHrefs(array $regions): array
    {
        if (!is_array($regions)) {
            throw new InvalidArgumentException('Input must be an array');
        }

        return array_map(function ($region) {
            if (!isset($region['HREF']) || !is_string($region['HREF'])) {
                return $region;
            }

            $href = $region['HREF'];

            // Если HREF уже начинается с "/", пропускаем преобразование
            if (strpos($href, '/') === 0) {
                return $region;
            }

            $parsedUrl = parse_url($href);

            if ($parsedUrl === false || !isset($parsedUrl['host'])) {
                return $region;
            }

            // Обрабатываем host - извлекаем поддомен или оставляем www
            $hostParts = explode('.', $parsedUrl['host']);
            $subdomain = (count($hostParts) > 2 && $hostParts[0] !== 'www')
                ? $hostParts[0]
                : '';

            // $query    = isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '';
            // $fragment = isset($parsedUrl['fragment']) ? '#' . $parsedUrl['fragment'] : '';

            $newHref = '/' . ($subdomain ? $subdomain . '/' : '');

            $region['HREF'] = $newHref;

            return $region;
        }, $regions);
    }
}

try {
    if (!empty($arResult['JS_REGIONS'])) {
        $arResult['JS_REGIONS'] = transformRegionHrefs($arResult['JS_REGIONS']);
    }
} catch (InvalidArgumentException $e) {
    error_log($e->getMessage());
}
