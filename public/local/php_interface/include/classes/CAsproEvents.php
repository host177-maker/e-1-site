<?
use \Bitrix\Main\Localization\Loc,
	\Bitrix\Main\Config\Option,
	\Bitrix\Main\Context,
	\Bitrix\Main\Event,
	Absteam\Helper,
	\Bitrix\Main\Service\GeoIp;


if(!defined('ASPRO_MAX_MODULE_ID')){
	define('ASPRO_MAX_MODULE_ID', 'aspro.max');
}

class CAsproEvents
{
	public static function OnGetBuyBlockElement($arItem, $totalCount, $arParams, &$arOptions){

		$arAddToBasketOptions = array(
			"SHOW_BASKET_ONADDTOCART" => Option::get(ASPRO_MAX_MODULE_ID, "SHOW_BASKET_ONADDTOCART", "Y", SITE_ID) == "Y",
			"USE_PRODUCT_QUANTITY_LIST" => Option::get(ASPRO_MAX_MODULE_ID, "USE_PRODUCT_QUANTITY_LIST", "Y", SITE_ID) == "Y",
			"USE_PRODUCT_QUANTITY_DETAIL" => Option::get(ASPRO_MAX_MODULE_ID, "USE_PRODUCT_QUANTITY_DETAIL", "Y", SITE_ID) == "Y",
			"BUYNOPRICEGGOODS" => Option::get(ASPRO_MAX_MODULE_ID, "BUYNOPRICEGGOODS", "NOTHING", SITE_ID),
			"BUYMISSINGGOODS" => Option::get(ASPRO_MAX_MODULE_ID, "BUYMISSINGGOODS", "ADD", SITE_ID),
			"EXPRESSION_ORDER_BUTTON" => Option::get(ASPRO_MAX_MODULE_ID, "EXPRESSION_ORDER_BUTTON", GetMessage("EXPRESSION_ORDER_BUTTON_DEFAULT"), SITE_ID),
			"EXPRESSION_ORDER_TEXT" => Option::get(ASPRO_MAX_MODULE_ID, "EXPRESSION_ORDER_TEXT", GetMessage("EXPRESSION_ORDER_TEXT_DEFAULT"), SITE_ID),
			"EXPRESSION_SUBSCRIBE_BUTTON" => Option::get(ASPRO_MAX_MODULE_ID, "EXPRESSION_SUBSCRIBE_BUTTON", GetMessage("EXPRESSION_SUBSCRIBE_BUTTON_DEFAULT"), SITE_ID),
			"EXPRESSION_SUBSCRIBED_BUTTON" => Option::get(ASPRO_MAX_MODULE_ID, "EXPRESSION_SUBSCRIBED_BUTTON", GetMessage("EXPRESSION_SUBSCRIBED_BUTTON_DEFAULT"), SITE_ID),
			"EXPRESSION_ADDTOBASKET_BUTTON_DEFAULT" => Option::get(ASPRO_MAX_MODULE_ID, "EXPRESSION_ADDTOBASKET_BUTTON_DEFAULT", GetMessage("EXPRESSION_ADDTOBASKET_BUTTON_DEFAULT"), SITE_ID),
			"EXPRESSION_ADDEDTOBASKET_BUTTON_DEFAULT" => Option::get(ASPRO_MAX_MODULE_ID, "EXPRESSION_ADDEDTOBASKET_BUTTON_DEFAULT", GetMessage("EXPRESSION_ADDEDTOBASKET_BUTTON_DEFAULT"), SITE_ID),
			"EXPRESSION_READ_MORE_OFFERS_DEFAULT" => Option::get(ASPRO_MAX_MODULE_ID, "EXPRESSION_READ_MORE_OFFERS_DEFAULT", GetMessage("EXPRESSION_READ_MORE_OFFERS_DEFAULT"), SITE_ID),
		);

        if($arParams['RESTRICTION']){
            $arAddToBasketOptions['EXPRESSION_ADDTOBASKET_BUTTON_DEFAULT'] = 'В корзину';
        }

		if(!empty($arItem['OFFERS'])){
			$arItem = array_shift($arItem['OFFERS']);
		}

		$iIblockId = $arItem['IBLOCK_ID'];
		$iItemId = $arItem['ID'];
		if(!isset($arItem['PROPERTIES']['AVAILABILITY']) && !empty($arItem['PROPERTIES']['CML2_LINK']['LINK_IBLOCK_ID'])){
			$iIblockId = $arItem['PROPERTIES']['CML2_LINK']['LINK_IBLOCK_ID'];
			$iItemId = $arItem['PROPERTIES']['CML2_LINK']['VALUE'];
		}

		if(!isset($arItem['PROPERTIES']['AVAILABILITY'])){
			$dbProps = CIBlockElement::GetProperty($iIblockId, $iItemId, array("sort" => "asc"), Array("CODE" => "AVAILABILITY"));
			if($arProps = $dbProps->Fetch()){
				$arItem['PROPERTIES']['AVAILABILITY'] = $arProps;
			}
		}
		
		if($arItem['PROPERTIES']['AVAILABILITY']['VALUE_ENUM'] == 'Да' && $arItem["MIN_PRICE"]["DISCOUNT_VALUE"] > 0){
			$arItem["CAN_BUY"] = 1;
		}
		if($arItem["CAN_BUY"]){
			if(empty($class_btn)){
				$class_btn = 'btn-lg';
			}
			$buttonACTION = 'ADD';
			$buttonText = array($arAddToBasketOptions['EXPRESSION_ADDTOBASKET_BUTTON_DEFAULT'], $arAddToBasketOptions['EXPRESSION_ADDEDTOBASKET_BUTTON_DEFAULT']);
			$buttonHTML = '<span onclick="ecommerceOnBasketAdd('.\CUtil::PHPToJSObject(empty($arItem["ECOM_ITEM_DATA"]) ? $arItem["ECOM_DATA"] : $arItem["ECOM_ITEM_DATA"]).')" data-value="'.$arItem["MIN_PRICE"]["DISCOUNT_VALUE_VAT"].'" data-full-price="'.$arItem["MIN_PRICE"]["VALUE"].'" data-currency="'.$arItem["MIN_PRICE"]["CURRENCY"].'" class="'.$class_btn.' to-cart btn btn-default transition_bg animate-load" data-item="'.$arItem["ID"].'" data-float_ratio="'.$float_ratio.'" data-ratio="'.$ratio.'" data-bakset_div="bx_basket_div_'.$arItem["ID"].($arItemIDs['DOP_ID'] ? '_'.$arItemIDs['DOP_ID'] : '').'" data-props="'.$arItemProps.'" data-part_props="'.$partProp.'" data-add_props="'.$addProp.'"  data-empty_props="'.$emptyProp.'" data-offers="'.$arItem["IS_OFFER"].'" data-iblockID="'.$arItem["~IBLOCK_ID"].'"  data-quantity="'.$quantity.'">'.CMax::showIconSvg("fw ncolor colored", SITE_TEMPLATE_PATH."/images/svg/basket.svg", $buttonText[0]).'<span>'.$buttonText[0].'</span></span><a rel="nofollow" href="'.$basketUrl.'" class="'.$class_btn.' in-cart btn btn-default transition_bg" data-item="'.$arItem["ID"].'"  style="display:none;">'.CMax::showIconSvg("fw ncolor colored", SITE_TEMPLATE_PATH."/images/svg/inbasket.svg", $buttonText[1]).'<span>'.$buttonText[1].'</span></a>';

			//$arOptions = array("OPTIONS" => $arAddToBasketOptions, "TEXT" => $buttonText, "HTML" => $buttonHTML, "ACTION" => $buttonACTION, "RATIO_ITEM" => $ratio, "MIN_QUANTITY_BUY" => $quantity, "MAX_QUANTITY_BUY" => $max_quantity, "CAN_BUY" => $canBuy);

			$arOptions['TEXT'] = $buttonText;
			$arOptions['ACTION'] = $buttonACTION;
			$arOptions['HTML'] = $buttonHTML;
			$arOptions['CAN_BUY'] = 'Y';
		}
	}

