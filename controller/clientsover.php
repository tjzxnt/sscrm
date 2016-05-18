<?php
class clientsover extends spController {
	
	public function __construct(){
		parent::__construct();
		try {
			if(!in_array("clientover", $_SESSION["sscrm_user"]["auth_mark"]))
				throw new Exception("您无权查看该页面");
		}catch(Exception $e){
			$backurl = spUrl("main", "welcome");
			$this->redirect($backurl, $e->getMessage());
			exit();
		}
	}
	

	public function clientlist(){
		$obj_client = spClass('client');
		$obj_country = spClass('country');
		$obj_department = spClass('department');
		$obj_sep = spClass('department_sep');
		$obj_record = spClass("client_record");
		$postdate = $this->spArgs();
		$page = intval(max($postdate['page'], 1));
		$condition = "crm_client.isdel = 0 and crm_client.isoverdate = 1";
		if($postdate['searchkey'] != '')
			$condition .= " and (crm_client.realname like '%{$postdate['searchkey']}%' or crm_client.telphone like '%{$postdate['searchkey']}%')";
		try {
			$depart_id = 3;
			$depart_rs = $obj_department->getinfoById($depart_id);
			if($depart_rs["is_sep"]){
				if(!$sep_id = $_SESSION["sscrm_user"]["depart_sep_id"])
					throw new Exception("您尚未分配到".$depart_rs["dname"]."下的所在组，请尝试重新登录");
				if(!$sep_rs = $obj_sep->find(array("depart_id"=>$depart_id, "id"=>$sep_id)))
					throw new Exception("您所在的组不正确，请尝试重新登录");
				$this->sep_name = "(".$sep_rs["sep_name"].")";
				$extcondition = " and crm_user.depart_id = $depart_id and crm_user.depart_sep_id = $sep_id";
			}
		}catch(Exception $e){
			$this->redirect(spUrl("main", "welcome"), $e->getMessage());
			exit();
		}
		if($extcondition)
			$condition .= $extcondition;
		if($client_rs = $obj_client->join("crm_client_level")->join("crm_user", "crm_user.id = crm_client.user_sales_id")->join("crm_origin")->join("crm_credential")->join("crm_client_process", "crm_client_process.id = crm_client.process_id", "left")->spPager($page, 20)->findAll($condition, 'crm_client.overdatetime desc', "crm_client.*, crm_client_level.name as level_name, crm_credential.cname, crm_origin.oname, crm_user.realname as realname_sale, crm_client_process.pname")){
			foreach($client_rs as $key => $val){
				if($val["exp_country_id"]){
					$client_rs[$key]["ctname"] = $obj_country->getname($val["exp_country_id"]);
				}
				$client_rs[$key]["record_count"] = $obj_record->getCountById($val["id"]);
			}
		}
		$this->client_rs = $client_rs;
		$this->pager = $obj_client->spPager()->getPager();
		$this->searchkey = $postdate['searchkey'];
		$this->url = spUrl('clientsover', 'clientlist', array("searchkey"=>$this->searchkey));
	}
	
	public function viewclient(){
		try {
			if(!$id = intval($this->spArgs('id')))
				throw new Exception('请先选择查看项，再进行操作！');
			$url = $_SERVER['HTTP_REFERER'];
			$obj_client = spClass("client");
			$obj_origin = spClass("origin");
			if(!$client_rs = $obj_client->getClientById($id))
				throw new Exception("找不到该客户");
			$this->check_private($client_rs);
			$this->client_rs = $client_rs;
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
		}catch(Exception $e){
			$this->redirect($url, $e->getMessage());
		}
	}
	
	public function recordlist(){
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_client = spClass("client");
			if(!$this->client_rs = $obj_client->getClientById($client_id))
				throw new Exception("找不到该客户");
			$this->check_private($this->client_rs);
			$obj_record = spClass("client_record");
			$postdate = $this->spArgs();
			$page = intval(max($postdate['page'], 1));
			$record_rs = $obj_record->join("crm_user")->spPager($page, 20)->findAll(array("crm_client_record.client_id"=>$client_id, "crm_client_record.rtype_id"=>1), "crm_client_record.createtime asc", "crm_client_record.*, crm_user.realname as realname_create");
			$this->record_rs = $record_rs;
			$this->pager = $obj_record->spPager()->getPager();
			$this->client_id = $client_id;
			$this->url = spUrl('clientsover', 'recordlist', array("client_id"=>$client_id));
		}catch(Exception $e){
			$this->redirect(spUrl("clientsover", "clientlist"), $e->getMessage());
		}
	}
	
	public function transfer(){
		try {
			if(!$id = intval($this->spArgs('id')))
				throw new Exception('请先选择查看项，再进行操作！');
			$backurl = spUrl("clientsover", "clientlist");
			$obj_client = spClass("client");
			$obj_origin = spClass("origin");
			$obj_user = spClass("user");
			$obj_department = spClass('department');
			$obj_sep = spClass('department_sep');
			$depart_id = 3;
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
			if(!$client_rs = $obj_client->getClientById($id))
				throw new Exception("找不到该客户，可能已被分配");
			if($depart_rs["is_sep"]){
				if(!$user_sep_rs = $obj_user->find(array("id"=>$client_rs["user_sales_id"], "depart_id"=>$depart_id, "depart_sep_id"=>$sep_id)))
					throw new Exception("您无权分配该组的客户");
			}
			$this->check_private($client_rs);
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try {
					$data["transfer"] = intval($this->spArgs("transfer"));
					if(!$data["transfer"])
						throw new Exception("请选择被分配人");
					$obj_client->overtransfer($client_rs, $data["transfer"]);
					$message = array('msg'=>"客户分配成功", 'result'=>1, "url"=>spUrl("clientsover", "clientlist"));
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
			if($extcondition)
				$condition .= $extcondition;
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