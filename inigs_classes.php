<?
class inigs_mail {
	
	public function my_send_mail($to, $subj, $text)
	{
		$headers = "Content-type:text/html; charset = utf-8\r\n";
		$headers .= "From:info@inigs.ru";
		mail($to, $subj, $text, $headers);
	}
}

class inigs {
	
	public $admin_mail = "mtelegin@yandex.ru";
	public $text_base = "WORK";
	//public $catalog_quantity = 0;
	
	public function convert($str)
	{
		//$s = $APPLICATION->ConvertCharset($str, SITE_CHARSET, 'utf-8');
		$s = iconv("utf-8", "windows-1251", $str);
		return $s;
	}
	
	public function set_admin_mail($st = "")
	{
		$this->admin_mail = $st;
	}
	
	public function set_text_base($st = "")
	{
		$this->text_base = $st;
	}
	
	/*public function set_catalog_quantity($element_id = 0)
	{
		$prod = CCatalogProduct::GetByID($element_id);
		$this->catalog_quantity = $prod["QUANTITY"];
	}*/
	
	function Reindex_Iblocks()
	{
		if(CModule::IncludeModule("search"))
		{
			//mail('test@inigs.ru', 'reindex IBLOCK  start', 'reindex IBLOCK start. Time: '.$hours.' : '.$minuts.' : '.$seconds.' started.');
			
			$NS = array("MODULE_ID" => "iblock");
			$NS = CSearch::ReIndexAll(false, 40, $NS, false);
			$ind = 0;
			while(is_array($NS))
			{
				$NS = CSearch::ReIndexAll(false, 40, $NS, false);
				$ind = $ind + 1;
			}
			
			//return true;
			$hours = (int)date('H');
			$minuts = (int)date('i');
			$seconds = (int)date('s');
			
			$msubj = $this->text_base.' - reindex IBLOCK finish';
			$mtext = $this->text_base.' - reindex IBLOCK finish. Time: '.$hours.' : '.$minuts.' : '.$seconds.' finished. Обработано : '.$ind;
			$in_mail = new inigs_mail;
			$in_mail->my_send_mail($this->admin_mail, $msubj, $mtext);
		}
		/*else
		{
			return false;
		}*/
		return true;
	}
	
	public function fill_brands()
	{
		global	$DB;
		$in_mail = new inigs_mail;
		$in_mail->my_send_mail($this->admin_mail, $this->text_base.' - Заполнение брендов началось!', $this->text_base.' - Заполнение брендов началось!');
		$cat_iblock_id = INIGS_IBLOCK_CATALOG; //20;
		$iblock_id_brands = INIGS_IBLOCK_ID_BRANDS; //15;
		$PROP_CML2_ID = INIGS_PROPERTY_CML2_ATTR_ID;
		
		$arSelect_brands = Array("NAME");
		
		$arFilter_brands = Array(
			"IBLOCK_ID" => $iblock_id_brands,
			"ACTIVE" => "Y"
		);
		$res_brands = CIBlockElement::GetList(
			array(),
			$arFilter_brands,
			false,
			array("nPageSize"=>30000),
			$arSelect_brands
		);

		while($ob_brands = $res_brands->GetNextElement())
		{
			$arFields_brands = $ob_brands->GetFields();
			$cur_brand = $arFields_brands["NAME"];
			
			$arSelect = Array(
				"ID",
				"NAME",
				//"PROPERTY_BRAND",
				"PROPERTY_BREND"
			);
			
			$arFilter = Array(
				"IBLOCK_ID" => $cat_iblock_id,
				"NAME" => "%".$cur_brand."%"
			);
			
			$res = CIBlockElement::GetList(
				array(),
				$arFilter,
				false,
				array("nPageSize"=>30000),
				$arSelect
			);
			
			while($ob = $res->GetNextElement())
			{
				$arFields	= $ob->GetFields();
				$element_id = $arFields['ID'];
				$arProps 	= $ob->GetProperties();
				
				//$query = "select DESCRIPTION from b_iblock_element_property where IBLOCK_PROPERTY_ID = 170
				$query = "select DESCRIPTION from b_iblock_element_property where IBLOCK_PROPERTY_ID = ".(string)$PROP_CML2_ID."
					&& VALUE ='Бренд' && IBLOCK_ELEMENT_ID = ".(string)$element_id;
				
				//$rows = $this->DB->Query($query);
				$rows = $DB->Query($query);
				
				$count_id = 0;
				while ($row = $rows->Fetch())
				{
					$descr = $row["DESCRIPTION"];
					
					if (strcmp($descr, "") === 0)
						continue;
					
					if (strlen($descr) === 0)
						continue;
					
					if (strlen($descr) === 1 && strcmp(trim($descr), "-") === 0)
						continue;
					
					//$query_br_ib = "select ID from b_iblock_element where IBLOCK_ID = 15 && NAME like '".$descr."'";
					$query_br_ib = "select ID from b_iblock_element where IBLOCK_ID = ".(string)$iblock_id_brands." && NAME like '".$descr."'";
					$query_br = "select UF_XML_ID from b_brend where UF_NAME like '".$descr."'";
					$rows_br = $DB->Query($query_br);
					while ($row_br = $rows_br->Fetch())
					{
						$row_br_id = $row_br['UF_XML_ID'];
						
						$property_code = "BREND";
						$ret = CIBlockElement::SetPropertyValues(
							$element_id, //$arFields['ID'], //int ELEMENT_ID,
							$cat_iblock_id, //int IBLOCK_ID,
							$row_br_id, //"198780", //array("")//array PROPERTY_VALUES,
							$property_code  //"FREE_DELIVERY" //true //string PROPERTY_CODE = false
						);
					}
					
					$rows_br_ib = $DB->Query($query_br_ib);
					while ($row_br_ib = $rows_br_ib->Fetch())
					{
						$row_br_id = $row_br_ib['ID'];
						
						$property_code = "BRAND";
						$ret = CIBlockElement::SetPropertyValues(
							$element_id, //$arFields['ID'], //int ELEMENT_ID,
							$cat_iblock_id, //int IBLOCK_ID,
							$row_br_id, //"198780", //array("")//array PROPERTY_VALUES,
							$property_code  //"FREE_DELIVERY" //true //string PROPERTY_CODE = false
						);
					}
				}
			}
		}
		$msubj = $this->text_base.' - Заполнение брендов успешно завершилось!';
		$mtext = $this->text_base.' - Заполнение брендов успешно завершилось!';
		$in_mail->my_send_mail($this->admin_mail, $msubj, $mtext);
		return true;
	}
	
