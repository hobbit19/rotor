<?php
$debugmode = 1;

if ($debugmode) {
	@error_reporting(E_ALL);
	@ini_set('display_errors', true);
	@ini_set('html_errors', true);
	@ini_set('error_reporting', E_ALL);
} else {
	@error_reporting(E_ALL ^ E_NOTICE);
	@ini_set('display_errors', false);
	@ini_set('html_errors', false);
	@ini_set('error_reporting', E_ALL ^ E_NOTICE);
}

define('STARTTIME', microtime(1));
define('BASEDIR', dirname(dirname(__FILE__)));
define('DATADIR', BASEDIR.'/storage');
define('HOME', BASEDIR.'/public');
define('SITETIME', time());
define('PCLZIP_TEMPORARY_DIR', BASEDIR.'/local/temp/');

session_name('SID');
session_start();

if (file_exists(BASEDIR.'/includes/connect.php')) {
	include_once (BASEDIR.'/includes/connect.php');
} else {
	die('Переименуйте файл connect.example.php в connect.php в директории include!');
}

include_once BASEDIR.'/vendor/autoload.php';

// -------- Автозагрузка классов ---------- //
function autoloader($class) {

	$class = str_replace('\\', '/', $class);
	if (file_exists(BASEDIR.'/includes/classes/'.$class.'.php')) {
		include_once BASEDIR.'/includes/classes/'.$class.'.php';
	}
}

spl_autoload_register('autoloader');

include_once BASEDIR.'/includes/routes.php';

DBM::run()->config(DBHOST, DBNAME, DBUSER, DBPASS, DBPORT);

if (!file_exists(DATADIR.'/temp/setting.dat')) {
	$queryset = DB::run() -> query("SELECT `setting_name`, `setting_value` FROM `setting`;");
	$config = $queryset -> fetchAssoc();
	file_put_contents(DATADIR.'/temp/setting.dat', serialize($config), LOCK_EX);
}
$config = unserialize(file_get_contents(DATADIR.'/temp/setting.dat'));

date_default_timezone_set($config['timezone']);
