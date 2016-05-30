<?php
class clientdepart_oversea extends spController {
	
	public function __construct(){
		parent::__construct();
		try {
			$obj_cpt = spClass("department_competence");
			$obj_cpt->check_login_competence("CLIENTOVERSEAS");
			//if(!in_array("oversea_viewallclient", $_SESSION["sscrm_user"]["auth_mark"]))
			if($_SESSION["sscrm_user"]["depart_id"] != 4 || !$_SESSION["sscrm_user"]["isdirector"])
				throw new Exception("您无权查看该页面");
			$this->controller = "clientdepart_oversea";
			$this->clisturl = spUrl($this->controller, "clientlist");
		}catch(Exception $e){
			$backurl = spUrl("main", "welcome");
			$this->redirect($backurl, $e->getMessage());
			exit();
		}
	}
	
	public function clientlist(){
		$obj_client = spClass('client');
		$obj_level = spClass("client_level");
		$obj_record = spClass("client_record");
		$obj_origin = spClass('origin');
		$obj_user = spClass('user');
		$obj_country = spClass('country');
		$obj_channel = spClass('channel');
		$obj_active = spClass('channel_active');
		$obj_trader = spClass('trader');
		$obj_travel = spClass('travel');
		$obj_department = spClass('department');
		$obj_sep = spClass('department_sep');
		$obj_comactive = spClass('comactive');
		$obj_int = spClass("client_intention");
		$obj_intrecord = spClass("client_intention_record");
		$postdate = $this->spArgs();
		$page = intval(max($postdate['page'], 1));
		$user_id = $_SESSION["sscrm_user"]["id"];
		$depart_id = 3;
		$condition = $user_condition = "crm_user.depart_id = $depart_id";
		$condition .= " and crm_client.isdel = 0";
		$user_rs = $obj_user->getUser_prep($user_condition);
		/*
		$relative_array = $obj_user->get_relative_array();
		if($user_rs){
			foreach($user_rs as $key => $val){
				$user_key[$val["id"]] = $val["realname"];
				$user_id_list[] = $val["id"];
			}
			$user_id_str = implode(",", $user_id_list);
			$condition = "(0";
			if($relative_array){
				foreach($relative_array as $val){
					$condition .= " or IF($val > 0, $val in($user_id_str), 0)";
				}
			}
			$condition .= ")";
		}else{
			$condition = "0";
		}
		*/
		$origin_id = intval($postdate['origin_id']);
		if($origin_id > 0){
			$condition .= " and crm_client.origin_id = $origin_id";
			$this->origin_id = $origin_id;
		}elseif($origin_id == -1){
			$condition .= " and crm_client.channel_id > 0";
			$this->origin_id = $origin_id;
		}
		if($postdate['starttime'] != ''){
			$condition .= " and crm_client.visit_time >= ".strtotime($postdate['starttime']);
			$this->starttime = $postdate['starttime'];
		}
		if($postdate['endtime'] != ''){
			$condition .= " and crm_client.visit_time <= ".strtotime($postdate['endtime']. "+ 1 day");
			$this->endtime = $postdate['endtime'];
		}
		if($postdate["searchkey"]){
			$condition .= " and (crm_client.realname like '%".$postdate['searchkey']."%' or crm_client.telphone like '%".$postdate['searchkey']."%')";
			$this->searchkey = $postdate['searchkey'];
		}
		if($postdate["statdate"] != ''){
			$condition .= " and FROM_UNIXTIME(crm_client.fullpay_arrivaltime,'%Y-%m') = '".date("Y-m", strtotime($postdate["statdate"]))."'";
			$this->statdate = $postdate['statdate'];
		}
		if(intval($postdate['user_sales_id'])){
			$condition .= " and crm_client.user_sales_id = ".intval($postdate['user_sales_id']);
			$this->user_sales_id = $postdate['user_sales_id'];
		}
		if($level_id = intval($postdate['level_id'])){
			$condition .= " and crm_client.level_id = {$level_id}";
			$this->level_id = $level_id;
		}
		if($comactive_id = intval($postdate['comactive_id'])){
			$condition .= " and crm_client.comactive_id = {$comactive_id}";
			$this->comactive_id = $comactive_id;
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
		$sort = 'crm_client.createtime desc';
		if($postdate['sort'].'a' !== 'a'){
			switch ($postdate['sort']){
				case "overdate_desc":
					$sort = "overdate desc, ".$sort;
					$this->sort = $postdate['sort'];
					break;
				case "level_asc":
					$sort = "crm_client_level.sort asc, ".$sort;
					$this->sort = $postdate['sort'];
					break;
				case "level_desc":
					$sort = "crm_client_level.sort desc, ".$sort;
					$this->sort = $postdate['sort'];
					break;
				default:
						
				break;
			}
		}
		if($client_rs = $obj_client
			->join("crm_client_level")
			->join("crm_user", "crm_user.id = crm_client.user_sales_id")
			->join("crm_client_seehouse")
			->join("crm_client_overtime", "crm_client_overtime.client_id = crm_client.id and crm_client_overtime.endtime = 0", "left")
			->join("crm_client_process", "crm_client_process.id = crm_client.process_id and crm_client.process_id > 0", "left")
			->join("crm_client_plan", "crm_client.id = crm_client_plan.client_id and crm_client_plan.typeid = 2 and crm_client_plan.isfinish = 0 and crm_client_plan.starttime <= UNIX_TIMESTAMP()", "left")
			->spPager($page, 20)->findAll($condition, $sort, "crm_client.*, crm_client_level.name as level_name, crm_client_seehouse.see_status, count(crm_client_plan.id) as plan_count, crm_client_overtime.fromtime, crm_user.realname as realname_sale, crm_client_process.pname, IF(crm_client.ispay = 0, datediff(curdate(), FROM_UNIXTIME(crm_client.overdatestart, '%Y-%m-%d')), 0) as overdate", "crm_client.id")){
			foreach($client_rs as $key => $val){
				if($val["sourcetype"] == 1){
					$client_rs[$key]["oname"] = "渠道来源";
				}elseif($val["sourcetype"] == 2){
					$origin_rs = $obj_origin->findByPk($val["origin_id"]);
					$client_rs[$key]["oname"] = $origin_rs["oname"];
				}
				if($val["channel_id"]){
					$client_rs[$key]["channel_name"] = $obj_channel->getname($val["channel_id"]);
				}
				if($val["channelact_id"]){
					$client_rs[$key]["channel_act_name"] = $obj_active->getname($val["channelact_id"]);
				}
				if($val["trader_id"]){
					$client_rs[$key]["trader_name"] = $obj_trader->getname($val["trader_id"]);
				}
				if($val["travel_id"]){
					$client_rs[$key]["travel_name"] = $obj_travel->getname($val["travel_id"]);
				}
				if($val["exp_country_id"]){
					$client_rs[$key]["ctname"] = $obj_country->getname($val["exp_country_id"]);
				}
				$client_rs[$key]["record_count"] = $obj_record->getCountById($val["id"]);
				$client_rs[$key]["record_call_count"] = $obj_intrecord->getCountByClientId($val["id"]);
				if($int_rs = $obj_int->find(array("client_id"=>$val["id"]), null, "createtime")){
					$client_rs[$key]["int_createtime"] = $int_rs["createtime"];
				}
			}
			$this->client_rs = $client_rs;
		}
		$this->comactive_rs = $obj_comactive->getlist();
		$this->level_rs = $obj_level->getlist();
		$this->user_rs = $user_rs;
		$this->pager = $obj_client->spPager()->getPager();
		$this->url = spUrl('clientdepart_oversea', 'clientlist', array("starttime"=>$this->starttime, "endtime"=>$this->endtime, "sort"=>$this->sort, "level_id"=>$this->level_id, "statdate"=>$this->statdate, "searchkey"=>$this->searchkey, "user_sales_id"=>$this->user_sales_id, "comactive_id"=>$this->comactive_id, "isovertime"=>$this->isovertime));
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
			$record_rs = $obj_record->join("crm_user")->join("crm_client")->join("crm_country", "crm_country.id = crm_client.exp_country_id", "left")->spPager($page, 20)->findAll(array("crm_client_record.client_id"=>$client_id, "crm_client_record.rtype_id"=>1), "crm_client_record.createtime asc", "crm_client_record.*, crm_country.country, crm_client.demand, crm_user.realname as realname_create");
			$this->record_rs = $record_rs;
			$this->pager = $obj_record->spPager()->getPager();
			$this->client_id = $client_id;
			$this->url = spUrl('clientdepart_oversea', 'clientrecordlist', array("client_id"=>$client_id));
			$this->display("clients/clientrecordlist.html");
		}catch(Exception $e){
			$this->redirect(spUrl("clientdepart_oversea", "clientlist"), $e->getMessage());
		}
	}
	
