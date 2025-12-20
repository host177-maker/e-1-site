<?php

namespace Bitrix\Sale\Delivery\ExtraServices;


use Bitrix\Sale\Internals\Input,
	Bitrix\Main\Page\Asset,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class COwnCourierDeliveryExtraServices extends Base
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
		return 'Стоимость подъёма на этаж';
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
		return 'Цена';
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

		Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . "/js/own-courier-delivery.js");
		Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . "/css/own-courier-delivery.css");

		if($prefix <> '')
			$name = $prefix;
		else
			$name = $this->id;

		if(!$value)
			$value = $this->value;

		$sHtml = '';

		$sHtml = '<div class="delivery-lifting-cost">';

		$sHtml .= '<div class="floor-rise-need-block"><div class="switch_block onoff filter"><input type="checkbox" name="buy_switch_services" id="floor-rise-need-input"><label for="floor-rise-need-input"> &nbsp;</label></div>';
		$sHtml .= '<div class="switch_block_desc">Нужен подъем на этаж</div></div>';

		$sHtml .= '<div class="elevator-block "><div class="switch_block onoff filter"><input type="checkbox" name="buy_switch_services" id="elevator-input"><label for="elevator-input"> &nbsp;</label></div>';
		$sHtml .= '<div class="switch_block_desc">Есть грузовой лифт</div></div>';


		$sHtml .= '<div class="switch_block_counter" data-item="' . $name . '">';
		$sHtml .= '<span class="minus dark-color" data-type="minus"><i class="svg inline  svg-inline-wish ncolor colored1" aria-hidden="true"><svg width="11" height="1" viewBox="0 0 11 1"><rect width="11" height="1" rx="0.5" ry="0.5"></rect></svg></i></span>';
		$sHtml .= '<input type="text" name="delivery-lifting-input" value="' . $value . '" class="form-control">';
		$sHtml .= '<input type="hidden" name="' . $name . '" value="' . $value . '" onchange="' . $this->params['ONCHANGE'] . '" class="form-control">';
		$sHtml .= '<span class="plus dark-color" data-type="plus"><i class="svg inline  svg-inline-wish ncolor colored1" aria-hidden="true"><svg width="11" height="11" viewBox="0 0 11 11"><path d="M1034.5,193H1030v4.5a0.5,0.5,0,0,1-1,0V193h-4.5a0.5,0.5,0,0,1,0-1h4.5v-4.5a0.5,0.5,0,0,1,1,0V192h4.5A0.5,0.5,0,0,1,1034.5,193Z" transform="translate(-1024 -187)"></path></svg></i></span></div><label class="switch_block_counter_label">Мой этаж</label>';

		$sHtml .= '</div>';
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