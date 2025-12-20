<?// подключим все необходимые файлы:
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php"); // первый общий пролог
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use E_1\Quantities;

$module_id = 'e1.site.settings'; //обязательно, иначе права доступа не работают!

Loc::loadMessages($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/options.php");
Loc::loadMessages(__FILE__);

// if ($APPLICATION->GetGroupRight($module_id)<"S")
// {
//     $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
// }

\Bitrix\Main\Loader::includeModule('iblock');
\Bitrix\Main\Loader::includeModule('catalog');
\Bitrix\Main\Loader::includeModule('sale');
//\Bitrix\Main\Loader::includeModule($module_id);

// Start получаем инфоблоки для формы
$obIblocks = \Bitrix\Iblock\IblockTable::getList(array(
        'select'  => array('*'), // имена полей, которые необходимо получить в результате
        //'filter'  => array('STOCK_ID' => $ID), // описание фильтра для WHERE и HAVING
        //'order'   => array('ID'=>'ASC'), // параметры сортировки
));

$iPropertySeriya = \Bitrix\Iblock\PropertyTable::getList(array(
    'filter' => array(
        'IBLOCK_ID'=>\Cosmos\Config::getInstance()->getIblockIdByCode('1c_catalog'),
        'ACTIVE'=>'Y',
        '=PROPERTY_TYPE'=>\Bitrix\Iblock\PropertyTable::TYPE_LIST,
		'CODE'=> 'SERIYA_SHKAFA',
    ),
    'select' => array(
        'ID',
        'CODE',
    ),
))->fetch()['ID'];

$arSeriyaProperty = [];

CModule::IncludeModule("fileman");

//добавление визуального редактора
function add_textarea_form ($c,$g) {
	$LHE = new CLightHTMLEditor;
	$LHE->Show(array(
		'id' => "",
		'width' => '100%',
		'height' => '400px',
		'inputName' => $c,
		'content' => $g,
		'bUseFileDialogs' => false,
		'bFloatingToolbar' => false,
		'bArisingToolbar' => false,
		'toolbarConfig' => array(
			'Bold', 'Italic', 'Underline', 'RemoveFormat', 'Code', 'Source', 'Video', 'Html',
			'CreateLink', 'DeleteLink', 'Image', 'Video',
			'BackColor', 'ForeColor',
			'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyFull',
			'InsertOrderedList', 'InsertUnorderedList', 'Outdent', 'Indent',
			'StyleList', 'HeaderList',
			'FontList', 'FontSizeList',
		),
	));
}

if (!empty($iPropertySeriya)) {
	$rsEnumSeriya = \Bitrix\Iblock\PropertyEnumerationTable::getList(array(
		'filter' => array('PROPERTY_ID'=> $iPropertySeriya),
	));

	while($arEnumSeriya = $rsEnumSeriya->fetch())
	{
		$arSeriyaProperty[] = ['E1_SS_SYSTEM_SERIYA_' . $arEnumSeriya['ID'], $arEnumSeriya['VALUE'], '',['textareaBitrix']];
	}
}

$arIblocks = $obIblocks->fetchAll();

$arFormIblockField = array(0 => Loc::getMessage('E1_SS_NOT_CHOOSE_FIELD'));
foreach ($arIblocks as $key => $value) {
    $arFormIblockField[$value['ID']] = '[' . $value['ID'] .  '] ' . $value['NAME'];
}
// End получаем инфоблоки для формы

//Start получаем доставки для формы
$arFormDeliverysField = array(0 => Loc::getMessage('E1_SS_NOT_CHOOSE_FIELD'));
$obFormDeliverys = \Bitrix\Sale\Delivery\Services\Table::getList(array(
    'select'  => array('*'),
    'order'   => array('ID' => 'ASC')
));
$arFormDeliverys = $obFormDeliverys->fetchAll();

foreach ($arFormDeliverys as $key => $value) {
    $arFormDeliverysField[$value['ID']] = '[' . $value['ID'] .  '] ' . $value['NAME'];
}
//End получаем доставки для формы

//Start получаем оплаты для формы
$arFormPaymentsField = array(0 => Loc::getMessage('E1_SS_NOT_CHOOSE_FIELD'));
$obFormPayments = \Bitrix\Sale\Internals\PaySystemActionTable::getList(array(
    'select'  => array('*'),
    'order'   => array('ID' => 'ASC')
));
$arFormPayments = $obFormPayments->fetchAll();

foreach ($arFormPayments as $key => $value) {
    $arFormPaymentsField[$value['ID']] = '[' . $value['ID'] .  '] ' . $value['NAME'];
}
//End получаем оплаты для формы

//Start получаем цены для формы
$obFormPrices = \Bitrix\Catalog\GroupTable::getList(array(
    'select'  => array('*'),
    'order'   => array('SORT' => 'ASC')
));
$arFormPrices = $obFormPrices->fetchAll();

$arFormPricesField = array(0 => Loc::getMessage('E1_SS_NOT_CHOOSE_FIELD'));
foreach ($arFormPrices as $key => $value) {
    $arFormPricesField[$value['ID']] = array();
}
$obFormPrices = \Bitrix\Catalog\GroupLangTable::getList(array(
    'select'  => array('*'),
    //'order'   => array('SORT'=>'ASC'),
    'filter' => array('LANG' => LANG)
));
$arFormPrices = $obFormPrices->fetchAll();

foreach ($arFormPrices as $key => $value) {
    $arFormPricesField[$value['CATALOG_GROUP_ID']] = '[' . $value['CATALOG_GROUP_ID'] .  '] ' . $value['NAME'];
}
//End получаем цены для формы

$request = \Bitrix\Main\HttpApplication::getInstance()->getContext()->getRequest();

#Описание опций

// не забудем разделить подготовку данных и вывод
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$aTabs = array(
    array(
        'DIV' => 'edit1',
        'TAB' => Loc::getMessage('E1_SS_TAB'),
        'OPTIONS' => array(
            // array('field_line', Loc::getMessage('ACADEMY_D7_FIELD_LINE_TITLE'),
            //     '',
            //     array('text', 10)),
            // array('field_list', Loc::getMessage('ACADEMY_D7_FIELD_LIST_TITLE'),
            //     '',
            //     array('multiselectbox', array('var1'=>'var1','var2'=>'var2','var3'=>'var3','var4'=>'var4'))),
            array('E1_SS_USE_PREPAYMENT', Loc::getMessage('E1_SS_USE_PREPAYMENT'),
                '',
                array('checkbox', '')),
			array('E1_SS_USE_FILTER_SAVE_PRICE', Loc::getMessage('E1_SS_USE_FILTER_SAVE_PRICE'),
                '',
                array('checkbox', '')),
			array('E1_SS_USE_FILTER_CLEAR_CACHE', Loc::getMessage('E1_SS_USE_FILTER_CLEAR_CACHE'),
                '',
                array('checkbox', '')),
			array('E1_SS_IS_HOME_SHOW_BUTTON', Loc::getMessage('E1_SS_IS_HOME_SHOW_BUTTON'),
                '',
                array('checkbox', '')),
            array('E1_SS_PREPAYMENT_VALUE', Loc::getMessage('E1_SS_PREPAYMENT_VALUE'),
                '',
                array('text', 10)),
            array('E1_SS_PREPAYMENT_PAYMENT', Loc::getMessage('E1_SS_PREPAYMENT_PAYMENT'),
                '',
                array('selectbox', $arFormPaymentsField)),
            array('E1_SS_SHOW_DELIVERY_WAREHOUSES', Loc::getMessage('E1_SS_SHOW_DELIVERY_WAREHOUSES'),
                '',
                array('selectbox', $arFormDeliverysField)),
            array('E1_SS_DEFAULT_PRICE', Loc::getMessage('E1_SS_DEFAULT_PRICE'),
                '',
                array('multiselectbox', $arFormPricesField)),
            array('E1_SS_BASE_PRICE', Loc::getMessage('E1_SS_BASE_PRICE'),
                '',
                array('selectbox', $arFormPricesField)),
            array('E1_SS_REGIONS_IBLOCK', Loc::getMessage('E1_SS_REGIONS_IBLOCK'),
                '',
                array('selectbox', $arFormIblockField)),
            array('E1_SS_UNDER_PAYMENT_BUTTONTEXT', Loc::getMessage('E1_SS_UNDER_PAYMENT_BUTTONTEXT'),
                '',
                array('textarea', 3, 50)),
			array('E1_SS_FOOTER_PHONE', Loc::getMessage('E1_SS_FOOTER_PHONE'),
                '',
                array('textarea', 3, 50)),
			array('E1_SS_MODAL_ORDER_TITLE', Loc::getMessage('E1_SS_MODAL_ORDER_TITLE'),
                '',
                array('textarea', 3, 50)),
			array('E1_SS_URL_TAGS_SECTION', Loc::getMessage('E1_SS_URL_TAGS_SECTION'),
                '',
                array('textarea', 3, 50)),
			array('E1_SS_ID_TAGS_SECTION', Loc::getMessage('E1_SS_ID_TAGS_SECTION'),
                '',
                array('textarea', 3, 50)),
			array('E1_SS_MODAL_ORDER_TEXT', Loc::getMessage('E1_SS_MODAL_ORDER_TEXT'),
                '',
                array('textarea', 3, 50)),
			array('E1_SS_MODAL_ORDER_BUTTON_YES', Loc::getMessage('E1_SS_MODAL_ORDER_BUTTON_YES'),
                '',
                array('textarea', 3, 50)),
			array('E1_SS_MODAL_ORDER_BUTTON_NO', Loc::getMessage('E1_SS_MODAL_ORDER_BUTTON_NO'),
                '',
                array('textarea', 3, 50)),
			array('E1_SS_SYSTEM_NUMBER_ZVONOK', Loc::getMessage('E1_SS_SYSTEM_NUMBER_ZVONOK'),
                '',
                array('textarea', 3, 50)),
			array('E1_SS_PRICE_MODULE_ASSEMBLY', Loc::getMessage('E1_SS_PRICE_MODULE_ASSEMBLY'),
			'',
			array('textarea', 3, 50)),
            array('E1_SS_USE_WARNING_MESSAGE', Loc::getMessage('E1_SS_USE_WARNING_MESSAGE'),
                '',
                array('checkbox', '')),
            array('E1_SS_MODAL_WARNING_TITLE', Loc::getMessage('E1_SS_MODAL_WARNING_TITLE'),
                '',
                array('textarea', 3, 50)),
            array('E1_SS_MODAL_WARNING_TEXT', Loc::getMessage('E1_SS_MODAL_WARNING_TEXT'),
                '',
                array('textarea', 3, 50)),
            array('E1_SS_MODAL_WARNING_BUTTON', Loc::getMessage('E1_SS_MODAL_WARNING_BUTTON'),
                '',
                array('textarea', 3, 50)),
			array('E1_SS_UTM_BANNERY_HOME_CODE', Loc::getMessage('E1_SS_UTM_BANNERY_HOME_CODE'),
                '',
                array('textarea', 3, 50)),
            // array('ORWOPM_NEW_PRICE', Loc::getMessage('ORWOPM_NEW_PRICE_FIELD'),
            //     '',
            //     array('selectbox', $arFormPricesField)),
            // array('ORWOPM_ACTION_PRICE', Loc::getMessage('ORWOPM_ACTION_PRICE_FIELD'),
            //     '',
            //     array('multiselectbox', $arFormPricesField)),
        )
    ),
    array(
        "DIV" => "edit2",
        "TAB" => 'Единое описание',
        "TITLE" => 'Единое описание для серий в каталоге',
		'OPTIONS' => $arSeriyaProperty,
    ),
	array(
        "DIV" => "edit3",
        "TAB" => 'Кастомные скидки',
        "TITLE" => 'Настройки кастомных скидок',
		'OPTIONS' =>  array(
			array(
				'E1_SS_CUSTOM_SALE_BASKET',
				Loc::getMessage('E1_SS_CUSTOM_SALE_BASKET'),
				'',
				array('checkbox', '')
			),
		),
	),
    array(
        "DIV" => "edit4",
        "TAB" => 'Импорт',
        "TITLE" => 'Импорт количества товаров',
		'OPTIONS' => array(
			array(
				'E1_SS_PRODUCTS_QUANTITY',
				'Количество товаров (XLSX)',
				'',
				array('file')
			),
		),
    ),
);
#Сохранение
if ($request->isPost() && $request['Update'] && check_bitrix_sessid())
{
    foreach ($aTabs as $aTab)
    {
        //Или можно использовать __AdmSettingsSaveOptions($MODULE_ID, $arOptions);
        foreach ($aTab['OPTIONS'] as $arOption)
        {
            if (!is_array($arOption)) //Строка с подсветкой. Используется для разделения настроек в одной вкладке
                continue;

            if ($arOption['note']) //Уведомление с подсветкой
                continue;

			if ($arOption[0] == 'E1_SS_PRODUCTS_QUANTITY' && isset($_FILES[$arOption[0]])) {
				Quantities::importQuantitiesFromXlsx($_FILES[$arOption[0]]);
				continue;
			}

            //Или __AdmSettingsSaveOption($MODULE_ID, $arOption);
            $optionName = $arOption[0];

            $optionValue = $request->getPost($optionName);

            Option::set($module_id, $optionName, is_array($optionValue) ? implode(",", $optionValue):$optionValue);
        }
    }
}

#Визуальный вывод

$tabControl = new CAdminTabControl('tabControl', $aTabs);
?>
<?$tabControl->Begin();?>
<form enctype="multipart/form-data" method='post' action='<?echo $APPLICATION->GetCurPage()?>?mid=<?=htmlspecialcharsbx($request['mid'])?>&lang=<?=$request['lang']?>' name='orwo_pricemanagement_settings'>

    <? foreach ($aTabs as $aTab):?>
        <?if($aTab['OPTIONS']):?>
            <?$tabControl->BeginNextTab();?>
            <?//__AdmSettingsDrawList($module_id, $aTab['OPTIONS']);?>
            <?//__AdmSettingsDrawRow($module_id, $aTab['OPTIONS']);?>

			<?foreach($aTab['OPTIONS'] as $Option):?>
				<?if(!is_array($Option)):?>
					<tr class="heading">
						<td colspan="2"><?=$Option?></td>
					</tr>
				<?elseif(isset($Option["note"])):?>
					<tr>
						<td colspan="2" align="center">
							<?echo BeginNote('align="center"');?>
							<?=$Option["note"]?>
							<?echo EndNote();?>
						</td>
					</tr>
				<?else:?>
					<?$isChoiceSites = array_key_exists(6, $Option) && $Option[6] == "Y" ? true : false;
					$listSite = array();
					$listSiteValue = array();
					if ($Option[0] != "")
					{
						if ($isChoiceSites)
						{
							$queryObject = \Bitrix\Main\SiteTable::getList(array(
								"select" => array("LID", "NAME"),
								"filter" => array(),
								"order" => array("SORT" => "ASC"),
							));
							$listSite[""] = GetMessage("MAIN_ADMIN_SITE_DEFAULT_VALUE_SELECT");
							$listSite["all"] = GetMessage("MAIN_ADMIN_SITE_ALL_SELECT");
							while ($site = $queryObject->fetch())
							{
								$listSite[$site["LID"]] = $site["NAME"];
								$val = COption::GetOptionString($module_id, $Option[0], $Option[2], $site["LID"], true);
								if ($val)
									$listSiteValue[$Option[0]."_".$site["LID"]] = $val;
							}
							$val = "";
							if (empty($listSiteValue))
							{
								$value = COption::GetOptionString($module_id, $Option[0], $Option[2]);
								if ($value)
									$listSiteValue = array($Option[0]."_all" => $value);
								else
									$listSiteValue[$Option[0]] = "";
							}
						}
						else
						{
							$val = COption::GetOptionString($module_id, $Option[0], $Option[2]);
						}
					}
					else
					{
						$val = $Option[2];
					}
					if ($isChoiceSites):?>
					<tr>
						<td colspan="2" style="text-align: center!important;">
							<label><?=$Option[1]?></label>
						</td>
					</tr>
					<?endif;?>
					<?if ($isChoiceSites):
						foreach ($listSiteValue as $fieldName => $fieldValue):?>
						<tr>
						<?
							$siteValue = str_replace($Option[0]."_", "", $fieldName);
							renderLable($Option, $listSite, $siteValue);
							renderInput($Option, $arControllerOption, $fieldName, $fieldValue);
						?>
						</tr>
						<?endforeach;?>
					<?else:
						?>
						<tr>
						<?
							renderLable($Option, $listSite);
							renderInput($Option, $arControllerOption, $Option[0], $val);
						?>
						</tr>
					<?endif;?>
					<? if ($isChoiceSites): ?>
						<tr>
							<td width="50%">
								<a href="javascript:void(0)" onclick="addSiteSelector(this)" class="bx-action-href">
									<?=GetMessage("MAIN_ADMIN_ADD_SITE_SELECTOR")?>
								</a>
							</td>
							<td width="50%"></td>
						</tr>
					<? endif; ?>
				<?endif;?>
			<?endforeach?>
        <?endif;?>
    <?endforeach;?>

    <?
    $tabControl->BeginNextTab();
    $tabControl->Buttons(); 
    ?>

    <input type="submit" name="Update" value="<?echo GetMessage('MAIN_SAVE')?>">
    <input type="reset" name="reset" value="<?echo GetMessage('MAIN_RESET')?>">
    <?=bitrix_sessid_post();?>
</form>
<?$tabControl->End();?>


<?
function renderLable($Option, array $listSite, $siteValue = "")
{
	$type = $Option[3];
	$sup_text = array_key_exists(5, $Option) ? $Option[5] : '';
	$isChoiceSites = array_key_exists(6, $Option) && $Option[6] == "Y" ? true : false;
	?>
	<?if ($isChoiceSites): ?>
	<script type="text/javascript">
		//TODO It is possible to modify the functions if necessary to clone different elements
		function changeSite(el, fieldName)
		{
			var tr = jsUtils.FindParentObject(el, "tr");
			var sel = jsUtils.FindChildObject(tr.cells[1], "select");
			sel.name = fieldName+"_"+el.value;
		}
		function addSiteSelector(a)
		{
			var row = jsUtils.FindParentObject(a, "tr");
			var tbl = row.parentNode;
			var tableRow = tbl.rows[row.rowIndex-1].cloneNode(true);
			tbl.insertBefore(tableRow, row);
			var sel = jsUtils.FindChildObject(tableRow.cells[0], "select");
			sel.name = "";
			sel.selectedIndex = 0;
			sel = jsUtils.FindChildObject(tableRow.cells[1], "select");
			sel.name = "";
			sel.selectedIndex = 0;
		}
	</script>
	<td width="50%">
		<select onchange="changeSite(this, '<?=htmlspecialcharsbx($Option[0])?>')">
			<?foreach ($listSite as $lid => $siteName):?>
				<option <?if ($siteValue ==$lid) echo "selected";?> value="<?=htmlspecialcharsbx($lid)?>">
					<?=htmlspecialcharsbx($siteName)?>
				</option>
			<?endforeach;?>
		</select>
	</td>
	<?else:?>
		<td<?if ($type[0]=="multiselectbox" || $type[0]=="textarea" || $type[0]=="statictext" ||
		$type[0]=="statichtml") echo ' class="adm-detail-valign-top"'?> width="50%"><?
		if ($type[0]=="checkbox")
			echo "<label for='".htmlspecialcharsbx($Option[0])."'>".$Option[1]."</label>";
		else
			echo $Option[1];
		if (strlen($sup_text) > 0)
		{
			?><span class="required"><sup><?=$sup_text?></sup></span><?
		}
		?><a name="opt_<?=htmlspecialcharsbx($Option[0])?>"></a></td>
	<?endif;
}

function renderInput($Option, $arControllerOption, $fieldName, $val)
{
	$type = $Option[3];
	$disabled = array_key_exists(4, $Option) && $Option[4] == 'Y' ? ' disabled' : '';
	?><td width="50%"><?
	if($type[0]=="checkbox"):
		?><input type="checkbox" <?if(isset($arControllerOption[$Option[0]]))echo ' disabled title="'.GetMessage("MAIN_ADMIN_SET_CONTROLLER_ALT").'"';?> id="<?echo htmlspecialcharsbx($Option[0])?>" name="<?=htmlspecialcharsbx($fieldName)?>" value="Y"<?if($val=="Y")echo" checked";?><?=$disabled?><?if($type[2]<>'') echo " ".$type[2]?>><?
	elseif($type[0]=="text" || $type[0]=="password"):
		?><input type="<?echo $type[0]?>"<?if(isset($arControllerOption[$Option[0]]))echo ' disabled title="'.GetMessage("MAIN_ADMIN_SET_CONTROLLER_ALT").'"';?> size="<?echo $type[1]?>" maxlength="255" value="<?echo htmlspecialcharsbx($val)?>" name="<?=htmlspecialcharsbx($fieldName)?>"<?=$disabled?><?=($type[0]=="password" || $type["noautocomplete"]? ' autocomplete="new-password"':'')?>><?
	elseif($type[0]=="selectbox"):
		$arr = $type[1];
		if(!is_array($arr))
			$arr = array();
		?><select name="<?=htmlspecialcharsbx($fieldName)?>" <?if(isset($arControllerOption[$Option[0]]))echo ' disabled title="'.GetMessage("MAIN_ADMIN_SET_CONTROLLER_ALT").'"';?> <?=$disabled?>><?
		foreach($arr as $key => $v):
			?><option value="<?echo $key?>"<?if($val==$key)echo" selected"?>><?echo htmlspecialcharsbx($v)?></option><?
		endforeach;
		?></select><?
	elseif($type[0]=="multiselectbox"):
		$arr = $type[1];
		if(!is_array($arr))
			$arr = array();
		$arr_val = explode(",",$val);
		?><select size="5" <?if(isset($arControllerOption[$Option[0]]))echo ' disabled title="'.GetMessage("MAIN_ADMIN_SET_CONTROLLER_ALT").'"';?> multiple name="<?=htmlspecialcharsbx($fieldName)?>[]"<?=$disabled?>><?
		foreach($arr as $key => $v):
			?><option value="<?echo $key?>"<?if(in_array($key, $arr_val)) echo " selected"?>><?echo htmlspecialcharsbx($v)?></option><?
		endforeach;
		?></select><?
	elseif($type[0]=="textarea"):
		?><textarea <?if(isset($arControllerOption[$Option[0]]))echo ' disabled title="'.GetMessage("MAIN_ADMIN_SET_CONTROLLER_ALT").'"';?> rows="<?echo $type[1]?>" cols="<?echo $type[2]?>" name="<?=htmlspecialcharsbx($fieldName)?>"<?=$disabled?>><?echo htmlspecialcharsbx($val)?></textarea><?
	elseif($type[0]=="statictext"):
		echo htmlspecialcharsbx($val);
	elseif($type[0] == 'textareaBitrix'):
		add_textarea_form($fieldName, ($val));
	elseif($type[0]=="statichtml"):
		echo $val;
	elseif($type[0]=="file"):?>
		<input type="file" name="<?=$fieldName?>">
	<?endif;?>
	</td><?
}

// завершение страницы
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>