	public function export_yandex($mail_hours)
	{
		if (CModule::IncludeModule("catalog"))
		{
			$export_id = INIGS_YANDEX_EXPORT_ID;
			CCatalogExport::PreGenerateExport($export_id); // 3 - профиль YANDEX
			$hours = (int)date('H');
			$minuts = (int)date('i');
			if (in_array($hours, $mail_hours))
			{
				$msubj = $this->text_base.' - Агент yandex конец';
				$mtext = $this->text_base.' - Агент по экспорту в файл yandex.php. Время: '.$hours.' : '.$minuts.' завершился.';
				$in_mail = new inigs_mail;
				$in_mail->my_send_mail($this->admin_mail, $msubj, $mtext);
			}
		}
		else
		{
			$msubj = $this->text_base.' - Агент yandex ошибка';
			$mtext = $this->text_base.' - Агент - не подключился модуль catalog!!!';
			$in_mail = new inigs_mail;
			$in_mail->my_send_mail($this->admin_mail, $msubj, $mtext);
			return false;
		}
		return true;
	}
	
	public function export_yandex_tp($mail_hours)
	{
		if (CModule::IncludeModule("catalog"))
		{
			$export_id = INIGS_YANDEX_TP_EXPORT_ID;
			CCatalogExport::PreGenerateExport($export_id); // профиль YANDEX_TP
			$hours = (int)date('H');
			$minuts = (int)date('i');
			if (in_array($hours, $mail_hours))
			{
				$msubj = $this->text_base.' - Агент yandex_tp конец';
				$mtext = $this->text_base.' - Агент по экспорту в файл yandex_tp.php. Время: '.$hours.' : '.$minuts.' завершился.';
				$in_mail = new inigs_mail;
				$in_mail->my_send_mail($this->admin_mail, $msubj, $mtext);
			}
		}
		else
		{
			$msubj = $this->text_base.' - Агент yandex_tp ошибка';
			$mtext = $this->text_base.' - Агент - не подключился модуль catalog!!!';
			$in_mail = new inigs_mail;
			$in_mail->my_send_mail($this->admin_mail, $msubj, $mtext);
			return false;
		}
		return true;
	}
	
	public function export_google($mail_hours)
	{
		if (CModule::IncludeModule("catalog"))
		{
			$export_id = INIGS_GOOGLE_EXPORT_ID;
			CCatalogExport::PreGenerateExport($export_id); // профиль GOOGLE
			$hours = (int)date('H');
			$minuts = (int)date('i');
			if (in_array($hours, $mail_hours))
			{
				$msubj = $this->text_base.' - Агент Google конец';
				$mtext = $this->text_base.' - Агент по экспорту в файл google.xml. Время: '.$hours.' : '.$minuts.' завершился.';
				$in_mail = new inigs_mail;
				$in_mail->my_send_mail($this->admin_mail, $msubj, $mtext);
			}
		}
		else
		{
			$msubj = $this->text_base.' - Агент Google ошибка';
			$mtext = $this->text_base.' - Агент - не подключился модуль catalog!!!';
			$in_mail = new inigs_mail;
			$in_mail->my_send_mail($this->admin_mail, $msubj, $mtext);
			return false;
		}
		return true;
	}
	
	public function deactivate_elems($mail_hours)
	{
		global	$DB;
		$id_prop = (string)INIGS_NE_VYVODIT_NA_SAYTE_ID;
		$prop_value = (string)INIGS_NE_VYVODIT_NA_SAYTE_TRUE;
		
		$query_ids = 'update b_iblock_element as element
			INNER JOIN b_iblock_element_property as property
			ON property.IBLOCK_ELEMENT_ID = element.ID && property.IBLOCK_PROPERTY_ID = '.$id_prop.' && property.VALUE = '.$prop_value.'
			set element.ACTIVE = "N"
			where element.ACTIVE = "Y"';
			
		/*$query_ids = 'update b_iblock_element as element
			INNER JOIN b_iblock_element_property as property
			ON property.IBLOCK_ELEMENT_ID = element.ID && property.IBLOCK_PROPERTY_ID = ".1602 && property.VALUE = 5735
			set element.ACTIVE = "N"
			where element.ACTIVE = "Y"';*/
			
		$rows = $DB->Query($query_ids); //выполняем запрос

		$hours = (int)date('H');
		$minuts = (int)date('i');
		$seconds = (int)date('s');
		
		if (in_array($hours, $mail_hours))
		{
			$msubj = $this->text_base.' - агент по деактивации элементов';
			$mtext = $this->text_base.' - агент по деактивации элементов каталога конец. Время: '.$hours.' : '.$minuts.' : '.$seconds.' завершился.';
			$in_mail = new inigs_mail;
			$in_mail->my_send_mail($this->admin_mail, $msubj, $mtext);
		}
		
		return true;
	}
	
	public function fill_elem_Code1C($id_val)
	{
		$cat_iblock_id = INIGS_IBLOCK_CATALOG;
		$arSelect = Array();
		$arFilter = Array(
			"IBLOCK_ID" => (string)$cat_iblock_id, //"20",
		);
		$arFilter['=ID'] = (string)$id_val;
		
		$res = CIBlockElement::GetList(
			array(),
			$arFilter,
			false,
			array("nPageSize"=>30000),
			$arSelect
		);
		$k = 30000;
		$i = 0;
		
		while($ob = $res->GetNextElement())
		{
			$i = $i + 1;
			
			if ($i > $k)
				break;
			
			$arFields = $ob->GetFields();

			$element_id = $arFields['ID'];  // код элемента
			//$PROPERTY_CODE = 6936;  		// код свойства CODE_1C
			//$PROPERTY_CODE = 20711;  		// код свойства CODE_1C
			//$PROPERTY_CODE = 3196; 			// код свойства CODE_1C
			$property_code = INIGS_PROPERTY_CODE_1C_ID;
			
			$arProps = $ob->GetProperties();
			$code_val = $arProps["CML2_TRAITS"]["VALUE"][2];
			$property_value = $code_val;

			$dbr = CIBlockElement::GetList(array(), array("=ID"=>$element_id, "=IBLOCK_ID"=>$cat_iblock_id), false, false, array("ID", "IBLOCK_ID"));
			if ($dbr_arr = $dbr->Fetch())
			{
				$iblock_id = $dbr_arr["IBLOCK_ID"];
				CIBlockElement::SetPropertyValues($element_id, $iblock_id, $property_value, $property_code);
			}
		}
		return true;
	}
	
	public function fillCode1C_All()
	{
		global		$DB;
		$cat_iblock_id = (string)INIGS_IBLOCK_CATALOG;
		$code_1c_id = (string)INIGS_PROPERTY_CODE_1C_ID;
		
		$query_id = "select element.ID as ID, property.VALUE as CODE_1C
			from b_iblock_element element
			LEFT JOIN b_iblock_element_property property ON property.IBLOCK_ELEMENT_ID = element.ID && property.IBLOCK_PROPERTY_ID = ".$code_1c_id."
			where
				element.IBLOCK_ID = ".$cat_iblock_id."
			&&  property.VALUE IS NULL";
			//&& (element.ID <> 15409 && element.ID <> 15410 && element.ID <> 15411)";

		$rows = $DB->Query($query_id);
		if (!isset($rows))
		{
			return "null";
		}
		
		while ($row = $rows->Fetch())
		{
			$id_elem = $row["ID"];
			//fillByID($id_elem);
			$this->fill_elem_Code1C($id_elem);
		}
		return true;
	}
	
