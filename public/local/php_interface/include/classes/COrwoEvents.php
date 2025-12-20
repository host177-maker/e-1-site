<?
use \Bitrix\Main\Localization\Loc,
	\Bitrix\Main\Config\Option,
	\Bitrix\Main\Context,
	\Bitrix\Main\Event,
	\Bitrix\Main\Page\Asset;	

if(!defined('ASPRO_MAX_MODULE_ID')){
	define('ASPRO_MAX_MODULE_ID', 'aspro.max');
}

class COrwoEvents
{
	public static $iPriceDontDelete = 0;
	public static $bHandlerDisallow = 0;

	public static function disableHandler()
	{
		self::$bHandlerDisallow--;
	}

	public static function enableHandler()
	{
		self::$bHandlerDisallow++;
	}

	public static function isEnabledHandler()
	{
		return (self::$bHandlerDisallow >= 0);
	}

	public static function OnEpilogHandler(){
		global $APPLICATION;
		$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH.'/vendor/fonts/font-awesome5/css/all.min.css', true);

		$sPageTitle = $APPLICATION->GetTitle();
		$sBrowserTitle = $APPLICATION->GetPageProperty("title");
		$sBrowserTitle = str_replace(array('&#128276;'), array('🔔'), $sBrowserTitle);
		
		$sPageDescription = $APPLICATION->GetPageProperty("description");
		$sPageDescription = str_replace(array('&#128276;'), array('🔔'), $sPageDescription);
		
      	if (isset($_GET['PAGEN_1']) && (intval($_GET['PAGEN_1'])>0) && (!defined('ERROR_404')) ) {
			$sBrowserTitle = $sPageTitle . ' – купить под заказ {IN_CITY} в каталоге Е1, страница ' . intval($_GET['PAGEN_1']);
			$sPageDescription = $sPageTitle . ' – купить {IN_CITY} в каталоге мебельной фабрики Е1, страница ' . intval($_GET['PAGEN_1']);
      	}

      	$APPLICATION->SetPageProperty("title", $sBrowserTitle);
      	$APPLICATION->SetPageProperty("description", $sPageDescription);

