<?php
class vipclientoverseas extends spController {
	
	public function __construct(){
		parent::__construct();
		try {
			$obj_cpt = spClass("department_competence");
			$obj_cpt->check_login_competence("CLIENTOVERSEAS");
			if($_SESSION["sscrm_user"]["depart_id"] != 4 || !$_SESSION["sscrm_user"]["isdirector"])
				throw new Exception("您无权查看该页面");
		}catch(Exception $e){
			$backurl = spUrl("main", "welcome");
			$this->redirect($backurl, $e->getMessage());
			exit();
		}
	}
	
	public function myclientlist(){
		$obj_client = spClass('vipclient');
		$obj_process = spClass('client_process');
		$postdate = $this->spArgs();
		$page = intval(max($postdate['page'], 1));
		//$condition = "crm_client.user_overseas_id = ".$_SESSION["sscrm_user"]["id"] . " and crm_client.isdel = 0 and (crm_client.process_id = 2 or crm_client.process_id = 3)";
		$condition = "crm_vip_client.isdel = 0 and crm_vip_client.is_protocol = 1";
		if($postdate['searchkey'] != '')
			$condition .= " and (crm_vip_client.realname like '%{$postdate['searchkey']}%' or crm_vip_client.telphone like '%{$postdate['searchkey']}%' or crm_vip_client.cred_license like '%{$postdate['searchkey']}%')";
		if($postdate['process_id'].'a' !== 'a'){
			$condition .= " and crm_vip_client.process_id = ".$postdate['process_id'];
			$this->process_id = $postdate['process_id'];
		}
		$this->client_rs = $obj_client->join("crm_user")->join("crm_credential")->join("crm_country")->join("crm_client_process")->spPager($page, 20)->findAll($condition, 'crm_vip_client.to_overseas_time desc', "crm_vip_client.*, crm_credential.cname, crm_client_process.pname, crm_user.realname as realname_sales, crm_country.country as exp_country");
		$this->process_rs = $obj_process->getlist();
		$this->pager = $obj_client->spPager()->getPager();
		$this->searchkey = $postdate['searchkey'];
		$this->url = spUrl('vipclientoverseas', 'myclientlist', array("searchkey"=>$this->searchkey, "process_id"=>$this->process_id));
	}
	
	public function viewmyoverseasclient(){
		try {
			if(!$id = intval($this->spArgs('id')))
				throw new Exception('请先选择查看项，再进行操作！');
			$obj_client = spClass("vipclient");
			$obj_origin = spClass("origin");
			if(!$client_rs = $obj_client->getOverSeasClientById($id))
				throw new Exception("找不到该客户");
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
			$this->display("vipclients/viewclient.html");
		}catch(Exception $e){
			$this->redirect($_SERVER['HTTP_REFERER'], $e->getMessage());
		}
	}
	