	public function fill_in_store($mail_hours)
	{
		$cat_iblock_id = INIGS_IBLOCK_CATALOG;
		CModule::IncludeModule('iblock');
		$hours = (int)date('H');
		$minuts = (int)date('i');
		$seconds = (int)date('s');
		
		if (in_array($hours, $mail_hours))
		{
			$msubj = $this->text_base.' - Агент по заполнению свойства В наличии СТАРТ';
			$mtext = $this->text_base.' - Агент по заполнению свойства В наличии СТАРТ. Время: '.$hours.' : '.$minuts.' : '.$seconds.' СТАРТ.';
			$in_mail = new inigs_mail;
			$in_mail->my_send_mail($this->admin_mail, $msubj, $mtext);
		}
		$arSelect = Array(
			"ID",
			"NAME",
			"CATALOG_QUANTITY",
			"PROPERTY_ITEM_IN_STORE"
		);
		
		$arFilter = Array(
			"IBLOCK_ID" => $cat_iblock_id, //'20',
		);
		
		$res = CIBlockElement::GetList(
			array(),
			$arFilter,
			false,
			array("nPageSize"=>30000),
			$arSelect
		);
		
		$k = 30000;
		$i = 0;
		$property_code = INIGS_ITEM_IN_STORE;
		$prop_code_true = (int)INIGS_PROPERTY_AVAILABLE_TRUE;
		$prop_code_false = (int)INIGS_PROPERTY_AVAILABLE_FALSE;
		
		while($ob = $res->GetNextElement())
		{
			$i = $i + 1;
			
			if ($i > $k)
				break;
			
			$arFields = $ob->GetFields();
			$element_id = $arFields['ID'];  // код элемента
			if ($arFields['CATALOG_QUANTITY'] > 0)
			{
				//$property_value = Array("VALUE" => $prop_code_true); // Да
				$property_value = $prop_code_true;
			}
			else
			{
				//$property_value = Array("VALUE" =>  $prop_code_false); //"Нет";
				$property_value = $prop_code_false;
			}

			// Установим новое значение для данного свойства данного элемента
			$dbr = CIBlockElement::GetList(array(), array("=ID"=>$element_id, "=IBLOCK_ID"=>$cat_iblock_id), false, false, array("ID", "IBLOCK_ID"));
			if ($dbr_arr = $dbr->Fetch())
			{
				$iblock_id = $dbr_arr["IBLOCK_ID"];
				//CIBlockElement::SetPropertyValues($element_id, $iblock_id, $property_value, $property_code);
				CIBlockElement::SetPropertyValues(
					(int)$element_id,
					(int)$cat_iblock_id,
					$property_value,
					$property_code
				);
			}
		}
		
		$hours = (int)date('H');
		$minuts = (int)date('i');
		$seconds = (int)date('s');
		
		if (in_array($hours, $mail_hours))
		{
			$msubj = $this->text_base.' - Агент по заполнению свойства В наличии конец';
			$mtext = $this->text_base.' - Агент по заполнению свойства В наличии конец. Время: '.$hours.' : '.$minuts.' : '.$seconds.' завершился.';
			$in_mail = new inigs_mail;
			$in_mail->my_send_mail($this->admin_mail, $msubj, $mtext);
		}
		return true;
	}
	
	public function elem_ordering($mail_hours)
	{
		global	$DB;
		$cat_iblock_id = (string)INIGS_IBLOCK_CATALOG;
		$cat_section_ids = INIGS_SECTIONS_QUERY_EXLUDE; // "(474, 96, 735)"
		
		CModule::IncludeModule('iblock');
		// 5839 - Расходка, 5470 - Аэраторы, 6101 - Спецодежда и СИЗ
		// 474	- Расходка, 96 - Аэраторы, 735 - Спецодежда и СИЗ
		$query = "select subtable.ID as ID, min(subtable.COL), max(subtable.QUANTITY) as COL from
		(select DISTINCT element.ID as ID, 1 as COL, product.QUANTITY as QUANTITY from b_iblock_element element
		LEFT JOIN b_iblock_section_element section ON section.IBLOCK_ELEMENT_ID = element.ID
		LEFT JOIN b_iblock_section parlev1 ON parlev1.ID = section.IBLOCK_SECTION_ID && parlev1.IBLOCK_ID = ".$cat_iblock_id."
		LEFT JOIN b_iblock_section parlev2 ON parlev2.ID = parlev1.IBLOCK_SECTION_ID && parlev2.IBLOCK_ID = ".$cat_iblock_id."
		LEFT JOIN b_iblock_section parlev3 ON parlev3.ID = parlev2.IBLOCK_SECTION_ID && parlev3.IBLOCK_ID = ".$cat_iblock_id."
		LEFT JOIN b_iblock_section parlev4 ON parlev4.ID = parlev3.IBLOCK_SECTION_ID && parlev4.IBLOCK_ID = ".$cat_iblock_id."
		LEFT JOIN b_catalog_product product ON product.ID = element.ID
		where element.IBLOCK_ID = ".$cat_iblock_id."
		&& section.IBLOCK_SECTION_ID NOT IN ".$cat_section_ids."
		&& (parlev1.ID IS NULL or parlev1.ID NOT IN ".$cat_section_ids.")
		&& (parlev2.ID IS NULL or parlev2.ID NOT IN ".$cat_section_ids.")
		&& (parlev3.ID IS NULL or parlev3.ID NOT IN ".$cat_section_ids.")
		&& (parlev4.ID IS NULL or parlev4.ID NOT IN ".$cat_section_ids.")
		&& section.IBLOCK_SECTION_ID IS NOT NULL
		UNION
		select DISTINCT element.ID, 2, product.QUANTITY from b_iblock_element element
		LEFT JOIN b_iblock_section_element section ON section.IBLOCK_ELEMENT_ID = element.ID
		LEFT JOIN b_iblock_section parlev1 ON parlev1.ID = section.IBLOCK_SECTION_ID && parlev1.IBLOCK_ID = ".$cat_iblock_id."
		LEFT JOIN b_iblock_section parlev2 ON parlev2.ID = parlev1.IBLOCK_SECTION_ID && parlev2.IBLOCK_ID = ".$cat_iblock_id."
		LEFT JOIN b_iblock_section parlev3 ON parlev3.ID = parlev2.IBLOCK_SECTION_ID && parlev3.IBLOCK_ID = ".$cat_iblock_id."
		LEFT JOIN b_iblock_section parlev4 ON parlev4.ID = parlev3.IBLOCK_SECTION_ID && parlev4.IBLOCK_ID = ".$cat_iblock_id."
		LEFT JOIN b_catalog_product product ON product.ID = element.ID
		where element.IBLOCK_ID = ".$cat_iblock_id."
		&& ((section.IBLOCK_SECTION_ID IN ".$cat_section_ids." or parlev1.ID IN ".$cat_section_ids."
		or  parlev2.ID IN ".$cat_section_ids." or  parlev3.ID IN ".$cat_section_ids." or  parlev4.ID IN ".$cat_section_ids.")
		&& section.IBLOCK_SECTION_ID IS NOT NULL)
		UNION
		select DISTINCT element.ID, 3, product.QUANTITY from b_iblock_element element
		LEFT JOIN b_iblock_section_element section ON section.IBLOCK_ELEMENT_ID = element.ID
		LEFT JOIN b_catalog_product product ON product.ID = element.ID
		where element.IBLOCK_ID = ".$cat_iblock_id." && section.IBLOCK_SECTION_ID IS NULL) as subtable
		group by subtable.ID
		order by CASE WHEN max(subtable.QUANTITY) > 0 THEN 1 ELSE 0 END DESC, min(subtable.COL) ASC, subtable.ID ASC";
		
		//order by min(subtable.COL) ASC, CASE WHEN max(subtable.QUANTITY) > 0 THEN 1 ELSE 0 END DESC, subtable.ID ASC";
		
		//echo "query = ".$query."<br>";
		//die();
		$rows = $DB->Query($query);
		$i = 0;
		$kol = 30000;
		while ($row = $rows->Fetch())
		{
			$i = $i + 1;
			
			$id = (int)$row["ID"];
			//continue;
			
			$sort_num = $i * 10;
			
			$new_query = 'update b_iblock_element
				set SORT = "'.$sort_num.'" where ID = "'.$id.'"';
			
			$rows_new = $DB->Query($new_query);
		}
		
		$hours = (int)date('H');
		$minuts = (int)date('i');
		if (in_array($hours, $mail_hours))
		{
			$msubj = $this->text_base.' - Сортировка элементов';
			$mtext = $this->text_base.' - Сортировка элементов. Время: '.$hours.' : '.$minuts.' завершился.';
			$in_mail = new inigs_mail;
			$in_mail->my_send_mail($this->admin_mail, $msubj, $mtext);
		}
		
		return true;
	}
	
