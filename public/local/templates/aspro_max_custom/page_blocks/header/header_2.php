<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die(); ?>
<?
global $arTheme, $arRegion, $bLongHeader, $bColoredHeader;
$arRegions = CMaxRegionality::getRegions();
if ($arRegion)
	$bPhone = ($arRegion['PHONES'] ? true : false);
else
	$bPhone = ((int) $arTheme['HEADER_PHONES'] ? true : false);
$logoClass      = ($arTheme['COLORED_LOGO']['VALUE'] !== 'Y' ? '' : ' colored');
$bLongHeader    = true;
$bColoredHeader = true;
?>
<div class="header-wrapper">
	<div class="logo_and_menu-row with-search">
		<div class="logo-row short paddings">
			<div class="maxwidth-theme">
				<div class="row">
					<div class="col-md-12">
						<div class="logo-block pull-left floated">
							<div class="logo<?= $logoClass ?>">
								<?= CMax::ShowLogo(); ?>
								<p class="logo-text">Шкафы купе в наличии и под заказ <?= IN_CITY; ?></p>
							</div>
						</div>

						<? if ($arRegions): ?>
							<div class="inline-block pull-left">
								<div class="top-description no-title">
									<? \Aspro\Functions\CAsproMax::showRegionListModal(); ?>
								</div>
								<? //оставил для вызова города?>
								<div class="regions-confirm">
									<div class="regions-confirm-icon">
										<svg width="21" height="29" viewBox="0 0 21 29" fill="none"
											xmlns="http://www.w3.org/2000/svg">
											<path fill-rule="evenodd" clip-rule="evenodd"
												d="M1.83751 9.775C1.83751 4.99083 5.71584 1.1125 10.5 1.1125C15.2842 1.1125 19.1625 4.99083 19.1625 9.775C19.1625 12.0644 18.0733 15.3075 16.4399 18.6184C14.8152 21.9115 12.6919 25.1924 10.7034 27.5393C10.5947 27.6677 10.4054 27.6677 10.2966 27.5393C8.30807 25.1924 6.1848 21.9115 4.56016 18.6184C2.9267 15.3075 1.83751 12.0644 1.83751 9.775ZM10.5 0.0625C5.13594 0.0625 0.787506 4.41093 0.787506 9.775C0.787506 12.3172 1.97017 15.7419 3.61852 19.083C5.27568 22.4419 7.44418 25.7971 9.49548 28.2181C10.0236 28.8415 10.9764 28.8415 11.5045 28.2181C13.5558 25.7971 15.7243 22.4419 17.3815 19.083C19.0298 15.7419 20.2125 12.3172 20.2125 9.775C20.2125 4.41093 15.8641 0.0625 10.5 0.0625ZM6.01426 10.6101C6.01426 8.13239 8.02288 6.12378 10.5006 6.12378C12.9784 6.12378 14.987 8.13239 14.987 10.6101C14.987 13.0879 12.9784 15.0965 10.5006 15.0965C8.02288 15.0965 6.01426 13.0879 6.01426 10.6101ZM10.5006 5.07378C7.44298 5.07378 4.96426 7.55249 4.96426 10.6101C4.96426 13.6678 7.44298 16.1465 10.5006 16.1465C13.5583 16.1465 16.037 13.6678 16.037 10.6101C16.037 7.55249 13.5583 5.07378 10.5006 5.07378Z"
												fill="#62BB46" />
										</svg>
									</div>
									<p class="regions-confirm-text">
									</p>
									<button class="regions-confirm-button-confirm">Да, верно</button>
									<span class="regions-confirm-button-negative" data-event="jqm"
										data-name="city_chooser_small" data-param-url="%2F"
										data-param-form_id="city_chooser">Нет, другой</span>
								</div>
							</div>
						<? endif; ?>

						<div class="search_wrap pull-left">
							<div class="search-block inner-table-block _show-search">
								<? $APPLICATION->IncludeComponent(
									"bitrix:main.include",
									"",
									array(
										"AREA_FILE_SHOW" => "file",
										"PATH"           => SITE_DIR . "include/top_page/search.title.catalog.php",
										"EDIT_TEMPLATE"  => "include_area.php",
										'SEARCH_ICON'    => 'Y'
									)
								); ?>
							</div>
						</div>

						<div class="right-icons pull-right" style="position: relative">
                            <div>
                                <div class="wrap_icon inner-table-block person">
                                    <?= CMax::showCabinetLink(true, true, 'big'); ?>
                                </div>
                            </div>
							<div>
								<?= CMaxCustom::ShowBasketWithCompareLink('', 'big', '', 'wrap_icon wrap_basket baskets'); ?>
							</div>
                            <div class="messengers-block">
                                <a href="https://t.me/%2B79384222111" class="telegram">
                                    <img class="telegram-icon" alt="E1 Telegram" src="/images/telegram.png">
                                </a>
                                <a href="https://wa.me/79384222111" class="whatsapp">
                                    <img class="whatsapp-icon" alt="E1 Telegram" src="/images/whatsapp.png">
                                </a>
                            </div>

                            <div class="header__send-us-message">
                                Напишите нам +7 (938) 422-21-11
                            </div>
						</div>

						<div class="pull-right">
							<div class="wrap_icon inner-table-block">
								<div class="phone-block blocks">
									<div class="call-center">
										<? $APPLICATION->IncludeFile(SITE_DIR . "include/call-center.php", array(), array(
											"MODE"     => "php",
											"NAME"     => "Copyright",
											"TEMPLATE" => "include_area.php",
										)
										); ?>
									</div>
									<? if ($bPhone): ?>
										<? CMax::ShowHeaderPhones('no-icons'); ?>
									<? endif ?>
									<? $callbackExploded = explode(',', $arTheme['SHOW_CALLBACK']['VALUE']);
									if (in_array('HEADER', $callbackExploded)): ?>
										<div class="inline-block">
											<span class="callback-block animate-load colored" data-event="jqm"
												data-param-form_id="CALLBACK"
												data-name="callback"><?= GetMessage("CALLBACK") ?></span>
										</div>
									<? endif; ?>
								</div>
							</div>
						</div>

					</div>
				</div>
			</div>
		</div>
		<? // class=logo-row?>
	</div>
	<div class="menu-row middle-block bg<?= strtolower($arTheme["MENU_COLOR"]["VALUE"]); ?>">
		<div class="maxwidth-theme">
			<div class="row">
				<div class="col-md-12">
					<div class="menu-only">
						<nav class="mega-menu sliced">
							<? $APPLICATION->IncludeComponent("bitrix:main.include", ".default",
								array(
									"COMPONENT_TEMPLATE"  => ".default",
									"PATH"                => SITE_DIR . "include/menu/menu." . ($arTheme["HEADER_TYPE"]["LIST"][$arTheme["HEADER_TYPE"]["VALUE"]]["ADDITIONAL_OPTIONS"]["MENU_HEADER_TYPE"]["VALUE"] == "Y" ? "top_catalog_wide" : "top") . ".php",
									"AREA_FILE_SHOW"      => "file",
									"AREA_FILE_SUFFIX"    => "",
									"AREA_FILE_RECURSIVE" => "Y",
									"EDIT_TEMPLATE"       => "include_area.php"
								),
								false, array("HIDE_ICONS" => "Y")
							); ?>
						</nav>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="line-row visible-xs"></div>
</div>