<?php

namespace E_1\Api;

class Base
{

    public $data = null;
    public $sMessage = '';
    public $bError = false;

    public function __construct()
    {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'DELETE':
            case 'POST':
            case 'PUT':
            case 'PATCH':
                $data = file_get_contents('php://input');
                $this->data = json_decode($data, true);
                break;
            
            default:
                $this->data = \Bitrix\Main\Context::getCurrent()->getRequest()->getQueryList();
                break;
        }
    }

    public function __call($name, $args)
    {
        $this->sMessage = "Метод {$name} не найден";

        $this->end();
    }

    public function outputJson($data)
    {
        header('Content-Type: application/json; charset=utf-8');
        die(json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    public function end()
    {
        if ($this->bError) {
            header('HTTP/1.1 400 Bad Request');
        }

        die(json_encode([
            'message' => $this->sMessage,
        ], JSON_UNESCAPED_UNICODE));
    }

}
