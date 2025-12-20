<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {die();}

/**
 * @var array $arResult
 */

if (defined('E1_SOCIAL_YOUTUBE_HIDE') && E1_SOCIAL_YOUTUBE_HIDE) {
    unset($arResult['SOCIAL_YOUTUBE']);
}

if (defined('E1_SOCIAL_RUTUBE_URL') && E1_SOCIAL_RUTUBE_URL) {
    $arResult['SOCIAL_RUTUBE'] = E1_SOCIAL_RUTUBE_URL;
}