	public function fill_weight($mail_hours) {
		
		global	$DB;
		$iblock_id 		= INIGS_IBLOCK_CATALOG;
		$prop_cml2_id 	= (string)INIGS_PROPERTY_CML2_ATTR_ID;
		
		CModule::IncludeModule('iblock');
		
		$arSelect = Array(
			"ID",
			"NAME",
			"PROPERTY_GABARITY_MM",
			"PROPERTY_GABARITY_MM_1",
			"PROPERTY_GABARITY_UPAKOVKI_MM_DKHSHKHV",
			"PROPERTY_VES_KG",
			"PROPERTY_VES_UPAKOVKI_KG",
			"PROPERTY_VES_KG_2",
			"PROPERTY_VES_KG_1",
			"PROPERTY_VES_KG_3",
			//"PROPERTY_*"
		);
		
		$arFilter = Array(
			"IBLOCK_ID"=>$iblock_id,
		);
		
		$res = CIBlockElement::GetList(
			array(),
			$arFilter,
			false,
			array("nPageSize"=>30000),
			$arSelect
		);
		
		while($ob = $res->GetNextElement())
		{
			$arFields = $ob->GetFields();
			$element_id = $arFields["ID"];
			$arProps = $ob->GetProperties();
			
			$query_prop = "select VALUE, DESCRIPTION from b_iblock_element_property
				where IBLOCK_ELEMENT_ID = ".$element_id." && IBLOCK_PROPERTY_ID = ".$prop_cml2_id."
				&& (VALUE like '%Вес%' || VALUE like '%Габариты%')";
			
			$rows_prop = $DB->Query($query_prop);
			$prop_weight = 0;
			$prop_gabarits = "";
			while ($row_prop = $rows_prop->Fetch())
			{
				if (strpos($row_prop['VALUE'], 'Вес') !== false)
				{
					$prop_weight = $row_prop['DESCRIPTION'];
				}
				elseif (strpos($row_prop['VALUE'], 'Габариты') !== false)
				{
					$prop_gabarits = $row_prop['DESCRIPTION'];
				}
			}
			$prop_gabarits = str_replace(" ", "", $prop_gabarits);
			$prop_weight = str_replace(",", ".", $prop_weight);
			
			$weight_st = trim((string)$arFields["PROPERTY_VES_UPAKOVKI_KG_VALUE"]);

			if (strcmp($weight_st, '') === 0)
			{
				$weight_st = $prop_weight;
			}
			
			if (strcmp($weight_st, '') === 0)
			{
				$weight_st = trim((string)$arFields["PROPERTY_VES_KG_1_VALUE"]);
			}
			
			if (strcmp($weight_st, '') === 0)
			{
				$weight_st = trim((string)$arFields["PROPERTY_VES_KG_2_VALUE"]);
			}
			
			//if (strcmp($weight_st, '') !== 0)
			//	$weight_st = trim($arFields["PROPERTY_VES_KG_VALUE"]);
			
			if (strcmp($weight_st, '') === 0)
			{
				$weight_st = trim((string)$arFields["PROPERTY_VES_KG_3_VALUE"]);
			}
			
			if (strcmp($weight_st, '') === 0)
			{
				$weight_st = trim((string)$arFields["PROPERTY_VES_KG_VALUE"]);
			}
			
			$weight = (float)$weight_st;
			$weight = (int)($weight * 1000);
			
			$gabarits = mb_strtolower(trim($arFields["PROPERTY_GABARITY_UPAKOVKI_MM_DKHSHKHV_VALUE"]));
			
			if (strcmp($gabarits, '') === 0)
			{
				$gabarits = $prop_gabarits;
			}
			
			if (strcmp($gabarits, '') === 0)
			{
				$gabarits = mb_strtolower(trim($arFields["PROPERTY_GABARITY_MM_VALUE"]));
			}
			
			$gabarits = str_replace('х', 'x', $gabarits);
			$char_x1 = mb_strpos($gabarits, 'x'); // английская x
			$length = 0;
			$width = 0;
			$height = 0;
			
			if ($char_x1 > 0)
			{
				$length = (int)substr($gabarits, 0, $char_x1);
				$gabarits = substr($gabarits, $char_x1 + 1);
			}
			
			$char_x2 = mb_strpos($gabarits, 'x'); // английская x
			
			if ($char_x2 > 0)
			{
				$width = (int)substr($gabarits, 0, $char_x2);
				$gabarits = substr($gabarits, $char_x2 + 1);
				$height = (int)$gabarits;
			}
			else
			{
				$width = (int)$gabarits;
				$gabarits = '';
				$height = 0;
			}
			
			$arFields_UPD=array();
			if ($weight > 0)
			{
				$arFields_UPD['WEIGHT'] = $weight;
			}
			
			if($length > 0)
			{
				$arFields_UPD['LENGTH'] = $length;
			}
			
			if ($width > 0)
			{
				$arFields_UPD['WIDTH'] = $width;
			}
			
			if ($height > 0)
			{
				$arFields_UPD['HEIGHT'] = $height;
			}
			
			if (count($arFields_UPD) > 0)
			{
				$succ = CCatalogProduct::Update($element_id, $arFields_UPD);
			}
		}
		
		$hours = (int)date('H');
		$minuts = (int)date('i');
		$seconds = (int)date('s');
		
		//if ($hours === 13 || $hours === 16 || $hours === 17)
		if (in_array($hours, $mail_hours))
		{
			$msubj = $this->text_base.' - Агент по заполнению полей вес и габариты конец';
			$mtext = $this->text_base.' - Агент по заполнению полей вес и габариты конец. Время: '.$hours.' : '.$minuts.' : '.$seconds.' завершился.';
			$in_mail = new inigs_mail;
			$in_mail->my_send_mail($this->admin_mail, $msubj, $mtext);
		}
		
		return true;
	}
	
