<?
IncludeModuleLangFile(__FILE__);

if (class_exists('pecom_ecomm')) return;

use \Bitrix\Main\Loader;
use \Bitrix\Main\Config\Option;
use \Bitrix\Sale\Delivery\Services\Manager;
use CAgent;

class pecom_ecomm extends CModule
{
	public $MODULE_ID = "pecom.ecomm";
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $PARTNER_NAME;
    public $PARTNER_URI;
    public $MODULE_GROUP_RIGHTS = 'Y';
    public $NEED_MAIN_VERSION = '16.0.0';
    public $NEED_MODULES = array("sale" => '16.0.0');
    public $GROUP_ID;
    public $DELIVERY_ID;

	function __construct()
	{
        Loader::includeModule('sale');
		$arModuleVersion = array();

		include('version.php');

		if ( is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion) )
		{
			$this->MODULE_VERSION = $arModuleVersion['VERSION'];
			$this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
		}

		$this->PARTNER_NAME = GetMessage('PEC_DELIVERY_PARTNER_NAME');
		$this->PARTNER_URI = "https://pecom.ru/";

		$this->MODULE_NAME = GetMessage('PEC_DELIVERY_MODULE_NAME');
		$this->MODULE_DESCRIPTION = GetMessage('PEC_DELIVERY_MODULE_DESCRIPTION');
	}

	public function DoInstall()
	{
        global $APPLICATION;
		global $pecom_ecomm_global_errors;
        $pecom_ecomm_global_errors = array();

		if ( is_array($this->NEED_MODULES) && !empty($this->NEED_MODULES) )
		{
			foreach ( array_keys($this->NEED_MODULES) as $module )
			{
				if ( !IsModuleInstalled($module)
                    || CheckVersion($this->NEED_MODULES[$module], CModule::CreateModuleObject($module)->MODULE_VERSION))
				{
                    $pecom_ecomm_global_errors[] = GetMessage('PEC_DELIVERY_NEED_MODULES_VERSION', array('#MODULE#' => $module, '#NEED#' => $this->NEED_MODULES[$module]));
				}
			}
		}

		if ( strlen($this->NEED_MAIN_VERSION) > 0 && version_compare(SM_VERSION, $this->NEED_MAIN_VERSION) < 0 )
		{
            $pecom_ecomm_global_errors[] = GetMessage('PEC_DELIVERY_NEED_RIGHT_VER', array('#NEED#' => $this->NEED_MAIN_VERSION));
		}

        if ( count( $pecom_ecomm_global_errors ) == 0 ) {
            RegisterModule($this->MODULE_ID);
            $this->InstallFiles();
            $this->InstallDB();
            $this->InstallEvents();
            $this->setOptions();

            $this->DELIVERY_ID = $this->InstallDeliveryService();
            $this->SetPropsRequired();
            $this->RegisterAgent();
            $this->addSaleProperty();
        }

        exec("chmod -R 744 ".$_SERVER['DOCUMENT_ROOT']."/bitrix/modules/pecom.ecomm/lib/pec-api");
        $APPLICATION->IncludeAdminFile(GetMessage("PEC_DELIVERY_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/step1.php");
	}

	public function DoUninstall()
	{
        global $APPLICATION;
        Loader::includeModule($this->MODULE_ID);
        $arParams = Array();
		$this->UnInstallFiles($arParams);
        $this->UnInstallDB($arParams);
        $this->UnInstallEvents($arParams);
        $this->UnregisterAgent();
        $this->UninstallDeliveryService();
        UnRegisterModule($this->MODULE_ID);
        $APPLICATION->IncludeAdminFile(GetMessage("pecom_ecomm_uninstal_title"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/unstep1.php");
        return true;
	}


	function InstallDB($arParams = array())
	{
        $sql = '
create table if not exists pecom_ecomm
(
    ID int(11) NOT NULL auto_increment,
    ORDER_ID int(11),
    PEC_ID varchar(50),
    WIDGET text,
    STATUS text,
    PEC_API_SUBMIT_REQUEST text,
    PEC_API_SUBMIT_RESPONSE text,
    PEC_API_SUBMIT_OK varchar(1),
	UPTIME varchar(10),
	PRIMARY KEY(ID),
	INDEX ix_pecom_ecomm (ORDER_ID)
);
';
        global $DB;
        $errors = $DB->Query($sql);
        $sql = "show columns FROM `pecom_ecomm` where `Field` = 'TRANSPORTATION_TYPE'";
        $query = $DB->Query($sql);
        if (!$query->Fetch())
            $DB->Query('ALTER TABLE pecom_ecomm ADD COLUMN TRANSPORTATION_TYPE VARCHAR(255) NOT NULL AFTER STATUS;');
	}

	function InstallFiles($arParams = array())
	{
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/js/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js/", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/sale_delivery/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/include/sale_delivery/", true, true);
	}

	function InstallEvents($arParams = array())
	{
        $eventManager = \Bitrix\Main\EventManager::getInstance();

        $eventManager->registerEventHandler("sale", "OnSaleComponentOrderResultPrepared", $this->MODULE_ID, "\\Pec\\Delivery\\Handlers", "prepare");
        $eventManager->registerEventHandler("sale", "OnSaleOrderSaved", $this->MODULE_ID, "\\Pec\\Delivery\\Handlers", "SaleSaved");
        $eventManager->registerEventHandler("sale", "OnOrderUpdate", $this->MODULE_ID, "\\Pec\\Delivery\\Handlers", "OrderUpdate");
        $eventManager->registerEventHandler("sale", "OnSaleStatusOrder", $this->MODULE_ID, "\\Pec\\Delivery\\Handlers", "OnSaleStatusOrder");
        $eventManager->registerEventHandler("main", "OnAdminSaleOrderViewDraggable", $this->MODULE_ID, "\\Pec\\Delivery\\Handlers", "onOrderAdmin");
        $eventManager->registerEventHandler("main", "OnEpilog", $this->MODULE_ID, "\\Pec\\Delivery\\Handlers", "onChangeDeliveryService");
        $eventManager->unRegisterEventHandler("main", "OnEpilog", $this->MODULE_ID, '\\Ipolh\\Pecom\\subscribeHandler', "onEpilog");
        DeleteDirFilesEx($_SERVER["DOCUMENT_ROOT"].\COption::GetOptionString('sale','delivery_handles_custom_path','/bitrix/php_interface/include/sale_delivery')."delivery_ipolh_pecom.php");
    }

    function setOptions() {
        $arOptions = [
            'PEC_API_ALLOW_DELIVERY' => 'a:14:{i:-2;s:1:"1";i:-1;s:1:"1";i:1;s:1:"1";i:2;s:1:"1";i:3;s:1:"1";i:4;s:1:"1";i:5;s:1:"1";i:6;s:1:"1";i:7;s:1:"1";i:8;s:1:"1";i:9;s:1:"1";i:10;s:1:"1";i:11;s:1:"1";i:12;s:1:"1";}',
            'PEC_API_SHIPPED' => 'a:14:{i:-2;s:1:"2";i:-1;s:1:"2";i:1;s:1:"1";i:2;s:1:"1";i:3;s:1:"1";i:4;s:1:"1";i:5;s:1:"1";i:6;s:1:"1";i:7;s:1:"1";i:8;s:1:"1";i:9;s:1:"1";i:10;s:1:"1";i:11;s:1:"1";i:12;s:1:"1";}',
            'PEC_API_STATUS_TABLE' => 'a:14:{i:-2;s:2:"DT";i:-1;s:2:"DT";i:1;s:2:"DS";i:2;s:2:"DT";i:3;s:2:"DS";i:4;s:2:"DS";i:5;s:2:"DS";i:6;s:2:"DS";i:7;s:2:"DS";i:8;s:2:"DF";i:9;s:2:"DF";i:10;s:2:"DS";i:11;s:2:"DF";i:12;s:2:"DF";}',
            'PEC_API_START_AGENT' => 'a:9:{i:-2;s:2:"on";i:-1;s:2:"on";i:1;s:2:"on";i:3;s:2:"on";i:4;s:2:"on";i:5;s:2:"on";i:6;s:2:"on";i:7;s:2:"on";i:10;s:2:"on";}',
        ];

        foreach ($arOptions as $key => $val) {
            Option::set($this->MODULE_ID, $key, $val);
        }
    }

    function SetPropsRequired() {
        $db_props = CSaleOrderProps::GetList(
            array("SORT" => "ASC"),
            array(
                "CODE" => "PHONE",
            ),
            false,
            false,
            array()
        );

        while ($props = $db_props->Fetch()) {
            if ($props['REQUIED'] == 'N') {
                $arFields = array(
                    "REQUIED" => "Y",
                );

                // Если установлен код свойства, то изменяем свойство с этим кодом,
                // иначе добавляем новой свойство
                $ID = $props['ID'];
                if ($ID>0)
                {
                    if (!CSaleOrderProps::Update($ID, $arFields))
                    {
                        // echo "Ошибка изменения параметров свойства";
                    }
                    else
                    {
                        // Обновим символьный код у значений свойства
                        // (хранение избыточных данных для оптимизации работы)
                        $db_order_props_tmp =
                            CSaleOrderPropsValue::GetList(($b="NAME"),
                                ($o="ASC"),
                                Array("ORDER_PROPS_ID"=>$ID));
                        while ($ar_order_props_tmp = $db_order_props_tmp->Fetch())
                        {
                            CSaleOrderPropsValue::Update($ar_order_props_tmp["ID"],
                                array("CODE" => "COMPLECT"));
                        }
                    }
                }
            }
        }
    }


	function UnInstallFiles($arParams = array())
	{
		DeleteDirFilesEx("/bitrix/js/pecom.ecomm/");
		DeleteDirFilesEx("/bitrix/php_interface/include/sale_delivery/pecomecomm/");
	}


	function UnInstallDB($arParams = array())
	{
	    return; // todo
        // $sql = 'drop table if exists pecom_ecomm;';
        // global $DB;
        // $errors = $DB->Query($sql);
        // return $errors->result;
	}

	function UnInstallEvents($arParams = array())
	{
        $eventManager = \Bitrix\Main\EventManager::getInstance();

        $eventManager->unRegisterEventHandler("sale", "OnSaleComponentOrderResultPrepared", $this->MODULE_ID, "\\Pec\\Delivery\\Handlers", "prepare");
        $eventManager->unRegisterEventHandler("sale", "OnSaleOrderBeforeSaved", $this->MODULE_ID, "\\Pec\\Delivery\\Handlers", "BeforeSaved");
        $eventManager->unRegisterEventHandler("sale", "OnSaleOrderSaved", $this->MODULE_ID, "\\Pec\\Delivery\\Handlers", "SaleSaved");
        $eventManager->unRegisterEventHandler("sale", "OnSaleStatusOrder", $this->MODULE_ID, "\\Pec\\Delivery\\Handlers", "OnSaleStatusOrder");
        $eventManager->unRegisterEventHandler("sale", "OnOrderUpdate", $this->MODULE_ID, "\\Pec\\Delivery\\Handlers", "OrderUpdate");
        $eventManager->unRegisterEventHandler("main", "OnAdminSaleOrderViewDraggable", $this->MODULE_ID, "\\Pec\\Delivery\\Handlers", "onOrderAdmin");
	}

	function UnregisterAgent() {
        CAgent::RemoveModuleAgents($this->MODULE_ID);
    }

    function InstallDeliveryService() {
        if (!self::UninstallDeliveryService()) {
            $arFile = \CFile::MakeFileArray('/bitrix/modules/' . $this->MODULE_ID . '/install/sale_delivery/pecomecomm/logo_sq2.png');
            $arLogo = \CFile::SaveFile($arFile, "sale/delivery/logotip");

            $arFields = array(
                'NAME' => GetMessage('PEC_DELIVERY_MODULE_NAME'),
                'ACTIVE' => 'Y',
                'DESCRIPTION' => GetMessage('PEC_DELIVERY_SERVICE_NAME_COURIER'),
                'LOGOTIP' => $arLogo,
                'CLASS_NAME' => '\Sale\Handlers\Delivery\PecomEcommHandler',
                'CURRENCY' => 'RUB',
                'ALLOW_EDIT_SHIPMENT' => 'Y'
            );

            return Manager::add($arFields);
        }
    }

    function UninstallDeliveryService() {
        $deliveries = [];
        $result = false;

        if (method_exists ( '\Bitrix\Sale\Delivery\Services\Manager', 'getList' )) {
            $res = Manager::getList(
                array(
                    'select' => array('ID', 'NAME', 'DESCRIPTION', 'CLASS_NAME', 'ALLOW_EDIT_SHIPMENT', 'CURRENCY'),
                    'order' => array('ACTIVE' => 'DESC')
                )
            );
            while ($item = $res->fetch()) {
                if ($item['CLASS_NAME'] == '\Sale\Handlers\Delivery\PecomEcommHandler'
                    || $item['CLASS_NAME'] == '\Sale\Handlers\Delivery\PecomIntegrationHandler'
                ) {
                    $deliveries[] = [
                        'id' => $item['ID'],
                        'ALLOW_EDIT_SHIPMENT' => $item['ALLOW_EDIT_SHIPMENT'],
                        'NAME' => $item['NAME'],
                        'DESCRIPTION' => $item['DESCRIPTION'],
                        'CURRENCY' => $item['CURRENCY']
                    ];
                }
            }
            rsort($deliveries);
        }
        foreach ($deliveries as $key =>$delivery) {
            if ($key == 0) {
                $name = $delivery['NAME'] ? : GetMessage('PEC_DELIVERY_MODULE_NAME');
                $description = $delivery['DESCRIPTION'] ? : GetMessage('PEC_DELIVERY_SERVICE_NAME_COURIER');
                $arFields = array(
                    'NAME' => $name,
                    'ACTIVE' => 'Y',
                    'DESCRIPTION' => $description,
                    'CLASS_NAME' => '\Sale\Handlers\Delivery\PecomEcommHandler',
                    'CURRENCY' => $delivery['CURRENCY'],
                    'ALLOW_EDIT_SHIPMENT' => $delivery['ALLOW_EDIT_SHIPMENT']
                );

                if (Manager::update($delivery['id'], $arFields))
                    $result = true;
            } else {
                Manager::delete($delivery['id']);
            }
        }

        if (count($deliveries) == 0)
            $result = false;

        return $result;
    }

    function RegisterAgent($interval = 7200)
    {
		$arAgent = CAgent::GetList([], ["NAME" => '\Pec\Delivery\Tools::agentUpdateOrdersPecStatus();'])->Fetch();
		if (!$arAgent) {
			$active = Option::get($this->MODULE_ID, 'PEC_API_AGENT_ACTIVE', '') ? 'Y' : 'N';
			$objDateTime = new \DateTime("+10 seconds");
            $date = $objDateTime->format("d.m.Y H:i:s");
            CAgent::AddAgent(
                "\Pec\Delivery\Tools::agentUpdateOrdersPecStatus();",
                $this->MODULE_ID,
                'Y',
                $interval,
                'Y',
                $active,
                $date
            );
        }
    }

    function addSaleProperty() {
		$tmpGet = \CSalePersonType::GetList(Array("SORT" => "ASC"), Array());
		$allPayers=array();
		while($tmpElement=$tmpGet->Fetch()){
			if ($tmpElement['ACTIVE'] == 'Y')
				$allPayers[] = $tmpElement['ID'];
		}

		foreach($allPayers as $payer){
			$prop = CSaleOrderProps::GetList(array(), array("CODE" => "PEC_DELIVERY"))->Fetch();
			$tmpGet = CSaleOrderPropsGroup::GetList(array("SORT" => "ASC"),array("PERSON_TYPE_ID" => $payer),false,array('nTopCount' => '1'));
			$tmpVal = $tmpGet->Fetch();
			if (!$prop) {
				$arField = array(
					"PERSON_TYPE_ID" => $payer,
					"NAME" => GetMessage("PEC_DELIVERY_SERVICE_NAME"),
					"DESCRIPTION" => '',
					"TYPE" => "TEXT",
					"REQUIED" => "N",
					"DEFAULT_VALUE" => "",
					"SORT" => 200,
					"CODE" => 'PEC_DELIVERY',
					"PROPS_GROUP_ID" => $tmpVal['ID'],
					"USER_PROPS" => "Y",
					"IS_LOCATION" => "N",
					"IS_LOCATION4TAX" => "N",
					"SIZE1" => 140,
					"SIZE2" => 2,
					"IS_EMAIL" => "N",
					"IS_PROFILE_NAME" => "N",
					"IS_PAYER" => "N",
					"IS_FILTERED" => "Y",
					"IS_ZIP" => "N",
					"UTIL" => "Y"
				);
				$propId = CSaleOrderProps::Add($arField);
				if ($propId) {
					CSaleOrderProps::UpdateOrderPropsRelations($propId, [$this->DELIVERY_ID], "D");
				}
			}

			$op = CSaleOrderProps::GetList(array(), array("CODE" => "PEC_DOCUMENT_TYPE"))->Fetch();
			if (!$op) {
				$arFields = array(
					"PERSON_TYPE_ID" => $payer,
					"NAME" => GetMessage("PEC_DELIVERY_DOCUMENT_TYPE"),
					"DESCRIPTION" => GetMessage("PEC_DELIVERY_DOCUMENT_TYPE_DESCRIPTION"),
					"ACTIVE" => "N",
					"TYPE" => "SELECT",
					"REQUIED" => "N",
					"DEFAULT_VALUE" => "",
					"SORT" => 200,
					"CODE" => 'PEC_DOCUMENT_TYPE',
					"PROPS_GROUP_ID" => $tmpVal['ID'],
				);
				$ID = CSaleOrderProps::Add($arFields);
				if ($ID) {
					$arFieldsV = array(
						[
							"ORDER_PROPS_ID" => $ID,
							"VALUE" => 10,
							"NAME" => GetMessage("PEC_DELIVERY_DOCUMENT_TYPE_P"),
							"SORT" => 100,
							"DESCRIPTION" => GetMessage("PEC_DELIVERY_DOCUMENT_TYPE_P")
						],
						[
							"ORDER_PROPS_ID" => $ID,
							"VALUE" => 3,
							"NAME" => GetMessage("PEC_DELIVERY_DOCUMENT_TYPE_V"),
							"SORT" => 200,
							"DESCRIPTION" => GetMessage("PEC_DELIVERY_DOCUMENT_TYPE_V")
						],
						[
							"ORDER_PROPS_ID" => $ID,
							"VALUE" => 5,
							"NAME" => GetMessage("PEC_DELIVERY_DOCUMENT_TYPE_Z"),
							"SORT" => 300,
							"DESCRIPTION" => GetMessage("PEC_DELIVERY_DOCUMENT_TYPE_Z")
						],
						[
							"ORDER_PROPS_ID" => $ID,
							"VALUE" => 12,
							"NAME" => GetMessage("PEC_DELIVERY_DOCUMENT_TYPE_B"),
							"SORT" => 400,
							"DESCRIPTION" => GetMessage("PEC_DELIVERY_DOCUMENT_TYPE_B")
						],
						[
							"ORDER_PROPS_ID" => $ID,
							"VALUE" => 1,
							"NAME" => GetMessage("PEC_DELIVERY_DOCUMENT_TYPE_I"),
							"SORT" => 500,
							"DESCRIPTION" => GetMessage("PEC_DELIVERY_DOCUMENT_TYPE_I")
						]
					);
					foreach ($arFieldsV as $item) {
						CSaleOrderPropsVariant::Add($item);
					}
					return CSaleOrderProps::UpdateOrderPropsRelations($ID, [$this->DELIVERY_ID], "D");
				}
			}
		}
	}
}

?>