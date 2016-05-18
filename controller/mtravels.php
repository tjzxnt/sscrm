<?php
class mtravels extends spController {
	
	public function __construct(){
		parent::__construct();
		try {
			$obj_cpt = spClass("department_competence");
			if(!$_SESSION["sscrm_user"]["user_identity"]["operate"]["enabled"])
				$obj_cpt->check_login_competence("MTRAVEL");
		}catch(Exception $e){
			$backurl = spUrl("main", "welcome");
			$this->redirect($backurl, $e->getMessage());
			exit();
		}
	}
	
	public function travellist(){
		$obj_travel = spClass('travel');
		$postdate = $this->spArgs();
		$page = intval(max($postdate['page'], 1));
		$condition = "crm_travel.ishide = 0";
		if($postdate['searchkey'] != '')
			$condition .= " and crm_travel.travelname like '%{$postdate['searchkey']}%'";
		$this->travel_rs = $obj_travel
		->join("crm_user as create_user", "create_user.id = crm_travel.create_id")
		->spPager($page, 20)->findAll($condition, 'crm_travel.createtime desc', "crm_travel.*, create_user.realname as create_name");
		$this->pager = $obj_travel->spPager()->getPager();
		$this->searchkey = $postdate['searchkey'];
		$this->url = spUrl('mtravels', 'travellist', array("searchkey"=>$this->searchkey));
	}
	
	public function createtravel(){
		$obj_travel = spClass('travel');
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			try {
				$postdate = $this->spArgs();
				$data = array();
				//客户端提交方法
				$data['create_id'] = $_SESSION["sscrm_user"]["id"];
				$data['travelname'] = $postdate['travelname'];
				$data['rebate'] = intval($postdate["rebate"]);
				$data['remark'] = $postdate["remark"];
				$data['createtime'] = time();
				if($result = $obj_travel->spValidatorForOPT()->spValidator($data)){
					foreach($result as $item) {
						throw new Exception($item[0]);
						break;
					}
				}
				if($data['rebate'] <= 0)
					throw new Exception("请填写返点数");
				if(!$obj_travel->checkName($data['travelname']))
					throw new Exception("该旅行社已经被注册");
				if(!$id = $obj_travel->create($data))
					throw new Exception("未知错误，添加失败");
				spClass('user_log')->save_log(8, "添加了旅行社 ".$data['travelname']." [id:$id]");
				$url = spUrl("mtravels", "travellist");
				$message = array('msg'=>'旅行社添加成功','result'=>1, 'url'=>$url);
				echo json_encode($message);
				exit();
			}catch(Exception $e){
				$message = array('msg'=>$e->getMessage() ,'result'=>0);
				echo json_encode($message);
				exit();
			}
	    }
	    $this->validator = $obj_travel->getValidatorForCreateJS();
	    $this->saveurl = spUrl("mtravels", "createtravel");
	}

	public function account_modify(){
		try {
			$obj_travel = spClass('travel');
			$postdate = $this->spArgs();
			if(!$id = intval($postdate["id"]))
				throw new Exception("参数错误");
			if(!$travel_rs = $obj_travel->find(array("id"=>$id)))
				throw new Exception("找不到该旅行社，可能已被删除");
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try{
					$data = array();
					$data["username"] = $postdate["username"];
					if($data["username"]){
						if($obj_travel->find(array("username"=>$data["username"], "id <>"=>$id)))
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
					if(!$travel_rs["password"] && $data["islogin"])
						throw new Exception("请设置密码才可登录");
					if(!$obj_travel->update(array("id"=>$id), $data))
						throw new Exception("未知错误，账号信息更新失败");
					$message = array('msg'=>"账号信息更新成功", 'url'=>spUrl("mtravels", "travellist"), 'result'=>1);
					echo json_encode($message);
					exit();
				}catch(Exception $e){
					$message = array('msg'=>$e->getMessage(), 'result'=>-1);
					echo json_encode($message);
					exit();
				}
			}
			$this->id = $id;
			$this->travel_rs = $travel_rs;
		}catch(Exception $e){
			$this->redirect(spUrl("mtravels", "travellist"), $e->getMessage());
			exit();
		}
	}

	public function checkName(){
		@header('Cache-Control: no-cache, must-revalidate, max-age=0');
		@header('Pragma: no-cache');
		$obj_travel = spClass('travel');
		echo $obj_travel->checkName($this->spArgs('travelname'), $this->spArgs('id')) ? 'true':'false';
		exit();
	}
}
?>