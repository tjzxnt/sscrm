<?php
class vipclients extends spController {
	
	public function __construct(){
		parent::__construct();
		try {
			
		}catch(Exception $e){
			$backurl = spUrl("main", "welcome");
			$this->redirect($backurl, $e->getMessage());
			exit();
		}
	}
	
	public function clientlist(){
		$obj_client = spClass('vipclient');
		$obj_origin = spClass('origin');
		$obj_process = spClass('client_process');
		$obj_user = spClass("user");
		$obj_country = spClass('country');
		$obj_comactive = spClass('comactive');
		$obj_record = spClass("vipclient_record");
		$postdate = $this->spArgs();
		$page = intval(max($postdate['page'], 1));
		$condition = "crm_vip_client.create_id = ".$_SESSION["sscrm_user"]["id"] . " and crm_vip_client.isdel = 0";
		if($postdate['ispay'].'a' !== 'a'){
			$condition .= " and crm_vip_client.ispay = ".intval($postdate['ispay']);
			$this->ispay = $postdate['ispay'];
		}
		if($comactive_id = intval($postdate['comactive_id'])){
			$condition .= " and crm_vip_client.comactive_id = {$comactive_id}";
			$this->comactive_id = $comactive_id;
		}
		if($channel_main_id = intval($postdate["channel_main_id"])){
			$condition .= " and crm_channel.maintenance_id = $channel_main_id";
			$this->channel_main_id = $channel_main_id;
		}
		if($postdate['searchkey'] != '')
			$condition .= " and (crm_vip_client.comname like '%{$postdate['searchkey']}%' or crm_vip_client.realname like '%{$postdate['searchkey']}%' or crm_vip_client.telphone like '%{$postdate['searchkey']}%')";
		if($client_rs = $obj_client->join("crm_client_level")->join("crm_origin")->join("crm_credential")->join("crm_client_process")->spPager($page, 20)->findAll($condition, 'crm_vip_client.createtime desc', "crm_vip_client.*, crm_client_level.name as level_name, crm_origin.oname, crm_credential.cname, crm_client_process.pname")){
			foreach($client_rs as $key => $val){
				if($val["user_overseas_id"]){
					$over_seas_rs = $obj_user->findByPk($val["user_overseas_id"]);
					$client_rs[$key]["realname_overseas"] = $over_seas_rs["realname"];
				}
				if($val["exp_country_id"]){
					$client_rs[$key]["ctname"] = $obj_country->getname($val["exp_country_id"]);
				}
				$client_rs[$key]["record_count"] = $obj_record->getCountById($val["id"]);
			}
			$this->client_rs = $client_rs;
		}
		$this->comactive_rs = $obj_comactive->getlist();
		$this->channel_prep_rs = $obj_user->getUser_prep("depart_id = 2");
		$this->process_rs = $obj_process->getlist();
		$this->pager = $obj_client->spPager()->getPager();
		$this->searchkey = $postdate['searchkey'];
		$this->url = spUrl('vipclients', 'clientlist', array("searchkey"=>$this->searchkey, "ispay"=>$this->ispay, "channel_main_id"=>$this->channel_main_id, "process_id"=>$this->process_id, "comactive_id"=>$this->comactive_id));
	}
	
