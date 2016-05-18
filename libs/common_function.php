<?php
//注入函数, 显示模板前传入系统配置系统
function output_global_vars($controller) {
	global $app_config;
	global $spConfig;
	$controller->web_root = WEB_ROOT;
	$controller->app_config = $app_config;
	$controller->urlsep = $spConfig['ext']['spUrlRewrite']['sep'] ? $spConfig['ext']['spUrlRewrite']['sep'] : '&';
	$controller->urlconn = $spConfig['ext']['spUrlRewrite']['sep'] ? $spConfig['ext']['spUrlRewrite']['sep'] : '=';
	$controller->date_version = date("Ymd");
	if(strtolower($_SERVER['HTTP_HOST']) == "localhost")
		$controller->testing = 1;
}

if (!function_exists('json_encode')) {
    function json_encode($data) {
        switch ($type = gettype($data)) {
            case 'NULL':
                return 'null';
            case 'boolean':
                return ($data ? 'true' : 'false');
            case 'integer':
            case 'double':
            case 'float':
                return $data;
            case 'string':
                return '"' . addslashes($data) . '"';
            case 'object':
                $data = get_object_vars($data);
            case 'array':
                $output_index_count = 0;
                $output_indexed = array();
                $output_associative = array();
                foreach ($data as $key => $value) {
                    $output_indexed[] = json_encode($value);
                    $output_associative[] = json_encode($key) . ':' . json_encode($value);
                    if ($output_index_count !== NULL && $output_index_count++ !== $key) {
                        $output_index_count = NULL;
                    }
                }
                if ($output_index_count !== NULL) {
                    return '[' . implode(',', $output_indexed) . ']';
                } else {
                    return '{' . implode(',', $output_associative) . '}';
                }
            default:
                return ''; // Not supported
        }
    }
}


function getExchangeRate($from_Currency,$to_Currency){
	$amount = urlencode($amount);
	$from_Currency = urlencode($from_Currency);
	$to_Currency = urlencode($to_Currency);
	if(!$from_exchange = get_exchange_config($from_Currency)){
		$url = "http://download.finance.yahoo.com/d/quotes.csv?s=CNY".$from_Currency."=X&f=sl1d1t1ba&e=.csv";
		$ch = curl_init();
		$timeout = 0;
		$rawdata = "";
		$from_exchange = $data = array();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT , "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)");
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$rawdata = curl_exec($ch);
		curl_close($ch);
		$data = explode(',', $rawdata);
		$from_exchange["exchange"] = $data[1];
		$from_exchange["time"] = time();
		set_exchange_config($from_Currency, $from_exchange["exchange"]);
	}
	if(!$to_exchange = get_exchange_config($to_Currency)){
		$url = "http://download.finance.yahoo.com/d/quotes.csv?s=CNY".$to_Currency."=X&f=sl1d1t1ba&e=.csv";
		$ch = curl_init();
		$timeout = 0;
		$rawdata = "";
		$to_exchange = $data = array();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT , "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)");
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$rawdata = curl_exec($ch);
		curl_close($ch);
		$data = explode(',', $rawdata);
		$to_exchange["exchange"] = $data[1];
		$to_exchange["time"] = time();
		set_exchange_config($to_Currency, $to_exchange["exchange"]);
	}
	$exchange["exchange"] = $to_exchange["exchange"] / $from_exchange["exchange"];
	$exchange["time"] = max($from_exchange["time"], $to_exchange["time"]);
	$exchange["desc_exchange"] = $from_exchange["exchange"] / $to_exchange["exchange"];
	return $exchange;
}

function get_exchange_config($Currency){
	if($Currency == "CNY"){
		$exchange_config["exchange"] = 1;
		$exchange_config["time"] = 0;
		return $exchange_config;
	}
	include(dirname(dirname(__FILE__)) . DS . "exchange_config.php");
	if($exchange_config["to".$Currency] && (date("Y-m-d", $exchange_config["to".$Currency]["time"]) == date("Y-m-d")))
		return $exchange_config["to".$Currency];
	return "";
}

function set_exchange_config($Currency, $exchange){
	if(!floatval($exchange))
		return false;
	include(dirname(dirname(__FILE__)) . DS . "exchange_config.php");
	$exchange_config["to".$Currency] = array("exchange"=>$exchange, "time"=>time(), "formattime"=>date("Y-m-d H:i:s"), "userid"=>$_SESSION["sscrm_user"]["id"]);
	$str = "<?php\r\n\$exchange_config = ".var_export($exchange_config, true).';';
	$fp = fopen(dirname(dirname(__FILE__)) . DS . 'exchange_config.php', 'w');
	if (!fputs($fp,$str)){
		throw new Exception("未知错误，更新失败");
	}
}
?>