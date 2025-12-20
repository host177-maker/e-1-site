<?
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use \Pec\Delivery\Request;
use \Pec\Delivery\Tools;
use Bitrix\Sale\Location\Admin\LocationHelper as Helper;

Loc::loadMessages($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/options.php");
Loc::loadMessages(__FILE__);

$module_id = "pecom.ecomm";

Loader::includeModule($module_id);
Loader::includeModule('sale');

$RIGHT = $APPLICATION->GetGroupRight($module_id);
$RIGHT_W = ($RIGHT>="W");
$RIGHT_R = ($RIGHT>="R");

if ($RIGHT_R)
{
	if (
		$REQUEST_METHOD=="POST"
		&& $RIGHT_W
		&& check_bitrix_sessid()
	)
	{
		$_REQUEST['PEC_API_AGENT_ORDER_EXPIRED'] = (int)$_REQUEST['PEC_API_AGENT_ORDER_EXPIRED'];
		if ($_REQUEST['PEC_API_AGENT_ORDER_EXPIRED'] > 60) {
			$_REQUEST['PEC_API_AGENT_ORDER_EXPIRED'] = 60;
		}

		$arPecSettingsString = ['PEC_GET_USER_ADDRESS', 'PEC_STORE_ADDRESS', 'PEC_STORE_PZZ', 'PEC_WEIGHT', 'PEC_VOLUME',
			'PEC_MAX_SIZE', 'PEC_STORE_TITLE', 'PEC_STORE_INN', 'PEC_STORE_PERSON', 'PEC_STORE_PHONE',
			'PEC_API_TYPE_DELIVERY', 'PEC_SHOW_TYPE_WIDGET', 'PEC_API_LOGIN', 'PEC_API_KEY', 'PEC_API_AGENT_ORDER_EXPIRED',
			'PEC_INN', 'PEC_DOCUMENT_TYPE', 'PEC_DOCUMENT_SERIES', 'PEC_DOCUMENT_NUMBER', 'PEC_DOCUMENT_DATE',
			'PEC_DELIVERY_ADDRESS', 'PEC_ORDER_SEND', 'PEC_ORDER_CREATE', 'PEC_STORE_TYPE', 'PEC_PRINT_LABEL',
			'PEC_SAVE_PDF_URL', 'PEC_API_AGENT_PERIOD', 'PEC_SELF_PACK_INPUT'];


		foreach ($arPecSettingsString as $key) {
			if($_REQUEST[$key])
			{
				if ($key == 'PEC_ORDER_SEND' && $_REQUEST[$key] != 'U')
					COption::RemoveOption($module_id, "PEC_ORDER_CREATE");
				if(in_array($key, ['PEC_WEIGHT', 'PEC_VOLUME', 'PEC_MAX_SIZE'])) {
					$_REQUEST[$key] = str_replace(',', '.', $_REQUEST[$key]);
				}
				Option::set($module_id, $key, $_REQUEST[$key]);
			}
			else
			{
				$value = '';

				if ($key == 'PEC_INN')
					$value = CSaleOrderProps::GetList(array(), array("NAME" => Loc::getMessage("PEC_DELIVERY_INN")))->Fetch()['CODE'];
				if ($key == 'PEC_DOCUMENT_TYPE')
					$value = CSaleOrderProps::GetList(array(), array("CODE" => "PEC_DOCUMENT_TYPE"))->Fetch()['CODE'];
				Option::set($module_id, $key, $value);
			}
		}

		$arPecSettingArray = ['PEC_API_ALLOW_DELIVERY', 'PEC_API_SHIPPED', 'PEC_API_STATUS_TABLE', 'PEC_API_START_AGENT'];
		foreach ($arPecSettingArray as $key) {
			if($_REQUEST[$key])
			{
				Option::set($module_id, $key, serialize($_REQUEST[$key]));
			}
            elseif ($_REQUEST['PEC_API_SHIPPED']) // если передана настроечная таблица (если нет логина - таблицы нет)
			{
				Option::set($module_id, $key, '');
			}
		}
		$arPecSettingsBool = ['PEC_SAFE_PRICE', 'PEC_SELF_PACK', 'PEC_COST_OUT', 'PEC_SHOW_WIDGET', 'PEC_SAVE_PDF', 'PEC_API_AGENT_ACTIVE'];
		foreach ($arPecSettingsBool as $key) {
			if($_REQUEST[$key])
			{
				Option::set($module_id, $key, 1);
			}
			else
			{
				Option::set($module_id, $key, 0);
			}
		}
		foreach ($_REQUEST['PEC_STORE_DOP'] as $sklad) {
			if($sklad['parent_id'] && $sklad['address']) {
				$dop_sklads[] = $sklad;
			}
		}
		if (!empty($dop_sklads)) {
			Option::set($module_id, 'PEC_STORE_DOP', serialize($dop_sklads));
		} else {
			Option::set($module_id, 'PEC_STORE_DOP', '');
		}

		// agent params
		$interval = $_REQUEST['PEC_API_AGENT_PERIOD'];
		if (!$interval || (int)$interval < 5) $interval = 120;
		$arField = ['AGENT_INTERVAL' => $interval * 60];
		$arField['ACTIVE'] = Option::get($module_id, 'PEC_API_AGENT_ACTIVE', '') ? 'Y' : 'N';
		$arOldAgent = CAgent::GetList([], ["NAME" => '\Pec\Delivery\Tools::agentUpdateOrdersPecStatus();'])->Fetch();
		$objDateTime = new DateTime("+10 seconds");
		$date = $objDateTime->format("d.m.Y H:i:s");
		$arField['NEXT_EXEC'] = $date;
		if ($arOldAgent)
			CAgent::Update($arOldAgent["ID"], $arField);
		else
			Tools::RegisterAgent($arField['AGENT_INTERVAL']);
	}

	$optionPecApiAllowDelivery = array_filter(unserialize(Option::get($module_id, 'PEC_API_ALLOW_DELIVERY', ''), ['allowed_classes' => false]));
	$optionPecApiShipped = array_filter(unserialize(Option::get($module_id, 'PEC_API_SHIPPED', ''), ['allowed_classes' => false]));
	$optionPecApiStatusTable = array_filter(unserialize(Option::get($module_id, 'PEC_API_STATUS_TABLE', ''), ['allowed_classes' => false]));
	$optionPecApiStartAgent = array_filter(unserialize(Option::get($module_id, 'PEC_API_START_AGENT', ''), ['allowed_classes' => false]));

	$arAgent = CAgent::GetList([], ["NAME" => '\Pec\Delivery\Tools::agentUpdateOrdersPecStatus();'])->Fetch();
	if ($arAgent) {
		$optionAgentActive = $arAgent['ACTIVE'] == 'Y' ? 1 : 0;
	} else {
		Tools::RegisterAgent();
		$optionAgentActive = 0;
	}


	$request = new Request;
	$pecStatus = $request->getAllStatus();

	$aTabs = array(
		array("DIV" => "edit1", "TAB" => Loc::getMessage("PEC_DELIVERY_SETTING"), "ICON" => "", "TITLE" => Loc::getMessage("PEC_DELIVERY_SETTING_TITLE")),
		array("DIV" => "edit2", "TAB" => Loc::getMessage("PEC_DELIVERY_SETTING_STORE"), "ICON" => "", "TITLE" => Loc::getMessage("PEC_DELIVERY_SETTING_STORE")),
		array("DIV" => "edit3", "TAB" => Loc::getMessage("PEC_DELIVERY_API"), "ICON" => "", "TITLE" => Loc::getMessage("PEC_DELIVERY_API_TITLE")),
		array("DIV" => "edit4", "TAB" => Loc::getMessage("PEC_DELIVERY_FAQ"), "ICON" => "", "TITLE" => Loc::getMessage("PEC_DELIVERY_FAQ")),
	);
	$tabControl = new CAdminTabControl("tabControl", $aTabs);
	$tabControl->Begin();

	$storeTypes = array(
		["id" => 0, "name" => Loc::getMessage("PEC_SETUP_OWNER")],
		["id" => 1, "name" => Loc::getMessage("PEC_SETUP_OWNER_ANO")],
		["id" => 2, "name" => Loc::getMessage("PEC_SETUP_OWNER_AO")],
		["id" => 3, "name" => Loc::getMessage("PEC_SETUP_OWNER_GAUK")],
		["id" => 4, "name" => Loc::getMessage("PEC_SETUP_OWNER_GAUK")],
		["id" => 5, "name" => Loc::getMessage("PEC_SETUP_OWNER_GAUSO")],
		["id" => 6, "name" => Loc::getMessage("PEC_SETUP_OWNER_GBUZ")],
		["id" => 7, "name" => Loc::getMessage("PEC_SETUP_OWNER_GOBU")],
		["id" => 8, "name" => Loc::getMessage("PEC_SETUP_OWNER_GOUVPO")],
		["id" => 9, "name" => Loc::getMessage("PEC_SETUP_OWNER_GP")],
		["id" => 10, "name" => Loc::getMessage("PEC_SETUP_OWNER_GUZ")],
		["id" => 11, "name" => Loc::getMessage("PEC_SETUP_OWNER_GUP")],
		["id" => 13, "name" => Loc::getMessage("PEC_SETUP_OWNER_DOAO")],
		["id" => 14, "name" => Loc::getMessage("PEC_SETUP_OWNER_ZAO")],
		["id" => 15, "name" => Loc::getMessage("PEC_SETUP_OWNER_IP")],
		["id" => 16, "name" => Loc::getMessage("PEC_SETUP_OWNER_KGBUZ")],
		["id" => 17, "name" => Loc::getMessage("PEC_SETUP_OWNER_KGOY")],
		["id" => 18, "name" => Loc::getMessage("PEC_SETUP_OWNER_KT")],
		["id" => 19, "name" => Loc::getMessage("PEC_SETUP_OWNER_KFX")],
		["id" => 20, "name" => Loc::getMessage("PEC_SETUP_OWNER_MAOY")],
		["id" => 21, "name" => Loc::getMessage("PEC_SETUP_OWNER_MAY")],
		["id" => 22, "name" => Loc::getMessage("PEC_SETUP_OWNER_MKP")],
		["id" => 23, "name" => Loc::getMessage("PEC_SETUP_OWNER_MC")],
		["id" => 24, "name" => Loc::getMessage("PEC_SETUP_OWNER_MUP")],
		["id" => 25, "name" => Loc::getMessage("PEC_SETUP_OWNER_NAO")],
		["id" => 26, "name" => Loc::getMessage("PEC_SETUP_OWNER_NOYVPO")],
		["id" => 27, "name" => Loc::getMessage("PEC_SETUP_OWNER_NP")],
		["id" => 28, "name" => Loc::getMessage("PEC_SETUP_OWNER_OAO")],
		["id" => 29, "name" => Loc::getMessage("PEC_SETUP_OWNER_ODO")],
		["id" => 30, "name" => Loc::getMessage("PEC_SETUP_OWNER_OOO")],
		["id" => 31, "name" => Loc::getMessage("PEC_SETUP_OWNER_OC")],
		["id" => 32, "name" => Loc::getMessage("PEC_SETUP_OWNER_PAO")],
		["id" => 33, "name" => Loc::getMessage("PEC_SETUP_OWNER_PK")],
		["id" => 34, "name" => Loc::getMessage("PEC_SETUP_OWNER_PT")],
		["id" => 35, "name" => Loc::getMessage("PEC_SETUP_OWNER_PO")],
		["id" => 36, "name" => Loc::getMessage("PEC_SETUP_OWNER_ROPP")],
		["id" => 37, "name" => Loc::getMessage("PEC_SETUP_OWNER_CNGOC")],
		["id" => 38, "name" => Loc::getMessage("PEC_SETUP_OWNER_COOO")],
		["id" => 39, "name" => Loc::getMessage("PEC_SETUP_OWNER_CPK")],
		["id" => 40, "name" => Loc::getMessage("PEC_SETUP_OWNER_TDO")],
		["id" => 41, "name" => Loc::getMessage("PEC_SETUP_OWNER_TOO")],
		["id" => 42, "name" => Loc::getMessage("PEC_SETUP_OWNER_UP")],
		["id" => 43, "name" => Loc::getMessage("PEC_SETUP_OWNER_FBU")],
		["id" => 44, "name" => Loc::getMessage("PEC_SETUP_OWNER_FBUZ")],
		["id" => 45, "name" => Loc::getMessage("PEC_SETUP_OWNER_FGAOYVPO")],
		["id" => 46, "name" => Loc::getMessage("PEC_SETUP_OWNER_FGAY")],
		["id" => 47, "name" => Loc::getMessage("PEC_SETUP_OWNER_FGBOYVPO")],
		["id" => 48, "name" => Loc::getMessage("PEC_SETUP_OWNER_FGBU")],
		["id" => 49, "name" => Loc::getMessage("PEC_SETUP_OWNER_FGU")],
		["id" => 50, "name" => Loc::getMessage("PEC_SETUP_OWNER_FGUP")],
		["id" => 51, "name" => Loc::getMessage("PEC_SETUP_OWNER_FKU")],
		["id" => 52, "name" => Loc::getMessage("PEC_SETUP_OWNER_FOND")],
		["id" => 53, "name" => Loc::getMessage("PEC_SETUP_OWNER_FCGAOY")],
		["id" => 54, "name" => Loc::getMessage("PEC_SETUP_OWNER_CHNOYVPO")],
		["id" => 55, "name" => Loc::getMessage("PEC_SETUP_OWNER_CHP")]
	);
	?>
    <form method="post" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=htmlspecialchars($mid)?>&lang=<?=LANGUAGE_ID?>">
		<?=bitrix_sessid_post()?>
		<?$tabControl->BeginNextTab();?>
        <div class="adm-detail-content-item-block">
            <div style="display: flex;">
                <div style="width: 45%;">
					<?=Loc::getMessage("PEC_DELIVERY_ADDRESS_RECEPTION")?>
                </div>
                <div>
					<?
					$check_personal = (Option::get($module_id, "PEC_GET_USER_ADDRESS") == 'personal') ? 'checked' : '';
					$check_work = (Option::get($module_id, "PEC_GET_USER_ADDRESS") == 'work') ? 'checked' : '';
					$check_no = (Option::get($module_id, "PEC_GET_USER_ADDRESS") == 'no') ? 'checked' : '';
					if ($check_personal == '' && $check_work == '') {
						$check_no = 'checked';
					}
					?>
                    <input type="radio" value="personal" name="PEC_GET_USER_ADDRESS" <?=$check_personal?>><?=Loc::getMessage("PEC_DELIVERY_USER_ADDRESS1")?>
                    <input type="radio" value="work" name="PEC_GET_USER_ADDRESS" <?=$check_work?>><?=Loc::getMessage("PEC_DELIVERY_USER_ADDRESS2")?>
                    <input type="radio" value="no" name="PEC_GET_USER_ADDRESS" <?=$check_no?>><?=Loc::getMessage("PEC_DELIVERY_USER_ADDRESS3")?>
                </div>
            </div>
            <br>
            <div style="display: flex;">
                <div style="width: 45%;">
					<?=Loc::getMessage("PEC_DELIVERY_ADDRESS")?>
                </div>
                <div>
					<?
					$address = Option::get($module_id, "PEC_STORE_ADDRESS", '');
					if (!$address) {
						$address = Tools::getStoreAddress();
					}
					if (!$address) {
						$address = Loc::getMessage("PEC_SETUP_STORE_CITY_DEFAULT");
					}
					?>
                    <input type="radio" value="store" name="PEC_STORE_PZZ" <?if(Option::get($module_id, "PEC_STORE_PZZ", 'pzz') != 'pzz') echo 'checked'?>><?=Loc::getMessage("PEC_DELIVERY_BY_ADDRESS")?>
                    <input type="radio" value="pzz" name="PEC_STORE_PZZ" <?if(Option::get($module_id, "PEC_STORE_PZZ", 'pzz') == 'pzz') echo 'checked'?>><?=Loc::getMessage("PEC_DELIVERY_IN_DEPARTMENT")?>
                    <br>
                    <br>
                    <textarea  rows="2" cols="45" name="PEC_STORE_ADDRESS"><?=$address;?></textarea>
                </div>
            </div>
			<?
			$sklads = is_array(unserialize(Option::get($module_id, "PEC_STORE_DOP"), ['allowed_classes' => false])) ?
				unserialize(Option::get($module_id, "PEC_STORE_DOP"), ['allowed_classes' => false]) : [];
			if(empty($sklads)) {
				?><div style="display: none"><?
				$APPLICATION->IncludeComponent("bitrix:sale.location.selector.".Helper::getWidgetAppearance(), "", array(
					"ID" => "",
					"CODE" => "",
					"INPUT_NAME" => "",
					"PROVIDE_LINK_BY" => "id",
					"SHOW_ADMIN_CONTROLS" => 'Y',
					"SELECT_WHEN_SINGLE" => 'N',
					"FILTER_BY_SITE" => 'N',
					"SHOW_DEFAULT_LOCATIONS" => 'N',
					"SEARCH_BY_PRIMARY" => 'Y',
				),
					false
				);?></div><?
			}
			foreach ($sklads as $key => $sklad) {
				?>
                <div style="display: flex; border-top: 1px solid grey; margin-top: 15px; padding-top: 15px" class="dop_sklad"
                     id="div-dop-sklad-<?=$key?>">
                    <div style="width: 45%;"><?=Loc::getMessage("PEC_SETUP_OTHER_ADDRESS_LABEL")?><br><br>
                        <div style="padding: 0 20px 20px 0">
							<?$APPLICATION->IncludeComponent("bitrix:sale.location.selector.".Helper::getWidgetAppearance(), "", array(
								"ID" => $sklad['parent_id'],
								"CODE" => "",
								"INPUT_NAME" => "PEC_STORE_DOP[$key][parent_id]", //element[PARENT_ID]
								"PROVIDE_LINK_BY" => "id",
								"SHOW_ADMIN_CONTROLS" => 'Y',
								"SELECT_WHEN_SINGLE" => 'N',
								"FILTER_BY_SITE" => 'N',
								"SHOW_DEFAULT_LOCATIONS" => 'N',
								"SEARCH_BY_PRIMARY" => 'Y',
								//"EXCLUDE_SUBTREE" => $nodeId,
							),
								false
							);?>
                        </div>
                    </div>
                    <div>
                        <br><br>
                        <textarea  rows="2" cols="45" name="PEC_STORE_DOP[<?=$key?>][address]"><?=$sklad['address'];?></textarea>
                        <div><?=Loc::getMessage("PEC_SETUP_OTHER_ADDRESS_LABEL2")?></div>
                        <br>
                        <input type="radio" value="1" name="PEC_STORE_DOP[<?=$key?>][intake]" <?if($sklad['intake'] == 1) echo 'checked'?>><?=Loc::getMessage("PEC_DELIVERY_BY_ADDRESS")?>
                        <input type="radio" value="0" name="PEC_STORE_DOP[<?=$key?>][intake]" <?if($sklad['intake'] == 0) echo 'checked'?>><?=Loc::getMessage("PEC_DELIVERY_IN_DEPARTMENT")?>
                    </div>
                    <div style="padding: 30px">
                        <button type="button" class="adm-btn" id="but-dop-sklad-<?=$key?>"><?=Loc::getMessage("PEC_SETUP_OTHER_ADDRESS_LABEL3")?></button>
                    </div>
                    <script>
                        BX.ready(function (){
                            BX('but-dop-sklad-<?=$key?>').addEventListener('click',function (){
                                BX.remove(BX('div-dop-sklad-<?=$key?>'));
                            });
                        });
                    </script>
                </div>
				<?
			}
			?>
            <div id="but-dop-sklad-add" style="display: flex;"><button type="button" class="adm-btn"><?=Loc::getMessage("PEC_SETUP_OTHER_ADDRESS_LABEL4")?></button></div>
            <script>
                BX.ready(function (){
                    var but=BX('but-dop-sklad-add');
                    but.addEventListener('click',function (){
                        BX.ajax({
                            url: '/bitrix/js/pecom.ecomm/ajax.php',
                            data: {
                                method:'dopSklad',
                            },
                            method: 'POST',
                            timeout: 30,
                            async: false,
                            onsuccess: function(data){
                                var div=document.createElement("div");
                                div.innerHTML=data;
                                BX.insertBefore(div,but);
                            }
                        });
                    });
                });
            </script>
        </div>
        <div class="adm-detail-content-item-block">
            <div style="display: flex;">
                <div style="width: 45%;">
					<?=Loc::getMessage("PEC_DELIVERY_WEIGHT")?>
                </div>
                <div>
                    <input type="text" value="<?=Option::get($module_id, "PEC_WEIGHT", '0.05');?>" name="PEC_WEIGHT">
                </div>
            </div>
            <br>
            <div style="display: flex;">
                <div style="width: 45%;">
					<?=Loc::getMessage("PEC_DELIVERY_VOLUME")?>
                </div>
                <div>
                    <input type="text" value="<?=Option::get($module_id, "PEC_VOLUME", '0.001');?>" name="PEC_VOLUME">
                </div>
            </div>
            <br>
            <div style="display: flex;">
                <div style="width: 45%;">
					<?=Loc::getMessage("PEC_DELIVERY_MAX_SIZE")?>
                </div>
                <div>
                    <input type="text" value="<?=Option::get($module_id, "PEC_MAX_SIZE", '0.2');?>" name="PEC_MAX_SIZE">
                </div>
            </div>
            <br>
            <div style="display: flex;">
                <div style="width: 45%;">
					<?=Loc::getMessage("PEC_DELIVERY_PRICE")?>
                </div>
                <div>
                    <input type="checkbox" value="Y" name="PEC_SAFE_PRICE" <?if(Option::get($module_id, "PEC_SAFE_PRICE", '0')) echo 'checked';?>>
                </div>
            </div>
            <br>
            <div style="display: flex;">
                <div style="width: 45%;">
					<?=Loc::getMessage("PEC_DELIVERY_ZTU")?>
                </div>
                <div>
                    <input type="checkbox" value="Y" id="PEC_SELF_PACK" name="PEC_SELF_PACK" <?if(Option::get($module_id, "PEC_SELF_PACK", '0')) echo 'checked';?>>
                </div>
            </div>
            <br>
            <div style="display: flex;">
                <div style="width: 45%;">
					<?=Loc::getMessage("PEC_DELIVERY_ZTU_INPUT")?>
                </div>
                <div>
                    <input type="text" id="PEC_SELF_PACK_INPUT" value="<?=Option::get($module_id, "PEC_SELF_PACK_INPUT");?>" name="PEC_SELF_PACK_INPUT">
                </div>
            </div>
            <br>
            <div style="display: flex;">
                <div style="width: 45%;">
					<?=Loc::getMessage("PEC_DELIVERY_PAYMENT_DELIVERY")?>
                </div>
                <div>
                    <input type="checkbox" name="PEC_COST_OUT" <?if(Option::get($module_id, "PEC_COST_OUT", '1')) echo 'checked';?>>
                </div>
            </div>
            <br>
            <div style="display: flex;">
                <div style="width: 45%;">
					<?=Loc::getMessage("PEC_DELIVERY_SHOW_TYPE_WIDGET")?>
                </div>
                <div>
                    <select name="PEC_SHOW_TYPE_WIDGET">
                        <option value="show" <?if(Option::get($module_id, "PEC_SHOW_TYPE_WIDGET", '0') == 'show') echo 'selected';?>><?=Loc::getMessage("PEC_DELIVERY_SHOW_TYPE_VERSION1")?></option>
                        <option value="hide" <?if(Option::get($module_id, "PEC_SHOW_TYPE_WIDGET", '0') == 'hide') echo 'selected';?>><?=Loc::getMessage("PEC_DELIVERY_SHOW_TYPE_VERSION2")?></option>
                        <option value="modal" <?if(Option::get($module_id, "PEC_SHOW_TYPE_WIDGET", '0') == 'modal') echo 'selected';?>><?=Loc::getMessage("PEC_DELIVERY_SHOW_TYPE_VERSION3")?></option>
                    </select>
                </div>
            </div>
            <br>
            <div style="display: flex;">
                <div style="width: 45%;">
					<?=Loc::getMessage("PEC_DELIVERY_TYPE")?>
                </div>
                <div>
                    <select name="PEC_API_TYPE_DELIVERY">
                        <option value="auto" <?if(Option::get($module_id, "PEC_API_TYPE_DELIVERY", '0') == 'auto') echo 'selected';?>><?=Loc::getMessage("PEC_DELIVERY_TYPE_AVTO")?></option>
                        <option value="avia" <?if(Option::get($module_id, "PEC_API_TYPE_DELIVERY", '0') == 'avia') echo 'selected';?>><?=Loc::getMessage("PEC_DELIVERY_TYPE_AVIA")?></option>
                        <option value="easyway" <?if(Option::get($module_id, "PEC_API_TYPE_DELIVERY", '0') == 'easyway') echo 'selected';?>><?=Loc::getMessage("PEC_DELIVERY_TYPE_EASY_WAY")?></option>
                    </select>
                </div>
            </div>
        </div>

		<?$tabControl->BeginNextTab();?>
        <div class="adm-detail-content-item-block">

            <div style="display: flex;">
                <div style="width: 45%;">
					<?=Loc::getMessage("PEC_INN_TEXT")?>
                </div>
                <div>
					<? $inn = CSaleOrderProps::GetList(array(), array("NAME" => Loc::getMessage("PEC_DELIVERY_INN")))->Fetch()['CODE']; ?>
                    <input type="text" value="<?=Option::get($module_id, "PEC_INN", $inn, 's1');?>" name="PEC_INN">
                </div>
            </div>
            <br>
            <div style="display: flex;">
                <div style="width: 45%;">
					<?=Loc::getMessage("PEC_DOCUMENT_TYPE_TEXT")?>
                </div>
                <div><?
					$type = CSaleOrderProps::GetList(array(), array("CODE" => "PEC_DOCUMENT_TYPE"))->Fetch()['CODE'];
					?>
                    <div>
                        <input type="text" value="<?=Option::get($module_id, "PEC_DOCUMENT_TYPE", $type);?>" name="PEC_DOCUMENT_TYPE">
                    </div>
                </div>
            </div>
            <br>
            <div style="display: flex;">
                <div style="width: 45%;">
					<?=Loc::getMessage("PEC_DOCUMENT_SERIES_TEXT")?>
                </div>
                <div>
                    <input type="text" value="<?=Option::get($module_id, "PEC_DOCUMENT_SERIES", '');?>" name="PEC_DOCUMENT_SERIES">
                </div>
            </div>
            <br>
            <div style="display: flex;">
                <div style="width: 45%;">
					<?=Loc::getMessage("PEC_DOCUMENT_NUMBER_TEXT")?>
                </div>
                <div>
                    <input type="text" value="<?=Option::get($module_id, "PEC_DOCUMENT_NUMBER", '');?>" name="PEC_DOCUMENT_NUMBER">
                </div>
            </div>
            <br>
            <div style="display: flex;">
                <div style="width: 45%;">
					<?=Loc::getMessage("PEC_DOCUMENT_DATE_TEXT")?>
                </div>
                <div>
                    <input type="text" value="<?=Option::get($module_id, "PEC_DOCUMENT_DATE", '');?>" name="PEC_DOCUMENT_DATE">
                </div>
            </div>
            <br>
            <div style="display: flex; ">
                <div style="width: 45%;">
					<?=Loc::getMessage("PEC_DELIVERY_ADDRESS_TEXT")?>
                </div>
                <div>
                    <input type="text" value="<?=Option::get($module_id, "PEC_DELIVERY_ADDRESS", '');?>" name="PEC_DELIVERY_ADDRESS">
                </div>
            </div>
            <br>
        </div>
        <div class="adm-detail-content-item-block">
            <div style="display: flex;">
                <div style="width: 45%;">
					<?=Loc::getMessage("PEC_DELIVERY_ORDER_SEND")?>
                </div>
                <div>
                    <select name="PEC_ORDER_SEND" id="PEC_ORDER_SEND" onchange="pecomOptions.getStatus(this);">
                        <option value="M" <?if(Option::get($module_id, "PEC_ORDER_SEND", '0') == 'M') echo 'selected';?>><?=Loc::getMessage("PEC_DELIVERY_ORDER_SEND_TEXT1")?></option>
                        <option value="C" <?if(Option::get($module_id, "PEC_ORDER_SEND", '0') == 'C') echo 'selected';?>><?=Loc::getMessage("PEC_DELIVERY_ORDER_SEND_TEXT2")?></option>
                        <option value="U" <?if(Option::get($module_id, "PEC_ORDER_SEND", '0') == 'U') echo 'selected';?>><?=Loc::getMessage("PEC_DELIVERY_ORDER_SEND_TEXT3")?></option>
                    </select>
                </div>
            </div>
            <br>
            <div style="display: <?=Option::get($module_id, "PEC_ORDER_SEND", '0') == 'status_update' ? 'flex' : 'none';?>" id="PEC_ORDER_CREATE_DIV">
                <div style="width: 45%;">
					<?=Loc::getMessage("PEC_DELIVERY_STATUS_ORDER_SEND")?>
                </div>
                <div>
					<? $orderStatus = [];
					$db_lang = \CSaleStatus::GetList(array('SORT'=>'ASC'), array("LID" => LANGUAGE_ID));
					while ($arLang = $db_lang->Fetch()) {
						$orderStatus[] = ['ID' => $arLang['ID'], 'VALUE' => $arLang['NAME'] . ' [' . $arLang['ID'].']'];
					}?>
                    <select name="PEC_ORDER_CREATE" id="PEC_ORDER_CREATE">
                        <option value=""></option>
						<? foreach ($orderStatus as $item) {?>
                            <option value="<?=$item['ID']?>" <?if(Option::get($module_id, "PEC_ORDER_CREATE") == $item['ID']) echo 'selected';?>><?=$item['VALUE']?></option><?
						}?>
                    </select>
                </div>
            </div>
            <div class="adm-info-message-wrap" id="infoBlock">
                <div class="adm-info-message">
					<?=Loc::getMessage("PEC_DELIVERY_STATUS_WARNING_TEXT")?>
                </div>
            </div>
            <br>
            <hr>
            <br>
            <div style="display: flex;">
                <div style="width: 45%;">
					<?=Loc::getMessage("PEC_DELIVERY_PRINT_LABEL")?>
                </div>
                <div>
                    <select name="PEC_PRINT_LABEL">
                        <option value="1" <?if(Option::get($module_id, "PEC_PRINT_LABEL", '0') == '1') echo 'selected';?>><?=Loc::getMessage("PEC_DELIVERY_PRINT_LABEL_OPTIONS1")?></option>
                        <option value="2" <?if(Option::get($module_id, "PEC_PRINT_LABEL", '0') == '2') echo 'selected';?>><?=Loc::getMessage("PEC_DELIVERY_PRINT_LABEL_OPTIONS2")?></option>
                        <option value="3" <?if(Option::get($module_id, "PEC_PRINT_LABEL", '0') == '3') echo 'selected';?>><?=Loc::getMessage("PEC_DELIVERY_PRINT_LABEL_OPTIONS3")?></option>
                    </select>
                </div>
            </div>
            <br>
            <div style="display: flex;">
                <div style="width: 45%;">
					<?=Loc::getMessage("PEC_DELIVERY_SAVE_PDF")?>
                </div>
                <div>
                    <input type="checkbox" value="Y" onclick="pecomOptions.getUrl();" id="PEC_SAVE_PDF" name="PEC_SAVE_PDF"
						<?if(Option::get($module_id, "PEC_SAVE_PDF", 0)) echo 'checked';?>>
                </div>
            </div>
            <br>
            <div style="display: none;" id="PEC_SAVE_PDF_URL_DIV">
                <div style="width: 45%;">
					<?=Loc::getMessage("PEC_DELIVERY_SAVE_PDF_URL")?>
                </div>
                <div>
                    <div class="adm-workarea" style="padding: 0">
                        <input type="text" value="<?=Option::get($module_id, "PEC_SAVE_PDF_URL");?>"
                               name="PEC_SAVE_PDF_URL" id="PEC_SAVE_PDF_URL">
                        <input type="button" onclick="OpenImage();" value="...">
                    </div>
                </div>
            </div>
        </div>

		<?$tabControl->BeginNextTab();?>
        <div class="adm-detail-content-item-block">
            <div style="display: flex;">
                <div style="width: 38%;">
					<?=Loc::getMessage("PEC_DELIVERY_LOGIN")?>
                </div>
                <div style="width: 60%;">
                    <input type="text" name="PEC_API_LOGIN" value="<?=Option::get($module_id, "PEC_API_LOGIN", '')?>"  style="width: 100%;">
                </div>
            </div>
            <br>
            <div style="display: flex;">
                <div style="width: 38%;">
					<?=Loc::getMessage("PEC_DELIVERY_API_KEY")?>
                </div>
                <div  style="width: 60%;">
                    <input type="text" name="PEC_API_KEY" value="<?=Option::get($module_id, "PEC_API_KEY", '')?>"  style="width: 100%;">
                </div>
            </div>
            <br><br>
            <div style="display: flex;">
                <div style="width: 38%;">
					<?=Loc::getMessage("PEC_DELIVERY_STORE_TYPE")?>
                </div>
                <div>
                    <select name="PEC_STORE_TYPE">

                        <option value=""></option>
						<? foreach ($storeTypes as $item) {?>
                        <option value="<?=$item['name']?>" <?if(Option::get($module_id, "PEC_STORE_TYPE") === $item['name']) echo 'selected';?>>
							<?=$item['name']?>
                            </option><?
						}?>
                    </select>
                </div>
            </div>
            <br>
            <div style="display: flex;">
                <div style="width: 38%;">
					<?=Loc::getMessage("PEC_DELIVERY_STORE_NAME")?>
                </div>
                <div>
                    <input type="text" value="<?=Option::get($module_id, "PEC_STORE_TITLE", COption::GetOptionString("eshop", "shopOfName", Loc::getMessage("PEC_SETUP_STORE_TYPE"), 's1'));?>" name="PEC_STORE_TITLE">
                </div>
            </div>
            <br>
            <div style="display: flex;">
                <div style="width: 38%;">
					<?=Loc::getMessage("PEC_DELIVERY_INN")?>
                </div>
                <div>
                    <input type="text" value="<?=Option::get($module_id, "PEC_STORE_INN", COption::GetOptionString("eshop", "shopINN", "", 's1'));?>" name="PEC_STORE_INN">
                </div>
            </div>
            <br>
            <div style="display: flex;">
                <div style="width: 38%;">
					<?=Loc::getMessage("PEC_DELIVERY_CONTACTS")?>
                </div>
                <div>
                    <input type="text" value="<?=Option::get($module_id, "PEC_STORE_PERSON", Loc::getMessage("PEC_DELIVERY_PEC_STORE_PERSON"));?>" name="PEC_STORE_PERSON">
                </div>
            </div>
            <br>
            <div style="display: flex;">
                <div style="width: 38%;">
					<?=Loc::getMessage("PEC_DELIVERY_PHONE")?>
                </div>
                <div>
                    <input type="text" value="<?=Option::get($module_id, "PEC_STORE_PHONE", COption::GetOptionString("eshop", "siteTelephone", "", 's1'));?>" name="PEC_STORE_PHONE">
                </div>
            </div>
        </div>
		<? if ($pecStatus && !$pecStatus['error']): ?>
            <div class="adm-detail-content-item-block----">
				<?
				$selectAllowDelivery = function ($pecStatus) use ($optionPecApiAllowDelivery) {
					return '
                    <select name="PEC_API_ALLOW_DELIVERY[' . $pecStatus . ']">
                        <option value="">---</option>
                        <option value="1" ' . ($optionPecApiAllowDelivery[$pecStatus] == 1 ? 'selected' : ' ') . '>'.Loc::getMessage("PEC_DELIVERY_TRUE").'</option>
                        <option value="2" ' . ($optionPecApiAllowDelivery[$pecStatus] == 2 ? 'selected' : ' ') . '>'.Loc::getMessage("PEC_DELIVERY_FALSE").'</option>
                    </select>
                ';
				};
				$selectIsShipped = function ($pecStatus) use ($optionPecApiShipped) {
					return '
                    <select name="PEC_API_SHIPPED[' . $pecStatus . ']">
                        <option value="">---</option>
                        <option value="1" ' . ($optionPecApiShipped[$pecStatus] == 1 ? 'selected' : ' ') . '>'.Loc::getMessage("PEC_DELIVERY_SHIPPED").'</option>
                        <option value="2" ' . ($optionPecApiShipped[$pecStatus] == 2 ? 'selected' : ' ') . '>'.Loc::getMessage("PEC_DELIVERY_NO_SHIPPED").'</option>
                    </select>
                ';
				};

				\Bitrix\Main\Loader::IncludeModule("sale");
				$statusResult = \Bitrix\Sale\Internals\StatusLangTable::getList(array(
					'order' => array('STATUS.SORT'=>'ASC'),
					'filter' => array('STATUS.TYPE'=>'D','LID'=>LANGUAGE_ID),
					'select' => array('STATUS_ID','NAME','DESCRIPTION'),
				));
				$bxShipStatus = [];
				while($status = $statusResult->fetch()) {
					$bxShipStatus[] = $status;
				}
				$selectShipStatuses = function ($pecStatus) use ($bxShipStatus, $optionPecApiStatusTable) {
					$result = '
                    <select name="PEC_API_STATUS_TABLE[' . $pecStatus . ']">
                        <option value="">---</option>';
					foreach ($bxShipStatus as $status) {
						$selected = '';
						if ($optionPecApiStatusTable[$pecStatus] == $status['STATUS_ID']) {
							$selected = 'selected';
						}
						$result .= '<option value="' . $status['STATUS_ID'] . '"' . $selected . '>' . $status['NAME'] . '</option>';
					}
					$result .= '</select>';

					return $result;
				}
				?>
                <h3 style="text-align: center;"><?=Loc::getMessage("PEC_DELIVERY_STATUS_UPDATE")?></h3>
                <td>
                    <tr>
                        <th><?=Loc::getMessage("PEC_DELIVERY_STATUS_ORDER")?></th>
                        <th><?=Loc::getMessage("PEC_DELIVERY_ALLOWED")?></th>
                        <th><?=Loc::getMessage("PEC_DELIVERY_SHIPMENT_STATUS")?></th>
                        <th><?=Loc::getMessage("PEC_DELIVERY_STATUS")?></th>
                        <th><?=Loc::getMessage("PEC_DELIVERY_STATUS_AGENT_UPDATE")?></th>
                    </tr>
					<? foreach ($pecStatus as $status): ?>
                        <tr>
                            <td><?=mb_convert_encoding($status->name, ini_get('default_charset') == 'cp1251' ? 'windows-1251' : ini_get('default_charset'), 'utf-8');?></td>
                            <td><?=$selectAllowDelivery($status->id)?></td>
                            <td><?=$selectIsShipped($status->id)?></td>
                            <td><?=$selectShipStatuses($status->id)?></td>
                            <td style="text-align: center;">
                                <input type="checkbox" name="PEC_API_START_AGENT[<?=$status->id?>]" <?if ($optionPecApiStartAgent[$status->id]) echo 'checked'?>>
                            </td>
                        </tr>
					<? endforeach; ?>
                    <tr><td style="padding-top: 20px;" </tr>
                    <tr>
                        <td>
							<?=Loc::getMessage("PEC_DELIVERY_STATUS_AGENT_RUN")?>
                        </td>
                        <td>
                            <input type="checkbox" name="PEC_API_AGENT_ACTIVE" <?if (Option::get($module_id, "PEC_API_AGENT_ACTIVE", 0)) echo 'checked'?>>
                        </td>
                    </tr>
                    <tr>
                        <td>
							<?=Loc::getMessage("PEC_DELIVERY_AGENT_PRESCRIPTION")?>
                        </td>
                        <td>
                            <input type="text" name="PEC_API_AGENT_ORDER_EXPIRED" value="<?=Option::get($module_id, "PEC_API_AGENT_ORDER_EXPIRED", '30');?>">
                        </td>
                    </tr>
                    <tr>
                        <td>
							<?=Loc::getMessage("PEC_DELIVERY_AGENT_RUN_PERIOD")?>
                        </td>
                        <td>
                            <input type="text" name="PEC_API_AGENT_PERIOD"  value="<?=Option::get($module_id, "PEC_API_AGENT_PERIOD", '120');?>">
                        </td>
                    </tr>
                </td>
            </div>
		<? else: ?>
            <div>
                <p style="color: red"><?
					if (strpos($pecStatus['error']['message'], 'Connection timed out') === 0) {?>
						<?=Loc::getMessage("PEC_DELIVERY_API_TIMEOUT")?>
					<? } else {?>
						<?=Loc::getMessage("PEC_DELIVERY_CHECK_LOGIN")?>
					<?}?>
                </p>
            </div>
		<? endif; ?>
		<?$tabControl->BeginNextTab();?>
        <div>
            <div style="color: tomato; font-size: 16px;"><?=Loc::getMessage("PEC_DELIVERY_ALARM")?></div>
            <br>
            <h2><?=Loc::getMessage("PEC_DELIVERY_CALC_SETUP")?></h2>
			<?=Loc::getMessage("PEC_DELIVERY_CALC_SETUP_TEXT")?>
            <br>
            <img src="/bitrix/js/<?=$module_id?>/faq1.png" title="faq" style="max-width: 100%;">
			<?=Loc::getMessage("PEC_DELIVERY_CALC_SETUP_TEXT2")?>
            <br>
            <h2><?=Loc::getMessage("PEC_DELIVERY_STORE_SETUP");?></h2>
			<?=Loc::getMessage("PEC_DELIVERY_STORE_SETUP_TEXT");?>
            <br>
            <img src="/bitrix/js/<?=$module_id?>/faq3.png" title="faq" style="max-width: 80%;">
            <br>
            <h2><?=Loc::getMessage("PEC_DELIVERY_API_SETUP");?></h2>
			<?=Loc::getMessage("PEC_DELIVERY_API_SETUP_TEXT");?>
            <br>
            <img src="/bitrix/js/<?=$module_id?>/fag1.jpg" title="faq" style="max-width: 100%;">
            <br>
			<?=Loc::getMessage("PEC_DELIVERY_API_SETUP_TEXT2");?>
            <br>
            <img src="/bitrix/js/<?=$module_id?>/fag2.jpg" title="faq" style="max-width: 80%;">
            </p>
			<?=Loc::getMessage("PEC_DELIVERY_API_SETUP_TEXT3");?>
        </div>
		<?//require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");?>
		<?$tabControl->Buttons();?>
        <input <?if(!$RIGHT_W) echo "disabled" ?> type="submit" name="Update" value="<?=GetMessage("MAIN_SAVE")?>" title="<?=GetMessage("MAIN_OPT_SAVE_TITLE")?>">
        <input <?if(!$RIGHT_W) echo "disabled" ?> type="submit" name="RestoreDefaults" title="<?echo GetMessage("MAIN_HINT_RESTORE_DEFAULTS")?>" OnClick="confirm('<?echo AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>')" value="<?echo GetMessage("MAIN_RESTORE_DEFAULTS")?>">
		<?$tabControl->End();?>
    </form>
	<?php
	CAdminFileDialog::ShowScript(Array
	(
		"event" => "OpenImage",
		"arResultDest" => Array("FUNCTION_NAME" => "pecomOptions.SetImageUrl"),
		"arPath" => Array(),
		"select" => 'D',
		"operation" => 'O',
		"showUploadTab" => false,
		"showAddToMenuTab" => false,
		"fileFilter" => '',
		"saveConfig" => true
	));
	?>
    <script>
        var pecomOptions = {
            init: function () {
                this.getUrl();
                this.getStatus();
            },
            getStatus: function () {
                let val = document.getElementById('PEC_ORDER_SEND').value;
                let div = document.getElementById('PEC_ORDER_CREATE_DIV');
                let info = document.getElementById('infoBlock');
                if (val === 'U') {
                    div.style.display = "flex";
                    info.style.display = "flex";
                } else {
                    document.getElementById('PEC_ORDER_CREATE').value = '';
                    div.style.display = "none";
                    info.style.display = "none";
                }
            },
            getUrl: function () {
                let val = document.getElementById('PEC_SAVE_PDF');
                let div = document.getElementById('PEC_SAVE_PDF_URL_DIV');
                if (val.checked)
                    div.style.display = "flex";
                else {
                    div.style.display = "none";
                }
            },
            SetImageUrl: function(filename,path) {
                document.getElementById('PEC_SAVE_PDF_URL').value = path;
            }
        }
        pecomOptions.init();
    </script>
	<?
}
?>
