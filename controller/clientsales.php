<?php
class clientsales extends spController {
	
	public function __construct(){
		parent::__construct();
		try {
			$obj_cpt = spClass("department_competence");
			$obj_cpt->check_login_competence("CLIENTSALE");
			$obj_idt = spClass("user_identity");
			$obj_idt->check_login_competence("getclient");
			$this->controller = "clientsales";
			$this->clistpayedurl = spUrl($this->controller, "mypayclientlist");
			$this->clisturl = spUrl($this->controller, "myclientlist");
		}catch(Exception $e){
			$backurl = spUrl("main", "welcome");
			$this->redirect($backurl, $e->getMessage());
			exit();
		}
	}
	
	public function myclientlist(){
		$obj_user = spClass("user");
		$obj_client = spClass('client');
		$obj_origin = spClass('origin');
		$obj_process = spClass('client_process');
		$obj_country = spClass('country');
		$obj_record = spClass("client_record");
		$obj_intrecord = spClass("client_intention_record");
		$obj_level = spClass("client_level");
		$obj_od = spClass('client_overtime');
		$obj_comactive = spClass('comactive');
		$postdate = $this->spArgs();
		$page = intval(max($postdate['page'], 1));
		$condition = "crm_client.user_sales_id = ".$_SESSION["sscrm_user"]["id"] . " and crm_client.isoverdate = 0 and crm_client.isdel = 0 and crm_client.ispool = 0";
		$condition .= " and crm_client.ispay = 0";
		if($postdate['ispay'].'a' !== 'a'){
			$condition .= " and crm_client.ispay = ".intval($postdate['ispay']);
			$this->ispay = $postdate['ispay'];
		}
		if($level_id = intval($postdate['level_id'])){
			$condition .= " and crm_client.level_id = {$level_id}";
			$this->level_id = $level_id;
		}
		if($comactive_id = intval($postdate['comactive_id'])){
			$condition .= " and crm_client.comactive_id = {$comactive_id}";
			$this->comactive_id = $comactive_id;
		}
		$sort = 'crm_client.poolouttime desc';
		if($postdate['sort'].'a' !== 'a'){
			switch ($postdate['sort']){
				case "overdate_desc":
					$sort = "overdate desc, ".$sort;
					$this->sort = $postdate['sort'];
				break;
				case "level_asc":
					$sort = "crm_client_level.sort asc, ".$sort;
					$this->sort = $postdate['sort'];
				break;
				case "level_desc":
					$sort = "crm_client_level.sort desc, ".$sort;
					$this->sort = $postdate['sort'];
				break;
				case "plan_desc":
					$sort = "count(crm_client_plan.id) desc, ".$sort;
					$this->sort = $postdate['sort'];
				break;
				default:
					
				break;
			}
		}
		if($channel_main_id = intval($postdate["channel_main_id"])){
			$condition .= " and crm_channel.maintenance_id = $channel_main_id";
			$this->channel_main_id = $channel_main_id;
		}
		/*
		if($postdate['isoversea'].'a' !== 'a'){
			$isoversea = intval($postdate['isoversea']) == 1 ? 2 : 1; 
			$condition .= " and crm_client.process_id = $isoversea";
			$this->isoversea = $isoversea;
		}
		if($postdate['process_id'].'a' !== 'a'){
			$condition .= " and crm_client.process_id = ".$postdate['process_id'];
			$this->process_id = $postdate['process_id'];
		}
		*/
		if($postdate['searchkey'] != '')
			$condition .= " and (crm_client.realname like '%{$postdate['searchkey']}%' or crm_client.telphone like '%{$postdate['searchkey']}%' or crm_channel.mechanism like '%{$postdate['searchkey']}%' or crm_user.realname = '{$postdate['searchkey']}')";
		//if($client_rs = $obj_client->join("crm_client_level")->join("crm_client_overtime", "crm_client_overtime.client_id = crm_client.id and crm_client_overtime.endtime = 0", "left")->join("crm_user")->join("crm_channel", "crm_channel.id = crm_client.channel_id and crm_client.channel_id > 0", "left")->join("crm_credential")->join("crm_client_process", "crm_client_process.id = crm_client.process_id")->spPager($page, 20)->findAll($condition, $sort, "crm_client.*, crm_client_level.name as level_name, crm_client_overtime.fromtime, crm_credential.cname, crm_user.realname as realname_create, crm_client_process.pname, crm_channel.mechanism, IF(crm_client.ispay = 0, datediff(curdate(), FROM_UNIXTIME(crm_client.overdatestart, '%Y-%m-%d')), 0) as overdate")){
		if($client_rs = $obj_client->join("crm_client_seehouse")->join("crm_client_level")->join("crm_client_overtime", "crm_client_overtime.client_id = crm_client.id and crm_client_overtime.endtime = 0", "left")->join("crm_user")->join("crm_channel", "crm_channel.id = crm_client.channel_id and crm_client.channel_id > 0", "left")->join("crm_client_plan", "crm_client.id = crm_client_plan.client_id and crm_client_plan.typeid = 2 and find_in_set({$_SESSION["sscrm_user"]["id"]}, crm_client_plan.main_id) and crm_client_plan.isfinish = 0 and crm_client_plan.starttime <= UNIX_TIMESTAMP()", "left")->join("crm_credential")->join("crm_client_process", "crm_client_process.id = crm_client.process_id")->spPager($page, 20)->findAll($condition, $sort, "crm_client.*, crm_client_level.name as level_name, crm_client_seehouse.see_status, count(crm_client_plan.id) as plan_count, crm_client_overtime.fromtime, crm_credential.cname, crm_user.realname as realname_create, crm_client_process.pname, crm_channel.mechanism, IF(crm_client.ispay = 0, datediff(curdate(), FROM_UNIXTIME(crm_client.overdatestart, '%Y-%m-%d')), 0) as overdate", "crm_client.id")){
			foreach($client_rs as $key => $val){
				if($val["sourcetype"] == 1){
					$client_rs[$key]["oname"] = "渠道来源";
				}elseif($val["sourcetype"] == 2){
					$origin_rs = $obj_origin->findByPk($val["origin_id"]);
					$client_rs[$key]["oname"] = $origin_rs["oname"];
					if($val["exp_country_id"]){
						$client_rs[$key]["ctname"] = $obj_country->getname($val["exp_country_id"]);
					}
				}
				$client_rs[$key]["record_count"] = $obj_record->getCountById($val["id"]);
				$client_rs[$key]["overtime_count"] = $obj_od->getCount($val["id"]);
				$client_rs[$key]["record_call_count"] = $obj_intrecord->getCountByClientId($val["id"]);
			}
			$this->client_rs = $client_rs;
		}
		$this->comactive_rs = $obj_comactive->getlist();
		$this->level_rs = $obj_level->getlist();
		$this->channel_prep_rs = $obj_user->getUser_prep("depart_id = 2");
		$this->process_rs = $obj_process->getlist();
		$this->pager = $obj_client->spPager()->getPager();
		$this->searchkey = $postdate['searchkey'];
		$this->url = spUrl('clientsales', 'myclientlist', array("searchkey"=>$this->searchkey, "level_id"=>$this->level_id, "ispay"=>$this->ispay, "channel_main_id"=>$this->channel_main_id, "sort"=>$this->sort, "process_id"=>$this->process_id, "isoversea"=>$this->isoversea, "comactive_id"=>$this->comactive_id));
	}
	
