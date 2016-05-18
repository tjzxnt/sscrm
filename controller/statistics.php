<?php
class statistics extends spController {
	
	public function __construct(){
		parent::__construct();
		try {
			$obj_cpt = spClass("department_competence");
			if(!$_SESSION["sscrm_user"]["user_identity"]["operate"]["enabled"])
				$obj_cpt->check_login_competence("PERFORMANCE");
		}catch(Exception $e){
			$backurl = spUrl("main", "welcome");
			$this->redirect($backurl, $e->getMessage());
			exit();
		}
	}
	

	public function sales(){
		$obj_user = spClass('user');
		$obj_client = spClass('client');
		$statdate = $this->spArgs("statdate") ? date("Y-m", strtotime($this->spArgs("statdate"))) : date("Y-m");
		$showtype = $this->spArgs("showtype");
		if($user_rs = $obj_user->getUserByDepart("3", "find_in_set('getclient', crm_user.identity_attr)", "crm_user.isdirector desc")){
			foreach($user_rs as $key => $val){
				$total_rs = $obj_client->find("user_sales_id = ".$val["id"]." and crm_client.isoverdate = 0 and crm_client.isdel = 0 and crm_client.ispool = 0", null, "count(id) as total");
				$user_rs[$key]["client_total"] = intval($total_rs["total"]);
				$total_deal = $obj_client->client_deal_count($val);
				if($showtype == "all"){
					$user_rs[$key]["total_deal_count"] = intval($total_deal["total"]);
					$user_rs[$key]["total_deal_total"] = intval($total_deal["total_standard"]);
				}
				$month_deal = $obj_client->client_deal_count($val, $statdate);
				$user_rs[$key]["month_deal_count"] = intval($month_deal["total"]);
				$user_rs[$key]["month_deal_total"] = intval($month_deal["total_standard"]);
			}
		}
		$this->user_rs = $user_rs;
		$this->statdate = $statdate;
		$this->showtype = $showtype;
		$this->controller = "statistics";
		$this->action = "sales";
	}
	
	public function markets($id){
		$obj_user = spClass('user');
		$obj_client = spClass('client');
		$obj_channel = spClass('channel');
		$obj_sign = spClass('channel_sign');
		$statdate = $this->spArgs("statdate") ? date("Y-m", strtotime($this->spArgs("statdate"))) : date("Y-m");
		$showtype = $this->spArgs("showtype");
		if($user_rs = $obj_user->getUserByDepart("2", null, "crm_user.isdirector desc")){
			foreach($user_rs as $key => $val){
				$month_visit_rs = $obj_client->channel_active_visit_count($val, $statdate);
				$total_main_channel_rs = $obj_sign->main_channel_total($val);
				$user_rs[$key]["total_main_count"] = intval($total_main_channel_rs["total"]);
				$user_rs[$key]["month_visit_count"] = intval($month_visit_rs["total"]);
				$month_client_deal_rs = $obj_client->sale_client_deal_account_origin_channel_all($val, $statdate, 0, 1);
				$user_rs[$key]["month_client_count"] = intval($month_client_deal_rs["total"]);
				$user_rs[$key]["month_client_total"] = intval($month_client_deal_rs["total"]);
				$month_sign_rs = $obj_sign->sign_total($val, $statdate);
				$user_rs[$key]["month_sign_count"] = intval($month_sign_rs["total"]);
				if($showtype == "all"){
					$total_sign_rs = $obj_sign->signed_channel_total($val);
					$user_rs[$key]["total_sign_count"] = intval($total_sign_rs["total"]);
					$total_visit_rs = $obj_client->channel_active_visit_count($val);
					$user_rs[$key]["total_visit_count"] = intval($total_visit_rs["total"]);
					$total_client_deal_rs = $obj_client->sale_client_deal_account_origin_channel_all($val, null, 0, 1);
					$user_rs[$key]["total_client_count"] = intval($total_client_deal_rs["total"]);
					$user_rs[$key]["total_client_total"] = intval($total_client_deal_rs["total"]);
				}
			}
		}
		$this->user_rs = $user_rs;
		$this->statdate = $statdate;
		$this->showtype = $showtype;
		$this->controller = "statistics";
		$this->action = "markets";
	}
	