	public function clientrecordlist(){
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_client = spClass("vipclient");
			if(!$this->client_rs = $obj_client->getOverSeasClientById($client_id))
				throw new Exception("找不到该客户");
			$obj_record = spClass("vipclient_record");
			$postdate = $this->spArgs();
			$page = intval(max($postdate['page'], 1));
			$record_rs = $obj_record->join("crm_user")->join("crm_vip_client")->spPager($page, 20)->findAll(array("crm_vip_client_record.client_id"=>$client_id, "crm_vip_client_record.rtype_id"=>1), "crm_vip_client_record.createtime asc", "crm_vip_client_record.*, crm_user.realname as realname_create");
			$this->record_rs = $record_rs;
			$this->pager = $obj_record->spPager()->getPager();
			$this->client_id = $client_id;
			$this->url = spUrl('vipclientoverseas', 'clientrecordlist', array("client_id"=>$client_id));
			$this->display("vipclientall/clientrecordlist.html");
		}catch(Exception $e){
			$this->redirect(spUrl("vipclientoverseas", "myclientlist"), $e->getMessage());
		}
	}
	
	//查看订单，只限处于已付全款和已移交到海外的客户
	public function clientpayview(){
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_client = spClass("vipclient");
			if(!$this->client_rs = $obj_client->getOverSeasClientById($client_id))
				throw new Exception("找不到该客户");
			/*
			if($this->client_rs["ispay"] != 2)
				throw new Exception("该客户不处于未已付全款状态，无法查看订单");
			if($this->client_rs["process_id"] != 2)
				throw new Exception("该客户不处于已移交到海外状态，无法查看订单");
			*/
			$this->display("vipclients/clientpayview.html");
		}catch(Exception $e){
			$this->redirect(spUrl("vipclientoverseas", "myclientlist"), $e->getMessage());
		}
	}
	
	//查看订单款项列表，已添加房款的客户
	public function clientorderfundlist(){
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_client = spClass("vipclient");
			$obj_fund = spClass("vipclient_order_fund");
			if(!$this->client_rs = $obj_client->getOverSeasClientById($client_id))
				throw new Exception("找不到该客户");
			if(!in_array($this->client_rs["ispay"], array(1,2)))
				throw new Exception("该客户不处于未付完全款和已付全款状态，无法查看订单");
			$postdate = $this->spArgs();
			$page = intval(max($postdate['page'], 1));
			$fund_rs = $obj_fund->join("crm_user")->join("crm_vip_client")->findAll(array("crm_vip_client_order_fund.client_id"=>$client_id, "is_protocol"=>1), "crm_vip_client_order_fund.createtime asc", "crm_vip_client_order_fund.*, crm_user.realname as realname_create");
			$this->fund_rs = $fund_rs;
			$this->client_id = $client_id;
			$this->url = spUrl('vipclientoverseas', 'clientorderfundlist', array("client_id"=>$client_id));
			$this->clearurl = spUrl('vipclientoverseas', 'clientorderfundclear');
			$this->createurl = spUrl("vipclientoverseas", "createorderfund", array("client_id"=>$client_id));
			$this->backurl = spUrl("vipclientoverseas", "myclientlist");
			$this->display("vipclients/clientorderfundlist.html");
		}catch(Exception $e){
			$this->redirect(spUrl("vipclientoverseas", "myclientlist"), $e->getMessage());
		}
	}
	
	//该订单款项已结清
	public function clientorderfundclear(){
		try {
			$postdata = $this->spArgs();
			if($_SESSION["sscrm_user"]["depart_id"] != 4 || !$_SESSION["sscrm_user"]["isdirector"])
				throw new Exception("只有海外部总监才能执行此权限");
			if(!$client_id = intval($postdata["client_id"]))
				throw new Exception("客户参数丢失");
			if(!$verify = $postdata["verify"])
				throw new Exception("请输入验证码");
			if(strtolower($verify) != strtolower($_SESSION["zxverify"]))
				throw new Exception("验证码错误，请重新输入");
			$obj_client = spClass("vipclient");
			$obj_fund = spClass("vipclient_order_fund");
			if(!$this->client_rs = $obj_client->getOverSeasClientById($client_id))
				throw new Exception("找不到该客户");
			if($this->client_rs["isform"] != "1")
				throw new Exception("该客户未签订单，无法进行该操作");
			if($this->client_rs["ispay"] != "1")
				throw new Exception("该客户不处于付款流程中，无法进行该操作");
			$fund_rs = $obj_fund->find(array("crm_vip_client_order_fund.client_id"=>$client_id), null, "max(arrivaltime) as fullpay_arrivaltime");
			if(!$fund_rs["fullpay_arrivaltime"])
				throw new Exception("找不到该订单的到账信息，操作失败");
			$data = array();
			$data["ispay"] = 2;
			//$data["fullpay_arrivaltime"] = $fund_rs["fullpay_arrivaltime"];
			$data["fullpay_arrivaltime"] = time();
			if(!$obj_client->update(array("id"=>$client_id), $data))
				throw new Exception("未知错误，操作失败");
			spClass('user_log')->save_log(12, "将大客户 ".$this->client_rs['realname']." [id:$client_id] 的订单 [" . $this->client_rs['bargain'] . "] 的合同款设置为可返佣", array("vip_client_id"=>$client_id));
			echo json_encode(array("url"=>spUrl("vipclientoverseas", "clientorderfundlist", array("client_id"=>$client_id)), "msg"=>"已将该订单的房款设为可返佣状态", "result"=>1));
			exit();
		}catch(Exception $e){
			echo json_encode(array("msg"=>$e->getMessage(), "result"=>0));
			exit();
		}
	}
	
	//添加订单款项
	public function createorderfund(){
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_client = spClass("vipclient");
			$obj_fund = spClass("vipclient_order_fund");
			if(!$this->client_rs = $obj_client->getOverSeasClientById($client_id))
				throw new Exception("找不到该客户");
			if(!in_array($this->client_rs["ispay"], array("1", "2")))
				throw new Exception("该客户不处于付款状态，无法添加订单款项");
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try {
					$postdata = $this->spArgs();
					$data = array();
					$data["create_id"] = $_SESSION["sscrm_user"]["id"];
					$data["client_id"] = intval($client_id);
					$data["fund_type"] = $postdata["fund_type"];
					$data["pay_standard"] = intval(str_replace(",", "", $postdata["pay_standard"]));
					$data["payabletime"] = strtotime($postdata["payabletime"]);
					$data["arrivaltime"] = strtotime($postdata["arrivaltime"]);
					$data["pay_real"] = intval(str_replace(",", "", $postdata["pay_real"]));
					$data["pay_overdraft"] = intval(str_replace(",", "", $postdata["pay_overdraft"]));
					$data["createtime"] = time();
					if($result = $obj_fund->spValidator($data)){
						foreach($result as $item) {
							throw new Exception($item[0]);
							break;
						}
					}
					if(!$obj_fund->create($data))
						throw new Exception("未知错误，客户款项添加失败");
					spClass('user_log')->save_log(12, "添加了大客户 ".$this->client_rs['realname']." [id:$client_id] 的订单房款明细", array("vip_client_id"=>$client_id));
					$message = array('msg'=>"大客户订单款项添加成功", 'result'=>1, "url"=>spUrl("vipclientoverseas", "clientorderfundlist", array("client_id"=>$client_id)));
					echo json_encode($message);
					exit();
				}catch(Exception $e){
					$message = array('msg'=>$e->getMessage(), 'result'=>0);
					echo json_encode($message);
					exit();
				}
			}
			$this->validator = $obj_fund->getValidatorJS();
			$this->client_id = $client_id;
			$this->saveurl = spUrl("vipclientoverseas", "createorderfund");
			$this->display("vipclients/createorderfund.html");
		}catch(Exception $e){
			$this->redirect(spUrl("vipclientoverseas", "clientorderfundlist", array("client_id"=>$client_id)), $e->getMessage());
		}
	}
	
	//更新房款记录，未付完全款的客户
	public function clienthousefundform(){
		die("close");
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_client = spClass("client");
			$obj_country = spClass("country");
			if(!$this->client_rs = $obj_client->getOverSeasClientById($client_id))
				throw new Exception("找不到该客户");
			if($this->client_rs["ishouse_create"] != 0)
				throw new Exception("该客户已添加房款，无法继续添加");
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try {
					$postdata = $this->spArgs();
					$data = array();
					$data["listings"] = $postdata["listings"];
					$data["listingstype"] = $postdata["listingstype"];
					$data["listingsarea"] = $postdata["listingsarea"];
					$data["houseunit"] = $postdata["houseunit"];
					$data["housefund"] = intval(str_replace(",", "", $postdata["housefund"]));
					$data["ishouseloan"] = intval($postdata["ishouseloan"]);
					$data["housefundtime"] = $data["ishouseloan"] ? intval($postdata["housefundtime"]) : 1;
					$data["houselenders"] = $postdata["houselenders"];
					$data["houselender_rate"] = $postdata["houselender_rate"];
					$data["house_firstpay"] = intval(str_replace(",", "", $postdata["house_firstpay"]));
					$data["houseloan"] = intval(str_replace(",", "", $postdata["houseloan"]));
					$data["houseendpay"] = intval(str_replace(",", "", $postdata["houseendpay"]));
					$data["ishouse_create"] = 1;
					if($result = $obj_client->getValidatorForHouse()->spValidator($data)){
						foreach($result as $item) {
							throw new Exception($item[0]);
							break;
						}
					}
					if($data["housefundtime"] < 1 || ($data["ishouseloan"] && $data["housefundtime"] < 2))
						throw new Exception("房款次数不正确");
					if($data["housefundtime"] > 15)
						throw new Exception("房款次数过多");
					if(!$obj_client->update(array("id"=>$client_id), $data))
						throw new Exception("未知错误，房款记录更新失败");
					spClass('user_log')->save_log(3, "登记了客户 ".$this->client_rs['realname']." [id:$client_id] 的海外房款", array("client_id"=>$client_id));
					$message = array('msg'=>"房款记录更新成功", 'result'=>1, "url"=>spUrl("clientoverseas", "myclientlist"));
					echo json_encode($message);
					exit();
				}catch(Exception $e){
					$message = array('msg'=>$e->getMessage(), 'result'=>0);
					echo json_encode($message);
					exit();
				}
			}
			$this->country_rs = $obj_country->getinfoById($this->client_rs["exp_country_id"]);
			$this->validator = $obj_client->getValidatorForHouseJS();
			$this->client_id = $client_id;
			$this->saveurl = spUrl("clientoverseas", "clienthousefundform");
		}catch(Exception $e){
			$this->redirect(spUrl("clientoverseas", "myclientlist"), $e->getMessage());
		}
	}
	
	//查看房款记录，已添加房款的客户
	public function clienthousefundview(){
		die("close");
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_client = spClass("client");
			if(!$this->client_rs = $obj_client->getOverSeasClientById($client_id))
				throw new Exception("找不到该客户");
			if($this->client_rs["ishouse_create"] != 1)
				throw new Exception("该客户已未添加房款，无法查看");
		}catch(Exception $e){
			$this->redirect(spUrl("clientoverseas", "myclientlist"), $e->getMessage());
		}
	}
	
	//查看款项列表，已添加房款的客户
	public function clientfundlist(){
		die("close");
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_client = spClass("client");
			$obj_fund = spClass("client_fund");
			$obj_country = spClass("country");
			if(!$this->client_rs = $obj_client->getOverSeasClientById($client_id))
				throw new Exception("找不到该客户");
			if($this->client_rs["ishouse_create"] != 1)
				throw new Exception("该客户已未添加房款，无法查看");
			if($this->client_rs["housefundtime"] < 1)
				throw new Exception("房款总数不能小于1次");
			if($this->client_rs["process_id"] != 2)
				throw new Exception("该客户不处于转到海外状态，无法进行该操作");
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try {
					$postdate = $this->spArgs();
					$total = 0;
					$obj_fund->getDb()->beginTrans();
					foreach($postdate["fundid"] as $key => $val){
						$data = array();
						if(!$val){
							$data["create_id"] = $_SESSION["sscrm_user"]["id"];
							$data["client_id"] = $client_id;
							$data["fund_type"] = 1;
							$data["createtime"] = time() + $key;
						}
						$data["pay_standard"] = intval(str_replace(",", "", $postdate["pay_standard"][$key]));
						$data["payabletime"] = $postdate["payabletime"][$key] ? strtotime($postdate["payabletime"][$key]) : 0;
						$data["arrivaltime"] = $postdate["arrivaltime"][$key] ? strtotime($postdate["arrivaltime"][$key]) : 0;
						$data["pay_real"] = intval(str_replace(",", "", $postdate["pay_real"][$key]));
						$data["pay_overdraft"] = intval(str_replace(",", "", $postdate["pay_overdraft"][$key]));
						$total += $data["pay_real"];
						if(!$val){
							$obj_fund->create($data);
						}else{
							$obj_fund->update(array("id"=>$val), $data);
						}
					}
					$isallow = $total >= $this->client_rs["housefound"] ? 1 : 0;
					$obj_client->update(array("id"=>$client_id), array("allow_finish"=>$isallow));
					spClass('user_log')->save_log(3, "将更新了客户 ".$this->client_rs['realname']." [id:$client_id] 的房款明细", array("client_id"=>$client_id));
					$obj_fund->getDb()->commitTrans();
					$message = array('msg'=>"房屋款项更新成功", "url"=>spUrl("clientoverseas", "clientfundlist", array("client_id"=>$client_id)), 'result'=>1);
					echo json_encode($message);
					exit();
				}catch(Exception $e){
					$obj_fund->getDb()->rollbackTrans();
					$message = array('msg'=>$e->getMessage(), 'result'=>0);
					echo json_encode($message);
					exit();
				}
			}
			$this->housefundtime = $this->client_rs["housefundtime"];
			$postdate = $this->spArgs();
			$page = intval(max($postdate['page'], 1));
			$housefund_rs = $obj_fund->join("crm_client_fund_type")->join("crm_user")->join("crm_client")->findAll(array("crm_client_fund.client_id"=>$client_id, "crm_client_fund_type.ishouse"=>1, "crm_client.user_overseas_id"=>$_SESSION["sscrm_user"]["id"]), "crm_client_fund.createtime asc", "crm_client_fund.*, crm_client.houseunit, crm_user.realname as realname_create, crm_client_fund_type.tname");
			$fund_rs = $obj_fund->join("crm_client_fund_type")->join("crm_user")->join("crm_client")->findAll(array("crm_client_fund.client_id"=>$client_id, "crm_client_fund_type.ishouse"=>0, "crm_client.user_overseas_id"=>$_SESSION["sscrm_user"]["id"]), "crm_client_fund.createtime asc", "crm_client_fund.*, crm_client.houseunit, crm_user.realname as realname_create, crm_client_fund_type.tname");
			$this->fund_rs = $fund_rs;
			$this->housefund_rs = $housefund_rs;
			$this->pager = $obj_fund->spPager()->getPager();
			$this->client_id = $client_id;
			$this->saveurl = spUrl('clientoverseas', 'clientfundlist');
		}catch(Exception $e){
			$this->redirect(spUrl("clientoverseas", "myclientlist"), $e->getMessage());
		}
	}
	
	public function clientfundviewlist(){
		die("close");
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_client = spClass("client");
			$obj_fund = spClass("client_fund");
			$obj_country = spClass("country");
			if(!$this->client_rs = $obj_client->getOverSeasClientById($client_id))
				throw new Exception("找不到该客户");
			if($this->client_rs["ishouse_create"] != 1)
				throw new Exception("该客户已未添加房款，无法查看");
			if($this->client_rs["housefundtime"] < 1)
				throw new Exception("房款总数不能小于1次");
			if($this->client_rs["process_id"] != 3)
				throw new Exception("该客户不处于已完成状态，无法进行该操作");
			$housefund_rs = $obj_fund->join("crm_client_fund_type")->join("crm_user")->join("crm_client")->findAll(array("crm_client_fund.client_id"=>$client_id, "crm_client_fund_type.ishouse"=>1, "crm_client.user_overseas_id"=>$_SESSION["sscrm_user"]["id"]), "crm_client_fund.createtime asc", "crm_client_fund.*, crm_client.houseunit, crm_user.realname as realname_create, crm_client_fund_type.tname");
			$fund_rs = $obj_fund->join("crm_client_fund_type")->join("crm_user")->join("crm_client")->findAll(array("crm_client_fund.client_id"=>$client_id, "crm_client_fund_type.ishouse"=>0, "crm_client.user_overseas_id"=>$_SESSION["sscrm_user"]["id"]), "crm_client_fund.createtime asc", "crm_client_fund.*, crm_client.houseunit, crm_user.realname as realname_create, crm_client_fund_type.tname");
			$this->fund_rs = $fund_rs;
			$this->housefund_rs = $housefund_rs;
			$this->client_id = $client_id;
			$this->url = spUrl('clientoverseas', 'clientfundlist', array("client_id"=>$client_id));
		}catch(Exception $e){
			$this->redirect(spUrl("clientoverseas", "myclientlist"), $e->getMessage());
		}
	}
	
	//添加款项，已添加房款的客户
	public function createfund(){
		die("close");
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_client = spClass("client");
			$obj_fund = spClass("client_fund");
			$obj_fund_type = spClass("client_fund_type");
			if(!$this->client_rs = $obj_client->getOverSeasClientById($client_id))
				throw new Exception("找不到该客户");
			if($this->client_rs["ishouse_create"] != 1)
				throw new Exception("该客户未添加房款，无法查看");
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try {
					$postdata = $this->spArgs();
					$data = array();
					$data["create_id"] = $_SESSION["sscrm_user"]["id"];
					$data["client_id"] = intval($client_id);
					$data["fund_type"] = intval($postdata["fund_type"]);
					$data["pay_standard"] = intval(str_replace(",", "", $postdata["pay_standard"]));
					$data["payabletime"] = strtotime($postdata["payabletime"]);
					$data["arrivaltime"] = strtotime($postdata["arrivaltime"]);
					$data["pay_real"] = intval(str_replace(",", "", $postdata["pay_real"]));
					$data["pay_overdraft"] = intval(str_replace(",", "", $postdata["pay_overdraft"]));
					$data["createtime"] = time();
					if($result = $obj_fund->spValidator($data)){
						foreach($result as $item) {
							throw new Exception($item[0]);
							break;
						}
					}
					if(!$data["fund_type"])
						throw new Exception("请选择客户款项类型");
					if(!$obj_fund_type->getunhouseinfo($data["fund_type"]))
						throw new Exception("请选择可用的款项类型");
					if(!$obj_fund->create($data))
						throw new Exception("未知错误，客户款项添加失败");
					$total_house = $obj_fund->getHousefundTotal($client_id);
					if($total_house >= $this->client_rs["housefund"] && $this->client_rs["allow_finish"] == 0)
						$obj_client->update(array("id"=>$client_id), array("allow_finish"=>1));
					spClass('user_log')->save_log(3, "添加了客户 ".$this->client_rs['realname']." [id:$client_id] 的房款明细", array("client_id"=>$client_id));
					$message = array('msg'=>"客户款项添加成功", 'result'=>1, "url"=>spUrl("clientoverseas", "clientfundlist", array("client_id"=>$client_id)));
					echo json_encode($message);
					exit();
				}catch(Exception $e){
					$message = array('msg'=>$e->getMessage(), 'result'=>0);
					echo json_encode($message);
					exit();
				}
			}
			$this->type_rs = $obj_fund_type->getunhouselist();
			$this->validator = $obj_fund->getValidatorJS();
			$this->client_id = $client_id;
			$this->saveurl = spUrl("clientoverseas", "createfund");
		}catch(Exception $e){
			$this->redirect(spUrl("clientoverseas", "myclientlist"), $e->getMessage());
		}
	}
	
	public function clientfinish(){
		die("close");
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_client = spClass("client");
			if(!$this->client_rs = $obj_client->getOverSeasClientById($client_id))
				throw new Exception("找不到该客户");
			if($this->client_rs["ishouse_create"] != 1)
				throw new Exception("该客户已未添加房款，无法查看");
			if($this->client_rs["process_id"] != 2)
				throw new Exception("该客户不处于移交到海外状态，无法进行该操作");
			if($this->client_rs["allow_finish"] != 1)
				throw new Exception("该客户不处于可完成状态，无法进行该操作");
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try {
					$postdata = $this->spArgs();
					$data = array();
					$data["room_certificate"] = intval($postdata["room_certificate"]);
					$data["room_certificate_time"] = strtotime($postdata["room_certificate_time"]);
					$data["room_key"] = intval($postdata["room_key"]);
					$data["room_key_time"] = strtotime($postdata["room_key_time"]);
					$data["user_finish_id"] = $_SESSION["sscrm_user"]["id"];
					$data["finishtime"] = time();
					$data["process_id"] = 3;
					if(!$data["room_certificate"])
						throw new Exception("必须拿到房本才能将客户设为完成状态");
					if(!$postdata["room_certificate_time"] || !$data["room_certificate_time"] < 0)
						throw new Exception("请填写拿到房本时间");
					if(!$data["room_key"])
						throw new Exception("必须拿到钥匙才能将客户设为完成状态");
					if(!$postdata["room_key_time"] || !$data["room_key_time"] < 0)
						throw new Exception("请填写拿到钥匙时间");
					if(!$obj_client->update(array("id"=>$client_id), $data))
						throw new Exception("未知错误，客户更新失败");
					spClass('user_log')->save_log(3, "将客户 ".$this->client_rs['realname']." [id:$client_id] 设为已完成", array("client_id"=>$client_id));
					$message = array('msg'=>"客户状态修改完成", 'result'=>1, "url"=>spUrl("clientoverseas", "myclientlist"));
					echo json_encode($message);
					exit();
				}catch(Exception $e){
					$message = array('msg'=>$e->getMessage(), 'result'=>0);
					echo json_encode($message);
					exit();
				}
			}
			$this->saveurl = spUrl("clientoverseas", "clientfinish");
			$this->client_id = $client_id;
		}catch(Exception $e){
			$this->redirect(spUrl("clientoverseas", "myclientlist"), $e->getMessage());
		}
	}
}
?>