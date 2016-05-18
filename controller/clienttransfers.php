<?php
class clienttransfers extends spController {
	
	public function __construct(){
		parent::__construct();
		try {
			die("close");
			$obj_cpt = spClass("department_competence");
			$obj_cpt->check_login_competence("CLIENT");
		}catch(Exception $e){
			$backurl = spUrl("main", "welcome");
			$this->redirect($backurl, $e->getMessage());
			exit();
		}
	}
	
	public function clientlist(){
		$obj_client = spClass('client');
		$obj_origin = spClass('origin');
		$obj_user = spClass("user");
		$postdate = $this->spArgs();
		$page = intval(max($postdate['page'], 1));
		$condition = "crm_client.user_sales_id = 0 and crm_client.istransfer = 1 and crm_client.isdel = 0";
		if($postdate['searchkey'] != '')
			$condition .= " and (crm_client.realname like '%{$postdate['searchkey']}%' or crm_client.telphone like '%{$postdate['searchkey']}%')";
		if($client_rs = $obj_client->join("crm_user")->join("crm_client_process", "crm_client_process.id = crm_client.process_id", "left")->spPager($page, 20)->findAll($condition, 'crm_client.createtime desc', "crm_client.*, crm_client_process.pname, crm_user.realname as realname_create")){
			foreach($client_rs as $key => $val){
				if($val["sourcetype"] == 1){
					$client_rs[$key]["oname"] = "渠道来源";
				}elseif($val["sourcetype"] == 2){
					$origin_rs = $obj_origin->findByPk($val["origin_id"]);
					$client_rs[$key]["oname"] = $origin_rs["oname"];
				}
			}
			$this->client_rs = $client_rs;
		}
		$this->pager = $obj_client->spPager()->getPager();
		$this->searchkey = $postdate['searchkey'];
		$this->url = spUrl('clienttransfers', 'clientlist', array("searchkey"=>$this->searchkey));
	}
	
	public function viewclient(){
		try {
			if(!$id = intval($this->spArgs('id')))
				throw new Exception('请先选择查看项，再进行操作！');
			$backurl = spUrl("clienttransfers", "clientlist");
			$obj_client = spClass("client");
			$obj_origin = spClass("origin");
			$obj_user = spClass("user");
			$obj_notice = spClass("user_notice");
			if(!$client_rs = $obj_client->getTransferClientById($id))
				throw new Exception("找不到该客户，可能已被分配");
			$this->origin_rs = $origin_rs = $obj_origin->getOriginById($client_rs["origin_id"]);
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try {
					$data["id"] = $id;
					$data["user_sales_id"] = $this->spArgs("user_owner_id");
					$sale_rs = $obj_client->transfer($data, $origin_rs);
					$obj_notice->send_notice($client_rs["create_id"], "客户 ".$client_rs[realname]." 被分配到置业顾问", "客户 ".$client_rs[realname]." 被 " . $_SESSION["sscrm_user"]["realname"] . " 分配给了置业顾问 $sale_rs[realname]");
					$obj_notice->send_notice($data["user_sales_id"], "客户 ".$client_rs[realname]." 被分配到置业顾问", "客户 ".$client_rs[realname]." 被 " . $_SESSION["sscrm_user"]["realname"] . " 分配给您");
					spClass('user_log')->save_log(3, "分配了来源为 ".$origin_rs["oname"]." 的客户 ".$client_rs['realname']." [id:$id]", array("client_id"=>$id, "user_owner_id"=>$this->spArgs("user_owner_id")));
					$message = array('msg'=>"客户分配成功", 'result'=>1, "url"=>spUrl("clienttransfers", "clientlist"));
					echo json_encode($message);
					exit();
				}catch(Exception $e){
					$message = array('msg'=>$e->getMessage(), 'result'=>0);
					echo json_encode($message);
					exit();
				}
			}
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
			$this->user_transfer_group_rs = $obj_user->getlistGroupDepart($this->origin_rs["transfer_department"]);
			$this->from_rs = $obj_origin->getClientViewFrom($this->origin_rs, $client_rs);
			$this->client_rs = $client_rs;
			$this->backurl = $backurl;
		}catch(Exception $e){
			$this->redirect($backurl, $e->getMessage());
		}
	}
}
?>