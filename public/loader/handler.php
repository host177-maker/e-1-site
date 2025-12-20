<?
require_once(__DIR__ ."/../bitrix/modules/main/include/prolog_before.php");
if(
	!CModule::IncludeModule('main') || 
	!CModule::IncludeModule('sale') || 
	!CModule::IncludeModule('catalog') || 
	!CModule::IncludeModule('iblock')
){
	die('bitrix error');
}	
global $DB;


function parse_csv_file($csvfile) {
    $csv = Array();
    $rowcount = 0;
    if (($handle = fopen($csvfile, "r")) !== FALSE) {
			
        $max_line_length = defined('MAX_LINE_LENGTH') ? MAX_LINE_LENGTH : 10000;
        $header = fgetcsv($handle, $max_line_length);
        $header_colcount = count($header);

        while (($row = fgetcsv($handle, $max_line_length)) !== FALSE) {
            $row_colcount = count($row);
            if ($row_colcount == $header_colcount) {
                $entry = array_combine($header, $row);
                $csv[] = $entry;
            }
            else {
                error_log("csvreader: Invalid number of columns at line " . ($rowcount + 2) . " (row " . ($rowcount + 1) . "). Expected=$header_colcount Got=$row_colcount");
                return null;
            }
            $rowcount++;
        }
        fclose($handle);
    }
    else {
        error_log("csvreader: Could not read CSV \"$csvfile\"");
        return null;
    }
    return $csv;
}
function getTree($dataset) {
	$tree = array();

	foreach ($dataset as $id => &$node) {    
			//Если нет вложений
			if (!$node['parent_id']){
					$tree[$id] = &$node;
			}else{ 
					//Если есть потомки то перебераем массив
					$dataset[$node['parent_id']]['childs'][$id] = &$node;
			}
	}
	return $tree;
}

$fullPath = $_SERVER["DOCUMENT_ROOT"]."/loader/files/photo_printing_catalog.csv";
$arResult = parse_csv_file($fullPath);

$arResult = array_chunk($arResult, 100);



$el = new CIBlockElement;
foreach($arResult[0] as $key => $arItem){
	
	$name = $arItem["Название"];
	$code = $arItem["Символьный код"];
	$catLvl1 = $arItem["Тип шкафа"];
	$catLvl1 = preg_replace("/ \[[0-9]+\]/", "",$catLvl1);
	$catLvl2 = $arItem["Категории фотопечати"];
	$catLvl2 = preg_replace("/ \[[0-9]+\]/", "",$catLvl2);
	$catLvl2 = str_replace("Архитектура / город", "Архитектура, город", $catLvl2);	

  $previewImg = CFile::MakeFileArray($arItem["Картинка для анонса"]);
	
	$propDOOR2_1 = CFile::MakeFileArray($arItem["1000мм"]);
	$propDOOR2_2 = CFile::MakeFileArray($arItem["1200мм"]);
	$propDOOR2_3 = CFile::MakeFileArray($arItem["1400мм"]);
	$propDOOR2_4 = CFile::MakeFileArray($arItem["1600мм"]);
	
	$propDOOR3_1 = CFile::MakeFileArray($arItem["1500мм"]);
	$propDOOR3_2 = CFile::MakeFileArray($arItem["1800мм"]);
	$propDOOR3_3 = CFile::MakeFileArray($arItem["2100мм"]);
	$propDOOR3_4 = CFile::MakeFileArray($arItem["2400мм"]);    

	$rsResult1 = CIBlockSection::GetList(
		array("SORT" => "ASC"), 
		array(
			"IBLOCK_ID" => 39,
			"NAME" => $catLvl1,
		),  
		false, 
		array("ID")
	);
	
	if($arSection1 = $rsResult1 -> GetNext()){
			$rsResult2 = CIBlockSection::GetList(
			array("SORT" => "ASC"), 
			array(
				"IBLOCK_ID" => 39,
				"SECTION_ID" => $arSection1["ID"],
				"NAME" => $catLvl2,
			),  
			false, 
			array("ID", "NAME")
		);
		if($arSection2 = $rsResult2 -> GetNext()){
		
			$PROP = array(
				'698'=>array($propDOOR2_1, $propDOOR2_2, $propDOOR2_3, $propDOOR2_4),
				'699'=>array($propDOOR3_1, $propDOOR3_2, $propDOOR3_3, $propDOOR3_4),
			);			
			
			$arLoadProductArray = Array(
				"MODIFIED_BY"       => 1, 
				"IBLOCK_SECTION"    => array($arSection1["ID"], $arSection2["ID"]),
				"IBLOCK_ID"         => 39,
				"NAME"              => $name,
				"CODE"              => $code,
				"ACTIVE"            => "Y",
				"PREVIEW_PICTURE"   => $previewImg,
				"PROPERTY_VALUES"   => $PROP,
			);			
			
 //var_dump($arLoadProductArray);		
			
			
/* 			$id = $el->Add($arLoadProductArray);
	var_dump($id);   */
		}	
	} 	
}
























