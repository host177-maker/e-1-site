<?php
require($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/main/include/prolog_before.php');

$requestMethod = mb_strtolower($_SERVER['REQUEST_METHOD']);
$method = ($_REQUEST['METHOD']);
if (strpos($method, 'promo') === false) {
    //доработка метода, заменяет тире на нижнее подчеркивание
    $method = str_replace('-', '_', $method);
}
if (strpos($method, '/') !== false) {
    $arrayUrl = explode('/', $method);
    $method = $arrayUrl[0];
    $_REQUEST['param'] = $arrayUrl[1];
}

// Обрабатываем данные
$api = new E_1\Api\Site();
$resMethod = $api->{$method}();
$api->outputJson($resMethod);