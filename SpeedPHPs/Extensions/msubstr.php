<?php

/**
 *
 * msubstr
 *
 * UTF-8的中文无错截字符程序
 *
 *
 */
class msubstr {
	/**
	 * 截取长度，按英文字符标准长度
	 */
	public function cut($str, $length, $suffix = "..."){
		$strcut = '';
		if(strlen($str) > $length) {
			//echo strlen($str);
			for($i = 0; $i < $length - 2; $i++) {
				$strcut .= ord($str[$i]) > 127 ? $str[$i].$str[++$i].$str[++$i] : $str[$i];
			}
			return $strcut.$suffix;
		} else {
			return $str;
		}
	}
	
	/**
	 * 截取长度，按汉字长度计算
	 */
	public function mcut($str, $length, $suffix = "..."){
		$strcut = '';
		$length = $length*3;
		if(strlen($str) > $length) {
			for($i = 0; $i < $length - 2; $i++) {
				if( ord($str[$i]) > 127 ){
					$strcut .= $str[$i].$str[++$i].$str[++$i];
				}else{
					$strcut .= $str[$i];
				}
			}
			return $strcut.$suffix;
		} else {
			return $str;
		}
	}
}