	public static function OnAsproItemShowItemPricesHandler($arParams, $arPrices, $strMeasure, &$price_id, $bShort, &$html){
		$popup = false;
		$bReturn = true;
		$price_id = 0;

		if((is_array($arParams) && $arParams)&& (is_array($arPrices) && $arPrices))
		{
			if(!$popup)
				ob_start();

			$sDiscountPrices = \Bitrix\Main\Config\Option::get(FUNCTION_MODULE_ID, 'DISCOUNT_PRICE');
			$arDiscountPrices = array();
			if($sDiscountPrices)
				$arDiscountPrices = array_flip(explode(',', $sDiscountPrices));

			if(!$popup)
			{
				$iCountPriceGroup = 0;
				foreach($arPrices as $key => $arPrice)
				{
					if($arPrice['CAN_ACCESS'])
					{
						$iCountPriceGroup++;
						if($arPrice["MIN_PRICE"] == "Y" && $arPrice['CAN_BUY'] == 'Y'){
							$price_id = $arPrice["PRICE_ID"];
						}
					}
				}
				if (!$price_id) {
					$result = false;
					$minPrice = 0;

					//get currency for convert
					$arCurrencyParams = array();
					if ("Y" == $arParams["CONVERT_CURRENCY"])
					{
						if(\CModule::IncludeModule("currency"))
						{
							$arCurrencyInfo = \CCurrency::GetByID($arParams["CURRENCY_ID"]);
							if (is_array($arCurrencyInfo) && !empty($arCurrencyInfo))
							{
								$arCurrencyParams["CURRENCY_ID"] = $arCurrencyInfo["CURRENCY"];
							}
						}
					}

					foreach($arPrices as $key => $arPrice)
					{
						if (empty($result))
						{
							$minPrice = (!$arCurrencyParams['CURRENCY_ID']
								? $arPrice['DISCOUNT_VALUE']
								: \CCurrencyRates::ConvertCurrency($arPrice['DISCOUNT_VALUE'], $arPrice['CURRENCY'], $arCurrencyParams['CURRENCY_ID'])
							);
							$result = $price_id = $arPrice["PRICE_ID"];
						}
						else
						{
							$comparePrice = (!$arCurrencyParams['CURRENCY_ID']
								? $arPrice['DISCOUNT_VALUE']
								: \CCurrencyRates::ConvertCurrency($arPrice['DISCOUNT_VALUE'], $arPrice['CURRENCY'], $arCurrencyParams['CURRENCY_ID'])
							);
							if ($minPrice > $comparePrice && $arPrice['CAN_BUY'] == 'Y')
							{
								$minPrice = $comparePrice;
								$result = $price_id = $arPrice["PRICE_ID"];
							}
						}
					}
				}

				if( (\CMax::GetFrontParametrValue('SHOW_POPUP_PRICE') == 'Y' || $arParams['ONLY_POPUP_PRICE'] == 'Y') && $arParams['SHOW_POPUP_PRICE'] != 'N')
				{
					foreach($arPrices as $key => $arPrice)
					{
						if($price_id == $arPrice["PRICE_ID"]):?>
							<?if($iCountPriceGroup > 1):?>
								<div class="js-show-info-block more-item-info rounded3 bordered-block text-center"><?=\CMax::showIconSvg("fw", SITE_TEMPLATE_PATH."/images/svg/dots.svg");?></div>
							<?endif;?>
							<?=\Aspro\Functions\CAsproMaxItem::showItemPrices($arParams, array($arPrice), $strMeasure, $price_id, $bShort, true)?>
						<?endif;
					}?>
					<div class="js-info-block rounded3">
						<div class="block_title text-upper font_xs font-bold">
							<?=Loc::getMessage('T_JS_PRICES_MORE')?>
							<?=\CMax::showIconSvg("close", SITE_TEMPLATE_PATH."/images/svg/Close.svg");?>
						</div>
						<div class="block_wrap">
							<div class="block_wrap_inner prices <?=($iCountPriceGroup > 1 ? 'srollbar-custom scroll-deferred' : '')?>">
					<?
				}
			}

			foreach($arPrices as $key => $arPrice):?>
				<?if($arPrice["CAN_ACCESS"]):?>
					<?$arPriceGroup = \CCatalogGroup::GetByID($arPrice["PRICE_ID"]);?>
					<?if($iCountPriceGroup > 1):?>
						<div class="price_group <?=($arPrice['MIN_PRICE'] == 'Y' ? 'min ' : '');?><?=$arPriceGroup['XML_ID']?>"><div class="price_name muted font_xs"><?=$arPriceGroup["NAME_LANG"]?></div>
					<?endif;?>
					<div class="price_matrix_wrapper <?=($arDiscountPrices && $iCountPriceGroup > 1 ? (isset($arDiscountPrices[$arPriceGroup['ID']]) ? 'strike_block' : '') : '');?>">
						<?if($arPrice["VALUE"] > $arPrice["DISCOUNT_VALUE"]):?>
							<div class="prices-wrapper">
								<div class="price font-bold <?=($arParams['MD_PRICE'] ? 'font_mlg' : 'font_mxs');?>" data-currency="<?=$arPrice["CURRENCY"];?>" data-value="<?=$arPrice["DISCOUNT_VALUE"];?>" <?=($bMinPrice ? ' itemprop="offers" itemscope itemtype="http://schema.org/Offer"' : '')?>>
									<?if($bMinPrice):?>
										<meta itemprop="price" content="<?=($arPrice['DISCOUNT_VALUE'] ? $arPrice['DISCOUNT_VALUE'] : $arPrice['VALUE'])?>" />
										<meta itemprop="priceCurrency" content="<?=$arPrice['CURRENCY']?>" />
										<link itemprop="availability" href="http://schema.org/<?=($arPrice['CAN_BUY'] ? 'InStock' : 'OutOfStock')?>" />
									<?endif;?>
									<?if(strlen($arPrice["PRINT_DISCOUNT_VALUE"])):?>
										<?=($arParams["MESSAGE_FROM"] ? $arParams["MESSAGE_FROM"] : '')?>
										<span class="values_wrapper">
											<?=\Aspro\Functions\CAsproMaxItem::getCurrentPrice("DISCOUNT_VALUE_VAT", $arPrice);?>
											<?/*if($arPrice['CURRENCY'] == 'RUB'):?>
												<span class="price_currency_text">руб.</span>
											<?endif;*/?>
										</span>
										<?if (($arParams["SHOW_MEASURE"]=="Y") && $strMeasure):?><span class="price_measure">/<?=$strMeasure?></span><?endif;?>
									<?endif;?>
								</div>
								<?if($arParams["SHOW_OLD_PRICE"]=="Y"):?>
									<div class="price discount" data-currency="<?=$arPrice["CURRENCY"];?>" data-value="<?=$arPrice["VALUE"];?>">
										<span class="values_wrapper <?=($arParams['MD_PRICE'] ? 'font_sm' : 'font_xs');?> muted">
											<?=\Aspro\Functions\CAsproMaxItem::getCurrentPrice("DISCOUNT_VALUE_VAT", $arPrice);?>
											<?/*if($arPrice['CURRENCY'] == 'RUB'):?>
												<span class="price_currency_text">руб.</span>
											<?endif;*/?>
										</span>
									</div>
								<?endif;?>
							</div>
							<?if($arParams["SHOW_DISCOUNT_PERCENT"]=="Y"):?>
								<div class="sale_block" data-currency="<?=$arPrice["CURRENCY"];?>">
									<div class="sale_wrapper font_xxs">
										<?if($bShort == 'Y'):?>
											<div class="inner-sale rounded1">
												<span class="title"><?=Loc::getMessage("CATALOG_ECONOMY");?></span>
												<div class="text"><span class="values_wrapper"><?=\Aspro\Functions\CAsproMaxItem::getCurrentPrice("DISCOUNT_DIFF", $arPrice);?></span></div>
											</div>
										<?else:?>
											<?
											if(isset($arPrice["DISCOUNT_DIFF_PERCENT"]))
												$percent = $arPrice["DISCOUNT_DIFF_PERCENT"];
											else
												$percent=round(($arPrice["DISCOUNT_DIFF"]/$arPrice["VALUE"])*100, 0);?>
											<div class="sale-number rounded2">
												<?if($percent && $percent<100):?>
													<div class="value">-<span><?=$percent;?></span>%</div>
												<?endif;?>
												<div class="inner-sale rounded1">
													<div class="text"><!--noindex--><?=Loc::getMessage("CATALOG_ECONOMY");?><!--/noindex--> <span class="values_wrapper"><?=\Aspro\Functions\CAsproMaxItem::getCurrentPrice("DISCOUNT_DIFF", $arPrice);?></span></div>
												</div>
											</div>
										<?endif;?>
									</div>
								</div>
							<?endif;?>
						<?else:?>
							<div class="price font-bold <?=($arParams['MD_PRICE'] ? 'font_mlg' : 'font_mxs');?>" data-currency="<?=$arPrice["CURRENCY"];?>" data-value="<?=$arPrice["VALUE"];?>">
								<?if(strlen($arPrice["PRINT_VALUE"])):?>
									<?=($arParams["MESSAGE_FROM"] ? $arParams["MESSAGE_FROM"] : '')?><span class="values_wrapper"><?=\Aspro\Functions\CAsproMaxItem::getCurrentPrice("VALUE", $arPrice);?></span><?if(($arParams["SHOW_MEASURE"]=="Y") && $strMeasure):?><span class="price_measure">/<?=$strMeasure?></span><?endif;?>
								<?endif;?>
							</div>
						<?endif;?>
					</div>
					<?if($iCountPriceGroup > 1):?>
						</div>
					<?endif;?>
				<?endif;?>
			<?endforeach;
			if(!$popup)
			{
				if( (\CMax::GetFrontParametrValue('SHOW_POPUP_PRICE') == 'Y' || $arParams['ONLY_POPUP_PRICE'] == 'Y') && $arParams['SHOW_POPUP_PRICE'] != 'N'):?>
							</div>
							<div class="more-btn text-center">
								<a href="" class="font_upper colored_theme_hover_bg"><?=Loc::getMessage("MORE_LINK")?></a>
							</div>
						</div>
					</div>
				<?endif;

				$html = ob_get_contents();
				ob_end_clean();

				//$html = '<div class="price_matrix_wrapper"><div class="prices-wrapper"></div></div>';

				if($bReturn)
					return $html;
				else
					echo $html;
			}
		}
	}

