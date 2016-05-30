<?php
class clientintention_m extends spController {
	
	public function __construct(){
		parent::__construct();
		try {
			if(!$_SESSION["sscrm_user"]["isceo"])
				throw new Exception("您无权查看该页");
			$this->controller = "clientintention_m";
			if($type = $this->spArgs("type")){
				$obj_type = spClass("client_intention_type");
				if(!$this->type_rs = $obj_type->find(array("id"=>$type)))
					throw new Exception("type参数错误");
			}
		}catch(Exception $e){
			$backurl = spUrl("main", "welcome");
			$this->redirect($backurl, $e->getMessage());
			exit();
		}
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
		$condition = "crm_client_intention.isdel = 0";
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
		$this->usetype_rs = $obj_type->getlist();
		$this->user_rs = $obj_user->getUser_prep();
		$this->int_rs = $int_rs;
		$this->pager = $obj_int->spPager()->getPager();
		$this->url = spUrl($this->controller, 'allclientlist', array("type"=>$this->type_rs["id"], "user_id"=>$this->user_id, "searchkey"=>$this->searchkey, "isclient"=>$this->isclient));
		$this->display("clientintention/allclientlist.html");
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
			$this->display("clientintention/intentionview.html");
		}catch(Exception $e){
			$this->redirect(spUrl($this->controller, "allclientlist", array('type'=>$this->type_rs["id"])), $e->getMessage());
			exit();
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
			$this->url = spUrl($this->controller, 'clientrecordlist', array("type"=>$this->type_rs["id"], "client_id"=>$client_id));
			$this->backurl = spUrl($this->controller, 'allclientlist', array("type"=>$this->type_rs["id"]));
			$this->display("clientintention/clientrecordlist.html");
		}catch(Exception $e){
			$this->redirect(spUrl($this->controller, "allclientlist", array("type"=>$this->type_rs["id"])), $e->getMessage());
		}
	}
	
	public function allrecordlist(){
		try {
			$obj_record = spClass("client_intention_record");
			$obj_type = spClass("client_intention_type");
			$obj_int = spClass("client_intention");
			$obj_user = spClass('user');
			$postdata = $this->spArgs();
			$page = intval(max($postdata['page'], 1));
			$condition = "1";
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
			$this->clisturl = spUrl($this->controller, "allclientlist");
			$this->url = spUrl($this->controller, 'allrecordlist', array("type"=>$this->type_rs["id"], "starttime"=>$this->starttime, "endtime"=>$this->endtime, "user_id"=>$this->user_id, "intention_id"=>$this->intention_id));
			$this->user_rs = $obj_user->getUser_prep();
			$this->usetype_rs = $obj_type->getlist();
			$this->display("clientintention/allrecordlist.html");
		}catch(Exception $e){
			$this->redirect(spUrl($this->controller, "allclientlist", array("type"=>$this->type_rs["id"])), $e->getMessage());
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
			$this->url = spUrl($this->controller, 'saleallclientrecordlist', array("type"=>$this->type_rs["id"], "client_id"=>$client_id));
			$this->backurl = spUrl($this->controller, "allclientlist", array("type"=>$this->type_rs["id"]));
			$this->display("clients/clientrecordlist.html");
		}catch(Exception $e){
			$this->redirect(spUrl($this->controller, "clientlist", array("type"=>$this->type_rs["id"])), $e->getMessage());
		}
	}
	
	public function allplanlist(){
		try {
			$postdata = $this->spArgs();
			$obj_plan = spClass("client_plan");
			$obj_client = spClass("client_intention");
			$page = intval(max($postdata['page'], 1));
			$condition = "crm_client_plan.typeid = 3";
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
			$this->backurl = spUrl($this->controller, "allclientlist");
			$this->clisturl = spUrl($this->controller, "allclientlist");
			$this->plan_rs = $obj_plan->join("crm_client_intention")->join("crm_user")->spPager($page, 10)->findAll($condition, "crm_client_plan.createtime desc", "crm_client_plan.*, crm_client_intention.realname, crm_client_intention.telphone, crm_client_intention.create_id as intention_create_id, crm_client_intention.realname as realname_create");
			$this->pager = $obj_plan->spPager()->getPager();
			$this->url = spUrl($this->controller, 'allplanlist', array("type"=>$this->type_rs["id"], "intention_id"=>$this->intention_id, "status"=>$this->status));
			$this->display("clientintention/allplanlist.html");
		}catch(Exception $e){
			$this->redirect(spUrl($this->controller, "allclientlist", array("type"=>$this->type_rs["id"])), $e->getMessage());
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
			$this->url = spUrl($this->controller, "allodlist", array("id"=>$id));
			$this->backurl = spUrl($this->controller, "allclientlist");
			$this->display("clientintention/odlist.html");
		}catch(Exception $e){
			$this->redirect(spUrl($this->controller, "allclientlist"), $e->getMessage());
		}
	}
}
?>