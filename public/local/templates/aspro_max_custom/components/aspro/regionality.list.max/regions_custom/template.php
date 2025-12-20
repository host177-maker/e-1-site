<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */

$this->setFrameMode(true);

use \Bitrix\Main\Localization\Loc;?>

<?if(!$arResult['POPUP']):?>
	<?if($arResult['CURRENT_REGION']):?>
		<div class="region_wrapper">
			<div class="io_wrapper">
				<?=CMax::showIconSvg("mark", SITE_TEMPLATE_PATH."/images/svg/location.svg");?>
				<div class="city_title"><?=Loc::getMessage('CITY_TITLE');?></div>
				<div class="js_city_chooser jsCityCurrentSelect  animate-load  io_wrapper" data-event="jqm" data-name="city_chooser_small" data-param-url="<?=urlencode($APPLICATION->GetCurUri());?>" data-param-form_id="city_chooser">
					<span><?=$arResult['CURRENT_REGION']['NAME'];?></span><span class="arrow"><?=CMax::showIconSvg("down", SITE_TEMPLATE_PATH."/images/svg/trianglearrow_down.svg");?></span>
				</div>
			</div>
			<?if($arResult['SHOW_REGION_CONFIRM']):?>
				<div class="confirm_region">
					<span class="close colored_theme_hover_text " data-id="<?=$arResult['CURRENT_REGION']['ID'];?>"><?=CMax::showIconSvg('', SITE_TEMPLATE_PATH.'/images/svg/Close.svg', '', 'light-ignore')?></span>
					<div class="title"><?=Loc::getMessage('CITY_TITLE');?> <?= (!empty($arResult['REAL_REGION']['NAME'])) ? $arResult['REAL_REGION']['NAME']: $arResult['CURRENT_REGION']["NAME"]?> ?</div>
					<div class="buttons">
						<span class="btn btn-default aprove" data-id="<?=$arResult['REAL_REGION']['ID'];?>"><?=Loc::getMessage('CITY_YES');?></span>
						<span class="btn btn-default white js_city_change"><?=Loc::getMessage('CITY_CHANGE');?></span>
					</div>
				</div>
			<?endif;?>
		</div>
	<?endif;?>
