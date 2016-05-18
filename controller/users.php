<?php
class users extends spController {
	
	public function __construct(){
		parent::__construct();
		try {
			$obj_cpt = spClass("department_competence");
			if(!$_SESSION["sscrm_user"]["user_identity"]["operate"]["enabled"])
				$obj_cpt->check_login_competence("USER");
		}catch(Exception $e){
			$backurl = spUrl("main", "welcome");
			$this->redirect($backurl, $e->getMessage());
			exit();
		}
	}
	
	public function userslist(){
		$user = spClass('user');
		$obj_department = spClass('department');
		$obj_identity = spClass("user_identity");
		$obj_sep = spClass('department_sep');
		$postdate = $this->spArgs();
		$page = intval(max($postdate['page'], 1));
		$condition = "crm_department.isdel = 0 and crm_user.isdel = 0";
		if($postdate['searchkey'] != '')
			$condition .= " and (crm_user.realname like '%{$postdate['searchkey']}%' or crm_user.username like '%{$postdate['searchkey']}%')";
		if($postdate['islogin'] != ''){
			$condition .= " and crm_user.islogin = " . $postdate['islogin'];
			$this->islogin = $postdate['islogin'];
		}
		if($postdate['depart_id'].'a' !== 'a'){
			$condition .= " and crm_user.depart_id = ".intval($postdate['depart_id']);
			$this->depart_id = $postdate['depart_id'];
		}
		if($user_rs = $user->join("crm_department")->spPager($page, 20)->findAll($condition, 'crm_department.isadmin desc, crm_user.isdirector, crm_user.createtime asc', "crm_user.*, crm_department.dname, crm_department.isadmin, crm_department.ismust_useridentity")){
			foreach($user_rs as $key => $val){
				if($val["identity_attr"]){
					if($identity_rs = $obj_identity->find("find_in_set(imark, '$val[identity_attr]')", null, "group_concat(iname) as inamestr"))
						$user_rs[$key]["identity"] = $identity_rs["inamestr"];
				}else{
					$user_rs[$key]["identity"] = "-";
				}
				if($val["identity_puserid"]){
					$identity_user_rs = $user->find(array("id"=>$val["identity_puserid"]), null, "realname");
					$user_rs[$key]["identity_user"] = $identity_user_rs["realname"];
				}else{
					$user_rs[$key]["identity_user"] = "-";
				}
				if($val["depart_sep_id"]){
					$sep_rs = $obj_sep->find(array("id"=>$val["depart_sep_id"]), null, "sep_name");
					$user_rs[$key]["sep_name"] = $sep_rs["sep_name"];
				}
					
			}
		}
		$this->user_rs = $user_rs;
		$this->pager = $user->spPager()->getPager();
		$this->searchkey = $postdate['searchkey'];
		$this->department_rs = $obj_department->getlist();
		$this->url = spUrl('users', 'userslist', array("searchkey"=>$this->searchkey, "depart_id"=>$this->depart_id, "islogin"=>$this->islogin));
	}
	
	public function delidentity(){
		try {
			if(!$id = intval($this->spArgs('id')))
				throw new Exception('请先选择删除项，再进行操作！');
			$url = $_SERVER['HTTP_REFERER'];
			$user = spClass('user');
			if(!$user_rs = $user->join("crm_department")->find(array("crm_user.id"=>$id, "crm_department.isadmin"=>0), null, "crm_user.realname, crm_user.isdel"))
				throw new Exception("参数错误，找不到该用户");
			if($user->find(array("identity_puserid"=>$id)))
				throw new Exception("该用户为权限对接人，无法进行该操作");
			if(!$user->update(array("id"=>$id), array("identity_attr"=>0, "identity_puserid"=>0)))
				throw new Exception("未知错误，权限删除失败");
			spClass('user_log')->save_log(4, "删除了用户 ".$user_rs["realname"]." [id:".$id."] 的权限");
			echo json_encode(array('msg'=>"权限删除成功", "url"=>$url, 'result'=>1));
			exit();
		}catch(Exception $e){
			echo json_encode(array('msg'=>$e->getMessage(), 'result'=>0));
			exit();
		}
	}
	
