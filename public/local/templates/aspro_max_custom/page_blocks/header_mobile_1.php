<?
global $arTheme, $arRegion;
$logoClass = ($arTheme['COLORED_LOGO']['VALUE'] !== 'Y' ? '' : ' colored');
?>
<div class="mobileheader-v1">
	<div class="burger pull-left">
		<?=CMax::showIconSvg("burger dark", SITE_TEMPLATE_PATH."/images/svg/burger.svg");?>
		<?=CMax::showIconSvg("close dark", SITE_TEMPLATE_PATH."/images/svg/Close.svg");?>
	</div>
	<div class="logo-block pull-left">
		<div class="logo<?=$logoClass?>">
			<?=CMax::ShowLogo();?>
		</div>
	</div>
	<div class="right-icons pull-right">
		<div class="messengers-block">
			<a href="https://t.me/%2B79384222111" class="telegram">
				<img class="telegram-icon" alt="E1 Telegram" src="/images/telegram.png">
			</a>
			<a href="https://wa.me/79384222111" class="whatsapp">
				<img class="whatsapp-icon" alt="E1 Telegram" src="/images/whatsapp.png">
			</a>
		</div>
		<div class="">
			<div class="wrap_icon wrap_phones">
				<?CMax::ShowHeaderMobilePhones("big");?>
			</div>
		</div>
		<div class="">
			<div class="wrap_icon wrap_basket">
				<?=CMax::ShowBasketWithCompareLink('', 'big', false, false, true);?>
			</div>
		</div>
		<div class="">
			<div class="wrap_icon wrap_cabinet">
				<?=CMax::showCabinetLink(true, false, 'big');?>
			</div>
		</div>
		<!--<div class="">
			<div class="wrap_icon">
				<button class="top-btn inline-search-show twosmallfont">
					<?php /*=CMax::showIconSvg("search", SITE_TEMPLATE_PATH."/images/svg/Search.svg");*/?>
				</button>
			</div>
		</div>-->
	</div>
</div>