	public function allrecordlist(){
		try {
			$obj_user = spClass('user');
			$obj_record = spClass("client_record");
			$postdate = $this->spArgs();
			$page = intval(max($postdate['page'], 1));
			$condition = "crm_client_record.rtype_id = 1";
			$is_sep = 0;//是否分部门
			if($is_sep){
				$condition .= " and sale_user.depart_sep_id = " . $_SESSION["sscrm_user"]["depart_sep_id"];
				//$condition .= " and sale_user.depart_sep_id <> 1";
			}
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
			$record_rs = $obj_record->join("crm_user")->join("crm_client")->join("crm_country", "crm_country.id = crm_client.exp_country_id", "left")->join("crm_user as sale_user", "sale_user.id = crm_client.user_sales_id")->spPager($page, 20)->findAll($condition, "crm_client_record.acttime desc", "crm_client_record.*, crm_country.country, crm_client.demand, crm_client.realname, crm_client.telphone, crm_user.realname as realname_create");
			$this->record_rs = $record_rs;
			$this->pager = $obj_record->spPager()->getPager();
			$this->url = spUrl('clientdepart_oversea', 'allrecordlist', array("starttime"=>$this->starttime, "endtime"=>$this->endtime, "user_sales_id"=>$this->user_sales_id));
			$this->user_rs = $obj_user->getUser_prep("crm_user.depart_id = 3 and find_in_set('getclient', crm_user.identity_attr)");
			$this->controller = "clientdepart_oversea";
		}catch(Exception $e){
			$this->redirect(spUrl("clientdepart_oversea", "clientlist"), $e->getMessage());
		}
	}
	