	public function fill_weight_gr($mail_hours) {
		
		global	$DB;
		$iblock_id 		= INIGS_IBLOCK_CATALOG;
		$prop_cml2_id 	= (string)INIGS_PROPERTY_CML2_ATTR_ID;
		$prop_code 		= 20762; // Код свойства "Вес, грамм"
		
		CModule::IncludeModule('iblock');
		
		$arSelect = Array(
			"ID",
			"NAME",
			"PROPERTY_GABARITY_MM",
			"PROPERTY_GABARITY_MM_1",
			"PROPERTY_GABARITY_UPAKOVKI_MM_DKHSHKHV",
			"PROPERTY_VES_KG",
			"PROPERTY_VES_UPAKOVKI_KG",
			"PROPERTY_VES_KG_2",
			"PROPERTY_VES_KG_1",
			"PROPERTY_VES_KG_3",
			"PROPERTY_WEIGHT_GR",
			//"PROPERTY_*"
		);
		
		$arFilter = Array(
			"IBLOCK_ID"=>$iblock_id,
		);
		
		$res = CIBlockElement::GetList(
			array(),
			$arFilter,
			false,
			array("nPageSize"=>30000),
			$arSelect
		);
		
		while($ob = $res->GetNextElement())
		{
			$arFields = $ob->GetFields();
			$element_id = $arFields["ID"];
			$arProps = $ob->GetProperties();
			
			$query_prop = "select VALUE, DESCRIPTION from b_iblock_element_property
				where IBLOCK_ELEMENT_ID = ".$element_id." && IBLOCK_PROPERTY_ID = ".$prop_cml2_id."
				&& (VALUE like '%Вес%' || VALUE like '%Габариты%')";
			
			$rows_prop = $DB->Query($query_prop);
			$prop_weight = 0;
			$prop_gabarits = "";
			while ($row_prop = $rows_prop->Fetch())
			{
				if (strpos($row_prop['VALUE'], 'Вес') !== false)
				{
					$prop_weight = $row_prop['DESCRIPTION'];
				}
				elseif (strpos($row_prop['VALUE'], 'Габариты') !== false)
				{
					$prop_gabarits = $row_prop['DESCRIPTION'];
				}
			}
			
			$prop_weight = str_replace(",", ".", $prop_weight);
			
			$weight_st = trim((string)$arFields["PROPERTY_VES_UPAKOVKI_KG_VALUE"]);

			if (strcmp($weight_st, '') === 0)
			{
				$weight_st = $prop_weight;
			}
			
			if (strcmp($weight_st, '') === 0)
			{
				$weight_st = trim((string)$arFields["PROPERTY_VES_KG_1_VALUE"]);
			}
			
			if (strcmp($weight_st, '') === 0)
			{
				$weight_st = trim((string)$arFields["PROPERTY_VES_KG_2_VALUE"]);
			}
			
			//if (strcmp($weight_st, '') !== 0)
			//	$weight_st = trim($arFields["PROPERTY_VES_KG_VALUE"]);
			
			if (strcmp($weight_st, '') === 0)
			{
				$weight_st = trim((string)$arFields["PROPERTY_VES_KG_3_VALUE"]);
			}
			
			if (strcmp($weight_st, '') === 0)
			{
				$weight_st = trim((string)$arFields["PROPERTY_VES_KG_VALUE"]);
			}
			
			$weight = (float)$weight_st;
			$weight = (int)($weight * 1000);
			
			//$arFields["PROPERTY_WEIGHT_GR"] = $weight;
			CIBlockElement::SetPropertyValues($element_id, $iblock_id, $weight, $prop_code);
		}
		
		$hours = (int)date('H');
		$minuts = (int)date('i');
		$seconds = (int)date('s');
		
		//if ($hours === 13 || $hours === 16 || $hours === 17)
		if (in_array($hours, $mail_hours))
		{
			$msubj = $this->text_base.' - Агент по заполнению полей вес и габариты конец';
			$mtext = $this->text_base.' - Агент по заполнению полей вес и габариты конец. Время: '.$hours.' : '.$minuts.' : '.$seconds.' завершился.';
			$in_mail = new inigs_mail;
			$in_mail->my_send_mail($this->admin_mail, $msubj, $mtext);
		}
		
		return true;
	}
	
