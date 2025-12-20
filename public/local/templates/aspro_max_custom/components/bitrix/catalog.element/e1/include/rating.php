<div class="rating">
	<?
	global $arTheme;
	if($arParams['REVIEWS_VIEW'] == 'EXTENDED'):?>
		<div class="right_reviews_info_duble">
			<div class="rating-wrapper">
				<div class="votes_block nstar with-text">
					<div class="ratings">
						<div class="inner_rating">
							<?for($i=1;$i<=5;$i++):?>
								<div class="item-rating <?=$i<= $arResult['RATING_VALUE'] ? 'filed' : ''?>"><?=CMax::showIconSvg("star", SITE_TEMPLATE_PATH."/images/svg/catalog/star_small.svg");?></div>
							<?endfor;?>
						</div>
					</div>
				</div>
				<div class="rating-value">
					<span class="count">
                        <?= $arResult['RATING_VALUE'] ?: ''; ?>
                    </span>
					<span class="maximum_value">/5</span>
				</div>
			</div>
		</div>
		<?
		//старый рейтинг
		/*
		<div class="blog-info__rating--top-info pointer">
			<div class="votes_block nstar with-text" itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
				<meta itemprop="ratingValue" content="<?=($arResult['PROPERTIES']['EXTENDED_REVIEWS_RAITING']['VALUE'] ? $arResult['PROPERTIES']['EXTENDED_REVIEWS_RAITING']['VALUE'] : 5)?>" />
				<meta itemprop="reviewCount" content="<?=(intval($arResult['PROPERTIES']['EXTENDED_REVIEWS_COUNT']['VALUE']) ? intval($arResult['PROPERTIES']['EXTENDED_REVIEWS_COUNT']['VALUE']) : 1)?>" />
				<meta itemprop="bestRating" content="5" />
				<meta itemprop="worstRating" content="0" />
				<div class="ratings">
					<?$message = $arResult['PROPERTIES']['EXTENDED_REVIEWS_COUNT']['VALUE'] ? GetMessage('VOTES_RESULT', array('#VALUE#' => $arResult['PROPERTIES']['EXTENDED_REVIEWS_RAITING']['VALUE'])) : GetMessage('VOTES_RESULT_NONE')?>
					<div class="inner_rating" title="<?=$message?>">
						<?for($i=1;$i<=5;$i++):?>
							<div class="item-rating <?=$i<=$arResult['PROPERTIES']['EXTENDED_REVIEWS_RAITING']['VALUE'] ? 'filed' : ''?>"><?=CMax::showIconSvg("star", SITE_TEMPLATE_PATH."/images/svg/catalog/star_small.svg");?></div>
						<?endfor;?>
					</div>
				</div>
			</div>
			<?if($arResult['PROPERTIES']['EXTENDED_REVIEWS_COUNT']['VALUE']):?>
				<span class="font_sxs"><?=$arResult['PROPERTIES']['EXTENDED_REVIEWS_COUNT']['VALUE']?></span>
			<?endif;?>
		</div>
		*/?>
	<?else:?>
		<?$APPLICATION->IncludeComponent(
			"bitrix:iblock.vote",
			"element_rating",
			Array(
				"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
				"IBLOCK_ID" => $arResult["IBLOCK_ID"],
				"ELEMENT_ID" => $arResult["ID"],
				"MAX_VOTE" => 5,
				"VOTE_NAMES" => array(),
				"CACHE_TYPE" => $arParams["CACHE_TYPE"],
				"CACHE_TIME" => $arParams["CACHE_TIME"],
				"DISPLAY_AS_RATING" => 'vote_avg'
			),
			$component, array("HIDE_ICONS" =>"Y")
		);?>
	<?endif;?>
</div>