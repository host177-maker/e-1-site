<?
use Bitrix\Sale;
use Bitrix\Sale\BusinessValue;
use Bitrix\Sale\BusinessValueConsumer1C;
use Bitrix\Sale\Exchange\Logger\Exchange;

class COrwo1CSaleExport extends CSaleExport
{

	static function getPayment($arOrder)
	{
		$result = array();
		$entity = static::getPaymentTable();

		$PaymentParam['select'] =
			array(
				"ID",
				"ID_1C",
				"PAID",
				"DATE_BILL",
				"ORDER_ID",
				"CURRENCY",
				"SUM",
				"COMMENTS",
				"DATE_PAID",
				"PAY_SYSTEM_ID",
				"PAY_SYSTEM_NAME",
				"IS_RETURN",
				"PAY_RETURN_COMMENT",
				"PAY_VOUCHER_NUM",
				"PAY_VOUCHER_DATE",
				"XML_ID"
			);


		$PaymentParam['filter']['ORDER_ID'] = $arOrder['ID'];
		$PaymentParam['filter']['!=EXTERNAL_PAYMENT'] = 'F';
		$innerPS = 0;
		$limit = 0;
		$inc = 0;

		if(self::getVersionSchema() < self::PARTIAL_VERSION)
		{
			$innerPS = \Bitrix\Sale\PaySystem\Manager::getInnerPaySystemId();
			$limit = 100;
		}

		$resPayment = $entity::getList($PaymentParam);

		while($arPayment = $resPayment->fetch())
		{
			foreach($arPayment as $field=>$value)
			{
			    if(self::isFormattedDateFields('Payment', $field))
			    {
			        $arPayment[$field] = self::getFormatDate($value);
			    }
			}

            $result['paySystems'][$arPayment['PAY_SYSTEM_ID']] = $arPayment['PAY_SYSTEM_NAME'];

			if($innerPS == 0 || $innerPS!=$arPayment['PAY_SYSTEM_ID'])
			{
			    if($limit == 0 || $inc < $limit)
			        $result['payment'][] = $arPayment;

			    $inc++;
			}
		}
		return $result;
	}

