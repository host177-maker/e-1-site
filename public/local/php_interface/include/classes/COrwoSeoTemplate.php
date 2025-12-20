<?
namespace COrwoSeoTemplate;
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/lib/template/functions/fabric.php");
class OnTemplateGetFunctionClass extends \Bitrix\Iblock\Template\Functions\FunctionBase
{
	//Обработчик события на вход получает имя требуемой функции
	//парсер её нашел в строке SEO
	public static function eventHandler($event)
	{
		$parameters = $event->getParameters();
		$functionName = $parameters[0];
		if ($functionName === "orwominpricesection")
		{
			//обработчик должен вернуть SUCCESS и имя класса
			//который будет отвечать за вычисления
			return new \Bitrix\Main\EventResult(
			   \Bitrix\Main\EventResult::SUCCESS,
			   "\\COrwoSeoTemplate\\SectionMinPrice"
			);
		}
	}
}
 ?>