<?

namespace E_1\EventHandler;

require_once $_SERVER["DOCUMENT_ROOT"] . '/../vendor/autoload.php';

use \App\Bitrix24\Bitrix24API;
use \App\Bitrix24\Bitrix24APIException;

class CrmSendForm
{
    /**
     * Метод добавляет в CRM B24 в сделки заявки с форм
     * @param array 
     * @return array
     */

    public static function crmSendDataForm(&$event, &$lid, &$arFields)
    {
        if ($event == 'FORM_FILLING_FEEDBACK_KKAP7') {
            $webhookURL = \Cosmos\Config::getInstance()->getParam('e1_crm_bx24')['crm_webhook_url'];
            $bx24 = new Bitrix24API($webhookURL);
            $iAssignedUserId = 1;
            $arFieldsDeal["TITLE"] = 'Новая сделка c формы "' . $arFields['RS_FORM_NAME'] . '"';
            $arFieldsDeal["CATEGORY_ID"] = 1;
            $arFieldsDeal["COMMENTS"] = 'Форма: ' . $arFields['RS_FORM_NAME'] . '<br/> Email: ' . $arFields['EMAIL_RAW'] . '<br/> Имя: ' . $arFields['CLIENT_NAME'] . '<br/> Телефон: ' . $arFields['PHONE'] . '<br/> Комментарий: ' . $arFields['POST'] . '<br/> Страница: ' . $arFields['PAGE_URL'];
            $arFieldsDeal["STAGE_ID"] = 'Заявка с сайта';
            $arFieldsDeal["OPENED"] = "Y";
            $arFieldsDeal["SOURCE_ID"] = "WEB";
            $arFieldsDeal["ASSIGNED_BY_ID"] = $iAssignedUserId;

            file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/b24.log', print_r($arFieldsDeal, true), FILE_APPEND);

            // Добавляем новую сделку
            $dealId = $bx24->addDeal($arFieldsDeal);
        }
    }

    /**
     * Метод возвращает пользовательские поля из B24
     * @param array 
     * @return array
     */
    public function getUsrFields()
    {
        $webhookURL = \Cosmos\Config::getInstance()->getParam('e1_crm_bx24')['crm_webhook_url'];
        $bx24 = new Bitrix24API($webhookURL);
        $aResult = $bx24->getDealFields();
        return $aResult;
    }
}
