<?php
class clientadm extends spController {
	
	public function __construct(){
		parent::__construct();
		try {
			if(!$_SESSION["sscrm_user"]["user_identity"]["operate"]["enabled"]){
				if(!$_SESSION["sscrm_user"]["isdirector"] || $_SESSION["sscrm_user"]["depart_id"] != 6)
					throw new Exception("您无权查看该页面");
			}
		}catch(Exception $e){
			$backurl = spUrl("main", "welcome");
			$this->redirect($backurl, $e->getMessage());
			exit();
		}
	}
	

	public function clientlist(){
		$obj_client = spClass('client');
		$obj_origin = spClass('origin');
		$obj_user = spClass('user');
		$obj_country = spClass('country');
		$obj_record = spClass("client_record");
		$obj_channel = spClass("channel");
		$obj_level = spClass("client_level");
		$obj_od = spClass('client_overtime');
		$obj_comactive = spClass('comactive');
		$postdate = $this->spArgs();
		$page = intval(max($postdate['page'], 1));
		$condition = "crm_client.isdel = 0";
		$origin_id = intval($postdate['origin_id']);
		if($origin_id > 0){
			$condition .= " and crm_client.origin_id = $origin_id";
			$this->origin_id = $origin_id;
		}elseif($origin_id == -1){
			$condition .= " and crm_client.channel_id > 0";
			$this->origin_id = $origin_id;
		}
		if($channel_id = intval($postdate['channel_id'])){
			$condition .= " and crm_client.channel_id = $channel_id";
			$this->channel_id = $channel_id;
		}
		if($postdate['statdate']){
			$condition .= " and FROM_UNIXTIME(crm_vip_client.fullpay_arrivaltime,'%Y-%m') = '{$postdate['statdate']}'";
			$this->statdate = $postdate['statdate'];
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
			$condition .= " and (crm_client.realname like '%".$postdate['searchkey']."%' or crm_client.telphone like '%".$postdate['searchkey']."%')";
			$this->searchkey = $postdate['searchkey'];
		}
		if(intval($postdate['channel_muserid'])){
			$condition .= " and crm_channel.maintenance_id = ".intval($postdate['channel_muserid']);
			$this->channel_muserid = $postdate['channel_muserid'];
		}
		if(intval($postdate['user_sales_id'])){
			$condition .= " and crm_client.user_sales_id = ".intval($postdate['user_sales_id']);
			$this->user_sales_id = $postdate['user_sales_id'];
		}
		if($postdate['overtime']){
			switch($postdate['overtime']){
				case "yes":
					$condition .= " and crm_client_overtime.fromtime > 0";
				break;
				case "no":
					$condition .= " and isnull(crm_client_overtime.fromtime)";
				break;
			}
			$this->overtime = $postdate['overtime'];
		}
		if($postdate['ispay']."a" != "a"){
			$condition .= " and crm_client.ispay = ".intval($postdate['ispay']);
			$this->ispay = $postdate['ispay'];
		}
		if($postdate['fword']."a" != "a"){
			$condition .= " and fristPinyin(crm_client.realname) = '".$postdate['fword']."'";
			$this->fword = $postdate['fword'];
		}
		if($level_id = intval($postdate['level_id'])){
			$condition .= " and crm_client.level_id = {$level_id}";
			$this->level_id = $level_id;
		}
		if($comactive_id = intval($postdate['comactive_id'])){
			$condition .= " and crm_client.comactive_id = {$comactive_id}";
			$this->comactive_id = $comactive_id;
		}
		if($user_teler_id = intval($postdate['user_teler_id'])){
			$condition .= " and crm_client.user_teler_id = {$user_teler_id}";
			$this->user_teler_id = $user_teler_id;
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
				case "level_asc":
					$client_sort = "crm_client_level.sort asc, ".$client_sort;
				break;
				case "level_desc":
					$client_sort = "crm_client_level.sort desc, ".$client_sort;
				break;
				case "plan_desc":
					$client_sort = "count(crm_client_plan.id) desc, ".$client_sort;
				break;
				default:
					$client_sort = "crm_client.createtime desc";
				break;
			}
			$this->sort = $sort;
		}
		if($client_rs = $obj_client->join("crm_client_level")->join("crm_user", "crm_user.id = crm_client.user_sales_id")->join("crm_client_overtime", "crm_client_overtime.client_id = crm_client.id and crm_client_overtime.endtime = 0", "left")->join("crm_channel", "crm_channel.id = crm_client.channel_id and crm_client.channel_id > 0", "left")->join("crm_client_plan", "crm_client.id = crm_client_plan.client_id and crm_client_plan.isfinish = 0 and crm_client_plan.starttime <= UNIX_TIMESTAMP()", "left")->spPager($page, 20)->findAll($condition, $client_sort, "crm_client.*, crm_client_level.name as level_name, count(crm_client_plan.id) as plan_count, crm_client_overtime.fromtime, crm_user.realname as realname_sale, crm_user.realname as realname_sale, fristPinyin(crm_client.realname) as py", "crm_client.id")){
			foreach($client_rs as $key => $val){
				if($val["sourcetype"] == 1){
					$client_rs[$key]["oname"] = "渠道来源";
				}elseif($val["sourcetype"] == 2){
					$origin_rs = $obj_origin->findByPk($val["origin_id"]);
					$client_rs[$key]["oname"] = $origin_rs["oname"];
					$client_rs[$key]["record_count"] = $obj_record->getCountById($val["id"]);
				}
				if($val["exp_country_id"]){
					$client_rs[$key]["ctname"] = $obj_country->getname($val["exp_country_id"]);
				}
				$client_rs[$key]["overtime_count"] = $obj_od->getCount($val["id"]);
			}
			$this->client_rs = $client_rs;
		}
		$this->user_tel_rs = $obj_user->getUser_prep("find_in_set('telclient', identity_attr)");
		$this->comactive_rs = $obj_comactive->getlist();
		$this->level_rs = $obj_level->getlist();
		$this->channel_muser_rs = $obj_user->getUser_prep("crm_user.depart_id = 2");
		$this->channel_rs = $obj_channel->getChannel_prep();
		$this->user_rs = $obj_user->getUser_prep("crm_user.depart_id = 3 and find_in_set('getclient', crm_user.identity_attr)");
		$this->pager = $obj_client->spPager()->getPager();
		$this->origin_rs = $obj_origin->get_origin();
		$this->url = spUrl('clientadm', 'clientlist', array("starttime"=>$this->starttime, "endtime"=>$this->endtime, "level_id"=>$this->level_id, "statdate"=>$this->statdate, "channel_id"=>$this->channel_id, "sort"=>$this->sort, "searchkey"=>$this->searchkey, "ispay"=>$this->ispay, "channel_muserid"=>$this->channel_muserid, "user_sales_id"=>$this->user_sales_id, "user_teler_id"=>$this->user_teler_id, "overtime"=>$this->overtime, "comactive_id"=>$this->comactive_id, "isovertime"=>$this->isovertime));
	}
	
	public function viewclient(){
		try {
			if(!$id = intval($this->spArgs('id')))
				throw new Exception('请先选择查看项，再进行操作！');
			$url = $_SERVER['HTTP_REFERER'];
			$obj_client = spClass("client");
			$obj_origin = spClass("origin");
			$obj_user = spClass("user");
			$obj_channel = spClass("channel");
			$obj_trader = spClass('trader');
			if(!$client_rs = $obj_client->getClientById($id))
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
			if(!$this->client_rs = $obj_client->getClientById($client_id))
				throw new Exception("找不到该客户");
			$obj_record = spClass("client_record");
			$postdate = $this->spArgs();
			$page = intval(max($postdate['page'], 1));
			$record_rs = $obj_record->join("crm_user")->join("crm_client")->spPager($page, 20)->findAll(array("crm_client_record.client_id"=>$client_id, "crm_client_record.rtype_id"=>1), "crm_client_record.createtime asc", "crm_client_record.*, crm_user.realname as realname_create");
			$this->record_rs = $record_rs;
			$this->pager = $obj_record->spPager()->getPager();
			$this->client_id = $client_id;
			$this->url = spUrl('clientadm', 'clientrecordlist', array("client_id"=>$client_id));
			$this->display("clients/clientrecordlist.html");
		}catch(Exception $e){
			$this->redirect(spUrl("clientadm", "mycreateclientlist"), $e->getMessage());
		}
	}
	
	//客户计划列表
	public function planlist(){
		try {
			$postdata = $this->spArgs();
			$obj_plan = spClass("client_plan");
			$obj_user = spClass("user");
			$page = intval(max($postdata['page'], 1));
			$condition = "crm_client_plan.typeid = 2";
			if($client_id = intval($this->spArgs("client_id"))){
				$obj_client = spClass("client");
				if(!$this->client_rs = $obj_client->getClientById($client_id))
					throw new Exception("找不到该客户");
				$condition .= " and crm_client_plan.client_id = $client_id";
				$this->client_id = $client_id;
				$this->backurl = spUrl("clientadm", "clientlist");
			}
			if($postdata["user_id"]){
				$condition .= " and crm_client.user_sales_id = {$postdata["user_id"]}";
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
			$this->plan_rs = $obj_plan->join("crm_client")->join("crm_user", "crm_user.id = crm_client_plan.main_id")->spPager($page, 10)->findAll($condition, "crm_client_plan.createtime desc", "crm_client_plan.*, crm_client.realname, crm_client.user_sales_id, crm_user.realname as realname_create");
			$this->pager = $obj_plan->spPager()->getPager();
			$this->user_prep_rs = $obj_user->getUser_prep("depart_id = 3 and find_in_set('getclient', identity_attr)");
			$this->url = spUrl('clientadm', 'planlist', array("user_id"=>$this->user_id, "status"=>$this->status));
		}catch(Exception $e){
			$this->redirect(spUrl("clientadm", "clientlist"), $e->getMessage());
		}
	}
	
	public function odlist(){
		try {
			if(!$id = intval($this->spArgs('id')))
				throw new Exception('请先选择客户，再进行操作！');
			$backurl = spUrl("clientall", "clientlist");
			$obj_client = spClass("client");
			$obj_origin = spClass("origin");
			$obj_user = spClass("user");
			if(!$client_rs = $obj_client->getClientById($id))
				throw new Exception("找不到该客户，可能已被分配");
			$postdata = $this->spArgs();
			$obj_od = spClass("client_overtime");
			$page = intval(max($postdata['page'], 1));
			$this->od_rs = $obj_od->join("crm_user")->spPager($page, 15)->findAll(array("client_id"=>$id), "createtime desc", "crm_client_overtime.*, crm_user.realname as realname");
			$this->client_rs = $client_rs;
			$this->id = $id;
			$this->pager = $obj_od->spPager()->getPager();
			$this->url = spUrl("clientadm", "odlist");
			$this->display("clientsales/odlist.html");
		}catch(Exception $e){
			$this->redirect(spUrl("clientadm", "clientlist"), $e->getMessage());
		}
	}
}
?>