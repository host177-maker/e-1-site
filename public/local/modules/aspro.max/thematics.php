<?php
/**
 * Aspro:Max module thematics
 * @copyright 2019 Aspro
 */

IncludeModuleLangFile(__FILE__);
$moduleClass = 'CMax';

// initialize module parametrs list and default values
$moduleClass::$arThematicsList = array(
	'UNIVERSAL' => array(
		'CODE' => 'UNIVERSAL',
		'TITLE' => GetMessage('THEMATIC_UNIVERSAL_TITLE'),
		'DESCRIPTION' => GetMessage('THEMATIC_UNIVERSAL_DESCRIPTION'),
		'PREVIEW_PICTURE' => '/bitrix/images/aspro.max/themes/thematic_preview_universal.png',
		'URL' => '',
		'OPTIONS' => array(
		),
		'PRESETS' => array(
			'DEFAULT' => 595,
			'LIST' => array(
				0 => 595,
				1 => 931,
				2 => 461,
				3 => 850,
			),
		),
	),
	'VOLT' => array(
		'CODE' => 'VOLT',
		'TITLE' => GetMessage('THEMATIC_VOLT_TITLE'),
		'DESCRIPTION' => GetMessage('THEMATIC_VOLT_DESCRIPTION'),
		'PREVIEW_PICTURE' => '/bitrix/images/aspro.max/themes/thematic_preview_volt.png',
		'URL' => '',
		'OPTIONS' => array(
		),
		'PRESETS' => array(
			'DEFAULT' => 124,
			'LIST' => array(
				0 => 124,
				1 => 403,
			),
		),
	),
	'MODA' => array(
		'CODE' => 'MODA',
		'TITLE' => GetMessage('THEMATIC_MODA_TITLE'),
		'DESCRIPTION' => GetMessage('THEMATIC_MODA_DESCRIPTION'),
		'PREVIEW_PICTURE' => '/bitrix/images/aspro.max/themes/thematic_preview_moda.png',
		'URL' => '',
		'OPTIONS' => array(
		),
		'PRESETS' => array(
			'DEFAULT' => 340,
			'LIST' => array(
				0 => 340,
				1 => 437,
				2 => 253,
				3 => 424,
				4 => 479,
				5 => 131,
				6 => 939,
				7 => 562,
				8 => 588,
			),
		),
	),
	'HOME' => array(
		'CODE' => 'HOME',
		'TITLE' => GetMessage('THEMATIC_HOME_TITLE'),
		'DESCRIPTION' => GetMessage('THEMATIC_HOME_DESCRIPTION'),
		'PREVIEW_PICTURE' => '/bitrix/images/aspro.max/themes/thematic_preview_housegoods.png',
		'URL' => '',
		'OPTIONS' => array(
		),
		'PRESETS' => array(
			'DEFAULT' => 695,
			'LIST' => array(
				0 => 695,
				1 => 594,
				2 => 895,
				3 => 701,
				4 => 826,
				5 => 555,
				6 => 103,
			),
		),
	),
);