	public function mypayclientlist(){
		$obj_user = spClass("user");
		$obj_client = spClass('client');
		$obj_origin = spClass('origin');
		$obj_process = spClass('client_process');
		$obj_country = spClass('country');
		$obj_record = spClass("client_record");
		$obj_intrecord = spClass("client_intention_record");
		$obj_level = spClass("client_level");
		$obj_od = spClass('client_overtime');
		$obj_comactive = spClass('comactive');
		$postdate = $this->spArgs();
		$page = intval(max($postdate['page'], 1));
		$condition = "crm_client.user_sales_id = ".$_SESSION["sscrm_user"]["id"] . " and crm_client.isoverdate = 0 and crm_client.isdel = 0 and crm_client.ispool = 0";
		$condition .= " and crm_client.ispay >= 1";
		if($postdate['ispay'].'a' !== 'a'){
			$condition .= " and crm_client.ispay = ".intval($postdate['ispay']);
			$this->ispay = $postdate['ispay'];
		}
		if($level_id = intval($postdate['level_id'])){
			$condition .= " and crm_client.level_id = {$level_id}";
			$this->level_id = $level_id;
		}
		if($comactive_id = intval($postdate['comactive_id'])){
			$condition .= " and crm_client.comactive_id = {$comactive_id}";
			$this->comactive_id = $comactive_id;
		}
		$sort = 'crm_client.poolouttime desc';
		if($postdate['sort'].'a' !== 'a'){
			switch ($postdate['sort']){
				case "overdate_desc":
					$sort = "overdate desc, ".$sort;
					$this->sort = $postdate['sort'];
					break;
				case "level_asc":
					$sort = "crm_client_level.sort asc, ".$sort;
					$this->sort = $postdate['sort'];
					break;
				case "level_desc":
					$sort = "crm_client_level.sort desc, ".$sort;
					$this->sort = $postdate['sort'];
					break;
				case "plan_desc":
					$sort = "count(crm_client_plan.id) desc, ".$sort;
					$this->sort = $postdate['sort'];
					break;
				default:
						
					break;
			}
		}
		if($channel_main_id = intval($postdate["channel_main_id"])){
			$condition .= " and crm_channel.maintenance_id = $channel_main_id";
			$this->channel_main_id = $channel_main_id;
		}
		if($postdate['searchkey'] != '')
			$condition .= " and (crm_client.realname like '%{$postdate['searchkey']}%' or crm_client.telphone like '%{$postdate['searchkey']}%' or crm_channel.mechanism like '%{$postdate['searchkey']}%' or crm_user.realname = '{$postdate['searchkey']}')";
		//if($client_rs = $obj_client->join("crm_client_level")->join("crm_client_overtime", "crm_client_overtime.client_id = crm_client.id and crm_client_overtime.endtime = 0", "left")->join("crm_user")->join("crm_channel", "crm_channel.id = crm_client.channel_id and crm_client.channel_id > 0", "left")->join("crm_credential")->join("crm_client_process", "crm_client_process.id = crm_client.process_id")->spPager($page, 20)->findAll($condition, $sort, "crm_client.*, crm_client_level.name as level_name, crm_client_overtime.fromtime, crm_credential.cname, crm_user.realname as realname_create, crm_client_process.pname, crm_channel.mechanism, IF(crm_client.ispay = 0, datediff(curdate(), FROM_UNIXTIME(crm_client.overdatestart, '%Y-%m-%d')), 0) as overdate")){
		if($client_rs = $obj_client->join("crm_client_seehouse")->join("crm_client_level")->join("crm_client_overtime", "crm_client_overtime.client_id = crm_client.id and crm_client_overtime.endtime = 0", "left")->join("crm_user")->join("crm_channel", "crm_channel.id = crm_client.channel_id and crm_client.channel_id > 0", "left")->join("crm_client_plan", "crm_client.id = crm_client_plan.client_id and crm_client_plan.typeid = 2 and find_in_set({$_SESSION["sscrm_user"]["id"]}, crm_client_plan.main_id) and crm_client_plan.isfinish = 0 and crm_client_plan.starttime <= UNIX_TIMESTAMP()", "left")->join("crm_credential")->join("crm_client_process", "crm_client_process.id = crm_client.process_id")->spPager($page, 20)->findAll($condition, $sort, "crm_client.*, crm_client_level.name as level_name, crm_client_seehouse.see_status, count(crm_client_plan.id) as plan_count, crm_client_overtime.fromtime, crm_credential.cname, crm_user.realname as realname_create, crm_client_process.pname, crm_channel.mechanism, IF(crm_client.ispay = 0, datediff(curdate(), FROM_UNIXTIME(crm_client.overdatestart, '%Y-%m-%d')), 0) as overdate", "crm_client.id")){
			foreach($client_rs as $key => $val){
				if($val["sourcetype"] == 1){
					$client_rs[$key]["oname"] = "渠道来源";
				}elseif($val["sourcetype"] == 2){
					$origin_rs = $obj_origin->findByPk($val["origin_id"]);
					$client_rs[$key]["oname"] = $origin_rs["oname"];
					if($val["exp_country_id"]){
						$client_rs[$key]["ctname"] = $obj_country->getname($val["exp_country_id"]);
					}
				}
				$client_rs[$key]["record_count"] = $obj_record->getCountById($val["id"]);
				$client_rs[$key]["overtime_count"] = $obj_od->getCount($val["id"]);
				if($val["user_teler_id"])
					$client_rs[$key]["record_call_count"] = $obj_intrecord->getCountByClientId($val["id"]);
			}
			$this->client_rs = $client_rs;
		}
		$this->comactive_rs = $obj_comactive->getlist();
		$this->level_rs = $obj_level->getlist();
		$this->channel_prep_rs = $obj_user->getUser_prep("depart_id = 2");
		$this->process_rs = $obj_process->getlist();
		$this->pager = $obj_client->spPager()->getPager();
		$this->searchkey = $postdate['searchkey'];
		$this->url = spUrl('clientsales', 'mypayclientlist', array("searchkey"=>$this->searchkey, "level_id"=>$this->level_id, "ispay"=>$this->ispay, "channel_main_id"=>$this->channel_main_id, "sort"=>$this->sort, "process_id"=>$this->process_id, "isoversea"=>$this->isoversea, "comactive_id"=>$this->comactive_id));
	}
	
	public function viewmysalesclient(){
		try {
			if(!$id = intval($this->spArgs('id')))
				throw new Exception('请先选择查看项，再进行操作！');
			$obj_user = spClass("user");
			$obj_channel = spClass("channel");
			$obj_client = spClass("client");
			$obj_origin = spClass("origin");
			$obj_cred = spClass("credential");
			$obj_country = spClass('country');
			$obj_trader = spClass('trader');
			if(!$client_rs = $obj_client->getMySalesClientById($id))
				throw new Exception("找不到该客户");
			if($client_rs["ispay"])
				throw new Exception("该客户已下订单，无法修改");
			if($client_rs["is_protocol"])
				throw new Exception("该客户已添加协议，无法修改");
			if($client_rs["isoverdate"])
				throw new Exception("该客户已过期，无法修改");
			$this->client_rs = $client_rs;
			$this->cred_rs = $obj_cred->get_credential();
			$this->country_rs = $obj_country->getlist();
			$this->user_getclient_prep_rs = $obj_user->getUser_prep("find_in_set('getclient', crm_user.identity_attr)");
			$this->user_abroad_prep_rs = $obj_user->getUser_prep("find_in_set('abroad', crm_user.identity_attr)");
			$this->origin_rs = $obj_origin->getOriginById($client_rs["origin_id"]);
			$extdata = array();
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
			if($this->origin_rs["isdatafrom"])
				$this->user_prep_rs = $obj_user->getUser_prep();
			switch ($client_rs["sourcetype"]){
				case "1":
					if($_SERVER['REQUEST_METHOD'] == 'POST'){
						try {
							throw new Exception("咱无该方式的来源");
							$postdate = $this->spArgs();
							$data = array();
							$data['updatetime'] = time();
							$data['realname'] = $postdate['realname'];
							$data['telphone'] = $this->client_rs['telphone'];
							$data["user_abroad_id"] = intval($postdate['user_abroad_id']);
							$data["user_tours_id"] = intval($postdate['user_tours_id']);
							$data['cred_id'] = intval($postdate['cred_id']);
							$data['cred_license'] = $postdate['cred_license'];
							$data['address'] = $postdate['address'];
							$data['profession'] = $postdate['profession'];
							$data['email'] = $postdate['email'];
							$data['wechat'] = $postdate['wechat'];
							$data['exp_country_id'] = intval($postdate['exp_country_id']);
							$data['demand'] = $postdate['demand'];
							$data['feedback'] = $postdate['feedback'];
							print_r($data);exit();
							if($postdate['visit_time'])
								$data['visit_time'] = strtotime($postdate['visit_time']);
							if($result = $obj_client->getValidatorForOrigin()->spValidator($data)){
								foreach($result as $item) {
									throw new Exception($item[0]);
									break;
								}
							}
							if($data['exp_country_id'] && !$cinfo_rs = $obj_country->getinfoById($data['exp_country_id']))
								throw new Exception("找不到该国家");
							if(!$obj_client->update(array("id"=>$id), $data))
								throw new Exception("未知错误，客户更新失败");
							spClass('user_log')->save_log(3, "更新了客户 ".$data['realname']." [id:$id] 的资料", array("client_id"=>$id));
							$message = array('msg'=>"客户更新成功", 'result'=>1, "url"=>spUrl("clientsales", "myclientlist"));
							echo json_encode($message);
							exit();
						}catch (Exception $e){
							$message = array('msg'=>$e->getMessage(), 'result'=>0);
							echo json_encode($message);
							exit();
						}
					}
					$this->id = $id;
					$this->validator = $obj_client->getValidatorForOriginJS();
					$this->saveurl = spUrl("clientsales", "viewmysalesclient");
					$this->display("clientsales/viewclient_channel.html");
				break;
				case "2":
					$postdate = $this->spArgs();
					if($_SERVER['REQUEST_METHOD'] == 'POST'){
						try {
							$data = array();
							$data['updatetime'] = time();
							$data['realname'] = $postdate['realname'];
							$data['tel_location'] = $postdate['tel_location'];
							$data['telphone'] = $this->client_rs['telphone'];
							$data['user_datafrom_id'] = intval($postdate['user_datafrom_id']);
							$data["user_abroad_id"] = intval($postdate['user_abroad_id']);
							$data["user_tours_id"] = intval($postdate['user_tours_id']);
							$data['cred_id'] = intval($postdate['cred_id']);
							$data['cred_license'] = $postdate['cred_license'];
							$data['address'] = $postdate['address'];
							$data['profession'] = $postdate['profession'];
							$data['email'] = $postdate['email'];
							$data['wechat'] = $postdate['wechat'];
							$data['exp_country_id'] = intval($postdate['exp_country_id']);
							$data['demand'] = $postdate['demand'];
							$data['feedback'] = $postdate['feedback'];
							if($postdate['visit_time'])
								$data['visit_time'] = strtotime($postdate['visit_time']);
							$data = array_merge($data, $extdata);
							if($result = $obj_client->getValidatorForChannelUpdate()->spValidator($data)){
								foreach($result as $item) {
									throw new Exception($item[0]);
									break;
								}
							}
							if($this->origin_rs["isdatafrom"] && !$data['user_datafrom_id'])
								throw new Exception("请选择资料来源人");
							if($data['exp_country_id'] && !$cinfo_rs = $obj_country->getinfoById($data['exp_country_id']))
								throw new Exception("找不到该国家");
							if($ext_field_rs){
								foreach ($ext_field_rs as $val){
									if($val["demand"] == "required" && !$data[$val[field]])
										throw new Exception($val["fieldname"]."不能为空");
								}
							}
							if(!$obj_client->update(array("id"=>$id), $data))
								throw new Exception("未知错误，客户更新失败");
							spClass('user_log')->save_log(3, "更新了客户 ".$data['realname']." [id:$id] 的资料", array("client_id"=>$id));
							$message = array('msg'=>"客户更新成功", 'result'=>1, "url"=>spUrl("clientsales", "myclientlist"));
							echo json_encode($message);
							exit();
						}catch (Exception $e){
							$message = array('msg'=>$e->getMessage(), 'result'=>0);
							echo json_encode($message);
							exit();
						}
					}
					$this->from_rs = $obj_origin->getClientViewFrom($this->origin_rs, $client_rs);
					$this->id = $id;
					$this->validator = $obj_client->getValidatorForChannelUpdateJS();
					$this->saveurl = spUrl("clientsales", "viewmysalesclient");
					$this->display("clientsales/viewclient.html");
				break;
				case "3":
					throw new Exception("错误的来源通道");
				break;
			}
		}catch(Exception $e){
			$this->redirect($_SERVER['HTTP_REFERER'], $e->getMessage());
		}
	}

