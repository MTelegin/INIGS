<?
	function company_stores()
	{
		$comp_stores = ["2", "7", "8"];
		//$comp_stores = ["2"];
		//$comp_stores = INIGS_COMPANY_STORES_ID;
		return $comp_stores;
	}

	// доступность товара
	// 0 - нет ни у кого
	// 1 - есть у нас
	// 2 - у нас нет, есть у поставщиков
	// 3 - у нас нет, поставщик данные не предоставляет	
	function product_avail($product_ID)
	{
		//use \Bitrix\Catalog\StoreTable;
		
		$arFilter = Array(
			"PRODUCT_ID" => $product_ID,
			//"STORE_ID" => $invent_ID
		);
		
		$res_store = CCatalogStoreProduct::GetList(
			Array(),
			$arFilter,
			false,
			false,
			Array("STORE_ID", "AMOUNT")
		);
		
		$available = 0;
		$deliv_time_str = "";
		$time = 50;
		$workDates = new WorkDates;
		
		//$arResult["PROPERTIES"]["OSTATKI_PREDOSTAVLYAYUTSYA_POSTAVSHCHIKOM"]["VALUE"];
		
		//$arFilter_el = Array("IBLOCK_ID" => 95, "ID" => $product_ID);
		$arFilter_el = Array("IBLOCK_ID" => INIGS_IBLOCK_CATALOG, "ID" => $product_ID);
		/*$res_elem = CIBlockElement::GetList(
			Array(),
			$arFilter,
			false,
			false,
			Array("STORE_ID", "AMOUNT")
		);*/
		
		// 6955 - ID свойства "Остатки предоставляются"
		$elems = CIBlockElement::GetList(
			Array(),
			$arFilter_el,
			false,
			false,
			//Array("PROPERTY_6955")
			Array("PROPERTY_17791")
			//Array("PROPERTY_OSTATKI_PREDOSTAVLYAYUTSYA_POSTAVSHCHIKOM")
		);
		$elem = $elems->Fetch();
		$have_data = false;
		//if (strcmp($elem["PROPERTY_6955_VALUE"], "Да") === 0)
		if (strcmp($elem["PROPERTY_17791_VALUE"], "Да") === 0)
		{
			//echo 'Ставим have_data = true'.'<br>';
			$have_data = true;
		}

		//$comp_stores = company_stores();
		//$comp_stores = company_stores();
		$comp_stores = ["2"]; // TO DO
		//dump($comp_stores);
		$holiday_NY = INIGS_NEW_YEAR_HOLIDAY;
		$diff = $workDates->dateDiff(2019, 1, 9);
		while ($arStore = $res_store->Fetch())
		{
			$prod_quantity = (int)$arStore["AMOUNT"];
			$store_id = $arStore["STORE_ID"];
			//dump($arStore);
			//if (($store_id === "2" || $store_id === "7" || $store_id === "8") && $prod_quantity > 0) // это наш склад
			if (in_array($store_id, $comp_stores) && $prod_quantity > 0) // это наш склад
			{
				$available = 1;
				//return [1, ""];
				if ($holiday_NY) {
					return [1, $workDates->rangeAdd("0 дней", $diff, "-"), $diff];
				}
				else {
					return [1, "", 0];
				}
			}
			
			//if (($store_id !== "2" && $store_id !== "7") && $prod_quantity > 0) // склады партнеров - брендов
			if (!in_array($store_id, $comp_stores) && $prod_quantity > 0) // склады партнеров - брендов
			{
				$filter = array("ID" => $store_id);
				//var_dump($filter);
				$stores = CCatalogStore::GetList(
					array(),
					$filter,
					false,
					false,
					array("ID", "UF_DELIVERY_TIME", "UF_DELIVERY_TIME_STR")
				);
				
				$line = $stores->Fetch();
				$deliv_time = $line["UF_DELIVERY_TIME"];
				$deliv_time_str = $line["UF_DELIVERY_TIME_STR"];
				
				if ($deliv_time < $time)
				{
					$time = $deliv_time;
					$time_str = $deliv_time_str;
				}
				
				$available = 2;
			}
		}
		
		if ($available === 0 && $have_data === false)
			$available = 3;
		
		//return [$available, $deliv_time_str, $deliv_time];
		if ($holiday_NY)
		{
			return [$available, $workDates->rangeAdd($deliv_time_str, $diff, "-"), $deliv_time + $diff];
		}
		else
		{
			return [$available, $deliv_time_str, $deliv_time];
		}
	}
	
	function calc_send_day(int $number, int $numday = 0, string $month_st = "")
	{
		$work_day = $number + $numday;
		$st = (string)$work_day." ".$month_st;
		return $st;
	}
	
	// На входе параметр - как возврат функции product_avail
	// Возвращаем 1 - купить 2 - под заказ 3 - подобрать аналог
	function status_prodavail($par, $sec = 0)
	{
		if ($par === 0)
		{
			return 3;
		}
		elseif ($par === 0)
		{
			return 2;
		}
		elseif ($par === 1)
		{
			return 1;
		}
		elseif ($par === 2)
		{
			return 2;
		}
		elseif ($par === 3)
		{
			return 2;
		}
		elseif ($par === 3)
		{
			return 2;
		}
	}
	
	function is_main_inigs_server()
	{
		$server_name = "";
		if (isset($_SERVER) && isset($_SERVER["SERVER_NAME"]))
		{
			$server_name = trim($_SERVER["SERVER_NAME"]);
			$server_name = str_replace("https", "", $server_name);
			$server_name = str_replace("http", "", $server_name);
			$server_name = str_replace("://", "", $server_name);
		}
		
		$inigs_test_domains = INIGS_TEST_SITE_NAMES;
		if (!in_array($server_name, $inigs_test_domains))			
		{
			return true; //false;
		}
		else
		{
			return false;
		}
	}
	
	function getCertImages($brand = "", $file = true, $thumb = false, $warranty = false, $warranty_thumb = false, $service = false, $service_thumb = false)
	{
		$arFilter = array('IBLOCK_ID' => INIGS_IBLOCK_CERTIF_ID, 'CODE' => $brand);
		$rsElement = CIBlockElement::GetList(
			array(),
			$arFilter,
			false,
			false,
			array(
				'NAME',
				'CODE',
				'PROPERTY_FILE_CERT',
				'PROPERTY_FILE',
				'PROPERTY_THUMB',
				'PROPERTY_WARRANTY',
				'PROPERTY_WARRANTY_THUMB',
				'PROPERTY_SERVICE',
				'PROPERTY_SERVICE_THUMB',				
			)
		);
		
		$arRes = array();
		while ($arElement = $rsElement->GetNext())
		{
			$loc_thumb = $arElement["PROPERTY_THUMB_VALUE"];
			$loc_path = $arElement["PROPERTY_FILE_VALUE"];
			$loc_warranty = $arElement["PROPERTY_WARRANTY_VALUE"];
			$loc_war_thumb = $arElement["PROPERTY_WARRANTY_THUMB_VALUE"];
			$loc_serv = $arElement["PROPERTY_SERVICE_VALUE"];
			$loc_serv_thumb = $arElement["PROPERTY_SERVICE_THUMB_VALUE"];
			
			$newEl = array();
			$newEl["FILE"] = $loc_path;
			
			if ($thumb)
			{
				$newEl["THUMB"] = $loc_thumb;
			}
			
			if ($warranty)
			{
				$newEl["WARRANTY"] = $loc_warranty;
			}
			
			if ($warranty_thumb)
			{
				//$newEl[] = array("WARRANTY_THUMB" => $loc_path);
				$newEl["WARRANTY_THUMB"] = $loc_war_thumb;
			}
			
			if ($service)
			{
				$newEl["SERVICE"] = $loc_serv;
			}
			
			if ($service_thumb)
			{
				//$newEl[] = array("WARRANTY_THUMB" => $loc_path);
				$newEl["SERVICE_THUMB"] = $loc_serv_thumb;
			}
			
			$arRes[] = $newEl;
		}
		return $arRes;
		//return $newEl;
	}
	
	function show_city_popup($city_id)
	{
		//$arFilter = array("IBLOCK_ID" => 80, "ID" => $city_id);
		$arFilter = array("IBLOCK_ID" => INIGS_IBLOCK_CITIES_ID, "ID" => $city_id);
		$arSelect = Array("ID", "IBLOCK_ID", "NAME", "PROPERTY_SHOW_CITY");//IBLOCK_ID и ID обязательн
		$el = CIBlockElement::GetList(
			array("SORT"=>"ASC"),
			$arFilter,
			false,
			false,
			$arSelect
		);
		$show_city = "Нет";
		while($ob = $el->GetNextElement())
		{
			$arFields = $ob->GetFields();
			$show_city = $arFields["PROPERTY_SHOW_CITY_VALUE"];
		}
		return $show_city;
	}
?>