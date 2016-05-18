<?php
class basic extends spController {
	
	public function mypwd(){
		$user = spClass("user");
		try{
			if(!$user_rs = $user->getAvailUserinfo($_SESSION["sscrm_user"]["id"]))
				throw new Exception("未知错误，找不到该账号");
		}catch (Exception $e){
			$this->redirect($_SERVER['HTTP_REFERER'], $e->getMessage());
		}
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			try {
				$postdate = $this->spArgs();
				$data = array();
				//客户端提交方法
				$data['oralpassword'] = $postdate['oralpassword'];
				$data['password'] = $postdate['password'];
				$data['password1'] = $postdate['password1'];
				if($result = $user->spValidatorForMyPwd()->spValidator($data)){
					foreach($result as $item) {
						throw new Exception($item[0]);
						break;
					}
				}
				if($user_rs["password"] != md5($data['oralpassword']))
					throw new Exception("原密码不正确，请重新输入");
				$data['password'] = md5($data['password']);
				unset($data['oralpassword']);
				unset($data['password1']);
				if(!$user->update(array("id"=>$_SESSION["sscrm_user"]["id"]), $data))
					throw new Exception("未知错误，更新失败");
				spClass('user_log')->save_log(1, "更新了密码");
				$url = spUrl("basic", "mypwd");
				$message = array('msg'=>'密码更新成功','result'=>1, 'url'=>$url);
				echo json_encode($message);
				exit();
			}catch(Exception $e){
				$message = array('msg'=>$e->getMessage() ,'result'=>0);
				echo json_encode($message);
				exit();
			}
		}
		$this->user_rs = $user->getAvailUserinfo($_SESSION["sscrm_user"]["id"]);
		$this->saveurl = spUrl("basic", "mypwd");
		$this->validator = $user->spValidatorForMyPwdJS();
	}
	
	public function ipsafesetting(){
		$user = spClass("user");
		try{
			if(!$user_rs = $user->getAvailUserinfo($_SESSION["sscrm_user"]["id"]))
				throw new Exception("未知错误，找不到该账号");
		}catch (Exception $e){
			$this->redirect($_SERVER['HTTP_REFERER'], $e->getMessage());
		}
		import("Common.php");
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			try {
				$postdate = $this->spArgs();
				$data = array();
				//客户端提交方法
				$data['issafeip'] = intval($postdate['issafeip']);
				$data['safeip'] = $postdate['safeip'];
				if($data['issafeip'] && !$data['safeip'])
					throw new Exception("您已启用IP安全设置，请输入IP地址");
				if($data['safeip'] && !Common::isIP($data['safeip']))
					throw new Exception("请输入正确的IP地址");
				if($data['safeip'])
					$data['safeip'] = bindec(decbin(ip2long($data['safeip'])));
				if(!$user->update(array("id"=>$_SESSION["sscrm_user"]["id"]), $data))
					throw new Exception("未知错误，IP安全设置失败");
				spClass('user_log')->save_log(1, "更新了IP安全设置");
				$url = spUrl("basic", "ipsafesetting");
				$message = array('msg'=>'安全设置更新成功','result'=>1, 'url'=>$url);
				echo json_encode($message);
				exit();
			}catch(Exception $e){
				$message = array('msg'=>$e->getMessage() ,'result'=>0);
				echo json_encode($message);
				exit();
			}
		}
		$this->saveurl = spUrl("basic", "ipsafesetting");
		$this->ip = Common::GetIP();
		$this->user_rs = $user->getAvailUserinfo($_SESSION["sscrm_user"]["id"]);
	}
}
?>