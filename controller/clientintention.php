<?php
class clientintention extends spController {
	
	public function __construct(){
		parent::__construct();
		try {
			$manageaction = array("allclientlist", "clientrecordlist", "allrecordlist", "allplanlist", "saleallclientrecordlist", "intentionview", "batchtransfer");
			$action = $this->spArgs("a");
			$this->controller = "clientintention";
			$type = $this->spArgs("type");
			if(in_array($action, $manageaction) && !$_SESSION["sscrm_user"]["isdirector"] && !$type)
				throw new Exception("您不是总监，无权查看该页");
			if($type){
				$obj_type = spClass("client_intention_type");
				if(!$this->type_rs = $obj_type->find(array("id"=>$type)))
					throw new Exception("type参数错误");
				$depdirector = 0;
				$actdep = 0;
				if($this->type_rs["viewall_depdirector"]){
					$viewall_depdirector = explode(",", $this->type_rs["viewall_depdirector"]);
					if(in_array($_SESSION["sscrm_user"]["depart_id"], $viewall_depdirector) && $_SESSION["sscrm_user"]["isdirector"])
						$depdirector = 1;
				}
				if($this->type_rs["act_dep"]){
					$act_dep_array = explode(",", $this->type_rs["act_dep"]);
					if(!in_array($_SESSION["sscrm_user"]["depart_id"], $act_dep_array))
						throw new Exception("您无权查看该页面");
					$actdep = 1;
				}
				if($identity = $this->type_rs["identity"]){
					if(in_array($action, $manageaction)){
						if(!$depdirector && !$_SESSION["sscrm_user"]["user_identity"][$identity."_viewall"]["enabled"])
							throw new Exception("您无权查看该统计页面");
					}else{
						if(!$actdep && !$_SESSION["sscrm_user"]["user_identity"][$identity]["enabled"])
							throw new Exception("您无权查看该页面");
					}
				}
			}
			$this->controller = "clientintention";
			$this->clisturl = spUrl($this->controller, "allclientlist");
		}catch(Exception $e){
			$backurl = $_SERVER['HTTP_REFERER'];
			$this->redirect($backurl, $e->getMessage());
			exit();
		}
	}
	
	public function clientlist(){
		$obj_int = spClass("client_intention");
		$obj_type = spClass("client_intention_type");
		$obj_country = spClass('country');
		$obj_record = spClass("client_intention_record");
		$obj_recordsale = spClass("client_record");
		$obj_viprecordsale = spClass("vipclient_record");
		$obj_od = spClass('client_intention_overtime');
		$postdata = $this->spArgs();
		$page = intval(max($postdata['page'], 1));
		$condition = "crm_client_intention.create_id = {$_SESSION["sscrm_user"]["id"]} and crm_client_intention.isdel = 0";
		if($this->type_rs)
			$condition .= " and crm_client_intention.typeid = {$this->type_rs["id"]}";
		if($postdata['searchkey']){
			$condition .= " and (crm_client_intention.realname like '%{$postdata['searchkey']}%' or crm_client_intention.telphone like '%{$postdata['searchkey']}%')";
			$this->searchkey = $postdata['searchkey'];
		}
		if($postdata["isclient"]){
			switch($postdata["isclient"]){
				case "yes":
					$condition .= " and crm_client_intention.client_id > 0";
				break;
				case "no":
					$condition .= " and crm_client_intention.client_id = 0";
				break;
				default:
					
				break;
			}
			$this->isclient = $postdata["isclient"];
		}
		if($int_rs = $obj_int
			->join("crm_client_intention_type")
			->join("crm_client_intention_level")
			->join("crm_user as inituser", "inituser.id = crm_client_intention.init_create_id")
			->join("crm_client_intention_overtime", "crm_client_intention_overtime.intention_id = crm_client_intention.id and crm_client_intention_overtime.endtime = 0", "left")
			->join("crm_client_plan", "crm_client_intention.id = crm_client_plan.intention_id and crm_client_plan.typeid = 3 and crm_client_plan.isfinish = 0 and crm_client_plan.starttime <= UNIX_TIMESTAMP()", "left")
			->spPager($page, 15)->findAll($condition, "createtime desc", "crm_client_intention.*, crm_client_intention_overtime.fromtime, count(crm_client_plan.id) as plan_count, IF(crm_client_intention.vip_client_id, crm_client_intention.vip_client_id, crm_client_intention.client_id) as client_id, crm_client_intention_type.name as typename, crm_client_intention_level.name as levelname, inituser.realname as realname_init", "crm_client_intention.id")){
			foreach($int_rs as $key => $val){
				if($val["exp_country_id"])
					$int_rs[$key]["ctname"] = $obj_country->getname($val["exp_country_id"]);
				$int_rs[$key]["record_count"] = $obj_record->getCountById($val["id"]);
				if($val["vip_client_id"])
					$int_rs[$key]["record_sale_count"] = $obj_viprecordsale->getCountById($val["client_id"]);
				else 
					$int_rs[$key]["record_sale_count"] = $obj_recordsale->getCountById($val["client_id"]);
				$int_rs[$key]["overtime_count"] = $obj_od->getCount($val["id"]);
			}
		}
		$this->usetype_rs = $obj_type->getuselist();
		$this->int_rs = $int_rs;
		$this->pager = $obj_int->spPager()->getPager();
		$this->url = spUrl('clientintention', 'clientlist', array("type"=>$this->type_rs["id"], "searchkey"=>$this->searchkey, "isclient"=>$this->isclient));
	}
	