	public static function OnAsproSkuShowItemPricesHandler($arParams, $arItem, &$item_id, &$min_price_id, $arItemIDs, $bShort, &$html){
		$item_id = $MIN_PRICE_ID = 0;
		if((is_array($arParams) && $arParams) && (is_array($arItem) && $arItem))
		{
			ob_start();

			$minPrice = false;
			if (isset($arItem['MIN_PRICE']) || isset($arItem['RATIO_PRICE']))
				$minPrice = $arItem['MIN_PRICE'];

			$offer_id=0;
			if($arParams["TYPE_SKU"]=="N")
				$offer_id=$minPrice["MIN_ITEM_ID"];

			$min_price_id=$minPrice["MIN_PRICE_ID"];
			if(!$min_price_id)
				$min_price_id=$minPrice["PRICE_ID"];
			if($minPrice["MIN_ITEM_ID"])
				$item_id=$minPrice["MIN_ITEM_ID"];

			if($arItem["OFFERS"])
				$arTmpOffer = current($arItem["OFFERS"]);

			if(!$min_price_id)
				$min_price_id=$arTmpOffer["MIN_PRICE"]["PRICE_ID"];
			$item_id = $arTmpOffer["ID"];

			$prefix = '';
			if('N' == $arParams['TYPE_SKU'] || $arParams['DISPLAY_TYPE'] !== 'block' || empty($arItem['OFFERS_PROP']))
				$prefix = GetMessage("CATALOG_FROM");
			$str_price_id = $str_price_old_id = '';
			if($arItemIDs)
			{
				if(isset($arItemIDs["ALL_ITEM_IDS"]) && (isset($arItemIDs["ALL_ITEM_IDS"]['PRICE']) && $arItemIDs["ALL_ITEM_IDS"]['PRICE']))
					$str_price_id = 'id="'.$arItemIDs["ALL_ITEM_IDS"]['PRICE'].'"';
				if(isset($arItemIDs["ALL_ITEM_IDS"]) && (isset($arItemIDs["ALL_ITEM_IDS"]['DISCOUNT_PRICE']) && $arItemIDs["ALL_ITEM_IDS"]['DISCOUNT_PRICE']))
					$str_price_old_id = 'id="'.$arItemIDs["ALL_ITEM_IDS"]['DISCOUNT_PRICE'].'"';
			}
			?>
			<?$measure_block = \Aspro\Functions\CAsproMaxSku::getMeasureRatio($arParams, $minPrice, $arItem);?>
			<div class="price_matrix_wrapper">
				<div class="prices-wrapper">
					<?if($arParams["SHOW_OLD_PRICE"]=="Y"){?>
						<div class="price font-bold <?=($arParams['MD_PRICE'] ? 'font_mlg' : 'font_mxs');?>" <?=$str_price_id;?>>
							<?if(strlen($minPrice["PRINT_DISCOUNT_VALUE"])):?>
								<?//=$prefix;?> <span class="values_wrapper"><?=$minPrice["PRINT_DISCOUNT_VALUE"];?></span> <?=$measure_block;?>
							<?endif;?>
						</div>
						<?if($arParams["SHOW_OLD_PRICE"]=="Y"):?>
							<div class="price discount">
								<span class="values_wrapper <?=($arParams['MD_PRICE'] ? 'font_sm' : 'font_xs');?> muted" <?=(!$minPrice["DISCOUNT_DIFF"] ? 'style="display:none;"' : '')?>><?=$minPrice["PRINT_VALUE"];?></span>
							</div>
						<?endif;?>
					<?}else{?>
						<div class="price only_price font-bold <?=($arParams['MD_PRICE'] ? 'font_mlg' : 'font_mxs');?>" <?=$str_price_id;?>>
							<?if(strlen($minPrice["PRINT_DISCOUNT_VALUE"])):?>
								<?//=$prefix;?> <span class="values_wrapper"><?=$minPrice['PRINT_DISCOUNT_VALUE'];?></span> <?=$measure_block;?>
							<?endif;?>
						</div>
					<?}?>
				</div>
				<?if($arParams["SHOW_DISCOUNT_PERCENT"]=="Y"){?>
					<div class="sale_block" <?=(!$minPrice["DISCOUNT_DIFF"] ? 'style="display:none;"' : '')?>>
						<?if($minPrice["DISCOUNT_DIFF"]):?>
							<div class="sale_wrapper font_xxs">
								<?if($bShort == 'Y'):?>
									<div class="inner-sale rounded1">
										<span class="title"><!--noindex--><?=GetMessage("CATALOG_ECONOMY");?><!--/noindex--></span>
										<div class="text"><span class="values_wrapper"><?=$minPrice["PRINT_DISCOUNT_DIFF"];?></span></div>
									</div>
								<?else:?>
									<?$percent=round(($minPrice["DISCOUNT_DIFF"]/$minPrice["VALUE"])*100, 0);?>
									<div class="sale-number rounded2">
										<?if($percent && $percent<100){?>
											<div class="value">-<span><?=$percent;?></span>%</div>
										<?}?>
										<div class="inner-sale rounded1">
											<div class="text"><!--noindex--><?=GetMessage("CATALOG_ECONOMY");?><!--/noindex--> <span class="values_wrapper"><?=$minPrice["PRINT_DISCOUNT_DIFF"];?></span></div>
										</div>
									</div>
								<?endif;?>
								<div class="clearfix"></div>
							</div>
						<?endif;?>
					</div>
				<?}?>
			</div>

			<?
				$html = ob_get_contents();
				ob_end_clean();
			?>
		<?}
	}

