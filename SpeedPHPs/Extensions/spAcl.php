<?php
/////////////////////////////////////////////////////////////////
// SpeedPHP中文PHP框架, Copyright (C) 2008 - 2010 SpeedPHP.com //
/////////////////////////////////////////////////////////////////

/**
 * 基于组的用户权限判断机制
 * 要使用该权限控制程序，需要在应用程序配置中做以下配置：
 * 有限控制的情况，在配置中使用	'launch' => array( 'router_prefilter' => array( array('spAcl','mincheck'), ), )
 * 强制控制的情况，在配置中使用	'launch' => array( 'router_prefilter' => array( array('spAcl','maxcheck'), ), )
 */
class spAcl{
	/**
	 * 默认提示无权限提示，可以是函数名或是数组（array(类名,方法)的形式）
	 */
	public $prompt = array('spAcl','def_prompt');

	/**
	 * 构造函数，设置权限检查程序与提示程序
	 */
	public function __construct(){	
		$params = spExt("spAcl");
		if( !empty($params["prompt"]) )$this->prompt = $params["prompt"];
	}

	/**
	 * 强制控制的检查程序，适用于后台。无权限控制的页面均不能进入
	 */
	public function maxcheck(){
		$acl_handle = $this->check();
		if( 1 !== $acl_handle){
			$this->prompt($acl_handle);
			return FALSE;
		}
		return TRUE;
	}
	

	/**
	 * 有限的权限控制，适用于前台。仅在权限表声明禁止的页面起作用，其他无声明页面均可进入
	 */
	public function mincheck(){
		$acl_handle = $this->check();
		if( 1 === $acl_handle){
			$this->prompt($acl_handle);
			return FALSE;
		}
		return TRUE;
	}
	
	/**
	 * 检查权限
	 */
	private function check(){
		GLOBAL $__controller, $__action;
		$acl_handle = $__controller . $GLOBALS['G_SP']['acl_handle_operators'] . $__action;
		$sessionName = $GLOBALS['G_SP']['session_name'];
		$sessionPrivateName = $GLOBALS['G_SP']['session_privacy_name'];
		$checkNoneArray = $GLOBALS['G_SP']['none_check_privacy'];
		if(!in_array($acl_handle, $checkNoneArray)){
			/*
			if($_SESSION[$sessionName]['admin_name']){
				return 1;
			}else{
			*/
				if(isset($_SESSION[$sessionName])){
					if($_SESSION[$sessionName][$sessionPrivateName][$acl_handle] != 'off'){
						return 1;
					}
					return 0;
				}
				return -1;
			/*
			}
			*/
		}
		return 1;
		
	}
	
	/**
	 * 无权限提示跳转
	 */
	public function prompt($type){
		$prompt = $this->prompt;
		if( is_array($prompt) ){
			return spClass($prompt[0])->{$prompt[1]}($type);
		}else{
			return call_user_func_array($prompt, array(), $type);
		}
	}
	
	/**
	 * 默认的无权限提示跳转
	 */
	public function def_prompt($type){
		$str = "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script>function sptips(){";
		if($type === 0){
			$url = spUrl($GLOBALS['G_SP']['home_url'][0], $GLOBALS['G_SP']['home_url'][1]); // 跳转到首页
			$str .= "alert(\"您并没有权限访问！\");";
		}else if($type === -1){
			$url = spUrl($GLOBALS['G_SP']['login_url'][0], $GLOBALS['G_SP']['login_url'][1]); // 跳转到登陆页
		}
		$str .= "window.top.location.href=\"{$url}\";}</script></head><body onload=\"sptips()\"></body></html>";
		echo $str;
		exit;
	}
}