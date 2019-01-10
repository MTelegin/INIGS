<?php
$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__)."/../../../..");
$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS",true);
//define("BX_CRONTAB_SUPPORT", true);
define("BX_CRONTAB", true);
define("BX_WITH_ON_AFTER_EPILOG", true);
define("BX_NO_ACCELERATOR_RESET", true);

$path = "/home/bitrix/www/bitrix/modules/main/include/prolog_before.php";
require_once($path);

$path_const = "/home/bitrix/www/local/php_interface/inigs_constants.php";
require_once($path_const);

//$path_class = "/home/bitrix/www/local/php_interface/inigs_classes.php";
//require_once($path_class);

//require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

@set_time_limit(0);
@ignore_user_abort(true);

CEvent::CheckEvents();

if(CModule::IncludeModule("sender"))
{
	\Bitrix\Sender\MailingManager::checkPeriod(false);
	\Bitrix\Sender\MailingManager::checkSend();
}

//global $DB;
$is_work_base = true;
$server_name = COption::GetOptionString("main", "server_name");
$server_name = strtolower($server_name);

if (in_array($server_name, ["inigs.ru"]))
{
	$is_work_base = true;
}

if ($is_work_base === true)
{
	$admin_mail = "mtelegin@yandex.ru";
	$text_base = "WORK";
}
else
{
	$admin_mail = "test@inigs.ru";
	$text_base = "TEST";
}

$hours = (int)date("H");
$minuts = (int)date("i");
$seconds = (int)date("s");

//mail("mtelegin@yandex.ru", "WORK - CRON ", "WORK - CRON ");

/* Переиндексация модуля iblock */

$obj = new inigs;
//$obj->set_admin_mail(admin_mail = $admin_mail;
$obj->set_admin_mail($admin_mail);
$obj->set_text_base($text_base);

// заполняем Код 1С где не заполнено
if ((in_array($hours, [2, 12, 20]) && in_array($minuts, [15]))
	|| (in_array($hours, [2]) && in_array($minuts, [46])))
{
	$obj->fillCode1C_All();
}

// деактивация элементов
//if ((in_array($hours, [1, 2, 6, 9, 13, 17, 21, 22, 23]) && in_array($minuts, [3]))
if ((in_array($hours, [6, 9, 13, 17, 23]) && in_array($minuts, [21]))
	|| (in_array($hours, [2]) && in_array($minuts, [33])))
{
	$ar = [11];
	$obj->deactivate_elems($ar);
}

// в наличии
if ((in_array($hours, [1, 9, 11, 12, 13, 15, 19]) && in_array($minuts, [14, 23]))
	|| (in_array($hours, [2, 4]) && in_array($minuts, [2])))
{
	$ar = [11, 12];
	$obj->fill_in_store($ar);
}

// упорядочивание элементов
if (in_array($hours, [3, 13, 17]) && in_array($minuts, [26]))
{
	$ar = [8, 9 , 10, 11, 12, 13, 14, 15, 16, 17, 18];
	$obj->elem_ordering($ar);
}

if (in_array($hours, [2, 4]) && in_array($minuts, [5]))
{
	$ar = [2];
	$obj->fill_weight_gr($ar);
}

// заполняем вес и габариты
if ((in_array($hours, [9, 11, 15]) && in_array($minuts, [29]))
//if ((in_array($hours, [4, 9, 11, 15, 16, 17, 19]) && in_array($minuts, [24]))
	|| (in_array($hours, [2, 4]) && in_array($minuts, [10])))
{
	$ar = [11, 15];
	$obj->fill_weight($ar);
}

// yandex.php
if ((in_array($hours, [9, 11, 13, 15, 17, 19]) && in_array($minuts, [33])) // 26
	//|| (in_array($hours, [15]) && in_array($minuts, [58]))
	//|| (in_array($hours, [13]) && in_array($minuts, [30])))
	|| (in_array($hours, [2, 4]) && in_array($minuts, [17])))
{
	$ar = [11, 13, 15, 17];
	$obj->export_yandex($ar);
}

// yandex_tp.php
if (in_array($hours, [4]) && in_array($minuts, [52]))
//if ((in_array($hours, [14]) && in_array($minuts, [2])))
{
	$ar = [4];
	$obj->export_yandex_tp($ar);
}

// только ночью

// заполнение бренда у товаров
if (in_array($hours, [4]) && in_array($minuts, [2]))
{
	$obj->fill_brands();
}

// google.xml
if (in_array($hours, [5]) && in_array($minuts, [35]))
{
	$ar = [5];
	$obj->export_google($ar);
}

// очистка корзин
if (in_array($hours, [6, 7]) && in_array($minuts, [3, 12, 21, 30, 39, 48]))
{
	$ar = [5];
	$obj->clear_empty_baskets($ar);
}

// чистка кеша на сайте
//if (in_array($hours, [4, 15]) && in_array($minuts, [3, 59]))
if (in_array($hours, [3]) && in_array($minuts, [2])
	|| in_array($hours, [10, 14, 18]) && in_array($minuts, [10]))
{
	mail("mtelegin@yandex.ru", "clear cache start", "clear cache start");
	$ar = [4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19];
	$obj = new Delete_cache;
	$obj->init_ar_path();
	
	// params: 1 - print, 2 - delete
	$obj->delete_ar_dirs($ar, false, true, true);
}

$path = "/home/bitrix/www/bitrix/modules/main/tools/backup.php";
require($path);
CMain::FinalActions();

//require($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/tools/backup.php");
?>