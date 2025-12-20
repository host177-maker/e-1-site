<?
namespace Aspro\Functions;

use Bitrix\Main\Application;
use Bitrix\Main\Web\DOM\Document;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\DOM\CssParser;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\IO\File;
use Bitrix\Main\IO\Directory;

Loc::loadMessages(__FILE__);
\Bitrix\Main\Loader::includeModule('sale');
\Bitrix\Main\Loader::includeModule('catalog');

if(!defined('FUNCTION_MODULE_ID'))
	define('FUNCTION_MODULE_ID', 'aspro.max');

if(!class_exists("CAsproMaxSku"))
{
	class CAsproMaxSku{

		public static function getMeasureRatio($arParams = array(), $minPrice = array(), $arItem){
			$measure_block = '';
			if((is_array($arParams) && $arParams)&& (is_array($minPrice) && $minPrice))
			{
				if($arParams["SHOW_MEASURE"]=="Y" && $arParams["SHOW_MEASURE_WITH_RATIO"] == "Y")
				{
					$measure_block = "<span class=\"price_measure\">/";
					if (isset($minPrice["CATALOG_MEASURE_RATIO"]) && $minPrice["CATALOG_MEASURE_RATIO"] != 1) {
						$measure_block .= $minPrice["CATALOG_MEASURE_RATIO"]." ";
						$measure_block .= $minPrice["CATALOG_MEASURE_NAME"];
					} elseif ($arItem['OFFERS'] && $arItem['OFFERS'][0]['ITEM_MEASURE']) {
						$measure_block .= $arItem['OFFERS'][0]['ITEM_MEASURE']["TITLE"]." ";
					} else {
						$measure_block .= $minPrice["CATALOG_MEASURE_NAME"];
					}
					$measure_block .= "</span>";
				}
			}
			return $measure_block;
		}

		public static function showItemPrices($arParams = array(), $arItem = array(), &$item_id = 0, &$min_price_id = 0, $arItemIDs = array(), $bShort = 'N', $bReturn = false){
			$item_id = $MIN_PRICE_ID = 0;
			if((is_array($arParams) && $arParams) && (is_array($arItem) && $arItem))
			{
				ob_start();

				$minPrice = false;
				if (isset($arItem['MIN_PRICE']) || isset($arItem['RATIO_PRICE']))
					$minPrice = $arItem['MIN_PRICE'];

				$min_price_id=$minPrice["MIN_PRICE_ID"] ?? $minPrice["PRICE_ID"];
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
					
				}

                $priceDiscountPrint = str_replace('руб.', '₽', $minPrice["PRINT_VALUE"]);
				?>

				<div class="price_matrix_wrapper">
					<div class="prices-wrapper">
						<?if($arParams["SHOW_OLD_PRICE"]=="Y"){?>
							<div class="price font-bold <?=($arParams['MD_PRICE'] ? 'font_mlg' : 'font_mxs');?>" <?=$str_price_id;?>>
								<?if(strlen($minPrice["PRINT_DISCOUNT_VALUE_VAT"])):?>
									<span class="values_wrapper"><?=str_replace('руб.', '₽', $minPrice["PRINT_DISCOUNT_VALUE_VAT"]);?></span> 
								<?elseif(strlen($minPrice["PRINT_DISCOUNT_VALUE"])):?>
									 <span class="values_wrapper"><?=str_replace('руб.', '₽', $minPrice["PRINT_DISCOUNT_VALUE"]);?></span> 
								<?endif;?>
							</div>
							<div class="price discount">
								<span class="values_wrapper <?=($arParams['MD_PRICE'] ? 'font_sm' : 'font_xs');?> muted" <?=(!$minPrice["DISCOUNT_DIFF"] ? 'style="display:none;"' : '')?>>
                                    <?= $priceDiscountPrint ?>
                                </span>
							</div>
						<?}else{?>
							<div class="price only_price font-bold <?=($arParams['MD_PRICE'] ? 'font_mlg' : 'font_mxs');?>" <?=$str_price_id;?>>
								<?if(strlen($minPrice["PRINT_DISCOUNT_VALUE_VAT"])):?>
									<?=$prefix;?> <span class="values_wrapper"><?=$minPrice['PRINT_DISCOUNT_VALUE_VAT'];?></span>
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
											<div class="text"><span class="values_wrapper"><?=$minPrice["PRINT_DISCOUNT_VALUE_VAT"];?></span></div>
										</div>
									<?else:?>
										<?$percent=round(($minPrice["DISCOUNT_DIFF"]/$minPrice["VALUE"])*100, 0);?>
										<div class="sale-number rounded2">
											<?if($percent && $percent<100){?>
												<div class="value">-<span><?=$percent;?></span>%</div>
											<?}?>
											<div class="inner-sale rounded1">
												<div class="text"><!--noindex--><?=GetMessage("CATALOG_ECONOMY");?> <!--/noindex--><span class="values_wrapper"><?=$minPrice["PRINT_DISCOUNT_VALUE_VAT"];?></span></div>
											</div>
										</div>
									<?endif;?>
									<div class="clearfix"></div>
								</div>
							<?endif;?>
						</div>
					<?}?>
				</div>

				<?$html = ob_get_contents();
				ob_end_clean();

                if($bReturn) {
                    return $html;
                }
                else {
                    echo $html;
                }
				?>

			<?}
		}
	}
}?>