	public function fill_elem_weight($element_id = 0) {
		
		global	$DB;
		
		if ($element_id === 0)
			return false;
		
		$cat_iblock_id	= INIGS_IBLOCK_CATALOG;
		$prop_cml2_id 	= (string)INIGS_PROPERTY_CML2_ATTR_ID;
		
		CModule::IncludeModule('iblock');
		
		$arSelect = Array(
			"ID",
			"NAME",
			"PROPERTY_GABARITY_MM",
			"PROPERTY_GABARITY_MM_1",
			"PROPERTY_GABARITY_UPAKOVKI_MM_DKHSHKHV",
			"PROPERTY_VES_KG",
			"PROPERTY_VES_UPAKOVKI_KG",
			"PROPERTY_VES_KG_2",
			"PROPERTY_VES_KG_1",
			"PROPERTY_VES_KG_3",
			//"PROPERTY_*"
		);
		
		$arFilter = Array(
			"IBLOCK_ID" => $cat_iblock_id,
			"ID" => $element_id, // только этот элемент
		);
		
		$res = CIBlockElement::GetList(
			array(),
			$arFilter,
			false,
			array("nPageSize"=>30000),
			$arSelect
		);
		
		while($ob = $res->GetNextElement())
		{
			$arFields = $ob->GetFields();
			
			$arProps = $ob->GetProperties();
			
			$query_prop = "select VALUE, DESCRIPTION from b_iblock_element_property
				where IBLOCK_ELEMENT_ID = ".$arFields['ID']." && IBLOCK_PROPERTY_ID = ".$prop_cml2_id."
				&& (VALUE like '%Вес%' || VALUE like '%Габариты%')";
			
			$rows_prop = $DB->Query($query_prop);
			$prop_weight = 0;
			$prop_gabarits = "";
			while ($row_prop = $rows_prop->Fetch())
			{
				if (strpos($row_prop['VALUE'], 'Вес') !== false)
				{
					$prop_weight = $row_prop['DESCRIPTION'];
				}
				elseif (strpos($row_prop['VALUE'], 'Габариты') !== false)
				{
					$prop_gabarits = $row_prop['DESCRIPTION'];
				}
			}
			$prop_gabarits = str_replace(" ", "", $prop_gabarits);
			$prop_weight = str_replace(",", ".", $prop_weight);
			
			$weight_st = trim((string)$arFields["PROPERTY_VES_UPAKOVKI_KG_VALUE"]);

			if (strcmp($weight_st, '') === 0)
			{
				$weight_st = $prop_weight;
			}
			
			if (strcmp($weight_st, '') === 0)
			{
				$weight_st = trim((string)$arFields["PROPERTY_VES_KG_1_VALUE"]);
			}
			
			if (strcmp($weight_st, '') === 0)
			{
				$weight_st = trim((string)$arFields["PROPERTY_VES_KG_2_VALUE"]);
			}
			
			//if (strcmp($weight_st, '') !== 0)
			//	$weight_st = trim($arFields["PROPERTY_VES_KG_VALUE"]);
		
			if (strcmp($weight_st, '') === 0)
			{
				$weight_st = trim((string)$arFields["PROPERTY_VES_KG_3_VALUE"]);
			}
			
			if (strcmp($weight_st, '') === 0)
			{
				$weight_st = trim((string)$arFields["PROPERTY_VES_KG_VALUE"]);
			}
			
			$weight = (float)$weight_st;
			$weight = (int)($weight * 1000);
			
			$gabarits = mb_strtolower(trim($arFields["PROPERTY_GABARITY_UPAKOVKI_MM_DKHSHKHV_VALUE"]));
			
			if (strcmp($gabarits, '') === 0)
			{
				$gabarits = $prop_gabarits;
			}
			
			if (strcmp($gabarits, '') === 0)
			{
				$gabarits = mb_strtolower(trim($arFields["PROPERTY_GABARITY_MM_VALUE"]));
			}
			
			$gabarits = str_replace('х', 'x', $gabarits);
			$char_x1 = mb_strpos($gabarits, 'x'); // английская x
			$length = 0;
			$width = 0;
			$height = 0;
			
			if ($char_x1 > 0)
			{
				$length = (int)substr($gabarits, 0, $char_x1);
				$gabarits = substr($gabarits, $char_x1 + 1);
			}
			
			$char_x2 = mb_strpos($gabarits, 'x'); // английская x
			
			if ($char_x2 > 0)
			{
				$width = (int)substr($gabarits, 0, $char_x2);
				$gabarits = substr($gabarits, $char_x2 + 1);
				$height = (int)$gabarits;
			}
			else
			{
				$width = (int)$gabarits;
				$gabarits = '';
				$height = 0;
			}
			
			//mail("mtelegin@yandex.ru", "TEST, weight = ".(string)$weight, "TEST, weight = ".(string)$weight);
			//$arFields_UPD=array();
			if ($weight > 0)
			{
				//$arFields_UPD['WEIGHT'] = $weight;
				//$succ = CCatalogProduct::Update($arFields['ID'], $arFields_UPD);
				$query_upd = "update b_catalog_product set WEIGHT = ".(string)$weight." where ID = ".(string)$element_id;
				$in_mail = new inigs_mail;
				$in_mail->my_send_mail("mtelegin@yandex.ru", "TEST - Запрос", $query_upd);
				$rows = $DB->Query($query_upd);
			}
			
			$arFields_UPD=array();
			if($length > 0)
			{
				$arFields_UPD['LENGTH'] = $length;
			}
			
			if ($width > 0)
			{
				$arFields_UPD['WIDTH'] = $width;
			}
			
			if ($height > 0)
			{
				$arFields_UPD['HEIGHT'] = $height;
			}
			
			if (count($arFields_UPD) > 0)
			{
				/*$st1 = "";
				foreach ($arFields_UPD as $k => $val)
				{
					$st1 = $st1.', k = '.$k.', val = '.$val.'<br>';
				}
				mail("mtelegin@yandex.ru", "TEST - UPDATE", "TEST".$st1);*/
				$succ = CCatalogProduct::Update($arFields['ID'], $arFields_UPD);
			}
		}
		
		//if ($hours === 13 || $hours === 16 || $hours === 17)
		$msubj = $this->text_base.' - Заполнил вес и гарабиры у элемента с ID '.(string)$element_id;
		$mtext = $this->text_base.' - Заполнил вес и гарабиры у элемента с ID '.(string)$element_id;
		$in_mail = new inigs_mail;
		$in_mail->my_send_mail($this->admin_mail, $msubj, $mtext);
		
		return true;
	}
	
	public function fill_Available($element_id) {
		
		if ($element_id === 0)
			return false;
		
		$property_code = (string)INIGS_PROPERTY_AVAILABLE; // код свойства
		if ($this->catalog_quantity > 0)
		{
			$property_value = Array("VALUE" => INIGS_PROPERTY_AVAILABLE_TRUE); //"Да";
        }
		else
		{
			$property_value = Array("VALUE" => INIGS_PROPERTY_AVAILABLE_FALSE); //"Нет";
        }

        // Установим новое значение для данного свойства данного элемента
		$cat_iblock_id = INIGS_IBLOCK_CATALOG;
        //$dbr = CIBlockElement::GetList(array(), array("=ID" => $ELEMENT_ID, "=IBLOCK_ID" => 10), false, false, array("ID", "IBLOCK_ID"));
		$dbr = CIBlockElement::GetList(array(), array("=ID" => $element_id, "=IBLOCK_ID" => $cat_iblock_id), false, false, array("ID", "IBLOCK_ID"));
        if ($dbr_arr = $dbr->Fetch()) {
            //CIBlockElement::SetPropertyValues($ELEMENT_ID, $IBLOCK_ID, $PROPERTY_VALUE, $PROPERTY_CODE);
			CIBlockElement::SetPropertyValues($element_id, $cat_iblock_id, $property_value, $property_code);
			return true;
        }
		return false;
	}
	
	public function DeleteOld($nDays)
	{
		global $DB;

		$nDays = IntVal($nDays);
		$strSql =
			"SELECT f.ID ".
			"FROM b_sale_fuser f ".
			"LEFT JOIN b_sale_order o ON (o.USER_ID = f.USER_ID) ".
			"WHERE ".
			"	TO_DAYS(f.DATE_UPDATE)<(TO_DAYS(NOW())-".$nDays.") ".
			"	AND o.ID is null ".
			"	AND f.USER_ID is null ".
			"LIMIT 1000";
		//LIMIT 300

		//$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$db_res = $DB->Query($strSql);
		$kol = 0;
		while ($ar_res = $db_res->Fetch())
		{
			CSaleBasket::DeleteAll($ar_res["ID"], false);
			CSaleUser::Delete($ar_res["ID"]);
			$kol = $kol + 1;
		}

		if ($kol >= 100)
			return true;
		else
			return false;
		
		//return true;
	}
	