	public function others(){
		$obj_user = spClass('user');
		$obj_client = spClass('client');
		$obj_channel = spClass('channel');
		$obj_sign = spClass('channel_sign');
		$statdate = $this->spArgs("statdate") ? date("Y-m", strtotime($this->spArgs("statdate"))) : date("Y-m");
		$showtype = $this->spArgs("showtype");
		if($user_rs = $obj_user->getUserByDepart(array(3, 4, 5, 6, 7, 8), "!find_in_set('getclient', crm_user.identity_attr)", "crm_user.depart_id asc, crm_user.isdirector desc")){
			foreach($user_rs as $key => $val){
				/*签约渠道数*/
				$month_sign_rs = $obj_sign->sign_total($val, $statdate);
				$user_rs[$key]["month_sign_count"] = intval($month_sign_rs["total"]);
				/*资料来源，我的call客，我的地推, 我的推荐*/
				$relative_condition = "(crm_client.user_datafrom_id = {$val["id"]} or crm_client.user_teler_id = {$val["id"]} or crm_client.user_preader_id = {$val["id"]} or crm_client.user_owner_id = {$val["id"]} or crm_channel.from_id = {$val["id"]})";
				$month_relative_condition =  $relative_condition . " and FROM_UNIXTIME(crm_client.visit_time,'%Y-%m') = '".date("Y-m", strtotime($statdate))."'";
				$month_relative_rs = $obj_client->join("crm_channel", "crm_channel.id = crm_client.channel_id", "left")->find($month_relative_condition, null, "count(crm_client.id) as total");
				$user_rs[$key]["month_relative_count"] = intval($month_relative_rs["total"]);
				/*资料来源，我的call客，我的地推, 我的推荐的签成数和佣金*/
				$relative_pay_condition = "(crm_client.user_datafrom_id = {$val["id"]} or crm_client.user_teler_id = {$val["id"]} or crm_client.user_preader_id = {$val["id"]} or crm_client.user_owner_id = {$val["id"]} or crm_channel.from_id = {$val["id"]}) and (ispay = 1 or ispay = 2)";
				$month_relative_pay_condition =  $relative_pay_condition . " and FROM_UNIXTIME(crm_client.dealtime,'%Y-%m') = '".date("Y-m", strtotime($statdate))."'";
				$month_relative_pay_rs = $obj_client->join("crm_channel", "crm_channel.id = crm_client.channel_id", "left")->find($month_relative_pay_condition, null, "count(crm_client.id) as total, sum(crm_client.service_price_standard) as total_standard");
				$user_rs[$key]["month_relative_pay_count"] = intval($month_relative_pay_rs["total"]);
				$user_rs[$key]["month_relative_pay_total"] = intval($month_relative_pay_rs["total_standard"]);
				if($showtype == "all"){
					$total_sign_rs = $obj_sign->signed_channel_total($val);
					$user_rs[$key]["total_sign_count"] = intval($total_sign_rs["total"]);
					$total_relative_rs = $obj_client->join("crm_channel", "crm_channel.id = crm_client.channel_id", "left")->find($relative_condition, null, "count(crm_client.id) as total");
					$user_rs[$key]["total_relative_count"] = intval($total_relative_rs["total"]);
					$total_relative_pay_rs = $obj_client->join("crm_channel", "crm_channel.id = crm_client.channel_id", "left")->find($relative_pay_condition, null, "count(crm_client.id) as total, sum(crm_client.service_price_standard) as total_standard");
					$user_rs[$key]["total_relative_pay_count"] = intval($total_relative_pay_rs["total"]);
					$user_rs[$key]["total_relative_pay_total"] = intval($total_relative_pay_rs["total_standard"]);
				}
			}
		}
		$this->user_rs = $user_rs;
		$this->statdate = $statdate;
		$this->showtype = $showtype;
		$this->controller = "statistics";
		$this->action = "others";
	}
}
?>