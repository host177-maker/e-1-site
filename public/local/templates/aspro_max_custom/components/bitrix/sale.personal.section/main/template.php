<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;

/*$APPLICATION->SetTitle(Loc::getMessage("SPS_TITLE_MAIN"));
$APPLICATION->AddChainItem(Loc::getMessage("SPS_CHAIN_MAIN"), $arResult['SEF_FOLDER']);

$theme = Bitrix\Main\Config\Option::get("main", "wizard_eshop_bootstrap_theme_id", "blue", SITE_ID);*/

$availablePages = array();

if ($arParams['SHOW_ORDER_PAGE'] === 'Y')
{
	$availablePages[] = array(
		"path" => $arResult['PATH_TO_ORDERS'],
		"name" => Loc::getMessage("SPS_ORDER_PAGE_NAME"),
		//CMax::showIconSvg("cat_icons light-ignore", $arImg["src"]);
		//"icon" => '<i class="cur_orders"></i>'
		"icon" => CMax::showIconSvg("cur_orders colored", SITE_TEMPLATE_PATH.'/images/svg/personal/recent_orders.svg')
	);
}

if ($arParams['SHOW_ORDER_PAGE'] === 'Y')
{

	$delimeter = ($arParams['SEF_MODE'] === 'Y') ? "?" : "&";
	$availablePages[] = array(
		"path" => $arResult['PATH_TO_ORDERS'].$delimeter."filter_history=Y",
		"name" => Loc::getMessage("SPS_ORDER_PAGE_HISTORY"),
		//"icon" => '<i class="filter_orders"></i>'
		"icon" => CMax::showIconSvg("filter_orders colored", SITE_TEMPLATE_PATH.'/images/svg/personal/orders_history.svg')
	);
}

if ($arParams['SHOW_ACCOUNT_PAGE'] === 'Y')
{
	$availablePages[] = array(
		"path" => $arResult['PATH_TO_ACCOUNT'],
		"name" => Loc::getMessage("SPS_ACCOUNT_PAGE_NAME"),
		//"icon" => '<i class="bill"></i>'
		"icon" => CMax::showIconSvg("bill colored", SITE_TEMPLATE_PATH.'/images/svg/personal/personal_account.svg')
	);
}

if ($arParams['SHOW_PRIVATE_PAGE'] === 'Y')
{
	$availablePages[] = array(
		"path" => $arResult['PATH_TO_PRIVATE'],
		"name" => Loc::getMessage("SPS_PERSONAL_PAGE_NAME"),
		//"icon" => '<i class="personal"></i>'
		"icon" => CMax::showIconSvg("personal colored", SITE_TEMPLATE_PATH.'/images/svg/personal/personal_data.svg')
	);
}

if ($arParams['SHOW_PROFILE_PAGE'] === 'Y')
{
	$availablePages[] = array(
		"path" => $arResult['PATH_TO_PROFILE'],
		"name" => Loc::getMessage("SPS_PROFILE_PAGE_NAME"),
		//"icon" => '<i class="profile"></i>'
		"icon" => CMax::showIconSvg("profile colored", SITE_TEMPLATE_PATH.'/images/svg/personal/orders_profiles.svg')
	);
}

if ($arParams['SHOW_BASKET_PAGE'] === 'Y')
{
	$availablePages[] = array(
		"path" => $arParams['PATH_TO_BASKET'],
		"name" => Loc::getMessage("SPS_BASKET_PAGE_NAME"),
		//"icon" => '<i class="cart"></i>'
		"icon" => CMax::showIconSvg("cart colored", SITE_TEMPLATE_PATH.'/images/svg/personal/basket.svg')
	);
}

if ($arParams['SHOW_SUBSCRIBE_PAGE'] === 'Y')
{
	$availablePages[] = array(
		"path" => $arResult['PATH_TO_SUBSCRIBE'],
		"name" => Loc::getMessage("SPS_SUBSCRIBE_PAGE_NAME"),
		//"icon" => '<i class="subscribe"></i>'
		"icon" => CMax::showIconSvg("subscribe colored", SITE_TEMPLATE_PATH.'/images/svg/personal/subscribes.svg')
	);
}

