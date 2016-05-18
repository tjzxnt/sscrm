<?php
class mtraders extends spController {
	
	public function __construct(){
		parent::__construct();
		try {
			$obj_cpt = spClass("department_competence");
			if(!$_SESSION["sscrm_user"]["user_identity"]["operate"]["enabled"])
				$obj_cpt->check_login_competence("MTRADER");
		}catch(Exception $e){
			$backurl = spUrl("main", "welcome");
			$this->redirect($backurl, $e->getMessage());
			exit();
		}
	}
	
	public function traderlist(){
		$obj_trader = spClass('trader');
		$postdate = $this->spArgs();
		$page = intval(max($postdate['page'], 1));
		$condition = "crm_trader.ishide = 0";
		if($postdate['searchkey'] != '')
			$condition .= " and crm_trader.tradername like '%{$postdate['searchkey']}%'";
		$this->trader_rs = $obj_trader
		->join("crm_user as create_user", "create_user.id = crm_trader.create_id")
		->join("crm_user as from_user", "from_user.id = crm_trader.from_id")
		->join("crm_user as contact_user", "contact_user.id = crm_trader.contact_id")
		->join("crm_user as maintenance_user", "maintenance_user.id = crm_trader.maintenance_id")
		->spPager($page, 20)->findAll($condition, 'crm_trader.createtime desc', "crm_trader.*, create_user.realname as create_name, from_user.realname as from_name, contact_user.realname as contact_name, maintenance_user.realname as maintenance_name");
		$this->pager = $obj_trader->spPager()->getPager();
		$this->searchkey = $postdate['searchkey'];
		$this->url = spUrl('mtraders', 'traderlist', array("searchkey"=>$this->searchkey));
	}
	
	public function createtrader(){
		$obj_trader = spClass('trader');
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			try {
				$postdate = $this->spArgs();
				$data = array();
				//客户端提交方法
				$data['create_id'] = $_SESSION["sscrm_user"]["id"];
				$data['from_id'] = intval($postdate['from_id']);
				$data['contact_id'] = intval($postdate['contact_id']) ? intval($postdate['contact_id']) : $data['from_id'];
				$data['maintenance_id'] = intval($postdate['maintenance_id']);
				$data['tradername'] = $postdate['tradername'];
				$data['rebate'] = intval($postdate["rebate"]);
				$data['remark'] = $postdate["remark"];
				$data['createtime'] = time();
				if($result = $obj_trader->spValidatorForOPT()->spValidator($data)){
					foreach($result as $item) {
						throw new Exception($item[0]);
						break;
					}
				}
				if($data['rebate'] <= 0)
					throw new Exception("请填写返点数");
				if(!$obj_trader->checkName($data['tradername']))
					throw new Exception("该分销商已经被注册");
				if(!$id = $obj_trader->create($data))
					throw new Exception("未知错误，添加失败");
				spClass('user_log')->save_log(7, "添加了分销商 ".$data['tradername']." [id:$id]");
				$url = spUrl("mtraders", "traderlist");
				$message = array('msg'=>'分销商添加成功','result'=>1, 'url'=>$url);
				echo json_encode($message);
				exit();
			}catch(Exception $e){
				$message = array('msg'=>$e->getMessage() ,'result'=>0);
				echo json_encode($message);
				exit();
			}
	    }
	    $user = spClass("user");
	    $this->tuser_rs = $user->getUser_prep();
	    $this->contact_rs = $user->getUser_prep("find_in_set('telclient', identity_attr)");
	    $this->validator = $obj_trader->getValidatorForCreateJS();
	    $this->saveurl = spUrl("mtraders", "createtrader");
	}

	public function account_modify(){
		try {
			$obj_trader = spClass('trader');
			$postdate = $this->spArgs();
			if(!$id = intval($postdate["id"]))
				throw new Exception("参数错误");
			if(!$trader_rs = $obj_trader->find(array("id"=>$id)))
				throw new Exception("找不到该分销商，可能已被删除");
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try{
					$data = array();
					$data["username"] = $postdate["username"];
					if($data["username"]){
						if($obj_trader->find(array("username"=>$data["username"], "id <>"=>$id)))
							throw new Exception("用户名已存在");
						$data["islogin"] = intval($postdate["islogin"]);
					}else
						$data["islogin"] = 0;
					if($postdate["password"]){
						if(strlen($postdate["password"]) < 6)
							throw new Exception("请输入6位以上的密码");
						if($postdate["password"] != $postdate["repassword"])
							throw new Exception("两次密码不正确，请重新输入");
						$data["password"] = md5($postdate["password"]);
					}
					if(!$trader_rs["password"] && $data["islogin"])
						throw new Exception("请设置密码才可登录");
					if(!$obj_trader->update(array("id"=>$id), $data))
						throw new Exception("未知错误，账号信息更新失败");
					$message = array('msg'=>"账号信息更新成功", 'url'=>spUrl("mtraders", "traderlist"), 'result'=>1);
					echo json_encode($message);
					exit();
				}catch(Exception $e){
					$message = array('msg'=>$e->getMessage(), 'result'=>-1);
					echo json_encode($message);
					exit();
				}
			}
			$this->id = $id;
			$this->trader_rs = $trader_rs;
		}catch(Exception $e){
			$this->redirect(spUrl("mtraders", "traderlist"), $e->getMessage());
			exit();
		}
	}

	public function checkName(){
		@header('Cache-Control: no-cache, must-revalidate, max-age=0');
		@header('Pragma: no-cache');
		$obj_trader = spClass('trader');
		echo $obj_trader->checkName($this->spArgs('tradername'), $this->spArgs('id')) ? 'true':'false';
		exit();
	}
}
?>