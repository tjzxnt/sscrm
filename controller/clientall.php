<?php
class clientall extends spController {
	
	public function __construct(){
		parent::__construct();
		try {
			$obj_cpt = spClass("department_competence");
			if(!$_SESSION["sscrm_user"]["user_identity"]["operate"]["enabled"])
				$obj_cpt->check_login_competence("CLIENTALL");
			$this->controller = "clientall";
			$this->clisturl = spUrl($this->controller, "clientlist");
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
		$obj_int = spClass("client_intention");
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
		if($client_rs = $obj_client->join("crm_client_seehouse")->join("crm_client_level")->join("crm_user", "crm_user.id = crm_client.user_sales_id")->join("crm_client_overtime", "crm_client_overtime.client_id = crm_client.id and crm_client_overtime.endtime = 0", "left")->join("crm_channel", "crm_channel.id = crm_client.channel_id and crm_client.channel_id > 0", "left")->join("crm_client_plan", "crm_client.id = crm_client_plan.client_id and crm_client_plan.isfinish = 0 and crm_client_plan.starttime <= UNIX_TIMESTAMP()", "left")->spPager($page, 20)->findAll($condition, $client_sort, "crm_client.*, crm_client_level.name as level_name, crm_client_seehouse.see_status, count(crm_client_plan.id) as plan_count, crm_client_overtime.fromtime, crm_user.realname as realname_sale, crm_user.realname as realname_sale, fristPinyin(crm_client.realname) as py", "crm_client.id")){
		//if($client_rs = $obj_client->join("crm_client_level")->join("crm_user", "crm_user.id = crm_client.user_sales_id")->join("crm_client_overtime", "crm_client_overtime.client_id = crm_client.id and crm_client_overtime.endtime = 0", "left")->join("crm_channel", "crm_channel.id = crm_client.channel_id and crm_client.channel_id > 0", "left")->spPager($page, 20)->findAll($condition, $client_sort, "crm_client.*, crm_client_level.name as level_name, crm_client_overtime.fromtime, crm_user.realname as realname_sale, crm_user.realname as realname_sale, fristPinyin(crm_client.realname) as py")){
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
				if($int_rs = $obj_int->find(array("client_id"=>$val["id"]), null, "createtime")){
					$client_rs[$key]["int_createtime"] = $int_rs["createtime"];
				}
			}
			$this->client_rs = $client_rs;
		}
		$this->user_tel_rs = $obj_user->getUser_prep("find_in_set('telclient', identity_attr)");
		$this->comactive_rs = $obj_comactive->getlist();
		$this->level_rs = $obj_level->getlist();
		$this->channel_muser_rs = $obj_user->getUser_prep("crm_user.depart_id = 2");
		$this->channel_rs = $obj_channel->getAllChannel_prep();
		$this->user_rs = $obj_user->getUser_prep("crm_user.depart_id = 3 and find_in_set('getclient', crm_user.identity_attr)");
		$this->pager = $obj_client->spPager()->getPager();
		$this->origin_rs = $obj_origin->get_origin();
		$this->url = spUrl('clientall', 'clientlist', array("starttime"=>$this->starttime, "endtime"=>$this->endtime, "level_id"=>$this->level_id, "statdate"=>$this->statdate, "channel_id"=>$this->channel_id, "sort"=>$this->sort, "searchkey"=>$this->searchkey, "ispay"=>$this->ispay, "channel_muserid"=>$this->channel_muserid, "user_sales_id"=>$this->user_sales_id, "comactive_id"=>$this->comactive_id, "overtime"=>$this->overtime, "isovertime"=>$this->isovertime));
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
			$this->url = spUrl('clientall', 'clientrecordlist', array("client_id"=>$client_id));
			$this->display("clients/clientrecordlist.html");
		}catch(Exception $e){
			$this->redirect(spUrl("clientall", "mycreateclientlist"), $e->getMessage());
		}
	}
	
	public function allrecordlist(){
		try {
			$obj_user = spClass('user');
			$obj_record = spClass("client_record");
			$obj_client = spClass("client");
			$postdate = $this->spArgs();
			$page = intval(max($postdate['page'], 1));
			$condition = "crm_client_record.rtype_id = 1";
			if($postdate['starttime'] != ''){
				$condition .= " and crm_client_record.acttime >= ".strtotime($postdate['starttime']);
				$this->starttime = $postdate['starttime'];
			}
			if($postdate['endtime'] != ''){
				$condition .= " and crm_client_record.acttime <= ".strtotime($postdate['endtime']);
				$this->endtime = $postdate['endtime'];
			}
			if(intval($postdate['user_sales_id'])){
				$condition .= " and crm_client.user_sales_id = ".intval($postdate['user_sales_id']);
				$this->user_sales_id = $postdate['user_sales_id'];
			}
			if($client_id = intval($postdate['client_id'])){
				if($this->client_rs = $obj_client->findByPk($client_id, "realname, sex, telphone")){
					$condition .= " and crm_client_record.client_id = ".$client_id;
					$this->client_id = $client_id;
				}
			}
			$record_rs = $obj_record->join("crm_user")->join("crm_client")->spPager($page, 20)->findAll($condition, "crm_client_record.acttime desc", "crm_client_record.*, crm_client.realname, crm_client.telphone, crm_user.realname as realname_create");
			$this->record_rs = $record_rs;
			$this->pager = $obj_record->spPager()->getPager();
			$this->url = spUrl('clientall', 'allrecordlist', array("starttime"=>$this->starttime, "endtime"=>$this->endtime, "user_sales_id"=>$this->user_sales_id, "client_id"=>$this->client_id));
			$this->user_rs = $obj_user->getUser_prep("crm_user.depart_id = 3 and find_in_set('getclient', crm_user.identity_attr)");
			$this->display("clientdepart/allrecordlist.html");
		}catch(Exception $e){
			$this->redirect(spUrl("clientall", "mycreateclientlist"), $e->getMessage());
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
				$this->backurl = spUrl("clientdepart", "clientlist");
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
			$this->plan_rs = $obj_plan->join("crm_client")->join("crm_user", "crm_user.id = crm_client_plan.main_id")->spPager($page, 20)->findAll($condition, "crm_client_plan.createtime desc", "crm_client_plan.*, crm_client.realname, crm_client.telphone, crm_client.user_sales_id, crm_user.realname as realname_create");
			$this->pager = $obj_plan->spPager()->getPager();
			$this->user_prep_rs = $obj_user->getUser_prep("depart_id = 3 and find_in_set('getclient', identity_attr)");
			$this->url = spUrl('clientall', 'planlist', array("user_id"=>$this->user_id, "status"=>$this->status));
		}catch(Exception $e){
			$this->redirect(spUrl("clientall", "clientlist"), $e->getMessage());
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
			$this->url = spUrl("clientall", "odlist");
			$this->display("clientsales/odlist.html");
		}catch(Exception $e){
			$this->redirect(spUrl("clientdepart", "clientlist"), $e->getMessage());
		}
	}
	
	public function allodlist(){
		try {
			$obj_user = spClass("user");
			$postdata = $this->spArgs();
			$obj_od = spClass("client_overtime");
			$page = intval(max($postdata['page'], 1));
			$condition = "1";
			if($postdata["endtime"]){
				switch($postdata["endtime"]){
					case "ing":
						$condition .= " and crm_client_overtime.endtime = 0";
					break;
					case "end":
						$condition .= " and crm_client_overtime.endtime > 0";
					break;
				}
				$this->endtime = $postdata["endtime"];
			}
			if($postdata["user_sales_id"]){
				$condition .= " and crm_client.user_sales_id = {$postdata["user_sales_id"]}";
				$this->user_sales_id = $postdata["user_sales_id"];
			}
			$this->od_rs = $obj_od->join("crm_user")->join("crm_client")->spPager($page, 15)->findAll($condition, "createtime desc", "crm_client_overtime.*, crm_client.realname as client_realname, crm_client.sex, crm_client.telphone, crm_user.realname as realname");
			$this->pager = $obj_od->spPager()->getPager();
			$this->url = spUrl("clientall", "allodlist", array("endtime"=>$this->endtime, "user_sales_id"=>$this->user_sales_id));
			$this->controller = "clientall";
			$this->istel = 1;
			$this->user_prep_rs = $obj_user->getUser_prep("depart_id = 3 and find_in_set('getclient', identity_attr)");
			$this->display("clientdepart/allodlist.html");
		}catch(Exception $e){
			$this->redirect(spUrl("clientall", "clientlist"), $e->getMessage());
		}
	}
	
	//批量转移
	public function batchtransfer(){
		$obj_origin = spClass('origin');
		$obj_client = spClass('client');
		$obj_department = spClass('department');
		$obj_user = spClass('user');
		$obj_country = spClass('country');
		$obj_channel = spClass('channel');
		$obj_level = spClass("client_level");
		$obj_travel = spClass('travel');
		$obj_comactive = spClass('comactive');
		$obj_od = spClass('client_overtime');
		$obj_record = spClass("client_record");
		$postdata = $this->spArgs();
		$depart_id = 3;
		//$condition = $user_condition = "crm_user.depart_id = $depart_id and find_in_set('getclient', crm_user.identity_attr)";
		$user_condition = "crm_user.depart_id = $depart_id and find_in_set('getclient', crm_user.identity_attr)";
		$condition = "crm_client.isdel = 0 and crm_client.isoverdate = 0";
		$origin_id = intval($postdata['origin_id']);
		if($origin_id > 0){
			$condition .= " and crm_client.origin_id = $origin_id";
			$this->origin_id = $origin_id;
		}elseif($origin_id == -1){
			$condition .= " and crm_client.channel_id > 0";
			$this->origin_id = $origin_id;
		}
		if($channel_id = intval($postdata['channel_id'])){
			$condition .= " and crm_client.channel_id = $channel_id";
			$this->channel_id = $channel_id;
		}
		if($postdata['statdate']){
			$condition .= " and FROM_UNIXTIME(crm_vip_client.fullpay_arrivaltime,'%Y-%m') = '{$postdata['statdate']}'";
			$this->statdate = $postdata['statdate'];
		}
		if($postdata['starttime'] != ''){
			$condition .= " and crm_client.visit_time >= ".strtotime($postdata['starttime']);
			$this->starttime = $postdata['starttime'];
		}
		if($postdata['endtime'] != ''){
			$condition .= " and crm_client.visit_time <= ".strtotime($postdata['endtime']);
			$this->endtime = $postdata['endtime'];
		}
		if($postdata["searchkey"]){
			$condition .= " and (crm_client.realname like '%".$postdata['searchkey']."%' or crm_client.telphone like '%".$postdata['searchkey']."%')";
			$this->searchkey = $postdata['searchkey'];
		}
		if(intval($postdata['channel_muserid'])){
			$condition .= " and crm_channel.maintenance_id = ".intval($postdata['channel_muserid']);
			$this->channel_muserid = $postdata['channel_muserid'];
		}
		if(intval($postdata['user_sales_id'])){
			$condition .= " and crm_client.user_sales_id = ".intval($postdata['user_sales_id']);
			$this->user_sales_id = $postdata['user_sales_id'];
		}
		if($postdata['ispay']."a" != "a"){
			$condition .= " and crm_client.ispay = ".intval($postdata['ispay']);
			$this->ispay = $postdata['ispay'];
		}
		if($postdata['fword']."a" != "a"){
			$condition .= " and fristPinyin(crm_client.realname) = '".$postdata['fword']."'";
			$this->fword = $postdata['fword'];
		}
		if($level_id = intval($postdata['level_id'])){
			$condition .= " and crm_client.level_id = {$level_id}";
			$this->level_id = $level_id;
		}
		if($comactive_id = intval($postdata['comactive_id'])){
			$condition .= " and crm_client.comactive_id = {$comactive_id}";
			$this->comactive_id = $comactive_id;
		}
		if($postdata['overtime']){
			switch($postdata['overtime']){
				case "yes":
					$condition .= " and crm_client_overtime.fromtime > 0";
					break;
				case "no":
					$condition .= " and isnull(crm_client_overtime.fromtime)";
					break;
			}
			$this->overtime = $postdata['overtime'];
		}
		if($isovertime = intval($postdata['isovertime'])){
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
		if($sort = $postdata['sort']){
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
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			try {
				$data = array();
				$id = $postdata["id"];
				$to_sales_id = intval($postdata["to_sales_id"]);
				if(!$id)
					throw new Exception("请先选择客户再进行该操作");
				if(!$to_sales_id)
					throw new Exception("请先选择分配人再进行该操作");
				$id_implode = implode(",", $id);
				$id_str = "'".implode("','", $id)."'";
				$count_id = count($id);
				$condition .= " and crm_client.id in($id_str)";
				$total_rs = $obj_client->find($condition, null, "count(id) as total");
				$total_id = intval($total_rs["total"]);
				if($count_id != $total_id)
					throw new Exception("您所选的客户不符合转移标准，无法进行该操作");
				if(!$client_implode_rs = $obj_client->findAll($condition, "field(id,{$id_implode})", "id, realname, telphone"))
					throw new Exception("客户汇总错误，如有问题请联系管理员");
				$user_condition .= " and crm_user.id = $to_sales_id";
				if(!$user_rs = $obj_user->find($user_condition, null, "id, realname"))
					throw new Exception("您所选的分配人不符合标准");
				$obj_client->getDb()->beginTrans();
				if(!$obj_client->update($condition, array("user_sales_id"=>$to_sales_id)))
					throw new Exception("未知错误，客户转移失败");
				if($client_implode_rs){
					$client_group_rs = array();
					foreach($client_implode_rs as $val){
						$client_group_rs[] = $val["id"].":".$val["realname"];
					}
					$client_group_str = implode(",", $client_group_rs);
				}
				spClass('user_log')->save_log(3, "将客户 (id:姓名)[{$client_group_str}]批量转移给了 {$user_rs["realname"]} [id:{$user_rs["id"]}]", array("client_id"=>$id_implode));
				$obj_client->getDb()->commitTrans();
				$message = array('msg'=>"客户批量转移成功", 'result'=>1);
				echo json_encode($message);
				exit();
			}catch(Exception $e){
				$obj_client->getDb()->rollbackTrans();
				echo json_encode(array("msg"=>$e->getMessage(), "result"=>0));
				exit();
			}
		}
		if($postdata["searchkey"]){
			$condition .= " and (crm_client.realname like '%".$postdata['searchkey']."%' or crm_client.telphone like '%".$postdata['searchkey']."%')";
			$this->searchkey = $postdata['searchkey'];
		}
		if(intval($postdata['user_sales_id'])){
			$condition .= " and crm_client.user_sales_id = ".intval($postdata['user_sales_id']);
			$this->user_sales_id = $postdata['user_sales_id'];
		}
		if($postdata['ispay'].'a' !== 'a'){
			$condition .= " and crm_client.ispay = ".intval($postdata['ispay']);
			$this->ispay = $postdata['ispay'];
		}
		$sort = 'crm_client.createtime desc';
		$page = intval(max($postdata['page'], 1));
		$this->user_rs = $obj_user->getUser_prep($user_condition);
		if($client_rs = $obj_client
		->join("crm_client_level")
		->join("crm_origin")
		->join("crm_user", "crm_user.id = crm_client.user_sales_id")
		->join("crm_channel", "crm_channel.id = crm_client.channel_id and crm_client.channel_id > 0", "left")
		->join("crm_client_overtime", "crm_client_overtime.client_id = crm_client.id and crm_client_overtime.endtime = 0", "left")
		->join("crm_client_process", "crm_client_process.id = crm_client.process_id and crm_client.process_id > 0", "left")
		->join("crm_client_plan", "crm_client.id = crm_client_plan.client_id and crm_client_plan.typeid = 2 and crm_client_plan.isfinish = 0 and crm_client_plan.starttime <= UNIX_TIMESTAMP()", "left")
		->spPager($page, 20)->findAll($condition, $sort, "crm_client.*, crm_origin.oname, crm_client_level.name as level_name, count(crm_client_plan.id) as plan_count, crm_client_overtime.fromtime, crm_user.realname as realname_sale, crm_client_process.pname, IF(crm_client.ispay = 0, datediff(curdate(), FROM_UNIXTIME(crm_client.overdatestart, '%Y-%m-%d')), 0) as overdate", "crm_client.id")){
			foreach($client_rs as $key => $val){
				if($val["channel_id"]){
					$client_rs[$key]["channel_name"] = $obj_channel->getname($val["channel_id"]);
				}
				/*
					if($val["channelact_id"]){
				$client_rs[$key]["channel_act_name"] = $obj_active->getname($val["channelact_id"]);
				}
				if($val["trader_id"]){
				$client_rs[$key]["trader_name"] = $obj_trader->getname($val["trader_id"]);
				}
				*/
				if($val["travel_id"]){
					$client_rs[$key]["travel_name"] = $obj_travel->getname($val["travel_id"]);
				}
				if($val["exp_country_id"]){
					$client_rs[$key]["ctname"] = $obj_country->getname($val["exp_country_id"]);
				}
			}
			$this->client_rs = $client_rs;
		}
		$this->level_rs = $obj_level->getlist();
		$this->channel_muser_rs = $obj_user->getUser_prep("crm_user.depart_id = 2");
		$this->channel_rs = $obj_channel->getAllChannel_prep();
		$this->pager = $obj_client->spPager()->getPager();
		$this->comactive_rs = $obj_comactive->getlist();
		$this->origin_rs = $obj_origin->get_origin();
		$this->url = spUrl('clientall', 'batchtransfer', array("starttime"=>$this->starttime, "endtime"=>$this->endtime, "level_id"=>$this->level_id, "statdate"=>$this->statdate, "channel_id"=>$this->channel_id, "sort"=>$this->sort, "searchkey"=>$this->searchkey, "ispay"=>$this->ispay, "channel_muserid"=>$this->channel_muserid, "user_sales_id"=>$this->user_sales_id, "comactive_id"=>$this->comactive_id, "overtime"=>$this->overtime, "isovertime"=>$this->isovertime));
	}
	
	//批量无意向
	public function batchoverdate(){
		$obj_origin = spClass('origin');
		$obj_client = spClass('client');
		$obj_department = spClass('department');
		$obj_user = spClass('user');
		$obj_country = spClass('country');
		$obj_channel = spClass('channel');
		$obj_level = spClass("client_level");
		$obj_travel = spClass('travel');
		$obj_comactive = spClass('comactive');
		$obj_od = spClass('client_overtime');
		$obj_record = spClass("client_record");
		$postdata = $this->spArgs();
		$depart_id = 3;
		//$condition = $user_condition = "crm_user.depart_id = $depart_id and find_in_set('getclient', crm_user.identity_attr)";
		$user_condition = "crm_user.depart_id = $depart_id and find_in_set('getclient', crm_user.identity_attr)";
		$condition = "crm_client.isdel = 0 and crm_client.isoverdate = 0";
		$origin_id = intval($postdata['origin_id']);
		if($origin_id > 0){
			$condition .= " and crm_client.origin_id = $origin_id";
			$this->origin_id = $origin_id;
		}elseif($origin_id == -1){
			$condition .= " and crm_client.channel_id > 0";
			$this->origin_id = $origin_id;
		}
		if($channel_id = intval($postdata['channel_id'])){
			$condition .= " and crm_client.channel_id = $channel_id";
			$this->channel_id = $channel_id;
		}
		if($postdata['statdate']){
			$condition .= " and FROM_UNIXTIME(crm_vip_client.fullpay_arrivaltime,'%Y-%m') = '{$postdata['statdate']}'";
			$this->statdate = $postdata['statdate'];
		}
		if($postdata['starttime'] != ''){
			$condition .= " and crm_client.visit_time >= ".strtotime($postdata['starttime']);
			$this->starttime = $postdata['starttime'];
		}
		if($postdata['endtime'] != ''){
			$condition .= " and crm_client.visit_time <= ".strtotime($postdata['endtime']);
			$this->endtime = $postdata['endtime'];
		}
		if($postdata["searchkey"]){
			$condition .= " and (crm_client.realname like '%".$postdata['searchkey']."%' or crm_client.telphone like '%".$postdata['searchkey']."%')";
			$this->searchkey = $postdata['searchkey'];
		}
		if(intval($postdata['channel_muserid'])){
			$condition .= " and crm_channel.maintenance_id = ".intval($postdata['channel_muserid']);
			$this->channel_muserid = $postdata['channel_muserid'];
		}
		if(intval($postdata['user_sales_id'])){
			$condition .= " and crm_client.user_sales_id = ".intval($postdata['user_sales_id']);
			$this->user_sales_id = $postdata['user_sales_id'];
		}
		if($postdata['ispay']."a" != "a"){
			$condition .= " and crm_client.ispay = ".intval($postdata['ispay']);
			$this->ispay = $postdata['ispay'];
		}
		if($postdata['fword']."a" != "a"){
			$condition .= " and fristPinyin(crm_client.realname) = '".$postdata['fword']."'";
			$this->fword = $postdata['fword'];
		}
		if($level_id = intval($postdata['level_id'])){
			$condition .= " and crm_client.level_id = {$level_id}";
			$this->level_id = $level_id;
		}
		if($comactive_id = intval($postdata['comactive_id'])){
			$condition .= " and crm_client.comactive_id = {$comactive_id}";
			$this->comactive_id = $comactive_id;
		}
		if($postdata['overtime']){
			switch($postdata['overtime']){
				case "yes":
					$condition .= " and crm_client_overtime.fromtime > 0";
					break;
				case "no":
					$condition .= " and isnull(crm_client_overtime.fromtime)";
					break;
			}
			$this->overtime = $postdata['overtime'];
		}
		if($isovertime = intval($postdata['isovertime'])){
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
		if($sort = $postdata['sort']){
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
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			try {
				$data = array();
				$id = $postdata["id"];
				if(!$id)
					throw new Exception("请先选择客户再进行该操作");
				$id_implode = implode(",", $id);
				$id_str = "'".implode("','", $id)."'";
				$count_id = count($id);
				$condition .= " and crm_client.id in($id_str)";
				$total_rs = $obj_client->find($condition, null, "count(id) as total");
				$total_id = intval($total_rs["total"]);
				if($count_id != $total_id)
					throw new Exception("您所选的客户不符合转移标准，无法进行该操作");
				if(!$client_implode_rs = $obj_client->findAll($condition, "field(id,{$id_implode})", "id, realname, telphone"))
					throw new Exception("客户汇总错误，如有问题请联系管理员");
				$obj_client->getDb()->beginTrans();
				$data = array();
				$data["isoverdate"] = 1;
				$data["overdatetime"] = time();
				$data["overdatereason"] = "强制批量，原因：".$postdata["reason"];
				if(!$postdata["reason"])
					throw new Exception("请填写原因");
				if(!$obj_client->update($condition, $data))
					throw new Exception("未知错误，客户批量无意向失败");
				if($client_implode_rs){
					$client_group_rs = array();
					foreach($client_implode_rs as $val){
						$client_group_rs[] = $val["id"].":".$val["realname"];
					}
					$client_group_str = implode(",", $client_group_rs);
				}
				spClass('user_log')->save_log(3, "将客户 (id:姓名)[{$client_group_str}]批量转为无意向客户", array("client_id"=>$id_implode));
				$obj_client->getDb()->commitTrans();
				$message = array('msg'=>"客户批量无意向成功", 'result'=>1);
				echo json_encode($message);
				exit();
			}catch(Exception $e){
				$obj_client->getDb()->rollbackTrans();
				echo json_encode(array("msg"=>$e->getMessage(), "result"=>0));
				exit();
			}
		}
		if($postdata["searchkey"]){
			$condition .= " and (crm_client.realname like '%".$postdata['searchkey']."%' or crm_client.telphone like '%".$postdata['searchkey']."%')";
			$this->searchkey = $postdata['searchkey'];
		}
		if(intval($postdata['user_sales_id'])){
			$condition .= " and crm_client.user_sales_id = ".intval($postdata['user_sales_id']);
			$this->user_sales_id = $postdata['user_sales_id'];
		}
		if($postdata['ispay'].'a' !== 'a'){
			$condition .= " and crm_client.ispay = ".intval($postdata['ispay']);
			$this->ispay = $postdata['ispay'];
		}
		$sort = 'crm_client.createtime desc';
		$page = intval(max($postdata['page'], 1));
		$this->user_rs = $obj_user->getUser_prep($user_condition);
		if($client_rs = $obj_client
		->join("crm_client_level")
		->join("crm_origin")
		->join("crm_user", "crm_user.id = crm_client.user_sales_id")
		->join("crm_channel", "crm_channel.id = crm_client.channel_id and crm_client.channel_id > 0", "left")
		->join("crm_client_overtime", "crm_client_overtime.client_id = crm_client.id and crm_client_overtime.endtime = 0", "left")
		->join("crm_client_process", "crm_client_process.id = crm_client.process_id and crm_client.process_id > 0", "left")
		->join("crm_client_plan", "crm_client.id = crm_client_plan.client_id and crm_client_plan.typeid = 2 and crm_client_plan.isfinish = 0 and crm_client_plan.starttime <= UNIX_TIMESTAMP()", "left")
		->spPager($page, 20)->findAll($condition, $sort, "crm_client.*, crm_origin.oname, crm_client_level.name as level_name, count(crm_client_plan.id) as plan_count, crm_client_overtime.fromtime, crm_user.realname as realname_sale, crm_client_process.pname, IF(crm_client.ispay = 0, datediff(curdate(), FROM_UNIXTIME(crm_client.overdatestart, '%Y-%m-%d')), 0) as overdate", "crm_client.id")){
			foreach($client_rs as $key => $val){
				if($val["channel_id"]){
					$client_rs[$key]["channel_name"] = $obj_channel->getname($val["channel_id"]);
				}
				/*
				 if($val["channelact_id"]){
				$client_rs[$key]["channel_act_name"] = $obj_active->getname($val["channelact_id"]);
				}
				if($val["trader_id"]){
				$client_rs[$key]["trader_name"] = $obj_trader->getname($val["trader_id"]);
				}
				*/
				if($val["travel_id"]){
					$client_rs[$key]["travel_name"] = $obj_travel->getname($val["travel_id"]);
				}
				if($val["exp_country_id"]){
					$client_rs[$key]["ctname"] = $obj_country->getname($val["exp_country_id"]);
				}
			}
			$this->client_rs = $client_rs;
		}
		$this->level_rs = $obj_level->getlist();
		$this->channel_muser_rs = $obj_user->getUser_prep("crm_user.depart_id = 2");
		$this->channel_rs = $obj_channel->getAllChannel_prep();
		$this->pager = $obj_client->spPager()->getPager();
		$this->comactive_rs = $obj_comactive->getlist();
		$this->origin_rs = $obj_origin->get_origin();
		$this->url = spUrl('clientall', 'batchoverdate', array("starttime"=>$this->starttime, "endtime"=>$this->endtime, "level_id"=>$this->level_id, "statdate"=>$this->statdate, "channel_id"=>$this->channel_id, "sort"=>$this->sort, "searchkey"=>$this->searchkey, "ispay"=>$this->ispay, "channel_muserid"=>$this->channel_muserid, "user_sales_id"=>$this->user_sales_id, "comactive_id"=>$this->comactive_id, "overtime"=>$this->overtime, "isovertime"=>$this->isovertime));
	}
	
	//再分配并转为有效客户
	public function transfer(){
		try {
			if(!$id = intval($this->spArgs('id')))
				throw new Exception('请先选择查看项，再进行操作！');
			$backurl = spUrl("clientall", "clientlist");
			$obj_client = spClass("client");
			$obj_origin = spClass("origin");
			$obj_user = spClass("user");
			$obj_department = spClass('department');
			if(!$client_rs = $obj_client->getClientById($id))
				throw new Exception("找不到该客户，可能已被分配");
			$this->check_private($client_rs);
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try {
					$data["transfer"] = intval($this->spArgs("transfer"));
					if(!$data["transfer"])
						throw new Exception("请选择被分配人");
					$obj_client->overtransfer($client_rs, $data["transfer"]);
					$message = array('msg'=>"客户分配成功", 'result'=>1, "url"=>spUrl("clientall", "clientlist"));
					echo json_encode($message);
					exit();
				}catch(Exception $e){
					$message = array('msg'=>$e->getMessage(), 'result'=>0);
					echo json_encode($message);
					exit();
				}
			}
			$this->client_rs = $client_rs;
			$condition = "crm_user.depart_id = 3 and find_in_set('getclient', identity_attr)";
			$this->user_prep_rs = $obj_user->getUser_prep($condition);
		}catch(Exception $e){
			$this->redirect($backurl, $e->getMessage());
		}
	}
	
	private function check_private($client_rs){
		if(!$client_rs["isoverdate"])
			throw new Exception("该客户不是过期客户，无法进行该操作");
	}
}
?>