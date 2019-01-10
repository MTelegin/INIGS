<?
	$inigs_is_work_server = true;
	
	if ($inigs_is_work_server)
	{
		define("INIGS_IBLOCK_ID_BRANDS", 90); // Контент / ИБ/ ТипыИБ / Контент (aspro.next) / Бренды
		define("INIGS_IBLOCK_CATALOG", 95); // Основной каталог товаров
		define("INIGS_IBLOCK_CITIES_ID", 80); // Контент / Региональность (aspro.next) / Регионы
		define("INIGS_IBLOCK_CERTIF_ID", 34); // Контент / Системный / Сертификаты
		define("INIGS_ASK_FORM_ID", 6); // Сервисы / Веб-формы / Настройка форм / ASK (Задать вопрос)
		define("INIGS_COMPANY_STORES_ID", ["2", "7", "8"]); // Магазин / Складской учет / Склады
		
		define("INIGS_YANDEX_EXPORT_ID", 8); // ID экспорт яндекс yandex.php
		define("INIGS_YANDEX_TP_EXPORT_ID", 11); // ID экспорт яндекс TP yandex_TP.php
		define("INIGS_GOOGLE_EXPORT_ID", 10); // ID экспорт Google google.xml
		
		define("INIGS_PROPERTY_CML2_ATTR_ID", 7490); // Каталог / Свойство "Характеристики"
		
		define("INIGS_NE_VYVODIT_NA_SAYTE_ID", 18469); // Свойство "Не выводить на сайте"
		define("INIGS_NE_VYVODIT_NA_SAYTE_TRUE", 170083); // значение "Да" у "Не выводить на сайте"
		
		define("INIGS_PROPERTY_CODE_1C_ID", 20711); // Свойство "Код 1С"
		define("INIGS_PROPERTY_AVAILABLE", 7507); // Свойство "В наличии"
		define("INIGS_PROPERTY_AVAILABLE_TRUE", 68044); // Свойство "В наличии" - ДА
		define("INIGS_PROPERTY_AVAILABLE_FALSE", 198779); // Свойство "В наличии" - НЕТ
		
		// 5839	- Расходка, 5470 - Аэраторы, 6101 - Спецодежда и СИЗ
		define("INIGS_SECTIONS_QUERY_EXLUDE", "(5839, 5470, 6101, 6615)");
		define("INIGS_HIGHLOAD_SEOBRANDS_ID", 22); // Контент / Highload блоки / Seobrands
		define("INIGS_HIGHLOAD_BRENDS_ID", 5); // Контент / Highload блоки / BREND
		
		define("INIGS_BRAND_STIHL", '77964'); // Свойство "Бренд" у ИБ "Каталог" / STIHL
		
		define("INIGS_MAX_SEARCH_COUNT" , 3000); // Максимальное количество элементов в поиске
		define("INIGS_CACHE_LIVE_DAYS", 1); // Сколько дней хранить кеш
		define("INIGS_PRICE_GROUP_ID", 2); // ID Типа цены 2 - PRICE_BASE
		define("INIGS_NEW_YEAR_HOLIDAY", false); // праздник новый год
	}
	else
	{
		/*Тестовый сервер*/
		define("INIGS_IBLOCK_ID_BRANDS", 90); // Контент / ИБ/ ТипыИБ / Контент (aspro.next) / Бренды
		define("INIGS_IBLOCK_CATALOG", 95); // Основной каталог товаров
		define("INIGS_IBLOCK_CITIES_ID", 80); // Контент / Региональность (aspro.next) / Регионы
		define("INIGS_IBLOCK_CERTIF_ID", 34); // Контент / Системный / Сертификаты
		define("INIGS_ASK_FORM_ID", 6); // Сервисы / Веб-формы / Настройка форм / ASK (Задать вопрос)
		define("INIGS_COMPANY_STORES_ID", ["2", "7", "8"]); // Магазин / Складской учет / Склады
		
		define("INIGS_YANDEX_EXPORT_ID", 8); // ID экспорт яндекс yandex.php
		define("INIGS_YANDEX_TP_EXPORT_ID", 11); // ID экспорт яндекс TP yandex_TP.php
		define("INIGS_GOOGLE_EXPORT_ID", 10); // ID экспорт Google google.xml

		define("INIGS_PROPERTY_CML2_ATTR_ID", 7490); // Каталог / Свойство "Характеристики"
		
		define("INIGS_NE_VYVODIT_NA_SAYTE_ID", 18469); // Свойство "Не выводить на сайте"
		define("INIGS_NE_VYVODIT_NA_SAYTE_TRUE", 170083); // значение "Да" у "Не выводить на сайте"
		
		define("INIGS_PROPERTY_CODE_1C_ID", 20711); // Свойство "Код 1С"
		define("INIGS_PROPERTY_AVAILABLE", 7507); // Свойство "В наличии"
		define("INIGS_PROPERTY_AVAILABLE_TRUE", 68044); // Свойство "В наличии" - ДА
		define("INIGS_PROPERTY_AVAILABLE_FALSE", 198779); // Свойство "В наличии" - НЕТ
		
		// 5839	- Расходка, 5470 - Аэраторы, 6101 - Спецодежда и СИЗ
		define("INIGS_SECTIONS_QUERY_EXLUDE", "(5839, 5470, 6101)");
		define("INIGS_HIGHLOAD_SEOBRANDS_ID", 22); // Контент / Highload блоки / Seobrands
		define("INIGS_HIGHLOAD_BRENDS_ID", 5); // Контент / Highload блоки / BREND
		
		define("INIGS_BRAND_STIHL", '77964'); // Свойство "Бренд" у ИБ "Каталог" / STIHL
		
		define("INIGS_MAX_SEARCH_COUNT" , 3000); // Максимальное количество элементов в поиске
		define("INIGS_CACHE_LIVE_DAYS", 1); // Сколько дней хранить кеш
		define("INIGS_PRICE_GROUP_ID", 2); // ID Типа цены 2 - PRICE_BASE
		define("INIGS_NEW_YEAR_HOLIDAY", false); // праздник новый год
	}
	
	define("INIGS_ITEM_IN_STORE", "ITEM_IN_STORE"); // В наличии свойство текстом
	
	// 46.146.210.114 - ip seo office
	// ip офиса и ip хостинга dev и test для скриптов
	define("INIGS_IP_ADDRESSES", ['82.112.32.58', '91.226.81.67', '46.146.210.114', '185.93.110.67']);
	// тестовые домены
	define("INIGS_TEST_SITE_NAMES", ["new.inigs.ru", "test.inigs.ru", "dev.inigs.ru", "demo.inigs.ru"]);
	
	define("INIGS_SUM_FREE_DELIVERY", 5000); // Мин. стоимость заказа для бесплатной доставки
	define("INIGS_DELIVERY_COST", 100); // Стоимость доставки по Екатеринбургу (при платной доставке)
	
	define("INIGS_COUNT_PARTS_QUERIES", 2); // Количество запросов для построения файлов типа yandex.php