	public function allclientlist(){
		$obj_int = spClass("client_intention");
		$obj_type = spClass("client_intention_type");
		$obj_country = spClass('country');
		$obj_record = spClass("client_intention_record");
		$obj_recordsale = spClass("client_record");
		$obj_od = spClass('client_intention_overtime');
		$obj_user = spClass('user');
		$postdata = $this->spArgs();
		$page = intval(max($postdata['page'], 1));
		$condition = "crm_client_intention.isdel = 0 and crm_user.depart_id = {$_SESSION["sscrm_user"]["depart_id"]}";
		if($this->type_rs)
			$condition .= " and crm_client_intention.typeid = {$this->type_rs["id"]}";
		if($postdata['searchkey']){
			$condition .= " and (crm_client_intention.realname like '%{$postdata['searchkey']}%' or crm_client_intention.telphone like '%{$postdata['searchkey']}%')";
			$this->searchkey = $postdata['searchkey'];
		}
		if($postdata["isclient"]){
			switch($postdata["isclient"]){
				case "yes":
					$condition .= " and crm_client_intention.client_id > 0";
				break;
				case "no":
					$condition .= " and crm_client_intention.client_id = 0";
				break;
				default:
						
				break;
			}
			$this->isclient = $postdata["isclient"];
		}
		if($user_id = intval($postdata['user_id'])){
			$condition .= " and crm_client_intention.create_id = $user_id";
			$this->user_id = $user_id;
		}
		if($int_rs = $obj_int
			->join("crm_client_intention_type")
			->join("crm_client_intention_level")
			->join("crm_user")
			->join("crm_user as inituser", "inituser.id = crm_client_intention.init_create_id")
			->join("crm_client_intention_overtime", "crm_client_intention_overtime.intention_id = crm_client_intention.id and crm_client_intention_overtime.endtime = 0", "left")
			->join("crm_client_plan", "crm_client_intention.id = crm_client_plan.intention_id and crm_client_plan.typeid = 3 and crm_client_plan.isfinish = 0 and crm_client_plan.starttime <= UNIX_TIMESTAMP()", "left")
			->spPager($page, 15)->findAll($condition, "crm_client_intention.createtime desc", "crm_client_intention.*, crm_client_intention_overtime.fromtime, crm_client_intention_type.name as typename, crm_client_intention_level.name as levelname, count(crm_client_plan.id) as plan_count, crm_user.realname as realname_create, inituser.realname as realname_init", "crm_client_intention.id")){
			foreach($int_rs as $key => $val){
				if($val["exp_country_id"])
					$int_rs[$key]["ctname"] = $obj_country->getname($val["exp_country_id"]);
				$int_rs[$key]["record_count"] = $obj_record->getCountById($val["id"]);
				if($val["client_id"])
					$int_rs[$key]["record_sale_count"] = $obj_recordsale->getCountById($val["client_id"]);
				$int_rs[$key]["overtime_count"] = $obj_od->getCount($val["id"]);
			}
		}
		$this->usetype_rs = $obj_type->getviewlist();
		if($_SESSION["sscrm_user"]["isdirector"]){
			$this->user_rs = $obj_user->getUser_prep("depart_id = {$_SESSION["sscrm_user"]["depart_id"]}");
		}else{
			$this->user_rs = $obj_user->getUser_prep("find_in_set('telclient', identity_attr)");
		}
		$this->int_rs = $int_rs;
		$this->pager = $obj_int->spPager()->getPager();
		$this->url = spUrl('clientintention', 'allclientlist', array("type"=>$this->type_rs["id"], "user_id"=>$this->user_id, "searchkey"=>$this->searchkey, "isclient"=>$this->isclient));
		$this->transfer = 1;
	}
	
