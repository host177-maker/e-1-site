<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

// use $thematic for a list of different services
$thematic = isset($_REQUEST['__wiz_'.ASPRO_PARTNER_NAME.'_'.ASPRO_MODULE_NAME_SHORT.'_thematicCODE']) ? strtolower($_REQUEST['__wiz_'.ASPRO_PARTNER_NAME.'_'.ASPRO_MODULE_NAME_SHORT.'_thematicCODE']) : 'universal';

include('service_'.$thematic.'.php');