	public static function OnAsproShowStickersHandler($arParams, $arItem, &$html){
		if($arItem){
			ob_start();

			$favItem = ($arParams["FAV_ITEM"] ? $arParams["FAV_ITEM"] : "FAVORIT_ITEM");
			$finalPrice = ($arParams["FINAL_PRICE"] ? $arParams["FINAL_PRICE"] : "FINAL_PRICE");
			$prop = ($arParams["STIKERS_PROP"] ? $arParams["STIKERS_PROP"] : "HIT");
			$saleSticker = ($arParams["SALE_STIKER"] ? $arParams["SALE_STIKER"] : "SALE_TEXT");
			$bDiscountSticker = $bChoosenSticker = false;
			
			if(!isset($arItem['MIN_BASIS_PRICE']['PERCENT']) && !empty($arItem['MIN_BASIS_PRICE']['PROCENT_SALE'])){
				$arItem['MIN_BASIS_PRICE']['PERCENT'] = $arItem['MIN_BASIS_PRICE']['PROCENT_SALE'];
			}
			
			if(!isset($arItem['MIN_BASIS_PRICE']['PERCENT']) && !empty($arItem['MIN_BASIS_PRICE']['DISCOUNT_DIFF_PERCENT'])){
				$arItem['MIN_BASIS_PRICE']['PERCENT'] = $arItem['MIN_BASIS_PRICE']['DISCOUNT_DIFF_PERCENT'];
			}

			if($arItem['MIN_BASIS_PRICE']['PERCENT'] > 0 && $arItem['MIN_BASIS_PRICE']['PERCENT'] < 100){
				$bDiscountSticker = true;
			}
			
			if(($arItem["PROPERTIES"][$favItem]["VALUE"] && $bShowFavItem) ||
				$arItem["PROPERTIES"][$finalPrice]["VALUE"] ||
				\CMax::GetItemStickers($arItem["PROPERTIES"][$prop]) ||
				$arItem["PROPERTIES"][$saleSticker]["VALUE"] ||
                $arItem["PROPERTIES"][$prop]['VALUE'] ||
				$bDiscountSticker)
			{?>
				<?if($wrapper):?>
					<div class="<?=$wrapper?>">
				<?endif;?>
				<div class="stickers custom-font">
					<?if($arItem["PROPERTIES"][$favItem]["VALUE"] && $bShowFavItem):?>
						<div><div class="sticker_sale_text font_sxs rounded2"><?=$arItem["PROPERTIES"][$favItem]["NAME"];?></div></div>
					<?endif;?>

					<?if($arItem["PROPERTIES"][$finalPrice]["VALUE"]):?>
						<div><div class="sticker_sale_text font_sxs rounded2"><?=$arItem["PROPERTIES"][$finalPrice]["NAME"];?></div></div>
					<?endif;?>

					<?foreach(\CMax::GetItemStickers($arItem["PROPERTIES"][$prop]) as $arSticker):?>
						<div><div class="<?=$arSticker['CLASS']?> font_sxs rounded2"><?=$arSticker['VALUE']?></div></div>
					<?endforeach;?>

					<?if($arItem["PROPERTIES"][$saleSticker]["VALUE"]):?>
						<div><div class="sticker_sale_text font_sxs rounded2"><?=$arItem["PROPERTIES"][$saleSticker]["VALUE"];?></div></div>
					<?endif;?>
					
					<?if($bDiscountSticker):?><?//oтображение стикера -50% ?>
						<div><div class="sticker_persent font_sxs rounded2">-<?=$arItem['MIN_BASIS_PRICE']['PERCENT'];?>%</div></div>
					<?endif;?>

					<?if($bChoosenSticker):?>
						<div><div class="sticker_choosen font_sxs rounded2">Выбирают</div></div>
					<?endif;?>
					<?if($arItem["PROPERTIES"]["SHOW_ICON_STANDART"]["VALUE"] && $arItem["IBLOCK_SECTION_ID"] != 599):?>
						<div class="delivery-icon">
							<img src="/images/delivery-icon.svg" alt="">
						</div>
					<?endif;?>

				</div>
				<?if($wrapper):?>
					</div>
				<?endif;
			}
			$html = ob_get_contents();
			ob_end_clean();
		}
	}

