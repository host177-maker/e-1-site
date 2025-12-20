<?
IncludeModuleLangFile(__FILE__);
//namespace Fact;
use \Bitrix\Main\Type\RandomSequence;
use \Bitrix\Sale;

class CSaleOrderPhone extends CSaleOrderLoader
{

	public function nodeHandlerDefaultModuleOneC(CDataXML $dataXml)
	{
		$value = $dataXml->GetArray();

		/**
		 * @deprecated
		 */
		if (!empty($value[GetMessage("CC_BSC1_DOCUMENT")])) {
			$value = $value[GetMessage("CC_BSC1_DOCUMENT")];

			$arDocument = $this->collectDocumentInfo($value);

			if (!empty($arDocument)) {
				$this->logMessage("StartExchange:");
				$this->logMessage("VersionSchema: " . self::getVersionSchema());

				if (self::getVersionSchema() >= self::PARTIAL_VERSION) {
					$this->nodeHandlerPartialVersion($arDocument);
				} else {
					self::oldSaveOrder($arDocument);

					\Bitrix\Main\Config\Option::set('sale', 'onec_exchange_type', 'default');
					\Bitrix\Main\Config\Option::set('sale', 'onec_exchange_last_time', time());
				}
			}
			$this->strError .= $this->strErrorDocument;
		}
	}

	function getUserByCodeOrProp($phone, $property)
	{
		$filter = array(
			$property => $phone
		);
		$rsUsers = CUser::GetList(($by = "personal_country"), ($order = "desc"), $filter); // выбираем пользователей
		while ($arUser = $rsUsers->Fetch()) {
			$arUserAll = $arUser;
		};
		return $arUserAll;
	}

