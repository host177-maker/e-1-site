<?
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

global $APPLICATION;
IncludeModuleLangFile(__FILE__);

$moduleClass = "CMax";
$moduleID = "aspro.max";
$arHideProps = array("YANDEX_MARKET_MAIN","YANDEX_MARKET_SORT","YANDEX_MARKET_GRADE");
\Bitrix\Main\Loader::includeModule($moduleID);

use \Bitrix\Main\Config\Option;

$RIGHT = $APPLICATION->GetGroupRight($moduleID);
if($RIGHT >= "R"){
	$context=\Bitrix\Main\Application::getInstance()->getContext();
	$request=$context->getRequest();
	$arPost = $request->getPostList()->toArray();
	$arPost = $APPLICATION->ConvertCharsetArray($arPost, 'UTF-8', LANG_CHARSET);
	if(isset($arPost["q"]))
	{
		$arPost["q"] = ltrim($arPost["q"]);
		$arPost["q"] = rtrim($arPost["q"]);
	}

	$bSearchMode = false;
	$bFunctionExists = (function_exists('mb_strtolower'));
	if(isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && $_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest" && $arPost["q"])
	{
		$strSearchWord = ($bFunctionExists ? mb_strtolower($arPost["q"]) : strtolower($arPost["q"]));
		$bSearchMode = true;
	}

	$by = "id";
	$sort = "asc";

	$arSiteFilter = array("ACTIVE"=>"Y");
	if($bSearchMode)
		$arSiteFilter["LID"] = $arPost["site"];

	$arSites = array();
	$db_res = CSite::GetList($by, $sort, $arSiteFilter);
	while($res = $db_res->Fetch()){
		$arSites[] = $res;
	}

	$arTabsForView = COption::GetOptionString($moduleID, 'TABS_FOR_VIEW_ASPRO_MAX', '');
	if($arTabsForView) {
		$arTabsForView = explode(',' , $arTabsForView);
	}

	$arTabs = array();
	foreach($arSites as $key => $arSite){
		if($arTabsForView) {
			if(in_array($arSite['ID'], $arTabsForView)) {
				$arBackParametrs = $moduleClass::GetBackParametrsValues($arSite["ID"], false);
				$arTabs[] = array(
					"DIV" => "edit".($key+1),
					"TAB" => GetMessage("MAIN_OPTIONS_SITE_TITLE", array("#SITE_NAME#" => $arSite["NAME"], "#SITE_ID#" => $arSite["ID"])),
					"ICON" => "settings",
					"PAGE_TYPE" => "site_settings",
					"SITE_ID" => $arSite["ID"],
					"SITE_DIR" => $arSite["DIR"],
					"TEMPLATE" => CMax::GetSiteTemplate($arSite["ID"]),
					"OPTIONS" => $arBackParametrs,
				);
			}
		} else if( Option::get($moduleID, "SITE_INSTALLED", "N", $arSite["ID"]) == 'Y' ) {
			$arBackParametrs = $moduleClass::GetBackParametrsValues($arSite["ID"], false);
			$arTabs[] = array(
				"DIV" => "edit".($key+1),
				"TAB" => GetMessage("MAIN_OPTIONS_SITE_TITLE", array("#SITE_NAME#" => $arSite["NAME"], "#SITE_ID#" => $arSite["ID"])),
				"ICON" => "settings",
				"PAGE_TYPE" => "site_settings",
				"SITE_ID" => $arSite["ID"],
				"SITE_DIR" => $arSite["DIR"],
				"TEMPLATE" => CMax::GetSiteTemplate($arSite["ID"]),
				"OPTIONS" => $arBackParametrs,
			);
		}
	}

	$tabControl = new CAdminTabControl("tabControl", $arTabs);

	if($REQUEST_METHOD == "POST" && strlen($Update.$Apply.$RestoreDefaults) > 0 && $RIGHT >= "W" && check_bitrix_sessid())
	{
		global $APPLICATION, $CACHE_MANAGER;

		if(strlen($RestoreDefaults) > 0)
		{
			Option::delete(CMax::moduleID);
			Option::delete(CMax::moduleID, array("name" => "NeedGenerateCustomTheme"));
			Option::delete(CMax::moduleID, array("name" => "NeedGenerateCustomThemeBG"));
			$APPLICATION->DelGroupRight(CMax::moduleID);
		}
		else
		{
			Option::delete(CMax::moduleID, array("name" => "sid"));
			unset($_SESSION['THEME']);

			foreach($arTabs as $key => $arTab)
			{
				$optionsSiteID = $arTab["SITE_ID"];
				foreach($moduleClass::$arParametrsList as $blockCode => $arBlock)
				{
					if(in_array($blockCode,$arHideProps)) continue;
					foreach($arBlock["OPTIONS"] as $optionCode => $arOption)
					{
						if($arOption['TYPE'] === 'array')
						{
							$arOptionsRequiredKeys = array();
							$arOptionsKeys = array_keys($arOption['OPTIONS']);
							$itemsKeysCount = Option::get($moduleID, $optionCode, '0', $optionsSiteID);
							$fullKeysCount = 0;

							if($arOption['OPTIONS'] && is_array($arOption['OPTIONS']))
							{
								foreach($arOption['OPTIONS'] as $_optionCode => $_arOption)
								{
									if(strlen($_arOption['REQUIRED']) && $_arOption['REQUIRED'] === 'Y')
										$arOptionsRequiredKeys[] = $_optionCode;

								}
								for($itemKey = 0, $cnt = $itemsKeysCount + 50; $itemKey <= $cnt; ++$itemKey)
								{
									$bFull = true;
									if($arOptionsRequiredKeys)
									{
										foreach($arOptionsRequiredKeys as $_optionCode)
										{
											if(!strlen($_REQUEST[$optionCode.'_array_'.$_optionCode.'_'.$itemKey.'_'.$optionsSiteID]))
											{
												$bFull = false;
												break;
											}
										}
									}
									if($bFull)
									{
										foreach($arOptionsKeys as $_optionCode)
										{
											$newOptionValue = $_REQUEST[$optionCode.'_array_'.$_optionCode.'_'.$itemKey.'_'.$optionsSiteID];
											Option::set($moduleID, $optionCode.'_array_'.$_optionCode.'_'.$fullKeysCount, $newOptionValue, $optionsSiteID);
											unset($_REQUEST[$optionCode.'_array_'.$_optionCode.'_'.$itemKey.'_'.$optionsSiteID]);
											unset($_FILES[$optionCode.'_array_'.$_optionCode.'_'.$itemKey.'_'.$optionsSiteID]);
										}

										++$fullKeysCount;
									}
								}
							}

							Option::set($moduleID, $optionCode, $fullKeysCount, $optionsSiteID);
						}
						else
						{
							if($optionCode == "BASE_COLOR_CUSTOM" || $optionCode == 'CUSTOM_BGCOLOR_THEME')
								$moduleClass::CheckColor($_REQUEST[$optionCode."_".$optionsSiteID]);

							if($optionCode == "BASE_COLOR" && $_REQUEST[$optionCode."_".$optionsSiteID] === 'CUSTOM')
								Option::set($moduleID, "NeedGenerateCustomTheme", 'Y', $optionsSiteID);

							if($optionCode == "BGCOLOR_THEME" && $_REQUEST[$optionCode."_".$optionsSiteID] === 'CUSTOM')
								Option::set($moduleID, "NeedGenerateCustomThemeBG", 'Y', $optionsSiteID);

							if($optionCode == 'CUSTOM_FONT')
							{
								$newVal = str_replace(array('<', '>', 'rel="stylesheet"'), '', $_REQUEST[$optionCode."_".$optionsSiteID]);
							}
							else
							{
								$newVal = $_REQUEST[$optionCode."_".$optionsSiteID];
							}

							if($optionCode == 'PRIORITY_SECTION_DESCRIPTION_SOURCE'){
								$priority = isset($_REQUEST[$optionCode."_".$optionsSiteID]) ? (is_array($_REQUEST[$optionCode."_".$optionsSiteID]) ? implode(',', $_REQUEST[$optionCode."_".$optionsSiteID]) : $_REQUEST[$optionCode."_".$optionsSiteID]) : '';
								if(!strlen($priority)){
									$priority = $arOption['DEFAULT'];
								}
								$arPriority = explode(',', $priority);
								if(!in_array('SMARTSEO', $arPriority)){
									$arPriority[] = 'SMARTSEO';
								}
								if(!in_array('SOTBIT_SEOMETA', $arPriority)){
									$arPriority[] = 'SOTBIT_SEOMETA';
								}
								if(!in_array('IBLOCK', $arPriority)){
									$arPriority[] = 'IBLOCK';
								}
								$newVal = implode(',', $arPriority);
							}

							if($arOption["TYPE"] == "checkbox")
							{
								if(!strlen($newVal) || $newVal != "Y")
									$newVal = "N";

								if(isset($arOption['DEPENDENT_PARAMS']) && $arOption['DEPENDENT_PARAMS'])
								{
									foreach($arOption['DEPENDENT_PARAMS'] as $keyOption => $arOtionValue)
									{
										if(isset($arTab["OPTIONS"][$keyOption]))
										{
											$newDependentVal = $_REQUEST[$keyOption."_".$optionsSiteID];
											if((!strlen($newDependentVal) || $newDependentVal != "Y") && $arOtionValue["TYPE"] == "checkbox")
												$newDependentVal = "N";

											if($keyOption == "YA_COUNTER_ID" && strlen($newDependentVal))
												$newDependentVal = str_replace('yaCounter', '', $newDependentVal);

											if ($arOtionValue["TYPE"] == "multiselectbox") {
												$newDependentVal = @implode(",", $newDependentVal);
											}

											Option::set($moduleID, $keyOption, $newDependentVal, $optionsSiteID);
										}
									}
								}
							}
							elseif($arOption["TYPE"] == "file")
							{
								$arValueDefault = serialize(array());
								$newVal = unserialize(COption::GetOptionString($moduleID, $optionCode, $arValueDefault, $optionsSiteID));
								if(isset($_REQUEST[$optionCode."_".$optionsSiteID.'_del']) || (isset($_FILES[$optionCode."_".$optionsSiteID]) && strlen($_FILES[$optionCode."_".$optionsSiteID]['tmp_name']['0'])))
								{
									$arValues = $newVal;
									$arValues = (array)$arValues;
									foreach($arValues as $fileID)
										CFile::Delete($fileID);

									$newVal = serialize(array());
								}

								if(isset($_FILES[$optionCode."_".$optionsSiteID]) && (strlen($_FILES[$optionCode."_".$optionsSiteID]['tmp_name']['n0']) || strlen($_FILES[$optionCode."_".$optionsSiteID]['tmp_name']['0'])))
								{
									$arValues = array();
									$absFilePath = (strlen($_FILES[$optionCode."_".$optionsSiteID]['tmp_name']['n0']) ? $_FILES[$optionCode."_".$optionsSiteID]['tmp_name']['n0'] : $_FILES[$optionCode."_".$optionsSiteID]['tmp_name']['0']);
									$arOriginalName = (strlen($_FILES[$optionCode."_".$optionsSiteID]['name']['n0']) ? $_FILES[$optionCode."_".$optionsSiteID]['name']['n0'] : $_FILES[$optionCode."_".$optionsSiteID]['name']['0']);
									if(file_exists($absFilePath))
									{
										$arFile = CFile::MakeFileArray($absFilePath);
										$arFile['name'] = $arOriginalName; // for original file extension

										if($bIsIco = strpos($arOriginalName, '.ico') !== false)
										{
											$script_files = COption::GetOptionString("fileman", "~script_files", "php,php3,php4,php5,php6,phtml,pl,asp,aspx,cgi,dll,exe,ico,shtm,shtml,fcg,fcgi,fpl,asmx,pht,py,psp,var");
											$arScriptFiles = explode(',', $script_files);
											if(($p = array_search('ico', $arScriptFiles)) !== false)
												unset($arScriptFiles[$p]);

											$tmp = implode(',', $arScriptFiles);
											Option::set("fileman", "~script_files", $tmp);
										}

										if($fileID = CFile::SaveFile($arFile, $moduleClass))
											$arValues[] = $fileID;

										if($bIsIco)
											Option::set("fileman", "~script_files", $script_files);
									}
									$newVal = serialize($arValues);
								}

								if(!isset($_FILES[$optionCode."_".$optionsSiteID]) || (!strlen($_FILES[$optionCode."_".$optionsSiteID]['tmp_name']['n0']) && !strlen($_FILES[$optionCode."_".$optionsSiteID]['tmp_name']['0']) && !isset($_REQUEST[$optionCode."_".$optionsSiteID.'_del'])))
								{
									//return;
								}

								if($optionCode === 'FAVICON_IMAGE')
									$moduleClass::CopyFaviconToSiteDir($newVal, $optionsSiteID); //copy favicon for search bots

								if(is_array($newVal))
									$newVal = serialize($newVal);
								Option::set($moduleID, $optionCode, $newVal, $optionsSiteID);
								unset($arTab["OPTIONS"][$optionCode]);
							}
							elseif($arOption["TYPE"] == "selectbox")
							{
								if(isset($arOption["ADDITIONAL_OPTIONS"]) && $arOption["ADDITIONAL_OPTIONS"])
								{
									foreach($arOption['LIST'] as $keyh => $arValueH)
									{
										if($arValueH['ADDITIONAL_OPTIONS'])
										{
											foreach($arValueH['ADDITIONAL_OPTIONS'] as $keyh2 => $arValueH2)
											{
												if($_REQUEST[$keyh2."_".$keyh."_".$optionsSiteID])
												{
													\Bitrix\Main\Config\Option::set('aspro.max', $keyh2."_".$keyh, $_REQUEST[$keyh2."_".$keyh."_".$optionsSiteID], $optionsSiteID);
												}
												else
												{
													\Bitrix\Main\Config\Option::set('aspro.max', $keyh2."_".$keyh, "N", $optionsSiteID);
												}
											}
										}
									}
								}
								if(isset($arOption["DEPENDENT_PARAMS"]) && $arOption["DEPENDENT_PARAMS"])
								{
									foreach($arOption['DEPENDENT_PARAMS'] as $keyh2 => $arValueH2)
									{
										if($_REQUEST[$keyh2."_".$optionsSiteID])
										{
											\Bitrix\Main\Config\Option::set('aspro.max', $keyh2, $_REQUEST[$keyh2."_".$optionsSiteID], $optionsSiteID);
										}
										else
										{
											\Bitrix\Main\Config\Option::set('aspro.max', $keyh2, "N", $optionsSiteID);
										}
									}
								}
								if(isset($arOption["SUB_PARAMS"]) && $arOption["SUB_PARAMS"])
								{
									if(isset($arOption["LIST"]) && $arOption["LIST"])
									{
										$arSubValues = array();
										foreach($arOption["LIST"] as $key2 => $value)
										{
											if($arOption["SUB_PARAMS"][$key2] && $key2 == $newVal)
											{
												foreach($arOption["SUB_PARAMS"][$key2] as $key3 => $arSubValue)
												{
													if($_REQUEST[$key2."_".$key3."_".$optionsSiteID])
													{
														$arSubValues[$key3] = $_REQUEST[$key2."_".$key3."_".$optionsSiteID];
														unset($arTab["OPTIONS"][$key2."_".$key3]);
													}
													elseif($arTab["OPTIONS"][$key2."_".$key3])
													{
														if($arSubValue["TYPE"] == "checkbox" && $key2 == $newVal && !isset($arSubValue["VISIBLE"]))
															$arSubValues[$key3] = "N";

														unset($arTab["OPTIONS"][$key2."_".$key3]);
													}

													//set fon index components
													if(isset($arSubValue['FON']) && $arSubValue['FON'])
													{
														$code_tmp = 'fon'.$key2.$key3;

														if($_REQUEST[$code_tmp.'_'.$optionsSiteID])
															Option::set($moduleID, $code_tmp, $_REQUEST[$code_tmp.'_'.$optionsSiteID], $optionsSiteID);
														else
															Option::set($moduleID, $code_tmp, 'N', $optionsSiteID);
													}

													//set default template index components
													if(isset($arSubValue['TEMPLATE']) && $arSubValue['TEMPLATE'])
													{

														$code_tmp = $key2.'_'.$key3.'_TEMPLATE';
														if($_REQUEST[$code_tmp.'_'.$optionsSiteID])
															Option::set($moduleID, $code_tmp, $_REQUEST[$code_tmp.'_'.$optionsSiteID], $optionsSiteID);

														if($arSubValue['TEMPLATE']['LIST'])
														{
															$arTmpDopConditions = array();
															foreach($arSubValue['TEMPLATE']['LIST'] as $skey => $arSValue)
															{
																if($arSValue['ADDITIONAL_OPTIONS'])
																{
																	foreach($arSValue['ADDITIONAL_OPTIONS'] as $additOpKey => $additOp) {
																		$strCodeTmp = $key3.'_'.$additOpKey.'_'.$skey;
																		if($_REQUEST[$key2.'_'.$strCodeTmp.'_'.$optionsSiteID])
																			$arTmpDopConditions[$strCodeTmp] = $_REQUEST[$key2.'_'.$strCodeTmp.'_'.$optionsSiteID];
																		else
																			$arTmpDopConditions[$strCodeTmp] = "N";
																	}
																}
															}
															if($arTmpDopConditions)
																\Bitrix\Main\Config\Option::set($moduleID, "N_O_".$optionCode.'_'.$key2.'_'.$key3.'_', serialize($arTmpDopConditions), $optionsSiteID);
														}
													}

												}

												//sort order prop for main page
												$param = 'SORT_ORDER_'.$optionCode.'_'.$key2;
												if(isset($_REQUEST[$param.'_'.$optionsSiteID]))
												{
													Option::set($moduleID, $param, $_REQUEST[$param.'_'.$optionsSiteID], $optionsSiteID);
												}
											}
										}
										if($arSubValues)
											Option::set($moduleID, "NESTED_OPTIONS_".$optionCode."_".$newVal, serialize($arSubValues), $optionsSiteID);

									}
								}
							}
							elseif($arOption["TYPE"] == "multiselectbox")
							{
								$newVal = @implode(",", $newVal);
							}

							if($arOption["TYPE"] != "file")
								$arTab["OPTIONS"][$optionCode] = $newVal;

							Option::set($moduleID, $optionCode, $newVal, $optionsSiteID);

							if($optionCode == "CUSTOM_FONT")
							{
								$path = \Bitrix\Main\Application::getDocumentRoot().'/bitrix/components/aspro/theme.max/css/user_font_'.$optionsSiteID.'.css';
								$content = '';
								if($newVal)
								{
									$string = str_replace('link href=', '', $newVal);
									$stringLength = strlen($string);
									$startLetter = strpos($string, '=');
									$string = substr($string, $startLetter + 1, $stringLength);
									$endLetter = strpos($string, ':');
									$string = ($endLetter ? substr($string, 0, $endLetter) : $string);
									$string = str_replace('" rel="stylesheet"', '', $string);
									$endLetter = strpos($string, '&amp');
									$string = ($endLetter ? substr($string, 0, $endLetter) : $string);
									$content = "body,h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6, .popup-window,body div.bx-yandex-map,.fancybox-title{font-family: '".str_replace('+', ' ', $string)."', sans-serif;}";
								}
								\Bitrix\Main\IO\File::putFileContents($path, $content);
							}
						}
					}
				}

				CBitrixComponent::clearComponentCache('bitrix:catalog.element', $optionsSiteID);
				CBitrixComponent::clearComponentCache('bitrix:form.result.new', $optionsSiteID);
				CBitrixComponent::clearComponentCache('bitrix:catalog.section', $optionsSiteID);
				CBitrixComponent::clearComponentCache('bitrix:news.list', $optionsSiteID);
				CBitrixComponent::clearComponentCache('bitrix:news.detail', $optionsSiteID);
				CBitrixComponent::clearComponentCache('bitrix:catalog.bigdata.products', $optionsSiteID);
				CBitrixComponent::clearComponentCache('bitrix:catalog.store.amount', $optionsSiteID);
				CBitrixComponent::clearComponentCache('bitrix:menu', $optionsSiteID);
				CBitrixComponent::clearComponentCache('aspro:com.banners.max', $optionsSiteID);
				CBitrixComponent::clearComponentCache('aspro:catalog.section.list.max', $optionsSiteID);
				$arTabs[$key] = $arTab;
			}
		}

		// clear composite cache
		if($compositeMode = $moduleClass::IsCompositeEnabled())
		{
			$obCache = new CPHPCache();
			$obCache->CleanDir('', 'html_pages');
			$moduleClass::EnableComposite($compositeMode === 'AUTO_COMPOSITE');
		}

		// send statistics
		if(\Aspro\Max\GS::isEnabled()){
			if(\Aspro\Max\GS::register()){
				\Aspro\Max\GS::sendData(
					\Aspro\Max\GS::mkData(array('sites', 'options'))
				);
			}
		}

		$APPLICATION->RestartBuffer();
	}

	CJSCore::RegisterExt('iconset', array(
		'js' => '/bitrix/js/'.$moduleID.'/iconset.js',
		'css' => '/bitrix/css/'.$moduleID.'/iconset.css',
		'lang' => '/bitrix/modules/'.$moduleID.'/lang/'.LANGUAGE_ID.'/admin/iconset.php',
	));

	\CJSCore::RegisterExt('regionphone', array(
		'js' => '/bitrix/js/'.$moduleID.'/property/regionphone.js',
		'css' => '/bitrix/css/'.$moduleID.'/property/regionphone.css',
		'lang' => '/bitrix/modules/'.$moduleID.'/lang/'.LANGUAGE_ID.'/lib/property/regionphone.php',
	));

	CJSCore::Init(array('jquery', 'iconset', 'regionphone'));

	$GLOBALS['APPLICATION']->SetAdditionalCss('/bitrix/css/'.$moduleID.'/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCss('/bitrix/css/'.$moduleID.'/spectrum.css');
	$GLOBALS['APPLICATION']->SetAdditionalCss('/bitrix/js/main/loader/loader.css');
	$GLOBALS['APPLICATION']->AddHeadScript('/bitrix/js/'.$moduleID.'/spectrum.js');
	$GLOBALS['APPLICATION']->AddHeadScript('/bitrix/js/'.$moduleID.'/jquery.splendid.textchange.js');
	$GLOBALS['APPLICATION']->AddHeadScript('/bitrix/js/'.$moduleID.'/sort/Sortable.js');
	?>
	<?if(!count($arTabs)):?>
		<div class="adm-info-message-wrap adm-info-message-red">
			<div class="adm-info-message">
				<div class="adm-info-message-title"><?=GetMessage("ASPRO_MAX_NO_SITE_INSTALLED", array("#SESSION_ID#"=>bitrix_sessid_get()))?></div>
				<div class="adm-info-message-icon"></div>
			</div>
			<a href="aspro.max_options_tabs.php" id="tabs_settings" target="_blank">
				<span>
					<?=GetMessage('TABS_SETTINGS')?>
				</span>
			</a>
		</div>
	<?else:?>
		<?$tabControl->Begin();?>
		<a href="aspro.max_options_tabs.php" id="tabs_settings" target="_blank">
			<span>
				<?=GetMessage('TABS_SETTINGS')?>
			</span>
		</a>
		<form method="post" class="max_options views" enctype="multipart/form-data" action="<?=$APPLICATION->GetCurPage()?>?mid=<?=urlencode($mid)?>&amp;lang=<?=LANGUAGE_ID?>">
		<?=bitrix_sessid_post();?>
		<?CModule:: IncludeModule('sale');
		$arPersonTypes = $arDeliveryServices = $arPaySystems = $arCurrency = $arOrderPropertiesByPerson = $arS = $arC = $arN = array();
		$dbRes = CSalePersonType::GetList(array('SORT' => 'ASC'), array('ACTIVE' => 'Y'), false, false, array());
		while($arItem = $dbRes->Fetch()){
			if($arItem['LIDS'] && is_array($arItem['LIDS'])){
				foreach($arItem['LIDS'] as $site_id){
					$arPersonTypes[$site_id][$arItem['ID']] = '['.$arItem['ID'].'] '.$arItem['NAME'].' ('.$site_id.')';
				}
			}
			$arS[$arItem['ID']] = array('FIO', 'PHONE', 'EMAIL');
			$arN[$arItem['ID']] = array(
				'FIO' => GetMessage('ONECLICKBUY_PROPERTIES_FIO'),
				'PHONE' => GetMessage('ONECLICKBUY_PROPERTIES_PHONE'),
				'EMAIL' => GetMessage('ONECLICKBUY_PROPERTIES_EMAIL'),
			);
		}

		foreach($arTabs as $key => $arTab)
		{
			if($arTab["SITE_ID"])
			{
				$dbRes = CSaleDelivery::GetList(array('SORT' => 'ASC'), array('ACTIVE' => 'Y', 'LID' => $arTab["SITE_ID"]), false, false, array());
				while($arItem = $dbRes->Fetch())
				{
					$arDeliveryServices[$arTab["SITE_ID"]][$arItem['ID']] = '['.$arItem['ID'].'] '.$arItem['NAME'].' ('.$arTab["SITE_ID"].')';
				}
			}
		}

		$dbRes = CSalePaySystem::GetList(array('SORT' => 'ASC'), array('ACTIVE' => 'Y'), false, false, array());
		while($arItem = $dbRes->Fetch())
		{
			$arPaySystems[$arItem['ID']] = '['.$arItem['ID'].'] '.$arItem['NAME'];
		}

		$dbRes = CCurrency::GetList(($by = "sort"), ($order = "asc"), LANGUAGE_ID);
		while($arItem = $dbRes->Fetch())
		{
			$arCurrency[$arItem['CURRENCY']] = $arItem['FULL_NAME'].' ('.$arItem['CURRENCY'].')';
		}

		$dbRes = CSaleOrderProps::GetList(array('SORT' => 'ASC'), array('ACTIVE' => 'Y'), false, false, array('ID', 'CODE', 'NAME', 'PERSON_TYPE_ID', 'TYPE', 'IS_PHONE', 'IS_EMAIL', 'IS_PAYER'));
		while($arItem = $dbRes->Fetch())
		{
			if($arItem['TYPE'] === 'TEXT' || $arItem['TYPE'] === 'FILE' && strlen($arItem['CODE']))
			{
				$arN[$arItem['PERSON_TYPE_ID']][$arItem['CODE']] = $arItem['NAME'];
				if($arItem['IS_PAYER'] === 'Y')
					$arS[$arItem['PERSON_TYPE_ID']][0] = $arItem['CODE'];
				elseif($arItem['IS_PHONE'] === 'Y')
					$arS[$arItem['PERSON_TYPE_ID']][1] = $arItem['CODE'];
				elseif($arItem['IS_EMAIL'] === 'Y')
					$arS[$arItem['PERSON_TYPE_ID']][2] = $arItem['CODE'];
				else
					$arS[$arItem['PERSON_TYPE_ID']][] = $arItem['CODE'];
			}
		}
		if($arS && $arN)
		{
			foreach($arS as $PERSON_TYPE_ID => $arCodes)
			{
				if($arCodes)
				{
					foreach($arCodes as $CODE)
						$arOrderPropertiesByPerson[$PERSON_TYPE_ID][$CODE] = $arN[$PERSON_TYPE_ID][$CODE];

					$arOrderPropertiesByPerson[$PERSON_TYPE_ID]['COMMENT'] = GetMessage('ONECLICKBUY_PROPERTIES_COMMENT');
				}
			}
		}?>
		<?

		$bGroupsBlockContact = $bGroupsBlockCounters = false;
		foreach($moduleClass::$arParametrsList as $keyGroup => $arGroup)
		{
			if($arGroup["OPTIONS"])
			{
				foreach($arGroup["OPTIONS"] as $keyOption => $arTmpOption)
				{
					if($bSearchMode)
					{
						if($keyOption == "PAGE_CONTACTS")
						{
							$arTmpOption["TITLE"] = GetMessage("BLOCK_VIEW_TITLE");
							$moduleClass::$arParametrsList[$keyGroup]["OPTIONS"][$keyOption]["TITLE"] = GetMessage("BLOCK_VIEW_TITLE");
						}

						//find items
						$strTitle = ($bFunctionExists ? mb_strtolower($arTmpOption["TITLE"]) : strtolower($arTmpOption["TITLE"]));
						if(stripos($strTitle, $strSearchWord) !== false)
						{
							$arTmpOption["SEARCH_FIND"] = true;
							$moduleClass::$arParametrsList[$keyGroup]["OPTIONS"][$keyOption]["SEARCH_FIND"] = true;

							if(strpos($keyOption, "CONTACTS") !== false)
							{
								if($keyOption == "CONTACTS_MAP_NOTE" || $keyOption == "CONTACTS_USE_FEEDBACK")
								{
									if(in_array(array(1,2), Option::get($moduleID, "CONTACTS_MAP_NOTE", 1, $arPost['site'])))
										$bGroupsBlockContact = true;
								}
								else
									$bGroupsBlockContact = true;
							}
						}

						// add find item for dependent groups
						if(isset($arTmpOption["DEPENDENT_PARAMS"]))
						{
							$bFind = false;
							foreach($arTmpOption["DEPENDENT_PARAMS"] as $keyOption2 => $arTmpOption2)
							{
								$strTitle = ($bFunctionExists ? mb_strtolower($arTmpOption2["TITLE"]) : strtolower($arTmpOption2["TITLE"]));
								if(stripos($strTitle, $strSearchWord) !== false)
								{
									$arTmpOption2["SEARCH_FIND"] = true;
									$arTmpOption["DEPENDENT_PARAMS"][$keyOption2]["SEARCH_FIND"] = true;
									$moduleClass::$arParametrsList[$keyGroup]["OPTIONS"][$keyOption]["DEPENDENT_PARAMS"][$keyOption2]["SEARCH_FIND"] = true;
									$moduleClass::$arParametrsList[$keyGroup]["OPTIONS"][$keyOption]["SEARCH_FIND"] = true;
									$bFind = true;
								}
							}
							if(strpos($keyOption, "YA_GOALS") !== false && $bFind)
							{
								$arTmpOption["SEARCH_FIND"] = true;
							}
						}

						// add find item for social group
						if($keyGroup == "SOCIAL")
						{
							$strTitle = ($bFunctionExists ? mb_strtolower($arGroup["TITLE"]) : strtolower($arGroup["TITLE"]));
							if(stripos($strTitle, $strSearchWord) !== false)
							{
								$arTmpOption["SEARCH_FIND"] = true;
								$moduleClass::$arParametrsList[$keyGroup]["OPTIONS"][$keyOption]["SEARCH_FIND"] = true;
							}
						}

						if(isset($arTmpOption["SUB_PARAMS"]))
						{
							$strGroupTitle = GetMessage("SUB_PARAMS");
							$strTitle = ($bFunctionExists ? mb_strtolower($strGroupTitle) : strtolower($strGroupTitle));
							if(stripos($strTitle, $strSearchWord) !== false)
							{
								$arTmpOption["SEARCH_FIND"] = true;
								$moduleClass::$arParametrsList[$keyGroup]["OPTIONS"][$keyOption]["SEARCH_FIND"] = true;
							}

							$strGroupTitle = GetMessage("FRONT_TEMPLATE_GROUP");
							$strTitle = ($bFunctionExists ? mb_strtolower($strGroupTitle) : strtolower($strGroupTitle));
							if(stripos($strTitle, $strSearchWord) !== false)
							{
								$arTmpOption["SEARCH_FIND"] = true;
								$moduleClass::$arParametrsList[$keyGroup]["OPTIONS"][$keyOption]["SEARCH_FIND"] = true;
							}
							$indexType = Option::get($moduleID, "INDEX_TYPE", "index1", $arPost['site']);
							foreach($arTmpOption["SUB_PARAMS"][$indexType] as $keyOption2 => $arTmpOption2)
							{
								$strTitle = ($bFunctionExists ? mb_strtolower($arTmpOption2["TITLE"]) : strtolower($arTmpOption2["TITLE"]));
								if(stripos($strTitle, $strSearchWord) !== false)
								{
									$moduleClass::$arParametrsList[$keyGroup]["OPTIONS"][$keyOption]["SUB_PARAMS"][$keyOption2]["SEARCH_FIND"] = true;
									$moduleClass::$arParametrsList[$keyGroup]["OPTIONS"][$keyOption]["SEARCH_FIND"] = true;
								}
								if(isset($arTmpOption2["TEMPLATE"]))
								{
									$strTitle = ($bFunctionExists ? mb_strtolower($arTmpOption2["TEMPLATE"]["TITLE"]) : strtolower($arTmpOption2["TEMPLATE"]["TITLE"]));
									if(stripos($strTitle, $strSearchWord) !== false)
									{
										$moduleClass::$arParametrsList[$keyGroup]["OPTIONS"][$keyOption]["SEARCH_FIND"] = true;
									}
								}
							}
						}
					}

					if(isset($arTmpOption["GROUP_BLOCK"]))
					{
						$strGroupTitle = GetMessage($arTmpOption["GROUP_BLOCK"]);
						if(!$moduleClass::$arParametrsList[$keyGroup]["OPTIONS"][$arTmpOption["GROUP_BLOCK"]])
						{
							$moduleClass::$arParametrsList[$keyGroup]["OPTIONS"][$arTmpOption["GROUP_BLOCK"]]["TITLE"] = $strGroupTitle;
							if(isset($arTmpOption["GROUP_BLOCK_LINE"]))
								$moduleClass::$arParametrsList[$keyGroup]["OPTIONS"][$arTmpOption["GROUP_BLOCK"]]["ONE_BLOCK"] = "Y";
						}
						if($bSearchMode)
						{
							$strTitle = ($bFunctionExists ? mb_strtolower($strGroupTitle) : strtolower($strGroupTitle));
							if(stripos($strTitle, $strSearchWord) !== false)
							{
								$arTmpOption["SEARCH_FIND"] = true;
							}
						}
						$moduleClass::$arParametrsList[$keyGroup]["OPTIONS"][$arTmpOption["GROUP_BLOCK"]]["ITEMS"][$keyOption] = $arTmpOption;
						unset($moduleClass::$arParametrsList[$keyGroup]["OPTIONS"][$keyOption]);
					}
					if(isset($arTmpOption["DEPENDENT_PARAMS"]))
					{
						foreach($arTmpOption["DEPENDENT_PARAMS"] as $keyOption2 => $arTmpOption2)
						{
							if(isset($arTmpOption2["GROUP_BLOCK"]))
							{
								$strGroupTitle = GetMessage($arTmpOption2["GROUP_BLOCK"]);
								if($bSearchMode)
								{
									$strTitle = ($bFunctionExists ? mb_strtolower($strGroupTitle) : strtolower($strGroupTitle));
									if(stripos($strTitle, $strSearchWord) !== false)
									{
										$arTmpOption2["SEARCH_FIND"] = true;
									}
								}

								if(!$moduleClass::$arParametrsList[$keyGroup]["OPTIONS"][$arTmpOption2["GROUP_BLOCK"]])
								{
									$moduleClass::$arParametrsList[$keyGroup]["OPTIONS"][$arTmpOption2["GROUP_BLOCK"]]["TITLE"] = $strGroupTitle;
									$moduleClass::$arParametrsList[$keyGroup]["OPTIONS"][$arTmpOption2["GROUP_BLOCK"]]["PARENT"] = $keyOption;
									if(isset($arTmpOption2["GROUP_BLOCK_LINE"]))
										$moduleClass::$arParametrsList[$keyGroup]["OPTIONS"][$arTmpOption2["GROUP_BLOCK"]]["ONE_BLOCK"] = "Y";
								}

								$moduleClass::$arParametrsList[$keyGroup]["OPTIONS"][$arTmpOption2["GROUP_BLOCK"]]["ITEMS"][$keyOption2] = $arTmpOption2;
								unset($moduleClass::$arParametrsList[$keyGroup]["OPTIONS"][$keyOption]["DEPENDENT_PARAMS"][$keyOption2]);
							}
						}
					}
				}
			}
		}

		unset($moduleClass::$arParametrsList["YANDEX_MARKET_MAIN"]);
		unset($moduleClass::$arParametrsList["YANDEX_MARKET_SORT"]);
		unset($moduleClass::$arParametrsList["YANDEX_MARKET_GRADE"]);

		//


		$moduleClass::$arParametrsList["SECTION"]["OPTIONS"]["SECTION_CONTACTS_GROUP2"]["ONE_BLOCK"] = "Y";

		/* required props */
		$arRequiredOptions = array(
			"BASE_COLOR_GROUP" => $moduleClass::$arParametrsList["MAIN"]["OPTIONS"]["BASE_COLOR_GROUP"],
			"LOGO_GROUP" => $moduleClass::$arParametrsList["MAIN"]["OPTIONS"]["LOGO_GROUP"],
			"HEADER_PHONES" => $moduleClass::$arParametrsList["HEADER"]["OPTIONS"]["HEADER_PHONES"],
			"SOCIAL" => array(
				"TITLE" => GetMessage("SOCIAL_OPTIONS"),
				"ONE_BLOCK" => "Y",
				"ITEMS" => $moduleClass::$arParametrsList["SOCIAL"]["OPTIONS"]
			),
			"SOCIAL_VIBER_OPTIONS" => array(
				"TITLE" => GetMessage("SOCIAL_VIBER_OPTIONS"),
				"ITEMS" => $moduleClass::$arParametrsList["SOCIAL_VIBER_OPTIONS"]["OPTIONS"],
			),
			"SOCIAL_WHATS_OPTIONS" => array(
				"TITLE" => GetMessage("SOCIAL_WHATS_OPTIONS"),
				"ITEMS" => $moduleClass::$arParametrsList["SOCIAL_WHATS_OPTIONS"]["OPTIONS"],
			),
			"SECTION_CONTACTS_GROUP" => $moduleClass::$arParametrsList["SECTION"]["OPTIONS"]["SECTION_CONTACTS_GROUP"],
			"SECTION_CONTACTS_GROUP2" => $moduleClass::$arParametrsList["SECTION"]["OPTIONS"]["SECTION_CONTACTS_GROUP2"],
			"SECTION_CONTACTS_GROUP3" => $moduleClass::$arParametrsList["SECTION"]["OPTIONS"]["SECTION_CONTACTS_GROUP3"],
			"COUNTERS_GOALS_GROUP" => $moduleClass::$arParametrsList["COUNTERS_GOALS"]["OPTIONS"]["COUNTERS_GOALS_GROUP"],
			"COUNTERS_GOALS_GROUP2" => $moduleClass::$arParametrsList["COUNTERS_GOALS"]["OPTIONS"]["COUNTERS_GOALS_GROUP2"],
			"COUNTERS_GOALS_GROUP3" => $moduleClass::$arParametrsList["COUNTERS_GOALS"]["OPTIONS"]["COUNTERS_GOALS_GROUP3"],
			"COUNTERS_GOALS_GROUP4" => $moduleClass::$arParametrsList["COUNTERS_GOALS"]["OPTIONS"]["COUNTERS_GOALS_GROUP4"],
		);

		unset($moduleClass::$arParametrsList["MAIN"]["OPTIONS"]["BASE_COLOR_GROUP"]);
		unset($moduleClass::$arParametrsList["MAIN"]["OPTIONS"]["LOGO_GROUP"]);
		unset($moduleClass::$arParametrsList["HEADER"]["OPTIONS"]["HEADER_PHONES"]);
		unset($moduleClass::$arParametrsList["SOCIAL"]);

		unset($moduleClass::$arParametrsList["SOCIAL_VIBER_OPTIONS"]);
		unset($moduleClass::$arParametrsList["SOCIAL_WHATS_OPTIONS"]);

		unset($moduleClass::$arParametrsList["SECTION"]["OPTIONS"]["SECTION_CONTACTS_GROUP"]);
		unset($moduleClass::$arParametrsList["SECTION"]["OPTIONS"]["SECTION_CONTACTS_GROUP2"]);
		unset($moduleClass::$arParametrsList["SECTION"]["OPTIONS"]["SECTION_CONTACTS_GROUP3"]);
		unset($moduleClass::$arParametrsList["COUNTERS_GOALS"]);
		unset($moduleClass::$arParametrsList["COUNTERS_GOALS"]["OPTIONS"]["COUNTERS_GOALS_GROUP2"]);
		unset($moduleClass::$arParametrsList["COUNTERS_GOALS"]["OPTIONS"]["COUNTERS_GOALS_GROUP3"]);
		unset($moduleClass::$arParametrsList["COUNTERS_GOALS"]["OPTIONS"]["COUNTERS_GOALS_GROUP4"]);


		array_unshift($moduleClass::$arParametrsList, array(
			"TITLE" => GetMessage("ASPRO_SOLUTION_REQUIRED_FIELDS"),
			"CODE" => "REQUIRED",
			"OPTIONS" => $arRequiredOptions,
		));

		if($bSearchMode)
		{
			foreach($moduleClass::$arParametrsList as $keyGroup => $arGroup)
			{
				if($arGroup["OPTIONS"])
				{
					foreach($arGroup["OPTIONS"] as $keyOption => $arTmpOption)
					{
						$strTitle = ($bFunctionExists ? mb_strtolower($arGroup["TITLE"]) : strtolower($arGroup["TITLE"]));
						if(isset($arTmpOption["ITEMS"]))
						{
							foreach($arTmpOption["ITEMS"] as $keyOption2 => $arTmpOption2)
							{
								// add find item for contact group
								if($bGroupsBlockContact && strpos($keyOption2, "CONTACTS") !== false)
								{
									$moduleClass::$arParametrsList[$keyGroup]["OPTIONS"][$keyOption]["ITEMS"][$keyOption2]["SEARCH_FIND"] = true;
								}

								if(stripos($strTitle, $strSearchWord) !== false)
								{
									$moduleClass::$arParametrsList[$keyGroup]["OPTIONS"][$keyOption]["ITEMS"][$keyOption2]["SEARCH_FIND"] = true;
								}

								/*if($keyOption == "GOOGLE_RECAPTCHA_GROUP" && $keyOption2 == "USE_GOOGLE_RECAPTCHA")
								{
									$bGroupsBlockGcaptcha = false;
									foreach($arTmpOption["ITEMS"] as $keyOption22 => $arTmpOption22)
									{
										if($arTmpOption22["SEARCH_FIND"])
											$bGroupsBlockGcaptcha = true;
									}
									if($bGroupsBlockGcaptcha)
									{
										$moduleClass::$arParametrsList[$keyGroup]["OPTIONS"][$keyOption]["ITEMS"][$keyOption2]["SEARCH_FIND"] = true;
									}
								}*/

								if($arTmpOption2["DEPENDENT_PARAMS"])
								{
									/*if($keyOption2 == "GOOGLE_ECOMERCE")
									{*/
										foreach($arTmpOption2["DEPENDENT_PARAMS"] as $keyOption3 => $arTmpOption3)
										{
											$strGroupTitle = $arTmpOption3["TITLE"];

											$strTitle = ($bFunctionExists ? mb_strtolower($strGroupTitle) : strtolower($strGroupTitle));
											if(stripos($strTitle, $strSearchWord) !== false)
											{
												$moduleClass::$arParametrsList[$keyGroup]["OPTIONS"][$keyOption]["ITEMS"][$keyOption2]["SEARCH_FIND"] = true;
											}
										}
									//}
								}

								if(isset($arTmpOption2["LIST"]) && $arTmpOption2["LIST"])
								{
									foreach($arTmpOption2["LIST"] as $key => $value)
									{
										$value = ((is_array($value) && isset($value["TITLE"])) ? $value["TITLE"] : $value);
										$strTitle = ($bFunctionExists ? mb_strtolower($value) : strtolower($value));
										if(stripos($strTitle, $strSearchWord) !== false)
										{
											$moduleClass::$arParametrsList[$keyGroup]["OPTIONS"][$keyOption]["ITEMS"][$keyOption2]["SEARCH_FIND"] = true;
										}
									}
								}
							}
						}
						else
						{
							if(stripos($strTitle, $strSearchWord) !== false)
							{
								$moduleClass::$arParametrsList[$keyGroup]["OPTIONS"][$keyOption]["SEARCH_FIND"] = true;
							}
						}
					}
				}
			}
			// print_r($moduleClass::$arParametrsList[0]);
			// die();
		}
		/**/

		foreach($arTabs as $key => $arTab)
		{
			$tabControl->BeginNextTab();
			if($arTab["SITE_ID"])
			{
				$optionsSiteID = $arTab["SITE_ID"];?>
				<tr>
					<td colspan="2" class="site_<?=$optionsSiteID;?>">

						<?/* set border color from site */?>
						<?$themeColor = '#546772';
						$colorBase = \Bitrix\Main\Config\Option::get($moduleID, 'BASE_COLOR', '', $optionsSiteID);
						$colorCustom = \Bitrix\Main\Config\Option::get($moduleID, 'BASE_COLOR_CUSTOM', '', $optionsSiteID);
						if($colorBase !== 'CUSTOM')
							$themeColor = $arRequiredOptions["BASE_COLOR_GROUP"]["ITEMS"]["BASE_COLOR"]["LIST"][$colorBase]["COLOR"];
						else
							$themeColor = $colorCustom;
						$themeColor = str_replace('#', '', $themeColor);?>
						<?if($themeColor):?>
							<?$APPLICATION->AddHeadString('<style>.site_'.$optionsSiteID.' .status-block.current,.site_'.$optionsSiteID.' .current .status-block{border-color:#'.$themeColor.' !important;}.site_'.$optionsSiteID.' .tabs-wrapper .tabs-heading > .head.active:before,.site_'.$optionsSiteID.' .colored_theme_bg{background:#'.$themeColor.' !important;}</style>',true)?>
						<?endif;?>
						<?/**/?>

						<div class="tabs-wrapper">
							<div class="search_wrapper">
								<div class="search_wrapper_inner">
									<input type="text" size="" maxlength="255" value="" name="SEARCH_CONFIG" data-site="<?=$optionsSiteID;?>" placeholder="<?=GetMessage("FILTER_SEARCH");?>">
									<div class="buttons">
										<div class="search" title="<?=GetMessage("SEARCH_CLICK");?>"></div>
										<div class="delete" title="<?=GetMessage("REMOVE_CLICK");?>"></div>
									</div>
								</div>
							</div>

							<?if($bSearchMode):?>
								<?//$APPLICATION->RestartBuffer();?>
							<?endif;?>

							<div class="tabs">
								<div class="main-grid-loader-container">
									<div class="main-ui-loader main-ui-show">
										<svg class="main-ui-loader-svg" viewBox="25 25 50 50"><circle class="main-ui-loader-svg-circle" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"></circle></svg>
									</div>
								</div>
								<div class="main-grid-empty-inner"><div class="main-grid-empty-image"></div><div class="main-grid-empty-text"><?=GetMessage("NOTHING_FOUND");?></div></div>
								<div class="tabs-heading <?=($bSearchMode ? 'searched' : '');?>">
									<?$i = 0;?>
									<?foreach($moduleClass::$arParametrsList as $blockCode => $arBlock):?>
										<div class="head <?=($_COOKIE['activeTab_site_'.$optionsSiteID] !== false ? ($_COOKIE['activeTab_site_'.$optionsSiteID] == $i ? "active" : "") : (!$i ? "active" : ""));?>">
											<?=$arBlock["TITLE"]?>
										</div>
										<?$i++;?>
									<?endforeach;?>
								</div>
								<div class="tabs-content <?=($bSearchMode ? 'searched' : '');?>">
									<?$i = 0;?>
									<?foreach($moduleClass::$arParametrsList as $blockCode => $arBlock){?>

										<?if(isset($arBlock["CODE"]) && !$blockCode)
											$blockCode = "REQUIRED";?>

										<?if(in_array($blockCode,$arHideProps)) continue;?>

										<div class="tab <?=($_COOKIE['activeTab_site_'.$optionsSiteID] !== false ? ($_COOKIE['activeTab_site_'.$optionsSiteID] == $i ? "active" : "") : (!$i ? "active" : ""));?>" data-prop_code="<?=$blockCode?>">
											<div class="title bg"><?=$arBlock["TITLE"]?></div>
											<?foreach($arBlock["OPTIONS"] as $optionCode => $arOption){?>
												<?if(isset($arOption["ITEMS"])):?>
													<?$style = '';
													if(isset($arOption["PARENT"]))
													{
														if(\Bitrix\Main\Config\Option::get($moduleID, $arOption["PARENT"], "N", $optionsSiteID) == "N")
															$style = "style='display:none;'";
													}?>
													<div class="groups_block block <?=$optionCode?> <?=(isset($arOption["PARENT"]) ? "depend-block" : "");?>" <?=(isset($arOption["PARENT"]) ? "data-parent='".$arOption["PARENT"]."_".$optionsSiteID."'" : "");?> <?=$style;?>>
														<?if($arOption["TITLE"]):?>
															<div class="title"><?=$arOption["TITLE"];?></div>
														<?endif;?>

														<?if(isset($arOption["ONE_BLOCK"]) && $arOption["ONE_BLOCK"] == "Y"):?>
															<div class="block_wrapper">
														<?endif;?>

														<?foreach($arOption["ITEMS"] as $optionCode2 => $arOption2):?>
															<?=CMax::showAllAdminRows($optionCode2, $arTab, $arOption2, $moduleID, $arPersonTypes, $optionsSiteID, $arDeliveryServices, $arPaySystems, $arCurrency, $arOrderPropertiesByPerson, $bSearchMode);?>
														<?endforeach;?>

														<?if(isset($arOption["ONE_BLOCK"]) && $arOption["ONE_BLOCK"] == "Y"):?>
															</div>
														<?endif;?>
													</div>
												<?else:?>
													<div class="block">
														<?=CMax::showAllAdminRows($optionCode, $arTab, $arOption, $moduleID, $arPersonTypes, $optionsSiteID, $arDeliveryServices, $arPaySystems, $arCurrency, $arOrderPropertiesByPerson, $bSearchMode);?>
													</div>
												<?endif;?>
											<?}?>
										</div>

										<?$i++;?>
									<?}?>
								</div>
							</div>

							<?if($bSearchMode):?>
								<?die();?>
							<?endif;?>

						</div>
					</td>
				</tr>
			<?}
		}?>
		<?
		if($REQUEST_METHOD == "POST" && strlen($Update.$Apply.$RestoreDefaults) && check_bitrix_sessid())
		{
			if(strlen($Update) && strlen($_REQUEST["back_url_settings"]))
				LocalRedirect($_REQUEST["back_url_settings"]);
			else
				LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($mid)."&lang=".urlencode(LANGUAGE_ID)."&back_url_settings=".urlencode($_REQUEST["back_url_settings"])."&".$tabControl->ActiveTabParam());
		}?>
			<?$tabControl->Buttons();?>
			<input <?if($RIGHT < "W") echo "disabled"?> type="submit" name="Apply" class="submit-btn" value="<?=GetMessage("MAIN_OPT_APPLY")?>" title="<?=GetMessage("MAIN_OPT_APPLY_TITLE")?>">
			<?if(strlen($_REQUEST["back_url_settings"]) > 0): ?>
				<input type="button" name="Cancel" value="<?=GetMessage("MAIN_OPT_CANCEL")?>" title="<?=GetMessage("MAIN_OPT_CANCEL_TITLE")?>" onclick="window.location='<?=htmlspecialcharsbx(CUtil::addslashes($_REQUEST["back_url_settings"]))?>'">
				<input type="hidden" name="back_url_settings" value="<?=htmlspecialcharsbx($_REQUEST["back_url_settings"])?>">
			<?endif;?>
			<?if(CMax::IsCompositeEnabled()):?>
				<div class="adm-info-message"><?=GetMessage("WILL_CLEAR_HTML_CACHE_NOTE")?></div><div style="clear:both;"></div>
			<?endif;?>
			<script type="text/javascript">
				var arOrderPropertiesByPerson = <?=CUtil::PhpToJSObject($arOrderPropertiesByPerson, false)?>;

				// onAdminFixerUnfix
				BX.addCustomEvent(window, "onFixedNodeChangeState", function(){
					/*if($('#tabControl_tabs').hasClass('bx-fixed-top'))
					{
						$('.tabs-wrapper')
					}*/
				});

				$(window).scroll(function(){
					var scroll = BX.GetWindowScrollPos();
					// console.log($('#tabControl_tabs').attr('class'));
					// BX.adminPanel.isFixed()

				})

				$(document).ready(function() {
					// $(window).scroll()
					/*setTimeout(function(){
						console.log($('#tabControl_tabs').attr('class'));
					},100)*/
					$('input[name^="THEME_SWITCHER"]').change(function(){
						var ischecked = $(this).attr('checked');
						if(typeof(ischecked) != 'undefined')
						{
							if(!confirm("<?=GetMessage("NO_COMPOSITE_NOTE")?>"))
								$(this).removeAttr('checked');
						}
					});

					// $(".tabs-wrapper .search_wrapper input").on('input propertychange',function(){
					$(".tabs-wrapper .search_wrapper .delete").click(function(){
						$(".tabs-wrapper .search_wrapper input").val('');
						//$(".tabs-wrapper .search_wrapper input").trigger('textchange');
						searchParams();
						$(this).parent().find('div').hide();
					});
					$(".tabs-wrapper .search_wrapper .search").click(function(){
						//$(".tabs-wrapper .search_wrapper input").trigger('textchange')
						searchParams();
					});




					$(".tabs-wrapper .search_wrapper input").on('textchange',delayTextChange(searchParams, 1000));


					/*$(".tabs-wrapper .search_wrapper input").on('textchange',function(){
						var _this = $(this),
							val = _this.val(),
							site = _this.data('site'),
							wrapper = _this.closest('.tabs-wrapper');

						if(!val || val.length > 2)
							wrapper.addClass('loading');

						wrapper.find('.main-grid-empty-inner').hide();
						wrapper.find('.search_wrapper .delete').show();


						$.ajax({
							type: 'POST',
							dataType: 'html',
							data: {q: val, site: site},
							success: function(html){
								val = val.replace(/^\s+/g, '');
								wrapper.removeClass('loading');
								if(val)
								{
									if(val.length > 2)
									{
										wrapper.addClass('searched');
										wrapper.find('.search_wrapper .search').show();

										$(html).find('.tabs-content .js_block').each(function(){
											var _this2 = $(this),
												item = wrapper.find('.tabs .tabs-content .js_block[data-class="'+_this2.data('class')+'"]');

											if(_this2.data('search'))
											{
												if(item.find(' > div').attr('style') == undefined)
												{

													if(item.attr('style') == undefined || (item.attr('style') && item.attr('style').indexOf('block') !== -1)) //fix fo hidden elements
													{
														item.addClass(_this2.data('search'));
														if(item.data('class').indexOf('GOOGLE') !== -1)
															wrapper.find('.tabs-content .js_block[data-class="USE_GOOGLE_RECAPTCHA"]').addClass(_this2.data('search'));
													}

												}
												else
													item.removeClass('visible_block');
											}
											else
											{
												item.removeClass('visible_block');
											}
										})

										wrapper.find('.tabs-content .js_block').each(function(){
											var _this2 = $(this);

											if(_this2.hasClass('includefile') && _this2.data('class') != 'ALL_COUNTERS' && _this2.data('class') != 'LICENCE_TEXT' && _this2.data('class') != 'LOGO_IMAGE_SVG')
											{
												if(!_this2.is(':visible'))
													_this2.removeClass('visible_block');
											}

											if(_this2.closest('.block').find('.js_block.visible_block').length)
												_this2.closest('.block').addClass('visible_block');
											else
												_this2.closest('.block').removeClass('visible_block');
										})

										wrapper.find('.tabs-content > .tab').each(function(){
											var _this2 = $(this);

											if(_this2.find(' > .block.visible_block').length)
											{
												_this2.addClass('visible_block');
												wrapper.find('.tabs-heading .head:eq('+_this2.index()+')').addClass('visible_block');
											}
											else
											{
												_this2.removeClass('visible_block');
												wrapper.find('.tabs-heading .head:eq('+_this2.index()+')').removeClass('visible_block');
											}
										})

										wrapper.find('.tabs-heading').each(function(){
											var _this2 = $(this);
											if(_this2.find(' > .head.visible_block').length)
												_this2.addClass('visible_block');
											else
												_this2.removeClass('visible_block');
										})

										if(wrapper.find('.tabs-heading').hasClass('visible_block'))
										{
											if(!wrapper.find('.tabs-heading .head.active').hasClass('visible_block'))
											{
												wrapper.find('.tabs-heading .head').removeClass('active');
												wrapper.find('.tabs-heading .head.visible_block:first').addClass('active');

												wrapper.find('.tabs-content .tab').removeClass('active');
												wrapper.find('.tabs-content .tab.visible_block:first').addClass('active');
											}
											wrapper.find('.main-grid-empty-inner').hide();
										}
										else
										{
											wrapper.find('.main-grid-empty-inner').fadeIn();
										}
									}
									else
									{
										if(!wrapper.find('.tabs-heading').hasClass('visible_block') && wrapper.hasClass('searched'))
											wrapper.find('.main-grid-empty-inner').fadeIn();
									}
								}
								else
								{
									wrapper.removeClass('searched');
									// wrapper.find('.tabs-content .block').removeAttr('style');
									wrapper.find('.tabs-content .item').removeClass('visible_block');
								}
							},
							error: function(jqXHR){
								console.log(jqXHR);
							}
						})
					});*/

					/*set active tab*/
					$('.tabs-wrapper .tabs-heading .head').click(function(){
						var _this = $(this);
						_this.siblings().removeClass('active');
						_this.addClass('active');
						_this.closest('.tabs-wrapper').find('.tabs-content .tab').removeClass('active');
						_this.closest('.tabs-wrapper').find('.tabs-content .tab:eq('+_this.index()+')').addClass('active');

						if(!!document.cookie)
							document.cookie = 'activeTab_'+_this.closest('td').attr('class')+'='+_this.index()
					})

					/*set active color*/
					$('.bases_block .base_color').click(function(){
						var _this = $(this);
						_this.siblings().removeClass('current');
						_this.addClass('current');
						_this.closest('.bases_block').find('input[type="hidden"]').val(_this.data('value'));

						_this.closest('.groups_block').find('.base_color_custom').removeClass('current');
						_this.closest('.groups_block').find('.base_color_custom .click_block').removeAttr('style');
						_this.closest('.groups_block').find('.base_color_custom .click_block .vals').text('#');
						_this.closest('.groups_block').find('.base_color_custom input[type="hidden"]').val('');
					});

					/*spectrum*/
					if($('.base_color_custom input[type=hidden]').length)
					{
						$('.base_color_custom input[type=hidden]').each(function(){
							var _this = $(this),
								parent = $(this).closest('.base_color_custom');
							_this.spectrum({
								preferredFormat: 'hex',
								showButtons: true,
								showInput: true,
								showPalette: false,
								appendTo: parent,
								chooseText: '<?=GetMessage('CUSTOM_COLOR_CHOOSE');?>',
								cancelText: '<?=GetMessage('CUSTOM_COLOR_CANCEL');?>',
								containerClassName: 'custom_picker_container',
								replacerClassName: 'custom_picker_replacer',
								clickoutFiresChange: false,
								move: function(color) {
									var colorCode = color.toHexString();
									parent.find('span span.bg').attr('style', 'background:' + colorCode);
								},
								hide: function(color) {
									var colorCode = color.toHexString();
									parent.find('span span.bg').attr('style', 'background:' + colorCode);
								},
								change: function(color) {
									var colorCode = color.toHexString();
									parent.addClass('current').siblings().removeClass('current');

									parent.find('span span.vals').text(colorCode);
									parent.find('span.animation-all').attr('style', 'border-color:' + colorCode);

									$('input[name=' + parent.find('.click_block').data('option-id') + ']').val(parent.find('.click_block').data('option-value'));
									$('input[name=' + parent.find('.click_block').data('option-id') + ']').siblings().removeClass('current');
								}
							});
						})
					}

					$('.base_color_custom').click(function(e) {
						e.preventDefault();
						$('input[name='+$(this).data('name')+']').spectrum('toggle');
						return false;
					});
					/**/

					/* href for phones */
					if(typeof window.JRegionPhone === 'function'){
						$('.item.array[data-class=HEADER_PHONES] .aspro-admin-item').each(function(){
							window.JRegionPhone._bindTitleChange(this);
						});
					}

					/* sort order for arrays */
					$('.adm-detail-content .item .aspro-admin-item').each(function(){
						(function(sort_block){
							Sortable.create(sort_block,{
								handle: '.drag',
								animation: 150,
								forceFallback: true,
								filter: '.no_drag',
								onChoose: function (evt) {
									$(sort_block).addClass('sortable-started');
								},
								onUnchoose: function(evt) {
									$(sort_block).removeClass('sortable-started');
								},
								onStart: function(evt){
									$(sort_block).addClass('sortable-started');
									window.getSelection().removeAllRanges();
								},
								onEnd: function(evt){
									$(sort_block).removeClass('sortable-started');
								},
								onMove: function(evt){
									return evt.related.className.indexOf('no_drag') === -1;
								},
								onUpdate: function(evt){
									try{
										var current_type = $(sort_block).data('key');
										if(!current_type){
											var keys = [];
											var inputsNames = [];
											var rows = Array.prototype.slice.call(sort_block.querySelectorAll('.wrapper'));
											for(var j in rows){
												keys.push(j * 1);

												var names = [];
												var inputs = Array.prototype.slice.call(rows[j].querySelectorAll('input'));
												for(var k in inputs){
													names.push(inputs[k].getAttribute('name'));
												}
												inputsNames.push(names);
											}

											var k = evt.oldIndex;
											do{
												keys[k] = (k == evt.oldIndex ? evt.newIndex : (evt.newIndex > evt.oldIndex ? k - 1 : k + 1)) ;
												evt.newIndex > evt.oldIndex ? ++k : --k;
											}
											while(evt.newIndex > evt.oldIndex ? k <= evt.newIndex : k >= evt.newIndex);

											for(var j in rows){
												if(keys[j] != j){
													var inputs = Array.prototype.slice.call(rows[j].querySelectorAll('input'));
													for(var k in inputs){
														inputs[k].setAttribute('name', inputsNames[keys[j]][k]);
													}
												}
											}
										}
										else{
											var order = [];
											var itemEl = evt.item;
											var current_site = $(sort_block).data('site');
											$(sort_block).find('.block').each(function(){
												order.push($(this).find('input[type="checkbox"]').attr('name').replace(current_type+'_', '').replace('_'+current_site, ''))
											})
											$(sort_block).closest('.parent-wrapper').find('input[name^=SORT_ORDER_INDEX_TYPE_'+current_type+']').val(order.join(','));
										}
									}
									catch(e){
										console.error(e);
									}
								},
							});
						})(this);
					});

					$(document).on('click', '.item.array .aspro-admin-item .remove', function(){
						var $array = $(this).closest('.item.array');
						$(this).closest('.wrapper').remove();
						if(!$array.find('.aspro-admin-item .wrapper:not(.has_title)').length){
							$array.addClass('empty_block');
						}
					})

					$('.item.array .adm-btn-save.adm-btn-add').click(function(){
						var _this = $(this);
						var newItemHtml = _this.closest('.item.array').find('.new-item-html').html();
						var $array = _this.closest('.item.array');
						newItemHtml = newItemHtml.replace(/#INDEX#/g, $array.find('.aspro-admin-item .wrapper').length);
						$(newItemHtml).appendTo($array.find('.aspro-admin-item'));
						$array.removeClass('empty_block');
					})
					/**/

					/*set active page*/
					$('.block_with_img .link-item').on('click', function(){
						var _this = $(this);
						_this.closest('.rows').find('.link-item').removeClass('current');
						_this.addClass('current');
						_this.closest('.block_with_img').find('input[type="hidden"]').val(_this.data('value'));
						/*page contacts*/
						if(_this.closest('div[data-optioncode="PAGE_CONTACTS"]').length)
						{
							var tab = _this.closest('.tab');
							if(_this.data('value') == 'custom')
							{
								tab.find('.SECTION_CONTACTS_GROUP2').hide();
								tab.find('.SECTION_CONTACTS_GROUP3').hide();
							}
							else
							{
								tab.find('.SECTION_CONTACTS_GROUP2').show();
								tab.find('.SECTION_CONTACTS_GROUP3').show();

								if(_this.data('value') < 3)
								{
									tab.find('[name^="CONTACTS_PHONE"]').closest('.item').show();
									tab.find('[name^="CONTACTS_REGIONAL_PHONE"]').closest('.item').hide();
									tab.find('[name^="CONTACTS_SCHEDULE"]').closest('.item').show();
									tab.find('[name^="CONTACTS_DESCRIPTION12"]').closest('.item').show();
									tab.find('[name^="CONTACTS_REGIONAL_DESCRIPTION34"]').closest('.item').hide();
									tab.find('[name^="CONTACTS_REGIONAL_DESCRIPTION5"]').closest('.item').hide();
									tab.find('[name^="CONTACTS_USE_FEEDBACK"]').closest('.item').show();
									tab.find('[name^="CONTACTS_MAP"]').first().closest('.item').show();
									tab.find('[name^="CONTACTS_MAP_NOTE"]').closest('.notes-block').show();
								}
								else
								{
									tab.find('[name^="CONTACTS_PHONE"]').closest('.item').show();
									tab.find('[name^="CONTACTS_REGIONAL_PHONE"]').closest('.item').hide();
									tab.find('[name^="CONTACTS_SCHEDULE"]').closest('.item').hide();

									if(_this.data('value') < 5)
									{
										tab.find('[name^="CONTACTS_ADDRESS"]').closest('.item').show();
										tab.find('[name^="CONTACTS_DESCRIPTION12"]').closest('.item').hide();
										tab.find('[name^="CONTACTS_REGIONAL_DESCRIPTION34"]').closest('.item').show();
										tab.find('[name^="CONTACTS_REGIONAL_DESCRIPTION5"]').closest('.item').hide();
										tab.find('[name^="CONTACTS_USE_FEEDBACK"]').closest('.item').show();
									}
									else
									{
										tab.find('[name^="CONTACTS_ADDRESS"]').closest('.item').hide();
										tab.find('[name^="CONTACTS_DESCRIPTION12"]').closest('.item').hide();
										tab.find('[name^="CONTACTS_REGIONAL_DESCRIPTION34"]').closest('.item').hide();
										tab.find('[name^="CONTACTS_REGIONAL_DESCRIPTION5"]').closest('.item').show();
										tab.find('[name^="CONTACTS_USE_FEEDBACK"]').closest('.item').hide();
									}

									tab.find('[name^="CONTACTS_MAP"]').first().closest('.item').hide();
									tab.find('[name^="CONTACTS_MAP_NOTE"]').closest('.notes-block').hide();
								}
							}
						}

						if (_this.closest('[data-class="FILTER_VIEW"]').length) {
							_this.closest('.item').find('.depend-block').hide();
							_this.closest('.item').find('.depend-block[data-show="'+_this.data('value')+'"]').show();
						}

						/*index page*/
						if(_this.closest('div[data-optioncode="INDEX_TYPE"]').length)
						{
							_this.closest('.item').find('.js-sub').fadeOut();
							_this.closest('.item').find('.block_'+_this.data('value')+'_'+_this.data('site')+' div.block').show();
							_this.closest('.item').find('.block_'+_this.data('value')+'_'+_this.data('site')).fadeIn();
						}
					});

					/*scroll btn action*/
					$('select[name^="SCROLLTOTOP_TYPE"]').change(function() {
						var posSelect = $(this).closest('.tab').find('select[name^="SCROLLTOTOP_POSITION"]');
						if(posSelect){
							var posSelectTr = posSelect.closest('.item');
							var isNone = $(this).val().indexOf('NONE') != -1;
							if(isNone)
							{
								if(posSelectTr.is(':visible'))
									posSelectTr.fadeOut();
							}
							else
							{
								if(!posSelectTr.is(':visible'))
									posSelectTr.fadeIn();
								var isRound = $(this).val().indexOf('ROUND') != -1;
								var isTouch = posSelect.val().indexOf('TOUCH') != -1;
								if(isRound && !!posSelect)
								{
									posSelect.find('option[value^="TOUCH"]').attr('disabled', 'disabled');
									if(isTouch)
										posSelect.val(posSelect.find('option[value^="PADDING"]').first().attr('value'));
								}
								else
								{
									posSelect.find('option[value^="TOUCH"]').removeAttr('disabled');
								}
							}
						}
					});

					$('select[name^="SCROLLTOTOP_TYPE"]').change();
					$('.block_with_img .link-item.current').trigger('click');
				});


				function delayTextChange(callback, ms) {
				  var timer = 0;
					return function() {
						var context = this, args = arguments;
						clearTimeout(timer);
						timer = setTimeout(function () {
							callback.apply(context, args);
						}, ms || 0);
					};
				}

				function searchParams(){
					var _this = $(".tabs-wrapper .search_wrapper input"),
						val = _this.val(),
						site = _this.data('site'),
						wrapper = _this.closest('.tabs-wrapper');

					if(!val || val.length > 2)
						wrapper.addClass('loading');

					wrapper.find('.main-grid-empty-inner').hide();
					wrapper.find('.search_wrapper .delete').show();


					$.ajax({
						type: 'POST',
						dataType: 'html',
						data: {q: val, site: site},
						success: function(html){
							val = val.replace(/^\s+/g, '');
							wrapper.removeClass('loading');
							if(val)
							{
								if(val.length > 2)
								{
									wrapper.addClass('searched');
									wrapper.find('.search_wrapper .search').show();

									$(html).find('.tabs-content .js_block').each(function(){
										var _this2 = $(this),
											item = wrapper.find('.tabs .tabs-content .js_block[data-class="'+_this2.data('class')+'"]');

										if(_this2.data('search'))
										{
											if(item.find(' > div').attr('style') == undefined)
											{
												/*console.log(item);
												console.log(item.attr('style'));*/
												if(item.attr('style') == undefined || (item.attr('style') && item.attr('style').indexOf('block') !== -1)) //fix fo hidden elements
												{
													item.addClass(_this2.data('search'));
													if(item.data('class').indexOf('GOOGLE') !== -1)
														wrapper.find('.tabs-content .js_block[data-class="USE_GOOGLE_RECAPTCHA"]').addClass(_this2.data('search'));
												}

											}
											else
												item.removeClass('visible_block');
										}
										else
										{
											item.removeClass('visible_block');
										}
									})

									wrapper.find('.tabs-content .js_block').each(function(){
										var _this2 = $(this);

										if(_this2.hasClass('includefile') && _this2.data('class') != 'ALL_COUNTERS' && _this2.data('class') != 'LICENCE_TEXT' && _this2.data('class') != 'LOGO_IMAGE_SVG')
										{
											if(!_this2.is(':visible'))
												_this2.removeClass('visible_block');
										}

										if(_this2.closest('.block').find('.js_block.visible_block').length)
											_this2.closest('.block').addClass('visible_block');
										else
											_this2.closest('.block').removeClass('visible_block');
									})

									wrapper.find('.tabs-content > .tab').each(function(){
										var _this2 = $(this);

										if(_this2.find(' > .block.visible_block').length)
										{
											_this2.addClass('visible_block');
											wrapper.find('.tabs-heading .head:eq('+_this2.index()+')').addClass('visible_block');
										}
										else
										{
											_this2.removeClass('visible_block');
											wrapper.find('.tabs-heading .head:eq('+_this2.index()+')').removeClass('visible_block');
										}
									})

									wrapper.find('.tabs-heading').each(function(){
										var _this2 = $(this);
										if(_this2.find(' > .head.visible_block').length)
											_this2.addClass('visible_block');
										else
											_this2.removeClass('visible_block');
									})

									if(wrapper.find('.tabs-heading').hasClass('visible_block'))
									{
										if(!wrapper.find('.tabs-heading .head.active').hasClass('visible_block'))
										{
											wrapper.find('.tabs-heading .head').removeClass('active');
											wrapper.find('.tabs-heading .head.visible_block:first').addClass('active');

											wrapper.find('.tabs-content .tab').removeClass('active');
											wrapper.find('.tabs-content .tab.visible_block:first').addClass('active');
										}
										wrapper.find('.main-grid-empty-inner').hide();
									}
									else
									{
										wrapper.find('.main-grid-empty-inner').fadeIn();
									}
								}
								else
								{
									if(!wrapper.find('.tabs-heading').hasClass('visible_block') && wrapper.hasClass('searched'))
										wrapper.find('.main-grid-empty-inner').fadeIn();
								}
							}
							else
							{
								wrapper.removeClass('searched');
								// wrapper.find('.tabs-content .block').removeAttr('style');
								wrapper.find('.tabs-content .item').removeClass('visible_block');
							}
							var btnWrap = $('.max_options.views .adm-detail-content-btns-wrap');
							if(btnWrap.length) {
								btnWrap.each(function(i, el) {
									if( el.BXFIXER !== undefined ) {
										el.BXFIXER._recalc_pos();
									}
								})
							}
						},
						error: function(jqXHR){
							console.log(jqXHR);

							var btnWrap = $('.max_options.views .adm-detail-content-btns-wrap');
							if(btnWrap.length) {
								btnWrap.each(function(i, el) {
									if( el.BXFIXER !== undefined ) {
										el.BXFIXER._recalc_pos();
									}
								})
							}
						}
					})
				}


				function CheckActive(){
					$('input[name^="USE_WORD_EXPRESSION"]').each(function() {
						var input = this;
						var isActiveUseExpressions = $(input).attr('checked') == 'checked';
						var tab = $(input).parents('.adm-detail-content-item-block');
						if(!isActiveUseExpressions)
						{
							tab.find('input[name^="MAX_AMOUNT"]').attr('disabled', 'disabled');
							tab.find('input[name^="MIN_AMOUNT"]').attr('disabled', 'disabled');
							tab.find('input[name^="EXPRESSION_FOR_MIN"]').attr('disabled', 'disabled');
							tab.find('input[name^="EXPRESSION_FOR_MAX"]').attr('disabled', 'disabled');
							tab.find('input[name^="EXPRESSION_FOR_MID"]').attr('disabled', 'disabled');
						}
						else
						{
							tab.find('input[name^="MAX_AMOUNT"]').removeAttr('disabled');
							tab.find('input[name^="MIN_AMOUNT"]').removeAttr('disabled');
							tab.find('input[name^="EXPRESSION_FOR_MIN"]').removeAttr('disabled');
							tab.find('input[name^="EXPRESSION_FOR_MAX"]').removeAttr('disabled');
							tab.find('input[name^="EXPRESSION_FOR_MID"]').removeAttr('disabled');
						}
					});

					$('select[name^="BUYMISSINGGOODS"]').each(function() {
						var select = this;
						var BuyMissingGoodsVal = $(select).val();
						var tab = $(select).parents('.adm-detail-content-item-block');
						tab.find('input[name^="EXPRESSION_SUBSCRIBE_BUTTON"]').attr('disabled', 'disabled');
						tab.find('input[name^="EXPRESSION_SUBSCRIBED_BUTTON"]').attr('disabled', 'disabled');
						tab.find('input[name^="EXPRESSION_ORDER_BUTTON"]').attr('disabled', 'disabled');
						if(BuyMissingGoodsVal == 'SUBSCRIBE')
						{
							tab.find('input[name^="EXPRESSION_SUBSCRIBE_BUTTON"]').removeAttr('disabled');
							tab.find('input[name^="EXPRESSION_SUBSCRIBED_BUTTON"]').removeAttr('disabled');
						}
						else if(BuyMissingGoodsVal == 'ORDER')
						{
							tab.find('input[name^="EXPRESSION_ORDER_BUTTON"]').removeAttr('disabled');
						}
					});
				}

				function checkGoalsNote(){
					var inUAC = $('.adm-detail-content-table:visible').first().find('.item input[id^=YA_GOALS]');
					var itrYACID = $('.adm-detail-content-table:visible').first().find('div.YA_COUNTER_ID');
					var itrGNote = $('.adm-detail-content-table:visible').first().find('div.GOALS_NOTE');
					var itrUFG = $('.adm-detail-content-table:visible').first().find('div.USE_FORMS_GOALS');
					var itrUBG = $('.adm-detail-content-table:visible').first().find('div.USE_BASKET_GOALS');
					var itrU1CG = $('.adm-detail-content-table:visible').first().find('div.USE_1CLICK_GOALS');
					var itrUQOG = $('.adm-detail-content-table:visible').first().find('div.USE_FASTORDER_GOALS');
					var itrUFOG = $('.adm-detail-content-table:visible').first().find('div.USE_FULLORDER_GOALS');
					var itrUDG = $('.adm-detail-content-table:visible').first().find('div.USE_DEBUG_GOALS');

					if(inUAC.length && inUAC.attr('checked'))
					{
						var bShowNote = 6;

						if(itrUFG.find('select').val().indexOf('NONE') == -1)
						{
							itrGNote.find('[data-goal=form]').show();
						}
						else
						{
							itrGNote.find('[data-goal=form]').hide();
							--bShowNote;
						}

						if(itrUBG.find('input').attr('checked'))
						{
							itrGNote.find('[data-goal=basket]').show();
						}
						else
						{
							itrGNote.find('[data-goal=basket]').hide();
							--bShowNote;
						}

						if(itrU1CG.find('input').attr('checked'))
						{
							itrGNote.find('[data-goal=1click]').show();
						}
						else
						{
							itrGNote.find('[data-goal=1click]').hide();
							--bShowNote;
						}

						if(itrUQOG.find('input').attr('checked'))
						{
							itrGNote.find('[data-goal=fastorder]').show();
						}
						else
						{
							itrGNote.find('[data-goal=fastorder]').hide();
							--bShowNote;
						}

						if(itrUFOG.find('input').attr('checked'))
						{
							itrGNote.find('[data-goal=fullorder]').show();
						}
						else
						{
							itrGNote.find('[data-goal=fullorder]').hide();
							--bShowNote;
						}

						if(itrUDG.find('input').attr('checked'))
						{
							itrGNote.find('[data-goal=debug]').show();
						}
						else
						{
							itrGNote.find('[data-goal=debug]').hide();
							--bShowNote;
						}

						if(bShowNote)
						{
							itrGNote.find('.inner_wrapper').show();
						}
						else
						{
							itrGNote.find('.inner_wrapper').hide();
						}
					}
					else
					{
						itrGNote.find('.inner_wrapper').hide();
					}
				}
			</script>
			<script type="text/javascript">
				$(document).ready(function(){
					CheckActive();

					$('form.max_options').submit(function(e) {
						$(this).attr('id', 'max_options');
						$(this).find('input').removeAttr('disabled');
					});

					$('form.max_options .tabs-wrapper .tabs-heading .head').click(function() {
						var btnWrap = $('.max_options.views .adm-detail-content-btns-wrap');
						if(btnWrap.length) {
							btnWrap.each(function(i, el) {
								if( el.BXFIXER !== undefined ) {
									el.BXFIXER._recalc_pos();
								}
							})
						}
					});

					$('input.depend-check').change(function() {
						var ischecked = $(this).prop('checked'),
							depend_block = $('.depend-block[data-parent='+$(this).attr('id')+']');
						if(depend_block.length && $(this).attr('id').indexOf('YA_GOALS') < 0)
						{
							if(typeof(depend_block.data('show')) != 'undefined')
							{
								if(depend_block.data('show') == 'Y')
								{
									if(ischecked)
										depend_block.fadeIn();
									else
										depend_block.fadeOut();
								}
								else
								{
									if(ischecked)
										depend_block.fadeOut();
									else
										depend_block.fadeIn();
								}
							}
						}
					});

					$('select.depend-check').change(function() {

						var value = $(this).prop('value'),
							depend_block = $('.depend-block[data-parent='+$(this).attr('name')+']');

						if(depend_block.length && $(this).attr('name').indexOf('YA_GOALS') < 0)
						{
							if(typeof(depend_block.data('show')) != 'undefined')
							{
								if(depend_block.data('show') == value)
								{
									depend_block.fadeIn();
								} else {
									depend_block.fadeOut();
								}
							}
						}
					});
				})

				$('input[name^="SHOW_BG_BLOCK"]').change(function(){
					if($(this).attr('checked') != 'checked')
					{
						$(this).closest('.groups_block').find('div[data-class="BGCOLOR_THEME"]').fadeOut();
						$(this).closest('.groups_block').find('div[data-class="CUSTOM_BGCOLOR_THEME"]').fadeOut();
					}
					else
					{
						$(this).closest('.groups_block').find('div[data-class="BGCOLOR_THEME"]').fadeIn();
						$(this).closest('.groups_block').find('div[data-class="CUSTOM_BGCOLOR_THEME"]').fadeIn();
					}
				});

				$('select[name^="USE_FORMS_GOALS"]').change(function() {
					var parent = $(this).closest('.depend-block').data('parent');
					var inUAC = $(this).closest('.tab').find('input#'+parent);
					if(inUAC.length && inUAC.attr('checked')){
						var isNone = $(this).val().indexOf('NONE') != -1;
						var isCommon = $(this).val().indexOf('COMMON') != -1;
						var itrGNote = $(this).closest('.tab').find('div.GOALS_NOTE');
						if(!isNone)
						{
							if(isCommon)
							{
								itrGNote.find('[data-value=common]').show();
								itrGNote.find('[data-value=single]').hide();
							}
							else
							{
								itrGNote.find('[data-value=common]').hide();
								itrGNote.find('[data-value=single]').show();
							}
							itrGNote.find('[data-goal=form]').show();
						}
						else
						{
							itrGNote.find('[data-goal=form]').hide();
						}
					}

					checkGoalsNote();
				});

				$('input[name^="USE_BASKET_GOALS"]').change(function() {
					var parent = $(this).closest('.depend-block').data('parent');
					var inUAC = $(this).closest('.tab').find('input#'+parent);
					if(inUAC.length && inUAC.attr('checked'))
					{
						var itrGNote = $(this).closest('.tab').find('div[data-optioncode=GOALS_NOTE]');
						var ischecked = $(this).attr('checked');
						if(typeof(ischecked) != 'undefined')
							itrGNote.find('[data-goal=basket]').show();
						else
							itrGNote.find('[data-goal=basket]').hide();
					}

					checkGoalsNote();
				});

				$('input[name^="USE_1CLICK_GOALS"]').change(function() {
					var parent = $(this).closest('.depend-block').data('parent');
					var inUAC = $(this).closest('.tab').find('input#'+parent);
					if(inUAC.length && inUAC.attr('checked')){
						var itrGNote = $(this).closest('.tab').find('div[data-optioncode=GOALS_NOTE]');
						var ischecked = $(this).attr('checked');
						if(typeof(ischecked) != 'undefined')
							itrGNote.find('[data-goal=1click]').show();
						else
							itrGNote.find('[data-goal=1click]').hide();
					}

					checkGoalsNote();
				});

				$('input[name^="USE_FASTORDER_GOALS"]').change(function() {
					var parent = $(this).closest('.depend-block').data('parent');
					var inUAC = $(this).closest('.tab').find('input#'+parent);
					if(inUAC.length && inUAC.attr('checked'))
					{
						var itrGNote = $(this).closest('.tab').find('div[data-optioncode=GOALS_NOTE]');
						var ischecked = $(this).attr('checked');
						if(typeof(ischecked) != 'undefined')
							itrGNote.find('[data-goal=fastorder]').show();
						else
							itrGNote.find('[data-goal=fastorder]').hide();
					}

					checkGoalsNote();
				});

				$('input[name^="USE_FULLORDER_GOALS"]').change(function() {
					var parent = $(this).closest('.depend-block').data('parent');
					var inUAC = $(this).closest('.tab').find('input#'+parent);
					if(inUAC.length && inUAC.attr('checked'))
					{
						var itrGNote = $(this).closest('.tab').find('div[data-optioncode=GOALS_NOTE]');
						var ischecked = $(this).attr('checked');
						if(typeof(ischecked) != 'undefined')
							itrGNote.find('[data-goal=fullorder]').show();
						else
							itrGNote.find('[data-goal=fullorder]').hide();
					}

					checkGoalsNote();
				});

				$('input[name^="USE_DEBUG_GOALS"]').change(function() {
					var parent = $(this).closest('.depend-block').data('parent');
					var inUAC = $(this).closest('.tab').find('input#'+parent);
					if(inUAC.length && inUAC.attr('checked'))
					{
						var itrGNote = $(this).closest('.tab').find('div[data-optioncode=GOALS_NOTE]');
						var ischecked = $(this).attr('checked');
						if(typeof(ischecked) != 'undefined')
							itrGNote.find('[data-goal=debug]').show();
						else
							itrGNote.find('[data-goal=debug]').hide();
					}

					checkGoalsNote();
				});

				$('input[name^="YA_GOALS"]').change(function(){
					var tab = $(this).closest('.tab');
					var itrYACID = tab.find('div.YA_COUNTER_ID');
					var itrUFG = tab.find('div.USE_FORMS_GOALS');
					var itrUBG = tab.find('div.USE_BASKET_GOALS');
					var itrU1CG = tab.find('div.USE_1CLICK_GOALS');
					var itrUQOG = tab.find('div.USE_FASTORDER_GOALS');
					var itrUFOG = tab.find('div.USE_FULLORDER_GOALS');
					var itrUDG = tab.find('div.USE_DEBUG_GOALS');
					var itrGNote = tab.find('div.GOALS_NOTE');
					var ischecked = $(this).attr('checked');
					if(typeof(ischecked) != 'undefined')
					{
						itrYACID.fadeIn();
						itrUFG.fadeIn();
						var valUFG = itrUFG.find('select').val();

						if(valUFG.indexOf('NONE') == -1)
						{
							var isCommon = valUFG.indexOf('COMMON') != -1;
							if(isCommon)
							{
								itrGNote.find('[data-value=common]').show();
								itrGNote.find('[data-value=single]').hide();
							}
							else
							{
								itrGNote.find('[data-value=common]').hide();
								itrGNote.find('[data-value=single]').show();
							}
							itrGNote.fadeIn();
						}
						itrUBG.fadeIn();
						itrU1CG.fadeIn();
						itrUQOG.fadeIn();
						itrUFOG.fadeIn();
						itrUDG.fadeIn();
					}
					else
					{
						itrYACID.fadeOut();
						itrUFG.fadeOut();
						itrUBG.fadeOut();
						itrU1CG.fadeOut();
						itrUQOG.fadeOut();
						itrUFOG.fadeOut();
						itrUDG.fadeOut();
						itrGNote.fadeOut();
					}
					checkGoalsNote();
				});

				$('input[name^="USE_WORD_EXPRESSION"], select[name^="BUYMISSINGGOODS"]').change(function() {
					CheckActive();
				});

				$('select[name^="SHOW_SECTION_DESCRIPTION"]').change(function(){
					if($(this).val() != 'BOTH')
						$(this).closest('.block').find('select[name*="SECTION_DESCRIPTION_POSITION"]').closest('.item').fadeOut();
					else
						$(this).closest('.block').find('select[name*="SECTION_DESCRIPTION_POSITION"]').closest('.item').fadeIn();
				});

				$('input[name^="USE_PRIORITY_SECTION_DESCRIPTION_SOURCE"]').change(function(){
					if($(this).prop('checked'))
						$(this).closest('.block').find('input[name^="PRIORITY_SECTION_DESCRIPTION_SOURCE"]').closest('.item:not(.array)').fadeIn();
					else
						$(this).closest('.block').find('input[name^="PRIORITY_SECTION_DESCRIPTION_SOURCE"]').closest('.item:not(.array)').fadeOut();
				});

				$('select[name^="SHOW_QUANTITY_FOR_GROUPS"]').change(function() {
					var val = $(this).val();
					var tab = $(this).parents('.adm-detail-content-item-block');
					var sqcg = tab.find('select[name^="SHOW_QUANTITY_COUNT_FOR_GROUPS"]');

					var isAll = false;
					if(val)
						isAll = val.indexOf('2') !== -1;

					if(!isAll)
					{
						$(this).find('option').each(function() {
							if($(this).attr('selected') != 'selected')
								sqcg.find('option[value="' + $(this).attr('value') + '"]').removeAttr('selected');
						});
					}
				});

				$('select[name^="SHOW_QUANTITY_COUNT_FOR_GROUPS"]').change(function(e) {
					e.stopPropagation();
					var val = $(this).val();
					var tab = $(this).parents('.adm-detail-content-item-block');
					var sqg_val = tab.find('select[name^="SHOW_QUANTITY_FOR_GROUPS"]').val();

					if(!sqg_val)
					{
						$(this).find('option').removeAttr('selected');
						return;
					}

					var isAll = false;
					if(sqg_val)
						isAll = sqg_val.indexOf('2') !== -1;

					if(!isAll && val)
					{
						for(i in val)
						{
							var g = val[i];
							if(sqg_val.indexOf(g) === -1)
								$(this).find('option[value="' + g + '"]').removeAttr('selected');
						}
					}
				});

				$('select[name^="ONECLICKBUY_PERSON_TYPE"]').change(function() {
					if(typeof arOrderPropertiesByPerson !== 'undefined'){
						var table = $(this).closest('.tab');
						var value = $(this).val();
						var site = $(this).data('site');
						if(typeof value !== 'undefined' && typeof arOrderPropertiesByPerson[value] !== 'undefined')
						{
							var arSelects = [table.find('div[data-optioncode="ONECLICKBUY_PROPERTIES"] .props'), table.find('div[data-optioncode="ONECLICKBUY_REQUIRED_PROPERTIES"] .props')];
							for(var i in arSelects)
							{
								var $fields = arSelects[i];
								var code = arSelects[i].closest('.item').find(' > div').data('optioncode');
								var $fields2 = $fields.next();
								if($fields.length && $fields2.length)
								{
									var fields = $fields2.val();
									$fields2.find('option').remove();

									if(fields)
									{
										if(fields.indexOf('FIO') !== -1 && fields.indexOf('CONTACT_PERSON') === -1)
											fields.push('CONTACT_PERSON');
										else if(fields.indexOf('FIO') === -1 && fields.indexOf('CONTACT_PERSON') !== -1)
											fields.push('FIO');
									}

									for(var j in arOrderPropertiesByPerson[value])
									{
										var selected = '';
										if(fields)
										{
											selected = (fields.indexOf(j) !== -1 ? ' selected="selected"' : '');
										}
										$fields2.append('<option value="' + j + '"' + selected + '>' + arOrderPropertiesByPerson[value][j] + '</option>');
									}
									/*$fields.find('option').eq(0).attr('selected', 'selected');
									$fields.find('option').eq(1).attr('selected', 'selected');*/

									$fields.html('');
									for(var j in arOrderPropertiesByPerson[value])
									{
										var selected = '';
										var input_id = code+'_'+site+'_'+j;
										if(fields)
											selected = (fields.indexOf(j) !== -1 ? ' checked' : '');
										$fields.append('<div class="outer_wrapper '+selected+'">'+
											'<div class="inner_wrapper checkbox">'+
												'<div class="title_wrapper">'+
													'<div class="subtitle"><label for="'+input_id+'">'+arOrderPropertiesByPerson[value][j]+'</label></div>'+
												'</div>'+
												'<div class="value_wrapper">'+
													'<input type="checkbox" class="adm-designed-checkbox" id="'+input_id+'" name="tmp_'+code+'_'+site+'[]" value="'+j+'" '+selected+'><label for="'+input_id+'" title="" class="adm-designed-checkbox-label"></label><label for="'+input_id+'"></label>'+
												'</div>'+
											'</div>'+
										'</div>');
									}
								}
							}
						}
					}
				});

				/*$('select[name^="ONECLICKBUY_PROPERTIES"]').change(function() {
					var table = $(this).parents('table').first();
					$(this).find('option').eq(0).attr('selected', 'selected');
					$(this).find('option').eq(1).attr('selected', 'selected');
					var fiedsValue = $(this).val();
					var $requiredFields = table.find('select[name^=ONECLICKBUY_REQUIRED_PROPERTIES]');
					var requiredFieldsValue = $requiredFields.val();
					for(var i in requiredFieldsValue)
					{
						if(fiedsValue === null || fiedsValue.indexOf(requiredFieldsValue[i]) === -1)
							$requiredFields.find('option[value=' + requiredFieldsValue[i] + ']').removeAttr('selected');
					}
				});

				$('select[name^="ONECLICKBUY_REQUIRED_PROPERTIES"]').change(function() {
					var table = $(this).parents('table').first();
					$(this).find('option').eq(0).attr('selected', 'selected');
					$(this).find('option').eq(1).attr('selected', 'selected');
					var requiredFieldsValue = $(this).val();
					var $fieds = table.find('select[name^=ONECLICKBUY_PROPERTIES]');
					var fiedsValue = $fieds.val();
					var $FIO = $(this).find('option[value^=FIO]');
					var $PHONE = $(this).find('option[value^=PHONE]');
					for(var i in requiredFieldsValue)
					{
						if(fiedsValue === null || fiedsValue.indexOf(requiredFieldsValue[i]) === -1)
							$(this).find('option[value=' + requiredFieldsValue[i] + ']').removeAttr('selected');
					}
				});*/

				$(document).on('change', 'input[name^="tmp_ONECLICKBUY"]', function(){
					var parent = $(this).closest('.outer_wrapper'),
						index = parent.index();

					if($(this).is(':checked'))
					{
						$(this).closest('.outer_wrapper').addClass('checked');
						parent.closest('.inner_wrapper').find('select option:eq('+index+')').attr('selected', 'selected');
					}
					else
					{
						$(this).closest('.outer_wrapper').removeClass('checked');
						parent.closest('.inner_wrapper').find('select option:eq('+index+')').removeAttr('selected');
					}

				})

				$('input[name^="USE_GOOGLE_RECAPTCHA"]').change(function(){
					if($(this).attr('checked') != 'checked')
					{
						$(this).closest('.adm-detail-content-table').find('div[name^="GOOGLE_RECAPTCHA_NOTE"] div[data-version=3]').css('display','none');
						$(this).closest('.adm-detail-content-table').find('div[data-class^="GOOGLE_RECAPTCHA"]').each(function(){
							$(this).fadeOut();
						});
					}
					else
					{
						$(this).closest('.adm-detail-content-table').find('div[data-class^="GOOGLE_RECAPTCHA_VERSION"]').fadeIn();
						$(this).closest('.adm-detail-content-table').find('div[data-class^="GOOGLE_RECAPTCHA_PUBLIC_KEY"]').fadeIn();
						$(this).closest('.adm-detail-content-table').find('div[data-class^="GOOGLE_RECAPTCHA_PRIVATE_KEY"]').fadeIn();
						$(this).closest('.adm-detail-content-table').find('div[data-class^="GOOGLE_RECAPTCHA_MASK_PAGE"]').fadeIn();

						var ver = $(this).closest('.adm-detail-content-table').find('div[data-class^="GOOGLE_RECAPTCHA_VERSION"] select').val();
						if(ver == '3'){
							$(this).closest('.adm-detail-content-table').find('div[name^="GOOGLE_RECAPTCHA_NOTE"] div[data-version=3]').css('display','');
							$(this).closest('.adm-detail-content-table').find('div[data-class^="GOOGLE_RECAPTCHA_MIN_SCORE"]').fadeIn();
						}
						else{
							var size = $(this).closest('.adm-detail-content-table').find('div[data-class^="GOOGLE_RECAPTCHA_SIZE"] select').val();
							$(this).closest('.adm-detail-content-table').find('div[data-class^="GOOGLE_RECAPTCHA_COLOR"]').fadeIn();
							$(this).closest('.adm-detail-content-table').find('div[data-class^="GOOGLE_RECAPTCHA_SIZE"]').fadeIn();
							if(size === 'INVISIBLE'){
								$(this).closest('.adm-detail-content-table').find('div[data-class^="GOOGLE_RECAPTCHA_SHOW_LOGO"]').fadeIn();
								$(this).closest('.adm-detail-content-table').find('div[data-class^="GOOGLE_RECAPTCHA_BADGE"]').fadeIn();
							}
						}
					}
					$('select[name^="GOOGLE_RECAPTCHA_SIZE"]').change();
					$('select[name^="GOOGLE_RECAPTCHA_VERSION"]').change();
				});

				$('select[name^="GOOGLE_RECAPTCHA_SIZE"]').change(function() {
					var val = $(this).val();
					var tab = $(this).parents('.adm-detail-content-item-block');
					if(tab.find('input[name^="USE_GOOGLE_RECAPTCHA"]').attr('checked') == 'checked')
					{
						if(val != 'INVISIBLE')
						{
							tab.find('div[data-class^="GOOGLE_RECAPTCHA_SHOW_LOGO"]').fadeOut();
							tab.find('div[data-class^="GOOGLE_RECAPTCHA_BADGE"]').fadeOut();
						}
						else
						{
							tab.find('div[data-class^="GOOGLE_RECAPTCHA_SHOW_LOGO"]').fadeIn();
							tab.find('div[data-class^="GOOGLE_RECAPTCHA_BADGE"]').fadeIn();
						}
					}
					else
					{
						tab.find('div[data-class^="GOOGLE_RECAPTCHA_SHOW_LOGO"]').fadeOut();
						tab.find('div[data-class^="GOOGLE_RECAPTCHA_BADGE"]').fadeOut();
					}
				});

				$('select[name^="GOOGLE_RECAPTCHA_VERSION"]').change(function() {
					var val = $(this).val();
					var tab = $(this).parents('.adm-detail-content-item-block');
					if(tab.find('input[name^="USE_GOOGLE_RECAPTCHA"]').attr('checked') == 'checked')
					{
						if(val == '3')
						{
							tab.find('div[name^="GOOGLE_RECAPTCHA_NOTE"] div[data-version=3]').css('display','');
							tab.find('div[data-class^="GOOGLE_RECAPTCHA_COLOR"]').fadeOut();
							tab.find('div[data-class^="GOOGLE_RECAPTCHA_SIZE"]').fadeOut();
							tab.find('div[data-class^="GOOGLE_RECAPTCHA_SHOW_LOGO"]').fadeOut();
							tab.find('div[data-class^="GOOGLE_RECAPTCHA_BADGE"]').fadeOut();
							setTimeout(function(){
								tab.find('div[data-class^="GOOGLE_RECAPTCHA_MIN_SCORE"]').fadeIn();
							}, 400);
						}
						else
						{
							tab.find('div[name^="GOOGLE_RECAPTCHA_NOTE"] div[data-version=3]').css('display','none');
							tab.find('div[data-class^="GOOGLE_RECAPTCHA_MIN_SCORE"]').fadeOut();
							setTimeout(function(){
								tab.find('div[data-class^="GOOGLE_RECAPTCHA_COLOR"]').fadeIn();
								tab.find('div[data-class^="GOOGLE_RECAPTCHA_SIZE"]').fadeIn();
								tab.find('div[data-class^="GOOGLE_RECAPTCHA_SIZE"] select').trigger('change');
							}, 400);
						}
					}
				});

				$('input[name^="USE_PHONE_AUTH"]').change(function(){
					if($(this).prop('disabled')){
						$(this).prop('checked', false);
						$(this).closest('.block').find('label').attr('title', '<?=GetMessage('PHONE_AUTH_DISABLED_TITLE')?>');
					}

					var tab = $(this).parents('.adm-detail-content-item-block');
					var itrUPANote = tab.find('div[name^=USE_PHONE_AUTH_NOTE]');
					if(itrUPANote.length){
						var bChecked = $(this).prop('checked') && !$(this).prop('disabled');
						if(bChecked){
							itrUPANote.show();
						}
						else{
							itrUPANote.hide();
						}
					}
				});

				$('input[name^="REGIONALITY_FILTER_ITEM"]').change(function() {
					var filterOn = $(this).attr('checked') == 'checked';
					var tab = $(this).parents('.tab');
					if(filterOn)
					{
						tab.find('div[data-class^="REGIONALITY_FILTER_CATALOG"]').fadeIn();
						tab.find('div[data-option_code^="REGIONALITY_FILTER_CATALOG_NOTE"]').fadeIn();
					}
					else
					{
						tab.find('div[data-class^="REGIONALITY_FILTER_CATALOG"]').fadeOut();
						tab.find('div[data-option_code^="REGIONALITY_FILTER_CATALOG_NOTE"]').fadeOut();
					}
				});

				$('select[name^="ONECLICKBUY_PERSON_TYPE"]').change();
				$('input[name^="YA_GOALS"]').change();
				$('select[name^="USE_FORMS_GOALS"]').change();
				$('input[name^="USE_BASKET_GOALS"]').change();
				$('input[name^="USE_1CLICK_GOALS"]').change();
				$('input[name^="USE_FASTORDER_GOALS"]').change();
				$('input[name^="USE_FULLORDER_GOALS"]').change();
				$('input[name^="USE_DEBUG_GOALS"]').change();

				$('input[name^="USE_GOOGLE_RECAPTCHA"]').change();
				$('select[name^="GOOGLE_RECAPTCHA_SIZE"]').change();
				$('select[name^="GOOGLE_RECAPTCHA_VERSION"]').change();
				$('select[name^="SHOW_SECTION_DESCRIPTION"]').change();
				$('input[name^="USE_PRIORITY_SECTION_DESCRIPTION_SOURCE"]').change();
				$('input[name^="USE_PHONE_AUTH"]').change();
				$('input[name^="REGIONALITY_FILTER_ITEM"]').change();
			</script>
		</form>
		<?$tabControl->End();?>
	<?endif;?>
<?}
else
{
	echo CAdminMessage::ShowMessage(GetMessage('NO_RIGHTS_FOR_VIEWING'));
}?>
<?require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');?>