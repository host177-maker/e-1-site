<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();?>
<?$this->setFrameMode(true);?>
		

<?if($arResult['ITEMS']):?>
<?$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH.'/js/jquery.flexslider-min.js',true)?> 
	<div class="our-works__section">    
		<div id="our-works__slider" class="our-works__slider flexslider flexslider-custom thmb hovers">
			<ul class="slides">
				<?foreach($arResult['ITEMS'] as $i => $arItem):?>
					<?
						$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem['IBLOCK_ID'], 'ELEMENT_EDIT'));
						$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem['IBLOCK_ID'], 'ELEMENT_DELETE'), array('CONFIRM' => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));

						$bImage = isset($arItem['FIELDS']['DETAIL_PICTURE']) && strlen($arItem['DETAIL_PICTURE']['SRC']);
						$imageSrc = ($bImage ? $arItem['DETAIL_PICTURE']['SRC'] : false);
					?>
					<li class="box lazy light image our-works__slider-item" id="<?=$this->GetEditAreaId($arItem['ID'])?>">
						<img src="<?=$imageSrc?>" alt="<?=$arItem['NAME']?>" title="<?=$arItem['NAME']?>" />
					</li>
				<?endforeach;?>
			</ul>
		</div>
		<div id="our-works__thumbnails" class="our-works__slider-thumbnails flexslider flexslider-custom thmb hovers">
			<ul class="slides">
				<?foreach($arResult['ITEMS'] as $i => $arItem):?>
					<?
						$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem['IBLOCK_ID'], 'ELEMENT_EDIT'));
						$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem['IBLOCK_ID'], 'ELEMENT_DELETE'), array('CONFIRM' => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));

						$bImage = CFile::ResizeImageGet(
							$arItem['DETAIL_PICTURE'],
							array("width" => "190"),
							BX_RESIZE_IMAGE_PROPORTIONAL
						);
					?>
					<li class="box lazy light image our-works__slider-item" id="<?=$this->GetEditAreaId($arItem['ID'])?>">
						<img src="<?=$bImage["src"]?>" alt="<?=$arItem['NAME']?>" title="<?=$arItem['NAME']?>" />
					</li>
				<?endforeach;?>
			</ul>
		</div>		
	</div>
	<script>
		$(window).load(function() {
			$('#our-works__thumbnails').flexslider({
				animation: "slide",
				controlNav: false,
				animationLoop: true,
				directionNav: true,
				slideshow: false,
				useCSS: false,		
				itemWidth: 190,
				itemMargin: 5,
				asNavFor: '#our-works__slider'
			});
		 
			$('#our-works__slider').flexslider({
				animation: "slide",
				controlNav: false,
				animationLoop: true,
				directionNav: true,
				slideshow: false,
				itemWidth: 1920,
				useCSS: false,				
				sync: "#our-works__thumbnails"
			});
		});	
	</script>
<?endif;?>