<?else:?>
	<div class="popup_regions_wrap">
		<?$onlySearchRow = \Bitrix\Main\Config\Option::get('aspro.max', 'REGIONALITY_SEARCH_ROW', 'N') == 'Y';?>
		<div class="popup_regions <?=($onlySearchRow ? 'only_search' : '')?>">
			<div class="popup_regions-header">
				<div class="popup_regions-header-body row">
					<div class="h-search autocomplete-block col-md-4" id="title-search-city">
						<div class="wrapper">
							<input id="search" class="autocomplete text" type="text" placeholder="<?=Loc::getMessage('CITY_PLACEHOLDER');?>">
							<div class="search_btn"><?=CMax::showIconSvg("search2", SITE_TEMPLATE_PATH."/images/svg/Search.svg");?></div>
						</div>
						<?if($arResult['FAVORITS']):?>
							<div class="favorits">
								<span class="title"><?=GetMessage('EXAMPLE_CITY');?></span>
								<div class="cities">
									<?foreach($arResult['FAVORITS'] as $arItem):?>
										<div class="item">
											<a href="/<?=$arItem['CODE'];?>/" data-id="<?=$arItem['ID'];?>" data-code="<?=$arItem['CODE'];?>" class="name"><?=$arItem['NAME'];?></a>
										</div>
									<?endforeach;?>
								</div>
							</div>
						<?endif;?>
					</div>

					<div class="popup_regions-sort col-md-4">
			            <?if(!empty($arResult['ALPHABET_REGIONS'])):?><div class="popup_regions-sort-item active" data-id="alphabet"><span>По алфавиту</span></div><?endif;?>
			            <?if(!empty($arResult['SECTIONS_REGIONS'])):?><div class="popup_regions-sort-item" data-id="sections"><span>По регионам</span></div><?endif;?>
			        </div>

					<div class="popup_regions-filter col-md-4">
			            <div class="popup_regions-filter-item" data-id="salon"><i class="icon"></i><span>Города с салоном-магазином</span></div>
			        </div>
			    </div>
		    </div>

		    <div class="clear"></div>
			<?
				$bShowInDeliveryLegend = false;
				$bShowSalonLegend = false;
			?>
			<?if(!empty($arResult['ALPHABET_REGIONS'])):?>
				<div class="items only_city active" data-id="alphabet">
					<div class="block cities">
						<div class="items_block">
							<?foreach($arResult['ALPHABET_REGIONS'] as $sAlphabetKey => $arAlphabet):?>
								<?if(!empty($arAlphabet['ITEMS'])):?>
									<div class="items_block_body">
										<div class="items_block_item">
											<div class="block-title"><?=$arAlphabet['NAME'];?></div>
											<?foreach($arAlphabet['ITEMS'] as $key => $arItem):?>
												<?$bCurrent = ($arResult['CURRENT_REGION']['ID'] == $arItem['ID']);?>

												<?if(!$bShowInDeliveryLegend && $arItem['PROPERTY_REGION_TAG_IN_DELIVERY_VALUE'] == 'Да'){
													$bShowInDeliveryLegend = true;
												}?>
												<?if(!$bShowSalonLegend && $arItem['PROPERTY_REGION_TAG_SALON_SHOP_VALUE'] == 'Да'){
													$bShowSalonLegend = true;
												}?>

												<?	
												$sDomain = $arItem['PROPERTY_MAIN_DOMAIN_VALUE'];
												if(
													!empty($arItem['PROPERTY_REGION_TAG_NEAREST_REGION_VALUE']) && !empty($arResult['REGIONS'][$arItem['PROPERTY_REGION_TAG_NEAREST_REGION_VALUE']]['PROPERTY_MAIN_DOMAIN_VALUE'])
													&& empty($arItem['PROPERTY_MAIN_DOMAIN_VALUE'])
												){
													$sDomain = $arResult['REGIONS'][$arItem['PROPERTY_REGION_TAG_NEAREST_REGION_VALUE']]['PROPERTY_MAIN_DOMAIN_VALUE'];
												}
												?>
												<div class="item <?=($bCurrent ? 'current' : '');?> <?=$arItem['PROPERTY_REGION_TAG_IN_DELIVERY_VALUE'] == 'Да' ? 'in_delivery' : ($arItem['PROPERTY_REGION_TAG_SALON_SHOP_VALUE'] == 'Да' ? 'salon' : 'default')?> <?=(!empty($arItem['PROPERTY_MAIN_DOMAIN_VALUE'])) ? 'domain' : ''?>" data-id="<?=((isset($arItem['IBLOCK_SECTION_ID']) && $arItem['IBLOCK_SECTION_ID']) ? $arItem['IBLOCK_SECTION_ID'] : 0);?>">
													<?if($bCurrent):?>
														<div class="item-link" data-id="<?=$arItem['ID'];?>" data-code="<?=$arItem['CODE'];?>" ><i class="icon"></i><span class="name"><?=$arItem['NAME'];?></span></div>
													<?else:?>
														<div class="item-link" data-id="<?=$arItem['ID'];?>" data-code="<?=$arItem['CODE'];?>" class="name dark_link"><i class="icon"></i><span><?=$arItem['NAME'];?></span></div>
													<?endif;?>
												</div>
											<?endforeach;?>
										</div>
									</div>
								<?endif;?>
							<?endforeach;?>
						</div>
					</div>
				</div>
			<?endif;?>

			<?if(!empty($arResult['SECTIONS_REGIONS'])):?>
				<div class="items only_city" data-id="sections">
					<div class="block cities">
						<div class="items_block">
							<?foreach($arResult['SECTIONS_REGIONS'] as $sSectionKey => $arSection):?>
								<?if(!empty($arSection['ITEMS'])):?>
									<div class="items_block_body">
										<div class="items_block_item">
											<div class="block-title"><?=$arSection['NAME'];?></div>
											<?foreach($arSection['ITEMS'] as $key => $arItem):?>
												<?$bCurrent = ($arResult['CURRENT_REGION']['ID'] == $arItem['ID']);?>

												<?	
												$sDomain = $arItem['PROPERTY_MAIN_DOMAIN_VALUE'];
												if(
													!empty($arItem['PROPERTY_REGION_TAG_NEAREST_REGION_VALUE']) && !empty($arResult['REGIONS'][$arItem['PROPERTY_REGION_TAG_NEAREST_REGION_VALUE']]['PROPERTY_MAIN_DOMAIN_VALUE'])
													&& empty($arItem['PROPERTY_MAIN_DOMAIN_VALUE'])
												){
													$sDomain = $arResult['REGIONS'][$arItem['PROPERTY_REGION_TAG_NEAREST_REGION_VALUE']]['PROPERTY_MAIN_DOMAIN_VALUE'];
												}
												?>
												<div class="item <?=($bCurrent ? 'current' : '');?> <?=$arItem['PROPERTY_REGION_TAG_IN_DELIVERY_VALUE'] == 'Да' ? 'in_delivery' : ($arItem['PROPERTY_REGION_TAG_SALON_SHOP_VALUE'] == 'Да' ? 'salon' : 'default')?> <?=(!empty($arItem['PROPERTY_MAIN_DOMAIN_VALUE'])) ? 'domain' : ''?>" data-id="<?=((isset($arItem['IBLOCK_SECTION_ID']) && $arItem['IBLOCK_SECTION_ID']) ? $arItem['IBLOCK_SECTION_ID'] : 0);?>">
													<?if($bCurrent):?>
														<div class="item-link" data-id="<?=$arItem['ID'];?>" data-code="<?=$arItem['CODE'];?>" ><i class="icon"></i><span class="name"><?=$arItem['NAME'];?></span></div>
													<?else:?>
														<div class="item-link" data-id="<?=$arItem['ID'];?>" data-code="<?=$arItem['CODE'];?>" class="name dark_link"><i class="icon"></i><span><?=$arItem['NAME'];?></span></div>
													<?endif;?>
												</div>
											<?endforeach;?>
										</div>
									</div>
								<?endif;?>
							<?endforeach;?>
						</div>
					</div>
				</div>
			<?endif;?>

			<div class="popup_regions-footer">
				<div class="popup_regions-footer-body row">
					<?if($bShowSalonLegend):?>
						<div class="popup_regions-footer-item salon col-md-4">
				            <i class="icon"></i> - есть доставка и сборка
				        </div>
				    <?endif;?>

					<div class="popup_regions-footer-item default col-md-4">
			            <i class="icon"></i> - доставка без поднятия на этаж
			        </div>

					<?if($bShowInDeliveryLegend):?>
						<div class="popup_regions-footer-item in_delivery col-md-4">
				            <i class="icon"></i> - есть партнерская доставка
				        </div>
				    <?endif;?>
			    </div>
		    </div>

			<?if(\Bitrix\Main\Config\Option::get('aspro.max', 'REGIONALITY_SEARCH_ROW', 'N') != 'Y'):?>
				<script>
					$(document).ready(function(){
						$(".popup_regions-sort-item").on("click", function(){
							if(!$(this).is('.active')){
								$(".popup_regions-sort-item").removeClass('active');
								$(".popup_regions .items").removeClass('active');
								var type = $(this).attr('data-id');
								$('.popup_regions-sort-item[data-id = ' + type + ']').addClass('active');
								$('.popup_regions .items[data-id = ' + type + ']').addClass('active').fadeIn('600');
							}
						});

						$(".popup_regions-filter-item").on("click", function(){
							$(this).toggleClass('active');
							var type = $(this).attr('data-id');
							$('.popup_regions .items .item').addClass('hidden');
							if($(this).is('.active')){
								$('.popup_regions .items .item.' + type).removeClass('hidden');
							}
							else{
								$('.popup_regions .items .item').removeClass('hidden');
							}
							$('.items_block_body').each(function(index, el) {
								if($(el).find('.item').is(':not(.hidden)')){
									$(el).removeClass('hidden');
								}
								else{
									$(el).addClass('hidden');					
								}
							});
						});

						$(".popup_regions .item .item-link").on("click", function(){
							var _this = $(this);
							const curentRegionCode = $.cookie('current_region_code');

							const currentUrl = new URL(location.href);
							// убрать из currentUrl параметр региона (curentRegionCode)
							let newPath;
							if (typeof curentRegionCode !== 'undefined') {
								newPath = currentUrl.pathname.replace(`/${curentRegionCode}/`, '/');
							}

							$.removeCookie('current_region');
							$.removeCookie('current_region_code');
							$.cookie('current_region', _this.attr('data-id'), {path: '/'});
							$.cookie('current_region_code', _this.attr('data-code'), {path: '/'});

							// Проверяем, есть ли код региона
							if (typeof _this.attr('data-code') !== 'undefined') {
								
								// Определяем, нужно ли добавлять регион в URL
								const allowedPaths = ['/', '/catalog/', '/contacts/'];
								let shouldAddRegion = false;
								
								// Проверяем, начинается ли текущий путь с одного из разрешенных
								for (const path of allowedPaths) {
									if (newPath === path || 
										newPath.includes(path) && 
										(path !== '/' || newPath === '/')) {
										shouldAddRegion = true;
										break;
									}
								}

								let dataCode = _this.attr('data-code') ;
								if (dataCode && dataCode !== 'moskva' && dataCode !== '') {
									dataCode += '/';
								} else {
									dataCode = '';
								}

								let partOfPath = currentUrl.pathname.substring(1);
								
								if (shouldAddRegion && curentRegionCode && curentRegionCode !== '' && curentRegionCode  !== 'moskva') {
									if (currentUrl.pathname.startsWith(`/${curentRegionCode}/`)) {
										partOfPath = currentUrl.pathname.substring(curentRegionCode.length + 2);
									}

									newPath = currentUrl.origin + '/' + dataCode  + partOfPath;
									const newUrl = new URL(newPath, currentUrl.origin);
									newUrl.search = currentUrl.search; // сохраняем query-параметры
									newUrl.hash = currentUrl.hash;    // сохраняем хэш
	
									const resultUrl = newUrl.toString();
									location.href = resultUrl;
								} else if(!shouldAddRegion){
									location.reload();
								}

							}

							return false;
						});


					});
					var arRegions = <?=CUtil::PhpToJsObject($arResult['JS_REGIONS']);?>;
				</script>
			<?else:?>
				<script>
					var arRegions = [];
				</script>
			<?endif;?>
		</div>
	</div>
<?endif;?>