	public function viewmysalesclientonly(){
		try {
			if(!$id = intval($this->spArgs('id')))
				throw new Exception('请先选择查看项，再进行操作！');
			$obj_client = spClass("client");
			$obj_origin = spClass("origin");
			$obj_cred = spClass("credential");
			$obj_country = spClass('country');
			if(!$client_rs = $obj_client->getMySalesClientById($id))
				throw new Exception("找不到该客户");
			$this->client_rs = $client_rs;
			$this->cred_rs = $obj_cred->get_credential();
			$this->country_rs = $obj_country->getlist();
			switch ($client_rs["sourcetype"]){
				case "1":
					$this->display("clientsales/viewclientonly_channel.html");
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
					$this->display("clientsales/viewclientonly.html");
					break;
				case "3":
					throw new Exception("错误的来源通道");
					break;
			}
		}catch(Exception $e){
			$this->redirect($_SERVER['HTTP_REFERER'], $e->getMessage());
		}
	}
	
	public function againclient(){
		try {
			if(!$id = intval($this->spArgs('id')))
				throw new Exception('请先选择查看项，再进行操作！');
			$obj_client = spClass("client");
			$obj_origin = spClass("origin");
			$obj_cred = spClass("credential");
			$obj_country = spClass('country');
			$obj_user = spClass("user");
			$obj_channel = spClass("channel");
			$obj_notice = spClass("user_notice");
			if(!$client_rs = $obj_client->getMySalesClientById($id))
				throw new Exception("找不到该客户");
			$this->client_rs = $client_rs;
			$this->cred_rs = $obj_cred->get_credential();
			$this->country_rs = $obj_country->getlist();
			if(!$client_rs = $obj_client->getMySalesClientById($id))
				throw new Exception("找不到该客户");
			if(!$client_rs["is_protocol"])
				throw new Exception("该客户未签署协议，无法再加一单");
			$this->client_rs = $client_rs;
			$this->cred_rs = $obj_cred->get_credential();
			$this->country_rs = $obj_country->getlist();
			$this->user_getclient_prep_rs = $obj_user->getUser_prep("find_in_set('getclient', crm_user.identity_attr)");
			$this->user_abroad_prep_rs = $obj_user->getUser_prep("find_in_set('abroad', crm_user.identity_attr)");
			$this->user_prep_rs = $obj_user->getUser_prep();
			switch ($client_rs["sourcetype"]){
				case "1":
					throw new Exception("错误的来源通道");
				break;
				case "2":
					$postdate = $this->spArgs();
					$this->origin_rs = $obj_origin->getOriginById($client_rs["origin_id"]);
					$extdata = array();
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
					if($_SERVER['REQUEST_METHOD'] == 'POST'){
						$data = array();
						$data['create_id'] = $_SESSION["sscrm_user"]["id"];
						$data['overdatestart'] = $data["poolouttime"] = $data['createtime'] = time();
						$data["sourcetype"] = 2;
						$data['origin_id'] = $this->client_rs["origin_id"];
						$data['channel_id'] = $this->client_rs["channel_id"];
						$data['channelact_id'] = $this->client_rs["channelact_id"];
						$data['user_datafrom_id'] = $this->client_rs["user_datafrom_id"];
						$data['trader_id'] = $this->client_rs['trader_id'];
						$data['travel_id'] = $this->client_rs['travel_id'];
						$data['realname'] = $postdate['realname'];
						$data['sex'] = $this->client_rs['sex'];
						$data['telphone'] = $this->client_rs['telphone'];
						$data['cred_id'] = intval($postdate['cred_id']);
						$data['cred_license'] = $postdate['cred_license'];
						$data['address'] = $postdate['address'];
						$data['profession'] = $postdate['profession'];
						$data['email'] = $postdate['email'];
						$data['wechat'] = $postdate['wechat'];
						$data['exp_country_id'] = intval($postdate['exp_country_id']);
						$data['demand'] = $postdate['demand'];
						$data['feedback'] = $postdate['feedback'];
						$data['visit_time'] = $this->client_rs['visit_time'];
						$data['user_sales_id'] = $_SESSION["sscrm_user"]["id"];
						$data["user_abroad_id"] = intval($postdate['user_abroad_id']);
						$data["user_tours_id"] = intval($postdate['user_tours_id']);
						$data = array_merge($data, $extdata);
						if($result = $obj_client->getValidatorForOrigin()->spValidator($data)){
							foreach($result as $item) {
								throw new Exception($item[0]);
								break;
							}
						}
						if($ext_field_rs){
							foreach ($ext_field_rs as $val){
								if($val["demand"] == "required" && !$data[$val[field]])
									throw new Exception($val["fieldname"]."不能为空");
							}
						}
						$obj_client->getDb()->beginTrans();
						if(!$new_id = $obj_client->create($data))
							throw new Exception("未知错误，客户添加失败");
						$obj_notice->send_notice($data["user_sales_id"], "客户 ".$data[realname]." 被复制（再加一单）后分配到置业顾问", "客户 ".$data[realname]." 被 " . $_SESSION["sscrm_user"]["realname"] . " 创建后已分配给您");
						spClass('user_log')->save_log(3, "复制（再加一单）了来源为 ".$this->origin_rs["oname"]." 的客户 ".$data['realname']." [id:$new_id](原id:$id)", array("client_id"=>$new_id));
						$obj_client->getDb()->commitTrans();
						$message = array('msg'=>"客户添加成功", 'result'=>1, "url"=>spUrl("clientsales", "myclientlist"));
						echo json_encode($message);
						exit();
					}
					$sex = array("1"=>"男", "2"=>"女");
					$this->ext_position = "[" . $this->client_rs["realname"] . " / " . ($sex[$this->client_rs[sex]] ? $sex[$this->client_rs[sex]] : "未知") . " / " . $this->client_rs["telphone"] . "]" . " - [再加一单]";
					$this->from_rs = $obj_origin->getClientViewFrom($this->origin_rs, $client_rs);
					$this->id = $id;
					$this->display("clientsales/viewclient.html");
				break;
				case "3":
					throw new Exception("错误的来源通道");
				break;
			}
		}catch(Exception $e){
			$this->redirect($_SERVER['HTTP_REFERER'], $e->getMessage());
		}
	}
	
