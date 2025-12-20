<?
use Bitrix\Main\Localization\Loc;

AddEventHandler('main', 'OnBuildGlobalMenu', 'OnBuildGlobalMenuHandlerMax');
function OnBuildGlobalMenuHandlerMax(&$arGlobalMenu, &$arModuleMenu){
	if(!defined('ASPRO_MAX_MENU_INCLUDED')){
		define('ASPRO_MAX_MENU_INCLUDED', true);

		IncludeModuleLangFile(__FILE__);
		$moduleID = 'aspro.max';

		$GLOBALS['APPLICATION']->SetAdditionalCss("/bitrix/css/".$moduleID."/menu.css");

		if($GLOBALS['APPLICATION']->GetGroupRight($moduleID) >= 'R'){
			$arMenu = array(
				'menu_id' => 'global_menu_aspro_max',
				'text' => Loc::getMessage('ASPRO_MAX_GLOBAL_MENU_TEXT'),
				'title' => Loc::getMessage('ASPRO_MAX_GLOBAL_MENU_TITLE'),
				'sort' => 1000,
				'items_id' => 'global_menu_aspro_max_items',
				'icon' => 'imi_max',
				'items' => array(
					array(
						'text' => Loc::getMessage('ASPRO_MAX_MENU_CONTROL_CENTER_TEXT'),
						'title' => Loc::getMessage('ASPRO_MAX_MENU_CONTROL_CENTER_TITLE'),
						'sort' => 10,
						'url' => '/bitrix/admin/'.$moduleID.'_mc.php?lang='.urlencode(LANGUAGE_ID),
						'icon' => 'imi_control_center',
						'page_icon' => 'pi_control_center',
						'items_id' => 'control_center',
					),
					array(
						'text' => Loc::getMessage('ASPRO_MAX_MENU_TYPOGRAPHY_TEXT'),
						'title' => Loc::getMessage('ASPRO_MAX_MENU_TYPOGRAPHY_TITLE'),
						'sort' => 20,
						'url' => '/bitrix/admin/'.$moduleID.'_options.php?mid=main&lang='.urlencode(LANGUAGE_ID),
						'icon' => 'imi_typography',
						'page_icon' => 'pi_typography',
						'items_id' => 'main',
					),
					array(
						'text' => Loc::getMessage('ASPRO_MAX_MENU_PWA_TEXT'),
						'title' => Loc::getMessage('ASPRO_MAX_MENU_PWA_TITLE'),
						'sort' => 40,
						'url' => '/bitrix/admin/'.$moduleID.'_pwa.php?lang='.urlencode(LANGUAGE_ID),
						'icon' => 'imi_pwa',
						'page_icon' => 'pi_pwa',
						'items_id' => 'pwa',
					),
					array(
						'text' => Loc::getMessage('ASPRO_MAX_MENU_DEVELOP_TEXT'),
						'title' => Loc::getMessage('ASPRO_MAX_MENU_DEVELOP_TITLE'),
						'sort' => 40,
						'url' => '/bitrix/admin/'.$moduleID.'_develop.php?lang='.urlencode(LANGUAGE_ID),
						'icon' => 'util_menu_icon',
						'page_icon' => 'pi_typography',
						'items_id' => 'develop',
					),
					array(
						'text' => Loc::getMessage('ASPRO_MAX_MENU_SYNC_STORES_TEXT'),
						'title' => Loc::getMessage('ASPRO_MAX_MENU_SYNC_STORES_TITLE'),
						'sort' => 60,
						'url' => '/bitrix/admin/'.$moduleID.'_sync_stores.php?lang='.urlencode(LANGUAGE_ID),
						'icon' => 'imi_pages',
						'page_icon' => 'pi_typography',
						'items_id' => 'main',
					),
					array(
						'text' => Loc::getMessage('ASPRO_MAX_MENU_CRM_TEXT'),
						'title' => Loc::getMessage('ASPRO_MAX_MENU_CRM_TITLE'),
						'sort' => 30,
						'icon' => 'imi_marketing',
						'page_icon' => 'pi_typography',
						'items_id' => 'ncrm',
						"items" => array(
							array(
								'text' => Loc::getMessage('ASPRO_MAX_MENU_AMO_CRM_TEXT'),
								'title' => Loc::getMessage('ASPRO_MAX_MENU_AMO_CRM_TITLE'),
								'sort' => 10,
								'url' => '/bitrix/admin/'.$moduleID.'_crm_amo.php?lang='.urlencode(LANGUAGE_ID),
								'icon' => '',
								'page_icon' => 'pi_typography',
								'items_id' => 'crm_amo',
							),
							array(
								'text' => Loc::getMessage('ASPRO_MAX_MENU_FLOWLU_CRM_TEXT'),
								'title' => Loc::getMessage('ASPRO_MAX_MENU_FLOWLU_CRM_TITLE'),
								'sort' => 20,
								'url' => '/bitrix/admin/'.$moduleID.'_crm_flowlu.php?lang='.urlencode(LANGUAGE_ID),
								'icon' => '',
								'page_icon' => 'pi_typography',
								'items_id' => 'crm_flowlu',
							),
						)
					),
					array(
						'text' => Loc::getMessage('ASPRO_MAX_MENU_GENERATE_FILES_TEXT'),
						'title' => Loc::getMessage('ASPRO_MAX_MENU_GENERATE_FILES_TITLE'),
						'sort' => 50,
						'icon' => 'imi_marketing',
						'page_icon' => 'pi_typography',
						'items_id' => 'gfiles',
						"items" => array(
							array(
								'text' => Loc::getMessage('ASPRO_MAX_MENU_GENERATE_ROBOTS_TEXT'),
								'title' => Loc::getMessage('ASPRO_MAX_MENU_GENERATE_ROBOTS_TITLE'),
								'sort' => 10,
								'url' => '/bitrix/admin/'.$moduleID.'_generate_robots.php?lang='.urlencode(LANGUAGE_ID),
								'icon' => '',
								'page_icon' => 'pi_typography',
								'items_id' => 'grobots',
							),
							array(
								'text' => Loc::getMessage('ASPRO_MAX_MENU_GENERATE_SITEMAP_TEXT'),
								'title' => Loc::getMessage('ASPRO_MAX_MENU_GENERATE_SITEMAP_TITLE'),
								'sort' => 20,
								'url' => '/bitrix/admin/'.$moduleID.'_generate_sitemap.php?lang='.urlencode(LANGUAGE_ID),
								'icon' => '',
								'page_icon' => 'pi_typography',
								'items_id' => 'gsitemap',
							),
						)
					),
					array(
						'text' => Loc::getMessage('ASPRO_MAX_MENU_YANDEX_MARKET_TEXT'),
						'title' => Loc::getMessage('ASPRO_MAX_MENU_YANDEX_MARKET_TITLE'),
						'sort' => 20,
						'url' => '/bitrix/admin/'.$moduleID.'_options_ym.php?lang='.urlencode(LANGUAGE_ID),
						'icon' => 'statistic_icon_online',
						'page_icon' => 'pi_typography',
						'items_id' => 'ymindex_link',
					),
					// array(
					// 	'text' => Loc::getMessage('ASPRO_MAX_MENU_GS_TEXT'),
					// 	'title' => Loc::getMessage('ASPRO_MAX_MENU_GS_TITLE'),
					// 	'sort' => 1000,
					// 	'url' => '/bitrix/admin/'.$moduleID.'_gs.php?lang='.urlencode(LANGUAGE_ID),
					// 	'icon' => 'imi_gs',
					// 	'page_icon' => 'pi_gs',
					// 	'items_id' => 'gs',
					// ),
					array(
                        'menu_id' => 'global_menu_aspro_max_smartseo',
                        'text' => Loc::getMessage('ASPRO_MAX_SMARTSEO__MENU__ROOT_TEXT'),
                        'title' => Loc::getMessage('ASPRO_MAX_SMARTSEO__MENU__ROOT_TITLE'),
                        'sort' => 1000,
                        'items_id' => 'global_menu_aspro_max_smartseo_items',
                        'icon' => 'imi_smartseo',
                        'items' => array(
                            array(
                                'text' => Loc::getMessage('ASPRO_MAX_SMARTSEO__MENU__FILTER_RULES_TEXT'),
                                'title' => Loc::getMessage('ASPRO_MAX_SMARTSEO__MENU__FILTER_RULES_TITLE'),
                                'sort' => 10,
                                'url' => '/bitrix/admin/'.$moduleID.'_smartseo.php?route=filter_rules/list',
                                'more_url' => array(
                                    '/bitrix/admin/'.$moduleID.'_smartseo.php?route=filter_rule_detail/detail',
                                    '/bitrix/admin/'.$moduleID.'_smartseo.php?route=filter_section/detail',
                                ),
                                'icon' => '',
                                'page_icon' => '',
                                'items_id' => 'menu_aspro_max_smartseo_rule_filter',
                            ),
                            array(
                                'text' => Loc::getMessage('ASPRO_MAX_SMARTSEO__MENU__NOINDEX_RULES_TEXT'),
                                'title' => Loc::getMessage('ASPRO_MAX_SMARTSEO__MENU__NOINDEX_RULES_TITLE'),
                                'sort' => 10,
                                'url' => '/bitrix/admin/'.$moduleID . '_smartseo.php?route=noindex_rules/list',
                                'more_url' => [
                                    '/bitrix/admin/'.$moduleID . '_smartseo.php?route=noindex_rule_detail/detail',
                                ],
                                'icon' => '',
                                'page_icon' => '',
                                'items_id' => 'menu_aspro_max_smartseo_noindex_rules',
                            ),
                            array(
                                'text' => Loc::getMessage('ASPRO_MAX_SMARTSEO__MENU__SEOTEXT_TEXT'),
                                'title' => Loc::getMessage('ASPRO_MAX_SMARTSEO__MENU__SEOTEXT_TITLE'),
                                'sort' => 10,
                                'url' => '/bitrix/admin/'.$moduleID.'_smartseo.php?route=seo_text/list',
                                'more_url' => array(
                                    '/bitrix/admin/'.$moduleID.'_smartseo.php?route=seo_text_section/detail',
                                    '/bitrix/admin/'.$moduleID.'_smartseo.php?route=seo_text_element/detail',
                                ),
                                'icon' => '',
                                'page_icon' => '',
                                'items_id' => 'menu_aspro_max_smartseo_seo_text',
                            ),
                            array(
                                'text' => Loc::getMessage('ASPRO_MAX_SMARTSEO__MENU__SITEMAP_TEXT'),
                                'title' => Loc::getMessage('ASPRO_MAX_SMARTSEO__MENU__SITEMAP_TITLE'),
                                'sort' => 10,
                                'url' => '/bitrix/admin/'.$moduleID.'_smartseo.php?route=sitemap/list',
                                'more_url' => array(
                                    '/bitrix/admin/'.$moduleID.'_smartseo.php?route=sitemap_detail/detail',
                                ),
                                'icon' => '',
                                'page_icon' => '',
                                'items_id' => 'menu_aspro_max_smartseo_sitemap',
                            ),
                            array(
                                'text' => Loc::getMessage('ASPRO_MAX_SMARTSEO__MENU__SETTINGS_TEXT'),
                                'title' => Loc::getMessage('ASPRO_MAX_SMARTSEO__MENU__SETTINGS_TITLE'),
                                'sort' => 10,
                                'icon' => '',
                                'page_icon' => '',
                                'items_id' => 'menu_aspro_max_smartseo_settings',
                                'items' => array(
                                    array(
                                        'text' => Loc::getMessage('ASPRO_MAX_SMARTSEO__MENU__SITE_SETTINGS_TEXT'),
                                        'title' => Loc::getMessage('ASPRO_MAX_SMARTSEO__MENU__SITE_SETTINGS_TITLE'),
                                        'sort' => 10,
                                        'url' => '/bitrix/admin/'.$moduleID.'_smartseo.php?route=setting/sites',
                                        'more_url' => array(),
                                        'icon' => '',
                                        'page_icon' => '',
                                        'items_id' => 'menu_aspro_max_smartseo_site_settings',
                                    ),
                                    array(
                                        'text' => Loc::getMessage('ASPRO_MAX_SMARTSEO__MENU__GENERAL_SETTINGS_TEXT'),
                                        'title' => Loc::getMessage('ASPRO_MAX_SMARTSEO__MENU__GENERAL_SETTINGS_TITLE'),
                                        'sort' => 10,
                                        'url' => '/bitrix/admin/'.$moduleID.'_smartseo.php?route=setting/general',
                                        'more_url' => array(),
                                        'icon' => '',
                                        'page_icon' => '',
                                        'items_id' => 'menu_aspro_max_smartseo_general_settings',
                                    ),
                                )
                            ),
                        ),
                    ),
				),
			);
			if(!isset($arGlobalMenu['global_menu_aspro'])){
				$arGlobalMenu['global_menu_aspro'] = array(
					'menu_id' => 'global_menu_aspro',
					'text' => Loc::getMessage('ASPRO_MAX_GLOBAL_ASPRO_MENU_TEXT'),
					'title' => Loc::getMessage('ASPRO_MAX_GLOBAL_ASPRO_MENU_TITLE'),
					'sort' => 1000,
					'items_id' => 'global_menu_aspro_items',
				);
			}

			$arGlobalMenu['global_menu_aspro']['items'][$moduleID] = $arMenu;
		}
	}
}
?>