	public static function OnAsproShowSideFormLinkIconsHandler($bWrapper, &$html)
	{?>
		<?ob_start();?>
			<?if($bWrapper):?>
				<?global $arTheme?>
				<div class="basket_fly_forms basket_fill_<?=$arTheme['ORDER_BASKET_COLOR']['VALUE'];?>">
					<div class="wrap_cont">
						<div class="opener">
			<?endif;?>
			<?
			$callbackExploded = explode(',', \CMax::GetFrontParametrValue('SHOW_CALLBACK'));
			$bShowCallBackBlock = (in_array('SIDE', $callbackExploded));
			$questionExploded = explode(',', \CMax::GetFrontParametrValue('SHOW_QUESTION'));
			$bShowQuestionBlock = (in_array('SIDE', $questionExploded));
			$reviewExploded = explode(',', \CMax::GetFrontParametrValue('SHOW_REVIEW'));
			$bShowReviewBlock = (in_array('SIDE', $reviewExploded));
			?>
			<?if($bShowCallBackBlock):?>
				<div title="<?=GetMessage("CALLBACK");?>" class="colored_theme_hover_text">
					<span class="forms callback-block animate-load" data-event="jqm" data-param-form_id="CALLBACK" data-name="callback"></span>
					<div class="wraps_icon_block form callback">
						<?=\CMax::showIconSvg("icon", SITE_TEMPLATE_PATH.'/images/svg/callback.svg', '', '', true, false);?>
					</div>
				</div>
			<?endif;?>
			<?if($bShowReviewBlock):?>
				<div title="<?=GetMessage("REVIEW");?>" class="colored_theme_hover_text">
					<span class="forms review-block animate-load" data-event="jqm" data-param-form_id="GIVE_FEEDBACK" data-name="review"></span>
					<div class="wraps_icon_block form review">
						<?=\CMax::showIconSvg("icon", SITE_TEMPLATE_PATH.'/images/svg/review.svg', '', '', true, false);?>
					</div>
				</div>
			<?endif;?>
			<?if($bShowQuestionBlock):?>
				<div title="<?=GetMessage("ASK");?>" class="colored_theme_hover_text">
					<span class="forms ask-block animate-load" data-event="jqm" data-param-form_id="ASK" data-name="ask"></span>
					<div class="wraps_icon_block ask">
						<?=\CMax::showIconSvg("icon", SITE_TEMPLATE_PATH.'/images/svg/ask.svg', '', '', true, false);?>
					</div>
				</div>
			<?endif;?>

			<?if($bWrapper):?>
						</div>
					</div>
				</div>
			<?endif;?>
		<?$html = ob_get_contents();
		ob_end_clean();

		if(!$bReturn)
			echo $html;
		else
			return $html;?>
	<?}

