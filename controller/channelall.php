<?php
class channelall extends spController {
	
	public function __construct(){
		parent::__construct();
		try {
			$obj_cpt = spClass("department_competence");
			$obj_cpt->check_login_competence("CHANNELALL");
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
		$condition = "crm_channel.ishide = 0 and muser.depart_id = ".$depart_id;
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
				case "plan_desc":
					$sort = "count(crm_client_plan.id) desc, ".$sort;
					$this->sort = $postdate['sort'];
				break;
				default:
	
				break;
			}
		}
		if($channel_rs = $obj_channel->join("crm_channel_level")->join("crm_client_plan", "crm_channel.id = crm_client_plan.channel_id and crm_client_plan.typeid = 3 and crm_client_plan.isfinish = 0 and crm_client_plan.starttime <= UNIX_TIMESTAMP()", "left")->join("crm_channel_type")->join("crm_user as cuser", "cuser.id = crm_channel.create_id")->join("crm_user as fuser", "fuser.id = crm_channel.from_id")->join("crm_user as muser", "muser.id = crm_channel.maintenance_id")->spPager($page, 20)->findAll($condition, $sort, "crm_channel.*, crm_channel_level.name as level_name, count(crm_client_plan.id) as plan_count, cuser.realname as c_realname, fuser.realname as f_realname, muser.realname as m_realname, crm_channel_type.isactive, crm_channel_type.name as typename, IF(crm_channel.issign > 0, 0, datediff(curdate(), IF(crm_channel.sign_enddate > '0000-00-00', crm_channel.sign_enddate, FROM_UNIXTIME(crm_channel.createtime, '%Y-%m-%d')))) as overdate", "crm_channel.id")){
		//if($channel_rs = $obj_channel->join("crm_channel_level")->join("crm_channel_type")->join("crm_user as cuser", "cuser.id = crm_channel.create_id")->join("crm_user as fuser", "fuser.id = crm_channel.from_id")->join("crm_user as muser", "muser.id = crm_channel.maintenance_id")->spPager($page, 20)->findAll($condition, $sort, "crm_channel.*, crm_channel_level.name as level_name, cuser.realname as c_realname, fuser.realname as f_realname, muser.realname as m_realname, crm_channel_type.isactive, crm_channel_type.name as typename, IF(crm_channel.issign > 0, 0, datediff(curdate(), IF(crm_channel.sign_enddate > '0000-00-00', crm_channel.sign_enddate, FROM_UNIXTIME(crm_channel.createtime, '%Y-%m-%d')))) as overdate")){
			foreach($channel_rs as $key => $val){
				if($val["typeid"])
					$channel_rs[$key]["typename"] = $obj_type->getname($val["typeid"]);
				if($val["isactive"])
					$channel_rs[$key]["active_count"] = $obj_active->getCountById($val["id"]);
				if($val["type2id"])
					$channel_rs[$key]["type2name"] = $obj_type->getname($val["type2id"]);
				$channel_rs[$key]["record_count"] = $obj_record->getCountById($val["id"]);
			}
		}
		$this->channel_rs = $channel_rs;
		$this->level_rs = $obj_level->getlist();
		$this->pager = $obj_channel->spPager()->getPager();
		$this->type_rs = $obj_type->getlist();
		$this->searchkey = $postdate['searchkey'];
		$this->user_rs = $user_rs;
		$this->url = spUrl('channelall', 'channellist', array("searchkey"=>$this->searchkey, "level_id"=>$this->level_id, "typeid"=>$this->typeid, "main_id"=>$main_id, "sort"=>$this->sort,"inuse"=>$this->inuse));
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
			$this->url = spUrl('channelall', 'channelrecordlist', array("channelid"=>$channelid));
			$this->display("channels/channelrecordlist.html");
		}catch(Exception $e){
			$this->redirect(spUrl("channelall", "channellist"), $e->getMessage());
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
			$this->url = spUrl('clientall', 'allrecordlist', array("starttime"=>$this->starttime, "endtime"=>$this->endtime, "maintenance_id"=>$this->maintenance_id));
			$this->user_rs = $obj_user->getUser_prep("crm_user.depart_id = 2");
			$this->controller = "channelall";
			$this->display("channeldeparts/allrecordlist.html");
		}catch(Exception $e){
			$this->redirect(spUrl("channelall", "channellist"), $e->getMessage());
		}
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
			$this->controller = "channelall";
			$this->backurl = spUrl("channelall", "channellist");
			$this->url = spUrl('channelall', 'actlist', array("channelid"=>$channelid, "searchkey"=>$this->searchkey));
			$this->display("channeldeparts/actlist.html");
		}catch(Exception $e){
			$this->redirect(spUrl("channelall", "channellist"), $e->getMessage());
		}
	}
	
	public function allactlist(){
		try {
			$url = $_SERVER['HTTP_REFERER'];
			$obj_channel = spClass("channel");
			$obj_active = spClass("channel_active");
			$obj_type = spClass("channel_type");
			$obj_user = spClass('user');
			$postdate = $this->spArgs();
			$page = intval(max($postdate['page'], 1));
			$condition = null;
			if($postdate['searchkey'] != '')
				$condition .= " and (crm_channel.mechanism like '%{$postdate['searchkey']}%' or crm_channel_active.actname like '%{$postdate['searchkey']}%')";
			$sort = "crm_channel_active.acttime desc";
			if($postdate['sort'].'a' !== 'a'){
				switch ($postdate['sort']){
					case "createtime_desc":
						$sort = "crm_channel_active.createtime desc, ".$sort;
						$this->sort = $postdate['sort'];
						break;
					default:
							
					break;
				}
			}
			$this->act_rs = $obj_active->join("crm_channel")->join("crm_user")->spPager($page, 20)->findAll($condition, $sort, "crm_channel_active.*, crm_channel.mechanism, crm_user.realname as realname_create");
			$this->pager = $obj_active->spPager()->getPager();
			$this->url = spUrl('channelall', 'allactlist', array("sort"=>$this->sort));
			$this->controller = "channelall";
			$this->display("channelverifys/allactlist.html");
		}catch(Exception $e){
			$this->redirect($url, $e->getMessage());
		}
	}
	
	public function signlist(){
		try {
			$url = $_SERVER['HTTP_REFERER'];
			$obj_channel = spClass("channel");
			$obj_sign = spClass("channel_sign");
			$obj_type = spClass("channel_type");
			$obj_user = spClass('user');
			$postdate = $this->spArgs();
			if(!$channel_id = intval($this->spArgs("channel_id")))
				throw new Exception("渠道参数丢失");
			$page = intval(max($postdate['page'], 1));
			$channel_rs = $obj_channel
			->join("crm_user as create_user", "create_user.id = crm_channel.create_id")
			->join("crm_user as contact_user", "contact_user.id = crm_channel.contact_id")
			->join("crm_user as from_user", "from_user.id = crm_channel.from_id")
			->join("crm_user as maintenance_user", "maintenance_user.id = crm_channel.maintenance_id")
			->find(array("crm_channel.id"=>$channel_id), null, "crm_channel.*");
			if(!$channel_rs)
				throw new Exception("找不到该渠道，可能已被删除");
			$condition = array("crm_channel_sign.channel_id"=>$channel_id, "crm_channel_sign.isdel"=>0);
			$sort = "crm_channel_sign.signdate desc";
			if($postdate['sort'].'a' !== 'a'){
				switch ($postdate['sort']){
					case "createtime_desc":
						$sort = "crm_channel_sign.createtime desc, ".$sort;
						$this->sort = $postdate['sort'];
						break;
					default:
							
					break;
				}
			}
			$this->sign_rs = $obj_sign->join("crm_user as cuser", "cuser.id = crm_channel_sign.user_id")->spPager($page, 20)->findAll($condition, $sort, "crm_channel_sign.*, cuser.realname");
			$this->pager = $obj_sign->spPager()->getPager();
			$this->channel_rs = $channel_rs;
			$this->channel_id = $channel_id;
			$this->url = spUrl('channelall', 'signlist', array("sort"=>$this->sort));
			$this->backurl = spUrl("channelall", "channellist");
			$this->controller = "channelall";
			$this->display("channelverifys/signlist.html");
		}catch(Exception $e){
			$this->redirect($url, $e->getMessage());
		}
	}
	
	public function allsignlist(){
		try {
			$url = $_SERVER['HTTP_REFERER'];
			$obj_channel = spClass("channel");
			$obj_sign = spClass("channel_sign");
			$obj_type = spClass("channel_type");
			$obj_user = spClass('user');
			$postdate = $this->spArgs();
			$condition = array("crm_channel_sign.isdel"=>0);
			if($sign_id = intval($postdate["sign_id"])){
				$condition["crm_channel.from_id"] = $sign_id;
				$this->sign_id = $sign_id;
			}
			if($main_id = intval($postdate["main_id"])){
				$condition["crm_channel.maintenance_id"] = $main_id;
				$this->main_id = $main_id;
			}
			$sort = "crm_channel_sign.signdate desc";
			if($postdate['sort'].'a' !== 'a'){
				switch ($postdate['sort']){
					case "createtime_desc":
						$sort = "crm_channel_sign.createtime desc, ".$sort;
						$this->sort = $postdate['sort'];
						break;
					default:
							
					break;
				}
			}
			$this->sign_rs = $obj_sign->join("crm_user")->join("crm_channel")->join("crm_user as c_cuser", "c_cuser.id = crm_channel.from_id")->join("crm_user as c_muser", "c_muser.id = crm_channel.maintenance_id")->spPager($page, 20)->findAll($condition, $sort, "crm_channel_sign.*, crm_user.realname, c_cuser.realname as cc_realname, c_muser.realname as cm_realname, crm_channel.mechanism, crm_channel.main_contact, crm_channel.main_tel");
			$this->pager = $obj_sign->spPager()->getPager();
			$this->url = spUrl('channelall', 'allsignlist', array("sign_id"=>$this->sign_id, "main_id"=>$this->main_id, "sort"=>$this->sort));
			$this->backurl = spUrl("channelall", "channellist");
			$this->controller = "channelall";
			$this->showtel = 1;
			$this->user_sign_prep_rs = $obj_user->getUser_prep();
			$this->user_main_prep_rs = $obj_user->getUser_prep("crm_user.depart_id = 2");
			$this->display("channelverifys/allsignlist.html");
		}catch(Exception $e){
			$this->redirect($url, $e->getMessage());
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
			$this->plan_rs = $obj_plan->join("crm_channel")->join("crm_user")->spPager($page, 10)->findAll($condition, "crm_client_plan.createtime desc", "crm_client_plan.*, crm_channel.mechanism, crm_channel.maintenance_id, crm_user.realname as realname_create");
			$this->pager = $obj_plan->spPager()->getPager();
			$this->user_prep_rs = $obj_user->getUser_prep("depart_id = 2");
			$this->url = spUrl('channelall', 'planlist', array("user_id"=>$this->user_id, "status"=>$this->status));
		}catch(Exception $e){
			$this->redirect(spUrl("channelall", "channellist"), $e->getMessage());
		}
	}
}
?>