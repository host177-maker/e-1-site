<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Покупателю");
$APPLICATION->SetPageProperty('description', 'Узнайте о наших условиях доставки и покупки. Ознакомьтесь с гарантией на товары и получите помощь в выборе, а также узнайте больше о нашей компании.');
?><div class="row">
	<div class="col-md-3 col-sm-6 col-xs-12"> 
		<div class="more_wrapper">
			<a href="/client/delivery/" data-toggle="tooltip" title="" data-original-title="Можно использовать Tooltip!">
				<?=CMax::showIconSvg("more_icon colored", SITE_TEMPLATE_PATH.'/images/svg/features.svg', '', '', true, false);?>
				<div class="title color-theme-hover">
					Доставка
				</div>
			</a>
		</div>
	</div>
	<div class="col-md-3 col-sm-6 col-xs-12">
		<div class="more_wrapper">
			<a href="/client/purchase-warranty/" data-toggle="tooltip" title="" data-original-title="Можно использовать Tooltip!">
				<?=CMax::showIconSvg("more_icon colored", SITE_TEMPLATE_PATH.'/images/svg/purchase-warranty.svg', '', '', true, false);?>
				<div class="title color-theme-hover">
					Покупка и гарантии
				</div>
			</a>
		</div>
	</div>
	<div class="col-md-3 col-sm-6 col-xs-12"> 
		<div class="more_wrapper">
			<a href="/client/features/" data-toggle="tooltip" title="" data-original-title="Можно использовать Tooltip!">
				<?=CMax::showIconSvg("more_icon colored", SITE_TEMPLATE_PATH.'/images/svg/interesting.svg', '', '', true, false);?>
				<div class="title color-theme-hover">
					Помощь в выборе
				</div>
			</a>
		</div>
	</div>
	<div class="col-md-3 col-sm-6 col-xs-12"> 
		<div class="more_wrapper">
			<a href="/client/company/" data-toggle="tooltip" title="" data-original-title="Можно использовать Tooltip!">
				<?=CMax::showIconSvg("more_icon colored", SITE_TEMPLATE_PATH.'/images/svg/company.svg', '', '', true, false);?>
				<div class="title color-theme-hover">
					О компании
				</div>
			</a>
		</div>
	</div>
</div><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>