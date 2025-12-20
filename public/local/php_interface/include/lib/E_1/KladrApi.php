<?php
namespace E_1;
use Cosmos\Config;

class KladrApi
{
    private $sUrl;
    private $sToken = '';
    private $iResponseTimeout = 10;
    private $bDebug = false;

    public function __construct($sFormType = '', $bDebug = false)
    {
        $aConfig = Config::getInstance()->getParam("KLADR");

        $this->sUrl = (isset($aConfig['URL']) ? $aConfig['URL'] : '');
        $this->sToken = (isset($aConfig['TOKEN']) ? $aConfig['TOKEN'] : '');
        $this->bDebug = $bDebug;

        if (isset($aConfig['RESPONSE_TIMEOUT'])) {
            $this->iResponseTimeout = $aConfig['RESPONSE_TIMEOUT'];
        }
    }

    /**
     * @param $name
     * @param array $args
     * @return bool|mixed
     * @throws Exception
     */
    public function __call($name, array $args)
    {
        if (method_exists($this, $name)) {
            try {
                return call_user_func_array(array($this, $name), $args);
            } catch (\Exception $e) {
                self::saveToLog(__CLASS__, $this->sFormType . '_' . $name, $e->getMessage());
                throw new \Exception($e->getMessage());
            }
        }

        return false;
    }

    protected function getCityApi($aParams = array())
    {
        $sMethod = !empty(Config::getInstance()->getParam("KLADR")['API']) ? Config::getInstance()->getParam("KLADR")['API'] : 'api.php';
        $aParams['token'] = $this->sToken;
        $aParams['contentType'] = 'city';
        $aParams['limit'] = '1';
        $aParams['withParent'] = '1';
        $aRequest = $this->_request($sMethod, $aParams, 'GET');
        return $aRequest;
    }

    protected function getAddressApi($aParams = array())
    {
        $sMethod = !empty(Config::getInstance()->getParam("KLADR")['API']) ? Config::getInstance()->getParam("KLADR")['API'] : 'api.php';
        $aParams['token'] = $this->sToken;
        $aParams['contentType'] = 'street';
        $aParams['limit'] = '10';
        $aRequest = $this->_request($sMethod, $aParams, 'GET');
        return $aRequest;
    }

    /**
     * @param $sMethod
     * @param bool $bNeedAuth
     * @param array $aParams
     * @param string $sType
     * @return mixed
     * @throws Exception
     */
    private function _request($sMethod, $aParams = array(), $sType = 'GET')
    {
        $sMethod = trim($sMethod, '/');
        $sRequestUrl = $this->sUrl . $sMethod;

        if ($sType === 'GET') {
            if (!empty($aParams)) {
                $sRequestUrl .= '?' . http_build_query($aParams);
            }
        }

        $aHeaders = array(
            'Content-Type: application/json'
        );

        if ($this->bDebug) {
            self::saveToLog(__CLASS__, $this->sFormType . '_' . $sMethod, json_encode($aParams), 'INFO');
        }

        $oCh = curl_init();
        curl_setopt($oCh, CURLOPT_URL, $sRequestUrl);
        curl_setopt($oCh, CURLOPT_HTTPHEADER, $aHeaders);
        curl_setopt($oCh, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($oCh, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($oCh, CURLOPT_TIMEOUT, $this->iResponseTimeout);

        if ($sType === 'POST') {
            curl_setopt($oCh, CURLOPT_POST, true);
            if (!empty($aParams)) {
                curl_setopt($oCh, CURLOPT_POSTFIELDS, json_encode($aParams));
            }
        }

        $mRawData = curl_exec($oCh);
        $sCurlError = curl_error($oCh);
        $iHttpCode = curl_getinfo($oCh, CURLINFO_HTTP_CODE);
        if ($mRawData === false || !empty($sCurlError) || !in_array($iHttpCode, array(200, 201))) {
            throw new \Exception('Не удалось получить данные. RequestURL: [' . $sRequestUrl . ']. HTTP статус: [' . $iHttpCode . ']. Ошибка curl: [' . $sCurlError . ']. Данные: [' . $mRawData . ']');
        }
        curl_close($oCh);

        $aData = json_decode($mRawData, true);
        $sJsonError = '';
        switch (json_last_error()) {
            case JSON_ERROR_DEPTH:
                $sJsonError = 'Достигнута максимальная глубина стека';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $sJsonError = 'Неверный или не корректный JSON';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $sJsonError = 'Ошибка управляющего символа, возможно неверная кодировка';
                break;
            case JSON_ERROR_SYNTAX:
                $sJsonError = 'Синтаксическая ошибка, неверный формат JSON';
                break;
            case JSON_ERROR_UTF8:
                $sJsonError = 'Некорректные символы UTF-8, возможно неверная кодировка';
                break;
        }
        if (!empty($sJsonError)) {
            throw new \Exception('Не удалось декодировать данные. RequestURL: [' . $sRequestUrl . ']. Ошибка json: [' . $sJsonError . ']. Данные: [' . $mRawData . ']');
        }

        return $aData;
    }

    /**
     * Запись в лог
     * @param string $sTypeId Тип
     * @param string $sModuleId Название модуля или класса
     * @param string $sDesc Описание
     * @param string $sType Тип лога
     */
    static public function saveToLog($sTypeId, $sModuleId, $sDesc, $sType = 'ERROR')
    {
        $sDesc = explode('. ', $sDesc);
        $sDesc = implode('<br>', $sDesc);

        $aLog = array(
            'SEVERITY'      => $sType,
            'AUDIT_TYPE_ID' => (string)$sTypeId,
            'MODULE_ID'     => (string)$sModuleId,
            'ITEM_ID'       => 0,
            'REMOTE_ADDR'   => self::getIp(),
            'USER_AGENT'    => (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : ''),
            'REQUEST_URI'   => '',
            'SITE_ID'       => SITE_ID,
            'USER_ID'       => '',
            'GUEST_ID'      => '',
            'DESCRIPTION'   => (string)$sDesc
        );
        \CEventLog::Add($aLog);
    }

    /**
     * Получание IP адреса пользователя
     * @return string
     */
    static public function getIp()
    {
        $ips = array();
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips[] = trim(strtok($_SERVER['HTTP_X_FORWARDED_FOR'], ','));
        }

        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ips[] = $_SERVER['HTTP_CLIENT_IP'];
        }

        if (isset($_SERVER['REMOTE_ADDR'])) {
            $ips[] = $_SERVER['REMOTE_ADDR'];
        }

        if (isset($_SERVER['HTTP_X_REAL_IP'])) {
            $ips[] = $_SERVER['HTTP_X_REAL_IP'];
        }

        // проверяем ip-адреса на валидность начиная с приоритетного.
        foreach ($ips as &$ip) {
            // если ip валидный обрываем цикл и возвращаем его
            if (preg_match("#^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$#", $ip)) {
                return $ip;
            }
        }

        return '';
    }
}