	public function clear_empty_baskets($mail_hours)
	{
		CModule::IncludeModule('iblock');
		$i = 0;
		$cont = true;
		while ($i <= 10 && $cont) {
			$cont = $this->DeleteOld(1);
			$i = $i + 1;
		}
		$hours = (int)date('H');
		$minuts = (int)date('i');
		$seconds = (int)date('s');
		
		if (in_array($hours, $mail_hours) && $minuts < 30)
		{
			$msubj = $this->text_base.' - Очистка корзин конец';
			$mtext = $this->text_base.' - Очистка корзин конец. Время: '.$hours.' : '.$minuts.' : '.$seconds.' завершился.';
			$in_mail = new inigs_mail;
			$in_mail->my_send_mail($this->admin_mail, $msubj, $mtext);
		}
	}
}

class Parts
{
	public $minVal;
	public $maxVal;
	public $kol;
	
   function __construct() {
       $this->kol = INIGS_COUNT_PARTS_QUERIES;
   }

	public function setMin($minValue)
	{
		$this->minVal = $minValue;
	}
	
	public function setMax($maxValue)
	{
		$this->maxVal = $maxValue;
	}
	
	public function get_maxminID()
	{
		global $DB;
		$cat_iblock_id	= INIGS_IBLOCK_CATALOG;
		$strSql = "select max(ID) as MAX, min(ID) as MIN from b_iblock_element where IBLOCK_ID =".(string)INIGS_IBLOCK_CATALOG;
		//echo "strSql = ".$strSql."<br>";
		
		$db_res = $DB->Query($strSql);
		$minVal = 0;
		$maxVal = 0;
		while ($ar_res = $db_res->Fetch())
		{
			$minVal = $ar_res["MIN"];
			$maxVal = $ar_res["MAX"];
		}
		$this->setMax($maxVal);
		$this->setMin($minVal);
		
		//echo "minVal = ".(string)$minVal."<br>";
		//echo "maxVal = ".(string)$maxVal."<br>";		
		
		return [$minVal, $maxVal];
	}
	
	public function get_parts()
	{
		$ar = $this->get_maxminID();
		$minVal = $ar[0];
		$maxVal = $ar[1];
		echo "kol = ".$this->kol."<br>";
		
		$step = intval(($maxVal - $minVal) / $this->kol);
		$ar_res = array();
		
		$ar_res[0] = $minVal;
		for ($i = 0; $i < $this->kol; $i++){
			
			if ($i + 1 <= $this->kol) {
				$ar_res[$i + 1] = $ar_res[$i] + $step;
			}
			else
			{
				$ar_res[$i + 1] = $maxVal;
			}
		}
		return $ar_res;
	}
}

class Delete_cache
{
	public $ar_path = array();
	public $days_live_cache = INIGS_CACHE_LIVE_DAYS;
	
	public $admin_mail = "mtelegin@yandex.ru";
	public $text_base = "WORK";
	
	public $str_to_delete = array();
	//public $catalog_quantity = 0;
	
	public function convert($str)
	{
		//$s = $APPLICATION->ConvertCharset($str, SITE_CHARSET, 'utf-8');
		$s = iconv("utf-8", "windows-1251", $str);
		return $s;
	}
	
	public function set_admin_mail($st = "")
	{
		$this->admin_mail = $st;
	}
	
	public function set_text_base($st = "")
	{
		$this->text_base = $st;
	}
	
	function exec_query($strSql, $print = false)
	{
		global	$DB;
		if ($print)
		{
			$hours = (int)date('H');
			$minuts = (int)date('i');
			$seconds = (int)date('s');
			
			//$msubj = $this->text_base.' - Очистка таблицы. Исполнение запроса';
			//$mtext = $this->text_base.' - SQL : '.$strSql.', Время: '.$hours.' : '.$minuts.' : '.$seconds;
			//$in_mail = new inigs_mail;
			//$in_mail->my_send_mail($this->admin_mail, $msubj, $mtext);
			if ($print)
			{
				echo "strSql : "."<br>";
				dump($strSql);
			}
		}
		$DB->Query($strSql);
	}
	
	function clear_table($print = false)
	{
		$i = 0;
		foreach ($this->str_to_delete as $el)
		{
			$s_del = (string)$el;
			$s_del = str_replace("/home/bitrix/www", "", $s_del);
			$s_del = str_replace("//", "/", $s_del);
			
			$i = $i + 1;
			if ($i === 1)
			{
				$strSql = "delete from b_cache_tag where ";
			}
			
			if ($i >= 1)
			{
				$strSql = $strSql.($i >= 2 ? " OR " : "").'RELATIVE_PATH like "%'.$s_del.'"';
			}
			
			if ($i >= 50)
			{
				$this->exec_query($strSql, $print);
				$i = 0;
			}
		}
		if ($i > 0)
		{
			$this->exec_query($strSql, $print);
		}
	}
	
	public function init_ar_path()
	{
		$ar_tmp = array();
		$ar_tmp[] = ["/home/bitrix/www/bitrix/cache/s1/bitrix/catalog.element/", ""];
		$ar_tmp[] = ["/home/bitrix/www/bitrix/cache/s1/bitrix/", "catalog.element.~"];
		$ar_tmp[] = ["/home/bitrix/www/bitrix/cache/s1/bitrix/", "catalog.section.~"];
		$ar_tmp[] = ["/home/bitrix/www/bitrix/cache/s1/bitrix/", "catalog.smart.filter.~"];
		$ar_tmp[] = ["/home/bitrix/www/bitrix/cache/s1/bitrix/", "catalog.store.amount.~"];
		$ar_tmp[] = ["/home/bitrix/www/bitrix/cache/s1/bitrix/", "form.result.new.~"];
		$ar_tmp[] = ["/home/bitrix/www/bitrix/cache/s1/bitrix/", "forum.topic.reviews.~"];
		$ar_tmp[] = ["/home/bitrix/www/bitrix/cache/s1/bitrix/", "catalog.bigdata.products.~"];
		$ar_tmp[] = ["/home/bitrix/www/bitrix/cache/s1/bitrix/", "menu.~"];
		$ar_tmp[] = ["/home/bitrix/www/bitrix/cache/s1/bitrix/", "news.detail.~"];
		$ar_tmp[] = ["/home/bitrix/www/bitrix/cache/s1/bitrix/", "news.list.~"];

		$ar_tmp[] = ["/home/bitrix/www/bitrix/cache/s1/bitrix/catalog.section/", ""];
		$ar_tmp[] = ["/home/bitrix/www/bitrix/cache/s1/bitrix/catalog.smart.filter/", ""];
		$ar_tmp[] = ["/home/bitrix/www/bitrix/cache/s1/bitrix/catalog.section.list/", ""];
		$ar_tmp[] = ["/home/bitrix/www/bitrix/cache/CNextCache/iblock/CIBlock_GetList/", ""];
		$ar_tmp[] = ["/home/bitrix/www/bitrix/cache/CNextCache/iblock/CIBlockElement_GetList/", ""];
		$ar_tmp[] = ["/home/bitrix/www/bitrix/cache/CNextCache/iblock/CIBlockElement_GetProperty/", ""];
		$ar_tmp[] = ["/home/bitrix/www/bitrix/cache/CNextCache/iblock/CIBlockSection_GetCount/", ""];
		$ar_tmp[] = ["/home/bitrix/www/bitrix/cache/CNextCache/iblock/CIBlockSection_GetList/", ""];
		$ar_tmp[] = ["/home/bitrix/www/bitrix/cache/s1/bitrix/iblock.vote/", ""];
		$ar_tmp[] = ["/home/bitrix/www/bitrix/cache/s1/bitrix/", "catalog.section.~"];
		$ar_tmp[] = ["/home/bitrix/www/bitrix/cache/", "iblock_find.~"];
		$ar_tmp[] = ["/home/bitrix/www/bitrix/cache/", "dd"];
		$this->ar_path = $ar_tmp;
	}