	public function clientcallrecordlist(){
		try {
			$obj_type = spClass("client_intention_type");
			if(!$this->type_rs = $obj_type->find(array("id"=>1)))
				throw new Exception("type参数错误");
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_client = spClass("client");
			if(!$this->client_rs = $obj_client->getClientById($client_id))
				throw new Exception("找不到该客户，可能已被分配");
			$obj_int = spClass("client_intention");
			$this->int_rs = $obj_int->find(array("client_id"=>$client_id));
			$obj_record = spClass("client_intention_record");
			$postdata = $this->spArgs();
			$page = intval(max($postdata['page'], 1));
			$record_rs = $obj_record->join("crm_user")->join("crm_client_intention")->spPager($page, 15)->findAll(array("crm_client_intention_record.intention_id"=>$this->int_rs["id"]), "crm_client_intention_record.createtime desc", "crm_client_intention_record.*, crm_user.realname as realname_create");
			$this->record_rs = $record_rs;
			$this->pager = $obj_record->spPager()->getPager();
			$this->url = spUrl('clientdepart_oversea', 'clientcallrecordlist', array("client_id"=>$client_id, "call_id"=>$call_id));
			$this->backurl = spUrl("clientdepart_oversea", "clientlist");
			$this->display("clientintention/clientrecordlist.html");
		}catch(Exception $e){
			$this->redirect(spUrl("clientdepart_oversea", "clientlist"), $e->getMessage());
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
			$this->plan_rs = $obj_plan->join("crm_client")->join("crm_user")->spPager($page, 10)->findAll($condition, "crm_client_plan.createtime desc", "crm_client_plan.*, crm_client.realname, crm_client.telphone, crm_client.user_sales_id, crm_user.realname as realname_create");
			$this->pager = $obj_plan->spPager()->getPager();
			$this->user_prep_rs = $obj_user->getUser_prep("depart_id = 3 and find_in_set('getclient', identity_attr)");
			$this->url = spUrl('clientdepart_oversea', 'planlist', array("user_id"=>$this->user_id, "status"=>$this->status));
			$this->backurl = spUrl("clientdepart_oversea", "clientlist");
			$this->display("clientdepart/planlist.html");
		}catch(Exception $e){
			$this->redirect(spUrl("clientdepart_oversea", "clientlist"), $e->getMessage());
		}
	}
}
?>