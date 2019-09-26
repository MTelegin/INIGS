<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
CModule::IncludeModule('iblock');
CModule::IncludeModule('sale');
?>
<?
	global $DB;
	
	$hours = (int)date('H');
	$minuts = (int)date('i');
	$seconds = (int)date('s');
	$only_absent = true;
	
	//$fout = fopen('/articul/articul.csv', 'a+');
	
	$file_imp = $_SERVER[DOCUMENT_ROOT]."/test/articul/jonnesway_articuls.csv";
	
	fwrite("file_imp = ".$file_imp."<br>");
	//fwrite($fout, 'File = '.$file_imp.PHP_EOL);
	
	$f = fopen($file_imp, "r") or die("Ошибка!");
	//$k = 2;
	
	$arSelect = Array(
		"ID",
		"NAME",
		"CATALOG_QUANTITY",
		"PRICE"
	);
	

	for ($i=0; $data = fgetcsv($f, 30, ";"); $i<10)
	{
		$i = $i + 1;
		
		$val_code = (string)$data[0];
		//echo "i = ".(string)$i.", val_code = ".$val_code."<br>";
		
		$ind = 0;
		//$query_st = "select * from b_iblock_element_property prop where prop.IBLOCK_PROPERTY_ID = 7498 && prop.VALUE = ".$val_code;
		$query_st = "select el.NAME, el.ID, el.IBLOCK_ID from b_iblock_element_property prop
			LEFT JOIN b_iblock_element el ON el.ID = prop.IBLOCK_ELEMENT_ID && el.IBLOCK_ID = 95
			where prop.IBLOCK_PROPERTY_ID = 7498 && prop.VALUE = ".$val_code;
		
		$rows = $DB->Query($query_st);
		
		$kol = 0;
		$st = "";
		while($ob = $rows->Fetch())
		{
			$kol = $kol + 1;
			$el_id = $ob['ID'];
			$el_name = $ob['NAME'];
			if ($kol > 1)
			{
				$st = $st.",";
			}
			
			//$st = "id = ".$el_id.", NAME = ".$el_name;
			$st = $el_id;
		}
		
		// only_absent - печать только отсутствующих
		//if ($kol >= 1 && !$only_absent)
		if ($kol >= 1 && false)
		{
			echo "i = ".(string)$i.", артикул = ".$st."<br>";
		}
		elseif ($kol === 0)
		{
			//echo "i = ".(string)$i.", артикул = ".$val_code." - НЕ НАШЛОСЬ "."<br>";
			echo $val_code."<br>";
		}
	}
	fclose($f);
	$hours = (int)date('H');
	$minuts = (int)date('i');
	$seconds = (int)date('s');
	//fwrite($fout, 'Произведено '.(string)$kol.' замен '.PHP_EOL);
	fwrite($fout, 'Время: '.(string)$hours.' : '.(string)$minuts.' : '.(string)$seconds.' завершился.'.PHP_EOL);
	fclose($fout);
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>