    static function ExportOrders2Xml($arFilter = Array(), $nTopCount = 0, $currency = "", $crmMode = false, $time_limit = 0, $version = false, $arOptions = Array())
	{
		$lastOrderPrefix = '';
		$arCharSets = array();
		$lastDateUpdateOrders = array();
		$entityMarker = static::getEntityMarker();

	    self::setVersionSchema($version);
		self::setCrmMode($crmMode);
		self::setCurrencySchema($currency);

		$count = false;
		if(intval($nTopCount) > 0)
			$count = Array("nTopCount" => $nTopCount);

		$end_time = self::getEndTime($time_limit);

		if(intval($time_limit) > 0)
		{
			if(self::$crmMode)
			{
				$lastOrderPrefix = md5(serialize($arFilter));
				if(!empty($_SESSION["BX_CML2_EXPORT"][$lastOrderPrefix]) && intval($nTopCount) > 0)
					$count["nTopCount"] = $count["nTopCount"]+count($_SESSION["BX_CML2_EXPORT"][$lastOrderPrefix]);
			}
		}

		if(!self::$crmMode)
        {
			$arFilter = static::prepareFilter($arFilter);
			$timeUpdate = isset($arFilter[">=DATE_UPDATE"])? $arFilter[">=DATE_UPDATE"]:'';
            $lastDateUpdateOrders = static::getLastOrderExported($timeUpdate);
        }

		self::$arResultStat = array(
			"ORDERS" => 0,
			"CONTACTS" => 0,
			"COMPANIES" => 0,
		);

		$bExportFromCrm = self::isExportFromCRM($arOptions);

		$arStore = self::getCatalogStore();
		$arMeasures = self::getCatalogMeasure();
		self::setCatalogMeasure($arMeasures);
		$arAgent = self::getSaleExport();

		if (self::$crmMode)
		{
			self::setXmlEncoding("UTF-8");
			$arCharSets = self::getSite();
		}

		echo self::getXmlRootName();?>

<<?=CSaleExport::getTagName("SALE_EXPORT_COM_INFORMATION")?> <?=self::getCmrXmlRootNameParams()?>><?

		$arOrder = array("DATE_UPDATE" => "ASC", "ID"=>"ASC");

		$arSelect = array(
			"ID", "LID", "PERSON_TYPE_ID", "PAYED", "DATE_PAYED", "EMP_PAYED_ID", "CANCELED", "DATE_CANCELED",
			"EMP_CANCELED_ID", "REASON_CANCELED", "STATUS_ID", "DATE_STATUS", "PAY_VOUCHER_NUM", "PAY_VOUCHER_DATE", "EMP_STATUS_ID",
			"PRICE_DELIVERY", "ALLOW_DELIVERY", "DATE_ALLOW_DELIVERY", "EMP_ALLOW_DELIVERY_ID", "PRICE", "CURRENCY", "DISCOUNT_VALUE",
			"SUM_PAID", "USER_ID", "PAY_SYSTEM_ID", "DELIVERY_ID", "DATE_INSERT", "DATE_INSERT_FORMAT", "DATE_UPDATE", "USER_DESCRIPTION",
			"ADDITIONAL_INFO",
			"COMMENTS", "TAX_VALUE", "STAT_GID", "RECURRING_ID", "ACCOUNT_NUMBER", "SUM_PAID", "DELIVERY_DOC_DATE", "DELIVERY_DOC_NUM", "TRACKING_NUMBER", "STORE_ID",
			"ID_1C", "VERSION",
			"USER.XML_ID", "USER.TIMESTAMP_X"
		);

		$bCrmModuleIncluded = false;
		if ($bExportFromCrm)
		{
			$arSelect[] = "UF_COMPANY_ID";
			$arSelect[] = "UF_CONTACT_ID";
			if (IsModuleInstalled("crm") && CModule::IncludeModule("crm"))
				$bCrmModuleIncluded = true;
		}

		$arFilter['RUNNING'] = 'N';

		$filter = array(
			'select' => $arSelect,
			'filter' => $arFilter,
			'order'  => $arOrder,
			'limit'  => $count["nTopCount"]
		);

		if (!empty($arOptions['RUNTIME']) && is_array($arOptions['RUNTIME']))
		{
			$filter['runtime'] = $arOptions['RUNTIME'];
		}

		$entity = static::getParentEntityTable();

        $dbOrderList = $entity::getList($filter);

		while($arOrder = $dbOrderList->Fetch())
		{
		    if(!self::$crmMode && (new Exchange(Sale\Exchange\Logger\ProviderType::ONEC_NAME))->isEffected($arOrder, $lastDateUpdateOrders))
            {
				continue;
            }

		    static::$documentsToLog = array();
			$contentToLog = '';

		    $order = static::load($arOrder['ID']);
			$arOrder['DATE_STATUS'] = $arOrder['DATE_STATUS']->toString();
		    $arOrder['DATE_INSERT'] = $arOrder['DATE_INSERT']->toString();
		    $arOrder['DATE_UPDATE'] = $arOrder['DATE_UPDATE']->toString();

			foreach($arOrder as $field=>$value)
			{
			    if(self::isFormattedDateFields('Order', $field))
			    {
			        $arOrder[$field] = self::getFormatDate($value);
			    }
			}

			if (self::$crmMode)
			{
				if(self::getVersionSchema() > self::DEFAULT_VERSION && is_array($_SESSION["BX_CML2_EXPORT"][$lastOrderPrefix]) && in_array($arOrder["ID"], $_SESSION["BX_CML2_EXPORT"][$lastOrderPrefix]) && empty($arFilter["ID"]))
					continue;
				ob_start();
			}

			self::$arResultStat["ORDERS"]++;

			$agentParams = (array_key_exists($arOrder["PERSON_TYPE_ID"], $arAgent) ? $arAgent[$arOrder["PERSON_TYPE_ID"]] : array() );

            $arResultPayment = self::getPayment($arOrder);
            $paySystems = $arResultPayment['paySystems'];
            $arPayment = $arResultPayment['payment'];

			$arResultShipment = self::getShipment($arOrder);
			$arShipment = $arResultShipment['shipment'];
			$delivery = $arResultShipment['deliveryServices'];

			self::setDeliveryAddress('');
			self::setSiteNameByOrder($arOrder);

			$arProp = self::prepareSaleProperty($arOrder, $bExportFromCrm, $bCrmModuleIncluded, $paySystems, $delivery, $locationStreetPropertyValue, $order);
			$agent = self::prepareSalePropertyRekv($order, $agentParams, $arProp, $locationStreetPropertyValue);

			$arOrderTax = CSaleExport::getOrderTax($order);
			$xmlResult['OrderTax'] = self::getXMLOrderTax($arOrderTax);
			self::setOrderSumTaxMoney(self::getOrderSumTaxMoney($arOrderTax));

			$xmlResult['Contragents'] = self::getXmlContragents($arOrder, $arProp, $agent, $bExportFromCrm ? array("EXPORT_FROM_CRM" => "Y") : array());
			$xmlResult['OrderDiscount'] = self::getXmlOrderDiscount($arOrder);
			$xmlResult['SaleStoreList'] = $arStore;
			$xmlResult['ShipmentsStoreList'] = self::getShipmentsStoreList($order);
			// вроде не то
			//echo self::getXmlSaleStoreBasket($arOrder,$arStore);
			$basketItems = self::getXmlBasketItems('Order', $arOrder, array('ORDER_ID'=>$arOrder['ID']), array(), $arShipment);

            $numberItems = array();
            foreach($basketItems['result'] as $basketItem)
            {
                $number = self::getNumberBasketPosition($basketItem["ID"]);

                if(in_array($number, $numberItems))
                {
					$r = new \Bitrix\Sale\Result();
					$r->addWarning(new \Bitrix\Main\Error(GetMessage("SALE_EXPORT_REASON_MARKED_BASKET_PROPERTY").'1C_Exchange:Order.export.basket.properties', 'SALE_EXPORT_REASON_MARKED_BASKET_PROPERTY'));
					$entityMarker::addMarker($order, $order, $r);
					$order->setField('MARKED','Y');
					$order->setField('DATE_UPDATE',null);
					$order->save();
                    break;
                }
                else
                {
                    $numberItems[] = $number;
                }
            }

			$xmlResult['BasketItems'] = $basketItems['outputXML'];
			$xmlResult['SaleProperties'] = self::getXmlSaleProperties($arOrder, $arShipment, $arPayment, $agent, $agentParams, $bExportFromCrm);
			$xmlResult['RekvProperties'] = self::getXmlRekvProperties($agent, $agentParams);


			if(self::getVersionSchema() >= self::CONTAINER_VERSION)
            {
                ob_start();
				echo '<'.CSaleExport::getTagName("SALE_EXPORT_CONTAINER").'>';
            }

			self::OutputXmlDocument('Order', $xmlResult, $arOrder);

			if(self::getVersionSchema() >= self::PARTIAL_VERSION)
			{
				self::OutputXmlDocumentsByType('Payment',$xmlResult, $arOrder, $arPayment, $order, $agentParams, $arProp, $locationStreetPropertyValue);
				self::OutputXmlDocumentsByType('Shipment',$xmlResult, $arOrder, $arShipment, $order, $agentParams, $arProp, $locationStreetPropertyValue);
				self::OutputXmlDocumentRemove('Shipment',$arOrder);
			}

			if(self::getVersionSchema() >= self::CONTAINER_VERSION)
			{
				echo '</'.CSaleExport::getTagName("SALE_EXPORT_CONTAINER").'>';
				$contentToLog = ob_get_contents();
				ob_end_clean();
				echo $contentToLog;
			}

			if (self::$crmMode)
			{
				$c = ob_get_clean();
				$c = CharsetConverter::ConvertCharset($c, $arCharSets[$arOrder["LID"]], "utf-8");
				echo $c;
				$_SESSION["BX_CML2_EXPORT"][$lastOrderPrefix][] = $arOrder["ID"];
			}
			else
			{
				static::saveExportParams($arOrder);
			}

			ksort(static::$documentsToLog);

			foreach (static::$documentsToLog as $entityTypeId=>$documentsToLog)
			{
				foreach ($documentsToLog as $documentToLog)
				{
					$fieldToLog = $documentToLog;
					$fieldToLog['ENTITY_TYPE_ID'] = $entityTypeId;
					if(self::getVersionSchema() >= self::CONTAINER_VERSION)
					{
						if($entityTypeId == \Bitrix\Sale\Exchange\EntityType::ORDER )
							$fieldToLog['MESSAGE'] = $contentToLog;
					}
					static::log($fieldToLog);
				}
			}

			if(self::checkTimeIsOver($time_limit, $end_time))
			{
				break;
			}
		}
		?>

	</<?=CSaleExport::getTagName("SALE_EXPORT_COM_INFORMATION")?>><?

		return self::$arResultStat;
	}

