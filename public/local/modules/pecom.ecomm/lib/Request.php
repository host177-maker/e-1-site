<?php

namespace Pec\Delivery;

use Bitrix\Main\Config\Option;

class Request
{
    protected $login;
    protected $api_key;
    protected $sdk;
    protected $logFile;
    protected $logMessage;
    protected $module_id = 'pecom.ecomm';

    public function __construct()
    {
        require_once('pec-api/pecom_kabinet.php');
        $this->login = Option::get($this->module_id, "PEC_API_LOGIN", '');
        $this->api_key = Option::get($this->module_id, "PEC_API_KEY", '');
        $this->sdk = new \PecomKabinet($this->login, $this->api_key);
        $this->logFile = __DIR__ . '/log/' . date('Y-m-d') . '.log';
        $dirLog = dirname($this->logFile);
        if (!dir($dirLog)) {
            mkdir($dirLog);
        }
        $this->logMessage = "\r\n-------- start " . date('H:i:s') . "\r\n";
    }

    public function __destruct()
    {
        $this->sdk->close();
        $this->logMessage .= "-------- finish " . date('H:i:s') . "\r\n";
        file_put_contents($this->logFile, $this->logMessage, FILE_APPEND);
    }

    public function getAllStatus()
    {
        try {
            $result = $this->sdk->call('cargos', 'STATUSTABLE', []);
        } catch (\Exception $e) {
            $result['error'] = ['message' => $e->getMessage()];
            $this->addLog('cargos/STATUSTABLE', [], $result['error']);
        }
        $this->addLog('cargos/STATUSTABLE', [], $result);

        return $result;
    }

    public function getPecStatus(array $pecId)
    {
        try {
            $result = $this->sdk->call('cargos', 'STATUS', [
                'cargoCodes' => $pecId,
            ]);
        } catch (\Exception $e) {
            $result = ['message' => $e->getMessage()];
        }
        $this->addLog('cargos/STATUS', ['cargoCodes' => $pecId], $result);

        return $result;
    }

    public function getPecDitail(string $pecId)
    {
        $pecId = str_replace('/', '\/', $pecId);
        try {
            $result = $this->sdk->call('cargos', 'DETAILS', [
                'cargoCode' => $pecId,
            ]);
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage()];
        }
        $this->addLog('cargos/DETAILS', ['cargoCode' => $pecId,], $result);

        return $result;
    }

    public function getBarCode(string $pecId)
    {
        try {
            $result = $this->sdk->call('cargos', 'STATUS', [
                'cargoCodes' => [$pecId],
            ]);
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage()];
        }
        $this->addLog('cargos/STATUS', '', $result);

        return $result->cargos[0]->cargo->cargoBarCode;
    }

    public function pickupNetworkSubmit(array $data)
    {
        try {
            $result = $this->sdk->call('cargopickupnetwork', 'SUBMIT', $data);
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage()];
        }
        $this->addLog('cargopickupnetwork/SUBMIT', $data, $result);

        return $result;
    }

    public function pickupSubmit(array $data)
    {
        try {
            $result = $this->sdk->call('cargopickup', 'SUBMIT', $data);
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage()];
        }
        $this->addLog('cargopickup/SUBMIT', $data, $result);

        return $result;
    }

    public function preRegistration(array $data)
    {
        try {
            $result = $this->sdk->call('preregistration', 'SUBMIT', $data);
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage()];
        }
        $this->addLog('preregistration/SUBMIT', $data, $result);

        return $result;
    }

    private function addLog($method, $data, $responce)
    {
        $this->logMessage .= 'method: ';
        $this->logMessage .= print_r($method, 1);
        $this->logMessage .= "\r\n";
        $this->logMessage .= 'data: ';
        $this->logMessage .= print_r($data, 1);
        $this->logMessage .= "\r\n";
        $this->logMessage .= 'responce: ';
        $this->logMessage .= print_r($responce, 1);
        $this->logMessage .= "\r\n";
    }
}