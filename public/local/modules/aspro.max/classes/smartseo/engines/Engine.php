<?php

namespace Aspro\Max\Smartseo\Engines;

use Aspro\Max\Smartseo;

class Engine
{
    private $errors = [];
    private $result = [];

    function __construct()
    {
    }

    public function setResult(array $values)
    {
        $this->result = $values;
    }

    public function getResult($code = null)
    {
        if($code && isset($this->result[$code])) {
            return $this->result[$code];
        }

        return $this->result;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function hasErrors()
    {
        return $this->errors ? true : false;
    }

    public function setErrors($errors)
    {
        if (is_array($errors)) {
            $this->errors = array_map(function($item) {
                return $item;
            }, $errors);
        } else {
            $this->errors[] = $errors;
        }
    }

    public function addError($error)
    {
        $this->errors[] = $error;
    }
}