if ($arParams['SHOW_CONTACT_PAGE'] === 'Y')
{
	$availablePages[] = array(
		"path" => $arParams['PATH_TO_CONTACT'],
		"name" => Loc::getMessage("SPS_CONTACT_PAGE_NAME"),
		//"icon" => '<i class="contact"></i>'
		"icon" => CMax::showIconSvg("contact colored", SITE_TEMPLATE_PATH.'/images/svg/personal/contacts.svg')
	);
}

$customPagesList = CUtil::JsObjectToPhp($arParams['~CUSTOM_PAGES']);
if ($customPagesList)
{
	foreach ($customPagesList as $page)
	{
		$availablePages[] = array(
			"path" => $page[0],
			"name" => $page[1],
			"icon" => (strlen($page[2])) ? '<i class="fa '.htmlspecialcharsbx($page[2]).'"></i>' : ""
		);
	}
}

if (empty($availablePages))
{
	ShowError(Loc::getMessage("SPS_ERROR_NOT_CHOSEN_ELEMENT"));
}
else
{
	?>
	
	<div class="personal_wrapper">
		<div class="row sale-personal-section-row-flex">
			<?
			foreach ($availablePages as $blockElement)
			{
				?>
				<div class="col-lg-4 col-md-6 col-sm-12 col-xs-6">
					<div class="sale-personal-section-index-block bx-theme-<?=$theme?>">
						<a class="sale-personal-section-index-block-link box-shadow" href="<?=htmlspecialcharsbx($blockElement['path'])?>">
						<span class="sale-personal-section-index-block-ico">
							<?=$blockElement['icon']?>
						</span>
							<h2 class="sale-personal-section-index-block-name color-theme-hover">
								<?=htmlspecialcharsbx($blockElement['name'])?>
							</h2>
						</a>
					</div>
				</div>
				<?
			}
			?>

			<div class="col-lg-4 col-md-6 col-sm-12 col-xs-6">
				<div class="sale-personal-section-index-block bx-theme-">
					<a class="sale-personal-section-index-block-link box-shadow" href="/personal/change-password/">
						<span class="sale-personal-section-index-block-ico">
							<i class="svg inline  svg-inline-cur_orders colored" aria-hidden="true">
								<svg id="Group_192_copy_3" data-name="Group 192 copy 3" xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">
									<defs>
										<style>
											.cls-1, .cls-2 { fill: #307edd; }
											.cls-1 { fill-rule: evenodd; }
											.cls-2 { opacity: 0.1; }
										</style>
									</defs>
									<path id="Rounded_Rectangle_926_copy" data-name="Rounded Rectangle 926 copy" class="cls-1" d="M1490,789h-10a1,1,0,0,1,0-2h7.17a22.994,22.994,0,0,0-44.07,7H1443a1,1,0,0,1-2,0,0.946,0.946,0,0,1,.17-0.521A24.948,24.948,0,0,1,1489,786.3V778a1,1,0,1,1,2,0v10A1,1,0,0,1,1490,789Zm-13,7a1,1,0,0,1-1,1h-8l4.8,6.426a1,1,0,0,1-.2,1.4,1.012,1.012,0,0,1-1.41-.205l-5.19-6.945-5.19,6.945a1.012,1.012,0,0,1-1.41.205,1,1,0,0,1-.2-1.4L1464,797h-8a1,1,0,0,1,0-2h8l-4.8-6.427a1,1,0,0,1,.2-1.4,1.012,1.012,0,0,1,1.41.206l5.19,6.944,5.19-6.944a1.012,1.012,0,0,1,1.41-.206,1,1,0,0,1,.2,1.4L1468,795h8A1,1,0,0,1,1477,796Zm-35,7h10a1,1,0,0,1,0,2h-7.17a22.994,22.994,0,0,0,44.07-7h0.1a1,1,0,0,1,2,0,0.949,0.949,0,0,1-.17.521A24.947,24.947,0,0,1,1443,805.7V814a1,1,0,1,1-2,0V804A1,1,0,0,1,1442,803Z" transform="translate(-1441 -771)"/><circle class="cls-2" cx="28" cy="28" r="20"/>
								</svg>
							</i>
						</span>
						<h2 class="sale-personal-section-index-block-name color-theme-hover">Сменить пароль</h2>
					</a>
				</div>
			</div>

		</div>
	</div>
	<?
}
?>