<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true ) die();?>
<?$this->setFrameMode(true);?>
<?use \Bitrix\Main\Localization\Loc;?>
<?if($arResult['SECTIONS']):?>
	<div class="item-views staff1 <?=($arParams['LINKED_MODE'] == 'Y' ? 'linked' : '')?> within">
		<div class="row">
			<div class="col-md-12">
				<?
					$col = ($arParams['COUNT_IN_LINE'] ? $arParams['COUNT_IN_LINE'] : 3);
					$size = floor(12/$col);
					$size_md = floor(12/($col-1));
				?>
				<div class="group-content">
					<div class="items row flexbox">
						<?foreach($arResult['ITEMS'] as $i => $arItem):?>
							<?
							// edit/add/delete buttons for edit mode
							$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem['IBLOCK_ID'], 'ELEMENT_EDIT'));
							$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem['IBLOCK_ID'], 'ELEMENT_DELETE'), array('CONFIRM' => Loc::getMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
							// use detail link?
							$bDetailLink = $arParams['SHOW_DETAIL_LINK'] != 'N' && (!strlen($arItem['DETAIL_TEXT']) ? ($arParams['HIDE_LINK_WHEN_NO_DETAIL'] !== 'Y' && $arParams['HIDE_LINK_WHEN_NO_DETAIL'] != 1) : true);
							// preview image
							$firstPopUpImg = '';
							$firstCaptionValue = '';
							$bImage = strlen($arItem['FIELDS']['PREVIEW_PICTURE']['SRC']);
							$imageSrc = ($bImage ? $arItem['FIELDS']['PREVIEW_PICTURE']['SRC'] : SITE_TEMPLATE_PATH.'/images/svg/noimage_content.svg');
							$arTwoDoorGallery = $arItem["DISPLAY_PROPERTIES"]["DOOR_2"]["VALUE"];
							$arThreeDoorGallery = $arItem["DISPLAY_PROPERTIES"]["DOOR_3"]["VALUE"];
							
							$arGalley['ITEMS'] = (count($arTwoDoorGallery) > 0 ? $arTwoDoorGallery : $arThreeDoorGallery);
							$arGalley['TYPE'] = (count($arTwoDoorGallery) > 0 ? "2" : "3");
							
							if(empty($firstPopUpImg) && !empty($arTwoDoorGallery[0])){
								$arImg = CFile::GetFileArray($arTwoDoorGallery[0]);
								$firstPopUpImg = $arImg["SRC"];
								$firstCaptionValue = (!empty($arImg["DESCRIPTION"]) ? $arImg["DESCRIPTION"] : "1 000 мм");
							}elseif(empty($firstPopUpImg) && !empty($arThreeDoorGallery[0])){
								$arImg = CFile::GetFileArray($arThreeDoorGallery[0]);
								$firstPopUpImg = $arImg["SRC"];
								$firstCaptionValue = (!empty($arImg["DESCRIPTION"]) ? $arImg["DESCRIPTION"] : "1 500 мм");
							}else{
								$firstCaptionValue = $arItem['PREVIEW_PICTURE']["DESCRIPTION"];
								$firstPopUpImg = $arItem['PREVIEW_PICTURE']["SRC"];
							}

							$bShowMessButton = isset($arItem['DISPLAY_PROPERTIES']['SEND_MESSAGE_BUTTON']) && $arItem['DISPLAY_PROPERTIES']['SEND_MESSAGE_BUTTON']['VALUE_XML_ID'] == 'Y';
							
							// show active date period
							$bActiveDate = strlen($arItem['DISPLAY_PROPERTIES']['PERIOD']['VALUE']) || ($arItem['DISPLAY_ACTIVE_FROM'] && in_array('DATE_ACTIVE_FROM', $arParams['FIELD_CODE']));
							?>
							<div class="col-lg-<?=$size;?> col-md-<?=$size_md;?> col-sm-4 col-xs-6 item-wrap">
								<div class="item " data-id="<?=$arItem['ID'];?>">
									<div class="wrap  rounded3 box-shadow clearfix" id="<?=$this->GetEditAreaId($arItem['ID']);?>">
										<?if($imageSrc):?>
											<div class="image<?=($bImage ? "" : " wti" );?>">
												<div class="wrap">
													<a data-fancybox="gallery-<?=$arItem['ID'];?>" data-caption="<?=$firstCaptionValue?>" class="popup_link" href="<?=$firstPopUpImg?>">
														<?$img = ($bImage ? CFile::ResizeImageGet($arItem['PREVIEW_PICTURE']['ID'], array('width' => 560, 'height' => 10000), BX_RESIZE_IMAGE_PROPORTIONAL_ALT, true) : array());?>
														<?$img['src'] = (strlen($img['src']) ? $img['src'] : SITE_TEMPLATE_PATH.'/images/svg/noimage_content.svg');?>
														<img class="img-responsive lazy" data-src="<?=$img['src']?>" src="<?=\Aspro\Functions\CAsproMax::showBlankImg($img['src']);?>" alt="<?=($bImage ? $arItem['PREVIEW_PICTURE']['ALT'] : $arItem['NAME'])?>" title="<?=($bImage ? $arItem['PREVIEW_PICTURE']['TITLE'] : $arItem['NAME'])?>" />
													</a>
													<?
														if(count($arGalley['ITEMS']) > 0){
															foreach($arGalley['ITEMS'] as $key => $imgId){
																if($key != 0){
																	$imgItem = CFile::GetFileArray($imgId);
																	$caption = $imgItem["DESCRIPTION"];
																	
																if(empty($caption)){	
																	switch($key){
																		case "1":
																			$caption = ($arGalley['TYPE'] == "2" ? "1 200 мм" : "1 800 мм");
																			break;
																		case "2":
																			$caption = ($arGalley['TYPE'] == "2" ? "1 400 мм" : "2 100 мм");
																			break;
																		case "3":
																			$caption = ($arGalley['TYPE'] == "2" ? "1 600 мм" : "2 400 мм");
																			break;																		
																	}
																}	
																?>
																	<img class="d-none" data-fancybox="gallery-<?=$arItem['ID'];?>" data-caption="<?=$caption?>" src="<?=$imgItem['SRC']?>">
																<?
																}
															}
														}
													?>
												</div>
											</div>
										<?endif;?>
									</div>
								</div>
							</div>
						<?endforeach;?>
					</div>
				</div>
				<?if($arParams["DISPLAY_BOTTOM_PAGER"]):?>
					<div class="pagination_nav">		
						<?=$arResult["NAV_STRING"]?>
					</div>
				<?endif;?>				
			</div>
		</div>
	</div>
<?endif;?>