	static function OutputXmlDocument($typeDocument,$xmlResult, $document=array())
	{
		global $DB;
		?>

		<?
			// Start Получение склада
			$iStoreId = '';
			$order = static::load($document["ID"]);
			$iPersonTypeId = $order->getPersonTypeId();
			$iDeliveryId = '';
			$collection = $order->getShipmentCollection()->getNotSystemItems();
			foreach ($collection as $shipment)
			{
			    $iDeliveryId = $shipment->getField('DELIVERY_ID');
			}

			if(!empty($iPersonTypeId) && !empty($iDeliveryId)){
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
							$iStoreId = $propertyValue->getField('VALUE');
							if(!empty($iStoreId) && empty($xmlResult['ShipmentsStoreList'])){
								$xmlResult['ShipmentsStoreList'][] = $iStoreId;
							}
						}
					}
				}
			}

			if(empty($iStoreId)){
				$iStoreId = $order->getField('STORE_ID');
				if(!empty($iStoreId) && empty($xmlResult['ShipmentsStoreList'])){
					$xmlResult['ShipmentsStoreList'][] = $iStoreId;
				}
			}
			// End Получение склада
		?>

		<?ob_start();?>
	<<?=CSaleExport::getTagName("SALE_EXPORT_DOCUMENT")?>><?
		switch($typeDocument)
		{
			case 'Order':
		?>
		<<?=CSaleExport::getTagName("SALE_EXPORT_ID")?>><?=$document["ID"]?></<?=CSaleExport::getTagName("SALE_EXPORT_ID")?>>
		<<?=CSaleExport::getTagName("SALE_EXPORT_NUMBER")?>><?=self::getAccountNumberShopPrefix();?><?=$document["ACCOUNT_NUMBER"]?></<?=CSaleExport::getTagName("SALE_EXPORT_NUMBER")?>>
		<<?=CSaleExport::getTagName("SALE_EXPORT_DATE")?>><?=$DB->FormatDate($document["DATE_INSERT_FORMAT"], CSite::GetDateFormat("FULL"), "YYYY-MM-DD")?></<?=CSaleExport::getTagName("SALE_EXPORT_DATE")?>>
		<<?=CSaleExport::getTagName("SALE_EXPORT_HOZ_OPERATION")?>><?=CSaleExport::getTagName("SALE_EXPORT_ITEM_ORDER")?></<?=CSaleExport::getTagName("SALE_EXPORT_HOZ_OPERATION")?>>
		<<?=CSaleExport::getTagName("SALE_EXPORT_ROLE")?>><?=CSaleExport::getTagName("SALE_EXPORT_SELLER")?></<?=CSaleExport::getTagName("SALE_EXPORT_ROLE")?>>
		<<?=CSaleExport::getTagName("SALE_EXPORT_CURRENCY")?>><?=htmlspecialcharsbx(((self::$currency <> '')? mb_substr(self::$currency, 0, 3) : mb_substr($document["CURRENCY"], 0, 3)))?></<?=CSaleExport::getTagName("SALE_EXPORT_CURRENCY")?>>
		<<?=CSaleExport::getTagName("SALE_EXPORT_CURRENCY_RATE")?>>1</<?=CSaleExport::getTagName("SALE_EXPORT_CURRENCY_RATE")?>>
		<<?=CSaleExport::getTagName("SALE_EXPORT_AMOUNT")?>><?=$document["PRICE"]?></<?=CSaleExport::getTagName("SALE_EXPORT_AMOUNT")?>>
				<?
				if(self::getVersionSchema() > self::DEFAULT_VERSION)
				{
					?>
		<<?=CSaleExport::getTagName("SALE_EXPORT_VERSION")?>><?=(intval($document["VERSION"]) > 0 ? $document["VERSION"] : 0)?></<?=CSaleExport::getTagName("SALE_EXPORT_VERSION")?>><?
					if($document["ID_1C"] <> '')
					{
						?>
		<<?=CSaleExport::getTagName("SALE_EXPORT_ID_1C")?>><?=htmlspecialcharsbx($document["ID_1C"])?></<?=CSaleExport::getTagName("SALE_EXPORT_ID_1C")?>><?
					}
				}
				if (self::$crmMode)
				{
			?><DateUpdate><?=$DB->FormatDate($document["DATE_UPDATE"], CSite::GetDateFormat("FULL"), "YYYY-MM-DD HH:MI:SS");?></DateUpdate><?
				}
				echo $xmlResult['Contragents'];
			?>
		<<?=CSaleExport::getTagName("SALE_EXPORT_TIME")?>><?=$DB->FormatDate($document["DATE_INSERT_FORMAT"], CSite::GetDateFormat("FULL"), "HH:MI:SS")?></<?=CSaleExport::getTagName("SALE_EXPORT_TIME")?>>
		<<?=CSaleExport::getTagName("SALE_EXPORT_COMMENTS")?>><?=htmlspecialcharsbx(self::toText($document["COMMENTS"]))?></<?=CSaleExport::getTagName("SALE_EXPORT_COMMENTS")?>>
			<?	echo $xmlResult['OrderTax'];
				echo $xmlResult['OrderDiscount'];
				echo self::getXmlSaleStore(array_unique($xmlResult['ShipmentsStoreList'], SORT_NUMERIC), $xmlResult['SaleStoreList']);
				// пустое
				//echo $storeBasket = self::getXmlSaleStoreBasket($document,$xmlResult['SaleStoreList']);
				echo $xmlResult['BasketItems'];
				echo $xmlResult['SaleProperties'];
			break;

			case 'Payment':
			case 'Shipment':
			?>
		<<?=CSaleExport::getTagName("SALE_EXPORT_ID")?>><?=($document["ID_1C"] <> '' ? $document["ID_1C"]:$document["ID"])?></<?=CSaleExport::getTagName("SALE_EXPORT_ID")?>>
		<<?=CSaleExport::getTagName("SALE_EXPORT_NUMBER")?>><?=$document["ID"]?></<?=CSaleExport::getTagName("SALE_EXPORT_NUMBER")?>>
		<?	switch($typeDocument)
			{
				case 'Payment':
		?>

		<<?=CSaleExport::getTagName("SALE_EXPORT_DATE")?>><?=$DB->FormatDate($document["DATE_BILL"], CSite::GetDateFormat("FULL"), "YYYY-MM-DD")?></<?=CSaleExport::getTagName("SALE_EXPORT_DATE")?>>
		<<?=CSaleExport::getTagName("SALE_EXPORT_HOZ_OPERATION")?>><?=CSaleExport::getTagName("SALE_EXPORT_ITEM_PAYMENT_".\Bitrix\Sale\PaySystem\Manager::getPsType($document['PAY_SYSTEM_ID']))?></<?=CSaleExport::getTagName("SALE_EXPORT_HOZ_OPERATION")?>>
		<?		break;
				case 'Shipment':?>
		<<?=CSaleExport::getTagName("SALE_EXPORT_DATE")?>><?=$DB->FormatDate($document["DATE_INSERT"], CSite::GetDateFormat("FULL"), "YYYY-MM-DD")?></<?=CSaleExport::getTagName("SALE_EXPORT_DATE")?>>
		<<?=CSaleExport::getTagName("SALE_EXPORT_HOZ_OPERATION")?>><?=CSaleExport::getTagName("SALE_EXPORT_ITEM_SHIPMENT")?></<?=CSaleExport::getTagName("SALE_EXPORT_HOZ_OPERATION")?>>
		<?		break;
			}?>
		<<?=CSaleExport::getTagName("SALE_EXPORT_ROLE")?>><?=CSaleExport::getTagName("SALE_EXPORT_SELLER")?></<?=CSaleExport::getTagName("SALE_EXPORT_ROLE")?>>
		<<?=CSaleExport::getTagName("SALE_EXPORT_CURRENCY")?>><?=htmlspecialcharsbx(((self::$currency <> '')? mb_substr(self::$currency, 0, 3) : mb_substr($document["CURRENCY"], 0, 3)))?></<?=CSaleExport::getTagName("SALE_EXPORT_CURRENCY")?>>
		<<?=CSaleExport::getTagName("SALE_EXPORT_CURRENCY_RATE")?>>1</<?=CSaleExport::getTagName("SALE_EXPORT_CURRENCY_RATE")?>>
		<?	switch($typeDocument)
			{
				case 'Payment':
		?>
		<<?=CSaleExport::getTagName("SALE_EXPORT_AMOUNT")?>><?=$document['SUM']?></<?=CSaleExport::getTagName("SALE_EXPORT_AMOUNT")?>>
		<?		break;
				case 'Shipment':
                    $price = 0;
                    if(count($document['BasketResult'])>0)
                    {
                        foreach($document['BasketResult'] as $basketItem)
                        {
                            $price = $price + $basketItem['PRICE'] * $basketItem['SALE_INTERNALS_BASKET_SHIPMENT_ITEM_QUANTITY'];
                        }
                    }
		?>
		<<?=CSaleExport::getTagName("SALE_EXPORT_AMOUNT")?>><?=$price+intval($document['PRICE_DELIVERY'])?></<?=CSaleExport::getTagName("SALE_EXPORT_AMOUNT")?>>
		<?		break;
			}?>
		<<?=CSaleExport::getTagName("SALE_EXPORT_VERSION")?>><?=(intval($document["VERSION"]) > 0 ? $document["VERSION"] : 0)?></<?=CSaleExport::getTagName("SALE_EXPORT_VERSION")?>>
		<<?=CSaleExport::getTagName("SALE_EXPORT_NUMBER_BASE")?>><?=$document['ORDER_ID']?></<?=CSaleExport::getTagName("SALE_EXPORT_NUMBER_BASE")?>>
		<?echo $xmlResult['Contragents'];?>
		<?	switch($typeDocument)
			{
				case 'Payment':
		?>
		<<?=CSaleExport::getTagName("SALE_EXPORT_TIME")?>><?=$DB->FormatDate($document["DATE_BILL"], CSite::GetDateFormat("FULL"), "HH:MI:SS")?></<?=CSaleExport::getTagName("SALE_EXPORT_TIME")?>>
		<?		break;
				case 'Shipment':?>
				<?=$xmlResult['OrderTax'];?>
				<?
				if(isset($xmlResult['ShipmentsStoreList'][$document["ID"]]))
				{
				    $storId = $xmlResult['ShipmentsStoreList'][$document["ID"]];
				    //echo self::getXmlSaleStore(array($document["ID"]=>$storId), $xmlResult['SaleStoreList']);
				}?>

		<<?=CSaleExport::getTagName("SALE_EXPORT_TIME")?>><?=$DB->FormatDate($document["DATE_INSERT"], CSite::GetDateFormat("FULL"), "HH:MI:SS")?></<?=CSaleExport::getTagName("SALE_EXPORT_TIME")?>>
		<?		break;
			}?>
		<<?=CSaleExport::getTagName("SALE_EXPORT_COMMENTS")?>><?=htmlspecialcharsbx($document["COMMENTS"])?></<?=CSaleExport::getTagName("SALE_EXPORT_COMMENTS")?>>

		<?	switch($typeDocument)
			{
				case 'Payment':

					$checkData = false;
				    $cashBoxOneCId = self::getCashBoxOneCId();
					if(isset($cashBoxOneCId) && $cashBoxOneCId>0)
                    {
                        $checks = \Bitrix\Sale\Cashbox\CheckManager::getPrintableChecks(array($cashBoxOneCId), array($document['ORDER_ID']));
						foreach($checks as $checkId=>$check)
                        {
							if($check['PAYMENT_ID']==$document["ID"])
                            {
								$checkData = $check;
                                break;
                            }
                        }
                    }
		?>
        <?
             if($checkData)
             {
        ?>
                 <<?=CSaleExport::getTagName("SALE_EXPORT_CASHBOX_CHECKS")?>>
                    <<?=CSaleExport::getTagName("SALE_EXPORT_CASHBOX_CHECK")?>>
                        <<?=CSaleExport::getTagName("SALE_EXPORT_ID")?>><?=($checkData['ID'])?></<?=CSaleExport::getTagName("SALE_EXPORT_ID")?>>
                        <<?=CSaleExport::getTagName("SALE_EXPORT_PROP_VALUES")?>>
                            <<?=CSaleExport::getTagName("SALE_EXPORT_PROP_VALUE")?>>
                                <<?=CSaleExport::getTagName("SALE_EXPORT_ID")?>>PRINT_CHECK</<?=CSaleExport::getTagName("SALE_EXPORT_ID")?>>
                                <<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>true</<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
                            </<?=CSaleExport::getTagName("SALE_EXPORT_PROP_VALUE")?>>
                        </<?=CSaleExport::getTagName("SALE_EXPORT_PROP_VALUES")?>>
                    </<?=CSaleExport::getTagName("SALE_EXPORT_CASHBOX_CHECK")?>>
                 </<?=CSaleExport::getTagName("SALE_EXPORT_CASHBOX_CHECKS")?>>
        <?
             }
        ?>
		<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTIES_VALUES")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_DATE_PAID")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=$document["DATE_PAID"]?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_CANCELED")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=($document["CANCELED"]=='Y'? 'true':'false')?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_PAY_SYSTEM_ID")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=$document["PAY_SYSTEM_ID"]?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_PAY_SYSTEM")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=$document["PAY_SYSTEM_NAME"]?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_PAY_PAID")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=($document["PAID"]=='Y'? 'true':'false')?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_PAY_RETURN")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=($document["IS_RETURN"]=='Y'? 'true':'false')?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_PAY_RETURN_REASON")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=$document["PAY_RETURN_COMMENT"]?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<?self::OutputXmlSiteName($document);?>
            <?if(isset($xmlResult['RekvProperties']) && $xmlResult['RekvProperties'] <> '') echo $xmlResult['RekvProperties'];?>
		</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTIES_VALUES")?>>
			<?	break;

				case 'Shipment':
			?>

			<?
			echo $xmlResult['BasketItems'];
			?>

		<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTIES_VALUES")?>>
		    <<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_PRICE_DELIVERY")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=($document["PRICE_DELIVERY"] <> ''? $document["PRICE_DELIVERY"]:"0.0000")?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_DATE_ALLOW_DELIVERY")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=$document["DATE_ALLOW_DELIVERY"]?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_DELIVERY_LOCATION")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=$document["DELIVERY_LOCATION"]?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_DELIVERY_STATUS")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=$document["STATUS_ID"]?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_DELIVERY_DEDUCTED")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=($document["DEDUCTED"]=='Y'? 'true':'false')?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_DATE_DEDUCTED")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=$document["DATE_DEDUCTED"]?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_REASON_UNDO_DEDUCTED")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=$document["REASON_UNDO_DEDUCTED"]?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_RESERVED")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=($document["RESERVED"]=='Y'? 'true':'false')?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_DELIVERY_ID")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=$document["DELIVERY_ID"]?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_DELIVERY")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=$document["DELIVERY_NAME"]?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_CANCELED")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=($document["CANCELED"]=='Y'? 'true':'false')?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_DELIVERY_DATE_CANCEL")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=$document["DATE_CANCELED"]?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_CANCEL_REASON")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=$document["REASON_CANCELED"]?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_REASON_MARKED")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=$document["REASON_MARKED"]?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
            <<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
                <<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_TRACKING_NUMBER")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
                <<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=$document["TRACKING_NUMBER"]?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
            </<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>

            <?self::OutputXmlSiteName($document);?>
			<?self::OutputXmlDeliveryAddress();?>

			<?if(isset($xmlResult['RekvProperties']) && $xmlResult['RekvProperties'] <> '') echo $xmlResult['RekvProperties'];?>
	</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTIES_VALUES")?>>
			<?
				break;
			}
		}
		?>
	</<?=CSaleExport::getTagName("SALE_EXPORT_DOCUMENT")?>>
		<?$c = ob_get_contents();
		ob_end_clean();
		echo $c;

		$typeEntityId = static::resolveEntityTypeId($typeDocument, $document);

		if(intval($typeEntityId)>0)
		{
			$filedsTolog = array(
				'ENTITY_ID' => $document["ID"],
				'XML_ID' => $document["ID_1C"]
			);

			if(self::getVersionSchema() < self::CONTAINER_VERSION)
			    $filedsTolog['MESSAGE'] = $c;

			switch ($typeDocument)
			{
                case 'Order':
					$filedsTolog['ENTITY_DATE_UPDATE'] = new \Bitrix\Main\Type\DateTime(\CAllDatabase::FormatDate($document['DATE_UPDATE']));
					if(self::getVersionSchema() >= self::CONTAINER_VERSION)
						$filedsTolog['PARENT_ID'] = $document["ID"];
                    break;
				case 'Payment':
				case 'Shipment':
				    $filedsTolog['OWNER_ENTITY_ID'] = $document["ORDER_ID"];

				    if(self::getVersionSchema() >= self::CONTAINER_VERSION)
				        $filedsTolog['PARENT_ID'] = $document["ORDER_ID"];
					break;
			}

			static::$documentsToLog[$typeEntityId][] = $filedsTolog;
		}
	}

	static function getXmlBasketItems($type, $arOrder, $arFilter, $arSelect=array(), $arShipment=array(), $order=null)
	{
		$result = array();
		$entity = static::getBasketTable();

		ob_start();
		?><<?=CSaleExport::getTagName("SALE_EXPORT_ITEMS")?>><?

		$select = array("ID", "NOTES", "PRODUCT_XML_ID", "CATALOG_XML_ID", "NAME", "PRICE", "QUANTITY", "DISCOUNT_PRICE", "VAT_RATE", "MEASURE_CODE", "SET_PARENT_ID", "TYPE", "VAT_INCLUDED", "MARKING_CODE_GROUP", "SHIPMENT_ITEM");
		if(count($arSelect)>0)
		    $select = array_merge($arSelect, $select);

		$dbBasket = $entity::getList(array(
			'select' => $select,
			'filter' => $arFilter,
			'order' => array("NAME" => "ASC")
		));

		$order = static::load($arOrder['ID']);

		$basketSum = 0;
		$priceType = "";
		$bVat = false;
		$vatRate = 0;
		$vatSum = 0;
		$arStore = self::getCatalogStore();
		while ($arBasket = $dbBasket->fetch())
		{
			if(strval($arBasket['TYPE'])!='' && $arBasket['TYPE']== \Bitrix\Sale\BasketItem::TYPE_SET)
			    continue;

			$shipmentCollection = $order->getShipmentCollection();    
			$shipment = $shipmentCollection->getItemById($arBasket['SALE_INTERNALS_BASKET_SHIPMENT_ITEM_ORDER_DELIVERY_ID']);
			$shipmentItemCollection = $shipment->getShipmentItemCollection();    

			$shipmentItem = $shipmentItemCollection->getItemByBasketId($arBasket['SALE_INTERNALS_BASKET_SHIPMENT_ITEM_BASKET_ID']);
			$shipmentItemStoreCollection = $shipmentItem->getShipmentItemStoreCollection();   

			$iOrderId = '';
			foreach ($shipmentItemStoreCollection as $shipmentItemStore) {
				$iOrderId = $shipmentItemStore->getField('STORE_ID');
			}

			$result[] = $arBasket;

			if($priceType == '')
				$priceType = $arBasket["NOTES"];
			?>
			<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ID")?>><?=htmlspecialcharsbx(static::normalizeExternalCode($arBasket["PRODUCT_XML_ID"]))?></<?=CSaleExport::getTagName("SALE_EXPORT_ID")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_CATALOG_ID")?>><?=htmlspecialcharsbx($arBasket["CATALOG_XML_ID"])?></<?=CSaleExport::getTagName("SALE_EXPORT_CATALOG_ID")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=htmlspecialcharsbx($arBasket["NAME"])?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<?

            	static::outputXmlUnit($arBasket);
            	
				if($type == 'Order')
				{
					static::outputXmlMarkingCodeGroup($arBasket);
				}
				elseif($type == 'Shipment')
				{	
					static::outputXmlMarkingCode($arBasket['SALE_INTERNALS_BASKET_SHIPMENT_ITEM_ID'], $order);					
				}
				
				if(DoubleVal($arBasket["DISCOUNT_PRICE"]) > 0)
			 	{
					?>
					<<?=CSaleExport::getTagName("SALE_EXPORT_DISCOUNTS")?>>
						<<?=CSaleExport::getTagName("SALE_EXPORT_DISCOUNT")?>>
							<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_ITEM_DISCOUNT")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
							<<?=CSaleExport::getTagName("SALE_EXPORT_AMOUNT")?>><?=$arBasket["DISCOUNT_PRICE"]?></<?=CSaleExport::getTagName("SALE_EXPORT_AMOUNT")?>>
							<<?=CSaleExport::getTagName("SALE_EXPORT_IN_PRICE")?>>true</<?=CSaleExport::getTagName("SALE_EXPORT_IN_PRICE")?>>
						</<?=CSaleExport::getTagName("SALE_EXPORT_DISCOUNT")?>>
					</<?=CSaleExport::getTagName("SALE_EXPORT_DISCOUNTS")?>>
					<?
				}
				?>
				<?if(self::getVersionSchema() >= self::PARTIAL_VERSION && $type == 'Shipment')
				{?>
				<<?=CSaleExport::getTagName("SALE_EXPORT_PRICE_PER_ITEM")?>><?=$arBasket["PRICE"]?></<?=CSaleExport::getTagName("SALE_EXPORT_PRICE_PER_ITEM")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_QUANTITY")?>><?=$arBasket["SALE_INTERNALS_BASKET_SHIPMENT_ITEM_QUANTITY"]?></<?=CSaleExport::getTagName("SALE_EXPORT_QUANTITY")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_AMOUNT")?>><?=$arBasket["PRICE"]*$arBasket["SALE_INTERNALS_BASKET_SHIPMENT_ITEM_QUANTITY"]?></<?=CSaleExport::getTagName("SALE_EXPORT_AMOUNT")?>>
				<?}
				else{
				?>
				<<?=CSaleExport::getTagName("SALE_EXPORT_PRICE_PER_ITEM")?>><?=$arBasket["PRICE"]?></<?=CSaleExport::getTagName("SALE_EXPORT_PRICE_PER_ITEM")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_QUANTITY")?>><?=$arBasket["QUANTITY"]?></<?=CSaleExport::getTagName("SALE_EXPORT_QUANTITY")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_AMOUNT")?>><?=$arBasket["PRICE"]*$arBasket["QUANTITY"]?></<?=CSaleExport::getTagName("SALE_EXPORT_AMOUNT")?>>
				<?}?>
				<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTIES_VALUES")?>>
					<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
						<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_TYPE_NOMENKLATURA")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
						<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=CSaleExport::getTagName("SALE_EXPORT_ITEM")?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
					</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
					<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
						<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_TYPE_OF_NOMENKLATURA")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
						<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=CSaleExport::getTagName("SALE_EXPORT_ITEM")?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
					</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>

					<?
					$number = self::getNumberBasketPosition($arBasket["ID"]);
					?>
					<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
						<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_BASKET_NUMBER")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
						<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=$number?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
					</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
					<?
					$dbProp = CSaleBasket::GetPropsList(Array("SORT" => "ASC", "ID" => "ASC"), Array("BASKET_ID" => $arBasket["ID"]), false, false, array("NAME", "SORT", "VALUE", "CODE"));
					while($arPropBasket = $dbProp->Fetch())
					{
						?>
						<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
							<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE_BASKET")?>#<?=($arPropBasket["CODE"] != "" ? $arPropBasket["CODE"]:htmlspecialcharsbx($arPropBasket["NAME"]))?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
							<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($arPropBasket["VALUE"])?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
						</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
						<?
					}
					?>
				</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTIES_VALUES")?>>
				<?if(DoubleVal($arBasket["VAT_RATE"]) > 0)
				{
					$bVat = true;
					$vatRate = DoubleVal($arBasket["VAT_RATE"]);
					$basketVatSum = (($arBasket["PRICE"] / ($arBasket["VAT_RATE"]+1)) * $arBasket["VAT_RATE"]);
					$vatSum += roundEx($basketVatSum * $arBasket["QUANTITY"], 2);
					?>
					<<?=CSaleExport::getTagName("SALE_EXPORT_TAX_RATES")?>>
						<<?=CSaleExport::getTagName("SALE_EXPORT_TAX_RATE")?>>
							<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_VAT")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
							<<?=CSaleExport::getTagName("SALE_EXPORT_RATE")?>><?=$arBasket["VAT_RATE"] * 100?></<?=CSaleExport::getTagName("SALE_EXPORT_RATE")?>>
						</<?=CSaleExport::getTagName("SALE_EXPORT_TAX_RATE")?>>
					</<?=CSaleExport::getTagName("SALE_EXPORT_TAX_RATES")?>>
					<<?=CSaleExport::getTagName("SALE_EXPORT_TAXES")?>>
						<<?=CSaleExport::getTagName("SALE_EXPORT_TAX")?>>
							<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_VAT")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
							<<?=CSaleExport::getTagName("SALE_EXPORT_IN_PRICE")?>><?=$arBasket["VAT_INCLUDED"]=="Y"?'true':'false'?></<?=CSaleExport::getTagName("SALE_EXPORT_IN_PRICE")?>>
							<<?=CSaleExport::getTagName("SALE_EXPORT_AMOUNT")?>><?=roundEx($basketVatSum, 2)?></<?=CSaleExport::getTagName("SALE_EXPORT_AMOUNT")?>>
						</<?=CSaleExport::getTagName("SALE_EXPORT_TAX")?>>
					</<?=CSaleExport::getTagName("SALE_EXPORT_TAXES")?>>
					<?
				}
				?>

			<?if(!empty($iOrderId)):?>
				<?$arOrder['STORE_ID'] = $iOrderId;?>
				<?=self::getXmlSaleStoreBasket($arOrder,$arStore)?>
			<?endif;?>

			</<?=CSaleExport::getTagName("SALE_EXPORT_ITEM")?>>
			<?
			$basketSum += $arBasket["PRICE"]*$arBasket["QUANTITY"];
		}

        if(self::getVersionSchema() >= self::PARTIAL_VERSION)
        {
            if(count($arShipment)>0)
            {
                foreach($arShipment as $shipment)
                {
                    self::getOrderDeliveryItem($shipment, $bVat, $vatRate, $vatSum);
                }
            }
        }
        else
		    self::getOrderDeliveryItem($arOrder, $bVat, $vatRate, $vatSum);

		?>
		</<?=CSaleExport::getTagName("SALE_EXPORT_ITEMS")?>><?

		$bufer = ob_get_clean();
		return array('outputXML'=>$bufer,'result'=>$result);
	}

	static function getXmlSaleProperties($arOrder, $arShipment, $arPayment, $agent, $agentParams, $bExportFromCrm)
	{
		ob_start();

        $arShipment = $arShipment[0];
        $arPayments = $arPayment;
        $arPayment = $arPayment[0];
		?>

		<?if(!empty($arPayments)):?>
			<Оплаты>
				<?foreach ($arPayments as $arPaymentItem):?>
					<Оплата>
						<КодПлатежа><?=$arPaymentItem['ID'];?></КодПлатежа>
						<НомерДокументаПрихода><?=$arPaymentItem['PAY_VOUCHER_NUM'];?></НомерДокументаПрихода>
						<ИндентификаторXML><?=$arPaymentItem['XML_ID'];?></ИндентификаторXML>
						<Индентификатор1C><?=$arPaymentItem['ID_1C'];?></Индентификатор1C>
						<ДатаОплаты><?=$arPaymentItem['DATE_PAID'];?></ДатаОплаты>
						<СуммаОплаты><?=$arPaymentItem['SUM'];?></СуммаОплаты>
						<Оплачено><?=$arPaymentItem['PAID'];?></Оплачено>
					</Оплата>
				<?endforeach;?>
			</Оплаты>
		<?endif;?>

		<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTIES_VALUES")?>><?
		if($arOrder["DATE_PAYED"] <> '')
		{
			?>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_DATE_PAID")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=$arOrder["DATE_PAYED"]?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<?
		}

		if(self::getVersionSchema() < self::PARTIAL_VERSION || $bExportFromCrm) // #version# < 2.10      ? || $bExportFromCrm
		{
			/*if($arPayment["PAY_VOUCHER_NUM"] <> '')
			{
				?>
				<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
					<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_PAY_NUMBER")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
					<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($arPayment["PAY_VOUCHER_NUM"])?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
				</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<?
			}*/
			if($arShipment["DATE_ALLOW_DELIVERY"] <> '')
			{
				?>
				<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
					<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_DATE_ALLOW_DELIVERY")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
					<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=$arShipment["DATE_ALLOW_DELIVERY"]?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
				</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<?
			}
		}
		else
		{
		?>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_DATE_ALLOW_DELIVERY_LAST")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=$arOrder["DATE_ALLOW_DELIVERY"]?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>><?

		}

		if($arShipment["DELIVERY_ID"] <> '')
		{
			?>
            <<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
            <<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_DELIVERY_SERVICE")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
            <<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($arShipment["DELIVERY_NAME"])?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
            </<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
            <<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
            <<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_DELIVERY_ID")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
            <<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($arShipment["DELIVERY_ID"])?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
            </<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<?
		}

		if(intval($arPayment["PAY_SYSTEM_ID"])>0)
		{
			?>
            <<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
            <<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_PAY_SYSTEM")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
            <<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($arPayment["PAY_SYSTEM_NAME"])?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
            </<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
            <<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
            <<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_PAY_SYSTEM_ID")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
            <<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($arPayment["PAY_SYSTEM_ID"])?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
            </<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<?
		}
		?>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_ORDER_PAID")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=($arOrder["PAYED"]=="Y")?"true":"false";?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
		<?
		if(self::getVersionSchema() < self::PARTIAL_VERSION || $bExportFromCrm)
		{
		?>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_ALLOW_DELIVERY")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=($arShipment["ALLOW_DELIVERY"]=="Y")?"true":"false";?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>><?
		}
		?>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_CANCELED")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=($arOrder["CANCELED"]=="Y")?"true":"false";?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_FINAL_STATUS")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=($arOrder["STATUS_ID"]=="F")?"true":"false";?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_ORDER_STATUS")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?$arStatus = static::getStatusInfoByStatusId($arOrder["STATUS_ID"]); echo htmlspecialcharsbx("[".$arOrder["STATUS_ID"]."] ".$arStatus["NAME"]);?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_ORDER_STATUS_ID")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($arOrder["STATUS_ID"]);?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<?if($arOrder["DATE_CANCELED"] <> '')
			{
				?>
				<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
					<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_DATE_CANCEL")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
					<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=$arOrder["DATE_CANCELED"]?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
				</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
					<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_CANCEL_REASON")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
					<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($arOrder["REASON_CANCELED"])?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
				</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<?
			}
			if($arOrder["DATE_STATUS"] <> '')
			{
				?>
				<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
					<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_DATE_STATUS")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
					<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=$arOrder["DATE_STATUS"]?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
				</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<?
			}
			if($arOrder["USER_DESCRIPTION"] <> '')
			{
				?>
				<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
					<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_USER_DESCRIPTION")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
					<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($arOrder["USER_DESCRIPTION"])?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
				</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<?
			}
			self::OutputXmlSiteName($arOrder);

			self::OutputXmlRekvProperties($agent, $agentParams);

			self::OutputXmlDeliveryAddress();

			?>
		</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTIES_VALUES")?>>
		<?
		$bufer = ob_get_clean();
		return $bufer;
	}

}