	public function prepcreate(){
		$obj_origin = spClass('origin');
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			try{
				$postdata = $this->spArgs();
				$url = spUrl("vipclients", "createclient", array("origin_id"=>$postdata["origin_id"]));
				$message = array('url'=>$url,'result'=>1);
				echo json_encode($message);
				exit();
			}catch(Exception $e){
				$message = array('msg'=>$e->getMessage() ,'result'=>0);
				echo json_encode($message);
				exit();
			}
		}
		$this->saveurl = spUrl("vipclients", "prepcreate");
		$this->origin_rs = $obj_origin->getlist();
		$this->display("clients/prepcreate.html");
		exit();
	}
	
	public function createclient(){
		try {
			$obj_user = spClass("user");
			$obj_origin = spClass("origin");
			$obj_dpt = spClass("department");
			$obj_client = spClass("vipclient");
			$obj_cred = spClass("credential");
			$obj_config = spClass("config");
			$obj_country = spClass('country');
			$obj_notice = spClass("user_notice");
			$obj_channel = spClass("channel");
			$obj_active = spClass("channel_active");
			$obj_trader = spClass('trader');
			$obj_travel = spClass('travel');
			$obj_comactive = spClass("comactive");
			$obj_ass = spClass("client_ass_intention");
			$postdate = $this->spArgs();
			if(!$origin_id = intval($postdate["origin_id"]))
				throw new Exception("请选择客户来源");
			if(!$origin_rs = $obj_origin->getOriginById($origin_id))
				throw new Exception("请选择正确的客户来源");
			$this->ass_rs = $obj_ass->find(array("origin_id"=>$origin_id, "isdel"=>0));
			$extdata = array();
			if($origin_rs["extinput"]){
				$extinput_rs = explode("|", $origin_rs["extinput"]);
				$ext_field_rs = array();
				foreach($extinput_rs as $key => $val){
					$input_array = explode(",", $val);
					$extdata[$input_array["1"]] = $postdate[$input_array["1"]];
					$ext_field_rs[$input_array[1]] = array("type"=>$input_array[0], "field"=>$input_array[1], "fieldname"=>$input_array[2], "demand"=>$input_array[3]);
				}
				$this->ext_field_rs = $ext_field_rs;
			}
			if($origin_rs["isdatafrom"])
				$this->user_prep_rs = $obj_user->getUser_prep();
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try {
					$data = array();
					$data['create_id'] = $_SESSION["sscrm_user"]["id"];
					$data['createtime'] = time();
					$data['origin_id'] = $origin_id;
					if($origin_rs["isownchannel"] || $origin_rs["isselfchannel"]){
						$data['channel_id'] = intval($postdate['channel_id']);
						if($origin_rs["ischannelact"])
							$data['channelact_id'] = intval($postdate['channelact_id']);
					}
					if($origin_rs["isdatafrom"])
						$data["user_datafrom_id"] = intval($postdate['user_datafrom_id']);
					/*
					if($origin_rs["isselfown"])
						$data['user_owner_id'] = $data['create_id'];
					*/
					if($origin_rs["isowntrader"] || $origin_rs["isselftrader"])
						$data['trader_id'] = intval($postdate['trader_id']);
					if($origin_rs["istravel"])
						$data['travel_id'] = intval($postdate['travel_id']);
					$data['comname'] = $postdate['comname'];
					$data['realname'] = $postdate['realname'];
					$data['sex'] = intval($postdate['sex']);
					$data['tel_location'] = $postdate["tel_location"];
					$data['telphone'] = $postdate['telphone'];
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
					if($result = $obj_client->getValidatorForOrigin()->spValidator($data)){
						foreach($result as $item) {
							throw new Exception($item[0]);
							break;
						}
					}
					if(spClass("client")->find(array("telphone"=>$data['telphone'])))
						throw new Exception("该电话号码已在普通客户中存在，无法再次录入系统");
					if($obj_client->find(array("telphone"=>$data['telphone'])))
						throw new Exception("该电话号码已在大客户中存在，无法再次录入系统");
					if($ext_field_rs){
						foreach ($ext_field_rs as $val){
							if($val["demand"] == "required" && !$data[$val[field]])
								throw new Exception($val["fieldname"]."不能为空");
						}
					}
					if($origin_rs["isownchannel"] || $origin_rs["isselfchannel"]){
						if(!$data['channel_id'])
							throw new Exception("请选择渠道");
						if(!$channel_rs = $obj_channel->getChannelById($data['channel_id']))
							throw new Exception("请选择正确的渠道");
						if($origin_rs["ischannelact"]){
							if(!$data['channelact_id'])
								throw new Exception("请选择渠道活动");
							if(!$obj_active->get_actives_by_channelid_id($data['channel_id'], $data['channelact_id']))
								throw new Exception("请选择正确的渠道活动");
						}
					}
					if($origin_rs["isowntrader"] || $origin_rs["isselftrader"]){
						if(!$data['trader_id'])
							throw new Exception("请选择分销商");
						if(!$trader_rs = $obj_trader->getTraderById($data['trader_id']))
							throw new Exception("请选择正确的分销商");
						if(!$trader_rs["create_id"])
							throw new Exception("该分销商没有创建者");
						if($origin_rs["isselftrader"] && ($trader_rs["create_id"] != $data['create_id']))
							throw new Exception("请不要选择其他人的分销商");
					}
					if($origin_rs["istravel"]){
						if(!$data['travel_id'])
							throw new Exception("请选择旅行社");
						if(!$travel_rs = $obj_travel->getTravelById($data['travel_id']))
							throw new Exception("找不到该旅行社，可能已被删除");
					}
					if($data['exp_country_id'] && !$cinfo_rs = $obj_country->getinfoById($data['exp_country_id']))
						throw new Exception("找不到该国家");
					if($origin_rs["isowner"]){
						if(!$data["user_owner_id"] = intval($postdate["user_owner_id"]))
							throw new Exception("请选择客户来源人");
						if(!$user_own_rs = $obj_user->getCommonUserinfo($data["user_owner_id"]))
							throw new Exception("客户来源不正确，找不到该客户来源");
						if($origin_rs["isowndepart"]){
							$owndepart_array = explode(",", $origin_rs["isowndepart"]);
							if(!in_array($user_own_rs["depart_id"], $owndepart_array))
								throw new Exception("该客户所在部门无对应权限");
						}
						/*
						if($user_own_rs["isdel"])
							throw new Exception("该客户来源已经被关闭");
						*/
						/*
						if($origin_rs["need_attr_str"]){
							if(!$user_own_rs["identity_attr"])
								throw new Exception("该客户来源无任何权限");
							$attr_array = explode(",", $user_own_rs["identity_attr"]);
							if($origin_rs["need_attr_str"] == "telclient" && in_array("getclient", $attr_array) && $data["user_owner_id"] == $_SESSION["sscrm_user"]["id"]){
								//如果有接客户的权限，选CALL客可选自己
							}elseif(!in_array($origin_rs["need_attr_str"], $attr_array))
								throw new Exception("该客户来源无对应权限");
						}
						*/
					}
					if($origin_rs["isteluser"] == 1){
						if(!$data["user_teler_id"] = intval($postdate["user_teler_id"]))
							throw new Exception("请选择CALL来源人");
						if(!$user_teler_rs = $obj_user->getCommonUserinfo($data["user_teler_id"]))
							throw new Exception("CALL客来源不正确，找不到该客户来源");
						$attr_array = explode(",", $user_teler_rs["identity_attr"]);
						if($data["user_teler_id"] != $_SESSION["sscrm_user"]["id"] && !in_array("telclient", $attr_array))
							throw new Exception("该CALL客无对应权限");
					}
					if($origin_rs["isacter"] == 1){
						if(!$data["user_preader_id"] = intval($postdate["user_preader_id"]))
							throw new Exception("请选择地推来源人");
						if(!$user_preader_rs = $obj_user->getCommonUserinfo($data["user_preader_id"]))
							throw new Exception("CALL客来源不正确，找不到该客户来源");
						$attr_array = explode(",", $user_preader_rs["identity_attr"]);
						if($data["user_preader_id"] != $_SESSION["sscrm_user"]["id"] && !in_array("preadclient", $attr_array))
							throw new Exception("该地推无对应权限");
					}
					if($origin_rs["iscomactive"] == 1){
						if(!$data["comactive_id"] = intval($postdate["comactive_id"]))
							throw new Exception("请选择公司活动");
						if(!$obj_comactive->getActive($data["comactive_id"]))
							throw new Exception("公司活动不正确，可能已经删除");
					}
					if($record_data["content"] = $postdate["content"]){
						$obj_record = spClass("vipclient_record");
						$record_data["client_id"] = 0;
						$record_data["rtype_id"] = 1;
						$record_data["create_id"] = $data['create_id'];
						$record_data["acttime"] = $record_data["createtime"] = time();
						if($result = $obj_record->spValidator($record_data)){
							foreach($result as $item) {
								throw new Exception($item[0]);
								break;
							}
						}
					}
					$obj_client->getDb()->beginTrans();
					if($this->ass_rs){
						$obj_int = spClass("client_intention");
						$ass_field = $this->ass_rs["fields"];
						if(!$data[$ass_field])
							throw new Exception($this->ass_rs["fieldnull"]);
						if(!$int_rs = $obj_int->checkintention($this->ass_rs["type"], $data[$ass_field], $data['telphone']))
							throw new Exception($this->ass_rs["checkerror"]);
					}
					if(!$id = $obj_client->create($data))
						throw new Exception("未知错误，添加失败");
					if($postdate["channel_id"])
						$obj_channel->updatetime($postdate["channel_id"], $id);
					if($int_rs["id"]){
						if(!$obj_int->update(array("id"=>$int_rs["id"]), array("vip_client_id"=>$id)))
							throw new Exception("CALL客户验证过程中出现问题，请稍后再试");
					}
					spClass('user_log')->save_log(12, "添加了来源为 ".$origin_rs["oname"]." 的大客户 ".$data['realname']." [id:$id]", array("vip_client_id"=>$id));
					if($record_data["content"]){
						$record_data["client_id"] = $id;
						if(!$obj_record->create($record_data))
							throw new Exception("未知错误，沟通记录添加失败");
						spClass('user_log')->save_log(12, "添加了大客户 ".$data['realname']." [id:$id] 的沟通记录", array("vip_client_id"=>$id));
					}
					$obj_client->getDb()->commitTrans();
					$message = array('msg'=>"大客户添加成功", 'result'=>1, "url"=>spUrl("vipclients", "clientlist"));
					echo json_encode($message);
					exit();
				}catch (Exception $e){
					$obj_client->getDb()->rollbackTrans();
					$message = array('msg'=>$e->getMessage(), 'result'=>0);
					echo json_encode($message);
					exit();
				}
			}
			if($origin_rs["isowner"])
				$this->user_group_rs = $obj_user->getlistGroupDepart($origin_rs["isowndepart"], $origin_rs["need_attr_str"]);
			if($origin_rs["isacter"])
				$this->user_pread_group_rs = $obj_user->getlistGroupDepart(2, "preadclient");
			if($origin_rs["isteluser"])
				$this->user_tel_group_rs = $obj_user->getlistGroupDepart(2, "telclient");
			if($origin_rs["isownchannel"]){
				if($origin_rs["isownchanneltype"])
					$this->channel_prep_rs = $obj_channel->getAllChannel_prep("typeid = ".$origin_rs["isownchanneltype"]);
				else 
					$this->channel_prep_rs = $obj_channel->getAllChannel_prep();
			}elseif($origin_rs["isselfchannel"]){
				$this->channel_group_rs = $obj_channel->getlistGroupOne($_SESSION["sscrm_user"]["id"]);
			}
			if($origin_rs["isowntrader"]){
				$this->trader_prep_rs = $obj_trader->getlist_prep();
			}elseif($origin_rs["isselftrader"]){
				$this->trader_group_rs = $obj_trader->getlistGroupOne($_SESSION["sscrm_user"]["id"]);
			}
			if($origin_rs["istravel"])
				$this->travel_prep_rs = $obj_travel->getlist_prep();
			if($origin_rs["iscomactive"])
				$this->comactive_rs = $obj_comactive->getlist();
			$this->country_rs = $obj_country->getlist();
			$this->validator = $obj_client->getValidatorForOriginJS();
			$this->cred_rs = $obj_cred->get_credential();
			$this->origin_id = $origin_id;
			$this->origin_rs = $origin_rs;
			$this->saveurl = spUrl("vipclients", "createclient");
		}catch(Exception $e){
			$this->redirect(spUrl('vipclients', 'clientlist'), $e->getMessage());
		}
	}
	
	public function modify(){
		try {
			if(!$id = intval($this->spArgs('id')))
				throw new Exception('请先选择查看项，再进行操作！');
			$obj_user = spClass("user");
			$obj_channel = spClass("channel");
			$obj_client = spClass("vipclient");
			$obj_origin = spClass("origin");
			$obj_cred = spClass("credential");
			$obj_country = spClass('country');
			$obj_trader = spClass('trader');
			if(!$client_rs = $obj_client->getMyClientById($id))
				throw new Exception("找不到该客户");
			if($client_rs["ispay"])
				throw new Exception("该客户已下订单，无法修改");
			if($client_rs["is_protocol"])
				throw new Exception("该客户已添加协议，无法修改");
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
			$postdate = $this->spArgs();
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try {
					$data = array();
					$data['updatetime'] = time();
					$data['comname'] = $postdate['comname'];
					$data['realname'] = $postdate['realname'];
					$data['sex'] = intval($postdate['sex']);
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
					if($result = $obj_client->getValidatorForOrigin()->spValidator($data)){
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
					spClass('user_log')->save_log(12, "更新了大客户 ".$data['realname']." [id:$id] 的资料", array("vip_client_id"=>$id));
					$message = array('msg'=>"大客户更新成功", 'result'=>1, "url"=>spUrl("vipclients", "clientlist"));
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
			$this->validator = $obj_client->getValidatorForOriginJS();
			$this->saveurl = spUrl("vipclients", "modify");
		}catch(Exception $e){
			$this->redirect($_SERVER['HTTP_REFERER'], $e->getMessage());
		}
	}
	
	public function viewclient(){
		try {
			if(!$id = intval($this->spArgs('id')))
				throw new Exception('请先选择查看项，再进行操作！');
			$url = $_SERVER['HTTP_REFERER'];
			$obj_client = spClass("vipclient");
			$obj_origin = spClass("origin");
			$obj_user = spClass("user");
			if(!$client_rs = $obj_client->getMyClientById($id))
				throw new Exception("找不到该客户");
			$this->client_rs = $client_rs;
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
			$this->from_rs = $obj_origin->getClientViewFrom($this->origin_rs, $client_rs);
		}catch(Exception $e){
			$this->redirect($url, $e->getMessage());
		}
	}
	
	//修改客户级别
	public function modify_level(){
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_client = spClass("vipclient");
			$obj_level = spClass("client_level");
			if(!$this->client_rs = $obj_client->getMyClientById($client_id))
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
					spClass('user_log')->save_log(12, "更新了大客户 ".$this->client_rs['realname']." [id:$client_id] 的级别，由 {$level_name} 改为了 {$new_level_name}", array("vip_client_id"=>$client_id));
					$backurl = $postdata["backurl"] ? $postdata["backurl"] : spUrl("vipclients", "clientlist");
					$message = array('msg'=>"大客户级别修改成功", 'result'=>1, "url"=>$backurl);
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
			$this->redirect(spUrl("vipclients", "clientlist"), $e->getMessage());
		}
	}
	
	public function clientrecordlist(){
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_client = spClass("vipclient");
			if(!$this->client_rs = $obj_client->getMyClientById($client_id))
				throw new Exception("找不到该客户");
			$obj_record = spClass("vipclient_record");
			$postdate = $this->spArgs();
			$page = intval(max($postdate['page'], 1));
			$record_rs = $obj_record->join("crm_user")->join("crm_vip_client")->spPager($page, 20)->findAll(array("crm_vip_client_record.client_id"=>$client_id, "crm_vip_client_record.rtype_id"=>1, "crm_vip_client.create_id"=>$_SESSION["sscrm_user"]["id"]), "crm_vip_client_record.createtime asc", "crm_vip_client_record.*, crm_user.realname as realname_create");
			$this->record_rs = $record_rs;
			$this->pager = $obj_record->spPager()->getPager();
			$this->client_id = $client_id;
			$this->url = spUrl('vipclients', 'clientrecordlist', array("client_id"=>$client_id));
		}catch(Exception $e){
			$this->redirect(spUrl("vipclients", "clientlist"), $e->getMessage());
		}
	}
	
	public function createrecord(){
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_client = spClass("vipclient");
			if(!$this->client_rs = $obj_client->getMyClientById($client_id))
				throw new Exception("找不到该客户");
			$obj_record = spClass("vipclient_record");
			$postdate = $this->spArgs();
			$backurl = spUrl("vipclients", "clientrecordlist", array("client_id"=>$client_id));
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
						throw new Exception("未知错误，沟通记录添加失败");
					spClass('user_log')->save_log(12, "添加了大客户 ".$this->client_rs['realname']." [id:$client_id] 的沟通记录", array("vip_client_id"=>$client_id));
					$message = array('msg'=>"沟通记录添加成功", 'result'=>1, "url"=>$backurl);
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
				$this->url = spUrl('vipclients', 'createrecord', array("client_id"=>$client_id, "showlist"=>1));
				$this->showlist = 1;
			}
			$this->backurl = $backurl;
			$this->validator = $obj_record->getValidatorJS();
			$this->client_id = $client_id;
			$this->saveurl = spUrl("vipclients", "createrecord");
		}catch(Exception $e){
			$this->redirect(spUrl("vipclients", "clientlist"), $e->getMessage());
		}
	}
	
	public function modifyrecord(){
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			if(!$id = intval($this->spArgs("id")))
				throw new Exception("参数丢失");
			$obj_client = spClass("vipclient");
			if(!$this->client_rs = $obj_client->getMyClientById($client_id))
				throw new Exception("找不到该客户");
			$obj_record = spClass("vipclient_record");
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
					spClass('user_log')->save_log(12, "修改了大客户 ".$this->client_rs['realname']." [id:$client_id] 的沟通记录[id:$id]", array("vip_client_id"=>$client_id));
					$message = array('msg'=>"沟通记录修改成功", 'result'=>1, "url"=>spUrl("vipclients", "clientrecordlist", array("client_id"=>$client_id)));
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
			$this->saveurl = spUrl("vipclients", "modifyrecord");
			$this->id = $id;
			$this->record_rs = $record_rs;
			$this->display("vipclients/createrecord.html");
		}catch(Exception $e){
			$this->redirect(spUrl("vipclients", "clientlist"), $e->getMessage());
		}
	}
	
	//添加协议，只限处于跟进中的客户
	public function protocolform(){
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_client = spClass("vipclient");
			$obj_origin = spClass("origin");
			$obj_fund = spClass("vipclient_order_fund");
			$obj_cred = spClass("credential");
			$obj_country = spClass("country");
			if(!$this->client_rs = $obj_client->getMyClientById($client_id))
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
						throw new Exception("未知错误，大客户款项添加失败");
					if(!$obj_client->update(array("id"=>$client_id),$client_data))
						throw new Exception("未知错误，大客户协议添加失败");
					spClass('user_log')->save_log(12, "添加了大客户 ".$this->client_rs['realname']." [id:$client_id] 的协议并添加意向金", array("vip_client_id"=>$client_id));
					$obj_fund->getDb()->commitTrans();
					$message = array('msg'=>"大客户协议添加成功", 'result'=>1, "url"=>spUrl("vipclients", "clientlist"));
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
			$this->saveurl = spUrl("vipclients", "protocolform");
		}catch(Exception $e){
			$this->redirect(spUrl("vipclients", "clientlist"), $e->getMessage());
		}
	}
	
	//添加订单，只限处于跟进中的客户
	public function clientpayform(){
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_client = spClass("vipclient");
			$obj_origin = spClass("origin");
			if(!$this->client_rs = $obj_client->getMyClientById($client_id))
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
					spClass('user_log')->save_log(12, "添加了大客户 ".$this->client_rs['realname']." [id:$client_id] 的订单", array("vip_client_id"=>$client_id));
					$message = array('msg'=>"大客户订单添加成功", 'result'=>1, "url"=>spUrl("vipclients", "clientlist"));
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
			$this->saveurl = spUrl("vipclients", "clientpayform");
		}catch(Exception $e){
			$this->redirect(spUrl("vipclients", "clientlist"), $e->getMessage());
		}
	}
	
	//查看订单，只限处于未付完全款和已付全款的客户
	public function clientpayview(){
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_client = spClass("vipclient");
			if(!$this->client_rs = $obj_client->getMyClientById($client_id))
				throw new Exception("找不到该客户");
			if($this->client_rs["ispay"] != 1 && $this->client_rs["ispay"] != 2)
				throw new Exception("该客户不处于未付完全款和已付全款状态，无法查看订单");
		}catch(Exception $e){
			$this->redirect(spUrl("vipclients", "clientlist"), $e->getMessage());
		}
	}
	
	//查看订单款项列表，已添加房款的客户
	public function clientorderfundlist(){
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_client = spClass("vipclient");
			$obj_fund = spClass("vipclient_order_fund");
			if(!$this->client_rs = $obj_client->getMyClientById($client_id))
				throw new Exception("找不到该客户");
			if(!in_array($this->client_rs["ispay"], array(1,2)))
				throw new Exception("该客户不处于未付完全款和已付全款状态，无法查看订单");
			$postdate = $this->spArgs();
			$page = intval(max($postdate['page'], 1));
			$fund_rs = $obj_fund->join("crm_user")->join("crm_vip_client")->findAll(array("crm_vip_client_order_fund.client_id"=>$client_id, "crm_vip_client.create_id"=>$_SESSION["sscrm_user"]["id"]), "crm_vip_client_order_fund.createtime asc", "crm_vip_client_order_fund.*, crm_user.realname as realname_create");
			$this->fund_rs = $fund_rs;
			$this->client_id = $client_id;
			$this->url = spUrl('vipclients', 'clientorderfundlist', array("client_id"=>$client_id));
			$this->createurl = spUrl("vipclients", "createorderfund", array("client_id"=>$client_id));
			$this->backurl = spUrl("vipclients", "clientlist");
		}catch(Exception $e){
			$this->redirect(spUrl("vipclients", "clientlist"), $e->getMessage());
		}
	}
	
	//添加订单款项
	public function createorderfund(){
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_client = spClass("vipclient");
			$obj_fund = spClass("vipclient_order_fund");
			if(!$this->client_rs = $obj_client->getMyClientById($client_id))
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
					spClass('user_log')->save_log(12, "添加了客户 ".$this->client_rs['realname']." [id:$client_id] 的订单房款明细", array("vip_client_id"=>$client_id));
					$message = array('msg'=>"客户订单款项添加成功", 'result'=>1, "url"=>spUrl("vipclients", "clientorderfundlist", array("client_id"=>$client_id)));
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
			$this->saveurl = spUrl("vipclients", "createorderfund");
		}catch(Exception $e){
			$this->redirect(spUrl("vipclients", "clientorderfundlist", array("client_id"=>$client_id)), $e->getMessage());
		}
	}
	
	public function getactiveByChannelid(){
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			try{
				$obj_act = spClass("channel_active");
				if(!$channelid = intval($this->spArgs("channelid")))
					throw new Exception("参数错误");
				$act_rs = $obj_act->get_actives_by_channelid($channelid);
				$message = array('act_rs'=>$act_rs, 'result'=>1);
				echo json_encode($message);
				exit();
			}catch(Exception $e){
				$message = array('msg'=>$e->getMessage(), 'result'=>0);
				echo json_encode($message);
				exit();
			}
		}
	}
}
?>