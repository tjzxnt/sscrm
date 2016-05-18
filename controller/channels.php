<?php
class channels extends spController {
	
	public function __construct(){
		parent::__construct();
		try {
			$action = strtolower($this->spArgs("a"));
			if(!in_array($action, array("channelsignlist"))){
				$obj_cpt = spClass("department_competence");
				if(!$_SESSION["sscrm_user"]["user_identity"]["operate"]["enabled"])
					$obj_cpt->check_login_competence("CHANNEL");
			}
		}catch(Exception $e){
			$backurl = spUrl("main", "welcome");
			$this->redirect($backurl, $e->getMessage());
			exit();
		}
	}
	
	public function channelsignlist(){
		$obj_channel = spClass('channel');
		$obj_level = spClass('channel_level');
		$obj_type = spClass("channel_type");
		$postdate = $this->spArgs();
		$page = intval(max($postdate['page'], 1));
		$nowtime = date("Y-m-d");
		$condition = "crm_channel.ishide = 0 and crm_channel.from_id = ".$_SESSION["sscrm_user"]["id"];
		if($postdate['searchkey'] != ''){
			$condition .= " and (crm_channel.mechanism like '%{$postdate['searchkey']}%' or crm_channel.main_contact like '%{$postdate['searchkey']}%' or crm_channel.main_tel like '%{$postdate['searchkey']}%')";
			$this->searchkey = $postdate['searchkey'];
		}
		if($postdate['issign']."a" != 'a'){
			$condition .= " and crm_channel.issign = " . intval($postdate['issign']);
			$this->issign = $postdate['issign'];
		}
		if($postdate['typeid']."a" != 'a'){
			$condition .= " and crm_channel.typeid = " . intval($postdate['typeid']);
			$this->typeid = $postdate['typeid'];
		}
		if($level_id = intval($postdate['level_id'])){
			$condition .= " and crm_channel.level_id = {$level_id}";
			$this->level_id = $level_id;
		}
		$sort = 'crm_channel.createtime desc';
		if($postdate['sort'].'a' !== 'a'){
			switch ($postdate['sort']){
				case "overdate_desc":
					$sort = "overdate desc, ".$sort;
					$this->sort = $postdate['sort'];
				break;
				case "level_asc":
					$sort = "crm_channel_level.sort asc, ".$sort;
					$this->sort = $postdate['sort'];
				break;
				case "level_desc":
					$sort = "crm_channel_level.sort desc, ".$sort;
					$this->sort = $postdate['sort'];
				break;
				default:
						
				break;
			}
		}
		if($channel_rs = $obj_channel->join("crm_channel_level")->join("crm_user as cuser", "cuser.id = crm_channel.create_id")->join("crm_user as fuser", "fuser.id = crm_channel.from_id")->join("crm_user as muser", "muser.id = crm_channel.maintenance_id")->spPager($page, 20)->findAll($condition, $sort, "crm_channel.*, crm_channel_level.name as level_name, cuser.realname as c_realname, fuser.realname as f_realname, muser.realname as m_realname, IF(crm_channel.issign > 0, 0, datediff(curdate(), IF(crm_channel.sign_enddate > '0000-00-00', crm_channel.sign_enddate, FROM_UNIXTIME(crm_channel.createtime, '%Y-%m-%d')))) as overdate")){
			foreach($channel_rs as $key => $val){
				if($val["typeid"])
					$channel_rs[$key]["type_rs"] = $obj_type->getinfo($val["typeid"]);
				if($val["type2id"])
					$channel_rs[$key]["type2name"] = $obj_type->getname($val["type2id"]);
			}
		}
		$this->level_rs = $obj_level->getlist();
		$this->channel_rs = $channel_rs;
		$this->pager = $obj_channel->spPager()->getPager();
		$this->type_rs = $obj_type->getlist();
		$this->url = spUrl('channels', 'channelsignlist', array("searchkey"=>$this->searchkey, "level_id"=>$this->level_id, "issign"=>$this->issign, "typeid"=>$this->typeid, "sort"=>$this->sort,"inuse"=>$this->inuse));
	}
	