	public function prepcreate(){
		$obj_type = spClass("client_intention_type");
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			try{
				$postdata = $this->spArgs();
				if(!$type = intval($postdata["type"]))
					throw new Exception("请选择蓄水客户类型");
				echo json_encode(array("url"=>spUrl("clientintention", "create", array("type"=>$type)), "result"=>1));
				exit();
			}catch(Exception $e){
				echo json_encode(array("msg"=>$e->getMessage(), "result"=>0));
				exit();
			}
			
		}
		$this->usetype_rs = $obj_type->getuselist();
		$this->saveurl = spUrl("clientintention", "prepcreate");
	}
	
	public function create(){
		$obj_int = spClass("client_intention");
		$obj_user = spClass("user");
		$obj_type = spClass("client_intention_type");
		$obj_client = spClass("client");
		$obj_country = spClass('country');
		$obj_channel = spClass("channel");
		$postdata = $this->spArgs();
		if(!$use_type_rs = $obj_type->find(array("id"=>$this->type_rs["id"])))
			throw new Exception("该客户数据类型错误");
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			try {
				if(!$this->type_rs)
					throw new Exception("请选择蓄水客户类型");
				$data = array();
				$isAjax = $postdata['isAjax'];
				$data['typeid'] = $this->type_rs["id"];
				$data["init_create_id"] = $data['create_id'] = $_SESSION["sscrm_user"]["id"];
				if($use_type_rs["ischannel"]){
					$data['channel_id'] = intval($postdata['channel_id']);
					$data['channelact_id'] = intval($postdata['channelact_id']);
				}
				if($use_type_rs["isowner"]){
					if(!$data['user_owner_id'] = intval($postdata['user_owner_id']))
						throw new Exception("请选择客户来源人");
				}
				$data['realname'] = $postdata['realname'];
				$data['sex'] = intval($postdata['sex']);
				$data['tel_location'] = $postdata["tel_location"];
				$data['telphone'] = $postdata['telphone'];
				$data['address'] = $postdata['address'];
				$data['profession'] = $postdata['profession'];
				$data['cred_id'] = intval($postdata['cred_id']);
				$data['cred_license'] = $postdata['cred_license'];
				$data['email'] = $postdata['email'];
				$data['wechat'] = $postdata['wechat'];
				$data['exp_country_id'] = intval($postdata['exp_country_id']);
				$data['demand'] = $postdata['demand'];
				$data["feedback"] = $postdata["feedback"];
				$data['receivetime'] = $data['createtime'] = time();
				$record_data['content'] = $postdata["content"];
				if($result = $obj_int->spValidator($data)) {
					foreach($result as $item) {
						throw new Exception($item[0]);
					}
				}
				if($this->type_rs["ischannel"]){
					if(!$data['channel_id'])
						throw new Exception("请选择渠道");
				}
				if($obj_int->find(array("telphone"=>$data['telphone'])))
					throw new Exception("该电话号码已存在，无法再次录入系统");
				if($obj_client->find(array("telphone"=>$data['telphone'])))
					throw new Exception("该电话号码已录入跟进客户中，无法再次录入系统");
				if($record_data['content']){
					$obj_record = spClass("client_intention_record");
					$record_data["intention_id"] = 0;
					$record_data["create_id"] = $data['create_id'];
					$record_data["acttime"] = $record_data["createtime"] = time();
					if($result = $obj_record->spValidator($record_data)){
						foreach($result as $item) {
							throw new Exception($item[0]);
							break;
						}
					}
				}
				$obj_int->getDb()->beginTrans();
				if(!$id = $obj_int->create($data))
					throw new Exception("未知错误，添加失败！");
				spClass('user_log')->save_log(11, "添加了意向客户 ".$data['realname']." [id:$id]", array("intention_id"=>$id));
				if($record_data['content']){
					$record_data["intention_id"] = $id;
					if(!$obj_record->create($record_data))
						throw new Exception("未知错误，沟通记录添加失败");
					spClass('user_log')->save_log(11, "添加了{$this->type_rs["name"]}蓄水客户 ".$data['realname']." [id:$id] 的沟通记录", array("intention_id"=>$id));
				}
				$obj_int->getDb()->commitTrans();
				$url = spUrl("clientintention", "clientlist", array('type'=>$this->type_rs["id"]));
				$message = array('msg'=>'添加成功！','result'=>1, 'url'=>$url);
				echo json_encode($message);
				exit();
			}catch(Exception $e){
				$obj_int->getDb()->rollbackTrans();
				$message = array('msg' => $e->getMessage(), 'result'=>0);
				echo json_encode($message);
				exit();
			}
		}
		if($use_type_rs["ischannel"])
			$this->channel_prep_rs = $obj_channel->getAllChannel_prep("maintenance_id = {$_SESSION["sscrm_user"]["id"]}");
		if($use_type_rs["isowner"]){
			$groupdepart_condition = "crm_user.id <> {$_SESSION["sscrm_user"]["id"]}";
			$this->user_group_rs = $obj_user->getUserGroupDepart_prep($groupdepart_condition);
			unset($groupdepart_condition);
		}
		$this->use_type_rs = $use_type_rs;
		$this->cred_rs = spClass("credential")->get_credential();
		$this->country_rs = $obj_country->getlist();
		$this->saveurl = spUrl("clientintention", "create");
	}
	
	public function modify(){
		try {
			$obj_user = spClass("user");
			$obj_int = spClass("client_intention");
			$obj_type = spClass("client_intention_type");
			$obj_client = spClass("client");
			$obj_country = spClass('country');
			$obj_channel = spClass("channel");
			$obj_act = spClass("channel_active");
			$postdata = $this->spArgs();
			if(!$id = intval($postdata["id"]))
				throw new Exception("意向客户参数丢失");
			if(!$int_rs = $obj_int->find(array("id"=>$id, "create_id"=>$_SESSION["sscrm_user"]["id"])))
				throw new Exception("找不到该意向客户，可能已被删除");
			if($int_rs["client_id"])
				throw new Exception("该意向客户已经被添加到系统客户中，无法修改");
			if($int_rs["vip_client_id"])
				throw new Exception("该意向客户已经被添加到大客户中，无法修改");
			if(!$use_type_rs = $obj_type->find(array("id"=>$int_rs["typeid"])))
				throw new Exception("该客户数据类型错误");
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try {
					$data = array();
					$isAjax = $postdata['isAjax'];
					if($use_type_rs["ischannel"]){
						$data['channel_id'] = intval($int_rs['channel_id']);
						$data['channelact_id'] = intval($postdata['channelact_id']);
					}
					if($use_type_rs["isowner"]){
						if(!$data['user_owner_id'] = intval($postdata['user_owner_id']))
							throw new Exception("请选择客户来源人");
					}
					$data['typeid'] = $use_type_rs['id'];
					$data['realname'] = $postdata['realname'];
					$data['tel_location'] = $postdata["tel_location"];
					$data['telphone'] = $int_rs["telphone"];
					$data['address'] = $postdata['address'];
					$data['profession'] = $postdata['profession'];
					$data['cred_id'] = intval($postdata['cred_id']);
					$data['cred_license'] = $postdata['cred_license'];
					$data['email'] = $postdata['email'];
					$data['wechat'] = $postdata['wechat'];
					$data['exp_country_id'] = intval($postdata['exp_country_id']);
					$data['demand'] = $postdata['demand'];
					if($result = $obj_int->spValidator($data)) {
						foreach($result as $item) {
							throw new Exception($item[0]);
						}
					}
					if($use_type_rs["ischannel"]){
						if(!$data['channel_id'])
							throw new Exception("请选择渠道");
					}
					$obj_int->getDb()->beginTrans();
					if(!$obj_int->update(array("id"=>$id), $data))
						throw new Exception("未知错误，添加失败！");
					spClass('user_log')->save_log(11, "更新了{$use_type_rs["name"]}蓄水客户 ".$data['realname']." [id:$id]的资料", array("intention_id"=>$id));
					$obj_int->getDb()->commitTrans();
					$url = spUrl("clientintention", "clientlist", array('type'=>$this->type_rs["id"]));
					$message = array('msg'=>'更新成功！','result'=>1, 'url'=>$url);
					echo json_encode($message);
					exit();
				}catch(Exception $e){
					$obj_int->getDb()->rollbackTrans();
					$message = array('msg' => $e->getMessage(), 'result'=>0);
					echo json_encode($message);
					exit();
				}
			}
			if($int_rs["channel_id"]){
				$this->channel_name = $obj_channel->getname($int_rs["channel_id"]);
				$this->act_rs = $obj_act->get_actives_by_channelid($int_rs["channel_id"]);
			}
			if($use_type_rs["isowner"]){
				$groupdepart_condition = "crm_user.id <> {$_SESSION["sscrm_user"]["id"]}";
				$this->user_group_rs = $obj_user->getUserGroupDepart_prep($groupdepart_condition);
				unset($groupdepart_condition);
			}
			$this->id = $id;
			$this->int_rs = $int_rs;
			$this->country_rs = $obj_country->getlist();
			$this->cred_rs = spClass("credential")->get_credential();
			$this->saveurl = spUrl("clientintention", "modify");
			$this->channel_prep_rs = $obj_channel->getAllChannel_prep("maintenance_id = {$_SESSION["sscrm_user"]["id"]}");
			$this->use_type_rs = $use_type_rs;
			$this->display("clientintention/create.html");
			exit();
		}catch(Exception $e){
			$this->redirect(spUrl("clientintention", "clientlist", array('type'=>$this->type_rs["id"])), $e->getMessage());
			exit();
		}
	}
	
	public function modify_level(){
		try {
			$obj_int = spClass("client_intention");
			$obj_level = spClass("client_intention_level");
			$postdata = $this->spArgs();
			if(!$id = intval($postdata["id"]))
				throw new Exception("意向客户参数丢失");
			if(!$int_rs = $obj_int->find(array("id"=>$id, "create_id"=>$_SESSION["sscrm_user"]["id"])))
				throw new Exception("找不到该意向客户，可能已被删除");
			if($int_rs["client_id"])
				throw new Exception("该意向客户已经被添加到系统客户中，无法修改");
			if($int_rs["vip_client_id"])
				throw new Exception("该意向客户已经被添加到大客户中，无法修改");
			$level_name = $obj_level->getName($int_rs["level_id"]);
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try {
					$postdata = $this->spArgs();
					$data = array();
					$data["level_id"] = intval($postdata["level_id"]);
					if(!$new_level_name = $obj_level->getName($data["level_id"]))
						throw new Exception("找不到该分级，可能已经丢失");
					if(!$obj_int->update(array("id"=>$id), $data))
						throw new Exception("未知错误，更新失败");
					spClass('user_log')->save_log(11, "更新了蓄水客户 ".$int_rs['realname']." [id:$id] 的级别，由 {$level_name} 改为了 {$new_level_name}", array("intention_id"=>$id));
					$backurl = $postdata["backurl"] ? $postdata["backurl"] : spUrl("clientintention", "clientlist", array("type"=>$this->type_rs["id"]));
					$message = array('msg'=>"蓄水客户级别修改成功", 'result'=>1, "url"=>$backurl);
					echo json_encode($message);
					exit();
				}catch(Exception $e){
					$message = array('msg'=>$e->getMessage(), 'result'=>0);
					echo json_encode($message);
					exit();
				}
			}
			$this->int_rs = $int_rs;
			$this->id = $id;
			$this->level_rs = $obj_level->getlist();
		}catch(Exception $e){
			$this->redirect(spUrl("clientintention", "clientlist"), $e->getMessage());
		}
	}
	
	public function view(){
		try {
			$obj_int = spClass("client_intention");
			$obj_client = spClass("client");
			$obj_country = spClass('country');
			$obj_channel = spClass('channel');
			$obj_active = spClass('channel_active');
			$postdata = $this->spArgs();
			if(!$id = intval($postdata["id"]))
				throw new Exception("意向客户参数丢失");
			if(!$int_rs = $obj_int->join("crm_client_intention_type")->join("crm_credential")->find(array("crm_client_intention.id"=>$id, "crm_client_intention.create_id"=>$_SESSION["sscrm_user"]["id"]), null, "crm_client_intention.*, crm_client_intention_type.name as typename, crm_credential.cname"))
				throw new Exception("找不到该意向客户，可能已被删除");
			if($int_rs["exp_country_id"])
				$this->country_rs = $obj_country->find(array("id"=>$int_rs["exp_country_id"]));
			if($this->type_rs["ischannel"]){
				if($int_rs["channel_id"])
					$this->channel_rs = $obj_channel->getChannelById($int_rs["channel_id"]);
				if($int_rs["channelact_id"])
					$this->active_rs = $obj_active->getname($int_rs["channelact_id"]);
			}
			$this->int_rs = $int_rs;
			$this->display("clientintention/intentionview.html");
			exit();
		}catch(Exception $e){
			$this->redirect(spUrl("clientintention", "clientlist", array('type'=>$this->type_rs["id"])), $e->getMessage());
			exit();
		}
	}
	
	public function intentionview(){
		try {
			$obj_int = spClass("client_intention");
			$obj_country = spClass('country');
			$postdata = $this->spArgs();
			if(!$id = intval($postdata["id"]))
				throw new Exception("意向客户参数丢失");
			if(!$int_rs = $obj_int->join("crm_client_intention_type")->join("crm_credential")->find(array("crm_client_intention.id"=>$id), null, "crm_client_intention.*, crm_client_intention_type.name as typename, crm_credential.cname"))
				throw new Exception("找不到该意向客户，可能已被删除");
			if($int_rs["exp_country_id"])
				$this->country_rs = $obj_country->find(array("id"=>$int_rs["exp_country_id"]));
			$this->int_rs = $int_rs;
		}catch(Exception $e){
			$this->redirect(spUrl("clientintention", "clientlist", array('type'=>$this->type_rs["id"])), $e->getMessage());
			exit();
		}
	}
	
	//蓄水客户计划
	public function planlist(){
		try {
			$postdata = $this->spArgs();
			$obj_plan = spClass("client_plan");
			$page = intval(max($postdata['page'], 1));
			$condition = "crm_client_plan.main_id = {$_SESSION["sscrm_user"]["id"]} and crm_client_plan.typeid = 3";
			if($intention_id = intval($this->spArgs("intention_id"))){
				$obj_client = spClass("client_intention");
				if(!$this->client_rs = $obj_client->find(array("crm_client_intention.id"=>$intention_id, "crm_client_intention.create_id"=>$_SESSION["sscrm_user"]["id"])))
					throw new Exception("找不到该蓄水客户");
				$this->createurl = spUrl("clientintention", "createplan", array("type"=>$this->type_rs["id"], "intention_id"=>$intention_id));
				$condition .= " and crm_client_plan.intention_id = $intention_id";
				$this->intention_id = $intention_id;
			}
			if($postdata["status"]){
				switch ($postdata["status"]){
					case "doing":
						$condition .= " and crm_client_plan.isfinish = 0 and crm_client_plan.starttime <= ".time();
						$this->status = $postdata["status"];
						break;
					case "going":
						$condition .= " and crm_client_plan.isfinish = 0 and crm_client_plan.starttime <= ".time()." and crm_client_plan.endtime >=" . time();
						$this->status = $postdata["status"];
						break;
					case "overdate":
						$condition .= " and crm_client_plan.isfinish = 0 and crm_client_plan.endtime <= ".time();
						$this->status = $postdata["status"];
						break;
					case "waiting":
						$condition .= " and crm_client_plan.isfinish = 0 and crm_client_plan.starttime >= ".time();
						$this->status = $postdata["status"];
						break;
					case "finish":
						$condition .= " and crm_client_plan.isfinish = 1";
						$this->status = $postdata["status"];
						break;
				}
			}
			$this->plan_rs = $obj_plan->join("crm_client_intention")->join("crm_user")->spPager($page, 10)->findAll($condition, "crm_client_plan.createtime desc", "crm_client_plan.*, crm_client_intention.realname, crm_client_intention.create_id as intention_create_id, crm_client_intention.realname as realname_create");
			$this->pager = $obj_plan->spPager()->getPager();
			$this->url = spUrl('clientintention', 'planlist', array("type"=>$this->type_rs["id"], "status"=>$this->status));
		}catch(Exception $e){
			$this->redirect(spUrl("clientintention", "clientlist", array("type"=>$this->type_rs["id"])), $e->getMessage());
		}
	}
	
	public function allplanlist(){
		try {
			$postdata = $this->spArgs();
			$obj_plan = spClass("client_plan");
			$obj_client = spClass("client_intention");
			$page = intval(max($postdata['page'], 1));
			$condition = "crm_client_plan.typeid = 3 and crm_user.depart_id = {$_SESSION["sscrm_user"]["depart_id"]}";
			if($intention_id = intval($this->spArgs("intention_id"))){
				if(!$this->client_rs = $obj_client->find(array("crm_client_intention.id"=>$intention_id)))
					throw new Exception("找不到该蓄水客户");
				$condition .= " and crm_client_plan.intention_id = $intention_id";
				$this->intention_id = $intention_id;
			}
			if($postdata["status"]){
				switch ($postdata["status"]){
					case "doing":
						$condition .= " and crm_client_plan.isfinish = 0 and crm_client_plan.starttime <= ".time();
						$this->status = $postdata["status"];
						break;
					case "going":
						$condition .= " and crm_client_plan.isfinish = 0 and crm_client_plan.starttime <= ".time()." and crm_client_plan.endtime >=" . time();
						$this->status = $postdata["status"];
						break;
					case "overdate":
						$condition .= " and crm_client_plan.isfinish = 0 and crm_client_plan.endtime <= ".time();
						$this->status = $postdata["status"];
						break;
					case "waiting":
						$condition .= " and crm_client_plan.isfinish = 0 and crm_client_plan.starttime >= ".time();
						$this->status = $postdata["status"];
						break;
					case "finish":
						$condition .= " and crm_client_plan.isfinish = 1";
						$this->status = $postdata["status"];
						break;
				}
			}
			$this->backurl = spUrl("clientintention", "allclientlist");
			$this->plan_rs = $obj_plan->join("crm_client_intention")->join("crm_user")->spPager($page, 10)->findAll($condition, "crm_client_plan.createtime desc", "crm_client_plan.*, crm_client_intention.realname, crm_client_intention.create_id as intention_create_id, crm_client_intention.telphone, crm_client_intention.realname as realname_create");
			$this->pager = $obj_plan->spPager()->getPager();
			$this->url = spUrl('clientintention', 'allplanlist', array("type"=>$this->type_rs["id"], "intention_id"=>$this->intention_id, "status"=>$this->status));
		}catch(Exception $e){
			$this->redirect(spUrl("clientintention", "clientlist", array("type"=>$this->type_rs["id"])), $e->getMessage());
		}
	}
	
	//添加客户计划
	public function createplan(){
		try {
			if(!$intention_id = intval($this->spArgs("intention_id")))
				throw new Exception("参数丢失");
			$obj_client = spClass("client_intention");
			if(!$this->client_rs = $obj_client->find(array("crm_client_intention.id"=>$intention_id, "crm_client_intention.create_id"=>$_SESSION["sscrm_user"]["id"])))
				throw new Exception("找不到该客户");
			$obj_plan = spClass("client_plan");
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try {
					$postdata = $this->spArgs();
					$data = array();
					$data["intention_id"] = $intention_id;
					$data["typeid"] = "3";
					$data["create_id"] = $data["main_id"] = $_SESSION["sscrm_user"]["id"];
					$data["starttime"] = strtotime($postdata["starttime"]);
					$data["endtime"] = strtotime($postdata["endtime"]);
					$data["title"] = "联系蓄水客户【".$this->client_rs["realname"]."】";
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
					spClass('user_log')->save_log(10, "添加了蓄水客户 ".$this->client_rs['realname']." [id:$intention_id] 的日程[id:{$id}]", array("intention_id"=>$intention_id));
					$message = array('msg'=>"客户日程添加成功", "url"=>spUrl("clientintention", "planlist", array("type"=>$this->type_rs["id"], "intention_id"=>$intention_id)), 'result'=>1);
					echo json_encode($message);
					exit();
				}catch(Exception $e){
					$message = array('msg'=>$e->getMessage(), 'result'=>0);
					echo json_encode($message);
					exit();
				}
			}
			$this->intention_id = $intention_id;
		}catch(Exception $e){
			$this->redirect(spUrl("clientintention", "clientlist", array("type"=>$this->type_rs["id"])), $e->getMessage());
		}
	}
	
	//修改客户计划
	public function modifyplan(){
		try {
			if(!$id = intval($this->spArgs("id")))
				throw new Exception("参数丢失");
			if(!$intention_id = intval($this->spArgs("intention_id")))
				throw new Exception("参数丢失");
			$obj_client = spClass("client_intention");
			if(!$this->client_rs = $obj_client->find(array("crm_client_intention.id"=>$intention_id, "crm_client_intention.create_id"=>$_SESSION["sscrm_user"]["id"])))
				throw new Exception("找不到该客户");
			$obj_plan = spClass("client_plan");
			if(!$plan_rs = $obj_plan->find(array("intention_id"=>$intention_id, "create_id"=>$_SESSION["sscrm_user"]["id"], "typeid"=>3, "id"=>$id)))
				throw new Exception("找不到该计划，可能是参数错误");
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
					spClass('user_log')->save_log(10, "更新了蓄水客户 ".$this->client_rs['realname']." [id:$intention_id] 的计划任务[id:{$id}]", array("intention_id"=>$intention_id));
					$backurl = $postdata["backurl"] ? $postdata["backurl"] : spUrl("clientintention", "planlist", array("type"=>$this->type_rs["id"], "intention_id"=>$intention_id));
					$message = array('msg'=>"蓄水客户计划修改成功", "url"=>$backurl, 'result'=>1);
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
			$this->intention_id = $intention_id;
			$this->display("clientintention/createplan.html");
		}catch(Exception $e){
			$this->redirect(spUrl("clientintention", "clientlist", array("type"=>$this->type_rs["id"])), $e->getMessage());
		}
	}
	
	//修改客户计划状态
	public function modifyplan_status(){
		try {
			if(!$id = intval($this->spArgs("id")))
				throw new Exception("参数丢失");
			if(!$intention_id = intval($this->spArgs("intention_id")))
				throw new Exception("参数丢失");
			$obj_client = spClass("client_intention");
			if(!$this->client_rs = $obj_client->find(array("crm_client_intention.id"=>$intention_id, "crm_client_intention.create_id"=>$_SESSION["sscrm_user"]["id"])))
				throw new Exception("找不到该客户");
			$obj_plan = spClass("client_plan");
			if(!$plan_rs = $obj_plan->find(array("intention_id"=>$intention_id, "create_id"=>$_SESSION["sscrm_user"]["id"], "id"=>$id)))
				throw new Exception("找不到该计划，是参数错误");
			if($plan_rs["isfinish"])
				throw new Exception("该计划已经完成，无法进行该操作");
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
					spClass('user_log')->save_log(10, "将蓄水客户 ".$this->client_rs['realname']." [id:$intention_id] 的计划任务[id:{$id}]设为已完成", array("intention_id"=>$intention_id));
					$backurl = $postdata["backurl"] ? $postdata["backurl"] : spUrl("clientintention", "planlist", array("type"=>$this->type_rs["id"], "intention_id"=>$intention_id));
					$message = array('msg'=>"蓄水客户日程修改成功", "url"=>$backurl, 'result'=>1);
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
			$this->intention_id = $intention_id;
		}catch(Exception $e){
			$this->redirect(spUrl("clientintention", "clientlist", array("type"=>$this->type_rs["id"])), $e->getMessage());
		}
	}
	
	public function recordlist(){
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_int = spClass("client_intention");
			if(!$this->int_rs = $obj_int->find(array("id"=>$client_id, "create_id"=>$_SESSION["sscrm_user"]["id"])))
				throw new Exception("找不到该意向客户");
			$obj_record = spClass("client_intention_record");
			$postdata = $this->spArgs();
			$page = intval(max($postdata['page'], 1));
			$record_rs = $obj_record->join("crm_user")->join("crm_client_intention")->spPager($page, 20)->findAll(array("crm_client_intention_record.intention_id"=>$client_id), "crm_client_intention_record.createtime desc", "crm_client_intention_record.*, crm_user.realname as realname_create");
			$this->record_rs = $record_rs;
			$this->pager = $obj_record->spPager()->getPager();
			$this->client_id = $client_id;
			$this->url = spUrl('clientintention', 'recordlist', array("type"=>$this->type_rs["id"], "client_id"=>$client_id));
		}catch(Exception $e){
			$this->redirect(spUrl("clientintention", "clientlist", array("type"=>$this->type_rs["id"])), $e->getMessage());
		}
	}
	
	public function clientrecordlist(){
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_int = spClass("client_intention");
			if(!$this->int_rs = $obj_int->find(array("id"=>$client_id)))
				throw new Exception("找不到该意向客户");
			$obj_record = spClass("client_intention_record");
			$postdata = $this->spArgs();
			$page = intval(max($postdata['page'], 1));
			$record_rs = $obj_record->join("crm_user")->join("crm_client_intention")->spPager($page, 15)->findAll(array("crm_client_intention_record.intention_id"=>$client_id), "crm_client_intention_record.createtime desc", "crm_client_intention_record.*, crm_user.realname as realname_create");
			$this->record_rs = $record_rs;
			$this->pager = $obj_record->spPager()->getPager();
			$this->client_id = $client_id;
			$this->url = spUrl('clientintention', 'clientrecordlist', array("type"=>$this->type_rs["id"], "client_id"=>$client_id));
		}catch(Exception $e){
			$this->redirect(spUrl("clientintention", "clientrecordlist", array("type"=>$this->type_rs["id"])), $e->getMessage());
		}
	}
	
	public function createrecord(){
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_int = spClass("client_intention");
			if(!$this->int_rs = $obj_int->find(array("id"=>$client_id, "create_id"=>$_SESSION["sscrm_user"]["id"])))
				throw new Exception("找不到该意向客户");
			$obj_record = spClass("client_intention_record");
			$obj_od = spClass("client_intention_overtime");
			$postdate = $this->spArgs();
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try {
					$data = array();
					$data["intention_id"] = $client_id;
					$data["create_id"] = $_SESSION["sscrm_user"]["id"];
					$data["content"] = $postdate["content"];
					$data["acttime"] = $data["createtime"] = time();
					if($result = $obj_record->spValidator($data)){
						foreach($result as $item) {
							throw new Exception($item[0]);
							break;
						}
					}
					if(!$obj_record->create($data))
						throw new Exception("未知错误，沟通记录添加失败");
					if($od_rs = $obj_od->find(array("intention_id"=>$client_id, "endtime"=>0)))
						$obj_od->update(array("id"=>$od_rs["id"]), array("endtime"=>time()));
					spClass('user_log')->save_log(11, "添加了意向客户 ".$this->int_rs['realname']." [id:$client_id] 的沟通记录", array("intention_id"=>$client_id));
					$backurl = spUrl("clientintention", "recordlist", array("type"=>$this->type_rs["id"], "client_id"=>$client_id));
					$message = array('msg'=>"沟通记录添加成功", 'result'=>1, "url"=>$backurl);
					echo json_encode($message);
					exit();
				}catch(Exception $e){
					$message = array('msg'=>$e->getMessage(), 'result'=>0);
					echo json_encode($message);
					exit();
				}
			}
			$this->validator = $obj_record->getValidatorJS();
			$this->client_id = $client_id;
			$this->saveurl = spUrl("clientintention", "createrecord");
		}catch(Exception $e){
			$this->redirect(spUrl("clientintention", "clientlist", array("type"=>$this->type_rs["id"])), $e->getMessage());
		}
	}
	
	public function modifyrecord(){
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			if(!$id = intval($this->spArgs("id")))
				throw new Exception("参数丢失");
			$obj_int = spClass("client_intention");
			if(!$this->int_rs = $obj_int->find(array("id"=>$client_id, "create_id"=>$_SESSION["sscrm_user"]["id"])))
				throw new Exception("找不到该意向客户");
			$obj_record = spClass("client_intention_record");
			if(!$record_rs = $obj_record->find(array("intention_id"=>$client_id, "id"=>$id)))
				throw new Exception("找不到该客户的回访记录，请联系系统管理员");
			if(date("Y-m-d", $record_rs["createtime"]) != date("Y-m-d", time()))
				throw new Exception("只能修改当天的回访记录");
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try {
					$postdate = $this->spArgs();
					$data = array();
					$data["acttime"] = $record_rs["acttime"];
					$data["content"] = $postdate["content"];
					if($result = $obj_record->spValidator($data)){
						foreach($result as $item) {
							throw new Exception($item[0]);
							break;
						}
					}
					if(!$obj_record->update(array("id"=>$id), $data))
						throw new Exception("未知错误，沟通记录修改失败");
					spClass('user_log')->save_log(11, "修改了蓄水客户 ".$this->int_rs['realname']." [id:$client_id] 的沟通记录[id:$id]", array("intention_id"=>$client_id));
					$message = array('msg'=>"沟通记录修改成功", 'result'=>1, "url"=>spUrl("clientintention", "recordlist", array("type"=>$this->type_rs["id"], "client_id"=>$client_id)));
					echo json_encode($message);
					exit();
				}catch(Exception $e){
					$message = array('msg'=>$e->getMessage(), 'result'=>0);
					echo json_encode($message);
					exit();
				}
			}
			$this->validator = $obj_record->getValidatorJS();
			$this->client_id = $client_id;
			$this->saveurl = spUrl("clientintention", "modifyrecord");
			$this->id = $id;
			$this->record_rs = $record_rs;
			$this->display("clientintention/createrecord.html");
		}catch(Exception $e){
			$this->redirect(spUrl("clientintention", "clientlist", array("type"=>$this->type_rs["id"])), $e->getMessage());
		}
	}
	
	public function allrecordlist(){
		try {
			$obj_record = spClass("client_intention_record");
			$obj_type = spClass("client_intention_type");
			$obj_user = spClass('user');
			$postdata = $this->spArgs();
			$page = intval(max($postdata['page'], 1));
			$condition = "crm_user.depart_id = {$_SESSION["sscrm_user"]["depart_id"]}";
			if($this->type_rs)
				$condition .= " and crm_client_intention.typeid = {$this->type_rs["id"]}";
			if($postdata['starttime'] != ''){
				$condition .= " and crm_client_intention_record.acttime >= ".strtotime($postdata['starttime']);
				$this->starttime = $postdata['starttime'];
			}
			if($postdata['endtime'] != ''){
				$condition .= " and crm_client_intention_record.acttime <= ".strtotime($postdata['endtime']);
				$this->endtime = $postdata['endtime'];
			}
			if($user_id = intval($postdata['user_id'])){
				$condition .= " and crm_client_intention.create_id = $user_id";
				$this->user_id = $user_id;
			}
			if($intention_id = intval($postdata['intention_id'])){
				if($this->int_rs = $obj_int->findByPk($intention_id, "realname, sex, telphone")){
					$condition .= " and crm_client_intention_record.intention_id = ".$intention_id;
					$this->intention_id = $intention_id;
				}
			}
			$record_rs = $obj_record->join("crm_user")->join("crm_client_intention")->spPager($page, 20)->findAll($condition, "crm_client_intention_record.acttime desc", "crm_client_intention_record.*, crm_client_intention.realname, crm_client_intention.sex, crm_client_intention.telphone, crm_user.realname as realname_create");
			$this->record_rs = $record_rs;
			$this->pager = $obj_record->spPager()->getPager();
			$this->url = spUrl('clientintention', 'allrecordlist', array("type"=>$this->type_rs["id"], "starttime"=>$this->starttime, "endtime"=>$this->endtime, "user_id"=>$this->user_id, "intention_id"=>$this->intention_id));
			$this->user_rs = $obj_user->getUser_prep("depart_id = {$_SESSION["sscrm_user"]["depart_id"]}");
			$this->usetype_rs = $obj_type->getviewlist();
		}catch(Exception $e){
			$this->redirect(spUrl("clientintention", "clientlist", array("type"=>$this->type_rs["id"])), $e->getMessage());
		}
	}
	
	public function saleclientrecordlist(){
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_client = spClass("client");
			$obj_int = spClass("client_intention");
			if(!$this->int_rs = $obj_int->find(array("client_id"=>$client_id, "create_id"=>$_SESSION["sscrm_user"]["id"])))
				throw new Exception("找不到该意向客户");
			if(!$this->client_rs = $obj_client->getClientById($client_id))
				throw new Exception("找不到该客户");
			$obj_record = spClass("client_record");
			$postdate = $this->spArgs();
			$page = intval(max($postdate['page'], 1));
			$record_rs = $obj_record->join("crm_user")->join("crm_client")->spPager($page, 20)->findAll(array("crm_client_record.client_id"=>$client_id, "crm_client_record.rtype_id"=>1), "crm_client_record.createtime asc", "crm_client_record.*, crm_user.realname as realname_create");
			$this->record_rs = $record_rs;
			$this->pager = $obj_record->spPager()->getPager();
			$this->client_id = $client_id;
			$this->url = spUrl('clientintention', 'saleclientrecordlist', array("type"=>$this->type_rs["id"], "client_id"=>$client_id));
			$this->display("clients/clientrecordlist.html");
		}catch(Exception $e){
			$this->redirect(spUrl("clientintention", "clientlist", array("type"=>$this->type_rs["id"])), $e->getMessage());
		}
	}
	
	public function saleallclientrecordlist(){
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_client = spClass("client");
			$obj_int = spClass("client_intention");
			if(!$this->int_rs = $obj_int->find(array("client_id"=>$client_id)))
				throw new Exception("找不到该意向客户");
			if(!$this->client_rs = $obj_client->getClientById($client_id))
				throw new Exception("找不到该客户");
			$obj_record = spClass("client_record");
			$postdate = $this->spArgs();
			$page = intval(max($postdate['page'], 1));
			$record_rs = $obj_record->join("crm_user")->join("crm_client")->spPager($page, 20)->findAll(array("crm_client_record.client_id"=>$client_id, "crm_client_record.rtype_id"=>1), "crm_client_record.createtime asc", "crm_client_record.*, crm_user.realname as realname_create");
			$this->record_rs = $record_rs;
			$this->pager = $obj_record->spPager()->getPager();
			$this->client_id = $client_id;
			$this->url = spUrl('clientintention', 'saleallclientrecordlist', array("type"=>$this->type_rs["id"], "client_id"=>$client_id));
			$this->display("clients/clientrecordlist.html");
		}catch(Exception $e){
			$this->redirect(spUrl("clientintention", "clientlist", array("type"=>$this->type_rs["id"])), $e->getMessage());
		}
	}
	
	public function odlist(){
		try {
			if(!$id = intval($this->spArgs('id')))
				throw new Exception('请先选择客户，再进行操作！');
			$obj_int = spClass("client_intention");
			if(!$client_rs = $obj_int->find(array("id"=>$id, "create_id"=>$_SESSION["sscrm_user"]["id"])))
				throw new Exception("找不到该蓄水客户");
			$postdata = $this->spArgs();
			$obj_od = spClass("client_intention_overtime");
			$page = intval(max($postdata['page'], 1));
			$this->od_rs = $obj_od->join("crm_user")->spPager($page, 20)->findAll(array("crm_client_intention_overtime.intention_id"=>$id), "crm_client_intention_overtime.createtime desc", "crm_client_intention_overtime.*, crm_user.realname as realname");
			$this->client_rs = $client_rs;
			$this->id = $id;
			$this->pager = $obj_od->spPager()->getPager();
			$this->url = spUrl("clientintention", "odlist", array("id"=>$id));
			$this->backurl = spUrl("clientintention", "clientlist");
		}catch(Exception $e){
			$this->redirect(spUrl("clientintention", "clientlist"), $e->getMessage());
		}
	}
	
	public function allodlist(){
		try {
			if(!$id = intval($this->spArgs('id')))
				throw new Exception('请先选择客户，再进行操作！');
			$obj_int = spClass("client_intention");
			if(!$client_rs = $obj_int->find(array("id"=>$id)))
				throw new Exception("找不到该蓄水客户");
			$postdata = $this->spArgs();
			$obj_od = spClass("client_intention_overtime");
			$page = intval(max($postdata['page'], 1));
			$this->od_rs = $obj_od->join("crm_user")->spPager($page, 20)->findAll(array("crm_client_intention_overtime.intention_id"=>$id), "crm_client_intention_overtime.createtime desc", "crm_client_intention_overtime.*, crm_user.realname as realname");
			$this->client_rs = $client_rs;
			$this->id = $id;
			$this->pager = $obj_od->spPager()->getPager();
			$this->url = spUrl("clientintention", "allodlist", array("id"=>$id));
			$this->backurl = spUrl("clientintention", "allclientlist");
			$this->display("clientintention/odlist.html");
		}catch(Exception $e){
			$this->redirect(spUrl("clientintention", "allclientlist"), $e->getMessage());
		}
	}
	
	public function batchtransfer(){
		$obj_int = spClass("client_intention");
		$obj_type = spClass("client_intention_type");
		$obj_country = spClass('country');
		$obj_record = spClass("client_intention_record");
		$obj_recordsale = spClass("client_record");
		$obj_user = spClass('user');
		$postdata = $this->spArgs();
		$page = intval(max($postdata['page'], 1));
		$condition = "crm_client_intention.isdel = 0 and crm_user.depart_id = {$_SESSION["sscrm_user"]["depart_id"]}";
		if($this->type_rs){
			$condition .= " and crm_client_intention.typeid = {$this->type_rs["id"]}";
			$condition_to_user = "crm_user.depart_id = {$_SESSION["sscrm_user"]["depart_id"]}";
			if($this->type_rs["identity"] != 'none')
				$condition_to_user .= " and find_in_set('{$this->type_rs["identity"]}', crm_user.identity_attr)";
			$this->to_user_rs = $obj_user->getUser_prep($condition_to_user);
		}
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			try {
				$data = array();
				$id = $postdata["id"];
				$to_user_id = intval($postdata["to_user_id"]);
				if(!$id)
					throw new Exception("请先选择蓄水客户再进行该操作");
				if(!$to_user_id)
					throw new Exception("请先选择分配人再进行该操作");
				$id_implode = implode(",", $id);
				$id_str = "'".implode("','", $id)."'";
				$count_id = count($id);
				$condition_id_str = "crm_client_intention.id in($id_str)";
				$total_rs = $obj_int->join("crm_user")->find($condition_to_user." and ".$condition_id_str, null, "count(crm_client_intention.id) as total");
				$total_id = intval($total_rs["total"]);
				if($count_id != $total_id)
					throw new Exception("您所选的客户不符合转移标准，无法进行该操作");
				if(!$client_implode_rs = $obj_int->findAll($condition_id_str, "field(id,{$id_implode})", "id, realname, telphone"))
					throw new Exception("客户汇总错误，如有问题请联系管理员");
				$condition_to_user .= " and crm_user.id = $to_user_id";
				if(!$user_rs = $obj_user->find($condition_to_user, null, "id, realname"))
					throw new Exception("您所选的分配人不符合标准");
				$obj_int->getDb()->beginTrans();
				if(!$obj_int->update($condition_id_str, array("create_id"=>$to_user_id, "receivetime"=>time())))
					throw new Exception("未知错误，客户转移失败");
				if($client_implode_rs){
					$client_group_rs = array();
					foreach($client_implode_rs as $val){
						$client_group_rs[] = $val["id"].":".$val["realname"];
					}
					$client_group_str = implode(",", $client_group_rs);
				}
				spClass('user_log')->save_log(11, "将蓄水客户 (id:姓名)[{$client_group_str}]批量转移给了 {$user_rs["realname"]} [id:{$user_rs["id"]}]", array("intention_id"=>$id_implode));
				$obj_int->getDb()->commitTrans();
				$message = array('msg'=>"蓄水客户批量转移成功", 'result'=>1);
				echo json_encode($message);
				exit();
			}catch(Exception $e){
				$obj_int->getDb()->rollbackTrans();
				echo json_encode(array("msg"=>$e->getMessage(), "result"=>0));
				exit();
			}
		}
		if($postdata['searchkey']){
			$condition .= " and (crm_client_intention.realname like '%{$postdata['searchkey']}%' or crm_client_intention.telphone like '%{$postdata['searchkey']}%')";
			$this->searchkey = $postdata['searchkey'];
		}
		if($postdata["isclient"]){
			switch($postdata["isclient"]){
				case "yes":
					$condition .= " and crm_client_intention.client_id > 0";
					break;
				case "no":
					$condition .= " and crm_client_intention.client_id = 0";
					break;
				default:
		
					break;
			}
			$this->isclient = $postdata["isclient"];
		}
		if($user_id = intval($postdata['user_id'])){
			$condition .= " and crm_client_intention.create_id = $user_id";
			$this->user_id = $user_id;
		}
		if($int_rs = $obj_int->join("crm_client_intention_type")->join("crm_user")->spPager($page, 15)->findAll($condition, "crm_client_intention.createtime desc", "crm_client_intention.*, crm_client_intention_type.name as typename, crm_user.realname as realname_create")){
			foreach($int_rs as $key => $val){
				if($val["exp_country_id"])
					$int_rs[$key]["ctname"] = $obj_country->getname($val["exp_country_id"]);
				$int_rs[$key]["record_count"] = $obj_record->getCountById($val["id"]);
				if($val["client_id"])
					$int_rs[$key]["record_sale_count"] = $obj_recordsale->getCountById($val["client_id"]);
			}
		}
		$this->usetype_rs = $obj_type->getviewlist();
		$this->user_rs = $obj_user->getUser_prep("depart_id = {$_SESSION["sscrm_user"]["depart_id"]}");
		$this->int_rs = $int_rs;
		$this->pager = $obj_int->spPager()->getPager();
		$this->url = spUrl('clientintention', 'batchtransfer', array("type"=>$this->type_rs["id"], "user_id"=>$this->user_id, "searchkey"=>$this->searchkey, "isclient"=>$this->isclient));
	}
}
?>