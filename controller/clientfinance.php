<?php
class clientfinance extends spController {
	
	public function __construct(){
		parent::__construct();
		try {
			$obj_cpt = spClass("department_competence");
			$obj_cpt->check_login_competence("FINANCE");
		}catch(Exception $e){
			$backurl = spUrl("main", "welcome");
			$this->redirect($backurl, $e->getMessage());
			exit();
		}
	}
	
	public function saleclientlist(){
		$obj_user = spClass('user');
		$obj_client = spClass('client');
		$obj_origin = spClass('origin');
		$obj_channel = spClass('channel');
		$obj_channel_user = spClass("channel_user");
		$postdate = $this->spArgs();
		$page = intval(max($postdate['page'], 1));
		$condition = "crm_client.process_id >= 2 and crm_client.isdel = 0 and crm_client.ispool = 0";
		if($postdate['searchkey'] != '')
			$condition .= " and crm_client.bargain like '%".$postdate['searchkey']."%'";
		if($postdate['starttime'] != ''){
			$condition .= " and crm_client.dealtime >= ".strtotime($postdate['starttime']);
			$this->starttime = $postdate['starttime'];
		}
		if($postdate['endtime'] != ''){
			$condition .= " and crm_client.dealtime <= ".strtotime($postdate['endtime']);
			$this->endtime = $postdate['endtime'];
		}
		if($postdate['saleid']."a" != "a"){
			$condition .= " and crm_client.user_sales_id = ".$postdate['saleid'];
			$this->saleid = $postdate['saleid'];
		}
		if($client_rs = $obj_client->join("crm_user")->join("crm_user as sale_user", "sale_user.id = crm_client.user_sales_id")->join("crm_credential")->spPager($page, 20)->findAll($condition, 'crm_client.dealtime desc', "crm_client.*, crm_credential.cname, sale_user.realname as realname_sale, crm_user.realname as realname_create")){
			foreach($client_rs as $key => $val){
				if($val["sourcetype"] == 1){
					$client_rs[$key]["oname"] = "渠道来源";
					$channel_rs = $obj_channel->findByPk($val["channel_id"], "mechanism");
					$channel_user_rs = $obj_user->findByPk($val["user_channel_id"], "realname");
					$channel_referrals_rs = $obj_channel_user->findByPk($val["channel_referrals"], "realname");
					$client_rs[$key]["mechanism"] = $channel_rs["mechanism"];
					$client_rs[$key]["realname_channel"] = $channel_user_rs["realname"];
					$client_rs[$key]["realname_channel_referrals"] = $channel_referrals_rs["realname"];
				}elseif($val["sourcetype"] == 2){
					$origin_rs = $obj_origin->findByPk($val["origin_id"]);
					$client_rs[$key]["oname"] = $origin_rs["oname"];
				}
			}
			$this->client_rs = $client_rs;
		}
		$this->user_rs = $obj_user->getUserByDepart(3, "identity_attr = 'getclient'");
		$this->pager = $obj_client->spPager()->getPager();
		$this->searchkey = $postdate['searchkey'];
		$this->url = spUrl('clientfinance', 'saleclientlist', array("starttime"=>$this->starttime, "endtime"=>$this->endtime, "saleid"=>$this->saleid,"searchkey"=>$this->searchkey));
	}

	public function overseaclientlist(){
		$obj_user = spClass('user');
		$obj_client = spClass('client');
		$obj_origin = spClass('origin');
		$obj_channel = spClass('channel');
		$obj_channel_user = spClass("channel_user");
		$postdate = $this->spArgs();
		$page = intval(max($postdate['page'], 1));
		$condition = "crm_client.process_id = 3 and crm_client.isdel = 0 and crm_client.ispool = 0";
		if($postdate['searchkey'] != '')
			$condition .= " and crm_client.bargain like '%".$postdate['searchkey']."%'";
		if($postdate['starttime'] != ''){
			$condition .= " and crm_client.finishtime >= ".strtotime($postdate['starttime']);
			$this->starttime = $postdate['starttime'];
		}
		if($postdate['endtime'] != ''){
			$condition .= " and crm_client.finishtime <= ".strtotime($postdate['endtime']);
			$this->endtime = $postdate['endtime'];
		}
		if($postdate['overid']."a" != "a"){
			$condition .= " and crm_client.user_overseas_id = ".$postdate['overid'];
			$this->overid = $postdate['overid'];
		}
		$this->client_rs = $obj_client->join("crm_user as oversea_user", "oversea_user.id = crm_client.user_overseas_id")->join("crm_credential")->spPager($page, 20)->findAll($condition, 'crm_client.finishtime desc', "crm_client.*, crm_credential.cname, oversea_user.realname as realname_oversea");
		$this->user_rs = $obj_user->getUserByDepart(4);
		$this->pager = $obj_client->spPager()->getPager();
		$this->searchkey = $postdate['searchkey'];
		$this->url = spUrl('clientfinance', 'overseaclientlist', array("starttime"=>$this->starttime, "endtime"=>$this->endtime, "overid"=>$this->overid,"searchkey"=>$this->searchkey));
	}
}
?>