	public function channellist(){
		$obj_channel = spClass('channel');
		$obj_level = spClass('channel_level');
		$obj_active = spClass('channel_active');
		$obj_type = spClass("channel_type");
		$obj_record = spClass('channel_record');
		$postdate = $this->spArgs();
		$page = intval(max($postdate['page'], 1));
		$nowtime = date("Y-m-d");
		$condition = "crm_channel.ishide = 0 and crm_channel.maintenance_id = ".$_SESSION["sscrm_user"]["id"];
		if($postdate['searchkey'] != ''){
			$condition .= " and (crm_channel.mechanism like '%{$postdate['searchkey']}%' or crm_channel.main_contact like '%{$postdate['searchkey']}%' or crm_channel.main_tel like '%{$postdate['searchkey']}%')";
			$this->searchkey = $postdate['searchkey'];
		}
		if($postdate['issign']."a" != 'a'){
			$condition .= " and crm_channel.issign = " . intval($postdate['issign']);
			$this->issign = $postdate['issign'];
		}
		if($postdate['typeid']."a" != 'a'){
			$condition .= " and crm_channel.typeid = " . intval($postdate['typeid']);
			$this->typeid = $postdate['typeid'];
		}
		if($level_id = intval($postdate['level_id'])){
			$condition .= " and crm_channel.level_id = {$level_id}";
			$this->level_id = $level_id;
		}
		$sort = 'crm_channel.createtime desc';
		if($postdate['sort'].'a' !== 'a'){
			switch ($postdate['sort']){
				case "overdate_desc":
					$sort = "overdate desc, ".$sort;
					$this->sort = $postdate['sort'];
				break;
				case "level_asc":
					$sort = "crm_channel_level.sort asc, ".$sort;
					$this->sort = $postdate['sort'];
				break;
				case "level_desc":
					$sort = "crm_channel_level.sort desc, ".$sort;
					$this->sort = $postdate['sort'];
				break;
				case "plan_desc":
					$sort = "count(crm_client_plan.id) desc, ".$sort;
					$this->sort = $postdate['sort'];
				break;
				default:
					
				break;
			}
		}
		if($channel_rs = $obj_channel->join("crm_channel_level")->join("crm_client_plan", "crm_channel.id = crm_client_plan.channel_id and crm_client_plan.typeid = 3 and find_in_set({$_SESSION["sscrm_user"]["id"]}, crm_client_plan.main_id) and crm_client_plan.isfinish = 0 and crm_client_plan.starttime <= UNIX_TIMESTAMP()", "left")->join("crm_channel_type")->join("crm_user as cuser", "cuser.id = crm_channel.create_id")->join("crm_user as fuser", "fuser.id = crm_channel.from_id")->join("crm_user as muser", "muser.id = crm_channel.maintenance_id")->spPager($page, 20)->findAll($condition, $sort, "crm_channel.*, crm_channel_level.name as level_name, count(crm_client_plan.id) as plan_count, cuser.realname as c_realname, fuser.realname as f_realname, muser.realname as m_realname, crm_channel_type.isactive, crm_channel_type.name as typename, IF(crm_channel.issign > 0, 0, datediff(curdate(), IF(crm_channel.sign_enddate > '0000-00-00', crm_channel.sign_enddate, FROM_UNIXTIME(crm_channel.createtime, '%Y-%m-%d')))) as overdate", "crm_channel.id")){
		//if($channel_rs = $obj_channel->join("crm_channel_level")->join("crm_channel_type")->join("crm_user as cuser", "cuser.id = crm_channel.create_id")->join("crm_user as fuser", "fuser.id = crm_channel.from_id")->join("crm_user as muser", "muser.id = crm_channel.maintenance_id")->spPager($page, 20)->findAll($condition, $sort, "crm_channel.*, crm_channel_level.name as level_name, cuser.realname as c_realname, fuser.realname as f_realname, muser.realname as m_realname, crm_channel_type.isactive, crm_channel_type.name as typename, IF(crm_channel.issign > 0, 0, datediff(curdate(), IF(crm_channel.sign_enddate > '0000-00-00', crm_channel.sign_enddate, FROM_UNIXTIME(crm_channel.createtime, '%Y-%m-%d')))) as overdate")){
			foreach($channel_rs as $key => $val){
				if($val["isactive"])
					$channel_rs[$key]["active_count"] = $obj_active->getCountById($val["id"]);
				if($val["type2id"])
					$channel_rs[$key]["type2name"] = $obj_type->getname($val["type2id"]);
				$channel_rs[$key]["record_count"] = $obj_record->getCountById($val["id"]);
			}
		}
		$this->level_rs = $obj_level->getlist();
		$this->channel_rs = $channel_rs;
		$this->pager = $obj_channel->spPager()->getPager();
		$this->type_rs = $obj_type->getlist();
		$this->url = spUrl('channels', 'channellist', array("searchkey"=>$this->searchkey, "level_id"=>$this->level_id, "issign"=>$this->issign, "typeid"=>$this->typeid, "sort"=>$this->sort,"inuse"=>$this->inuse));
	}

