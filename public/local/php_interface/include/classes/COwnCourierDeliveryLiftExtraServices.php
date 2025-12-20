<?php

namespace Bitrix\Sale\Delivery\ExtraServices;


use Bitrix\Sale\Internals\Input,
	Bitrix\Main\Page\Asset,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class COwnCourierDeliveryLiftExtraServices extends Base
{

	public function __construct($id, array $structure, $currency, $value = null, array $additionalParams = array())
	{
		if(isset($structure["PARAMS"]["PRICE"]))
			$structure["PARAMS"]["ONCHANGE"] = $this->createJSOnchange($id, $structure["PARAMS"]["PRICE"]);

		parent::__construct($id, $structure, $currency, $value);
		$this->params["TYPE"] = "STRING";
		$this->params["CUSTOM"] = "Y";
	}

    public static function getClassTitle()
	{
		return 'Стоимость подъёма на лифте';
	}

	public function setValue($value)
	{
		$this->value = intval($value) >= 0 ? intval($value) : 0;
	}

	public function getCost()
	{
		return floatval($this->getPrice())*floatval($this->value);
	}

	public static function getAdminParamsName()
	{
		return 'Цена подъема на лифте';
	}

	public static function getAdminParamsControl($name, array $params = array(), $currency = "")
	{
		if(!empty($params["PARAMS"]["PRICE"]))
			$price = roundEx(floatval($params["PARAMS"]["PRICE"]), SALE_VALUE_PRECISION);
		else
			$price = 0;

		return '<input type="text" name="'.$name.'[PARAMS][PRICE]" value="'.$price.'">'.($currency <> '' ? " (".htmlspecialcharsbx($currency).")" : "");
	}

	public function setOperatingCurrency($currency)
	{
		$this->params["ONCHANGE"] = $this->createJSOnchange($this->id, $this->getPrice());
		parent::setOperatingCurrency($currency);
	}

	public function getEditControl($prefix = "", $value = false)
	{

		if($prefix <> '')
			$name = $prefix;
		else
			$name = $this->id;

		if(!$value)
			$value = $this->value;

		$sHtml = '';
		$sHtml .= '<input data-id="liftPriceUpdate" type="hidden" name="' . $name . '" value="' . $value . '" onchange="' . $this->params['ONCHANGE'] . '" class="form-control">';
		return $sHtml;

		//return Input\Manager::getEditHtml($name, $this->params, $value);
	}

	public function getAdminDefaultControl($prefix = "", $value = false)
	{
		return parent::getEditControl($prefix, $value);
	}

	protected function createJSOnchange($id, $price)
	{
		$price = roundEx(floatval($price), SALE_VALUE_PRECISION);
		return "BX.onCustomEvent('onDeliveryExtraServiceValueChange', [{'id' : '".$id."', 'value': this.value, 'price': this.value*parseFloat('".$price."')}]);";
	}
}