	public static function OnAsproShowDiscountCounterHandler($totalCount, $arDiscount, $arQuantityData, $arResult, $strMeasure, $type, &$html)
	{
		if(!$arDiscount && $item_id)
		{
			global $USER;
			$arUserGroups = $USER->GetUserGroupArray();?>
			<?$arDiscounts = \CCatalogDiscount::GetDiscountByProduct($item_id, $arUserGroups, "N", array(), SITE_ID);?>
			<?$arDiscount=array();
			if($arDiscounts)
				$arDiscount=current($arDiscounts);
		}
		
		
		$bShowDiscont = ($arDiscount && $arDiscount["ACTIVE_TO"] && time() <= strtotime($arDiscount["ACTIVE_TO"]));

		
		$bOffers = $arResult["OFFERS"] && $arResult["OFFERS_PROP"];
		$bNeedHideDiscount = !$bShowDiscont && $bOffers;

		//$bNeedHideDiscount = false;
		/*if($arResult["OFFERS"] && $arResult["OFFERS_PROP"] && !$bShowDiscont){
			foreach ($arResult["OFFERS"] as $tmpOffer ) {
				$arOfferDiscount = \CCatalogDiscount::GetDiscountByProduct($tmpOffer["ID"], $arUserGroups, "N", array(), SITE_ID);
				if($arOfferDiscount){
					$bNeedHideDiscount = true;
					break;
				}
			}
		}*/

		$html = '';
		$bShowDiscont = false;
		$bOffers = false;
		
		if( $bShowDiscont || $bOffers):?>
			<?ob_start();?>
			<?if($bWrapper):?>
				<div class="view_sale_block_wrapper">
			<?endif;?>
				<div class="view_sale_block <?=$type?> <?=($arQuantityData["HTML"] ? '' : 'wq');?> <?=$bNeedHideDiscount ? 'init-if-visible' : '' ?>" <?=$bNeedHideDiscount ? 'style="display:none;"' : '' ?> >
					<?if($type != 'compact'):?>
						<div class="icons">
							<div class="values">
								<span class="item"><?=\CMax::showIconSvg("timer", SITE_TEMPLATE_PATH."/images/svg/timer.svg");?></span>
							</div>
						</div>
					<?endif;?>
					<div class="count_d_block">
						<span class="active_to hidden"><?=$arDiscount["ACTIVE_TO"];?></span>
						<span class="countdown values"><span class="item">0</span><span class="item">0</span><span class="item">0</span><span class="item">0</span></span>
					</div>
					<?if($arQuantityData["HTML"]):?>
						<div class="quantity_block">
							<div class="values">
								<span class="item">
									<span class="value" <?=(($arResult["OFFERS"] && $arParams["TYPE_SKU"] == 'TYPE_1' && $arResult["OFFERS_PROP"]) ? 'style="opacity:0;"' : '')?>><?=$totalCount;?></span>
									<span class="text"><?=($strMeasure ? $strMeasure : GetMessage("TITLE_QUANTITY"));?></span>
								</span>
							</div>
						</div>
					<?endif;?>
				</div>
			<?if($bWrapper):?>
				</div>
			<?endif;?>
			<?$html = ob_get_contents();
			ob_end_clean();
		endif;
	}

