<?
use \Bitrix\Main\Config\Option,
	\Bitrix\Main\Web\Json;

class CAsproMaxItemCustom extends Aspro\Functions\CAsproMaxItem 
{

	public static function showItemPrices($arParams = array(), $arPrices = array(), $strMeasure = '', &$price_id = 0, $bShort = 'N', $popup = false, $bReturn = false){
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

					/*get currency for convert*/
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
					/**/
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
							<?=self::showItemPrices($arParams, array($arPrice), $strMeasure, $price_id, $bShort, true)?>
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
										<?=($arParams["MESSAGE_FROM"] ? $arParams["MESSAGE_FROM"] : '')?><span class="values_wrapper">
											<?=self::getCurrentPrice("DISCOUNT_VALUE", $arPrice);?>
										</span><?if (($arParams["SHOW_MEASURE"]=="Y") && $strMeasure):?><span class="price_measure">/<?=$strMeasure?></span><?endif;?>
									<?endif;?>
								</div>
								<?if($arParams["SHOW_OLD_PRICE"]=="Y"):?>
									<div class="price discount" data-currency="<?=$arPrice["CURRENCY"];?>" data-value="<?=$arPrice["VALUE"];?>">
										<span class="values_wrapper <?=($arParams['MD_PRICE'] ? 'font_sm' : 'font_xs');?> muted"><?=self::getCurrentPrice("VALUE", $arPrice);?></span>
									</div>
								<?endif;?>
							</div>
							<?if($arParams["SHOW_DISCOUNT_PERCENT"]=="Y"):?>
								<div class="sale_block">
									<div class="sale_wrapper font_xxs">
										<?if($bShort == 'Y'):?>
											<div class="inner-sale rounded1">
												<span class="title"><?=Loc::getMessage("CATALOG_ECONOMY");?></span>
												<div class="text"><span class="values_wrapper"><?=self::getCurrentPrice("DISCOUNT_DIFF", $arPrice);?></span></div>
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
													<div class="text"><?=Loc::getMessage("CATALOG_ECONOMY");?> <span class="values_wrapper"><?=self::getCurrentPrice("DISCOUNT_DIFF", $arPrice);?></span></div>
												</div>
											</div>
										<?endif;?>
									</div>
								</div>
							<?endif;?>
						<?else:?>
							<div class="price font-bold <?=($arParams['MD_PRICE'] ? 'font_mlg' : 'font_mxs');?>" data-currency="<?=$arPrice["CURRENCY"];?>" data-value="<?=$arPrice["VALUE"];?>">
								<?if(strlen($arPrice["PRINT_VALUE"])):?>
									<?=($arParams["MESSAGE_FROM"] ? $arParams["MESSAGE_FROM"] : '')?><span class="values_wrapper"><?=self::getCurrentPrice("VALUE", $arPrice);?></span><?if(($arParams["SHOW_MEASURE"]=="Y") && $strMeasure):?><span class="price_measure">/<?=$strMeasure?></span><?endif;?>
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

				foreach(GetModuleEvents(FUNCTION_MODULE_ID, 'OnAsproItemShowItemPrices', true) as $arEvent) // event for manipulation item prices
					ExecuteModuleEventEx($arEvent, array($arParams, $arPrices, $strMeasure, &$price_id, $bShort, &$html));

				if($bReturn)
					return $html;
				else
					echo $html;
			}
		}
	}

}
?>