		//COrwoFunctions::GetApplicationsStats();
		COrwoSubdomain::SetMeta();
		//добавление каноникал, если есть Get параметр 32946
		if (!empty($_REQUEST['PAGEN_1']) || !empty($_REQUEST['PAGEN_3']) || strpos($_SERVER["REQUEST_URI"], "?PAGEN_") !== false) {
            $APPLICATION->AddHeadString('<link href="' . (\CMain::IsHTTPS() ? "https://" : "http://") . $_SERVER["HTTP_HOST"] . $APPLICATION->GetCurPage(false) . '" rel="canonical" />', true);
        }
	}

	public static function OnPrologHandler(){
		COrwoSubdomain::GetCurrentCityValues();
		COrwoFunctions::GetCatalogPricesCodes();
		COrwoFunctions::GetDefOffersSort();
		
	}

	public static function AddCustomDeliveryServices(){
	    return new \Bitrix\Main\EventResult(
	        \Bitrix\Main\EventResult::SUCCESS,
	        array(
	            '\Sale\Handlers\Delivery\COwnCourierDelivery' => '/local/php_interface/include/classes/COwnCourierDelivery.php',
	            '\Sale\Handlers\Delivery\COwnCourierDeliverySetPriceRegion' => '/local/php_interface/include/classes/COwnCourierDeliverySetPriceRegion.php'//самовывоз, цена зависит от региона
	        )
	    );
	}

	public static function AddCustomDeliveryExtraServices()
	{
		return new \Bitrix\Main\EventResult(
			\Bitrix\Main\EventResult::SUCCESS,
			array(
				'\Bitrix\Sale\Delivery\ExtraServices\COwnCourierDeliveryExtraServices' => '/local/php_interface/include/classes/COwnCourierDeliveryExtraServices.php',
				'\Bitrix\Sale\Delivery\ExtraServices\COwnCourierDeliveryLiftExtraServices' => '/local/php_interface/include/classes/COwnCourierDeliveryLiftExtraServices.php',
			)
		);
	}

    public static function OnBeforeResultAddHandler($WEB_FORM_ID, &$arFields, &$arrVALUES) {
        global $APPLICATION;
        global $_FILES;
        $bContinue = true;
		if(CModule::IncludeModule("form")){
			$arFormResultFilter = array();
			if(!empty($arFields['USER_ID'])){
				$arFormResultFilter['USER_ID'] = $arFields['USER_ID'];
			}
			elseif(!empty($arFields['STAT_GUEST_ID'])){
				$arFormResultFilter['GUEST_ID'] = $arFields['STAT_GUEST_ID'];
			}

			if(!empty($arFormResultFilter)){
				$arFormResultFilter['TIME_CREATE_1'] = ConvertTimeStamp(strtotime('-24 hour'), "FULL");
			}

			if(!empty($arFormResultFilter)){
				$rsFormResult = \CFormResult::GetList($WEB_FORM_ID, ($by="s_timestamp"), ($order="desc"), $arFormResultFilter, $is_filtered, "N", 1);
				if ($arFormResult = $rsFormResult->Fetch())
				{
					$bContinue = false;
					$APPLICATION->ThrowException('Вы уже оставляли заявку в течение последних 24х часов. Ожидайте связи со специалистом!');
				}
			}
		}

        if(!empty($_FILES['FILE']) && $bContinue){
            $arFiles = [];
            if(is_array($_FILES['FILE']['name'])){
                foreach ($_FILES['FILE'] as $key => $val){
                    foreach ($val as $k => $v){
                    	if(empty($_FILES['FILE']['error'][$k])){
                        	$arFiles[$k][$key]= $v;
                    	}
                    }
                }
            }else{
                $arFiles = [$_FILES['FILE']];
            }
            unset($_FILES['FILE']);

	        $arAnswers = [];
	        $iLastAnswerId = '';
	        if($arQuestion = \CFormField::GetBySID('FILE', $WEB_FORM_ID)->Fetch()){
	            $by = 's_id';
	            $order = 'asc';
	            $filter = false;
	            if(intval($arQuestion['ID'])){
		            $rsAnswers = \CFormAnswer::GetList($arQuestion['ID'], $by, $order, ["FIELD_TYPE" => 'file'], $filter);
		            while ($rsAnswer = $rsAnswers->Fetch())
		            {
		                $arAnswers[] = 'form_file_' . $rsAnswer['ID'];
		                $iLastAnswerId = $rsAnswer['ID'];
		            }
	            }
	        }

	        if(!empty($arAnswers) && !empty($arFiles)){

	        	if(count($arFiles) > count($arAnswers) && !empty($iLastAnswerId)){
	        		
					if ($iNewAnswerId = \CFormAnswer::Copy($iLastAnswerId))
					{
					    $arAnswers[] = 'form_file_' . $iNewAnswerId;
					}
	        	}

                foreach ($arFiles as $f){
                    if($sAnswer = array_shift($arAnswers)){
                        $_FILES[$sAnswer] = $f;
                    }
                }
	        }
        }
        //$APPLICATION->ThrowException('Значение должно быть больше или равно 5!');
    }

	public static function OnAfterResultAddHandler($WEB_FORM_ID, $RESULT_ID){
		$bAdminSection = (defined('ADMIN_SECTION') && ADMIN_SECTION === true);
		if(!$bAdminSection)
		{
			global $_FILES;
			//check REVIEW form
			$rsForm = \CForm::GetByID($WEB_FORM_ID);
			$arForm = $rsForm->Fetch();
			if($arForm && $arForm['SID'] == 'GIVE_FEEDBACK')
			{
				\CForm::GetResultAnswerArray(
						$WEB_FORM_ID,
						$arrColumns,
						$arrAnswers,
						$arrAnswersVarname,
						array("RESULT_ID" => $RESULT_ID)
					);
				\CFormResult::GetDataByID($RESULT_ID, array(), $arResultFields, $arAnswers);

				if($arrAnswersVarname)
				{
					$el = new \CIBlockElement;

					$request = Context::getCurrent()->getRequest();
					$arFiles['FILE'] = array();
					$tmpFiles = $request->getFileList()->toArray(); 

					foreach ($tmpFiles as $fK => $arFile) {
						foreach ($arFile as $cK => $sFileCode) {
							$iFileCounter = 0;
							foreach ($sFileCode as $vK => $sFileVal) {
								$arFiles['FILE']['n' . $iFileCounter][$cK] = $sFileVal;
								$iFileCounter++;
							}
						}
					}

					$PROP = array(
						'EMAIL' => $arrAnswersVarname[$RESULT_ID]['EMAIL'][0]['USER_TEXT'],
						'POST' => $arrAnswersVarname[$RESULT_ID]['POST'][0]['USER_TEXT'],
						'PHONE' => $arrAnswersVarname[$RESULT_ID]['PHONE'][0]['USER_TEXT'],
						'RATING' => $arrAnswersVarname[$RESULT_ID]['RATING'][0]['USER_TEXT'],
                        'AGREEMENT' => $arrAnswersVarname[$RESULT_ID]['AGREEMENT'][0]['USER_TEXT'],
						'FILE' => $arFiles['FILE'],
					);

					$arLoadProductArray = array(
						"IBLOCK_ID" => \CMaxCache::$arIBlocks[SITE_ID]["aspro_max_content"]["aspro_max_add_review"][0],
  						"PROPERTY_VALUES"=> $PROP,
  						"ACTIVE"=> "N",
  						"NAME"=> $arrAnswersVarname[$RESULT_ID]['NAME'][0]['USER_TEXT'],
                        'AGREEMENT' => $arrAnswersVarname[$RESULT_ID]['AGREEMENT'][0]['USER_TEXT'],
  						"PREVIEW_TEXT"=> $arrAnswersVarname[$RESULT_ID]['REVIEW_TEXT'][0]['USER_TEXT'],
  						//"PREVIEW_PICTURE"=> \CFile::MakeFileArray($arrAnswersVarname[$RESULT_ID]['FILE'][0]['USER_FILE_ID']),
					);
					$aFilter = array("IBLOCK_ID" => \CMaxCache::$arIBlocks[SITE_ID]["aspro_max_content"]["aspro_max_add_review"][0], "CODE" => 'ne_prosmotrennye');
					$aSelect = array("IBLOCK_ID", "ID");
					$oDbRes = \CIBlockSection::GetList(array(), $aFilter, false, $aSelect);
					if ($aDbRes = $oDbRes->fetch()) {
						$arLoadProductArray['IBLOCK_SECTION_ID'] = $aDbRes['ID'];
					}
					$el->Add($arLoadProductArray);
				}
			}

			if(!empty($_FILES)){
				$arAnswerFiles = array();

				foreach ($_FILES as $key => $value) {
					$arAnswerFiles[str_replace('form_file_', '', $key)] = $value;
				}
				
				if(!empty($arAnswerFiles)){
					\CFormResult::SetField($RESULT_ID, 'FILE', $arAnswerFiles);
				}
			}

			//file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/classes/FILES.txt', print_r($_FILES, true));
		}
	}

	public static function OnSaleOrderSavedHandler(Event $event){
		global $arRegion, $arRegions;
		$bOrderUpdate = false;
		$order = $event->getParameter("ENTITY");
		$bIsNew = $event->getParameter("IS_NEW");
		$iPersonTypeId = $order->getPersonTypeId();
		$iDeliveryId = '';
		$collection = $order->getShipmentCollection()->getNotSystemItems();
		foreach ($collection as $shipment)
		{
		    $iDeliveryId = $shipment->getField('DELIVERY_ID');
		}


		$iStoreId = is_array($arRegion) && isset($arRegion['LIST_STORES']) ? end($arRegion['LIST_STORES']) : '';
		if (empty($iStoreId)) {
			$arNearestRegion = array();
			if (!empty($arRegion['PROPERTY_REGION_TAG_NEAREST_REGION_VALUE']) && !empty($arRegions[$arRegion['PROPERTY_REGION_TAG_NEAREST_REGION_VALUE']])) {
				$arNearestRegion = $arRegions[$arRegion['PROPERTY_REGION_TAG_NEAREST_REGION_VALUE']];
			}
			if (!empty($arNearestRegion) && $iStoreId === 'component' && !empty($arNearestRegion['LIST_STORES'])) {
                $arRegion['LIST_STORES'] = $arNearestRegion['LIST_STORES'];
            }
		}

		$iDeliveryWithStores = Option::get('e1.site.settings', 'E1_SS_SHOW_DELIVERY_WAREHOUSES');
		if($iDeliveryId == $iDeliveryWithStores && $bIsNew){
			$request = Context::getCurrent()->getRequest();
			$iStoreId = $request["BUYER_STORE"];
			$arStoresFilter = array();
			if(!empty($iStoreId)){
				$arStoresFilter['ID'] = $iStoreId;
			}

			$arStores = \COrwoFunctions::GetStores($arStoresFilter);
			
			if(!empty($arStores) && count($arStores) == 1){
				$arStores = $arStores[0];
				$dbOrderProp = \Bitrix\Sale\Property::getList([
				    'select' => ['*'], 
				    'filter' => [
				        'REL_DLV.ENTITY_ID' => $iDeliveryId,
				        'CODE' => 'E1_PICKUP_POINTS',
				        'PERSON_TYPE_ID' => $iPersonTypeId,
				        'ACTIVE' => 'Y'
				    ],
				    'runtime' => [
				        new \Bitrix\Main\Entity\ReferenceField(
				            'REL_DLV',
				            '\Bitrix\Sale\Internals\OrderPropsRelationTable',
				            array("=this.ID" => "ref.PROPERTY_ID", "=ref.ENTITY_TYPE" => new \Bitrix\Main\DB\SqlExpression('?', 'D')),
				            array("join_type"=>"left")
				        ),
				    ],
				    'group' => ['ID'],
				    'order' => ['ID' => 'DESC']
				]);

				if ($arOrderProp = $dbOrderProp->fetch())
				{
					if(!empty($arOrderProp['ID'])){
						$propertyCollection = $order->getPropertyCollection();
						$propertyValue = $propertyCollection->getItemByOrderPropertyId($arOrderProp['ID']);
						$sCity = '';
						if(!empty($arStores['PROPERTY_LINK_REGION_VALUE'])){
							$arRegionItemFilter = [
								'IBLOCK_ID' => \CMaxRegionality::getRegionIBlockID(),
							    'ACTIVE' => 'Y',
							    'ACTIVE_DATE' => 'Y',
							    'ID' => $arStores['PROPERTY_LINK_REGION_VALUE']
							];

							if(!empty($arFilter)){
								$arRegionItemFilter = array_merge($arRegionsItemFilter, $arFilter);
							}

							$arRegionItemSelect = ['ID', 'NAME', 'IBLOCK_ID', 'DETAIL_PAGE_URL', 'PREVIEW_TEXT', 'IBLOCK_SECTION_ID', 'PROPERTY_MAP', 'PROPERTY_PHONE', 'PROPERTY_SCHEDULE', 'PROPERTY_METRO', 'PROPERTY_EMAIL', 'PROPERTY_ADDRESS', 'PROPERTY_LINK_REGION', 'PROPERTY_STORES_LINK'];
							$arRegions = CMaxCache::CIblockElement_GetList(array("CACHE" => array("TAG" => CMaxCache::GetIBlockCacheTag(\CMaxRegionality::getRegionIBlockID()))), $arRegionItemFilter, false, false, $arRegionItemSelect);

							if(!empty($arRegions) && count($arRegions) == 1){
								$arRegions = $arRegions[0];
								$sCity = $arRegions['NAME'] . ', ';

								if(!empty($arRegions['PROPERTY_STORES_LINK_VALUE'])){
									$shipmentCollections = $order->getShipmentCollection();
									foreach ($shipmentCollections as $shipment)
									{
										$shipment->setStoreId($arRegions['PROPERTY_STORES_LINK_VALUE']);
										$bOrderUpdate = true;
									}
								}
							}
						}

						$sStoreInfo = '[' . $arStores['ID'] . '] ' . $arStores['~NAME'] . ', ' . $sCity . $arStores['~PROPERTY_ADDRESS_VALUE'];
						if(!empty($sStoreInfo)){
							$res = $propertyValue->setField('VALUE', $sStoreInfo);
							$bOrderUpdate = true;
							// if (!$res->isSuccess())
							// {
							//     var_dump($res->getErrorMessages());
							// }
						}
					}

				}
			}

		}

		// Start Запись склада в свойство
		if(!empty($iPersonTypeId) && !empty($iDeliveryId) && $bIsNew){
			$dbOrderProp = \Bitrix\Sale\Property::getList([
			    'select' => ['*'], 
			    'filter' => [
			        'REL_DLV.ENTITY_ID' => $iDeliveryId,
			        'CODE' => 'STORE_ID',
			        'PERSON_TYPE_ID' => $iPersonTypeId,
			        'ACTIVE' => 'Y'
			    ],
			    'runtime' => [
			        new \Bitrix\Main\Entity\ReferenceField(
			            'REL_DLV',
			            '\Bitrix\Sale\Internals\OrderPropsRelationTable',
			            array("=this.ID" => "ref.PROPERTY_ID", "=ref.ENTITY_TYPE" => new \Bitrix\Main\DB\SqlExpression('?', 'D')),
			            array("join_type"=>"left")
			        ),
			    ],
			    'group' => ['ID'],
			    'order' => ['ID' => 'DESC']
			]);

			if ($arOrderProp = $dbOrderProp->fetch())
			{
				if(!empty($arOrderProp['ID'])){
					$propertyCollection = $order->getPropertyCollection();
					$propertyValue = $propertyCollection->getItemByOrderPropertyId($arOrderProp['ID']);
					if (!is_null($propertyValue)) {
						$iStoreId = '';
						if(is_array($arRegion['LIST_STORES'])){
							$iStoreId = end($arRegion['LIST_STORES']);
						}
						if(!empty($iStoreId)){
							$propertyValue->setField('VALUE', $iStoreId);
							$bOrderUpdate = true;
						}
					}
				}
			}
		}
		// End Запись склада в свойство

		if($bIsNew){
			//file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/classes/arRegion.txt', print_r($arRegion, true));
			$sStoreId = '';
			if(is_array($arRegion['LIST_STORES'])){
				$sStoreId = end($arRegion['LIST_STORES']);
			}
			if(!empty($sStoreId)){
				$order->setField('STORE_ID', $sStoreId);
				$bOrderUpdate = true;
			}
		}

		$sUsePrePayment = Option::get('e1.site.settings', 'E1_SS_USE_PREPAYMENT', 'N');
		if($sUsePrePayment == 'Y' && $bIsNew){
			$paymentCollection = $order->getPaymentCollection();

			$iPrePaymentValue = intval(Option::get('e1.site.settings', 'E1_SS_PREPAYMENT_VALUE', 1000));
			$iPrePaymentPaymentId = intval(Option::get('e1.site.settings', 'E1_SS_PREPAYMENT_PAYMENT'));

			if(!empty($iPrePaymentPaymentId) && !empty($iPrePaymentValue)){
				$iOrderPrice = $order->getPrice();
				foreach ($paymentCollection as $payment)
				{
					$payment->setField('SUM', $iPrePaymentValue);
					$iOrderPrice -= $iPrePaymentValue;
				}

				$service = \Bitrix\Sale\PaySystem\Manager::getObjectById($iPrePaymentPaymentId);
				$newPayment = $paymentCollection->createItem($service);

				$newPayment->setField('SUM', $iOrderPrice);
				$bOrderUpdate = true;
				//$result = $order->save();
			}
		}

		if($bOrderUpdate){
			$order->save();
		}

	}

	static function saleOrderBeforeSaved(Event $event)
	{
		/** @var \Bitrix\Sale\Order $order */
		$order = $event->getParameter("ENTITY");

		// Получаем текущую коллекцию свойств заказа
		/** @var \Bitrix\Sale\PropertyValueCollection $orderProperties */
		$orderProperties = $order->getPropertyCollection();
		/** @var \Bitrix\Sale\PropertyValue $orderProperty */
		$sAddress = "";
		foreach ($orderProperties as $orderProperty) {
			$dataProp[$orderProperty->getField('CODE')] = $orderProperty->getField('VALUE');
		}
		if (!empty($dataProp['CITY_NEW']) && !empty($dataProp['STREET']) && $dataProp['HOUSE']) {
			$sAddress = $dataProp['CITY_NEW'] . ", " . $dataProp['STREET'] . ", " . $dataProp['HOUSE'] . (!empty($dataProp['HOUSING']) ? " , корпус" . $dataProp['HOUSING']: " "). (!empty($dataProp['APARTMENT']) ? " , квартира " . $dataProp['APARTMENT'] : "");
		} elseif (!empty($dataProp['ADDRESS'])) {
			$sAddress = $dataProp['ADDRESS'];
		}
		if (!empty($sAddress)) {
			foreach ($orderProperties as $orderProperty) {
				if ($orderProperty->getField('CODE') == "ADDRESS") {
					$orderProperty->setValue($sAddress);
				}
			}
		}

	}

	public static function OnSaleComponentOrderResultPreparedHandler($order, &$arUserResult, $request, &$arParams, &$arResult){
		global $arDeliveryRegion;

		$arRegions = \COrwoFunctions::GetWareHouses();

		if(!empty($arRegions)){
			foreach ($arRegions as $key => $value) {
				//$arCoords = explode(',', $value['PROPERTY_MAP_VALUE']);
				$arResult['JS_DATA']['STORE_LIST'][$value['ID']] = [
                    'ID' => $value['ID'],
                    'TITLE' => $value['TITLE'],
                    'ADDRESS' => $value['ADDRESS'],
                    'DESCRIPTION' => $value['DESCRIPTION'],
                    'IMAGE_ID' => '',
                    'PHONE' => $value['PHONE'],
                    'SCHEDULE' => $value['SCHEDULE'],
                    'GPS_N' => $value['GPS_N'],
                    'GPS_S' => $value['GPS_S'],
                    'ISSUING_CENTER' => 'Y',
                    'SITE_ID' => "S1",
				];
			}
		}

		$iDeliveryKey = 0;
		foreach ($arResult['DELIVERY'] as &$delivery)
		{
			if (!empty($delivery['EXTRA_SERVICES']))
			{
				//$arExtraService = [];
				foreach ($delivery['EXTRA_SERVICES'] as $extraServiceId => $extraService)
				{
					if ($extraService->canUserEditValue())
					{

						$sExtraServiceCode = $extraService->getCode();
						$arExtraServiceParams = $extraService->getParams();
						if("LIFTING_COST_" . $arDeliveryRegion['DELIVERY_REGION']['VALUE_ENUM_ID'] == $sExtraServiceCode || "LIFT_PRICE" == $sExtraServiceCode){
							$arResult['JS_DATA']['DELIVERY'][$delivery['ID']]['EXTRA_SERVICES'][$iDeliveryKey]['PARAMS'] = $arExtraServiceParams;
						}
						else{
							unset($arResult['JS_DATA']['DELIVERY'][$delivery['ID']]['EXTRA_SERVICES'][$iDeliveryKey]);
						}

						$iDeliveryKey++;
					}
				}
			}
		}
	}

	public static function OnPriceChangeHandler($arg1, $arg2 = false)
	{

		if (!self::isEnabledHandler())
			return;

		self::disableHandler();

	    if(\Bitrix\Main\Loader::includeModule("catalog"))
	    {
			if (is_array($arg2) && $arg2["PRODUCT_ID"] > 0) {
				$ELEMENT_ID = $arg2["PRODUCT_ID"];
			} elseif (is_array($arg1) && $arg1["ID"] > 0) {
				//$arElementFields = \CIBlockElement::GetByID($arg1["ID"])->GetNext();
				$ELEMENT_ID = $arg1["ID"];
			}
	    	if(!empty($ELEMENT_ID)){
	    		$arResult = \COrwoFunctions::GetPriceByOfferId($ELEMENT_ID);
				if(!empty($arResult['OFFER']['MIN_PRICE']['DISCOUNT_VALUE'])){
					$arPriceUpdateFields = array(
					    "PRODUCT_ID" => $arResult['OFFER']['ID'],
					    "CATALOG_GROUP_ID" => $arResult['BASE_PRICE_ID'],
					    "PRICE" => $arResult['OFFER']['MIN_PRICE']['DISCOUNT_VALUE'],
					    "CURRENCY" => $arResult['OFFER']['MIN_PRICE']['CURRENCY']
					);

					$rsPriceUpdate = \Bitrix\Catalog\Model\Price::getList([
					    "filter" => [
					        "PRODUCT_ID" => $arResult['OFFER']['ID'],
					        "CATALOG_GROUP_ID" => $arResult['BASE_PRICE_ID']
					    ]
					]);

					if ($arPriceUpdate = $rsPriceUpdate->fetch()) {
					    $obPriceUpdateResult = \Bitrix\Catalog\Model\Price::update($arPriceUpdate["ID"], $arPriceUpdateFields);
					    if ($obPriceUpdateResult->isSuccess()) {
					        self::$iPriceDontDelete = $obPriceUpdateResult->getId();
					    } 
					} else {
					    $obPriceUpdateResult = \Bitrix\Catalog\Model\Price::add($arPriceUpdateFields);
					    if ($obPriceUpdateResult->isSuccess()) {
					        self::$iPriceDontDelete = $obPriceUpdateResult->getId();
					        //$result->getErrorMessages()
					    }
					}
				}
	    	}
	    }
	    self::enableHandler();
	}

	public static function OnBeforeProductPriceDeleteHandler($id, &$arr = null){
		if(!in_array(self::$iPriceDontDelete, $arr)) {
			$arr[] = self::$iPriceDontDelete;
		}
		//file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/classes/arr.txt', print_r($arr, true));
		return true;
	}

	public static function OnGetOptimalPriceHandler($productID, $quantity = 1, $arUserGroups = array(), $renewal = "N", $arPrices = array(), $siteID = false, $arDiscountCoupons = false)
	{
		if (!self::isEnabledHandler())
			return;

		self::disableHandler();
    	if(!empty($productID)){
    		$arResult = \COrwoFunctions::GetPriceByOfferId($productID);
    		self::enableHandler();
			return array(
				'PRICE' => array(
					"ID" => $arResult['OFFER']['ID'],
					//'CATALOG_GROUP_ID' => $catalog_group_id,
					'PRICE' => $arResult['OFFER']['MIN_PRICE']['VALUE'],
					'CURRENCY' => $arResult['OFFER']['MIN_PRICE']['CURRENCY'],
					//'ELEMENT_IBLOCK_ID' => $productID,
					//'VAT_INCLUDED' => "Y",
				),
				'DISCOUNT' => array(
					'PRICE' => $arResult['OFFER']['MIN_PRICE']['DISCOUNT_VALUE'],
					'CURRENCY' => $arResult['OFFER']['MIN_PRICE']['CURRENCY'],
				),
			);
    	}

    	self::enableHandler();
		return true;
	}

	public static function OnSaleBasketItemRefreshDataHandler(\Bitrix\Main\Event $event)
	{

		if (!self::isEnabledHandler())
			return;

		self::disableHandler();

	    $obBasketItem = $event->getParameter("ENTITY");
	    $arBasketItemValues = $event->getParameter("VALUES");

		// $item->getProductId();  // ID товара
		// $item->getPrice();      // Цена за единицу
		// $item->getQuantity();   // Количество
		// $item->getFinalPrice(); // Сумма
		// $item->getWeight();     // Вес
		// $item->getField('NAME');// Любое поле товара в корзине
	    $iProductId = $obBasketItem->getProductId();
	    $iProductQuantity = $obBasketItem->getQuantity();
	    $ibBasketItemId = $obBasketItem->getId();;

	    if(!empty($iProductId) && !empty($iProductQuantity) && !empty($ibBasketItemId)){
    		$arResult = \COrwoFunctions::GetPriceByOfferId($iProductId);

    		//'PRICE' => $arResult['OFFER']['MIN_PRICE']['DISCOUNT_VALUE']
    		file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/classes/entity.txt', print_r($arResult, true));

    		if(!empty($arResult['OFFER']['MIN_PRICE']['DISCOUNT_VALUE'])){
    			$obBasketItem->markFieldCustom('PRICE');
				$obBasketItem->setFields([
				    //'QUANTITY' => $quantity,
				    'PRICE' => $arResult['OFFER']['MIN_PRICE']['DISCOUNT_VALUE'],
				]);
				$obBasketItem->save();
			}
	    }

	    self::enableHandler();
	}


	public static function OnSaleBasketItemRefreshDataNoCustomPriceHandler(\Bitrix\Main\Event $event)
	{

		if (!self::isEnabledHandler())
			return;

		self::disableHandler();

	    $obBasketItem = $event->getParameter("ENTITY");
	    $arBasketItemValues = $event->getParameter("VALUES");

	    $iProductId = $obBasketItem->getProductId();
	    $iProductQuantity = $obBasketItem->getQuantity();
	    $ibBasketItemId = $obBasketItem->getId();;

	    if(!empty($iProductId) && !empty($iProductQuantity) && !empty($ibBasketItemId)){
    		$arResult = \COrwoFunctions::GetPriceByOfferId($iProductId);
            $fakeDiscount =  \E_1\Prices::getDiscountById($iProductId);
            $realDiscount = $obBasketItem->getField('DISCOUNT_PRICE_PERCENT');
            if ($realDiscount){
                $totalDiscount = (int)($fakeDiscount + ((100 - $fakeDiscount) * ($realDiscount / 100)));
            }else{
                $totalDiscount = (int)$fakeDiscount;
            }
            $basketItemPriceReal = (!empty($arResult['OFFER']['MIN_PRICE']["DISCOUNT_VALUE"])) ? floatval($arResult['OFFER']['MIN_PRICE']["DISCOUNT_VALUE"]) : floatval($arResult['ELEMENT']['MIN_PRICE']["DISCOUNT_VALUE"]);
            //подставляем для расчета скидки нужную цену (актуально для оптим)
            if (!empty($basketItemPriceReal) && $arResult['ELEMENT']["PROPERTIES"]["SERIYA_SHKAFA"]["VALUE"] === 'Оптим') {
                $intProcentSale = (!empty($arResult['OFFER']['MIN_PRICE']["PROCENT_SALE"])) ? floatval($arResult['OFFER']['MIN_PRICE']["PROCENT_SALE"]) : floatval($arResult['ELEMENT']['MIN_PRICE']["PROCENT_SALE"]);
                $totalDiscountReal = $intProcentSale;
                //цена изначальная
                $basePrice = $basketItemPriceReal* (100/(100-$totalDiscountReal));
                $priceSale = $basePrice * ((100-($totalDiscountReal + $totalDiscount))/100);
				global $USER;
				$arDiscounts = CCatalogDiscount::GetDiscountByProduct(
					$iProductId,
					$USER->GetUserGroupArray(),
					"N",
					[],
					SITE_ID
				);
				$obBasketItem->setFields([
					'PRICE' => $priceSale,
				]);
			
				$obBasketItem->save();
			}else {
				if(!empty($arResult['OFFER']['MIN_PRICE']['DISCOUNT_VALUE'])){
					$obBasketItem->setFields([
						'PRICE' => $arResult['OFFER']['MIN_PRICE']['DISCOUNT_VALUE'],
					]);
					$obBasketItem->save();
				}

			}
	    }

	    self::enableHandler();
	}

	public static function OnSaleBasketBeforeSavedHandler(\Bitrix\Main\Event $event)
	{

		if (!self::isEnabledHandler())
			return;

		self::disableHandler();

		$obBasket = $event->getParameter("ENTITY");

		$obBasketItems = $obBasket->getBasketItems();

		foreach ($obBasketItems as $obBasketItem) {
		    $iProductId = $obBasketItem->getProductId();
		    $iProductQuantity = $obBasketItem->getQuantity();
		    $ibBasketItemId = $obBasketItem->getId();

		    if(!empty($iProductId) && !empty($iProductQuantity) && !empty($ibBasketItemId)){
	    		$arResult = \COrwoFunctions::GetPriceByOfferId($iProductId);

	    		//'PRICE' => $arResult['OFFER']['MIN_PRICE']['DISCOUNT_VALUE']
	    		file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/classes/entity.txt', print_r($arResult, true));
				#временно закоментил, завышенные цены в корзине, ищем причины
	    		if(!empty($arResult['OFFER']['MIN_PRICE']['DISCOUNT_VALUE'])){
	    			$obBasketItem->markFieldCustom('PRICE');
					$obBasketItem->setFields([
					    //'QUANTITY' => $quantity,
					    'PRICE' => $arResult['OFFER']['MIN_PRICE']['DISCOUNT_VALUE'],
					]);
					$obBasketItem->save();
				}
		    }
		}

		self::enableHandler();
	}

	//метод подправляет цены для оптим товаров при сохранении заказа
	public static function OnSaleBasketBeforeSavedNoCustomPriceHandler(\Bitrix\Main\Event $event)
	{

		if (!self::isEnabledHandler())
			return;

		self::disableHandler();

		$obBasket = $event->getParameter("ENTITY");

		$obBasketItems = $obBasket->getBasketItems();

		foreach ($obBasketItems as $obBasketItem) {
		    $iProductId = $obBasketItem->getProductId();
		    $iProductQuantity = $obBasketItem->getQuantity();
		    $ibBasketItemId = $obBasketItem->getId();

		    if(!empty($iProductId) && !empty($iProductQuantity) && !empty($ibBasketItemId)){
	    		$arResult = \COrwoFunctions::GetPriceByOfferId($iProductId);
				$fakeDiscount =  \E_1\Prices::getDiscountById($iProductId);
				$realDiscount = $obBasketItem->getField('DISCOUNT_PRICE_PERCENT');
				if ($realDiscount){
					$totalDiscount = (int)($fakeDiscount + ((100 - $fakeDiscount) * ($realDiscount / 100)));
				}else{
					$totalDiscount = (int)$fakeDiscount;
				}
				global $USER;
				$arDiscounts = \CCatalogDiscount::GetDiscountByProduct(
					$iProductId,
					$USER->GetUserGroupArray(),
					"N",
					[],
					SITE_ID
				) ?: [];
				//получаем id скидки , $iDiscountOptim - это общая переменая 
				$iDiscountOptim = $iDiscountOptimSumm = 0;
				$idDiscounts = array_keys($arDiscounts);
				foreach ($arDiscounts as $key => $arDiscount) {
					if (empty($arDiscount['COUPON']) && $arDiscount['ACTIVE'] === 'Y'){
						$iDiscountOptim += $arDiscount['VALUE'];
					}else {
						$iDiscountOptimSumm += $arDiscount['VALUE'];
					}
				}
				$basketItemPriceReal = (!empty($arResult['OFFER']['MIN_PRICE']["DISCOUNT_VALUE"])) ? floatval($arResult['OFFER']['MIN_PRICE']["DISCOUNT_VALUE"]) : floatval($arResult['ELEMENT']['MIN_PRICE']["DISCOUNT_VALUE"]);
				//подставляем для расчета скидки нужную цену (актуально для оптим)
				if (!empty($basketItemPriceReal) && $arResult['ELEMENT']["PROPERTIES"]["SERIYA_SHKAFA"]["VALUE"] === 'Оптим') {
					$intProcentSale = (!empty($arResult['OFFER']['MIN_PRICE']["PROCENT_SALE"])) ? floatval($arResult['OFFER']['MIN_PRICE']["PROCENT_SALE"]) : floatval($arResult['ELEMENT']['MIN_PRICE']["PROCENT_SALE"]);
					$totalDiscountReal = $intProcentSale;
					
					//цена изначальная
					$basePrice = $basketItemPriceReal* (100/(100-$totalDiscountReal));
					if ($iDiscountOptim > 0) {
						$priceSale = $basePrice * ((100-($totalDiscountReal + $totalDiscount))/100);
						//магия для оптимы, прибавка скидки,которая будет уменьшена правилом корзины
						$priceSale = $priceSale* (100/(100-$iDiscountOptim));
					} else {
						$priceSale = $basePrice * ((100-($totalDiscountReal + $totalDiscount))/100);
					}
					//если у нас задана в инфоблоке отключение декоративной скидки, то учитываем это
                    if ($arResult['OFFER']['PROPERTIES']['OFF_DECOR_DISCOUNT']['VALUE'] === 'Y') {
                        $priceSale = $basePrice * ((100-($totalDiscountReal))/100);
					}

						$obBasketItem->setFields([
							'PRICE' => $priceSale,
							'BASE_PRICE' => $priceSale,
						]);

					$obBasketItem->save();
				}else {
					if(!empty($arResult['OFFER']['MIN_PRICE']['DISCOUNT_VALUE'])){
						$basePrice = $basketItemPriceReal* (100/(100-$totalDiscount));
						$oldPrice = $basePrice;
						if ($iDiscountOptim > 0) {
							$priceSale = $basePrice * ((100-($totalDiscount))/100);
							// прибавка скидки,которая будет уменьшена правилом корзины
							$priceSale = $priceSale* (100/(100-$iDiscountOptim));
						} else {
							$priceSale = $basePrice * ((100-($totalDiscount))/100);
						}
						if ($iDiscountOptim > 0 && $iDiscountOptimSumm > 0) {
							//здесь цена без купона, он применяется самим Битрикс
							$sPriceForCoupon = $basePrice / ((100-$iDiscountOptimSumm)/100);
							$sPriceForCoupon = $sPriceForCoupon / ((100-$iDiscountOptim)/100);
							$obBasketItem->setFields([
								'PRICE' => $sPriceForCoupon,
								'BASE_PRICE' => $sPriceForCoupon,
							]);
						} else {
							//корректировка для некоторых случаев, Прайм 5% Aprel
							if ($iDiscountOptimSumm > 0) {
								$priceSale = round($basePrice / ((100 - $iDiscountOptimSumm)/100));
							}
							$obBasketItem->setFields([
								'PRICE' => $priceSale,//$arResult['OFFER']['MIN_PRICE']['DISCOUNT_VALUE'],
								'BASE_PRICE' => $priceSale,//$arResult['OFFER']['MIN_PRICE']['DISCOUNT_VALUE'],
							]);
						}
						$obBasketItem->save();
					}
				}
		    }
		}

		self::enableHandler();
	}

	public static function OnSuccessCatalogImport1CHandler($arParams, $arFields){
		$dbAgent = \CAgent::GetList(array("ID" => "DESC"), array("ACTIVE" => "N", "NAME" => "\COrwoFunctions::CatalogDataChangeAgent();"));
		if($arAgent = $dbAgent->GetNext()){
		    if(!empty($arAgent['ID']) && $arAgent['NAME'] == '\COrwoFunctions::CatalogDataChangeAgent();'){

				$sCurHour = intval(date("H", strtotime("now"))) + 1;
				$sCurMinute = intval(date("i"));
		    	if($sCurMinute != 0){
		    		$sCurHour++;
		    	}
				if(($sCurHour % 2) != 0){
					$sCurHour++;
				}
		    	$objDateTime = new DateTime();
		    	$sCurHour = 21;
				$sNextExec = $objDateTime->format("d.m.Y ") . $sCurHour . ':00:00';

		    	\CAgent::Update($arAgent['ID'], array("ACTIVE" => "Y", "NEXT_EXEC" => $sNextExec));
		    }
		}
	}

	public static function OnBuildGlobalMenuHandler(&$adminMenu, &$moduleMenu){
		global $APPLICATION;
		//Asset::getInstance()->addCss("/local/php_interface/include/classes/site_settings/style.css");
		$APPLICATION->SetAdditionalCss('/local/php_interface/include/classes/site_settings/style.css');
		$moduleMenu[] = array(
			"parent_menu" => "global_menu_settings", // в раздел "Сервис"
			"section" => 'Настройки для сайта "Мебельная компания Е1"',
			"sort"        => 100,                    // сортировка пункта меню - поднимем повыше
			"url"         => "e1_site_settings.php?mid=e1.site.settings&lang=".LANG,  // ссылка на пункте меню - тут как раз и пишите адрес вашего файла, созданного в /bitrix/admin/
			"text"        => 'Настройки для сайта "Мебельная компания Е1"',
			"title"       => 'Настройки для сайта "Мебельная компания Е1"',
			"icon"        => "e1_site_settings_menu_icon", // малая иконка
			"page_icon"   => "e1_site_settings_page_icon", // большая иконка
			"items_id"    => "e1_site_settings",  // идентификатор ветви
			"items"       => array()          // остальные уровни меню
		);
	}
}