<?php
class traderdeparts extends spController {
	
	public function __construct(){
		parent::__construct();
		try {
			$obj_cpt = spClass("department_competence");
			$obj_cpt->check_login_competence("TRADER");
			if(!$_SESSION["sscrm_user"]["isdirector"])
				throw new Exception("您无权查看该页面");
		}catch(Exception $e){
			$backurl = spUrl("main", "welcome");
			$this->redirect($backurl, $e->getMessage());
			exit();
		}
	}
	
	public function traderlist(){
		$obj_trader = spClass('trader');
		$obj_user = spClass('user');
		$depart_id = $_SESSION["sscrm_user"]["depart_id"];
		$postdate = $this->spArgs();
		$page = intval(max($postdate['page'], 1));
		$user_rs = $obj_user->getUser_prep("crm_user.depart_id = $depart_id");
		$condition = "crm_trader.ishide = 0 and crm_user.depart_id = ".$_SESSION["sscrm_user"]["depart_id"];
		if($postdate['searchkey'] != '')
			$condition .= " and crm_trader.tradername like '%{$postdate['searchkey']}%'";
		if($main_id = intval($postdate["main_id"])){
			$condition .= " and crm_trader.maintenance_id = $main_id";
			$this->main_id = $main_id;
		}
		$sort = 'crm_trader.createtime desc';
		if($postdate['sort'].'a' !== 'a'){
			switch ($postdate['sort']){
				case "recordtime_desc":
					$sort = "recordtime desc, ".$sort;
					$this->sort = $postdate['sort'];
				break;
				default:
		
				break;
			}
		}
		$this->trader_rs = $obj_trader->join("crm_user", "crm_user.id = crm_trader.maintenance_id")->spPager($page, 20)->findAll($condition, $sort, "crm_trader.*, crm_user.realname as main_realname");
		$this->pager = $obj_trader->spPager()->getPager();
		$this->searchkey = $postdate['searchkey'];
		$this->user_rs = $user_rs;
		$this->url = spUrl('traderdeparts', 'traderlist', array("searchkey"=>$this->searchkey, "main_id"=>$main_id,"inuse"=>$this->inuse));
	}
	
	public function clientlist(){
		$obj_client = spClass('client');
		$obj_user = spClass('user');
		$obj_country = spClass('country');
		$obj_trader = spClass('trader');
		$postdate = $this->spArgs();
		$depart_id = $_SESSION["sscrm_user"]["depart_id"];
		if(!$trader_id = intval($this->spArgs("trader_id")))
			throw new Exception("分销商参数丢失");
		$trader_condition = array("crm_trader.ishide" => 0, "crm_trader.id"=>$trader_id, "crm_user.depart_id"=>$depart_id);
		if(!$trader_rs = $obj_trader->join("crm_user", "crm_user.id = crm_trader.maintenance_id")->find($trader_condition, null, "crm_trader.*, crm_user.realname as main_realname"))
			throw new Exception("找不到该分销公司，可能已经过期");
		$user_rs = $obj_user->getUser_prep();
		$page = intval(max($postdate['page'], 1));
		$condition = "crm_client.trader_id = $trader_id";
		if($postdate['starttime'] != ''){
			$condition .= " and crm_client.visit_time >= ".strtotime($postdate['starttime']);
			$this->starttime = $postdate['starttime'];
		}
		if($postdate['endtime'] != ''){
			$condition .= " and crm_client.visit_time <= ".strtotime($postdate['endtime']. "+ 1 day");
			$this->endtime = $postdate['endtime'];
		}
		if($postdate["statdate"] != ''){
			$condition .= " and FROM_UNIXTIME(crm_client.fullpay_arrivaltime,'%Y-%m') = '".date("Y-m", strtotime($postdate["statdate"]))."'";
			$this->statdate = $postdate['statdate'];
		}
		if(intval($postdate['user_sales_id'])){
			$condition .= " and crm_client.user_sales_id = ".intval($postdate['user_sales_id']);
			$this->user_sales_id = $postdate['user_sales_id'];
		}
		if($client_rs = $obj_client
		->join("crm_origin", "crm_origin.id = crm_client.origin_id")
		->join("crm_trader", "crm_trader.id = crm_client.trader_id")
		->join("crm_user", "crm_user.id = crm_client.user_sales_id")
		->join("crm_client_process", "crm_client_process.id = crm_client.process_id")
		->spPager($page, 20)->findAll($condition, 'crm_client.createtime desc', "crm_client.*, crm_origin.oname, crm_trader.tradername, crm_user.realname as realname_sale, crm_client_process.pname")){
			foreach($client_rs as $key => $val){
				if($val["exp_country_id"]){
					$client_rs[$key]["ctname"] = $obj_country->getname($val["exp_country_id"]);
				}
			}
			$this->client_rs = $client_rs;
		}
		$this->trader_id = $trader_id;
		$this->trader_rs = $trader_rs;
		$this->user_rs = $user_rs;
		$this->pager = $obj_client->spPager()->getPager();
		$this->url = spUrl('traderdeparts', 'clientlist', array("trader_id"=>$trader_id, "starttime"=>$this->starttime, "endtime"=>$this->endtime, "statdate"=>$this->statdate, "user_sales_id"=>$this->user_sales_id));
	}
	
