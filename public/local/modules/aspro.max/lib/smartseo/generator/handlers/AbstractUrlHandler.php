<?php

namespace Aspro\Max\Smartseo\Generator\Handlers;

abstract class AbstractUrlHandler
{
    protected $tokens = [];
    protected $initialParams = [];
    
    private $errors = [];
    

    public function setTokens(array $value) {
       $this->tokens = $value;
    }
    
    public function getTokens() {
        return $this->tokens;
    }
    
    public function setInitialParams($params) {
        $this->initialParams = array_merge($this->initialParams, $params);        
    }
    
    public function getInitialParams() {
        return $this->initialParams;        
    }    

    public function validateInitialParams()
    {
       return true;
    }
    
    public function getErrors()
    {
        return $this->errors;
    }

    public function hasErrors()
    {
        return $this->errors
          ? true
          : false;
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

    

    abstract public function generateResult(&$result);    
}
