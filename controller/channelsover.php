<?php
class channelsover extends spController {
	
	public function __construct(){
		parent::__construct();
		try {
			$obj_cpt = spClass("department_competence");
			if(!$_SESSION["sscrm_user"]["user_identity"]["operate"]["enabled"]){
				$obj_cpt->check_login_competence("CHANNEL");
				if(!$_SESSION["sscrm_user"]["isdirector"])
					throw new Exception("您无权查看该页面");
			}
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
		$condition = "crm_channel.ishide = 0 and crm_channel.isoverdate = 1";
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
		$this->url = spUrl('channelsover', 'channellist', array("searchkey"=>$this->searchkey, "level_id"=>$this->level_id, "typeid"=>$this->typeid, "main_id"=>$main_id, "sort"=>$this->sort,"inuse"=>$this->inuse));
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
			$this->controller = "channelsover";
			$this->backurl = spUrl("channelsover", "channellist");
			$this->url = spUrl('channelsover', 'actlist', array("channelid"=>$channelid, "searchkey"=>$this->searchkey));
			$this->display("channeldeparts/actlist.html");
			exit();
		}catch(Exception $e){
			$this->redirect(spUrl("channelsover", "channellist"), $e->getMessage());
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
			$this->url = spUrl('channelsover', 'channelrecordlist', array("channelid"=>$channelid));
			$this->display("channels/channelrecordlist.html");
		}catch(Exception $e){
			$this->redirect(spUrl("channelsover", "channellist"), $e->getMessage());
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
					$message = array('msg'=>"客户重新分配成功", 'result'=>1, "url"=>spUrl("channelsover", "channellist"));
					echo json_encode($message);
					exit();
				}catch(Exception $e){
					$message = array('msg'=>$e->getMessage(), 'result'=>0);
					echo json_encode($message);
					exit();
				}
			}
			$condition = "crm_user.depart_id = $depart_id";
			if($extcondition)
				$condition .= $extcondition;
			$this->user_prep_rs = $obj_user->getUser_prep($condition);
			$this->channel_rs = $channel_rs;
			$this->display("channeldeparts/channelremained.html");
			exit();
		}catch(Exception $e){
			$this->redirect(spUrl("channelsover", "channellist"), $e->getMessage());
		}
	}
	
	private function check_private($channel_rs){
		if($channel_rs["isoverdate"])
			throw new Exception("该客户已为无意向客户，无法进行该操作");
	}
}
?>