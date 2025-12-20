<?php

use Bitrix\Main\Context;

/**
 * Определяет проверку pageSpeed
 *
 * @return bool
 */
function isPageSpeedDetected(): bool
{
    return stripos(@$_SERVER['HTTP_USER_AGENT'], 'Lighthouse') !== false;
}

/**
 * Определяет страницы пагинации
 *
 * @param string $marker
 * @return bool
 */
function isPaginationDetected(string $marker = 'PAGEN_'): bool
{
    return !empty(array_filter(
        Context::getCurrent()->getRequest()->getQueryList()->toArray() ?? [],
        static fn($value, $key) => stripos($key, $marker) !== false,
        ARRAY_FILTER_USE_BOTH
    ));
}