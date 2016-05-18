<?php
class clientdeal extends spController {
	
	public function __construct(){
		parent::__construct();
		try {
			$obj_cpt = spClass("department_competence");
			if(!$_SESSION["sscrm_user"]["user_identity"]["operate"]["enabled"] && !$_SESSION["sscrm_user"]["user_identity"]["settle"]["enabled"])
				$obj_cpt->check_login_competence("CLIENTDEAL");
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
		$postdate = $this->spArgs();
		$page = intval(max($postdate['page'], 1));
		$condition = "crm_client.isdel = 0";
		$origin_id = intval($postdate['origin_id']);
		$data["starttime"] = $postdate["starttime"];
		$data["endtime"] = $postdate["endtime"];
		$page = intval(max($postdate['page'], 1));
		$pagesize = 20;
		if($postdate["searchkey"] !== "姓名/证件号"){
			$search["searchkey"] = $postdate["searchkey"];
			$this->searchkey = $search["searchkey"];
		}
		if($relate_id = intval($postdate["relate_id"])){
			$search["relate_id"] = $relate_id;
			$this->relate_id = $search["relate_id"];
		}
		$client_rs = $obj_client->clientuserstat($data, array("page"=>$page, "pagesize"=>$pagesize), $search);
		$this->client_rs = $client_rs;
		$this->user_rs = $obj_user->getUser_prep();
		$this->origin_rs = $obj_origin->get_origin();
		$this->pager = $obj_client->spPager()->getPager();
		$this->starttime = $data["starttime"];
		$this->endtime = $data["endtime"];
		$this->url = spUrl('clientdeal', 'clientlist', array("starttime"=>$this->starttime, "endtime"=>$this->endtime, "searchkey"=>$this->searchkey, "relate_id"=>$this->relate_id));
	}
}
?>