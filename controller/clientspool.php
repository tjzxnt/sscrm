<?php
class clientspool extends spController {
	
	public function __construct(){
		parent::__construct();
		try {
			$obj_cpt = spClass("department_competence");
			$obj_identity = spClass("user_identity");
			$obj_cpt->check_login_competence("CLIENTSALE");
			if($_SESSION["sscrm_user"]["depart_id"] != 3)
				throw new Exception("只有销售部能访问该模块");
			/*
			if(!$obj_identity->checkidentity("getclient"))
				throw new Exception("您没有接触客户的权限");
			*/
		}catch(Exception $e){
			$backurl = spUrl("main", "welcome");
			$this->redirect($backurl, $e->getMessage());
			exit();
		}
	}
	

	public function poolclientlist(){
		$obj_client = spClass('client');
		$obj_origin = spClass('origin');
		$obj_process = spClass('client_process');
		$obj_dpt = spClass("department");
		$postdate = $this->spArgs();
		$page = intval(max($postdate['page'], 1));
		$condition = "crm_client.isdel = 0 and crm_client.ispool = 1";
		if($postdate['searchkey'] != '')
			$condition .= " and (crm_client.realname like '%{$postdate['searchkey']}%' or crm_client.telphone like '%{$postdate['searchkey']}%')";
		if($client_rs = $obj_client->join("crm_user")->join("crm_credential")->join("crm_client_process", "crm_client_process.id = crm_client.process_id", "left")->spPager($page, 20)->findAll($condition, 'crm_client.pooltime desc', "crm_client.*, crm_credential.cname, crm_user.realname as realname_create, crm_client_process.pname")){
			foreach($client_rs as $key => $val){
				if($val["sourcetype"] == 1){
					$client_rs[$key]["oname"] = "渠道来源";
				}elseif($val["sourcetype"] == 2){
					$origin_rs = $obj_origin->findByPk($val["origin_id"]);
					$client_rs[$key]["oname"] = $origin_rs["oname"];
					if($origin_rs["reception_depart"]){
						$client_rs[$key]["reception_depart"] = explode(",",$origin_rs["reception_depart"]);
						$client_rs[$key]["reception_depart_rs"] = $obj_dpt->findAll("id in($origin_rs[reception_depart]) and isdel = 0", "sort asc", "dname");
					}
				}
			}
			$this->client_rs = $client_rs;
		}
		$this->pager = $obj_client->spPager()->getPager();
		$this->searchkey = $postdate['searchkey'];
		$this->url = spUrl('clientspool', 'poolclientlist', array("searchkey"=>$this->searchkey));
	}
	
	public function viewpool(){
		try {
			if(!$id = intval($this->spArgs('id')))
				throw new Exception('请先选择查看项，再进行操作！');
			$obj_user = spClass("user");
			$obj_channel = spClass("channel");
			$obj_client = spClass("client");
			$obj_origin = spClass("origin");
			$obj_cred = spClass("credential");
			$obj_country = spClass('country');
			if(!$client_rs = $obj_client->getPoolClientById($id))
				throw new Exception("找不到该客户");
			$this->client_rs = $client_rs;
			$this->cred_rs = $obj_cred->get_credential();
			$this->country_rs = $obj_country->getlist();
			switch ($client_rs["sourcetype"]){
				case "1":
					$this->display("clientspool/viewclientonly_channel.html");
					break;
				case "2":
					$postdate = $this->spArgs();
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
					$this->display("clientspool/viewclientonly.html");
					break;
				case "3":
					throw new Exception("错误的来源通道");
					break;
			}
		}catch(Exception $e){
			$this->redirect($_SERVER['HTTP_REFERER'], $e->getMessage());
		}
	}
	
	public function pooltome(){
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_client = spClass("client");
			$obj_notice = spClass("user_notice");
			$obj_origin = spClass("origin");
			if(!$this->client_rs = $obj_client->getPoolClientById($client_id))
				throw new Exception("找不到该客户");
			if(!$origin_rs = $obj_origin->getClientOrigin($client_id))
				throw new Exception("不正确的来源数据");
			if($origin_rs["reception_depart"]){
				$reception_depart_list = explode(",", $origin_rs["reception_depart"]);
				if(!in_array($_SESSION["sscrm_user"]["depart_id"], $reception_depart_list))
					throw new Exception("该客户不允许加入到您的部门");
			}
			$obj_client->getDb()->beginTrans();
			$obj_client->pooltome($client_id);
			$obj_notice->send_notice($_SESSION["sscrm_user"]["id"], "客户 ".$this->client_rs[realname]." 被从公共客户池放回", "客户 ".$this->client_rs[realname]." 被您加入到我的客户中");
			spClass('user_log')->save_log(3, "将客户 ".$this->client_rs['realname']." [id:$client_id] 从公共客户池中加入到我的客户", array("client_id"=>$client_id));
			$obj_client->getDb()->commitTrans();
			$message = array('msg'=>"已成功将该客户加入到我的客户", "url"=>spUrl("clientspool", "poolclientlist"), 'result'=>1);
			echo json_encode($message);
			exit();
		}catch(Exception $e){
			$obj_client->getDb()->rollbackTrans();
			$message = array('msg'=>$e->getMessage(), 'result'=>0);
			echo json_encode($message);
			exit();
		}
	}
	
	public function poolrecordlist(){
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_client = spClass("client");
			if(!$this->client_rs = $obj_client->getPoolClientById($client_id))
				throw new Exception("找不到该客户");
			$obj_record = spClass("client_record");
			$postdate = $this->spArgs();
			$page = intval(max($postdate['page'], 1));
			$record_rs = $obj_record->join("crm_user")->spPager($page, 20)->findAll(array("crm_client_record.client_id"=>$client_id, "crm_client_record.rtype_id"=>1), "crm_client_record.createtime asc", "crm_client_record.*, crm_user.realname as realname_create");
			$this->record_rs = $record_rs;
			$this->pager = $obj_record->spPager()->getPager();
			$this->client_id = $client_id;
			$this->url = spUrl('clientspool', 'poolrecordlist', array("client_id"=>$client_id));
		}catch(Exception $e){
			$this->redirect(spUrl("clientspool", "poolclientlist"), $e->getMessage());
		}
	}
	
	public function poolfalselist(){
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_client = spClass("client");
			if(!$this->client_rs = $obj_client->getPoolClientById($client_id))
				throw new Exception("找不到该客户");
			$obj_false = spClass("client_false");
			$postdate = $this->spArgs();
			$page = intval(max($postdate['page'], 1));
			$false_rs = $obj_false->join("crm_user")->spPager($page, 20)->findAll(array("crm_client_false.client_id"=>$client_id), "crm_client_false.createtime asc", "crm_client_false.*, crm_user.realname as realname_create");
			$this->false_rs = $false_rs;
			$this->pager = $obj_false->spPager()->getPager();
			$this->client_id = $client_id;
			$this->url = spUrl('clientspool', 'poolreason', array("client_id"=>$client_id));
		}catch(Exception $e){
			$this->redirect(spUrl("clientspool", "poolclientlist"), $e->getMessage());
		}
	}
}
?>