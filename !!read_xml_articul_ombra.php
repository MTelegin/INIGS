<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
//CModule::IncludeModule('iblock');
//CModule::IncludeModule('sale');
?>
<?
	global $DB;
	$only_absent = true;
	
	$hours = (int)date('H');
	$minuts = (int)date('i');
	$seconds = (int)date('s');
	
	//fwrite($fout, 'Произведено '.(string)$kol.' замен '.PHP_EOL);
	echo 'Время начала: '.(string)$hours.' : '.(string)$minuts.' : '.(string)$seconds."<br>";
	$file_imp = $_SERVER["DOCUMENT_ROOT"]."/test/articul/ombratools.xml";
	
	//fwrite("file_imp = ".$file_imp."<br>");
	//fwrite($fout, 'File = '.$file_imp.PHP_EOL);
	
	$fo = @fopen($file_imp, "r") or die("Ошибка!");
	//$k = 2;
	
	$arSelect = Array(
		"ID",
		"NAME",
		"CATALOG_QUANTITY",
		"PRICE"
	);

	//$rh = fopen($file_source, 'rb');
    //$wh = fopen($file_target, 'wb');
	
	if ($fo === false) {
		// error reading or opening file
        return true;
	}
	
	$i = 0;
	if ($fo) {
		$search_st = '<param name="code"';
		while (($st = fgets($fo, 4096)) !== false) {
			
			$i = $i + 1;
			
			//echo "<pre>".$st."</pre>"."<br>";
			$pos = strpos($st, $search_st);
			if ($pos > 0 ) {
				
				$pos = strpos($st, 'value');
				$code_val = substr($st, $pos + 6);
				$code_val = str_replace([' />', '"'], "", $code_val);
				
				$query_st = "select el.NAME as NAME, el.ID as ID, el.IBLOCK_ID from b_iblock_element_property prop
					LEFT JOIN b_iblock_element el ON el.ID = prop.IBLOCK_ELEMENT_ID && el.IBLOCK_ID = 95
					where prop.IBLOCK_PROPERTY_ID = 7498 && prop.VALUE = ".$code_val;
				
				$rows = $DB->Query($query_st);
				
				$kol = 0;
				while($ob = $rows->Fetch()) {
					$kol = $kol + 1;
					$el_id = $ob['ID'];
					$el_name = $ob['NAME'];
				}
				
				if ($kol >= 1 && false)
				{
					echo "i = ".(string)$i.", артикул = ".$st."<br>";
				}
				elseif ($kol === 0)
				{
					//echo "i = ".(string)$i.", артикул = ".$val_code." - НЕ НАШЛОСЬ "."<br>";
					echo $code_val."<br>";
				}
				//echo $code_val."<br>";
			}
		}
		
		fclose($fo);
	}
	
	$hours = (int)date('H');
	$minuts = (int)date('i');
	$seconds = (int)date('s');
	
	echo 'Время конца: '.(string)$hours.' : '.(string)$minuts.' : '.(string)$seconds."<br>";
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>