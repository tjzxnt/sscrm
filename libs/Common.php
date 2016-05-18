<?php

class Common{
	/**
	 * 发送邮件（使用系统定义的SMTP帐号）
	 * @param $email    收信人邮箱
	 * @param $subject  信件主题
	 * @param $body     信件内容
	 * @param $toname   收信人名称（可选）
	 * Written by: zyyutian
	 */	 
	public static function SendMail($info_arr) {
		global $app_config;
		
		$PHPMailer = spClass('PHPMailer');

		$PHPMailer->SMTPAuth   = true; 
		$PHPMailer->IsSMTP(); // telling the class to use SMTP
	    $PHPMailer->Host = $info_arr['mail_server'];
	    $PHPMailer->Port = $info_arr['mail_port'];
	    $PHPMailer->From = $info_arr['mail_from'];
	    $PHPMailer->FromName = $info_arr['mail_fromname'];	//发件人
	    $PHPMailer->Username = $info_arr['mail_user'];
	    $PHPMailer->Password = $info_arr['mail_pass'];

		//$PHPMailer->SMTPDebug = true;
		
		$PHPMailer->CharSet = 'utf-8';
		$PHPMailer->Subject = $info_arr['subject'];
		$PHPMailer->MsgHTML($info_arr['body']);
		if($info_arr['toname']){
			$PHPMailer->AddAddress($info_arr['email'], $info_arr['toname']);
		}
		else {
			$PHPMailer->AddAddress($info_arr['email']);
		}
		if(!$PHPMailer->Send()) {
			//print_r($PHPMailer->ErrorInfo);
	  		//JError($PHPMailer->ErrorInfo);
			return false;
		} 
		else {
			return true;
		}
	}
	