	function rdir($path, $add = 1, $delete = false)
	{
		// если путь существует и это папка
		if (file_exists( $path ) && is_dir($path))
		{
			// открываем папку
			$dir = opendir($path);
			while (false !== ($element = readdir($dir)))
			{
				// удаляем только содержимое папки
				if ($element != '.' && $element != '..')
				{
					// если последний символ $path не /
					$s_len = strlen($path);
					$last_symb = substr($path, $s_len - 1, 1);
					
					if (strcmp($last_symb , "/") === 0)
					{
						$tmp = $path . $element;
					}
					else
					{
						$tmp = $path . '/' . $element;
					}
					//echo "tmp = ".$tmp."<br>";
					chmod($tmp, 0777);
					
					// если элемент является папкой, то
					// удаляем его используя нашу функцию RDir
					$str_p = (string)$tmp;
					if (is_dir($tmp))
					{
						// если последний символ $path не /
						$s_len = strlen($str_p);
						$last_symb = substr($str_p, $s_len - 1, 1);
						
						if (strcmp($last_symb , "/") !== 0)
						{
							$str_p = $str_p."/";
						}
						
						$this->rdir($str_p, $add + 1, $delete);
						// если элемент является файлом, то удаляем файл
						if ($add === 0 || $add === 1)
						{
							$this->str_to_delete[] = $str_p;
						}
					}
					else
					{
						if ($delete)
						{
							unlink($str_p);
						}
					}
				}
			}
			
			// закрываем папку
			closedir($dir);
			// удаляем саму папку
			//if (file_exists($path))
			if (file_exists($path) && $delete)
			{
				if ($add === 0 || $add === 1)
				{
					$this->str_to_delete[] = $path;
					$path_short = substr($path, 0, -1);
					$this->str_to_delete[] = $path_short;
				}
				rmdir($path);
			}
		}
	}

	//function delete_ar_dirs($ar, $days_live_cache, $print = false, $delete = false)
	function delete_ar_dirs($mail_hours, $print = false, $delete = false, $clearTable = false) {
		
		foreach ($this->ar_path as $el)
		{
			if ($print)
			{
				dump($el);
			}
			$dir = $el[0];
			$templ = $el[1];
			if($handle = opendir($dir))
			{
				$cur_dt = mktime();
				
				//echo "mktime() = ".$cur_dt."<br>";
				while(false !== ($file = readdir($handle)))
				{
					$dir_path = $dir.$file."/";
					if($file != "." && $file != ".." && is_dir($dir_path))
					{
						$ret = filemtime($dir_path);
						
						$diff_days = ($cur_dt - $ret) / 86400;
						if ($print)
						{
							echo "Каталог : " . $dir_path.", filemtime = ".$ret.", diff_days = ".(string)$diff_days.", days_live_cache = ".(string)$this->days_live_cache."<br>";
							echo 'file = '.$file.'<br>';
						}
						
						if ($diff_days > $this->days_live_cache)
						{
							if ($templ === ""
								|| (strlen($file) > strlen($templ) && strpos($file, $templ) !== false
									&& strcmp($file, "s1") !== 0 && strcmp($file, "bx") !== 0))
							{
								$this->rdir($dir_path, 0, $delete);
							}
							
							if (strcmp($templ, "dd") === 0 && strlen($file) === 2 
								&& strcmp($file, "s1") !== 0 && strcmp($file, "bx") !== 0)
							{
								$this->rdir($dir_path, 0, $delete);
							}
						}
					}
				}
			}
		}
		
		if ($clearTable) {
			$this->clear_table(true);
		}
		
		$hours = (int)date('H');
		$minuts = (int)date('i');
		$seconds = (int)date('s');
		
		if (in_array($hours, $mail_hours))
		{
			$msubj = $this->text_base.' - Удаление кеша (очистка)';
			$mtext = $this->text_base.' - Удаление кеша завершено. Время: '.$hours.' : '.$minuts.' : '.$seconds;
			$in_mail = new inigs_mail;
			$in_mail->my_send_mail($this->admin_mail, $msubj, $mtext);
		}
	}
}

class WorkDates
{	
	function rangeAdd(string $strRange, int $numDays, string $delimeter, $note = true)
	{
		$strRange = str_replace(["день", "дня", "дней", " "], "", $strRange);
		$posDelim = strpos($strRange, $delimeter);
		
		if ($posDelim !== false)
		{
			$lPart = substr($strRange, 0, $posDelim);
			$rPart = substr($strRange, $posDelim + 1);
			$int_l = (int)$lPart;
			$int_r = (int)$rPart;
			$leftInt = $int_l + $numDays;
			$rightInt = $int_r + $numDays;
			$res = (string)$leftInt.$delimeter.(string)$rightInt;
			if ($note)
			{
				$res = $res." дней";
			}
			
			return $res;
		}
		else
		{
			$lPart = $strRange;
			$int_l = (int)$lPart;
			$leftInt = $int_l + $numDays;
			$res = (string)$leftInt;
			if ($note)
			{
				$res = $res." дней";
			}
			
			return $res;
		}
	}
	
	function dateDiff(int $year, int $month, int $day)
	{
		$curYear = (int)date("Y");
		$curMonth = (int)date("n");
		$curDay = (int)date("j");
		
		//[$curDay, $curMonth, $curYear] = [4, 1, 2019];
		
		$dateCur = mktime(0, 0, 1, $curMonth, $curDay, $curYear);
		$dateFuture = mktime(0, 0, 1, $month, $day, $year);
		
		$diff = $dateFuture - $dateCur; // разность между 2 датами в секундах
		
		$diffDay = (int)$diff / (24 * 60 * 60);
		return $diffDay;
	}
}