	public function viewclient(){
		try {
			$url = $_SERVER['HTTP_REFERER'];
			$obj_client = spClass("client");
			$obj_origin = spClass("origin");
			$obj_trader = spClass('trader');
			$postdate = $this->spArgs();
			if(!$id = intval($this->spArgs('id')))
				throw new Exception('请先选择查看项，再进行操作！');
			$depart_id = $_SESSION["sscrm_user"]["depart_id"];
			if(!$trader_id = intval($this->spArgs("trader_id")))
				throw new Exception("分销参数丢失");
			$trader_condition = array("crm_trader.ishide" => 0, "crm_trader.id"=>$trader_id, "crm_user.depart_id"=>$depart_id);
			if(!$trader_rs = $obj_trader->join("crm_user", "crm_user.id = crm_trader.maintenance_id")->find($trader_condition, null, "crm_trader.*, crm_user.realname as main_realname"))
				throw new Exception("找不到该分销商，可能已经过期");
			if(!$client_rs = $obj_client->getClientById($id))
				throw new Exception("找不到该客户");
			if($client_rs["trader_id"] != $trader_id)
				throw new Exception("客户与分销商不匹配");
			$this->client_rs = $client_rs;
			switch ($client_rs["sourcetype"]){
				case "1":
					//$this->display("clients/viewclient_channel.html");
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
			$obj_trader = spClass('trader');
			$obj_client = spClass("client");
			$depart_id = $_SESSION["sscrm_user"]["depart_id"];
			$client_id = $this->spArgs("client_id");
			if(!$trader_id = intval($this->spArgs("trader_id")))
				throw new Exception("分销参数丢失");
			$trader_condition = array("crm_trader.ishide" => 0, "crm_trader.id"=>$trader_id, "crm_user.depart_id"=>$depart_id);
			if(!$trader_rs = $obj_trader->join("crm_user", "crm_user.id = crm_trader.maintenance_id")->find($trader_condition, null, "crm_trader.*, crm_user.realname as main_realname"))
				throw new Exception("找不到该分销商，可能已经过期");
			if(!$this->client_rs = $obj_client->getClientById($client_id))
				throw new Exception("找不到该客户");
			if($this->client_rs["trader_id"] != $trader_id)
				throw new Exception("客户与分销商不匹配");
			$obj_record = spClass("client_record");
			$postdate = $this->spArgs();
			$page = intval(max($postdate['page'], 1));
			$record_rs = $obj_record->join("crm_user")->join("crm_client")->spPager($page, 20)->findAll(array("crm_client_record.client_id"=>$client_id, "crm_client_record.rtype_id"=>1), "crm_client_record.createtime asc", "crm_client_record.*, crm_user.realname as realname_create");
			$this->record_rs = $record_rs;
			$this->pager = $obj_record->spPager()->getPager();
			$this->client_id = $client_id;
			$this->url = spUrl('traderdeparts', 'clientrecordlist', array("trader_id"=>$trader_id, "client_id"=>$client_id));
			$this->display("clients/clientrecordlist.html");
		}catch(Exception $e){
			$this->redirect(spUrl("traderdeparts", "clientlist", array("trader_id"=>$trader_id)), $e->getMessage());
		}
	}

	public function traderrecordlist(){
		try {
			$obj_trader = spClass('trader');
			$traderid = intval($this->spArgs("traderid"));
			$condition = "crm_trader.ishide = 0 and crm_user.depart_id = ".$_SESSION["sscrm_user"]["depart_id"];
			$condition .= " and crm_trader.id = $traderid";
			if(!$trader_rs = $obj_trader->join("crm_user", "crm_user.id = crm_trader.maintenance_id")->find($condition))
				throw new Exception("您无权查看该页");
			$obj_record = spClass("trader_record");
			$postdate = $this->spArgs();
			$page = intval(max($postdate['page'], 1));
			$record_rs = $obj_record->join("crm_user")->join("crm_trader")->spPager($page, 20)->findAll(array("crm_trader_record.trader_id"=>$traderid), "crm_trader_record.createtime asc", "crm_trader_record.*, crm_user.realname as realname_create");
			$this->record_rs = $record_rs;
			$this->trader_rs = $trader_rs;
			$this->pager = $obj_record->spPager()->getPager();
			$this->traderid = $traderid;
			$this->url = spUrl('traders', 'traderrecordlist', array("traderid"=>$traderid));
			$this->display("traders/traderrecordlist.html");
		}catch(Exception $e){
			$this->redirect(spUrl("traders", "traderlist"), $e->getMessage());
		}
	}
}
?>