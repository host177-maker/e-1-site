<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?use \Bitrix\Main\Localization\Loc;?>

	<?
	$bPicture = ($arResult['ITEM']['PREVIEW_PICTURE']);

	$type = 'WEBFORM';

	$webFormId = $arResult['ITEM']['PROPERTY_LINK_WEB_FORM_VALUE'];	
	?>
	<div class="form marketing-popup with_web_form popup-text-info<?=($bPicture ? " popup-text-info--has-img" : "");?> <?=$templateName?>" data-classes="<?=$type?> <?=$position?>">

		<?if($arResult['ITEM']):?>
			<?if($arResult['ITEM']):?>

				<?if($arResult['ITEM']['PREVIEW_PICTURE']):?>				
					<div class="popup-text-info__picture"><div style="background-image: url(<?=CFile::GetPath($arResult['ITEM']["PREVIEW_PICTURE"]);?>)"></div></div>
				<?endif;?>
				
				<?if( (int)$webFormId > 0):?>
					<div class="popup-text-info__webform ">
						<?
						$APPLICATION->IncludeComponent(
							"bitrix:form.result.new",
							"popup",
							Array(
								"AJAX_MODE" => "Y",
								"SEF_MODE" => "N",
								"WEB_FORM_ID" => $webFormId,
								"START_PAGE" => "new",
								"SHOW_LIST_PAGE" => "N",
								"SHOW_EDIT_PAGE" => "N",
								"SHOW_VIEW_PAGE" => "N",
								"SUCCESS_URL" => "",
								"SHOW_ANSWER_VALUE" => "N",
								"SHOW_ADDITIONAL" => "N",
								"SHOW_STATUS" => "N",
								"EDIT_ADDITIONAL" => "N",
								"EDIT_STATUS" => "Y",
								"NOT_SHOW_FILTER" => "",
								"NOT_SHOW_TABLE" => "",
								"CHAIN_ITEM_TEXT" => "",
								"CHAIN_ITEM_LINK" => "",
								"IGNORE_CUSTOM_TEMPLATE" => "N",
								"USE_EXTENDED_ERRORS" => "Y",
								"CACHE_GROUPS" => "N",
								"CACHE_TYPE" => "A",
								"CACHE_TIME" => "3600000",
								"AJAX_OPTION_JUMP" => "N",
								"AJAX_OPTION_STYLE" => "Y",
								"AJAX_OPTION_HISTORY" => "N",
								"SHOW_LICENCE" => CMax::GetFrontParametrValue('SHOW_LICENCE'),
								"HIDDEN_CAPTCHA" => CMax::GetFrontParametrValue('HIDDEN_CAPTCHA'),
								"VARIABLE_ALIASES" => Array(
									"action" => "action"
								)
							)
						);
						?>
					</div>
				<?endif;?>
			<?endif;?>
		<?else:?>
			ERROR
		<?endif;?>
	</div>