	public function createchannel(){
		$obj_channel = spClass('channel');
		$obj_type = spClass("channel_type");
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
				$data["typeid"] = intval($postdate["typeid"]);
				$data["type2id"] = intval($postdate["type2id"]);
				$data["main_contact"] = $postdate["main_contact"];
				$data["main_tel"] = $postdate["main_tel"];
				$data['to_sale_id'] = 0;
				$data['mechanism'] = $postdate['mechanism'];
				$data['remark'] = $postdate["remark"];
				$data['createtime'] = time();
				if($result = $obj_channel->spValidatorForOPT()->spValidator($data)){
					foreach($result as $item) {
						throw new Exception($item[0]);
						break;
					}
				}
				if(!$obj_channel->checkName($data['mechanism']))
					throw new Exception("该机构已经被注册");
				if(!$obj_channel->checkTel($data['main_tel']))
					throw new Exception("该主联系人电话已经被注册");
				if($data['to_sale_id'] && !$obj_channel->is_tosale_allow($data['to_sale_id']))
					throw new Exception("该用户不允许设置为指定销售");
				if(!$id = $obj_channel->create($data))
					throw new Exception("未知错误，添加失败");
				spClass('user_log')->save_log(2, "添加了渠道 ".$data['mechanism']." [id:$id]", array("channel_id"=>$id));
				$url = spUrl("channels", "channellist");
				$message = array('msg'=>'渠道机构添加成功','result'=>1, 'url'=>$url);
				echo json_encode($message);
				exit();
			}catch(Exception $e){
				$message = array('msg'=>$e->getMessage() ,'result'=>0);
				echo json_encode($message);
				exit();
			}
		}
		$this->maintenance_self = 1;
		$user = spClass("user");
		$this->tuser_rs = $user->getUser_prep();
		$this->muser_rs = $user->getUser_prep("crm_user.depart_id = 2");
		$this->contact_rs = $user->getUser_prep("find_in_set('telclient', identity_attr)");
		$this->type_rs = $obj_type->getlist();
		$this->validator = $obj_channel->getValidatorForCreateJS();
		$this->saveurl = spUrl("channels", "createchannel");
	}
	
	public function channelmodify(){
		try {
			$obj_channel = spClass('channel');
			$obj_type = spClass("channel_type");
			if(!$channelid = intval($this->spArgs("channelid")))
				throw new Exception("参数丢失");
			if(!$channel_rs = $obj_channel->find(array("id"=>$channelid, "ishide"=>0)))
				throw new Exception("找不到该渠道，可能已经删除");
			if($channel_rs["issign"])
				throw new Exception("该渠道已签约，无法修改");
		}catch(Exception $e){
			$this->redirect(spUrl("channels", "channellist"), $e->getMessage());
		}
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			try {
				$postdate = $this->spArgs();
				$data = array();
				//客户端提交方法
				$data['from_id'] = intval($postdate['from_id']);
				$data['contact_id'] = intval($postdate['contact_id']) ? intval($postdate['contact_id']) : $data['from_id'];
				$data['maintenance_id'] = intval($postdate['maintenance_id']);
				//$data['to_sale_id'] = intval($postdate['to_sale_id']);
				$data["typeid"] = intval($postdate["typeid"]);
				$data["type2id"] = intval($postdate["type2id"]);
				$data["main_contact"] = $postdate["main_contact"];
				$data["main_tel"] = $postdate["main_tel"];
				$data['to_sale_id'] = 0;
				$data['mechanism'] = $postdate['mechanism'];
				$data['remark'] = $postdate["remark"];
				$data['createtime'] = time();
				if($result = $obj_channel->spValidatorForOPT()->spValidator($data)){
					foreach($result as $item) {
						throw new Exception($item[0]);
						break;
					}
				}
				if(!$obj_channel->checkName($data['mechanism'], $channelid))
					throw new Exception("该机构已经被注册");
				if(!$obj_channel->checkTel($data['main_tel'], $channelid))
					throw new Exception("该主联系人电话已经被注册");
				if($data['to_sale_id'] && !$obj_channel->is_tosale_allow($data['to_sale_id']))
					throw new Exception("该用户不允许设置为指定销售");
				if(!$obj_channel->update(array("id"=>$channelid), $data))
					throw new Exception("未知错误，更新失败");
				spClass('user_log')->save_log(2, "更新了渠道 ".$data['mechanism']." [id:$channelid]", array("channel_id"=>$channelid));
				$url = spUrl("channels", "channellist");
				$message = array('msg'=>'渠道机构更新成功','result'=>1, 'url'=>$url);
				echo json_encode($message);
				exit();
			}catch(Exception $e){
				$message = array('msg'=>$e->getMessage() ,'result'=>0);
				echo json_encode($message);
				exit();
			}
		}
		$this->maintenance_self = 1;
		$user = spClass("user");
		$this->tuser_rs = $user->getUser_prep();
		$this->muser_rs = $user->getUser_prep("crm_user.depart_id = 2");
		$this->contact_rs = $user->getUser_prep("find_in_set('telclient', identity_attr)");
		$this->type_rs = $obj_type->getlist();
		$this->channel_rs = $channel_rs;
		$this->validator = $obj_channel->getValidatorForModifyJS($channelid);
		$this->saveurl = spUrl("channels", "channelmodify");
		$this->display("channels/createchannel.html");
	}
	
	public function modify_level(){
		try {
			$obj_channel = spClass('channel');
			$obj_level = spClass('channel_level');
			if(!$channelid = intval($this->spArgs("channelid")))
				throw new Exception("参数丢失");
			if(!$this->channel_rs = $obj_channel->find(array("id"=>$channelid, "ishide"=>0)))
				throw new Exception("找不到该渠道，可能已经删除");
			$level_name = $obj_level->getName($this->channel_rs["level_id"]);
			$backurl = $_SERVER['HTTP_REFERER'];
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try {
					$postdata = $this->spArgs();
					$data = array();
					$data["level_id"] = intval($postdata["level_id"]);
					if(!$new_level_name = $obj_level->getName($data["level_id"]))
						throw new Exception("找不到该分级，可能已经丢失");
					if(!$obj_channel->update(array("id"=>$channelid), $data))
						throw new Exception("未知错误，更新失败");
					spClass('user_log')->save_log(2, "更新了渠道 ".$this->channel_rs['mechanism']." [id:$channelid] 的级别，由 {$level_name} 改为了 {$new_level_name}", array("channel_id"=>$channelid));
					$backurl = $postdata["backurl"] ? $postdata["backurl"] : spUrl("channels", "channellist");
					$message = array('msg'=>"渠道级别修改成功", 'result'=>1, "url"=>$backurl);
					echo json_encode($message);
					exit();
				}catch(Exception $e){
					$message = array('msg'=>$e->getMessage(), 'result'=>0);
					echo json_encode($message);
					exit();
				}
			}
			$this->backurl = $backurl;
			$this->channelid = $channelid;
			$this->level_rs = $obj_level->getlist();
		}catch(Exception $e){
			$this->redirect(spUrl("channels", "channellist"), $e->getMessage());
		}
	}
	
	public function checkName(){
		@header('Cache-Control: no-cache, must-revalidate, max-age=0');
		@header('Pragma: no-cache');
		$obj_channel = spClass('channel');
		echo $obj_channel->checkName($this->spArgs('mechanism'), $this->spArgs('id')) ? 'true':'false';
		exit();
	}
	
	public function checkTel(){
		@header('Cache-Control: no-cache, must-revalidate, max-age=0');
		@header('Pragma: no-cache');
		$obj_channel = spClass('channel');
		echo $obj_channel->checkTel($this->spArgs('main_tel'), $this->spArgs('id')) ? 'true':'false';
		exit();
	}
	
	public function actlist(){
		try{
			$obj_channel = spClass("channel");
			$channelid = intval($this->spArgs("channelid"));
			$channel_rs = $obj_channel->get_act_mychannel($channelid);
			if(!$channel_rs["type_rs"]["isactive"])
				throw new Exception("该类型的渠道不支持渠道活动");
			$obj_active = spClass('channel_active');
			$postdate = $this->spArgs();
			$page = intval(max($postdate['page'], 1));
			$condition = "crm_channel_active.channel_id = ".$channelid;
			if($postdate['searchkey'] != '')
				$condition .= " and (crm_channel_active.actname like '%{$postdate['searchkey']}%')";
			$this->act_rs = $obj_active->join("crm_user")->spPager($page, 20)->findAll($condition, 'crm_channel_active.createtime desc', "crm_channel_active.*, crm_user.realname as realname_create");
			$this->pager = $obj_active->spPager()->getPager();
			$this->searchkey = $postdate['searchkey'];
			$this->channelid = $channelid;
			$this->channel_rs = $channel_rs;
			$this->url = spUrl('channels', 'actlist', array("channelid"=>$channelid, "searchkey"=>$this->searchkey));
		}catch(Exception $e){
			$this->redirect(spUrl("channels", "channellist"), $e->getMessage());
		}
	}
	
	public function createactive(){
		try {
			$obj_channel = spClass("channel");
			$channelid = intval($this->spArgs("channelid"));
			$channel_rs = $obj_channel->get_act_mychannel($channelid);
			if(!$channel_rs["type_rs"]["isactive"])
				throw new Exception("该类型的渠道不支持渠道活动");
			$obj_active = spClass('channel_active');
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try {
					$postdate = $this->spArgs();
					$data = array();
					//客户端提交方法
					$data['create_id'] = $_SESSION["sscrm_user"]["id"];
					$data['channel_id'] = $channelid;
					$data['actname'] = $postdate['actname'];
					$data['acttime'] = date("Y-m-d", strtotime($postdate['acttime']));
					$data['createtime'] = time();
					if($result = $obj_active->spValidator($data)){
						foreach($result as $item) {
							throw new Exception($item[0]);
							break;
						}
					}
					if(!$id = $obj_active->create($data))
						throw new Exception("未知错误，添加失败");
					spClass('user_log')->save_log(2, "添加了渠道 ".$channel_rs["mechanism"]." 的活动 ".$data['actname']." [id:$id]");
					$url = spUrl("channels", "actlist", array("channelid"=>$channelid));
					$message = array('msg'=>'渠道活动添加成功','result'=>1, 'url'=>$url);
					echo json_encode($message);
					exit();
				}catch(Exception $e){
					$message = array('msg'=>$e->getMessage() ,'result'=>0);
					echo json_encode($message);
					exit();
				}
			}
			$this->validator = $obj_active->getValidatorJS();
			$this->channelid = $channelid;
			$this->channel_rs = $channel_rs;
			$this->saveurl = spUrl("channels", "createactive");
		}catch(Exception $e){
			$this->redirect(spUrl("channels", "channellist"), $e->getMessage());
		}
	}
	
	public function userlist(){
		try{
			$obj_channel = spClass("channel");
			$channelid = intval($this->spArgs("channelid"));
			$channel_rs = $obj_channel->get_act_mychannel($channelid);
			$obj_user = spClass('channel_user');
			$postdate = $this->spArgs();
			$page = intval(max($postdate['page'], 1));
			$condition = "crm_channel_user.channel_id = ".$channelid;
			if($postdate['searchkey'] != '')
				$condition .= " and (crm_channel_user.realname like '%{$postdate['searchkey']}%')";
			$this->user_rs = $obj_user->spPager($page, 20)->findAll($condition, 'crm_channel_user.createtime desc', "crm_channel_user.*");
			$this->pager = $obj_user->spPager()->getPager();
			$this->searchkey = $postdate['searchkey'];
			$this->channel_rs = $channel_rs;
			$this->channelid = $channelid;
			$this->url = spUrl('channels', 'userlist', array("channelid"=>$channelid, "searchkey"=>$this->searchkey));
		}catch(Exception $e){
			$this->redirect(spUrl("channels", "channellist"), $e->getMessage());
		}
	}
	
	public function createuser(){
		try {
			$obj_channel = spClass("channel");
			$channelid = intval($this->spArgs("channelid"));
			$channel_rs = $obj_channel->get_act_mychannel($channelid);
			$obj_user = spClass('channel_user');
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try {
					$postdate = $this->spArgs();
					$data = array();
					//客户端提交方法
					$data['create_id'] = $_SESSION["sscrm_user"]["id"];
					$data['channel_id'] = $channelid;
					$data['realname'] = $postdate['realname'];
					$data['telphone'] = $postdate['telphone'];
					$data['createtime'] = time();
					if($result = $obj_user->spValidator($data)){
						foreach($result as $item) {
							throw new Exception($item[0]);
							break;
						}
					}
					if(!$id = $obj_user->create($data))
						throw new Exception("未知错误，添加失败");
					spClass('user_log')->save_log(2, "添加了渠道 ".$channel_rs["mechanism"]." 的联系人 ".$data['realname']." [id:$id]");
					$url = spUrl("channels", "userlist", array("channelid"=>$channelid));
					$message = array('msg'=>'渠道联系人添加成功','result'=>1, 'url'=>$url);
					echo json_encode($message);
					exit();
				}catch(Exception $e){
					$message = array('msg'=>$e->getMessage() ,'result'=>0);
					echo json_encode($message);
					exit();
				}
			}
			$this->validator = $obj_user->getValidatorJS();
			$this->channelid = $channelid;
			$this->channel_rs = $channel_rs;
			$this->saveurl = spUrl("channels", "createuser");
		}catch(Exception $e){
			$this->redirect(spUrl("channels", "channellist"), $e->getMessage());
		}
	}
	
	public function channelrecordlist(){
		try {
			$obj_channel = spClass("channel");
			$channelid = intval($this->spArgs("channelid"));
			$channel_rs = $obj_channel->get_act_mychannel($channelid);
			$obj_record = spClass("channel_record");
			$postdate = $this->spArgs();
			$page = intval(max($postdate['page'], 1));
			$record_rs = $obj_record->join("crm_user")->join("crm_channel")->spPager($page, 20)->findAll(array("crm_channel_record.channel_id"=>$channelid), "crm_channel_record.createtime asc", "crm_channel_record.*, crm_user.realname as realname_create");
			$this->record_rs = $record_rs;
			$this->channel_rs = $channel_rs;
			$this->pager = $obj_record->spPager()->getPager();
			$this->channelid = $channelid;
			$this->url = spUrl('channels', 'channelrecordlist', array("channelid"=>$channelid));
		}catch(Exception $e){
			$this->redirect(spUrl("channels", "channellist"), $e->getMessage());
		}
	}
	
	public function createrecord(){
		try {
			$obj_channel = spClass("channel");
			$channelid = intval($this->spArgs("channelid"));
			$channel_rs = $obj_channel->get_act_mychannel($channelid);
			$obj_record = spClass("channel_record");
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try {
					$postdate = $this->spArgs();
					$data = array();
					$data["channel_id"] = $channelid;
					$data["create_id"] = $_SESSION["sscrm_user"]["id"];
					$data["content"] = $postdate["content"];
					//$data["acttime"] = strtotime($postdate["acttime"]);
					$data["acttime"] = $data["createtime"] = time();
					if($result = $obj_record->spValidator($data)){
						foreach($result as $item) {
							throw new Exception($item[0]);
							break;
						}
					}
					if(!$obj_record->create($data))
						throw new Exception("未知错误，沟通记录添加失败");
					$obj_channel->update(array("id"=>$channelid), array("recordtime"=>time()));
					spClass('user_log')->save_log(2, "添加了渠道 ".$channel_rs['mechanism']." [id:$channelid] 的沟通记录", array("channel_id"=>$channelid));
					$message = array('msg'=>"沟通记录添加成功", 'result'=>1, "url"=>spUrl("channels", "channelrecordlist", array("channelid"=>$channelid)));
					echo json_encode($message);
					exit();
				}catch(Exception $e){
					$message = array('msg'=>$e->getMessage(), 'result'=>0);
					echo json_encode($message);
					exit();
				}
			}
			$this->channel_rs = $channel_rs;
			$this->validator = $obj_record->getValidatorJS();
			$this->channelid = $channelid;
			$this->saveurl = spUrl("channels", "createrecord");
		}catch(Exception $e){
			$this->redirect(spUrl("channels", "channellist"), $e->getMessage());
		}
	}
	
	public function clientlist(){
		$obj_client = spClass('client');
		$obj_user = spClass('user');
		$obj_country = spClass('country');
		$obj_record = spClass("client_record");
		$obj_channel = spClass("channel");
		$obj_level = spClass("client_level");
		$postdate = $this->spArgs();
		$page = intval(max($postdate['page'], 1));
		$condition = "crm_client.channel_id > 0 and crm_channel.maintenance_id = {$_SESSION["sscrm_user"]["id"]} and crm_client.isdel = 0";
		if($channel_id = intval($postdate['channel_id'])){
			$condition .= " and crm_client.channel_id = $channel_id";
			$this->channel_id = $channel_id;
			if($act_rs = spClass('channel_active')->get_actives_by_channelid($channel_id))
				$this->act_rs = $act_rs;
		}
		if($channelact_id = intval($postdate['channelact_id'])){
			$condition .= " and channelact_id = $channelact_id";
			$this->channelact_id = $channelact_id;
		}
		if($postdate['starttime'] != ''){
			$condition .= " and crm_client.visit_time >= ".strtotime($postdate['starttime']);
			$this->starttime = $postdate['starttime'];
		}
		if($postdate['endtime'] != ''){
			$condition .= " and crm_client.visit_time <= ".strtotime($postdate['endtime']);
			$this->endtime = $postdate['endtime'];
		}
		if($postdate["searchkey"]){
			$condition .= " and (crm_client.realname like '%".$postdate['searchkey']."%')";
			$this->searchkey = $postdate['searchkey'];
		}
		if($postdate['ispay']."a" != "a"){
			$condition .= " and crm_client.ispay = ".intval($postdate['ispay']);
			$this->ispay = $postdate['ispay'];
		}
		if($level_id = intval($postdate['level_id'])){
			$condition .= " and crm_client.level_id = {$level_id}";
			$this->level_id = $level_id;
		}
		$client_sort = "crm_client.createtime desc";
		if($sort = $postdate['sort']){
			switch ($sort){
				case "sp_asc":
					$client_sort = "PY asc, ".$client_sort;
				break;
				case "sp_desc":
					$client_sort = "PY desc, ".$client_sort;
				break;
				default:
					$client_sort = "crm_client.createtime desc";
				break;
			}
			$this->sort = $sort;
		}
		if($client_rs = $obj_client->join("crm_client_level")->join("crm_channel")->join("crm_user as channel_user", "channel_user.id = crm_channel.maintenance_id")->join("crm_user", "crm_user.id = crm_client.user_sales_id")->join("crm_client_process", "crm_client_process.id = crm_client.process_id", "left")->spPager($page, 20)->findAll($condition, $client_sort, "crm_client.*, crm_client_level.name as level_name, crm_channel.mechanism, channel_user.realname as mchannel_realname, crm_user.realname as realname_sale, crm_client_process.pname, fristPinyin(crm_client.realname) as py")){
			foreach($client_rs as $key => $val){
				if($val["sourcetype"] == 1){
					$client_rs[$key]["oname"] = "渠道来源";
				}elseif($val["sourcetype"] == 2){
					$client_rs[$key]["record_count"] = $obj_record->getCountById($val["id"]);
				}
				if($val["exp_country_id"]){
					$client_rs[$key]["ctname"] = $obj_country->getname($val["exp_country_id"]);
				}
			}
			$this->client_rs = $client_rs;
		}
		$this->title = "我的渠道客户管理";
		$this->controller = "channels";
		$this->action = "clientlist";
		$this->level_rs = $obj_level->getlist();
		$this->channel_rs = $obj_channel->getAllChannel_prep("maintenance_id = {$_SESSION["sscrm_user"]["id"]}");
		$this->pager = $obj_client->spPager()->getPager();
		$this->url = spUrl($this->controller, $this->action, array("starttime"=>$this->starttime, "level_id"=>$this->level_id, "endtime"=>$this->endtime, "statdate"=>$this->statdate, "sort"=>$this->sort, "searchkey"=>$this->searchkey, "ispay"=>$this->ispay, "channel_id"=>$this->channel_id, "channelact_id"=>$this->channelact_id));
	}
	
	public function viewclient(){
		try {
			die("x");//考虑到不能查看客户资料，尤其是电话
			if(!$id = intval($this->spArgs('id')))
				throw new Exception('请先选择查看项，再进行操作！');
			$url = $_SERVER['HTTP_REFERER'];
			$obj_client = spClass("client");
			$obj_origin = spClass("origin");
			$obj_user = spClass("user");
			$obj_channel = spClass("channel");
			$obj_trader = spClass('trader');
			if(!$client_rs = $obj_client->join("crm_channel")->find(array("crm_channel.maintenance_id"=>$_SESSION["sscrm_user"]["id"], "crm_client.id"=>$id), null, "crm_client.*"))
				throw new Exception("找不到该客户");
			$this->client_rs = $client_rs;
			switch ($client_rs["sourcetype"]){
				case "1":
					$this->display("clients/viewclient_channel.html");
					break;
				case "2":
					$this->origin_rs = $obj_origin->getOriginById($client_rs["origin_id"]);
					if($this->origin_rs["extinput"]){
						$extinput_rs = explode("|", $this->origin_rs["extinput"]);
						$ext_field_rs = array();
						foreach($extinput_rs as $key => $val){
							$input_array = explode(",", $val);
							$extdata[$input_array["1"]] = $postdate[$input_array["1"]];
							$ext_field_rs[$input_array[1]] = array("type"=>$input_array[0], "field"=>$input_array[1], "fieldname"=>$input_array[2], "demand"=>$input_array[3]);
						}
						$this->ext_field_rs = $ext_field_rs;
					}
					$this->from_rs = $obj_origin->getClientViewFrom($this->origin_rs, $client_rs);
					$this->display("clients/viewclient.html");
				break;
				default:
					throw new Exception("来源通道不正确");
				break;
			}
		}catch(Exception $e){
			$this->redirect($url, $e->getMessage());
		}
	}
	
	public function clientrecordlist(){
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_client = spClass("client");
			if(!$client_rs = $obj_client->join("crm_channel")->find(array("crm_channel.maintenance_id"=>$_SESSION["sscrm_user"]["id"], "crm_client.id"=>$client_id), null, "crm_client.*"))
				throw new Exception("找不到该客户");
			$obj_record = spClass("client_record");
			$postdate = $this->spArgs();
			$page = intval(max($postdate['page'], 1));
			$record_rs = $obj_record->join("crm_user")->join("crm_client")->spPager($page, 20)->findAll(array("crm_client_record.client_id"=>$client_id, "crm_client_record.rtype_id"=>1), "crm_client_record.createtime asc", "crm_client_record.*, crm_user.realname as realname_create");
			$this->record_rs = $record_rs;
			$this->pager = $obj_record->spPager()->getPager();
			$this->client_id = $client_id;
			$this->client_rs = $client_rs;
			$this->no_tel = 1;
			$this->url = spUrl('channels', 'clientrecordlist', array("client_id"=>$client_id));
			$this->display("clients/clientrecordlist.html");
		}catch(Exception $e){
			$this->redirect(spUrl("channels", "clientlist"), $e->getMessage());
		}
	}
	
	//客户计划列表
	public function planlist(){
		try {
			$postdata = $this->spArgs();
			$obj_plan = spClass("client_plan");
			$page = intval(max($postdata['page'], 1));
			$condition = "crm_channel.maintenance_id = {$_SESSION["sscrm_user"]["id"]} and crm_client_plan.typeid = 3";
			if($channel_id = intval($this->spArgs("channel_id"))){
				$obj_channel = spClass("channel");
				if(!$this->channel_rs = $obj_channel->getMychannel($channel_id))
					throw new Exception("找不到该渠道");
				$this->createurl = spUrl("channels", "createplan", array("channel_id"=>$channel_id));
				$condition .= " and crm_client_plan.channel_id = $channel_id";
				$this->channel_id = $channel_id;
			}
			if($postdata["status"]){
				switch ($postdata["status"]){
					case "doing":
						$condition .= " and isfinish = 0 and crm_client_plan.starttime <= ".time();
						$this->status = $postdata["status"];
					break;
					case "going":
						$condition .= " and isfinish = 0 and crm_client_plan.starttime <= ".time()." and crm_client_plan.endtime >=" . time();
						$this->status = $postdata["status"];
					break;
					case "overdate":
						$condition .= " and isfinish = 0 and crm_client_plan.endtime <= ".time();
						$this->status = $postdata["status"];
					break;
					case "waiting":
						$condition .= " and isfinish = 0 and crm_client_plan.starttime >= ".time();
						$this->status = $postdata["status"];
					break;
					case "finish":
						$condition .= " and isfinish = 1";
						$this->status = $postdata["status"];
					break;
				}
			}
			$this->plan_rs = $obj_plan->join("crm_channel")->join("crm_user")->spPager($page, 10)->findAll($condition, "crm_client_plan.createtime desc", "crm_client_plan.*, crm_channel.mechanism, crm_channel.maintenance_id, crm_user.realname as realname_create");
			$this->pager = $obj_plan->spPager()->getPager();
			$this->url = spUrl('channels', 'planlist', array("status"=>$this->status));
		}catch(Exception $e){
			$this->redirect(spUrl("channels", "channellist"), $e->getMessage());
		}
	}
	
	//添加客户计划
	public function createplan(){
		try {
			if(!$channel_id = intval($this->spArgs("channel_id")))
				throw new Exception("参数丢失");
			$obj_channel = spClass("channel");
			if(!$this->channel_rs = $obj_channel->getMychannel($channel_id))
				throw new Exception("找不到该渠道");
			$obj_plan = spClass("client_plan");
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try {
					$postdata = $this->spArgs();
					$data = array();
					$data["channel_id"] = $channel_id;
					$data["typeid"] = "3";
					$data["create_id"] = $data["main_id"] = $_SESSION["sscrm_user"]["id"];
					$data["starttime"] = strtotime($postdata["starttime"]);
					$data["endtime"] = strtotime($postdata["endtime"]);
					$data["title"] = "联系渠道【".$this->channel_rs["mechanism"]."】";
					$data["content"] = $postdata["content"];
					$data["createtime"] = time();
					if($result = $obj_plan->spValidator($data)){
						foreach($result as $item) {
							throw new Exception($item[0]);
							break;
						}
					}
					if($data["endtime"] <= $data["starttime"])
						throw new Exception("结束时间必须大于开始时间");
					if(!$id = $obj_plan->create($data))
						throw new Exception("未知错误，添加失败");
					spClass('user_log')->save_log(10, "添加了渠道 ".$this->channel_rs['mechanism']." [id:$channel_id] 的日程[id:{$id}]", array("channel_id"=>$channel_id));
					$message = array('msg'=>"渠道日程添加成功", "url"=>spUrl("channels", "planlist", array("channel_id"=>$channel_id)), 'result'=>1);
					echo json_encode($message);
					exit();
				}catch(Exception $e){
					$message = array('msg'=>$e->getMessage(), 'result'=>0);
					echo json_encode($message);
					exit();
				}
			}
			$this->channel_id = $channel_id;
		}catch(Exception $e){
			$this->redirect(spUrl("channels", "channellist"), $e->getMessage());
		}
	}
	
	//修改客户计划
	public function modifyplan(){
		try {
			if(!$id = intval($this->spArgs("id")))
				throw new Exception("参数丢失");
			if(!$channel_id = intval($this->spArgs("channel_id")))
				throw new Exception("参数丢失");
			$obj_channel = spClass("channel");
			if(!$this->channel_rs = $obj_channel->getMychannel($channel_id))
				throw new Exception("找不到该渠道");
			$obj_plan = spClass("client_plan");
			if(!$plan_rs = $obj_plan->find(array("channel_id"=>$channel_id, "create_id"=>$_SESSION["sscrm_user"]["id"], "typeid"=>3, "id"=>$id)))
				throw new Exception("找不到该日程，可能是参数错误");
			if(date("Y-m-d", $plan_rs["createtime"]) != date("Y-m-d", time()))
				throw new Exception("只能修改当天的回访记录");
			$backurl = $_SERVER['HTTP_REFERER'];
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try {
					$postdata = $this->spArgs();
					$data = array();
					$data["title"] = $plan_rs["title"];
					$data["main_id"] = $plan_rs["main_id"];
					$data["starttime"] = strtotime($postdata["starttime"]);
					$data["endtime"] = strtotime($postdata["endtime"]);
					$data["content"] = $postdata["content"];
					if($result = $obj_plan->spValidator($data)){
						foreach($result as $item) {
							throw new Exception($item[0]);
							break;
						}
					}
					if($data["endtime"] <= $data["starttime"])
						throw new Exception("结束时间必须大于开始时间");
					if(!$obj_plan->update(array("id"=>$id), $data))
						throw new Exception("未知错误，更新失败");
					spClass('user_log')->save_log(2, "更新了渠道 ".$this->channel_rs['mechanism']." [id:$channel_id] 的日程[id:{$id}]", array("channel_id"=>$channel_id));
					$backurl = $postdata["backurl"] ? $postdata["backurl"] : spUrl("channels", "planlist", array("channel_id"=>$channel_id));
					$message = array('msg'=>"渠道日程修改成功", "url"=>$backurl, 'result'=>1);
					echo json_encode($message);
					exit();
				}catch(Exception $e){
					$message = array('msg'=>$e->getMessage(), 'result'=>0);
					echo json_encode($message);
					exit();
				}
			}
			$this->backurl = $backurl;
			$this->id = $id;
			$this->plan_rs = $plan_rs;
			$this->channel_id = $channel_id;
			$this->display("channels/createplan.html");
		}catch(Exception $e){
			$this->redirect(spUrl("channels", "channellist"), $e->getMessage());
		}
	}
	
	//修改客户计划状态
	public function modifyplan_status(){
		try {
			if(!$id = intval($this->spArgs("id")))
				throw new Exception("参数丢失");
			if(!$channel_id = intval($this->spArgs("channel_id")))
				throw new Exception("参数丢失");
			$obj_channel = spClass("channel");
			if(!$this->channel_rs = $obj_channel->getMychannel($channel_id))
				throw new Exception("找不到该渠道");
			$obj_plan = spClass("client_plan");
			if(!$plan_rs = $obj_plan->find(array("channel_id"=>$channel_id, "create_id"=>$_SESSION["sscrm_user"]["id"], "typeid"=>3, "id"=>$id)))
				throw new Exception("找不到该计划，可能是参数错误");
			if($plan_rs["isfinish"])
				throw new Exception("改计划已经完成，无法进行该操作");
			$backurl = $_SERVER['HTTP_REFERER'];
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try {
					$postdata = $this->spArgs();
					$data = array();
					$data["isfinish"] = intval($postdata["isfinish"]);
					$data["finishtime"] = time();
					if(!$data["isfinish"])
						throw new Exception("并没有设为已完成状态");
					if(!$obj_plan->update(array("id"=>$id), $data))
						throw new Exception("未知错误，更新失败");
					spClass('user_log')->save_log(2, "将渠道 ".$this->channel_rs['mechanism']." [id:$channel_id] 的日程[id:{$id}]设为已完成", array("channel_id"=>$channel_id));
					$backurl = $postdata["backurl"] ? $postdata["backurl"] : spUrl("channels", "planlist", array("channel_id"=>$channel_id));
					$message = array('msg'=>"渠道日程修改成功", "url"=>$backurl, 'result'=>1);
					echo json_encode($message);
					exit();
				}catch(Exception $e){
					$message = array('msg'=>$e->getMessage(), 'result'=>0);
					echo json_encode($message);
					exit();
				}
			}
			$this->backurl = $backurl;
			$this->id = $id;
			$this->plan_rs = $plan_rs;
			$this->channel_id = $channel_id;
		}catch(Exception $e){
			$this->redirect(spUrl("channels", "channellist"), $e->getMessage());
		}
	}
	
	public function ajaxActsByChannel(){
		try {
			$obj_channel = spClass("channel");
			$obj_active = spClass('channel_active');
			$channelid = intval($this->spArgs("channelid"));
			if(!$channelid)
				throw new Exception("参数错误");
			try{
				if(!$act_rs = $obj_active->get_actives_by_channelid($channelid))
					throw new Exception("该渠道暂无任何活动");
				echo json_encode(array('data'=>$act_rs, 'result'=>1));
				exit();
			}catch(Exception $e){
				echo json_encode(array('msg'=>$e->getMessage(), 'result'=>0));
				exit();
			}
		}catch(Exception $e){
			echo json_encode(array('msg'=>$e->getMessage(), 'result'=>-1));
			exit();
		}
	}
}
?>