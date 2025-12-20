<?
namespace Sale\Handlers\Delivery;

use Bitrix\Sale\Delivery\CalculationResult;
use Bitrix\Sale\Delivery\Services\Base;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Entity;
use Bitrix\Sale\Result;
use Bitrix\Main\Context;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
Loc::loadMessages(__FILE__);

class COwnCourierDelivery extends Base
{
	public static function getClassTitle(){
		return 'Собственная служба доставки';
	}
		
	public static function getClassDescription(){
		return 'Доставка, стоимость которой зависит региона';
	}

	public function calculate(\Bitrix\Sale\Shipment $shipment = null, $extraServices = array()) // null for compability with old configurable services api
	{
		global $arRegion;
		if($shipment && !$shipment->getCollection())
		{
			$result = new Delivery\CalculationResult();
			$result->addError(new Error('\Bitrix\Sale\Delivery\Services\Base::calculate() can\'t calculate empty shipment!'));
			return $result;
		}

		$result = $this->calculateConcrete($shipment);

		if($shipment)
		{
			if(empty($extraServices))
				$extraServices = $shipment->getExtraServices();

			$this->extraServices->setValues($extraServices);
			$this->extraServices->setOperationCurrency($shipment->getCurrency());
			$extraServicePrice = $this->extraServices->getTotalCostShipment($shipment);

			if(floatval($extraServicePrice) > 0)
				$result->setExtraServicesPrice($extraServicePrice);
		}

		$eventParams = array(
			"RESULT" => $result,
			"SHIPMENT" => $shipment,
			"DELIVERY_ID" => $this->id
		);

		$event = new Event('sale', self::EVENT_ON_CALCULATE, $eventParams);
		$event->send();
		$resultList = $event->getResults();

		if (is_array($resultList) && !empty($resultList))
		{
			foreach ($resultList as &$eventResult)
			{
				if ($eventResult->getType() != EventResult::SUCCESS)
					continue;

				$params = $eventResult->getParameters();

				if(isset($params["RESULT"]))
					$result = $params["RESULT"];
			}
		}

		return $result;
	}

		
	protected function calculateConcrete(\Bitrix\Sale\Shipment $shipment){
		$result = new CalculationResult();
		//костыль, которые после всех проверок нужно будет все ниже закомментировать или оставить для совместимости со старой версией Битрикс	
		if ($_SERVER['REQUEST_URI'] !== '/order/save.php') {
			//$result->setPeriodDescription('1 день');

			//$result->setExtraServicesPrice(50);
			$order = $shipment->getCollection()->getOrder();
			$props = $order->getPropertyCollection(); 
			$locationCode = $props->getDeliveryLocation()->getValue();

			global $arDeliveryRegion;
			$arDeliveryRegion = array();
			if ($loc = \Bitrix\Sale\Location\LocationTable::getByCode($locationCode, 
				array(
					'filter' => array('=NAME.LANGUAGE_ID' => LANGUAGE_ID),
					'select' => array('*', 'NAME_RU' => 'NAME.NAME')
				))->fetch()) 
			{
				$arRegionSelect = Array("IBLOCK_ID", "ID", "NAME");
				$arRegionFilter = Array("IBLOCK_ID" => \CMaxRegionality::getRegionIBlockID(), "ACTIVE" => "Y", "PROPERTY_LOCATION_LINK" => $loc['ID']);
				$dbRegion = \CIBlockElement::GetList(array("sort" => "asc"), $arRegionFilter, false, false, $arRegionSelect);
				if($obRegion = $dbRegion->GetNextElement())
				{
					$arRegionFields = $obRegion->GetFields();
					$arRegionFields['DELIVERY_REGION'] = $obRegion->GetProperty('REGION_TAG_DELIVERY_REGION');
					$arRegionFields['DELIVERY_AMOUNT'] = $obRegion->GetProperty('DELIVERY_AMOUNT');
					//если в регионах существует свойство DELIVERY_AMOUNT Сумма доставки, то устанавливаем его
					if (!empty($arRegionFields['DELIVERY_AMOUNT']["VALUE"])) {
						$price = floatval($arRegionFields['DELIVERY_AMOUNT']["VALUE"]);
						$result->setDeliveryPrice(roundEx($price, 2));
					} else {
						if(!empty($this->config["MAIN"]["PRICE_" . $arRegionFields['DELIVERY_REGION']['VALUE_ENUM_ID']])){
							$price = floatval($this->config["MAIN"]["PRICE_" . $arRegionFields['DELIVERY_REGION']['VALUE_ENUM_ID']]);
							$result->setDeliveryPrice(roundEx($price, 2));
						}
						else{
							$price = floatval($this->config["MAIN"]["PRICE_360"]);
							$result->setDeliveryPrice(roundEx($price, 2));
						}
					}

					//$result->setExtraServicesPrice(50);
					$arDeliveryRegion = $arRegionFields;
				}
				else{
					$result->addError(new Entity\EntityError(Loc::getMessage("SALE_DLVR_HANDL_GROUP_ERROR_CALCULATION"), 'DELIVERY_CALCULATION'));
				}

			}
		}
		//return false;
		return $result;
	}
		
	protected function getConfigStructure(){
		$arPropertyEnums = array();
		$dbPropertyEnums = \CIBlockPropertyEnum::GetList(array("DEF"=>"DESC", "SORT"=>"ASC"), array("IBLOCK_ID" => 5, "CODE" => "REGION_TAG_DELIVERY_REGION"));
		while($tmpPropertyEnums = $dbPropertyEnums->GetNext())
		{
			$arPropertyEnums["PRICE_" . $tmpPropertyEnums["ID"]] = [
				"TYPE" => "NUMBER",
				"MIN" => 0,
				"NAME" => 'Доставка "' . $tmpPropertyEnums["VALUE"] . '"'
			];
		}

		return array(
			"MAIN" => array(
				"TITLE" => 'Настройка обработчика',
				"DESCRIPTION" => 'Настройка обработчика',
				"ITEMS" => $arPropertyEnums
			)
		);
	}
		
	public function isCalculatePriceImmediately(){
		return true;
	}
		
	public static function whetherAdminExtraServicesShow(){
		return true;
	}

	public function isCompatible(\Bitrix\Sale\Shipment $shipment)
	{
		global $APPLICATION;
		$context = \Bitrix\Main\Application::getInstance()->getContext();
        $request = $context->getRequest();
		//костыль, которые после всех проверок нужно будет все ниже закомментировать или оставить для совместимости со старой версией Битрикс
		if ($_SERVER['REQUEST_URI'] !== '/order/save.php') {
			$calcResult = self::calculateConcrete($shipment);
			return $calcResult->isSuccess();
		}
	}
}
?>