<?php
class clientsrelated extends spController {
	
	public function __construct(){
		parent::__construct();
		try {
			/*
			$obj_cpt = spClass("department_competence");
			$obj_cpt->check_login_competence("CLIENTALL");
			*/
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
		$obj_level = spClass("client_level");
		$obj_comactive = spClass('comactive');
		$postdate = $this->spArgs();
		$page = intval(max($postdate['page'], 1));
		$user_id = $_SESSION["sscrm_user"]["id"];
		$relative_array = $obj_user->get_relative_array();
		$condition = "(0";
		if($relative_array){
			foreach($relative_array as $val){
				$condition .= " or IF($val > 0, $val = $user_id, 0)";
			}
		}
		$condition .= ")";
		$condition .= " and crm_client.isdel = 0";
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
			$condition .= " and crm_client.visit_time <= ".strtotime($postdate['endtime']);
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
		if($level_id = intval($postdate['level_id'])){
			$condition .= " and crm_client.level_id = {$level_id}";
			$this->level_id = $level_id;
		}
		if($comactive_id = intval($postdate['comactive_id'])){
			$condition .= " and crm_client.comactive_id = {$comactive_id}";
			$this->comactive_id = $comactive_id;
		}
		if($client_rs = $obj_client->join("crm_client_level")->join("crm_user", "crm_user.id = crm_client.user_sales_id")->join("crm_client_process", "crm_client_process.id = crm_client.process_id")->spPager($page, 20)->findAll($condition, 'crm_client.createtime desc', "crm_client.*, crm_client_level.name as level_name, crm_user.realname as realname_sale, crm_client_process.pname")){
			foreach($client_rs as $key => $val){
				if($val["sourcetype"] == 1){
					$client_rs[$key]["oname"] = "渠道来源";
				}elseif($val["sourcetype"] == 2){
					$origin_rs = $obj_origin->findByPk($val["origin_id"]);
					$client_rs[$key]["oname"] = $origin_rs["oname"];
				}
				if($val["exp_country_id"]){
					$client_rs[$key]["ctname"] = $obj_country->getname($val["exp_country_id"]);
				}
				$client_rs[$key]["record_count"] = $obj_record->getCountById($val["id"]);
			}
			$this->client_rs = $client_rs;
		}
		$this->comactive_rs = $obj_comactive->getlist();
		$this->level_rs = $obj_level->getlist();
		$this->user_rs = $obj_user->getUser_prep();
		$this->pager = $obj_client->spPager()->getPager();
		$this->url = spUrl('clientsrelated', 'clientlist', array("starttime"=>$this->starttime, "endtime"=>$this->endtime, "statdate"=>$this->statdate, "user_sales_id"=>$this->user_sales_id, "comactive_id"=>$this->comactive_id));
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
			if(!$client_rs = $obj_client->getClientById($id))
				throw new Exception("找不到该客户");
			/* 验证权限start */
			$relative_array = $obj_user->get_relative_array(1);
			$relate_time = 0;
			if($relative_array){
				foreach($relative_array as $val){
					if($client_rs[$val] == $_SESSION["sscrm_user"]["id"])
						$relate_time++;
				}
			}
			if(!$relate_time)
				throw new Exception("该客户为无关客户，无法查看");
			/* 验证权限end */
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
			$obj_user = spClass("user");
			$obj_client = spClass("client");
			if(!$this->client_rs = $obj_client->getClientById($client_id))
				throw new Exception("找不到该客户");
			/* 验证权限start */
			$relative_array = $obj_user->get_relative_array(1);
			$relate_time = 0;
			if($relative_array){
				foreach($relative_array as $val){
					if($this->client_rs[$val] == $_SESSION["sscrm_user"]["id"])
						$relate_time++;
				}
			}
			if(!$relate_time)
				throw new Exception("该客户为无关客户，无法查看");
			/* 验证权限end */
			$obj_record = spClass("client_record");
			$postdate = $this->spArgs();
			$page = intval(max($postdate['page'], 1));
			$record_rs = $obj_record->join("crm_user")->join("crm_client")->spPager($page, 20)->findAll(array("crm_client_record.client_id"=>$client_id, "crm_client_record.rtype_id"=>1), "crm_client_record.createtime asc", "crm_client_record.*, crm_user.realname as realname_create");
			$this->no_tel = 1;
			$this->record_rs = $record_rs;
			$this->pager = $obj_record->spPager()->getPager();
			$this->client_id = $client_id;
			$this->url = spUrl('clientall', 'clientrecordlist', array("client_id"=>$client_id));
			$this->display("clients/clientrecordlist.html");
		}catch(Exception $e){
			$this->redirect(spUrl("clients", "mycreateclientlist"), $e->getMessage());
		}
	}
	
	public function clientorderfundlist(){
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_user = spClass("user");
			$obj_client = spClass("client");
			$obj_fund = spClass("client_order_fund");
			if(!$this->client_rs = $obj_client->getClientById($client_id))
				throw new Exception("找不到该客户");
			if(!in_array($this->client_rs["ispay"], array(1,2)))
				throw new Exception("该客户不处于未付完全款和已付全款状态，无法查看订单");
			/* 验证权限start */
			$relative_array = $obj_user->get_relative_array(1);
			$relate_time = 0;
			if($relative_array){
				foreach($relative_array as $val){
					if($this->client_rs[$val] == $_SESSION["sscrm_user"]["id"])
						$relate_time++;
				}
			}
			if(!$relate_time)
				throw new Exception("该客户为无关客户，无法查看");
			/* 验证权限end */
			$postdate = $this->spArgs();
			$page = intval(max($postdate['page'], 1));
			$fund_rs = $obj_fund->join("crm_user")->join("crm_client")->findAll(array("crm_client_order_fund.client_id"=>$client_id, "is_protocol"=>1), "crm_client_order_fund.createtime asc", "crm_client_order_fund.*, crm_user.realname as realname_create");
			$this->fund_rs = $fund_rs;
			$this->client_id = $client_id;
			$this->url = spUrl('clientsrelated', 'clientorderfundlist', array("client_id"=>$client_id));
			$this->backurl = spUrl("clientsrelated", "clientlist");
			$this->display("clientsales/clientorderfundlist.html");
		}catch(Exception $e){
			$this->redirect(spUrl("clientsrelated", "clientlist"), $e->getMessage());
		}
	}
}
?>