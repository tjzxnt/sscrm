<?php
class mchannels extends spController {
	
	public function __construct(){
		parent::__construct();
		try {
			$obj_cpt = spClass("department_competence");
			if(!$_SESSION["sscrm_user"]["user_identity"]["operate"]["enabled"])
				$obj_cpt->check_login_competence("MCHANNEL");
		}catch(Exception $e){
			$backurl = spUrl("main", "welcome");
			$this->redirect($backurl, $e->getMessage());
			exit();
		}
	}
	
	public function channellist(){
		$obj_channel = spClass('channel');
		$postdate = $this->spArgs();
		$page = intval(max($postdate['page'], 1));
		$condition = "1";
		if($postdate["ishide"]."a" != "a"){
			$condition .= " and crm_channel.ishide = " . intval($postdate["ishide"]);
			$this->ishide = intval($postdate["ishide"]);
		}
		if($postdate['searchkey'] != '')
			$condition .= " and crm_channel.mechanism like '%{$postdate['searchkey']}%'";
		$sort = 'crm_channel.ishide asc, crm_channel.createtime desc';
		if($postdate['sort'].'a' !== 'a'){
			switch ($postdate['sort']){
				case "overdate_desc":
					$sort = "overdate desc, ".$sort;
					$this->sort = $postdate['sort'];
					break;
				default:
		
				break;
			}
		}
		$this->channel_rs = $obj_channel
		->join("crm_user as create_user", "create_user.id = crm_channel.create_id")
		->join("crm_user as contact_user", "contact_user.id = crm_channel.contact_id")
		->join("crm_user as from_user", "from_user.id = crm_channel.from_id")
		->join("crm_user as maintenance_user", "maintenance_user.id = crm_channel.maintenance_id")
		->spPager($page, 20)->findAll($condition, $sort, "crm_channel.*, create_user.realname as create_name, from_user.realname as from_name, contact_user.realname as contact_name, maintenance_user.realname as maintenance_name, IF(crm_channel.saletime > crm_channel.createtime, datediff(curdate(), FROM_UNIXTIME(crm_channel.saletime, '%Y-%m-%d')), datediff(curdate(), FROM_UNIXTIME(crm_channel.createtime, '%Y-%m-%d'))) as overdate");
		$this->pager = $obj_channel->spPager()->getPager();
		$this->searchkey = $postdate['searchkey'];
		$this->url = spUrl('mchannels', 'channellist', array("searchkey"=>$this->searchkey, "sort"=>$this->sort, "ishide"=>$this->ishide));
	}
	
	public function createchannel(){
		$obj_channel = spClass('channel');
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			try {
				$postdate = $this->spArgs();
				$data = array();
				//客户端提交方法
				$data['create_id'] = $_SESSION["sscrm_user"]["id"];
				$data['from_id'] = intval($postdate['from_id']);
				$data['contact_id'] = intval($postdate['contact_id']) ? intval($postdate['contact_id']) : $data['from_id'];
				$data['maintenance_id'] = intval($postdate['maintenance_id']);
				//$data['to_sale_id'] = intval($postdate['to_sale_id']);
				$data['to_sale_id'] = 0;
				$data['mechanism'] = $postdate['mechanism'];
				$data['rebate'] = intval($postdate["rebate"]);
				$data['remark'] = $postdate["remark"];
				$data['createtime'] = time();
				if($result = $obj_channel->spValidatorForOPT()->spValidator($data)){
					foreach($result as $item) {
						throw new Exception($item[0]);
						break;
					}
				}
				if($data['rebate'] <= 0)
					throw new Exception("请填写返点数");
				if(!$obj_channel->checkName($data['mechanism']))
					throw new Exception("该机构已经被注册");
				if($data['to_sale_id'] && !$obj_channel->is_tosale_allow($data['to_sale_id']))
					throw new Exception("该用户不允许设置为指定销售");
				if(!$id = $obj_channel->create($data))
					throw new Exception("未知错误，添加失败");
				spClass('user_log')->save_log(2, "添加了渠道 ".$data['mechanism']." [id:$id]");
				$url = spUrl("mchannels", "channellist");
				$message = array('msg'=>'渠道机构添加成功','result'=>1, 'url'=>$url);
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
	    $this->validator = $obj_channel->getValidatorForCreateJS();
	    $this->saveurl = spUrl("mchannels", "createchannel");
	}
	
	public function account_modify(){
		try {
			$obj_channel = spClass('channel');
			$postdate = $this->spArgs();
			if(!$id = intval($postdate["id"]))
				throw new Exception("参数错误");
			if(!$channel_rs = $obj_channel->find(array("id"=>$id)))
				throw new Exception("找不到该渠道，可能已被删除");
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try{
					$data = array();
					$data["username"] = $postdate["username"];
					if($data["username"]){
						if($obj_channel->find(array("username"=>$data["username"], "id <>"=>$id)))
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
					if(!$channel_rs["password"] && !$postdate["password"] && $data["islogin"])
						throw new Exception("请设置密码才可登录");
					if(!$obj_channel->update(array("id"=>$id), $data))
						throw new Exception("未知错误，账号信息更新失败");
					$message = array('msg'=>"账号信息更新成功", 'url'=>spUrl("mchannels", "channellist"), 'result'=>1);
					echo json_encode($message);
					exit();
				}catch(Exception $e){
					$message = array('msg'=>$e->getMessage(), 'result'=>-1);
					echo json_encode($message);
					exit();
				}
			}
			$this->id = $id;
			$this->channel_rs = $channel_rs;
		}catch(Exception $e){
			$this->redirect(spUrl("mchannels", "channellist"), $e->getMessage());
			exit();
		}
	}

	public function checkName(){
		@header('Cache-Control: no-cache, must-revalidate, max-age=0');
		@header('Pragma: no-cache');
		$obj_channel = spClass('channel');
		echo $obj_channel->checkName($this->spArgs('mechanism'), $this->spArgs('id')) ? 'true':'false';
		exit();
	}
}
?>