	function oldSaveOrder($arOrder, $option = array())
	{
		global $APPLICATION;
		global $USER;

		$userId = 0;
		if (isset($USER) && $USER instanceof CUser)
			$userId = (int)$USER->GetID();

		$isInvoice = (isset($option['CRM']) && $option['CRM'] == 'Y');

		/** @var \Bitrix\Crm\Invoice\Compatible\Helper|CSaleOrder $parentEntity */
		$parentEntity = $isInvoice ? \Bitrix\Crm\Invoice\Compatible\Helper::class : CSaleOrder::class;
		/** @var \Bitrix\Crm\Invoice\Compatible\BasketHelper|CSaleBasket $basketEntity */
		$basketEntity = $isInvoice ? \Bitrix\Crm\Invoice\Compatible\BasketHelper::class : CSaleBasket::class;
		/** @var \Bitrix\Crm\Invoice\Internals\InvoiceChangeTable|\Bitrix\Sale\Internals\OrderChangeTable $changeEntity */
		$changeEntity = $isInvoice ? \Bitrix\Crm\Invoice\Internals\InvoiceChangeTable::class : \Bitrix\Sale\Internals\OrderChangeTable::class;
		/** @var CCrmInvoiceTax|CSaleOrderTax $taxEntity */
		$taxEntity = $isInvoice ? CCrmInvoiceTax::class : CSaleOrderTax::class;

		if ($arOrder["ID"] == '' && $arOrder["ID_1C"] <> '') //try to search order from 1C
		{
			$dbOrder = $parentEntity::GetList(array("ID" => "DESC"), array("ID_1C" => $arOrder["ID_1C"]), false, false, array("ID", "ID_1C"));
			if ($orderInfo = $dbOrder->Fetch()) {
				$arOrder["ID"] = $orderInfo["ID"];
			}
		}
		if ($arOrder["ID"] <> '') // exists site order
		{
			$dbOrder = $parentEntity::GetList(array(), array("ID" => $arOrder["ID"]), false, false, array("ID", "LID", "PERSON_TYPE_ID", "PAYED", "DATE_PAYED", "CANCELED", "DATE_CANCELED", "REASON_CANCELED", "STATUS_ID", "DATE_STATUS", "PAY_VOUCHER_NUM", "PAY_VOUCHER_DATE", "PRICE_DELIVERY", "ALLOW_DELIVERY", "DATE_ALLOW_DELIVERY", "PRICE", "CURRENCY", "DISCOUNT_VALUE", "USER_ID", "PAY_SYSTEM_ID", "DELIVERY_ID", "DATE_INSERT", "DATE_INSERT_FORMAT", "DATE_UPDATE", "USER_DESCRIPTION", "ADDITIONAL_INFO", "COMMENTS", "TAX_VALUE", "DELIVERY_DOC_NUM", "DELIVERY_DOC_DATE", "STORE_ID", "ACCOUNT_NUMBER", "VERSION", "VERSION_1C", "ID_1C"));
			if ($orderInfo = $dbOrder->Fetch()) {
				if ($arOrder["VERSION_1C"] != $orderInfo["VERSION_1C"] || ($orderInfo["VERSION_1C"] == '' || $arOrder["VERSION_1C"] == '')) // skip update if the same version
				{
					$arOrderFields = array();
					$orderId = $orderInfo["ID"];

					if ($isInvoice) {
						if ($invoice = \Bitrix\Crm\Invoice\Invoice::load($orderId)) {
							$basket = $invoice->getBasket();
						}
					}

					$changeEntity::Add(['ORDER_ID' => $orderId, 'TYPE' => 'ORDER_1C_IMPORT', 'USER_ID' => $userId]);
					if ($arOrder["ID_1C"] != $orderInfo["ID_1C"])
						$arOrderFields["ID_1C"] = $arOrder["ID_1C"];

					$arOrderFields["VERSION_1C"] = $arOrder["VERSION_1C"];

					if ($orderInfo["PAYED"] != "Y" && $orderInfo["ALLOW_DELIVERY"] != "Y" && $orderInfo["STATUS_ID"] != "F") {
						$dbOrderTax = $taxEntity::GetList(
							array(),
							array("ORDER_ID" => $orderId),
							false,
							false,
							array("ID", "TAX_NAME", "VALUE", "VALUE_MONEY", "CODE", "IS_IN_PRICE")
						);
						$bTaxFound = false;
						if ($arOrderTax = $dbOrderTax->Fetch()) {
							$bTaxFound = true;
							if (intval($arOrderTax["VALUE_MONEY"]) != intval($arOrder["TAX"]["VALUE_MONEY"]) || intval($arOrderTax["VALUE"]) != intval($arOrder["TAX"]["VALUE"]) || ($arOrderTax["IS_IN_PRICE"] != $arOrder["TAX"]["IS_IN_PRICE"])) {
								if (intval($arOrder["TAX"]["VALUE"]) > 0) {
									$arFields = array(
										"TAX_NAME" => $arOrder["TAX"]["NAME"],
										"ORDER_ID" => $orderId,
										"VALUE" => $arOrder["TAX"]["VALUE"],
										"IS_PERCENT" => "Y",
										"IS_IN_PRICE" => $arOrder["TAX"]["IS_IN_PRICE"],
										"VALUE_MONEY" => $arOrder["TAX"]["VALUE_MONEY"],
										"CODE" => "VAT1C",
										"APPLY_ORDER" => "100"
									);
									$taxEntity::Update($arOrderTax["ID"], $arFields);
									$arOrderFields["TAX_VALUE"] = $arOrder["TAX"]["VALUE_MONEY"];
								} else {
									$taxEntity::Delete($arOrderTax["ID"]);
									$arOrderFields["TAX_VALUE"] = 0;
								}
							}
						}

						if (!$bTaxFound) {
							if (intval($arOrder["TAX"]["VALUE"]) > 0) {
								$arFields = array(
									"TAX_NAME" => $arOrder["TAX"]["NAME"],
									"ORDER_ID" => $orderId,
									"VALUE" => $arOrder["TAX"]["VALUE"],
									"IS_PERCENT" => "Y",
									"IS_IN_PRICE" => $arOrder["TAX"]["IS_IN_PRICE"],
									"VALUE_MONEY" => $arOrder["TAX"]["VALUE_MONEY"],
									"CODE" => 'VAT1C',
									"APPLY_ORDER" => '100',
								);
								$taxEntity::Add($arFields);
								$arOrderFields["TAX_VALUE"] = $arOrder["TAX"]["VALUE_MONEY"];
							}
						}

						$arShoppingCart = array();
						$bNeedUpdate = false;
						$dbBasket = $basketEntity::GetList(
							array("NAME" => "ASC"),
							array("ORDER_ID" => $orderId),
							false,
							false,
							array(
								"ID",
								"QUANTITY",
								"CANCEL_CALLBACK_FUNC",
								"MODULE",
								"PRODUCT_ID",
								"PRODUCT_PROVIDER_CLASS",
								"RESERVED",
								"RESERVE_QUANTITY",
								"TYPE",
								"SET_PARENT_ID",
								"PRICE",
								"VAT_RATE",
								"DISCOUNT_PRICE",
								"PRODUCT_XML_ID",
							)
						);

						while ($arBasket = $dbBasket->Fetch()) {
							$arFields = array();
							if (!empty($arOrder["items"][$arBasket["PRODUCT_XML_ID"]])) {
								if ($arBasket["QUANTITY"] != $arOrder["items"][$arBasket["PRODUCT_XML_ID"]]["QUANTITY"])
									$arFields["QUANTITY"] = $arOrder["items"][$arBasket["PRODUCT_XML_ID"]]["QUANTITY"];
								if ($arBasket["PRICE"] != $arOrder["items"][$arBasket["PRODUCT_XML_ID"]]["PRICE"])
									$arFields["PRICE"] = $arOrder["items"][$arBasket["PRODUCT_XML_ID"]]["PRICE"];
								if ($arBasket["VAT_RATE"] != $arOrder["items"][$arBasket["PRODUCT_XML_ID"]]["VAT_RATE"])
									$arFields["VAT_RATE"] = $arOrder["items"][$arBasket["PRODUCT_XML_ID"]]["VAT_RATE"];
								if ($arBasket["DISCOUNT_PRICE"] != $arOrder["items"][$arBasket["PRODUCT_XML_ID"]]["DISCOUNT_PRICE"])
									$arFields["DISCOUNT_PRICE"] = $arOrder["items"][$arBasket["PRODUCT_XML_ID"]]["DISCOUNT_PRICE"];

								if (count($arFields) > 0) {
									$bNeedUpdate = true;

									if ($isInvoice) {
										/** @var Sale\BasketItem $basketItem */
										$basketItem = $basket->getItemById($arBasket['ID']);
										if (isset($arFields['QUANTITY']))
											$basketItem->setField('QUANTITY', $arFields['QUANTITY']);
										if (isset($arFields['PRICE']))
											$basketItem->setPrice($arFields['PRICE']);
										if (isset($arFields['VAT_RATE']))
											$basketItem->setField('VAT_RATE', $arFields['VAT_RATE']);
										if (isset($arFields['DISCOUNT_PRICE']))
											$basketItem->setField('DISCOUNT_PRICE', $arFields['DISCOUNT_PRICE']);
									} else {
										$arFields["ID"] = $arBasket["ID"];
										if (DoubleVal($arFields["QUANTITY"]) <= 0)
											$arFields["QUANTITY"] = $arBasket["QUANTITY"];

										$arShoppingCart[] = $arFields;
									}
								} else {
									$arShoppingCart[] = $arBasket;
								}
								//CSaleBasket::Update($arBasket["ID"], $arFields);

								$arOrder["items"][$arBasket["PRODUCT_XML_ID"]]["CHECKED"] = "Y";
							} else {
								if ($arOrder['CANCELED'] != "true" && $arOrder["TRAITS"][GetMessage("CC_BSC1_CANCELED")] != "true" && $orderInfo["CANCELED"] == "N") {
									if ($isInvoice) {
										$basket->getItemById($arBasket["ID"])
											->delete();
									} else {
										$bNeedUpdate = true;
										//CSaleBasket::Delete($arBasket["ID"]);
									}
								}
							}
						}

						if (!empty($arOrder["items"])) {
							$priceDelivery = 0;
							foreach ($arOrder["items"] as $itemID => $arItem) {
								if ($arItem["CHECKED"] != "Y") {
									if ($arItem["TYPE"] == GetMessage("CC_BSC1_ITEM")) {
										$currencyTo = CSaleLang::GetLangCurrency($this->arParams["SITE_NEW_ORDERS"]);
										if ($orderInfo['CURRENCY'] == $currencyTo) {
											$bNeedUpdate = true;

											if ($arBasketFields = $this->prepareProduct4Basket($itemID, $arItem, $orderId, $orderInfo)) {
												if ($isInvoice) {
													$basketItem = $basket->createItem($arBasketFields['MODULE'], $arBasketFields['PRODUCT_ID']);
													$basketItem->setPrice($arBasketFields['PRICE'], true);
													unset($arBasketFields['MODULE'], $arBasketFields['PRODUCT_ID'], $arBasketFields['PRICE'], $arBasketFields['ORDER_ID']);

													$basketItem->setFields($arBasketFields);
												} else {
													$arShoppingCart[] = $arBasketFields;
												}
											}
										} else {
											$this->strError .= "\r\n " . GetMessage("CC_BSC1_ORDER_ERROR_5", array('#XML_1C_DOCUMENT_ID#' => $arOrder['ID'], '#CURRENCY_FROM#' => $orderInfo['CURRENCY'], '#CURRENCY_TO#' => $currencyTo));
										}
									} elseif ($arItem["TYPE"] == GetMessage("CC_BSC1_SERVICE")) {
										$priceDelivery = $arItem["PRICE"];
									}

									if ($hasServiceItem) {
										if ($priceDelivery != intval($orderInfo["PRICE_DELIVERY"])) {
											if ($arItem["TYPE"] == GetMessage("CC_BSC1_SERVICE"))
												$arOrderFields["PRICE_DELIVERY"] = $priceDelivery;
										}
									} else {
										if ($priceDelivery != intval($orderInfo["PRICE_DELIVERY"]))
											$arOrderFields["PRICE_DELIVERY"] = $priceDelivery;
									}
								}
							}
						}

						$isUsed = \Bitrix\Sale\Compatible\DiscountCompatibility::isUsed();
						\Bitrix\Sale\Compatible\DiscountCompatibility::stopUsageCompatible();

						if ($bNeedUpdate) {
							if ($isInvoice) {
								$invoice->save();
							} else {
								$arErrors = array();
								if (!$basketEntity::DoSaveOrderBasket($orderId, $orderInfo["LID"], $orderInfo["USER_ID"], $arShoppingCart, $arErrors)) {
									$e = $APPLICATION->GetException();
									if (is_object($e))
										$this->strError .= "\r\n " . GetMessage("CC_BSC1_ORDER_ERROR_3", array('#XML_1C_DOCUMENT_ID#' => $arOrder["ID"])) . $e->GetString();
								}
							}
						}

						if (DoubleVal($orderInfo["DISCOUNT_VALUE"]) > 0)
							$arOrderFields["DISCOUNT_VALUE"] = 0;
						if ($arOrder["COMMENT"] <> '' && $arOrder["COMMENT"] != $orderInfo["COMMENTS"])
							$arOrderFields["COMMENTS"] = $arOrder["COMMENT"];
						$arOrderFields["UPDATED_1C"] = "Y";

						if (!empty($arOrderFields))
							$parentEntity::Update($orderId, $arOrderFields);
						if ($isUsed === true) {
							\Bitrix\Sale\Compatible\DiscountCompatibility::revertUsageCompatible();
						}
					} else {
						$this->strError .= "\n" . GetMessage("CC_BSC1_FINAL_NOT_EDIT", array("#ID#" => $orderId));
					}
				}

				$arAditFields = array();
				if ($arOrder['CANCELED'] == "true" || $arOrder["TRAITS"][GetMessage("CC_BSC1_CANCELED")] == "true" || $arOrder["TRAITS"][GetMessage("CC_BSC1_CANCEL")] == "true") {
					if ($orderInfo["CANCELED"] == "N") {
						$parentEntity::CancelOrder($orderInfo["ID"], "Y", $arOrder["COMMENT"]);
						$arAditFields["UPDATED_1C"] = "Y";
					}
				} else {
					if ($arOrder["TRAITS"][GetMessage("CC_BSC1_CANCELED")] != "true") {
						if ($orderInfo["CANCELED"] == "Y") {
							$parentEntity::CancelOrder($orderInfo["ID"], "N", $arOrder["COMMENT"]);
							$arAditFields["UPDATED_1C"] = "Y";
						}
					}

					if (mb_strlen($arOrder["TRAITS"][GetMessage("CC_BSC1_1C_PAYED_DATE")]) > 1) {
						if ($orderInfo["PAYED"] == "N")
							$parentEntity::PayOrder($orderInfo["ID"], "Y");
						$arAditFields["PAY_VOUCHER_DATE"] = CDatabase::FormatDate(str_replace("T", " ", $arOrder["TRAITS"][GetMessage("CC_BSC1_1C_PAYED_DATE")]), "YYYY-MM-DD HH:MI:SS", CLang::GetDateFormat("FULL", LANG));
						if ($arOrder["TRAITS"][GetMessage("CC_BSC1_1C_PAYED_NUM")] <> '')
							$arAditFields["PAY_VOUCHER_NUM"] = $arOrder["TRAITS"][GetMessage("CC_BSC1_1C_PAYED_NUM")];
						$arAditFields["UPDATED_1C"] = "Y";
					}

					if (mb_strlen($arOrder["TRAITS"][GetMessage("CC_BSC1_1C_DELIVERY_DATE")]) > 1) {
						if (!$isInvoice) {
							if ($orderInfo["ALLOW_DELIVERY"] == "N")
								CSaleOrder::DeliverOrder($orderInfo["ID"], "Y");
						}

						$arAditFields["DATE_ALLOW_DELIVERY"] = CDatabase::FormatDate(str_replace("T", " ", $arOrder["TRAITS"][GetMessage("CC_BSC1_1C_DELIVERY_DATE")]), "YYYY-MM-DD HH:MI:SS", CLang::GetDateFormat("FULL", LANG));
						$arAditFields["DELIVERY_DOC_DATE"] = $arAditFields["DATE_ALLOW_DELIVERY"];

						if ($this->arParams["FINAL_STATUS_ON_DELIVERY"] <> '' && $orderInfo["STATUS_ID"] != "F" && $orderInfo["STATUS_ID"] != $this->arParams["FINAL_STATUS_ON_DELIVERY"])
							static::setStatus($orderInfo["ID"], $this->arParams["FINAL_STATUS_ON_DELIVERY"], $isInvoice);
						if ($arOrder["TRAITS"][GetMessage("CC_BSC1_1C_DELIVERY_NUM")] <> '')
							$arAditFields["DELIVERY_DOC_NUM"] = $arOrder["TRAITS"][GetMessage("CC_BSC1_1C_DELIVERY_NUM")];
						$arAditFields["UPDATED_1C"] = "Y";
					}
				}


				if ($this->arParams["CHANGE_STATUS_FROM_1C"] && $arOrder["TRAITS"][GetMessage("CC_BSC1_1C_STATUS_ID")] <> '') {
					if ($orderInfo["STATUS_ID"] != $arOrder["TRAITS"][GetMessage("CC_BSC1_1C_STATUS_ID")]) {
						static::setStatus($orderInfo["ID"], $arOrder["TRAITS"][GetMessage("CC_BSC1_1C_STATUS_ID")], $isInvoice);
						$arAditFields["UPDATED_1C"] = "Y";
					}
				}

				if (count($arAditFields) > 0)
					$parentEntity::Update($orderInfo["ID"], $arAditFields);
			} else
				$this->strError .= "\n" . GetMessage("CC_BSC1_ORDER_NOT_FOUND", array("#ID#" => $arOrder["ID"]));
		} elseif ($this->arParams["IMPORT_NEW_ORDERS"] == "Y") // create new order (ofline 1C)
		{
			if (!empty($arOrder["AGENT"]) && $arOrder["AGENT"]["ID"] <> '') {
				$arOrder["PERSON_TYPE_ID"] = 0;
				$arOrder["USER_ID"] = 0;
				$arErrors = array();
				$dbUProp = CSaleOrderUserProps::GetList(array(), array("XML_ID" => $arOrder["AGENT"]["ID"]), false, false, array("ID", "NAME", "USER_ID", "PERSON_TYPE_ID", "XML_ID", "VERSION_1C"));
				if ($arUProp = $dbUProp->Fetch()) {
					$arOrder["USER_ID"] = $arUProp["USER_ID"];
					$arOrder["PERSON_TYPE_ID"] = $arUProp["PERSON_TYPE_ID"];
					$arOrder["USER_PROFILE_ID"] = $arUProp["ID"];
					$arOrder["USER_PROFILE_VERSION"] = $arUProp["VERSION_1C"];

					$dbUPropValue = CSaleOrderUserPropsValue::GetList(array(), array("USER_PROPS_ID" => $arUProp["ID"]));
					while ($arUPropValue = $dbUPropValue->Fetch()) {
						$arOrder["USER_PROPS"][$arUPropValue["ORDER_PROPS_ID"]] = $arUPropValue["VALUE"];
					}
				} else {
					if ($arOrder["AGENT"]["ID"] <> '') {
						$arAI = explode("#", $arOrder["AGENT"]["ID"]);
						if (intval($arAI[0]) > 0) {
							$dbUser = CUser::GetByID($arAI[0]);
							if ($arU = $dbUser->Fetch()) {
								if (htmlspecialcharsback(mb_substr(htmlspecialcharsbx($arU["ID"] . "#" . $arU["LOGIN"] . "#" . $arU["LAST_NAME"] . " " . $arU["NAME"] . " " . $arU["SECOND_NAME"]), 0, 80)) == $arOrder["AGENT"]["ID"]) {
									$arOrder["USER_ID"] = $arU["ID"];
								}
							}
						}
					}

					if (intval($arOrder["USER_ID"]) <= 0) {
						//create new user
						$arUser = array(
							"NAME"  => $arOrder["AGENT"]["ITEM_NAME"],
							"EMAIL" => $arOrder["AGENT"]["CONTACT"]["MAIL_NEW"],
						);
						$emServer = $_SERVER["SERVER_NAME"];
						if (mb_strpos($_SERVER["SERVER_NAME"], ".") === false)
							$emServer .= ".bx";
						//если почта есть, то не будем создавать нового пользователя,
						$emailUserIsset = false;
						if (!empty($arUser["EMAIL"]) && $arUser["EMAIL"] !== 'почта отсутствует') {
							$filter = array("EMAIL" => $arUser["EMAIL"]);
							$rsUser = CUser::GetList(($by = "id"), ($order = "desc"), $filter);
							if ($arUserCheck = $rsUser->Fetch()) {
								$arOrder["USER_ID"] = $arUserCheck["ID"];
								if (strpos($arUserCheck["LOGIN"], 'buyer') !== false) {
									$obUser = new CUser;
									$userFields[] = array();
									if (!empty($arOrder["AGENT"]["CONTACT"]["PHONE"])) {
										//обновим buyer на номер телефона
										$phoneNormalLogin = Bitrix\Main\UserPhoneAuthTable::normalizePhoneNumber($arOrder["AGENT"]["CONTACT"]["PHONE"]);
										$userFields["LOGIN"] = $phoneNormalLogin;
										if (strpos($arUserCheck["EMAIL"], 'buyer') !== false) {
											$userFields["EMAIL"] = $phoneNormalLogin . "@" . $emServer;/*"buyer".time().GetRandomCode(2)*/
										}
										if (empty($arUserCheck["WORK_PHONE"])) {
											$userFields["WORK_PHONE"] = $arOrder["AGENT"]["CONTACT"]["PHONE"];
										}
										if (empty($arUserCheck["PERSONAL_PHONE"])) {
											$userFields["PERSONAL_PHONE"] = $arOrder["AGENT"]["CONTACT"]["PHONE"];
										}
										if (!$obUser->Update($arOrder["USER_ID"], $userFields, true))
											$this->strError .= "\n" . $obUser->LAST_ERROR;
									}
								}
								$emailUserIsset = true;
							} else $emailUserIsset = false;
						}

						if ($arUser["NAME"] == '' && !empty($arOrder["AGENT"]["CONTACT"]["CONTACT_PERSON"]))
							$arUser["NAME"] = $arOrder["AGENT"]["CONTACT"]["CONTACT_PERSON"];
						else if (!empty($arOrder["AGENT"]["AGENT_NAME"]) && empty($arUser["NAME"])) {
							$arUser["NAME"] = $arOrder["AGENT"]["AGENT_NAME"];
						}

						if (empty($arUser["EMAIL"]) || $arUser["EMAIL"] === 'почта отсутствует') {
							if (!empty($arOrder["AGENT"]["CONTACT"]["PHONE"])) {
								//приведем телефон к виду +7 (927) 001-01-01
								$phone = trim($arOrder["AGENT"]["CONTACT"]["PHONE"]);
								$strPhoneResult = preg_replace(
									array(
										'/[\+]?([7|8])[-|\s]?\([-|\s]?(\d{3})[-|\s]?\)[-|\s]?(\d{3})[-|\s]?(\d{2})[-|\s]?(\d{2})/',
										'/[\+]?([7|8])[-|\s]?(\d{3})[-|\s]?(\d{3})[-|\s]?(\d{2})[-|\s]?(\d{2})/',
										'/[\+]?([7|8])[-|\s]?\([-|\s]?(\d{4})[-|\s]?\)[-|\s]?(\d{2})[-|\s]?(\d{2})[-|\s]?(\d{2})/',
										'/[\+]?([7|8])[-|\s]?(\d{4})[-|\s]?(\d{2})[-|\s]?(\d{2})[-|\s]?(\d{2})/',
										'/[\+]?([7|8])[-|\s]?\([-|\s]?(\d{4})[-|\s]?\)[-|\s]?(\d{3})[-|\s]?(\d{3})/',
										'/[\+]?([7|8])[-|\s]?(\d{4})[-|\s]?(\d{3})[-|\s]?(\d{3})/',
									),
									array(
										'+7 ($2) $3-$4-$5',
										'+7 ($2) $3-$4-$5',
										'+7 ($2) $3-$4-$5',
										'+7 ($2) $3-$4-$5',
										'+7 ($2) $3-$4',
										'+7 ($2) $3-$4',
									),
									$phone
								);
								//ищем пользователя по номеру телефона
								$arUserResult = self::getUserByCodeOrProp($strPhoneResult, "WORK_PHONE");
								if (!empty($arUserResult["ID"])) {
									$arOrder["USER_ID"] = $arUserResult["ID"];
								} else {
									$arUserResult = self::getUserByCodeOrProp($arOrder["AGENT"]["CONTACT"]["PHONE"], "WORK_PHONE");
									if (!empty($arUserResult["ID"])) {
										$arOrder["USER_ID"] = $arUserResult["ID"];
									} else {
										$arUserResult = self::getUserByCodeOrProp($strPhoneResult, "PERSONAL_PHONE");
										if (!empty($arUserResult["ID"])) {
											$arOrder["USER_ID"] = $arUserResult["ID"];
										} else {
											$arUserResult = self::getUserByCodeOrProp($strPhoneResult, "PERSONAL_PHONE");
											if (!empty($arUserResult["ID"])) {
												$arOrder["USER_ID"] = $arUserResult["ID"];
											}
										}
									}
								}
								//если пользователь найден, то все данные добавляем в заказ
								if (!empty($arOrder["USER_ID"])) {
									$emailUserIsset = true;
									$arOrder["USER_ID"] = $arUserResult["ID"];
									if (!empty($arUserResult["EMAIL"])) {
										$arUserZakazUpdate["EMAIL"] = $arUserResult["EMAIL"];
									}
									if (!empty($arUserResult["NAME"])) {
										$arUserZakazUpdate["NAME"] = $arUserResult["NAME"];
									}
									$phoneNormalLogin = Bitrix\Main\UserPhoneAuthTable::normalizePhoneNumber($arOrder["AGENT"]["CONTACT"]["PHONE"]);
									if (strpos($arUser["EMAIL"], 'buyer') !== false) {
										$arUserZakazUpdate["EMAIL"] = $phoneNormalLogin . "@" . $emServer;
									}
									if (strpos($arUser["LOGIN"], 'buyer') !== false) {
										$arUserZakazUpdate["LOGIN"] = $phoneNormalLogin;
									}
									$obUser = new CUser;
									//обновление информации
									if (!$obUser->Update($arOrder["USER_ID"], $arUserZakazUpdate, true))
										$this->strError .= "\n" . $obUser->LAST_ERROR;
								} else {
									//сюда попадаем когда по телефону не удалось ничего найти, email пустой
									$phoneNormalLogin = Bitrix\Main\UserPhoneAuthTable::normalizePhoneNumber($arOrder["AGENT"]["CONTACT"]["PHONE"]);
									$arUser["EMAIL"] = $phoneNormalLogin . "@" . $emServer;/*"buyer".time().GetRandomCode(2)*/
									$emailUserIsset = false;
								}
							}
						}
						$arErrors = array();
						//если пользователь не найден, тогда создаем его, логин - номер телефона, email - номер телефона@site.ru
						if ($emailUserIsset == false)
							$arOrder["USER_ID"] = CSaleUser::DoAutoRegisterUser($arUser["EMAIL"], $arUser["NAME"], $this->arParams["SITE_NEW_ORDERS"], $arErrors, array("XML_ID" => $arOrder["AGENT"]["ID"], "EXTERNAL_AUTH_ID" => Sale\Exchange\Entity\UserImportBase::EXTERNAL_AUTH_ID));

						$obUser = new CUser;
						$userFields[] = array();

						if ($arOrder["AGENT"]["CONTACT"]["PHONE"] <> '')
							$userFields["WORK_PHONE"] = $arOrder["AGENT"]["CONTACT"]["PHONE"];

						if (count($userFields) > 0) {
							if (!$obUser->Update($arOrder["USER_ID"], $userFields, true))
								$this->strError .= "\n" . $obUser->LAST_ERROR;
						}
					}
				}

				if (empty($arPersonTypesIDs)) {
					$dbPT = CSalePersonType::GetList(array(), array("ACTIVE" => "Y", "LIDS" => $this->arParams["SITE_NEW_ORDERS"]));
					while ($arPT = $dbPT->Fetch()) {
						$arPersonTypesIDs[] = $arPT["ID"];
					}
				}

				if (empty($arExportInfo)) {
					$dbExport = CSaleExport::GetList(array(), array("PERSON_TYPE_ID" => $arPersonTypesIDs));
					while ($arExport = $dbExport->Fetch()) {
						$arExportInfo[$arExport["PERSON_TYPE_ID"]] = unserialize($arExport["VARS"], ['allowed_classes' => false]);
					}
				}

				if (intval($arOrder["PERSON_TYPE_ID"]) <= 0) {
					foreach ($arExportInfo as $pt => $value) {
						if (
							(($value["IS_FIZ"] == "Y" && $arOrder["AGENT"]["TYPE"] == "FIZ")
								|| ($value["IS_FIZ"] == "N" && $arOrder["AGENT"]["TYPE"] != "FIZ"))
						)
							$arOrder["PERSON_TYPE_ID"] = $pt;
					}
				}

				if (intval($arOrder["PERSON_TYPE_ID"]) > 0) {
					$arAgent = $arExportInfo[$arOrder["PERSON_TYPE_ID"]];
					foreach ($arAgent as $k => $v) {
						if (
							empty($v) ||
							(
								(empty($v["VALUE"]) || $v["TYPE"] != "PROPERTY") &&
								(empty($arOrder["USER_PROPS"])
									|| (is_array($v) && is_string($v["VALUE"]) && empty($arOrder["USER_PROPS"][$v["VALUE"]]))
								)
							)
						)
							unset($arAgent[$k]);
					}

					if (intval($arOrder["USER_ID"]) > 0) {
						$orderFields = array(
							"SITE_ID" => $this->arParams["SITE_NEW_ORDERS"],
							"PERSON_TYPE_ID" => $arOrder["PERSON_TYPE_ID"],
							"PAYED" => "N",
							"CANCELED" => "N",
							"STATUS_ID" => "N",
							"PRICE" => $arOrder["AMOUNT"],
							"CURRENCY" => CSaleLang::GetLangCurrency($this->arParams["SITE_NEW_ORDERS"]),
							"USER_ID" => $arOrder["USER_ID"],
							"TAX_VALUE" => doubleval($arOrder["TAX"]["VALUE_MONEY"]),
							"COMMENTS" => $arOrder["COMMENT"],
							"BASKET_ITEMS" => array(),
							"TAX_LIST" => array(),
							"ORDER_PROP" => array(),
						);
						$arAditFields = array(
							"EXTERNAL_ORDER" => "Y",
							"ID_1C" => $arOrder["ID_1C"],
							"VERSION_1C" => $arOrder["VERSION_1C"],
							"UPDATED_1C" => "Y",
							"DATE_INSERT" => CDatabase::FormatDate($arOrder["DATE"] . " " . $arOrder["TIME"], "YYYY-MM-DD HH:MI:SS", CLang::GetDateFormat("FULL", LANG)),
						);

						foreach ($arOrder["items"] as $productID => $val) {
							$orderFields["BASKET_ITEMS"][] = $this->prepareProduct4Basket($productID, $val, false, $orderFields);
						}

						if (!empty($arOrder["TAX"])) {
							$orderFields["TAX_LIST"][] = array(
								"NAME" => $arOrder["TAX"]["NAME"],
								"IS_PERCENT" => "Y",
								"VALUE" => $arOrder["TAX"]["VALUE"],
								"VALUE_MONEY" => $arOrder["TAX"]["VALUE_MONEY"],
								"IS_IN_PRICE" => $arOrder["TAX"]["IS_IN_PRICE"],
								"CODE" => 'VAT1C',
								"APPLY_ORDER" => '100',
							);
						}

						foreach ($arAgent as $k => $v) {
							if (!empty($arOrder["ORDER_PROPS"][$k])) {
								$orderFields["ORDER_PROP"][$v["VALUE"]] = $arOrder["ORDER_PROPS"][$k];
							}
							if (empty($orderFields["ORDER_PROP"][$v["VALUE"]]) && !empty($arOrder["USER_PROPS"][$v["VALUE"]])) {
								$orderFields["ORDER_PROP"][$v["VALUE"]] = $arOrder["USER_PROPS"][$v["VALUE"]];
							}
						}

						$importSettings = Sale\Exchange\OneC\ImportSettings::getCurrent();
						$deliverySystemId = $importSettings->shipmentServiceFor(Sale\Exchange\EntityType::SHIPMENT);
						$orderFields['DELIVERY_ID'] = ($deliverySystemId ? $deliverySystemId : null);

						if ($arOrder["ID"] = $parentEntity::DoSaveOrder($orderFields, $arAditFields, 0, $arErrors)) {
							$arAditFields = array("UPDATED_1C" => "Y");
							$parentEntity::Update($arOrder["ID"], $arAditFields);

							//add/update user profile
							if (intval($arOrder["USER_PROFILE_ID"]) > 0) {
								if ($arOrder["USER_PROFILE_VERSION"] != $arOrder["AGENT"]["VERSION"])
									CSaleOrderUserProps::Update($arOrder["USER_PROFILE_ID"], array("VERSION_1C" => $arOrder["AGENT"]["VERSION"], "NAME" => $arOrder["AGENT"]["AGENT_NAME"], "USER_ID" => $arOrder["USER_ID"]));
								$dbUPV = CSaleOrderUserPropsValue::GetList(array(), array("USER_PROPS_ID" => $arOrder["USER_PROFILE_ID"]));
								while ($arUPV = $dbUPV->Fetch()) {
									$arOrder["AGENT"]["PROFILE_PROPS_VALUE"][$arUPV["ORDER_PROPS_ID"]] = array("ID" => $arUPV["ID"], "VALUE" => $arUPV["VALUE"]);
								}
							}

							if (intval($arOrder["USER_PROFILE_ID"]) <= 0 || (intval($arOrder["USER_PROFILE_ID"]) > 0 && $arOrder["USER_PROFILE_VERSION"] != $arOrder["AGENT"]["VERSION"])) {
								$dbOrderProperties = CSaleOrderProps::GetList(
									array("SORT" => "ASC"),
									array(
										"PERSON_TYPE_ID" => $arOrder["PERSON_TYPE_ID"],
										"ACTIVE" => "Y",
										"UTIL" => "N",
										"USER_PROPS" => "Y",
									),
									false,
									false,
									array("ID", "TYPE", "NAME", "CODE", "USER_PROPS", "SORT", "MULTIPLE")
								);
								while ($arOrderProperties = $dbOrderProperties->Fetch()) {
									$curVal = $orderFields["ORDER_PROP"][$arOrderProperties["ID"]];

									if ($curVal <> '') {
										if (intval($arOrder["USER_PROFILE_ID"]) <= 0) {
											$arFields = array(
												"NAME" => $arOrder["AGENT"]["AGENT_NAME"],
												"USER_ID" => $arOrder["USER_ID"],
												"PERSON_TYPE_ID" => $arOrder["PERSON_TYPE_ID"],
												"XML_ID" => $arOrder["AGENT"]["ID"],
												"VERSION_1C" => $arOrder["AGENT"]["VERSION"],
											);
											$arOrder["USER_PROFILE_ID"] = CSaleOrderUserProps::Add($arFields);
										}
										if (intval($arOrder["USER_PROFILE_ID"]) > 0) {
											$arFields = array(
												"USER_PROPS_ID" => $arOrder["USER_PROFILE_ID"],
												"ORDER_PROPS_ID" => $arOrderProperties["ID"],
												"NAME" => $arOrderProperties["NAME"],
												"VALUE" => $curVal
											);
											if (empty($arOrder["AGENT"]["PROFILE_PROPS_VALUE"][$arOrderProperties["ID"]])) {
												CSaleOrderUserPropsValue::Add($arFields);
											} elseif ($arOrder["AGENT"]["PROFILE_PROPS_VALUE"][$arOrderProperties["ID"]]["VALUE"] != $curVal) {
												CSaleOrderUserPropsValue::Update($arOrder["AGENT"]["PROFILE_PROPS_VALUE"][$arOrderProperties["ID"]]["ID"], $arFields);
											}
										}
									}
								}
							}
						} else {
							$this->strError .= "\n" . GetMessage("CC_BSC1_ORDER_ADD_PROBLEM", array("#ID#" => $arOrder["ID_1C"]));
							if (is_array($arErrors))
								$this->strError .= "\n" . implode(', ', $arErrors);
						}
					} else {
						$this->strError .= "\n" . GetMessage("CC_BSC1_ORDER_USER_PROBLEM", array("#ID#" => $arOrder["ID_1C"]));
						if (!empty($arErrors)) {
							foreach ($arErrors as $v) {
								$this->strError .= "\n" . $v["TEXT"];
							}
						}
					}
				} else {
					$this->strError .= "\n" . GetMessage("CC_BSC1_ORDER_PERSON_TYPE_PROBLEM", array("#ID#" => $arOrder["ID_1C"]));
				}
			} else {
				$this->strError .= "\n" . GetMessage("CC_BSC1_ORDER_NO_AGENT_ID", array("#ID#" => $arOrder["ID_1C"]));
			}
		}
	}
}