	public function recycle(){
		$user = spClass('user');
		$obj_department = spClass('department');
		$postdate = $this->spArgs();
		$page = intval(max($postdate['page'], 1));
		$condition = "crm_department.isdel = 0 and crm_user.isdel = 1";
		if($postdate['searchkey'] != '')
			$condition .= " and (crm_user.realname like '%{$postdate['searchkey']}%' or crm_user.username like '%{$postdate['searchkey']}%')";
		if($postdate['depart_id'].'a' !== 'a'){
			$condition .= " and crm_user.depart_id = ".intval($postdate['depart_id']);
			$this->depart_id = $postdate['depart_id'];
		}
		$this->user_rs = $user->join("crm_department")->spPager($page, 20)->findAll($condition, 'crm_department.isadmin desc, crm_user.createtime asc', "crm_user.*, crm_department.dname, crm_department.isadmin");
		$this->pager = $user->spPager()->getPager();
		$this->searchkey = $postdate['searchkey'];
		$this->department_rs = $obj_department->getlist();
		$this->url = spUrl('users', 'recycle', array("searchkey"=>$this->searchkey, "depart_id"=>$this->depart_id));
	}

	public function creatusers(){
		$user = spClass("user");
		$obj_department = spClass('department');
		$obj_identity = spClass("user_identity");
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			try {
				$postdate = $this->spArgs();
				$data = array();
				//客户端提交方法
				$data['username'] = $postdate['username'];
				$data['depart_id'] = intval($postdate['depart_id']);
				/*
				$data['password'] = $postdate['password'];
				$data['password1'] = $postdate['password1'];
				*/
				$data['password'] = $data['password1'] = "111111";
				$data['realname'] = $postdate['realname'];
				$data['sex'] = intval($postdate['sex']);
				$data['isdel'] = $postdate['isdel'];
				$data['create_id'] = $_SESSION["sscrm_user"]["id"];
				$data["isdirector"] = intval($postdate["isdirector"]);
				if($data["qualified"] = intval($postdate["qualified"])){
					$data["qualifiedtime"] = $postdate["qualifiedtime"];
					if($data["qualifiedtime"] == '0000-00-00')
						throw new Exception("请设置转正时间");
				}else{
					$data["qualifiedtime"] = "0000-00-00";
				}
				$data['createtime'] = time();
				$data['identity_attr'] = $postdate["identity"] ? implode(",", $postdate["identity"]) : "";
				$data["depart_sep_id"] = intval($postdate["depart_sep_id"]);
				if($result = $user->spValidatorForCreate()->spValidator($data)){
					foreach($result as $item) {
						throw new Exception($item[0]);
						break;
					}
				}
				if(!$user->checkUsername($data['username']))
					throw new Exception("用户名已经被注册");
				if(!$user->checkRealname($data['realname']))
					throw new Exception("真实姓名已经被注册");
				if(!$department_rs = $obj_department->getinfoById($data['depart_id']))
					throw new Exception("所选部门错误");
				if($department_rs["is_sep"]){
					if(!$data["depart_sep_id"])
						throw new Exception("请选择部门组");
				}else{
					$data["depart_sep_id"] = 0;
				}
				if($data["isdirector"]){
					if($user->getdirector($data['depart_id'], 0, $data["depart_sep_id"]))
						throw new Exception("该部门只能有一个总监");
				}
				$data['password'] = md5($data['password']);
				unset($data['password1']);
				if(!$id = $user->create($data))
					throw new Exception("未知错误，添加失败");
				spClass('user_log')->save_log(4, "创建了用户 ".$data["realname"]." [id:".$id."]");
				$url = spUrl("users", "userslist");
				$message = array('msg'=>'账号添加成功','result'=>1, 'url'=>$url);
				echo json_encode($message);
				exit();
			}catch(Exception $e){
				$message = array('msg'=>$e->getMessage() ,'result'=>0);
				echo json_encode($message);
				exit();
			}
	    }
	    $this->department_rs = $obj_department->getlist();
	    $this->validator = $user->getValidatorForCreateJS();
	    $this->saveurl = spUrl("users", "creatusers");
	}

	public function modifyusers(){
		$id = intval($this->spArgs('id'));
		$user = spClass("user");
		$obj_department = spClass('department');
		try{
			if(!$id)
				throw new Exception("非法操作");
			if(!$user_rs = $user->getCommonUserinfo($id))
				throw new Exception("未知错误，找不到该用户");
			if($user_rs["isdel"])
				throw new Exception("该用户已关闭，不允许该操作");
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try {
					$postdate = $this->spArgs();
					$data = array();
					//客户端提交方法
					$data['username'] = $postdate['username'];
					$data['depart_id'] = intval($postdate['depart_id']);
					/*
					$data['password'] = $postdate['password'];
					$data['password1'] = $postdate['password1'];
					*/
					$data['realname'] = $postdate['realname'];
					$data['sex'] = intval($postdate['sex']);
					$data['isdel'] = $postdate['isdel'];
					$data['identity_attr'] = $postdate["identity"] ? implode(",", $postdate["identity"]) : "";
					$data["isdirector"] = intval($postdate["isdirector"]);
					if($data["qualified"] = intval($postdate["qualified"])){
						$data["qualifiedtime"] = $postdate["qualifiedtime"];
						if($data["qualifiedtime"] == '0000-00-00')
							throw new Exception("请设置转正时间");
					}else{
						$data["qualifiedtime"] = "0000-00-00";
					}
					$data["depart_sep_id"] = intval($postdate["depart_sep_id"]);
					//print_r($data);exit();
					if($result = $user->getValidatorForModify()->spValidator($data)){
						foreach($result as $item) {
							throw new Exception($item[0]);
							break;
						}
					}
					if(!$user->checkUsername($data['username'], $id))
						throw new Exception("用户名已经被注册");
					if(!$user->checkRealname($data['realname'], $id))
						throw new Exception("真实姓名已经被注册");
					if(!$department_rs = $obj_department->getinfoById($data['depart_id']))
						throw new Exception("所选部门错误");
					if($department_rs["is_sep"]){
						if(!$data["depart_sep_id"])
							throw new Exception("请选择部门组");
					}else{
						$data["depart_sep_id"] = 0;
					}
					if($data["isdirector"]){
						if($user->getdirector($data['depart_id'], $id, $data["depart_sep_id"]))
							throw new Exception("该部门只能有一个总监");
					}
					if($data['password'])
						$data['password'] = md5($data['password']);
					else 
						unset($data['password']);
					unset($data['password1']);
					if(!$user->update(array("id"=>$id), $data))
						throw new Exception("未知错误，添加失败");
					spClass('user_log')->save_log(4, "更新了用户 ".$data["realname"]." [id:".$id."]");
					$url = spUrl("users", "userslist");
					$message = array('msg'=>'账号修改成功','result'=>1, 'url'=>$url);
					echo json_encode($message);
					exit();
				}catch(Exception $e){
					$message = array('msg' => $e->getMessage(), 'result'=>0);
					echo json_encode($message);
					exit();
				}
			}
			$this->department_rs = $obj_department->getlist();
			$this->validator = $user->getValidatorForModifyJS($id);
			$this->id = $id;
			$this->user_rs = $user_rs;
			$this->saveurl = spUrl("users", "modifyusers");
			$this->display("users/creatusers.html");
		}catch(Exception $e){
			$this->redirect(spUrl("users", "userslist"), $e->getMessage());
		}
	}
	
	public function chooseidentity(){
		$user = spClass("user");
		$obj_identity = spClass("user_identity");
		$id = intval($this->spArgs("id"));
		try{
			if(!$id)
				throw new Exception("非法操作");
			if(!$user_rs = $user->getCommonUserinfo($id))
				throw new Exception("未知错误，找不到该用户");
			if($user_rs["isdel"])
				throw new Exception("该用户已关闭，不允许该操作");
			if($user->find(array("identity_puserid"=>$id)))
				throw new Exception("该用户为权限对接人，无法进行该操作");
			if(!$identity_rs = $obj_identity->getlist($user_rs["depart_id"]))
				throw new Exception("该用户所在的部门没有可选的个人权限");
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try {
					$postdate = $this->spArgs();
					$data = array();
					$data["identity_attr"] = intval($postdate["identity_attr"]);
					if(!$data["identity_attr"])
						throw new Exception("请选择权限");
					if(!$identity_rs = $obj_identity->getinfo($data["identity_attr"], $user_rs["depart_id"]))
						throw new Exception("该用户所在的部门不允许使用该权限");
					$data["identity_attr"] = $identity_rs["imark"];
					if($identity_rs["ispuser"]){
						if(!$data["identity_puserid"] = intval($postdate["identity_puserid"]))
							throw new Exception("请选择权限上级专员");
						if($data["identity_puserid"] == $id)
							throw new Exception("不能选择自己为权限专员");
						if(!$user_rs = $user->getDepartUserinfo($identity_rs["pdepart_id"], $data["identity_puserid"]))
							throw new Exception("上级专员部门不允许");
						if($user_rs["isdel"])
							throw new Exception("该上级专员已关闭，不允许进行该操作");
					}else{
						$data["identity_puserid"] = 0;
					}
					if(!$user->update(array("id"=>$id), $data))
						throw new Exception("未知错误，更新失败");
					spClass('user_log')->save_log(4, "更新了用户 ".$user_rs["realname"]." [id:".$id."] 的权限");
					$url = spUrl("users", "userslist");
					$message = array('msg'=>'账号权限修改成功','result'=>1, 'url'=>$url);
					echo json_encode($message);
					exit();
				}catch(Exception $e){
					$message = array('msg' => $e->getMessage(), 'result'=>0);
					echo json_encode($message);
					exit();
				}
			}
			$this->id = $id;
			$this->identity_rs = $identity_rs;
			$this->check_indentity = spUrl("users", "checkidentity");
			$this->saveurl = spUrl("users", "chooseidentity");
		}catch(Exception $e){
			$this->redirect(spUrl("users", "userslist"), $e->getMessage());
		}
	}
	
	public function checkidentity(){
		$user = spClass("user");
		$obj_identity = spClass("user_identity");
		$id = intval($this->spArgs("id"));
		if(!$identity_attr = intval($this->spArgs("identity_attr"))){
			echo json_encode(array('result'=>1));
			exit();
		}
		try{
			if(!$id)
				throw new Exception("非法操作");
			if(!$user_rs = $user->getCommonUserinfo($id))
				throw new Exception("未知错误，找不到该用户");
			if($user_rs["isdel"])
				throw new Exception("该用户已关闭，不允许该操作");
			if(!$obj_identity->getlist($user_rs["depart_id"]))
				throw new Exception("该用户所在的部门没有可选的个人权限");
			if(!$identity_rs = $obj_identity->getinfo($identity_attr, $user_rs["depart_id"]))
				throw new Exception("该用户所在的部门不允许使用该权限");
			if($identity_rs["ispuser"] && $identity_rs["pdepart_id"])
				$userlist_rs = $user->getUserByDepart($identity_rs["pdepart_id"], "crm_user.id <> $id and crm_user.identity_attr = 'getclient'");
			echo json_encode(array('userlist_rs'=>$userlist_rs, 'result'=>2));
			exit();
		}catch(Exception $e){
			echo json_encode(array('msg'=>$e->getMessage(), 'result'=>0));
			exit();
		}
	}
	
	public function islogin(){
		$user = spClass("user");
		$id = intval($this->spArgs("id"));
		$setlogin = intval($this->spArgs("setlogin")) ? 1 : 0;
		try{
			if(!$id)
				throw new Exception("非法操作");
			if(!$user_rs = $user->getCommonUserinfo($id))
				throw new Exception("未知错误，找不到该用户");
			if($user_rs["isdel"])
				throw new Exception("该用户已关闭，不允许该操作");
			if(!$user->update(array("id"=>$id), array("islogin"=>$setlogin)))
				throw new Exception("未知错误，更新失败");
			spClass('user_log')->save_log(4, "更新了用户 ".$user_rs["realname"]." [id:".$id."] 的登录权限，设为" . ($setlogin ? "可登录系统" : "不可登录系统"));
			$url = $_SERVER['HTTP_REFERER'];
			echo json_encode(array('msg'=>"权限更新成功", "url"=>$url, 'result'=>1));
			exit();
		}catch(Exception $e){
			echo json_encode(array('msg'=>$e->getMessage(), 'result'=>0));
			exit();
		}
	}
	
	public function checkUsername(){
		@header('Cache-Control: no-cache, must-revalidate, max-age=0');
		@header('Pragma: no-cache');
		$user = spClass('user');
		echo $user->checkUsername($this->spArgs('username'), $this->spArgs('id')) ? 'true':'false';
		exit();
	}
	
	public function checkRealname(){
		@header('Cache-Control: no-cache, must-revalidate, max-age=0');
		@header('Pragma: no-cache');
		$user = spClass('user');
		echo $user->checkRealname($this->spArgs('realname'), $this->spArgs('id')) ? 'true':'false';
		exit();
	}
	
	public function modifystatus(){
		try {
			if(!$id = intval($this->spArgs('id')))
				throw new Exception('请先选择更新项，再进行操作！');
			$url = $_SERVER['HTTP_REFERER'];
			$user = spClass('user');
			if(!$user_rs = $user->join("crm_department")->find(array("crm_user.id"=>$id, "crm_department.isadmin"=>0), null, "crm_user.isdel"))
				throw new Exception("参数错误，找不到该用户");
			if($user->find(array("identity_puserid"=>$id)))
				throw new Exception("该用户为权限对接人，无法进行该操作");
			$isdel = $user_rs["isdel"] ? 0 : 1;
			$user_state = array("启用", "关闭");
			if(!$user->update(array("id"=>$id), array("isdel"=>$isdel)))
				throw new Exception("未知错误，状态更新失败");
			spClass('user_log')->save_log(4, "将用户 ".$user_rs["realname"]." [id:".$id."] 的状态设为 ".$user_state[$isdel]);
			echo json_encode(array('msg'=>"状态更新成功", "url"=>$url, 'result'=>1));
			exit();
		}catch(Exception $e){
			echo json_encode(array('msg'=>$e->getMessage(), 'result'=>0));
			exit();
		}
	}
	
	public function getidentityBydepart(){
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			try{
				$obj_identity = spClass("user_identity");
				$obj_department = spClass('department');
				$obj_sep = spClass('department_sep');
				if(!$departid = intval($this->spArgs("departid")))
					throw new Exception("no departid");
				if(!$depart_rs = $obj_department->findByPk($departid))
					throw new Exception("no department");
				$identity_rs = $obj_identity->findAll("isdel = 0 and find_in_set($departid, depart_id)", "sort asc");
				if($depart_rs["is_sep"])
					$sep_rs = $obj_sep->findAll(array("depart_id"=>$departid, "ishide"=>0), "sort asc");
				if(!$identity_rs && !$sep_rs)
					throw new Exception("no rs");
				echo json_encode(array("data_rs"=>$identity_rs, "sep_rs"=>$sep_rs, 'result'=>1));
				exit();
			}catch(Exception $e){
				echo json_encode(array('msg'=>$e->getMessage(), 'result'=>0));
				exit();
			}
			
		}
	}
}
?>