<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<?php
// подключаем Яндекс Пэй 
Bitrix\Main\Page\Asset::getInstance()->addString('<script src="https://pay.yandex.ru/sdk/v1/pay.js?v=' . time() . '"></script>');
?>
<?

use \Bitrix\Main\Localization\Loc; ?>
<? $frame = $this->createFrame()->begin(''); ?>
<div class="content_wrapper_block">
	<div class="maxwidth-theme wide"></div>
</div>
<? $frame->end(); ?>