	public function clientrecordlist(){
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_client = spClass("client");
			if(!$this->client_rs = $obj_client->getMySalesClientById($client_id))
				throw new Exception("找不到该客户");
			$obj_record = spClass("client_record");
			$postdate = $this->spArgs();
			$page = intval(max($postdate['page'], 1));
			$record_rs = $obj_record->join("crm_user")->join("crm_client")->spPager($page, 20)->findAll(array("crm_client_record.client_id"=>$client_id, "crm_client_record.rtype_id"=>1, "crm_client.user_sales_id"=>$_SESSION["sscrm_user"]["id"]), "crm_client_record.createtime asc", "crm_client_record.*, crm_user.realname as realname_create");
			$this->record_rs = $record_rs;
			$this->pager = $obj_record->spPager()->getPager();
			$this->client_id = $client_id;
			$this->url = spUrl('clientsales', 'clientrecordlist', array("client_id"=>$client_id));
			$this->modify = 1;
			if($this->client_rs["ispay"] >= 1)
				$this->backurl = spUrl("clientsales", "mypayclientlist");
		}catch(Exception $e){
			$this->redirect(spUrl("clientsales", "myclientlist"), $e->getMessage());
		}
	}
	
	public function createrecord(){
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_client = spClass("client");
			if(!$this->client_rs = $obj_client->getMySalesClientById($client_id))
				throw new Exception("找不到该客户");
			$obj_record = spClass("client_record");
			$obj_od = spClass("client_overtime");
			$postdate = $this->spArgs();
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try {
					$data = array();
					$data["client_id"] = $client_id;
					$data["rtype_id"] = 1;
					$data["create_id"] = $_SESSION["sscrm_user"]["id"];
					$data["content"] = $postdate["content"];
					//$data["acttime"] = strtotime($postdate["acttime"]);
					$data["acttime"] = $data["createtime"] = time();
					if($result = $obj_record->spValidator($data)){
						foreach($result as $item) {
							throw new Exception($item[0]);
							break;
						}
					}
					if(!$obj_record->create($data))
						throw new Exception("未知错误，跟踪记录添加失败");
					if($od_rs = $obj_od->find(array("client_id"=>$client_id, "endtime"=>0)))
						$obj_od->update(array("id"=>$od_rs["id"]), array("endtime"=>time()));
					spClass('user_log')->save_log(3, "添加了客户 ".$this->client_rs['realname']." [id:$client_id] 的跟踪记录", array("client_id"=>$client_id));
					$backurl = $postdate["backurl"] ? $postdate["backurl"] : spUrl("clientsales", "clientrecordlist", array("client_id"=>$client_id));
					$message = array('msg'=>"跟踪记录添加成功", 'result'=>1, "url"=>$backurl);
					echo json_encode($message);
					exit();
				}catch(Exception $e){
					$message = array('msg'=>$e->getMessage(), 'result'=>0);
					echo json_encode($message);
					exit();
				}
			}
			if($postdate["showlist"]){
				$page = intval(max($postdate['page'], 1));
				$record_rs = $obj_record->join("crm_user")->join("crm_client")->spPager($page, 10)->findAll(array("crm_client_record.client_id"=>$client_id, "crm_client_record.rtype_id"=>1, "crm_client.user_sales_id"=>$_SESSION["sscrm_user"]["id"]), "crm_client_record.createtime asc", "crm_client_record.*, crm_user.realname as realname_create");
				$this->record_rs = $record_rs;
				$this->pager = $obj_record->spPager()->getPager();
				$this->url = spUrl('clientsales', 'createrecord', array("client_id"=>$client_id, "showlist"=>1));
				$this->showlist = 1;
				$this->backurl = spUrl("clientsales", "allodlist");
			}
			$this->validator = $obj_record->getValidatorJS();
			$this->client_id = $client_id;
			$this->saveurl = spUrl("clientsales", "createrecord");
		}catch(Exception $e){
			$this->redirect(spUrl("clientsales", "myclientlist"), $e->getMessage());
		}
	}
	
	public function modifyrecord(){
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			if(!$id = intval($this->spArgs("id")))
				throw new Exception("参数丢失");
			$obj_client = spClass("client");
			if(!$this->client_rs = $obj_client->getMySalesClientById($client_id))
				throw new Exception("找不到该客户");
			$obj_record = spClass("client_record");
			if(!$record_rs = $obj_record->find(array("client_id"=>$client_id, "id"=>$id)))
				throw new Exception("找不到该客户的回访记录，请联系系统管理员");
			if(date("Y-m-d", $record_rs["createtime"]) != date("Y-m-d", time()))
				throw new Exception("只能修改当天的回访记录");
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try {
					$postdate = $this->spArgs();
					$data = array();
					$data["acttime"] = $record_rs["acttime"];
					$data["content"] = $postdate["content"];
					if($result = $obj_record->spValidator($data)){
						foreach($result as $item) {
							throw new Exception($item[0]);
							break;
						}
					}
					if(!$obj_record->update(array("id"=>$id), $data))
						throw new Exception("未知错误，沟通记录修改失败");
					spClass('user_log')->save_log(3, "修改了客户 ".$this->client_rs['realname']." [id:$client_id] 的沟通记录[id:$id]", array("client_id"=>$client_id));
					$message = array('msg'=>"沟通记录修改成功", 'result'=>1, "url"=>spUrl("clientsales", "clientrecordlist", array("client_id"=>$client_id)));
					echo json_encode($message);
					exit();
				}catch(Exception $e){
					$message = array('msg'=>$e->getMessage(), 'result'=>0);
					echo json_encode($message);
					exit();
				}
			}
			$this->validator = $obj_record->getValidatorJS();
			$this->client_id = $client_id;
			$this->saveurl = spUrl("clientsales", "modifyrecord");
			$this->id = $id;
			$this->record_rs = $record_rs;
			$this->display("clientsales/createrecord.html");
		}catch(Exception $e){
			$this->redirect(spUrl("clientsales", "myclientlist"), $e->getMessage());
		}
	}
	
	//添加协议，只限处于跟进中的客户
	public function protocolform(){
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_client = spClass("client");
			$obj_origin = spClass("origin");
			$obj_fund = spClass("client_order_fund");
			$obj_cred = spClass("credential");
			$obj_country = spClass("country");
			if(!$this->client_rs = $obj_client->getMySalesClientById($client_id))
				throw new Exception("找不到该客户");
			if($this->client_rs["ispay"] != 0)
				throw new Exception("该客户不处于跟进中状态，无法添加协议");
			if(!$this->client_rs["exp_country_id"])
				throw new Exception("该客户未添加意向国家，无法添加协议");
			if(!$this->client_rs["visit_time"])
				throw new Exception("该客户未添加来访时间，无法添加协议");
			if(!$this->client_rs["demand"])
			throw new Exception("该客户未添加需求，无法添加协议");
			if($this->client_rs["isoverdate"])
				throw new Exception("该客户已过期，无法添加协议");
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try {
					$postdata = $this->spArgs();
					$data = array();
					$data["cash"] = intval(str_replace(",", "", $postdata["cash"]));
					$data["create_id"] = $_SESSION["sscrm_user"]["id"];
					$data["client_id"] = intval($client_id);
					$data["fund_type"] = "意向金";
					$data["pay_standard"] = intval(str_replace(",", "", $postdata["pay_standard"]));
					$data["payabletime"] = time();
					$data["arrivaltime"] = time();
					$data["pay_real"] = $data["pay_standard"];
					$data["pay_overdraft"] = 0;
					$data["createtime"] = time();
					if($data["pay_standard"] <= 0)
						throw new Exception("意向金不能为0");
					if($result = $obj_fund->spValidator($data)){
						foreach($result as $item) {
							throw new Exception($item[0]);
							break;
						}
					}
					$client_data =  array("is_protocol"=>1, "ispay"=>1);
					$client_data["cred_id"] = intval($postdata["cred_id"]);
					$client_data["cred_license"] = $postdata["cred_license"];
					$client_data["to_overseas_time"] = time();
					if(!$client_data["cred_license"])
						throw new Exception("请输入证件号码");
					$obj_fund->getDb()->beginTrans();
					if(!$obj_fund->create($data))
						throw new Exception("未知错误，客户款项添加失败");
					if(!$obj_client->update(array("id"=>$client_id),$client_data))
						throw new Exception("未知错误，客户协议添加失败");
					spClass('user_log')->save_log(3, "添加了客户 ".$this->client_rs['realname']." [id:$client_id] 的协议并添加意向金", array("client_id"=>$client_id));
					$obj_fund->getDb()->commitTrans();
					$message = array('msg'=>"客户协议添加成功", 'result'=>1, "url"=>spUrl("clientsales", "myclientlist"));
					echo json_encode($message);
					exit();
				}catch(Exception $e){
					$obj_fund->getDb()->rollbackTrans();
					$message = array('msg'=>$e->getMessage(), 'result'=>0);
					echo json_encode($message);
					exit();
				}
			}
			$this->country_rs = $obj_country->getlist();
			$this->cred_rs = $obj_cred->get_credential();
			$this->origin_rs = $obj_origin->getOriginById($this->client_rs["origin_id"]);
			$this->from_rs = $obj_origin->getClientViewFrom($this->origin_rs, $this->client_rs);
			$this->client_id = $client_id;
			$this->saveurl = spUrl("clientsales", "protocolform");
		}catch(Exception $e){
			$this->redirect(spUrl("clientsales", "myclientlist"), $e->getMessage());
		}
	}
	
	//添加订单，只限处于跟进中的客户
	public function clientpayform(){
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_client = spClass("client");
			$obj_origin = spClass("origin");
			if(!$this->client_rs = $obj_client->getMySalesClientById($client_id))
				throw new Exception("找不到该客户");
			if($this->client_rs["isform"] == 1)
				throw new Exception("该客户已下过订单，无法再次添加订单");
			if(!$this->client_rs["exp_country_id"])
				throw new Exception("该客户未添加意向国家，无法添加订单");
			if(!$this->client_rs["visit_time"])
				throw new Exception("该客户未添加来访时间，无法添加订单");
			if($this->client_rs["isoverdate"])
				throw new Exception("该客户已过期，无法添加订单");
			if(!$this->client_rs["is_protocol"])
				throw new Exception("该客户未添加协议，无法添加订单");
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try {
					$postdata = $this->spArgs();
					$data = array();
					$data["listings"] = $postdata["listings"];
					$data["listingstype"] = $postdata["listingstype"];
					$data["listingsarea"] = $postdata["listingsarea"];
					$data["bargain"] = $postdata["bargain"];
					$data["service_price_standard"] = intval(str_replace(",", "", $postdata["service_price_standard"]));
					$data["service_rate_standard"] = strval(str_replace(",", "", $postdata["service_rate_standard"]))."%";
					$data["service_rate_preferent"] = strval(str_replace(",", "", $postdata["service_rate_preferent"]))."%";
					$data["service_price_preferential"] = intval(str_replace(",", "", $postdata["service_price_preferential"]));
					$data["service_price_real"] = intval(str_replace(",", "", $postdata["service_price_real"]));
					$data["preferential_reason"] = $postdata["preferential_reason"];
					$data["dealtime"] = strtotime($postdata["dealtime"]);
					$data["isform"] = 1;
					if($result = $obj_client->getValidatorForBusiness()->spValidator($data)){
						foreach($result as $item) {
							throw new Exception($item[0]);
							break;
						}
					}
					if(!$obj_client->update(array("id"=>$client_id), $data))
						throw new Exception("未知错误，客户订单添加失败");
					spClass('user_log')->save_log(3, "添加了客户 ".$this->client_rs['realname']." [id:$client_id] 的订单", array("client_id"=>$client_id));
					$message = array('msg'=>"客户订单添加成功", 'result'=>1, "url"=>spUrl("clientsales", "myclientlist"));
					echo json_encode($message);
					exit();
				}catch(Exception $e){
					$message = array('msg'=>$e->getMessage(), 'result'=>0);
					echo json_encode($message);
					exit();
				}
			}
			$this->origin_rs = $obj_origin->getOriginById($this->client_rs["origin_id"]);
			$this->from_rs = $obj_origin->getClientViewFrom($this->origin_rs, $this->client_rs);
			$this->validator = $obj_client->getValidatorForBusinessJS();
			$this->client_id = $client_id;
			$this->saveurl = spUrl("clientsales", "clientpayform");
		}catch(Exception $e){
			$this->redirect(spUrl("clientsales", "myclientlist"), $e->getMessage());
		}
	}
	
	//查看订单，只限处于未付完全款和已付全款的客户
	public function clientpayview(){
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_client = spClass("client");
			if(!$this->client_rs = $obj_client->getMySalesClientById($client_id))
				throw new Exception("找不到该客户");
			if($this->client_rs["ispay"] != 1 && $this->client_rs["ispay"] != 2)
				throw new Exception("该客户不处于未付完全款和已付全款状态，无法查看订单");
		}catch(Exception $e){
			$this->redirect(spUrl("clientsales", "myclientlist"), $e->getMessage());
		}
	}
	
	//订单设为失败，只限处于跟进中的客户
	public function clientsetfalse(){
		try {
			die("close");
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception('请先选择客户，再进行操作！');
			$obj_client = spClass("client");
			$obj_false = spClass("client_false");
			if(!$this->client_rs = $obj_client->getMySalesClientById($client_id))
				throw new Exception("找不到该客户");
			if($this->client_rs["ispay"] != 0)
				throw new Exception("该客户不处于跟进中状态，无法将该订单设为失败");
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try {
					$postdata = $this->spArgs();
					$data = array();
					$data["client_id"] = $client_id;
					$data["create_id"] = $_SESSION["sscrm_user"]["id"];
					$data["unsoldreason"] = $postdata["unsoldreason"];
					$data["createtime"] = time();
					if($result = $obj_false->spValidator($data)){
						foreach($result as $item) {
							throw new Exception($item[0]);
							break;
						}
					}
					$obj_false->getDb()->beginTrans();
					if(!$obj_false->create($data))
						throw new Exception("未知错误，客户更新失败");
					$obj_client->setFalse($client_id);
					spClass('user_log')->save_log(3, "将客户 ".$this->client_rs['realname']." [id:$client_id] 设为失败并放入了公共客户池", array("client_id"=>$client_id));
					$obj_false->getDb()->commitTrans();
					echo json_encode(array('msg'=>"客户状态更新成功", "url"=>spUrl("clientsales", "myclientlist"), 'result'=>1));
					exit();
				}catch (Exception $e){
					$obj_false->getDb()->rollbackTrans();
					echo json_encode(array('msg'=>$e->getMessage(), 'result'=>0));
					exit();
				}
			}
			$this->validator = $obj_false->getValidatorJS();
			$this->client_id = $client_id;
			$this->saveurl = spUrl("clientsales", "clientsetfalse");
		}catch(Exception $e){
			$this->redirect(spUrl("clientsales", "myclientlist"), $e->getMessage());
		}
	}
	
	//查看未成交原因，只限处于未成交的客户
	public function clientviewfalse(){
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception('请先选择客户，再进行操作！');
			$obj_client = spClass("client");
			if(!$this->client_rs = $obj_client->getMySalesClientById($client_id))
				throw new Exception("找不到该客户");
			if($this->client_rs["ispay"] != -1)
				throw new Exception("该客户不处于未成交状态，无法查看");
		}catch(Exception $e){
			$this->redirect(spUrl("clientsales", "myclientlist"), $e->getMessage());
		}
	}
	
	//更新款项记录，未付完全款的客户，已弃用
	public function clientfundform(){
		exit();
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_client = spClass("client");
			if(!$this->client_rs = $obj_client->getMySalesClientById($client_id))
				throw new Exception("找不到该客户");
			if($this->client_rs["ispay"] != 1)
				throw new Exception("该客户不处于未付完全款，无法更新其款项记录");
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try {
					$postdata = $this->spArgs();
					$data = array();
					$data["intent_price"] = intval(str_replace(",", "", $postdata["intent_price"]));
					$data["intent_paytype"] = intval($postdata["intent_paytype"]);
					$data["intent_payabletime"] = strtotime($postdata["intent_payabletime"]);
					$data["intent_arrivaltime"] = strtotime($postdata["intent_arrivaltime"]);
					$data["intent_payreal"] = intval(str_replace(",", "", $postdata["intent_payreal"]));
					$data["intent_overdraft"] = intval(str_replace(",", "", $postdata["intent_overdraft"]));
					$data["service_price"] = intval(str_replace(",", "", $postdata["service_price"]));
					$data["service_paytype"] = intval($postdata["service_paytype"]);
					$data["service_payabletime"] = strtotime($postdata["service_payabletime"]);
					$data["service_arrivaltime"] = strtotime($postdata["service_arrivaltime"]);
					$data["service_payreal"] = intval(str_replace(",", "", $postdata["service_payreal"]));
					$data["service_overdraft"] = intval(str_replace(",", "", $postdata["service_overdraft"]));
					if($result = $obj_client->getValidatorForFund()->spValidator($data)){
						foreach($result as $item) {
							throw new Exception($item[0]);
							break;
						}
					}
					if($data["intent_price"] > 0 && ($data["intent_payabletime"] <= 0 || $data["intent_arrivaltime"] <= 0))
						throw new Exception("请填写意向金的应交日期和到账日期");
					if($data["service_price"] > 0 && ($data["service_payabletime"] <= 0 || $data["service_arrivaltime"] <= 0))
						throw new Exception("请填写服务费的应交日期和到账日期");
					if($data["intent_payreal"] + $data["service_payreal"] == $this->client_rs["service_price_standard"]){
						$data["ispay"] = 2;
						$data["fullpay_arrivaltime"] = max($data["intent_arrivaltime"], $data["service_arrivaltime"]);
					}elseif($data["intent_payreal"] + $data["service_payreal"] > $this->client_rs["service_price_standard"]){
						throw new Exception("客户交款大于订单总款，请您核对钱数后再提交");
					}else{
						$data["ispay"] = 1;
					}
					if(!$obj_client->update(array("id"=>$client_id), $data))
						throw new Exception("未知错误，客户款项更新失败");
					spClass('user_log')->save_log(3, "更新了客户 ".$this->client_rs['realname']." [id:$client_id] 的服务费款项记录", array("client_id"=>$client_id));
					$message = array('msg'=>"客户款项更新成功", 'result'=>1, "url"=>spUrl("clientsales", "myclientlist"));
					echo json_encode($message);
					exit();
				}catch(Exception $e){
					$message = array('msg'=>$e->getMessage(), 'result'=>0);
					echo json_encode($message);
					exit();
				}
			}
			$this->validator = $obj_client->getValidatorForFundJS();
			$this->client_id = $client_id;
			$this->saveurl = spUrl("clientsales", "clientfundform");
		}catch(Exception $e){
			$this->redirect(spUrl("clientsales", "myclientlist"), $e->getMessage());
		}
	}
	
	//查看款项记录，只能对应已付全款客户，已弃用，但提供跳转
	public function clientfundview(){
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_client = spClass("client");
			if(!$this->client_rs = $obj_client->getMySalesClientById($client_id))
				throw new Exception("找不到该客户");
			if($this->client_rs["ispay"] != 2)
				throw new Exception("该客户不处于已付全款状态，无法查看其款项记录");
			$this->client_id = $client_id;
			@header("location:".spUrl("clientsales", "clientorderfundlist", array("client_id"=>$client_id)));
			exit();
		}catch(Exception $e){
			$this->redirect(spUrl("clientsales", "myclientlist"), $e->getMessage());
		}
	}
	
	//将客户移交到海外，只能对应已付全款且处于在销售的客户，暂不用该方法
	public function clienttooverseas(){
		exit();
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_user = spClass("user");
			$obj_client = spClass("client");
			$obj_country = spClass('country');
			$obj_notice = spClass("user_notice");
			if(!$this->client_rs = $obj_client->getMySalesClientById($client_id))
				throw new Exception("找不到该客户");
			if($this->client_rs["ispay"] != 2)
				throw new Exception("该客户不处于已付全款状态，无法移交到海外");
			if($this->client_rs["process_id"] != 1)
				throw new Exception("该客户不处于转到销售状态，无法移交到海外");
			if(!$country_rs = $obj_country->getinfoById($this->client_rs["exp_country_id"]))
				throw new Exception("找不到该国家，可能已被删除");
			$postdata = $this->spArgs();
			$data = array();
			$data["user_overseas_id"] = intval($country_rs["to_overseas_id"]);
			$data["to_overseas_time"] = time();
			$data["process_id"] = 2;
			if(!$data["user_overseas_id"])
				throw new Exception("请选择海外部专员");
			if(!$overseas_rs = $obj_user->getDepartUserinfo(4, $data["user_overseas_id"]))
				throw new Exception("找不到该海外部成员，可能已离职");
			$obj_client->getDb()->beginTrans();
			if(!$obj_client->update(array("id"=>$client_id), $data))
				throw new Exception("未知错误，客户移交失败");
			$obj_notice->send_notice($data["user_overseas_id"], "客户 ".$this->client_rs[realname]." 被分配到海外", "客户 ".$this->client_rs[realname]." 被置业顾问 " . $_SESSION["sscrm_user"]["realname"] . " 分配给您");
			spClass('user_log')->save_log(3, "将客户 ".$this->client_rs['realname']." [id:$client_id] 移交到海外专员 " . $overseas_rs["realname"] . " [id:" . $data["user_overseas_id"] . "]", array("client_id"=>$client_id));
			$obj_client->getDb()->commitTrans();
			$message = array('msg'=>"客户移交成功", 'result'=>1, "url"=>spUrl("clientsales", "myclientlist"));
			echo json_encode($message);
			exit();
		}catch(Exception $e){
			$obj_client->getDb()->rollbackTrans();
			$message = array('msg'=>$e->getMessage(), 'result'=>0);
			echo json_encode($message);
			exit();
		}
	}
	
	//查看房款记录，已添加房款的客户
	public function clienthousefundview(){
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_client = spClass("client");
			if(!$this->client_rs = $obj_client->getMySalesClientById($client_id))
				throw new Exception("找不到该客户");
			if($this->client_rs["ishouse_create"] != 1)
				throw new Exception("该客户已未添加房款，无法查看");
		}catch(Exception $e){
			$this->redirect(spUrl("clientsales", "myclientlist"), $e->getMessage());
		}
	}
	
	//查看款项列表，已添加房款的客户，暂无海外流程，已弃用
	public function clientfundlist(){
		exit();
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_client = spClass("client");
			$obj_fund = spClass("client_fund");
			if(!$this->client_rs = $obj_client->getMySalesClientById($client_id))
				throw new Exception("找不到该客户");
			if($this->client_rs["ishouse_create"] != 1)
				throw new Exception("该客户已未添加房款，无法查看");
			$postdate = $this->spArgs();
			$page = intval(max($postdate['page'], 1));
			$housefund_rs = $obj_fund->join("crm_client_fund_type")->join("crm_user")->join("crm_client")->findAll(array("crm_client_fund.client_id"=>$client_id, "crm_client_fund_type.ishouse"=>1, "crm_client.user_sales_id"=>$_SESSION["sscrm_user"]["id"]), "crm_client_fund.createtime asc", "crm_client_fund.*, crm_client.houseunit, crm_user.realname as realname_create, crm_client_fund_type.tname");
			$fund_rs = $obj_fund->join("crm_client_fund_type")->join("crm_user")->join("crm_client")->findAll(array("crm_client_fund.client_id"=>$client_id, "crm_client_fund_type.ishouse"=>0, "crm_client.user_sales_id"=>$_SESSION["sscrm_user"]["id"]), "crm_client_fund.createtime asc", "crm_client_fund.*, crm_client.houseunit, crm_user.realname as realname_create, crm_client_fund_type.tname");
			$this->fund_rs = $fund_rs;
			$this->housefund_rs = $housefund_rs;
			$this->client_id = $client_id;
			$this->url = spUrl('clientsales', 'clientfundlist', array("client_id"=>$client_id));
		}catch(Exception $e){
			$this->redirect(spUrl("clientsales", "myclientlist"), $e->getMessage());
		}
	}
	
	//查看订单款项列表，已添加房款的客户
	public function clientorderfundlist(){
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_client = spClass("client");
			$obj_fund = spClass("client_order_fund");
			if(!$this->client_rs = $obj_client->getMySalesClientById($client_id))
				throw new Exception("找不到该客户");
			if(!in_array($this->client_rs["ispay"], array(1,2)))
				throw new Exception("该客户不处于未付完全款和已付全款状态，无法查看订单");
			$postdate = $this->spArgs();
			$page = intval(max($postdate['page'], 1));
			$fund_rs = $obj_fund->join("crm_user")->join("crm_client")->findAll(array("crm_client_order_fund.client_id"=>$client_id, "crm_client.user_sales_id"=>$_SESSION["sscrm_user"]["id"]), "crm_client_order_fund.createtime asc", "crm_client_order_fund.*, crm_user.realname as realname_create");
			$this->fund_rs = $fund_rs;
			$this->client_id = $client_id;
			$this->url = spUrl('clientsales', 'clientorderfundlist', array("client_id"=>$client_id));
			$this->createurl = spUrl("clientsales", "createorderfund", array("client_id"=>$client_id));
			$this->backurl = ($this->client_rs["ispay"] >= 1) ? spUrl("clientsales", "mypayclientlist") : spUrl("clientsales", "myclientlist");
		}catch(Exception $e){
			$this->redirect(spUrl("clientsales", "myclientlist"), $e->getMessage());
		}
	}
	
	//添加订单款项
	public function createorderfund(){
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_client = spClass("client");
			$obj_fund = spClass("client_order_fund");
			if(!$this->client_rs = $obj_client->getMySalesClientById($client_id))
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
					spClass('user_log')->save_log(3, "添加了客户 ".$this->client_rs['realname']." [id:$client_id] 的订单房款明细", array("client_id"=>$client_id));
					$message = array('msg'=>"客户订单款项添加成功", 'result'=>1, "url"=>spUrl("clientsales", "clientorderfundlist", array("client_id"=>$client_id)));
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
			$this->saveurl = spUrl("clientsales", "createorderfund");
		}catch(Exception $e){
			$this->redirect(spUrl("clientsales", "clientorderfundlist", array("client_id"=>$client_id)), $e->getMessage());
		}
	}
	
	//修改客户级别
	public function modify_level(){
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_client = spClass("client");
			$obj_level = spClass("client_level");
			if(!$this->client_rs = $obj_client->getMySalesClientById($client_id))
				throw new Exception("找不到该客户");
			$level_name = $obj_level->getName($this->client_rs["level_id"]);
			$backurl = $_SERVER['HTTP_REFERER'];
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try {
					$postdata = $this->spArgs();
					$data = array();
					$data["level_id"] = intval($postdata["level_id"]);
					if(!$new_level_name = $obj_level->getName($data["level_id"]))
						throw new Exception("找不到该分级，可能已经丢失");
					if(!$obj_client->update(array("id"=>$client_id), $data))
						throw new Exception("未知错误，更新失败");
					spClass('user_log')->save_log(3, "更新了客户 ".$this->client_rs['realname']." [id:$client_id] 的级别，由 {$level_name} 改为了 {$new_level_name}", array("client_id"=>$client_id));
					$backurl = $postdata["backurl"] ? $postdata["backurl"] : spUrl("clientsales", "myclientlist");
					$message = array('msg'=>"客户级别修改成功", 'result'=>1, "url"=>$backurl);
					echo json_encode($message);
					exit();
				}catch(Exception $e){
					$message = array('msg'=>$e->getMessage(), 'result'=>0);
					echo json_encode($message);
					exit();
				}
			}
			$this->backurl = $backurl;
			$this->client_id = $client_id;
			$this->level_rs = $obj_level->getlist();
		}catch(Exception $e){
			$this->redirect(spUrl("clientsales", "myclientlist"), $e->getMessage());
		}
	}
	
	//修改看房状态
	public function modify_seehouse(){
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_client = spClass("client");
			$obj_seehouse = spClass("client_seehouse");
			if(!$this->client_rs = $obj_client->getMySalesClientById($client_id))
				throw new Exception("找不到该客户");
			$seehouse_name = $obj_seehouse->getName($this->client_rs["seehouse"]);
			$backurl = $_SERVER['HTTP_REFERER'];
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try {
					$postdata = $this->spArgs();
					$data = array();
					$data["seehouse"] = intval($postdata["seehouse"]);
					if(!$new_seehouse_name = $obj_seehouse->getName($data["seehouse"]))
						throw new Exception("找不到该分级，可能已经丢失");
					if(!$obj_client->update(array("id"=>$client_id), $data))
						throw new Exception("未知错误，更新失败");
					spClass('user_log')->save_log(3, "更新了客户 ".$this->client_rs['realname']." [id:$client_id] 的看房状态，由 {$seehouse_name} 改为了 {$new_seehouse_name}", array("client_id"=>$client_id));
					$backurl = $postdata["backurl"] ? $postdata["backurl"] : spUrl("clientsales", "myclientlist");
					$message = array('msg'=>"客户看房状态修改成功", 'result'=>1, "url"=>$backurl);
					echo json_encode($message);
					exit();
				}catch(Exception $e){
					$message = array('msg'=>$e->getMessage(), 'result'=>0);
					echo json_encode($message);
					exit();
				}
			}
			$this->backurl = $backurl;
			$this->client_id = $client_id;
			$this->seehouse_rs = $obj_seehouse->getlist();
		}catch(Exception $e){
			$this->redirect(spUrl("clientsales", "myclientlist"), $e->getMessage());
		}
	}
	
	//客户计划列表
	public function planlist(){
		try {
			$postdata = $this->spArgs();
			$obj_plan = spClass("client_plan");
			$page = intval(max($postdata['page'], 1));
			$condition = "crm_client_plan.main_id = {$_SESSION["sscrm_user"]["id"]} and crm_client_plan.typeid = 2";
			if($client_id = intval($this->spArgs("client_id"))){
				$obj_client = spClass("client");
				if(!$this->client_rs = $obj_client->getMySalesClientById($client_id))
					throw new Exception("找不到该客户");
				$this->createurl = spUrl("clientsales", "createplan", array("client_id"=>$client_id));
				$condition .= " and crm_client_plan.client_id = $client_id";
				$this->client_id = $client_id;
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
			$this->plan_rs = $obj_plan->join("crm_client")->join("crm_user")->spPager($page, 10)->findAll($condition, "crm_client_plan.createtime desc", "crm_client_plan.*, crm_client.realname, crm_client.telphone, crm_client.ispay, crm_client.user_sales_id, crm_user.realname as realname_create");
			$this->pager = $obj_plan->spPager()->getPager();
			$this->url = spUrl('clientsales', 'planlist', array("status"=>$this->status));
			if($this->client_rs["ispay"] >= 1)
				$this->backurl = spUrl("clientsales", "mypayclientlist");
		}catch(Exception $e){
			$this->redirect(spUrl("clientsales", "myclientlist"), $e->getMessage());
		}
	}
	
	//添加客户计划
	public function createplan(){
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_client = spClass("client");
			if(!$this->client_rs = $obj_client->getMySalesClientById($client_id))
				throw new Exception("找不到该客户");
			$obj_plan = spClass("client_plan");
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try {
					$postdata = $this->spArgs();
					$data = array();
					$data["client_id"] = $client_id;
					$data["typeid"] = "2";
					$data["create_id"] = $data["main_id"] = $_SESSION["sscrm_user"]["id"];
					$data["starttime"] = strtotime($postdata["starttime"]);
					$data["endtime"] = strtotime($postdata["endtime"]);
					$data["title"] = "联系客户【".$this->client_rs["realname"]."】";
					$data["content"] = $postdata["content"];
					$data["createtime"] = time();
					if($result = $obj_plan->spValidator($data)){
						foreach($result as $item) {
							throw new Exception($item[0]);
							break;
						}
					}
					if($data["endtime"] <= $data["starttime"])
						throw new Exception("结束时间必须大于开始时间");
					if(!$id = $obj_plan->create($data))
						throw new Exception("未知错误，添加失败");
					spClass('user_log')->save_log(10, "添加了客户 ".$this->client_rs['realname']." [id:$client_id] 的日程[id:{$id}]", array("client_id"=>$client_id));
					$message = array('msg'=>"客户日程添加成功", "url"=>spUrl("clientsales", "planlist", array("client_id"=>$client_id)), 'result'=>1);
					echo json_encode($message);
					exit();
				}catch(Exception $e){
					$message = array('msg'=>$e->getMessage(), 'result'=>0);
					echo json_encode($message);
					exit();
				}
			}
			$this->client_id = $client_id;
		}catch(Exception $e){
			$this->redirect(spUrl("clientsales", "myclientlist"), $e->getMessage());
		}
	}
	
	//修改客户计划
	public function modifyplan(){
		try {
			if(!$id = intval($this->spArgs("id")))
				throw new Exception("参数丢失");
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_client = spClass("client");
			if(!$this->client_rs = $obj_client->getMySalesClientById($client_id))
				throw new Exception("找不到该客户");
			$obj_plan = spClass("client_plan");
			if(!$plan_rs = $obj_plan->find(array("client_id"=>$client_id, "create_id"=>$_SESSION["sscrm_user"]["id"], "typeid"=>2, "id"=>$id)))
				throw new Exception("找不到该计划，可能是参数错误");
			if(date("Y-m-d", $plan_rs["createtime"]) != date("Y-m-d", time()))
				throw new Exception("只能修改当天的回访记录");
			$backurl = $_SERVER['HTTP_REFERER'];
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try {
					$postdata = $this->spArgs();
					$data = array();
					$data["title"] = $plan_rs["title"];
					$data["main_id"] = $plan_rs["main_id"];
					$data["starttime"] = strtotime($postdata["starttime"]);
					$data["endtime"] = strtotime($postdata["endtime"]);
					$data["content"] = $postdata["content"];
					if($result = $obj_plan->spValidator($data)){
						foreach($result as $item) {
							throw new Exception($item[0]);
							break;
						}
					}
					if($data["endtime"] <= $data["starttime"])
						throw new Exception("结束时间必须大于开始时间");
					if(!$obj_plan->update(array("id"=>$id), $data))
						throw new Exception("未知错误，更新失败");
					spClass('user_log')->save_log(10, "更新了客户 ".$this->client_rs['realname']." [id:$client_id] 的计划任务[id:{$id}]", array("client_id"=>$client_id));
					$backurl = $postdata["backurl"] ? $postdata["backurl"] : spUrl("clientsales", "planlist", array("client_id"=>$client_id));
					$message = array('msg'=>"客户计划修改成功", "url"=>$backurl, 'result'=>1);
					echo json_encode($message);
					exit();
				}catch(Exception $e){
					$message = array('msg'=>$e->getMessage(), 'result'=>0);
					echo json_encode($message);
					exit();
				}
			}
			$this->backurl = $backurl;
			$this->id = $id;
			$this->plan_rs = $plan_rs;
			$this->client_id = $client_id;
			$this->display("clientsales/createplan.html");
		}catch(Exception $e){
			$this->redirect(spUrl("clientsales", "myclientlist"), $e->getMessage());
		}
	}
	
	//修改客户计划状态
	public function modifyplan_status(){
		try {
			if(!$id = intval($this->spArgs("id")))
				throw new Exception("参数丢失");
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_client = spClass("client");
			if(!$this->client_rs = $obj_client->getMySalesClientById($client_id))
				throw new Exception("找不到该客户");
			$obj_plan = spClass("client_plan");
			if(!$plan_rs = $obj_plan->find(array("client_id"=>$client_id, "create_id"=>$_SESSION["sscrm_user"]["id"], "id"=>$id)))
				throw new Exception("找不到该计划，是参数错误");
			if($plan_rs["isfinish"])
				throw new Exception("该计划已经完成，无法进行该操作");
			$backurl = $_SERVER['HTTP_REFERER'];
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try {
					$postdata = $this->spArgs();
					$data = array();
					$data["isfinish"] = intval($postdata["isfinish"]);
					$data["finishtime"] = time();
					if(!$data["isfinish"])
						throw new Exception("并没有设为已完成状态");
					if(!$obj_plan->update(array("id"=>$id), $data))
						throw new Exception("未知错误，更新失败");
					spClass('user_log')->save_log(10, "将客户 ".$this->client_rs['realname']." [id:$client_id] 的计划任务[id:{$id}]设为已完成", array("client_id"=>$client_id));
					$backurl = $postdata["backurl"] ? $postdata["backurl"] : spUrl("clientsales", "planlist", array("client_id"=>$client_id));
					$message = array('msg'=>"客户日程修改成功", "url"=>$backurl, 'result'=>1);
					echo json_encode($message);
					exit();
				}catch(Exception $e){
					$message = array('msg'=>$e->getMessage(), 'result'=>0);
					echo json_encode($message);
					exit();
				}
			}
			$this->backurl = $backurl;
			$this->id = $id;
			$this->plan_rs = $plan_rs;
			$this->client_id = $client_id;
		}catch(Exception $e){
			$this->redirect(spUrl("clientsales", "myclientlist"), $e->getMessage());
		}
	}
	
	public function odlist(){
		try {
			if(!$id = intval($this->spArgs('id')))
				throw new Exception('请先选择客户，再进行操作！');
			$backurl = spUrl("clientsales", "clientlist");
			$obj_client = spClass("client");
			$obj_origin = spClass("origin");
			$obj_user = spClass("user");
			if(!$client_rs = $obj_client->getMySalesClientById($id))
				throw new Exception("找不到该客户");
			$postdata = $this->spArgs();
			$obj_od = spClass("client_overtime");
			$page = intval(max($postdata['page'], 1));
			$this->od_rs = $obj_od->join("crm_user")->spPager($page, 20)->findAll(array("client_id"=>$id), "createtime desc", "crm_client_overtime.*, crm_user.realname as realname");
			$this->client_rs = $client_rs;
			$this->id = $id;
			$this->pager = $obj_od->spPager()->getPager();
			$this->url = spUrl("clientsales", "odlist");
			$this->backurl = ($client_rs["ispay"] >= 1) ? spUrl("clientsales", "mypayclientlist") : spUrl("clientsales", "myclientlist");
		}catch(Exception $e){
			$this->redirect(spUrl("clientsales", "clientlist"), $e->getMessage());
		}
	}

	public function allodlist(){
		try {
			$obj_user = spClass("user");
			$postdata = $this->spArgs();
			$obj_od = spClass("client_overtime");
			$page = intval(max($postdata['page'], 1));
			$condition = "crm_client_overtime.user_id = ".$_SESSION["sscrm_user"]["id"];
			if($postdata["endtime"]){
				switch($postdata["endtime"]){
					case "ing":
						$condition .= " and crm_client_overtime.endtime = 0";
					break;
					case "end":
						$condition .= " and crm_client_overtime.endtime > 0";
					break;
				}
				$this->endtime = $postdata["endtime"];
			}
			$this->od_rs = $obj_od->join("crm_user")->join("crm_client")->spPager($page, 20)->findAll($condition, "createtime desc", "crm_client_overtime.*, crm_client.realname as client_realname, crm_client.sex, crm_client.telphone, crm_user.realname as realname");
			$this->pager = $obj_od->spPager()->getPager();
			$this->url = spUrl("clientsales", "allodlist", array("endtime"=>$this->endtime));
			$this->controller = "clientsales";
			$this->istel = 1;
		}catch(Exception $e){
			$this->redirect(spUrl("clientsales", "clientlist"), $e->getMessage());
		}
	}
	
	public function clientintrecordlist(){
		try {
			$obj_type = spClass("client_intention_type");
			if(!$this->type_rs = $obj_type->find(array("id"=>1)))
				throw new Exception("type参数错误");
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_client = spClass("client");
			$obj_int = spClass("client_intention");
			if(!$this->client_rs = $obj_client->getMySalesClientById($client_id))
				throw new Exception("找不到该客户");
			$this->int_rs = $obj_int->find(array("client_id"=>$client_id));
			$obj_record = spClass("client_intention_record");
			$postdata = $this->spArgs();
			$page = intval(max($postdata['page'], 1));
			$record_rs = $obj_record->join("crm_user")->join("crm_client_intention")->spPager($page, 20)->findAll(array("crm_client_intention_record.intention_id"=>$this->int_rs["id"]), "crm_client_intention_record.createtime desc", "crm_client_intention_record.*, crm_user.realname as realname_create");
			$this->record_rs = $record_rs;
			$this->pager = $obj_record->spPager()->getPager();
			$this->url = spUrl('clientsales', 'clientcallrecordlist', array("client_id"=>$client_id, "call_id"=>$call_id));
			$this->backurl = ($this->client_rs["ispay"] >= 1) ? spUrl("clientsales", "mypayclientlist") : spUrl("clientsales", "myclientlist");
			$this->display("clientintention/clientrecordlist.html");
		}catch(Exception $e){
			$this->redirect(spUrl("clientsales", "myclientlist"), $e->getMessage());
		}
	}
}
?>