	public static function OnAsproRegionalityGetCurrentRegionHandler($arTheme, $arRegions, &$arRegion)
	{
		if(!$arRegion){
			if(isset($_COOKIE['current_region']) && $_COOKIE['current_region']){
				if(isset($arRegions[$_COOKIE['current_region']]) && $arRegions[$_COOKIE['current_region']]){
					$arRegion = $arRegions[$_COOKIE['current_region']];
				}
			}
			else{
				foreach($arRegions as $arItem){
					if(in_array($_SERVER['SERVER_NAME'], $arItem['LIST_DOMAINS']) || in_array($_SERVER['HTTP_HOST'], $arItem['LIST_DOMAINS'])){
						$arRegion = $arItem;
						break;
					}
				}
			}

			// Определение региона по IP
			$cityName   = Helper::getGeoByIp()->cityName;
			$isRedirect = \Cosmos\Config::getInstance()->getParam('COMMON')['redirect_to_region'] === 'Y';

            if ($cityName && !$_COOKIE['current_region']) {
                foreach ($arRegions as $arItem) {
                    if ($arItem['NAME'] == $cityName) {
                        $arRegion = $arItem;

                        if ($isRedirect && ($_SERVER['SERVER_NAME'] != $arRegion['PROPERTY_MAIN_DOMAIN_VALUE'])) {
                            $scheme = \Bitrix\Main\Context::getCurrent()->getRequest()->isHttps() ? 'https://' : 'http://';
                            $currentUrl = $scheme . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
                            $redirectTo = str_replace($_SERVER['SERVER_NAME'], $arRegion['PROPERTY_MAIN_DOMAIN_VALUE'], $currentUrl);
							$url = $_SERVER['REQUEST_URI'];
							
							if (!empty($_SERVER['SERVER_NAME']) && $redirectTo !== 'http://' && !empty($arRegion['PROPERTY_MAIN_DOMAIN_VALUE']) && strpos($url, '/bitrix/') === false) {
								LocalRedirect($redirectTo, true, '301 Moved permanently');
								exit;
							}
                        }
                        break;
                    }
                }
            }

			if(!$arRegion){
				foreach($arRegions as $arItem){
					if($arItem['PROPERTY_DEFAULT_VALUE'] === 'Y'){
						$arRegion = $arItem;
						break;
					}
				}
			}

			if(!$arRegion){
				$arRegion = reset($arRegions);
			}
		}

		if(!empty($arRegion)){
			$arNearestRegion = array();
			if(!empty($arRegion['PROPERTY_REGION_TAG_NEAREST_REGION_VALUE']) && !empty($arRegions[$arRegion['PROPERTY_REGION_TAG_NEAREST_REGION_VALUE']])){
				$arNearestRegion = $arRegions[$arRegion['PROPERTY_REGION_TAG_NEAREST_REGION_VALUE']];
			}

			if(!empty($arNearestRegion)){

				$sStoreId = '';
				$sStoreId = end($arRegion['LIST_STORES']);

				if(empty($sStoreId) || $sStoreId == 'component'){
					if(!empty($arNearestRegion['LIST_STORES'])){
						$arRegion['LIST_STORES'] = $arNearestRegion['LIST_STORES'];
					}
				}

				$arPhones = '';
				$arPhones = end($arRegion['PHONES']);

				if(empty($arPhones['PHONE'])){
					$arRegion['PHONES'] = $arNearestRegion['PHONES'];
				}
			}
		}
	}
}