	/**
	 * 加密/解密字符串
	 * @param  $string     需加密字符串
	 * @param  $operation  操作 加密: ENCODE 解密: DECODE，缺省为加密
	 * @param  $key        加密密钥
	 */
	public static function authcode($string, $operation = 'ENCODE', $key = 'jedoo.com') {
		 $key = md5($key);
		 $key_length = strlen($key);
		 $string = $operation == 'DECODE' ? base64_decode($string) : substr(md5($string.$key), 0, 8).$string;
		 $string_length = strlen($string);
		 $rndkey = $box = array();
		 $result = '';
		 for($i = 0; $i <= 255; $i++) {
			 $rndkey[$i] = ord($key[$i % $key_length]);
			 $box[$i] = $i;
		 }
	
		for($j = $i = 0; $i < 256; $i++) {
			$j = ($j + $box[$i] + $rndkey[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		}
		for($a = $j = $i = 0; $i < $string_length; $i++) {
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$tmp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;
			$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
		 }
	
		if($operation == 'DECODE') {
			if(substr($result, 0, 8) == substr(md5(substr($result, 8).$key), 0, 8)) {
				return substr($result, 8);
			}
			else {
				return '';
			}
		 }
		 else {
			 return str_replace('=', '', base64_encode($result));
		 }
	}
	
	/**
	 * 生成随机数
	 * 
	 * @param  int  $length 控制循环次数，生成$length位的随机数   
	 * @return int          返回生成的随机数
	 */
	public static function randomkeys($length){
		$rangenum = "";  //生成随机数
		
		$str = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890";			
		for($i=0; $i<$length; $i++){
			$rangenum .= $str{mt_rand(0, 61)};
		}        
		return $rangenum;
	}
	
	/**
	 * UTF8字符串截取
	 *
	 *
	 */	
	public static function utf8_substr($str, $start, $length, $dot = ' ...')
	{		
		if (function_exists('mb_substr')) 
		{
			if($dot != ''){
				if(strlen($str) <= $length){
					$dot = "";
				}
			}
			return mb_substr($str, $start, $length, 'UTF-8') .$dot;
		}
		
		preg_match_all("/./u", $str, $arr);
		if($dot != ''){
			if(strlen($arr[0]) <= $length){
				$dot = "";
			}		
		}		
		return implode("", array_slice($arr[0], $start, $length)) . $dot;	
	}
	
	/**
	 * summary的处理
	 */
	public static function summarydeal(&$arr, $frield, $length = 35){	
		if($arr){
			foreach($arr as $k=>$v){
				if(is_array($v)){					
					$arr[$k][$frield] = self::utf8_substr(self::trimHtml(strip_tags($v[$frield])), 0, intval($length));
				}else{
					if($k == $frield){
						$arr[$frield] = self::utf8_substr(self::trimHtml(strip_tags($v)), 0, intval($length));
						break;
					}
				}
			}
		}
	}
	
	/**
	 * 清除字符串首尾空格
	 *
	 */
	public static function trimHtml($str){
		$str = str_replace('&nbsp;', ' ', $str);
		return trim($str);
	}	
	
	/**
	 * 获取IP 
	 *
	 */
	public static function GetIP() { 
		if ($_SERVER["HTTP_X_FORWARDED_FOR"]) 
			$ip = $_SERVER["HTTP_X_FORWARDED_FOR"]; 	
		else if ($_SERVER["HTTP_CLIENT_IP"]) 	
			$ip = $_SERVER["HTTP_CLIENT_IP"]; 	
		else if ($_SERVER["REMOTE_ADDR"]) 	
			$ip = $_SERVER["REMOTE_ADDR"]; 	
		else if (getenv("HTTP_X_FORWARDED_FOR")) 	
			$ip = getenv("HTTP_X_FORWARDED_FOR"); 	
		else if (getenv("HTTP_CLIENT_IP")) 	
			$ip = getenv("HTTP_CLIENT_IP"); 	
		else if (getenv("REMOTE_ADDR")) 	
			$ip = getenv("REMOTE_ADDR"); 	
		else 	
			$ip = ""; 	
		return $ip; 
	} 
	
	/**
	 * 检查标签是否合法
	 *
	 */
	public static function checktags($tags, $num = 30, $length = 150){
		$flag = true;
		$tags = preg_replace("/[\s@#,?<>$%^&*()!`~|{}:;\"'.，；。]+/u", ' ', $tags);
		if(strlen($tags) > $length){
			$flag = false;
		}
		else{
			$tags = explode(' ', $tags);		
			foreach($tags as $v){ 
				if(strlen($v) > $num){
					$flag = false;
					break;
				}
			}
		}
		
		if($flag){
			return $tags;
		}
		else{
			return $flag;
		}
	}


	/**
	 * 删除文件夹及其子文件夹和文件
	 *
	 * @param string $dir
	 * @return bool
	 */
	public static function deldir($dir) {
		$dh = opendir($dir);
		while ($file = readdir($dh)) {
			if($file != "." && $file != ".." && $file != ".svn"){
				$fullpath = $dir . "/" . $file;
				if(!is_dir($fullpath)){
					unlink($fullpath);
				}
				else{
					deldir($fullpath);
				}
			}
		}
		closedir($dh);
		if(rmdir($dir)){
			return true;
		}
		else{
			return false;
		}
	}
	
	/**
	 * 清空文件夹
	 *
	 * @param string $dir
	 * @return bool
	 */
	public static function cleardir($dir) {
		if ($dh = opendir($dir)) {
			while ($file = readdir($dh)) {
				if($file != "." && $file != ".." && $file != ".svn"){
					$fullpath = $dir . "/" . $file;
					if(!is_dir($fullpath)){
						unlink($fullpath);
					}
					else{
						deldir($fullpath);
					}
				}
			}
			closedir($dh);
			return true;
		}else{
			return false;
		}
	}
	
	/**
	 * 创建多级目录并设置权限
	 *
	 * Written by: robin
	 */
	public static function rmkdir($path, $mode = 0766) {
		if(!is_dir($path)) {
			$path = rtrim(preg_replace(array("/\\\\/", "/\/{2,}/"), "/", $path), "/");
			$pos = strrpos($path, '/');
			if($pos >0 && Common::rmkdir(substr($path, 0, $pos))) {
				return @mkdir($path, $mode);
			}
			return @mkdir($path, $mode);
		}
		else {
			return true;
		}
	}
	
	/**
	 * 遍历文件夹下文件夹
	 *
	 * Written by: zx
	 */
	function getDir($dir) {
	    $dirArray[]=NULL;
	    if (false != ($handle = opendir ( $dir ))) {
	        $i=0;
	        while ( false !== ($file = readdir ( $handle )) ) {
	            //去掉"“.”、“..”以及带“.xxx”后缀的文件
	            if ($file != "." && $file != ".."&&!strpos($file,".")) {
	                $dirArray[$i]=$file;
	                $i++;
	            }
	        }
	        //关闭句柄
	        closedir ( $handle );
	    }
	    return $dirArray;
	}	 
	
	/**
	 * 遍历文件夹下文件
	 *
	 * Written by: zx
	 */
	
	function getFile($dir, $type = null) {
	    $fileArray[]=NULL;
	    if (false != ($handle = opendir ( $dir ))) {
	        $i=0;
	        while ( false !== ($file = readdir ( $handle )) ) {
	            if (strpos($file,".") && ($type ? in_array(strtolower(substr($file, strpos($file,".")+1)), $type) : 1)) {
	                $fileArray[$i]= "".$file;
	                if($i==100){
	                    break;
	                }
	                $i++;
	            }
	        }
	        //关闭句柄
	        closedir ( $handle );
	    }
	    return $fileArray;
	}
	
	//判断是否是正确的ip地址
	function isIP($ip){
		return preg_match('/^((?:(?:25[0-5]|2[0-4]\d|((1\d{2})|([1-9]?\d)))\.){3}(?:25[0-5]|2[0-4]\d|((1\d{2})|([1 -9]?\d))))$/', $ip);
	}
	
	//序列字符串中是否在指定数组中存在
	public function key_in_array($key_str, $array, $sep=","){
		if($keys = explode($sep, $key_str)){
			foreach($keys as $val){
				if(array_key_exists($val, $array))
					return true;
			}
		}
		return false;
	}
	
	/**
	 * 无级分类遍历器
	 *
	 */
	public static function TypeList($list_type, $array_type, $pk = 'id', $parent_id = 0, $class_number = 1, $parent_field = "parent_id"){
		$id = $pk;
		foreach ($array_type as $obj_type){
			if($obj_type[$parent_field] == $parent_id){
				$list_type[] = array(
					'object' => $obj_type,
					'grade'  => $class_number
				);
				$list_type = self::TypeList($list_type, $array_type, $id, $obj_type[$id], $class_number + 1, $parent_field);
			}
		}		
		return $list_type;
	}
	
	public static function createdb($dbarray, $sqlfile){
		include_once('db.inc.php');
		$db = new db();
		$connid = $db->connect($dbarray['db_host'], $dbarray['db_user'], $dbarray['db_pw'], $dbarray['db_name'], $dbarray['db_pconnect'], $dbarray['db_charset']);
		if($connid){
		
		}
		
		if(!file_exists($filepath)){
			echo '数据库对应的sql文件没有找到';
			return false;
		}
		$result = mysql_query("SHOW tables");
		while ($currow = mysql_fetch_array($result))
		{
		   mysql_query("drop TABLE IF EXISTS ".$currow['Tables_in_'.$dbarray['dbname']]."");                     
		   echo "清空数据表【".$currow['Tables_in_'.$dbarray['dbname']]."】成功！<br>";             
		}
		$sql = file_get_contents($filepath);
		if(Common::sql_execute($sql, $dbarray['db_charset'])){
			return true;
		}else{
			return false;
		}
	}
	
	private function sql_execute($sql, $db_charset)
	{
	    $sqls = Common::sql_split($sql, $db_charset);
		if(is_array($sqls))
	    {
			foreach($sqls as $sql)
			{
				if(trim($sql) != '') 
				{
					mysql_query($sql);
				}
			}
		}
		else
		{
			mysql_query($sqls);
		}
		return true;
	}
	
	private function sql_split($sql, $db_charset)
	{
		if(mysql_get_server_info() > '4.1' && $db_charset)
		{
			$sql = preg_replace("/TYPE=(InnoDB|MyISAM)( DEFAULT CHARSET=[^; ]+)?/", "TYPE=\\1 DEFAULT CHARSET=".$db_charset,$sql);
		}
		$sql = str_replace("\r", "\n", $sql);
		$ret = array();
		$num = 0;
		$queriesarray = explode(";\n", trim($sql));
		unset($sql);
		foreach($queriesarray as $query)
		{
			$ret[$num] = '';
			$queries = explode("\n", trim($query));
			$queries = array_filter($queries);
			foreach($queries as $query)
			{
				$str1 = substr($query, 0, 1);
				if($str1 != '#' && $str1 != '-') $ret[$num] .= $query;
			}
			$num++;
		}
		return($ret);
	}
}
?>