<div class="mobilemenu-v1 scroller">
	<div class="wrap">
		<? if (CMax::nlo('menu-mobile', 'class="loadings" style="height:47px;"')): ?>
			<!-- noindex -->

			<? $APPLICATION->IncludeComponent(
				"bitrix:menu",
				"top_mobile",
				array(
					"COMPONENT_TEMPLATE"    => "top_mobile",
					"MENU_CACHE_TIME"       => "3600000",
					"MENU_CACHE_TYPE"       => "A",
					"MENU_CACHE_USE_GROUPS" => "N",
					"MENU_CACHE_GET_VARS"   => array(
					),
					"DELAY"                 => "N",
					"MAX_LEVEL"             => \Bitrix\Main\Config\Option::get("aspro.max", "MAX_DEPTH_MENU", 2),
					"ALLOW_MULTI_SELECT"    => "Y",
					"ROOT_MENU_TYPE"        => "top_content_multilevel",
					"CHILD_MENU_TYPE"       => "top_left",
					"CACHE_SELECTED_ITEMS"  => "N",
					"ALLOW_MULTI_SELECT"    => "Y",
					"USE_EXT"               => "Y"
				)
			); ?>
			<div class="menu middle">
				<ul>
					<li>
						<div class="cooperation">
							<button style="margin: 16px 30px; padding: 16px 33px;" class="cooperation-button"
								data-event="jqm" data-param-form_id="FEEDBACK" data-name="FEEDBACK">Связь с директором</button>
						</div>
					</li>
				</ul>
			</div>
			<!-- /noindex -->
		<? endif; ?>
		<? CMax::nlo('menu-mobile'); ?>
		<?
		// show regions
		CMaxCustom::ShowMobileRegions();

		// show cabinet item
		CMax::ShowMobileMenuCabinet();

		// show basket item
		CMax::ShowMobileMenuBasket();

		// use module options for change contacts
		CMax::ShowMobileMenuContacts();
		?>
		<? $APPLICATION->IncludeComponent(
			"bitrix:main.include",
			"",
			array(
				"AREA_FILE_SHOW" => "file",
				"PATH"           => SITE_DIR . "include/top_page/messengers.php",
				"EDIT_TEMPLATE"  => "include_area.php",
				'SEARCH_ICON'    => 'Y'
			)
		); ?>
		<? $APPLICATION->IncludeComponent(
			"aspro:social.info.max",
			"",
			array(
				"CACHE_TYPE"         => "A",
				"CACHE_TIME"         => "3600000",
				"CACHE_GROUPS"       => "N",
				"COMPONENT_TEMPLATE" => ".default"
			),
			false
		); ?>
	</div>
</div>