<?php
class channeldeparts extends spController {
	
	public function __construct(){
		parent::__construct();
		try {
			$obj_cpt = spClass("department_competence");
			if(!$_SESSION["sscrm_user"]["user_identity"]["operate"]["enabled"]){
				$obj_cpt->check_login_competence("CHANNEL");
				if(!$_SESSION["sscrm_user"]["isdirector"])
					throw new Exception("您无权查看该页面");
			}
			$this->controller = "channeldeparts";
			$this->clisturl = spUrl($this->controller, "channellist");
		}catch(Exception $e){
			$backurl = spUrl("main", "welcome");
			$this->redirect($backurl, $e->getMessage());
			exit();
		}
	}
	
	public function channellist(){
		$obj_channel = spClass('channel');
		$obj_level = spClass('channel_level');
		$obj_active = spClass('channel_active');
		$obj_type = spClass("channel_type");
		$obj_user = spClass('user');
		$obj_record = spClass('channel_record');
		$depart_id = 2;
		$postdate = $this->spArgs();
		$page = intval(max($postdate['page'], 1));
		$user_rs = $obj_user->getUser_prep("crm_user.depart_id = $depart_id");
		//$condition = "crm_channel.ishide = 0 and muser.depart_id = ".$depart_id;
		$condition = "crm_channel.ishide = 0 and crm_channel.isoverdate = 0";
		if($postdate['searchkey'] != '')
			$condition .= " and (crm_channel.mechanism like '%{$postdate['searchkey']}%' or crm_channel.main_contact like '%{$postdate['searchkey']}%' or crm_channel.main_tel like '%{$postdate['searchkey']}%')";
		if($postdate['typeid']."a" != 'a'){
			$condition .= " and crm_channel.typeid = " . intval($postdate['typeid']);
			$this->typeid = $postdate['typeid'];
		}
		if($postdate["main_id"] == "other"){
			$condition .= " and muser.depart_id <> 2";
			$this->main_id = $postdate["main_id"];
		}elseif($main_id = intval($postdate["main_id"])){
			$condition .= " and crm_channel.maintenance_id = $main_id";
			$this->main_id = $main_id;
		}
		if($level_id = intval($postdate['level_id'])){
			$condition .= " and crm_channel.level_id = {$level_id}";
			$this->level_id = $level_id;
		}
		if($isovertime = intval($postdate['isovertime'])){
			switch ($isovertime){
				case "1":
					$condition .= " and crm_channel.isoverdate = 0";
				break;
				case "2":
					$condition .= " and crm_channel.isoverdate = 1";
				break;
			}
			$this->isovertime = $isovertime;
		}
		$sort = 'crm_channel.createtime desc';
		if($postdate['sort'].'a' !== 'a'){
			switch ($postdate['sort']){
				case "recordtime_desc":
					$sort = "recordtime desc, ".$sort;
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
		if($channel_rs = $obj_channel->join("crm_channel_level")->join("crm_client_plan", "crm_channel.id = crm_client_plan.channel_id and crm_client_plan.typeid = 3 and crm_client_plan.isfinish = 0 and crm_client_plan.starttime <= UNIX_TIMESTAMP()", "left")->join("crm_channel_overtime", "crm_channel_overtime.channel_id = crm_channel.id and crm_channel_overtime.endtime = 0", "left")->join("crm_channel_type")->join("crm_user as cuser", "cuser.id = crm_channel.create_id")->join("crm_user as fuser", "fuser.id = crm_channel.from_id")->join("crm_user as muser", "muser.id = crm_channel.maintenance_id")->spPager($page, 20)->findAll($condition, $sort, "crm_channel.*, crm_channel_level.name as level_name, count(crm_client_plan.id) as plan_count, cuser.realname as c_realname, fuser.realname as f_realname, muser.realname as m_realname, crm_channel_type.isactive, crm_channel_type.name as typename, IF(crm_channel.issign > 0, 0, datediff(curdate(), IF(crm_channel.sign_enddate > '0000-00-00', crm_channel.sign_enddate, FROM_UNIXTIME(crm_channel.createtime, '%Y-%m-%d')))) as overdate, crm_channel_overtime.fromtime", "crm_channel.id")){
		//if($channel_rs = $obj_channel->join("crm_channel_level")->join("crm_channel_type")->join("crm_user as cuser", "cuser.id = crm_channel.create_id")->join("crm_user as fuser", "fuser.id = crm_channel.from_id")->join("crm_user as muser", "muser.id = crm_channel.maintenance_id")->spPager($page, 20)->findAll($condition, $sort, "crm_channel.*, crm_channel_level.name as level_name, cuser.realname as c_realname, fuser.realname as f_realname, muser.realname as m_realname, crm_channel_type.isactive, crm_channel_type.name as typename")){
			foreach($channel_rs as $key => $val){
				if($val["isactive"])
					$channel_rs[$key]["active_count"] = $obj_active->getCountById($val["id"]);
				if($val["type2id"])
					$channel_rs[$key]["type2name"] = $obj_type->getname($val["type2id"]);
				$channel_rs[$key]["record_count"] = $obj_record->getCountById($val["id"]);
				$channel_rs[$key]["client_count"] = $obj_channel->getclientcount($val["id"]);
			}
		}
		$this->level_rs = $obj_level->getlist();
		$this->channel_rs = $channel_rs;
		$this->pager = $obj_channel->spPager()->getPager();
		$this->type_rs = $obj_type->getlist();
		$this->searchkey = $postdate['searchkey'];
		$this->user_rs = $user_rs;
		$this->url = spUrl('channeldeparts', 'channellist', array("searchkey"=>$this->searchkey, "level_id"=>$this->level_id, "isovertime"=>$this->isovertime, "typeid"=>$this->typeid, "main_id"=>$main_id, "sort"=>$this->sort,"inuse"=>$this->inuse));
	}
	
	public function verifychannellist(){
		$obj_channel = spClass('channel');
		$obj_level = spClass('channel_level');
		$obj_active = spClass('channel_active');
		$obj_type = spClass("channel_type");
		$obj_user = spClass('user');
		$depart_id = $_SESSION["sscrm_user"]["depart_id"];
		$postdate = $this->spArgs();
		$page = intval(max($postdate['page'], 1));
		$user_rs = $obj_user->getUser_prep("crm_user.depart_id = $depart_id");
		//$condition = "crm_channel.ishide = 0 and crm_channel.issign = 0 and crm_user.depart_id = ".$_SESSION["sscrm_user"]["depart_id"];
		$condition = "crm_channel.ishide = 0 and crm_channel.issign = 0 and crm_channel.isoverdate = 0";
		if($postdate['searchkey'] != '')
			$condition .= " and (crm_channel.mechanism like '%{$postdate['searchkey']}%' or crm_channel.main_contact like '%{$postdate['searchkey']}%' or crm_channel.main_tel like '%{$postdate['searchkey']}%')";
		if($postdate['typeid']."a" != 'a'){
			$condition .= " and crm_channel.typeid = " . intval($postdate['typeid']);
			$this->typeid = $postdate['typeid'];
		}
		if($main_id = intval($postdate["main_id"])){
			$condition .= " and crm_channel.maintenance_id = $main_id";
			$this->main_id = $main_id;
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
				case "recordtime_desc":
					$sort = "recordtime desc, ".$sort;
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
		if($channel_rs = $obj_channel->join("crm_channel_level")->join("crm_channel_type")->join("crm_user as cuser", "cuser.id = crm_channel.create_id")->join("crm_user as fuser", "fuser.id = crm_channel.from_id")->join("crm_user as muser", "muser.id = crm_channel.maintenance_id")->spPager($page, 20)->findAll($condition, $sort, "crm_channel.*, crm_channel_level.name as level_name, cuser.realname as c_realname, fuser.realname as f_realname, muser.realname as m_realname, crm_channel_type.isactive, crm_channel_type.name as typename, IF(crm_channel.issign > 0, 0, datediff(curdate(), IF(crm_channel.sign_enddate > '0000-00-00', crm_channel.sign_enddate, FROM_UNIXTIME(crm_channel.createtime, '%Y-%m-%d')))) as overdate")){
			foreach($channel_rs as $key => $val){
				if($val["isactive"])
					$channel_rs[$key]["active_count"] = $obj_active->getCountById($val["id"]);
				if($val["type2id"])
					$channel_rs[$key]["type2name"] = $obj_type->getname($val["type2id"]);
			}
		}
		$this->level_rs = $obj_level->getlist();
		$this->channel_rs = $channel_rs;
		$this->pager = $obj_channel->spPager()->getPager();
		$this->type_rs = $obj_type->getlist();
		$this->searchkey = $postdate['searchkey'];
		$this->user_rs = $user_rs;
		$this->url = spUrl('channeldeparts', 'verifychannellist', array("searchkey"=>$this->searchkey, "level_id"=>$this->level_id, "typeid"=>$this->typeid, "main_id"=>$main_id, "sort"=>$this->sort,"inuse"=>$this->inuse));
	}
	
	public function verifychannelmodify(){
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
				$data['mechanism'] = $postdate['mechanism'];
				$data['remark'] = $postdate["remark"];
				if($result = $obj_channel->spValidatorForOPT()->spValidator($data)){
					foreach($result as $item) {
						throw new Exception($item[0]);
						break;
					}
				}
				if(!$obj_channel->checkName($data['mechanism'], $channelid))
					throw new Exception("该机构已经被注册");
				if($data['to_sale_id'] && !$obj_channel->is_tosale_allow($data['to_sale_id']))
					throw new Exception("该用户不允许设置为指定销售");
				if(!$obj_channel->update(array("id"=>$channelid), $data))
					throw new Exception("未知错误，更新失败");
				spClass('user_log')->save_log(2, "更新了渠道 ".$data['mechanism']." [id:$channelid]");
				$url = spUrl("channeldeparts", "verifychannellist");
				$message = array('msg'=>'渠道机构更新成功','result'=>1, 'url'=>$url);
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
		$this->muser_rs = $user->getUser_prep("crm_user.depart_id = 2");
		$this->contact_rs = $user->getUser_prep("find_in_set('telclient', identity_attr)");
		$this->type_rs = $obj_type->getlist();
		$this->channel_rs = $channel_rs;
		$this->validator = $obj_channel->getValidatorForModifyJS($channelid);
		$this->saveurl = spUrl("channeldeparts", "verifychannelmodify");
		$this->display("channels/createchannel.html");
	}

	public function actlist(){
		try{
			$obj_channel = spClass("channel");
			$channelid = intval($this->spArgs("channelid"));
			$channel_rs = $obj_channel->get_act_allchannel($channelid);
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
			$this->controller = "channeldeparts";
			$this->backurl = spUrl("channeldeparts", "channellist");
			$this->url = spUrl('channeldeparts', 'actlist', array("channelid"=>$channelid, "searchkey"=>$this->searchkey));
		}catch(Exception $e){
			$this->redirect(spUrl("channeldeparts", "channellist"), $e->getMessage());
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
		$condition = "crm_client.channel_id > 0 and crm_client.isdel = 0";
		if($channel_id = intval($postdate['channel_id'])){
			$condition .= " and crm_client.channel_id = $channel_id";
			$this->channel_id = $channel_id;
			if($act_rs = spClass('channel_active')->get_actives_by_channelid($channel_id))
				$this->act_rs = $act_rs;
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
		if(intval($postdate['channel_muserid'])){
			$condition .= " and crm_channel.maintenance_id = ".intval($postdate['channel_muserid']);
			$this->channel_muserid = $postdate['channel_muserid'];
		}
		if($postdate['ispay']."a" != "a"){
			$condition .= " and crm_client.ispay = ".intval($postdate['ispay']);
			$this->ispay = $postdate['ispay'];
		}
		if($level_id = intval($postdate['level_id'])){
			$condition .= " and crm_client.level_id = {$level_id}";
			$this->level_id = $level_id;
		}
		if($isovertime = intval($postdate['isovertime'])){
			switch ($isovertime){
				case "1":
					$condition .= " and crm_client.isoverdate = 0";
					break;
				case "2":
					$condition .= " and crm_client.isoverdate = 1";
					break;
			}
			$this->isovertime = $isovertime;
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
		$this->title = "市场渠道客户管理";
		$this->controller = "channeldeparts";
		$this->action = "clientlist";
		$this->allclient = 1;
		$this->level_rs = $obj_level->getlist();
		$this->user_rs = $obj_user->getUser_prep("crm_user.depart_id = 2");
		$this->channel_rs = $obj_channel->getAllChannel_prep();
		$this->pager = $obj_client->spPager()->getPager();
		$this->url = spUrl($this->controller, $this->action, array("level_id"=>$this->level_id, "starttime"=>$this->starttime, "endtime"=>$this->endtime, "statdate"=>$this->statdate, "sort"=>$this->sort, "searchkey"=>$this->searchkey, "ispay"=>$this->ispay, "channel_id"=>$this->channel_id, "channel_muserid"=>$this->channel_muserid, "channelact_id"=>$this->channelact_id, "isovertime"=>$this->isovertime));
		$this->display("channels/clientlist.html");
	}
	
	public function viewclient(){
		try {
			die("x");
			if(!$id = intval($this->spArgs('id')))
				throw new Exception('请先选择查看项，再进行操作！');
			$url = $_SERVER['HTTP_REFERER'];
			$obj_client = spClass("client");
			$obj_origin = spClass("origin");
			$obj_user = spClass("user");
			$obj_channel = spClass("channel");
			$obj_trader = spClass('trader');
			if(!$client_rs = $obj_client->join("crm_channel")->find(array("crm_client.id"=>$id), null, "crm_client.*"))
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
			if(!$client_rs = $obj_client->join("crm_channel")->find(array("crm_client.id"=>$client_id), null, "crm_client.*"))
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
			$this->url = spUrl('clientall', 'clientrecordlist', array("client_id"=>$client_id));
			$this->display("clients/clientrecordlist.html");
		}catch(Exception $e){
			$this->redirect(spUrl("clients", "mycreateclientlist"), $e->getMessage());
		}
	}
	
	public function channelrecordlist(){
		try {
			$obj_channel = spClass("channel");
			$channelid = intval($this->spArgs("channelid"));
			$condition = "crm_channel.ishide = 0 and crm_user.depart_id = 2";
			$condition .= " and crm_channel.id = $channelid";
			if(!$channel_rs = $obj_channel->join("crm_user", "crm_user.id = crm_channel.maintenance_id")->find($condition))
				throw new Exception("您无权查看该页");
			$obj_record = spClass("channel_record");
			$postdate = $this->spArgs();
			$page = intval(max($postdate['page'], 1));
			$record_rs = $obj_record->join("crm_user")->join("crm_channel")->spPager($page, 20)->findAll(array("crm_channel_record.channel_id"=>$channelid), "crm_channel_record.createtime asc", "crm_channel_record.*, crm_user.realname as realname_create");
			$this->record_rs = $record_rs;
			$this->channel_rs = $channel_rs;
			$this->pager = $obj_record->spPager()->getPager();
			$this->channelid = $channelid;
			$this->url = spUrl('channeldeparts', 'channelrecordlist', array("channelid"=>$channelid));
			$this->display("channels/channelrecordlist.html");
		}catch(Exception $e){
			$this->redirect(spUrl("channeldeparts", "channellist"), $e->getMessage());
		}
	}

	public function allrecordlist(){
		try {
			$obj_user = spClass('user');
			$obj_record = spClass("channel_record");
			$postdate = $this->spArgs();
			$page = intval(max($postdate['page'], 1));
			$condition = "1";
			if($postdate['starttime'] != ''){
				$condition .= " and crm_channel_record.acttime >= ".strtotime($postdate['starttime']);
				$this->starttime = $postdate['starttime'];
			}
			if($postdate['endtime'] != ''){
				$condition .= " and crm_channel_record.acttime <= ".strtotime($postdate['endtime']);
				$this->endtime = $postdate['endtime'];
			}
			if(intval($postdate['maintenance_id'])){
				$condition .= " and crm_channel.maintenance_id = ".intval($postdate['maintenance_id']);
				$this->maintenance_id = $postdate['maintenance_id'];
			}
			$record_rs = $obj_record->join("crm_channel")->join("crm_user")->spPager($page, 20)->findAll($condition, "crm_channel_record.acttime desc", "crm_channel_record.*, crm_channel.mechanism, crm_user.realname");
			$this->record_rs = $record_rs;
			$this->pager = $obj_record->spPager()->getPager();
			$this->url = spUrl('channeldeparts', 'allrecordlist', array("starttime"=>$this->starttime, "endtime"=>$this->endtime, "maintenance_id"=>$this->maintenance_id));
			$this->user_rs = $obj_user->getUser_prep("crm_user.depart_id = 2");
			$this->controller = "channeldeparts";
		}catch(Exception $e){
			$this->redirect(spUrl("channeldeparts", "channellist"), $e->getMessage());
		}
	}
	
	//市场总监可重新分配部门所有渠道的维护人
	public function channelremained(){
		try {
			$obj_channel = spClass("channel");
			$channelid = intval($this->spArgs("channelid"));
			$condition = "crm_channel.ishide = 0";
			$condition .= " and crm_channel.id = $channelid";
			$obj_user = spClass("user");
			$obj_department = spClass('department');
			$obj_sep = spClass('department_sep');
			$depart_id = 2;
			try {
				$depart_rs = $obj_department->getinfoById($depart_id);
				$extcondition = "";
				if($depart_rs["is_sep"]){
					if(!$sep_id = $_SESSION["sscrm_user"]["depart_sep_id"])
						throw new Exception("您尚未分配到".$depart_rs["dname"]."下的所在组，请尝试重新登录");
					if(!$sep_rs = $obj_sep->find(array("depart_id"=>$depart_id, "id"=>$sep_id)))
						throw new Exception("您所在的组不正确，请尝试重新登录");
					$this->sep_name = "(".$sep_rs["sep_name"].")";
					$extcondition = " and crm_user.depart_sep_id = $sep_id";
				}
			}catch(Exception $e){
				$this->redirect(spUrl("main", "welcome"), $e->getMessage());
				exit();
			}
			if(!$channel_rs = $obj_channel->join("crm_user", "crm_user.id = crm_channel.maintenance_id")->find($condition, null, "crm_channel.*, crm_user.realname as realname_main"))
				throw new Exception("您无权查看该页");
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try {
					$data["transfer"] = intval($this->spArgs("transfer"));
					if(!$data["transfer"])
						throw new Exception("请选择被分配人");
					$obj_channel->resend($channel_rs, $data["transfer"]);
					$message = array('msg'=>"客户重新分配成功", 'result'=>1, "url"=>spUrl("channeldeparts", "channellist"));
					echo json_encode($message);
					exit();
				}catch(Exception $e){
					$message = array('msg'=>$e->getMessage(), 'result'=>0);
					echo json_encode($message);
					exit();
				}
			}
			$condition = "crm_user.depart_id = $depart_id and crm_user.id <> {$channel_rs["maintenance_id"]}";
			if($extcondition)
				$condition .= $extcondition;
			$this->user_prep_rs = $obj_user->getUser_prep($condition);
			$this->channel_rs = $channel_rs;
		}catch(Exception $e){
			$this->redirect(spUrl("channeldeparts", "channellist"), $e->getMessage());
		}
	}
	
	//渠道计划列表
	public function planlist(){
		try {
			$postdata = $this->spArgs();
			$obj_plan = spClass("client_plan");
			$obj_user = spClass("user");
			$page = intval(max($postdata['page'], 1));
			$condition = "crm_client_plan.typeid = 3";
			if($channel_id = intval($this->spArgs("channel_id"))){
				$obj_channel = spClass("channel");
				if(!$this->channel_rs = $obj_channel->getinfoById($channel_id))
					throw new Exception("找不到该客户");
				$condition .= " and crm_client_plan.channel_id = $channel_id";
				$this->channel_id = $channel_id;
				$this->backurl = spUrl("channeldeparts", "channellist");
			}
			if($postdata["user_id"]){
				$condition .= " and crm_channel.maintenance_id = {$postdata["user_id"]}";
				$this->user_id = $postdata["user_id"];
			}
			if($postdata["status"]){
				switch ($postdata["status"]){
					case "going":
						$condition .= " and crm_channel_plan.isfinish = 0 and crm_channel_plan.starttime <= ".time()." and crm_channel_plan.endtime >=" . time();
						$this->status = $postdata["status"];
						break;
					case "overdate":
						$condition .= " and crm_channel_plan.isfinish = 0 and crm_channel_plan.endtime <= ".time();
						$this->status = $postdata["status"];
						break;
					case "waiting":
						$condition .= " and crm_channel_plan.isfinish = 0 and crm_channel_plan.starttime >= ".time();
						$this->status = $postdata["status"];
						break;
					case "finish":
						$condition .= " and crm_client_plan.isfinish = 1";
						$this->status = $postdata["status"];
						break;
				}
			}
			$this->plan_rs = $obj_plan->join("crm_channel")->join("crm_user")->spPager($page, 10)->findAll($condition, "crm_client_plan.createtime desc", "crm_client_plan.*, crm_channel.mechanism, crm_channel.main_tel, crm_channel.maintenance_id, crm_user.realname as realname_create");
			$this->pager = $obj_plan->spPager()->getPager();
			$this->user_prep_rs = $obj_user->getUser_prep("depart_id = 2");
			$this->url = spUrl('channeldeparts', 'planlist', array("user_id"=>$this->user_id, "status"=>$this->status));
		}catch(Exception $e){
			$this->redirect(spUrl("channeldeparts", "channellist"), $e->getMessage());
		}
	}
	
	//全部过期记录
	public function allodlist(){
		try {
			$obj_user = spClass("user");
			$postdata = $this->spArgs();
			$obj_od = spClass("channel_overtime");
			$page = intval(max($postdata['page'], 1));
			$condition = "crm_channel.ishide = 0";
			if($postdata["endtime"]){
				switch($postdata["endtime"]){
					case "ing":
						$condition .= " and crm_channel_overtime.endtime = 0";
						break;
					case "end":
						$condition .= " and crm_channel_overtime.endtime > 0";
						break;
				}
				$this->endtime = $postdata["endtime"];
			}
			if($postdata["main_id"]){
				$condition .= " and crm_channel.maintenance_id = {$postdata["main_id"]}";
				$this->main_id = $postdata["main_id"];
			}
			$this->od_rs = $obj_od->join("crm_user")->join("crm_channel")->spPager($page, 15)->findAll($condition, "crm_channel_overtime.createtime desc", "crm_channel_overtime.*, crm_channel.mechanism, crm_channel.main_tel, crm_user.realname as realname");
			$this->pager = $obj_od->spPager()->getPager();
			$this->url = spUrl("channeldeparts", "allodlist", array("endtime"=>$this->endtime, "main_id"=>$this->main_id));
			$this->controller = "channeldeparts";
			$this->istel = 1;
			$this->user_prep_rs = $obj_user->getUser_prep("depart_id = 2");
			$this->display("channeldeparts/allodlist.html");
		}catch(Exception $e){
			$this->redirect(spUrl("channelall", "channellist"), $e->getMessage());
		}
	}
	
	//将客户强制转为无意向中
	public function channeloverdate(){
		try {
			if(!$id = intval($this->spArgs('channelid')))
				throw new Exception('请先选择渠道，再进行操作！');
			$backurl = $_SERVER['HTTP_REFERER'];
			//$backurl = spUrl("clientdepart", "clientlist");
			$obj_channel = spClass("channel");
			$obj_origin = spClass("origin");
			$obj_user = spClass("user");
			$obj_department = spClass('department');
			$obj_sep = spClass('department_sep');
			$depart_id = 2;
			try {
				$depart_rs = $obj_department->getinfoById($depart_id);
				$extcondition = "";
				if($depart_rs["is_sep"]){
					if(!$sep_id = $_SESSION["sscrm_user"]["depart_sep_id"])
						throw new Exception("您尚未分配到".$depart_rs["dname"]."下的所在组，请尝试重新登录");
					if(!$sep_rs = $obj_sep->find(array("depart_id"=>$depart_id, "id"=>$sep_id)))
						throw new Exception("您所在的组不正确，请尝试重新登录");
					$this->sep_name = "(".$sep_rs["sep_name"].")";
					$extcondition = " and crm_user.depart_sep_id = $sep_id";
				}
			}catch(Exception $e){
				$this->redirect(spUrl("main", "welcome"), $e->getMessage());
				exit();
			}
			if(!$channel_rs = $obj_channel->getChannelById($id))
				throw new Exception("找不到该渠道，可能已被删除");
			if($channel_rs["issign"])
				throw new Exception("该渠道为已签协议状态，无法进行该操作");
			$this->check_private($channel_rs);
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try {
					$postdata = $this->spArgs();
					$data = array();
					$data["isoverdate"] = 1;
					$data["overdatetime"] = time();
					$data["overdatereason"] = "强制，原因：".$postdata["reason"];
					$tourl = $postdata["backurl"] ? $postdata["backurl"] : spUrl("channeldeparts", "channellist");
					if(!$postdata["reason"])
						throw new Exception("请填写原因");
					$obj_channel->update(array("id"=>$id), $data);
					spClass('user_log')->save_log(2, "将渠道 ".$channel_rs["mechanism"]." [id:".$id."] 强制转为无意向", array("channel_id"=>$id));
					$message = array('msg'=>"该渠道已被强制转为无意向", 'result'=>1, "url"=>$tourl);
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
			$this->channel_rs = $channel_rs;
		}catch(Exception $e){
			$this->redirect(spUrl("channeldeparts", "channellist"), $e->getMessage());
		}
	}
	
	//批量转移
	public function batchtransfer(){
		$obj_channel = spClass('channel');
		$obj_level = spClass('channel_level');
		$obj_active = spClass('channel_active');
		$obj_type = spClass("channel_type");
		$obj_user = spClass('user');
		$obj_record = spClass('channel_record');
		$depart_id = 2;
		$postdate = $this->spArgs();
		$page = intval(max($postdate['page'], 1));
		//$condition = "crm_channel.ishide = 0 and muser.depart_id = ".$depart_id;
		$user_condition = "crm_user.depart_id = $depart_id";
		$user_rs = $obj_user->getUser_prep($user_condition);
		$condition = "crm_channel.ishide = 0 and crm_channel.isoverdate = 0";
		$postdata = $this->spArgs();
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			try {
				$data = array();
				$id = $postdata["id"];
				$main_id = intval($postdata["maintenance_id"]);
				if(!$id)
					throw new Exception("请先选择渠道再进行该操作");
				if(!$main_id)
					throw new Exception("请先选择分配人再进行该操作");
				$id_implode = implode(",", $id);
				$id_str = "'".implode("','", $id)."'";
				$count_id = count($id);
				$condition .= " and crm_channel.id in($id_str)";
				$total_rs = $obj_channel->find($condition, null, "count(id) as total");
				$total_id = intval($total_rs["total"]);
				if($count_id != $total_id)
					throw new Exception("您所选的渠道不符合转移标准，无法进行该操作");
				if(!$channel_implode_rs = $obj_channel->findAll($condition, "field(id,{$id_implode})", "id, mechanism, main_tel"))
					throw new Exception("渠道汇总错误，如有问题请联系管理员");
				$user_condition .= " and crm_user.id = $main_id";
				if(!$user_rs = $obj_user->find($user_condition, null, "id, realname"))
					throw new Exception("您所选的分配人不符合标准");
				$obj_channel->getDb()->beginTrans();
				if(!$obj_channel->update($condition, array("maintenance_id"=>$main_id)))
					throw new Exception("未知错误，渠道转移失败");
				if($channel_implode_rs){
					$channel_group_rs = array();
					foreach($channel_implode_rs as $val){
						$channel_group_rs[] = $val["id"].":".$val["mechanism"];
					}
					$channel_group_str = implode(",", $channel_group_rs);
				}
				spClass('user_log')->save_log(2, "将渠道 (id:渠道名)[{$channel_group_str}]批量转移给了 {$user_rs["realname"]} [id:{$user_rs["id"]}]", array("channel_id"=>$id_implode));
				$obj_channel->getDb()->commitTrans();
				$message = array('msg'=>"渠道批量转移成功", 'result'=>1);
				echo json_encode($message);
				exit();
			}catch(Exception $e){
				$obj_channel->getDb()->rollbackTrans();
				echo json_encode(array("msg"=>$e->getMessage(), "result"=>0));
				exit();
			}
		}
		if($postdate['searchkey'] != '')
			$condition .= " and (crm_channel.mechanism like '%{$postdate['searchkey']}%' or crm_channel.main_contact like '%{$postdate['searchkey']}%' or crm_channel.main_tel like '%{$postdate['searchkey']}%')";
		if($postdate['typeid']."a" != 'a'){
			$condition .= " and crm_channel.typeid = " . intval($postdate['typeid']);
			$this->typeid = $postdate['typeid'];
		}
		if($postdate["main_id"] == "other"){
			$condition .= " and muser.depart_id <> 2";
			$this->main_id = $postdate["main_id"];
		}elseif($main_id = intval($postdate["main_id"])){
			$condition .= " and crm_channel.maintenance_id = $main_id";
			$this->main_id = $main_id;
		}
		if($level_id = intval($postdate['level_id'])){
			$condition .= " and crm_channel.level_id = {$level_id}";
			$this->level_id = $level_id;
		}
		$sort = 'crm_channel.createtime desc';
		if($postdate['sort'].'a' !== 'a'){
			switch ($postdate['sort']){
				case "recordtime_desc":
					$sort = "recordtime desc, ".$sort;
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
		if($channel_rs = $obj_channel->join("crm_channel_level")->join("crm_client_plan", "crm_channel.id = crm_client_plan.channel_id and crm_client_plan.typeid = 3 and crm_client_plan.isfinish = 0 and crm_client_plan.starttime <= UNIX_TIMESTAMP()", "left")->join("crm_channel_overtime", "crm_channel_overtime.channel_id = crm_channel.id and crm_channel_overtime.endtime = 0", "left")->join("crm_channel_type")->join("crm_user as cuser", "cuser.id = crm_channel.create_id")->join("crm_user as fuser", "fuser.id = crm_channel.from_id")->join("crm_user as muser", "muser.id = crm_channel.maintenance_id")->spPager($page, 20)->findAll($condition, $sort, "crm_channel.*, crm_channel_level.name as level_name, count(crm_client_plan.id) as plan_count, cuser.realname as c_realname, fuser.realname as f_realname, muser.realname as m_realname, crm_channel_type.isactive, crm_channel_type.name as typename, IF(crm_channel.issign > 0, 0, datediff(curdate(), IF(crm_channel.sign_enddate > '0000-00-00', crm_channel.sign_enddate, FROM_UNIXTIME(crm_channel.createtime, '%Y-%m-%d')))) as overdate, crm_channel_overtime.fromtime", "crm_channel.id")){
		//if($channel_rs = $obj_channel->join("crm_channel_level")->join("crm_channel_type")->join("crm_user as cuser", "cuser.id = crm_channel.create_id")->join("crm_user as fuser", "fuser.id = crm_channel.from_id")->join("crm_user as muser", "muser.id = crm_channel.maintenance_id")->spPager($page, 20)->findAll($condition, $sort, "crm_channel.*, crm_channel_level.name as level_name, cuser.realname as c_realname, fuser.realname as f_realname, muser.realname as m_realname, crm_channel_type.isactive, crm_channel_type.name as typename")){
			foreach($channel_rs as $key => $val){
				if($val["isactive"])
					$channel_rs[$key]["active_count"] = $obj_active->getCountById($val["id"]);
				if($val["type2id"])
					$channel_rs[$key]["type2name"] = $obj_type->getname($val["type2id"]);
				$channel_rs[$key]["record_count"] = $obj_record->getCountById($val["id"]);
				$channel_rs[$key]["client_count"] = $obj_channel->getclientcount($val["id"]);
			}
		}
		$this->level_rs = $obj_level->getlist();
		$this->channel_rs = $channel_rs;
		$this->pager = $obj_channel->spPager()->getPager();
		$this->type_rs = $obj_type->getlist();
		$this->searchkey = $postdate['searchkey'];
		$this->user_rs = $user_rs;
		$this->url = spUrl('channeldeparts', 'batchtransfer', array("searchkey"=>$this->searchkey, "level_id"=>$this->level_id, "typeid"=>$this->typeid, "main_id"=>$main_id, "sort"=>$this->sort,"inuse"=>$this->inuse));
	}
	
	//批量无意向
	public function batchoverdate(){
		$obj_channel = spClass('channel');
		$obj_level = spClass('channel_level');
		$obj_active = spClass('channel_active');
		$obj_type = spClass("channel_type");
		$obj_user = spClass('user');
		$obj_record = spClass('channel_record');
		$depart_id = 2;
		$postdate = $this->spArgs();
		$page = intval(max($postdate['page'], 1));
		//$condition = "crm_channel.ishide = 0 and muser.depart_id = ".$depart_id;
		$condition = "crm_channel.ishide = 0 and crm_channel.isoverdate = 0 and crm_channel.issign = 0";
		$postdata = $this->spArgs();
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			try {
				$data = array();
				$id = $postdata["id"];
				if(!$id)
					throw new Exception("请先选择渠道再进行该操作");
				$id_implode = implode(",", $id);
				$id_str = "'".implode("','", $id)."'";
				$count_id = count($id);
				$condition .= " and crm_channel.id in($id_str)";
				$total_rs = $obj_channel->find($condition, null, "count(id) as total");
				$total_id = intval($total_rs["total"]);
				if($count_id != $total_id)
					throw new Exception("您所选的渠道不符合批量无意向标准，无法进行该操作");
				if(!$channel_implode_rs = $obj_channel->findAll($condition, "field(id,{$id_implode})", "id, mechanism, main_tel"))
					throw new Exception("渠道汇总错误，如有问题请联系管理员");
				$obj_channel->getDb()->beginTrans();
				$data = array();
				$data["isoverdate"] = 1;
				$data["overdatetime"] = time();
				$data["overdatereason"] = "强制批量，原因：".$postdata["reason"];
				if(!$postdata["reason"])
					throw new Exception("请填写原因");
				if(!$obj_channel->update($condition, $data))
					throw new Exception("未知错误，渠道批量无意向失败");
				if($channel_implode_rs){
					$channel_group_rs = array();
					foreach($channel_implode_rs as $val){
						$channel_group_rs[] = $val["id"].":".$val["mechanism"];
					}
					$channel_group_str = implode(",", $channel_group_rs);
				}
				spClass('user_log')->save_log(2, "将渠道 (id:渠道名)[{$channel_group_str}]批量转为无意向渠道", array("channel_id"=>$id_implode));
				$obj_channel->getDb()->commitTrans();
				$message = array('msg'=>"渠道批量无意向操作成功", 'result'=>1);
				echo json_encode($message);
				exit();
			}catch(Exception $e){
				$obj_channel->getDb()->rollbackTrans();
				echo json_encode(array("msg"=>$e->getMessage(), "result"=>0));
				exit();
			}
		}
		if($postdate['searchkey'] != '')
			$condition .= " and (crm_channel.mechanism like '%{$postdate['searchkey']}%' or crm_channel.main_contact like '%{$postdate['searchkey']}%' or crm_channel.main_tel like '%{$postdate['searchkey']}%')";
		if($postdate['typeid']."a" != 'a'){
			$condition .= " and crm_channel.typeid = " . intval($postdate['typeid']);
			$this->typeid = $postdate['typeid'];
		}
		if($postdate["main_id"] == "other"){
			$condition .= " and muser.depart_id <> 2";
			$this->main_id = $postdate["main_id"];
		}elseif($main_id = intval($postdate["main_id"])){
			$condition .= " and crm_channel.maintenance_id = $main_id";
			$this->main_id = $main_id;
		}
		if($level_id = intval($postdate['level_id'])){
			$condition .= " and crm_channel.level_id = {$level_id}";
			$this->level_id = $level_id;
		}
		$sort = 'crm_channel.createtime desc';
		if($postdate['sort'].'a' !== 'a'){
			switch ($postdate['sort']){
				case "recordtime_desc":
					$sort = "recordtime desc, ".$sort;
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
		if($channel_rs = $obj_channel->join("crm_channel_level")->join("crm_client_plan", "crm_channel.id = crm_client_plan.channel_id and crm_client_plan.typeid = 3 and crm_client_plan.isfinish = 0 and crm_client_plan.starttime <= UNIX_TIMESTAMP()", "left")->join("crm_channel_overtime", "crm_channel_overtime.channel_id = crm_channel.id and crm_channel_overtime.endtime = 0", "left")->join("crm_channel_type")->join("crm_user as cuser", "cuser.id = crm_channel.create_id")->join("crm_user as fuser", "fuser.id = crm_channel.from_id")->join("crm_user as muser", "muser.id = crm_channel.maintenance_id")->spPager($page, 20)->findAll($condition, $sort, "crm_channel.*, crm_channel_level.name as level_name, count(crm_client_plan.id) as plan_count, cuser.realname as c_realname, fuser.realname as f_realname, muser.realname as m_realname, crm_channel_type.isactive, crm_channel_type.name as typename, IF(crm_channel.issign > 0, 0, datediff(curdate(), IF(crm_channel.sign_enddate > '0000-00-00', crm_channel.sign_enddate, FROM_UNIXTIME(crm_channel.createtime, '%Y-%m-%d')))) as overdate, crm_channel_overtime.fromtime", "crm_channel.id")){
			//if($channel_rs = $obj_channel->join("crm_channel_level")->join("crm_channel_type")->join("crm_user as cuser", "cuser.id = crm_channel.create_id")->join("crm_user as fuser", "fuser.id = crm_channel.from_id")->join("crm_user as muser", "muser.id = crm_channel.maintenance_id")->spPager($page, 20)->findAll($condition, $sort, "crm_channel.*, crm_channel_level.name as level_name, cuser.realname as c_realname, fuser.realname as f_realname, muser.realname as m_realname, crm_channel_type.isactive, crm_channel_type.name as typename")){
			foreach($channel_rs as $key => $val){
				if($val["isactive"])
					$channel_rs[$key]["active_count"] = $obj_active->getCountById($val["id"]);
				if($val["type2id"])
					$channel_rs[$key]["type2name"] = $obj_type->getname($val["type2id"]);
				$channel_rs[$key]["record_count"] = $obj_record->getCountById($val["id"]);
				$channel_rs[$key]["client_count"] = $obj_channel->getclientcount($val["id"]);
			}
		}
		$this->level_rs = $obj_level->getlist();
		$this->channel_rs = $channel_rs;
		$this->pager = $obj_channel->spPager()->getPager();
		$this->type_rs = $obj_type->getlist();
		$this->searchkey = $postdate['searchkey'];
		$this->url = spUrl('channeldeparts', 'batchoverdate', array("searchkey"=>$this->searchkey, "level_id"=>$this->level_id, "typeid"=>$this->typeid, "main_id"=>$main_id, "sort"=>$this->sort,"inuse"=>$this->inuse));
	}
	
	private function check_private($channel_rs){
		if($channel_rs["isoverdate"])
			throw new Exception("该客户已为无意向客户，无法进行该操作");
	}
}
?>