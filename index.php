<?php
define('DS', DIRECTORY_SEPARATOR);
define("APP_PATH", dirname(__FILE__));
define("SP_PATH", realpath('SpeedPHPs'));
define("WEB_ROOT", '/sscrm');
date_default_timezone_set('PRC');

if(strtolower($_SERVER['HTTP_HOST']) != "localhost"){
	/*
	header("Content-Type: text/html;charset=utf-8");
	echo "ERP系统暂时维护中,预计维护时间1小时";
	exit();
	*/
	/*
	header("Content-Type: text/html;charset=utf-8");
	echo "<script type=text/javascript>";
	echo "alert('ERP系统已放到服务器上，您可随时在服务器上进行操作');";
	echo "alert('现将跳转到服务器地址 http://erp.sswcdc.com/ ，您以后可通过该域名直接进行访问');";
	echo "window.location.href='http://erp.sswcdc.com/';";
	echo "</script>";
	exit();
	*/
	/*
	@header("location: http://erp.sswcdc.com/");
	exit();
	*/
}

$spConfig = array(
	'mode' => 'debug', // 应用程序模式，默认为调试模式
	'nofound' => 'nofound.php', // 错误页面
	'db_debug' => TRUE,
	'session_name' => 'sscrm_user',
	'session_privacy_name' => 'privacy',
	'none_check_privacy' => array(
		 'main_login',
		 'main_logout'
	),
	'home_url' => array('main', 'index'),
	'login_url' => array('main', 'login'),
	'include_path' => array(
        APP_PATH . DS . 'libs',
        APP_PATH . DS . 'libs' . DS . 'captcha',
        APP_PATH . DS . 'libs' . DS . 'phpMailer',
      	APP_PATH . DS . 'libs' . DS . 'xheditor'
    ),
    'inject'=> array(
		'before_display'=> array('output_global_vars')
	),
	'model_path' => APP_PATH . DS . 'model',
	'launch' => array( 
		 'router_prefilter' => array( 
			array('spAcl', 'maxcheck') // 开启有限的权限控制
		 )
	 )
);
if(strtolower($_SERVER['HTTP_HOST']) == "localhost")
	$local_debug = 1;
$spConfig['db'] = $local_debug ? require(APP_PATH . DS . 'db_config_local.php') : require(APP_PATH . DS . 'db_config.php');
$spConfig['view'] = require(APP_PATH . DS . 'smarty_config.php');
require(APP_PATH . DS . ($local_debug ? 'app_config_local.php' : 'app_config.php'));
require(SP_PATH  . DS . "SpeedPHP.php");
require(APP_PATH  . DS . "libs" . DS . "common_function.php");
if($_SESSION['sscrm_user'] && $_SESSION['sscrm_user']['loginhost'] != strtolower($_SERVER['HTTP_HOST']).WEB_ROOT)
	unset($_SESSION['sscrm_user']);
if (get_magic_quotes_gpc())
{
	$in = array(& $_GET, & $_POST, & $_COOKIE, & $_REQUEST);
	while (list ($k, $v) = each($in))
	{
		foreach ($v as $key => $val)
		{
			if (! is_array($val))
			{
				$in[$k][$key] = stripslashes($val);
				continue;
			}
			$in[] = & $in[$k][$key];
		}
	}
	unset($in);
}
spRun();
?>