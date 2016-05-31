<?php
class clients extends spController {
	
	public function __construct(){
		parent::__construct();
		try {
			$obj_cpt = spClass("department_competence");
			$obj_cpt->check_login_competence("CLIENT");
			$obj_idt = spClass("user_identity");
			$obj_idt->check_login_competence("getclient");
		}catch(Exception $e){
			$backurl = spUrl("main", "welcome");
			$this->redirect($backurl, $e->getMessage());
			exit();
		}
	}
	
	public function mycreateclientlist(){
		$obj_client = spClass('client');
		$obj_origin = spClass('origin');
		$obj_user = spClass("user");
		$obj_country = spClass('country');
		$postdate = $this->spArgs();
		$page = intval(max($postdate['page'], 1));
		$condition = "crm_client.create_id = ".$_SESSION["sscrm_user"]["id"] . " and crm_client.isdel = 0";
		if($postdate['searchkey'] != '')
			$condition .= " and (crm_client.realname like '%{$postdate['searchkey']}%' or crm_client.telphone like '%{$postdate['searchkey']}%')";
		if($client_rs = $obj_client->join("crm_user as sale_user", "sale_user.id = crm_client.user_sales_id", "left")->join("crm_credential")->join("crm_client_process", "crm_client_process.id = crm_client.process_id", "left")->spPager($page, 20)->findAll($condition, 'crm_client.createtime desc', "crm_client.*, crm_credential.cname, sale_user.realname as realname_sale, crm_client_process.pname")){
			foreach($client_rs as $key => $val){
				if($val["sourcetype"] == 1){
					$client_rs[$key]["oname"] = "渠道来源";
				}elseif($val["sourcetype"] == 2){
					$origin_rs = $obj_origin->findByPk($val["origin_id"]);
					$client_rs[$key]["oname"] = $origin_rs["oname"];
				}
				if($val["user_overseas_id"]){
					$over_seas_rs = $obj_user->findByPk($val["user_overseas_id"]);
					$client_rs[$key]["realname_overseas"] = $over_seas_rs["realname"];
				}
				if($val["exp_country_id"]){
					$client_rs[$key]["ctname"] = $obj_country->getname($val["exp_country_id"]);
				}
			}
			$this->client_rs = $client_rs;
		}
		$this->pager = $obj_client->spPager()->getPager();
		$this->searchkey = $postdate['searchkey'];
		$this->url = spUrl('clients', 'mycreateclientlist', array("searchkey"=>$this->searchkey));
	}
	
	public function prepcreate(){
		$obj_origin = spClass('origin');
		$obj_client = spClass("client");
		$obj_channel = spClass('channel');
		$obj_idt_dpt = spClass('department_identity');
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			try{
				$postdata = $this->spArgs();
				switch($postdata["sourcetype"]){
					case "1":
						$url = spUrl("clients", "createclient_channel", array("channelid"=>$postdata["channelid"]));
					break;
					case "2":
						$url = spUrl("clients", "createclient", array("origin_id"=>$postdata["origin_id"]));
					break;
					default:
						throw new Exception("请选择正确的来源通道");
					break;
				}
				$message = array('url'=>$url,'result'=>1);
				echo json_encode($message);
				exit();
			}catch(Exception $e){
				$message = array('msg'=>$e->getMessage() ,'result'=>0);
				echo json_encode($message);
				exit();
			}
		}
		$this->isallow_channel = $obj_idt_dpt->checkidentity("isfromchannel") ? 1 : 0;
		$this->saveurl = spUrl("clients", "prepcreate");
		/*
		$this->channel_rs = $obj_channel->get_channel_list();
		*/
		$this->origin_rs = $obj_origin->get_department_origin($_SESSION["sscrm_user"]["depart_id"]);
	}
	
	public function createclient(){
		try {
			$obj_user = spClass("user");
			$obj_origin = spClass("origin");
			$obj_dpt = spClass("department");
			$obj_client = spClass("client");
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
			if(!$origin_rs["need_depart_str"])
				throw new Exception("该客户来源不允许使用");
			$need_depart_array = explode(",", $origin_rs["need_depart_str"]);
			if(!in_array($_SESSION["sscrm_user"]["depart_id"], $need_depart_array))
				throw new Exception("您无权使用该来源");
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
			$user_sales_rs = $obj_origin->setclient_to_assign($origin_id);//是否可以指派到某人(只限销售)
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try {
					$data = array();
					$data['create_id'] = $_SESSION["sscrm_user"]["id"];
					$data['overdatestart'] = $data["poolouttime"] = $data['createtime'] = time();
					$data["sourcetype"] = 2;
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
					$data['user_sales_id'] = $user_sales_rs["id"];
					$data = array_merge($data, $extdata);
					if($result = $obj_client->getValidatorForOrigin()->spValidator($data)){
						foreach($result as $item) {
							throw new Exception($item[0]);
							break;
						}
					}
					if($obj_client->find(array("telphone"=>$data['telphone'])))
						throw new Exception("该电话号码已存在，无法再次录入系统");
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
						if(!$channel_rs["create_id"])
							throw new Exception("该渠道没有创建者");
						$data["user_channel_id"] = $channel_rs["maintenance_id"];
						$data["user_channel_assign_id"] = $channel_rs["from_id"];
						$data["user_channel_contact_id"] = $channel_rs["contact_id"];
						if($origin_rs["ischannelrebate"])
							$data["channel_rebate"] = $channel_rs["rebate"];
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
						$data["user_trader_id"] = $trader_rs["maintenance_id"];
						$data["user_trader_assign_id"] = $trader_rs["from_id"];
						$data["user_trader_contact_id"] = $trader_rs["contact_id"];
						if($origin_rs["istraderrebate"])
							$data["trader_rebate"] = $trader_rs["rebate"];
					}
					if($origin_rs["istravel"]){
						if(!$data['travel_id'])
							throw new Exception("请选择旅行社");
						if(!$travel_rs = $obj_travel->getTravelById($data['travel_id']))
							throw new Exception("找不到该旅行社，可能已被删除");
						if($origin_rs["istravelrebate"])
							$data["travel_rebate"] = $travel_rs["rebate"];
					}
					if($data['exp_country_id'] && !$cinfo_rs = $obj_country->getinfoById($data['exp_country_id']))
						throw new Exception("找不到该国家");
					if($data['user_sales_id']){
						if(!$obj_user->getDepartUserinfo(array(2,3), $data['user_sales_id']))
							throw new Exception("找不到所选择的职业顾问");
					}elseif($origin_rs["istopool"]){
						$data['user_sales_id'] = 0;
					}elseif($origin_rs["istotransfer"]){
						$data['user_sales_id'] = 0;
					}else{
						//禁用轮盘机制
						throw new Exception("暂不支持该用户传播机制");
						$data["isauto_touser"] = 1;
						$auto_user_rs = $obj_config->auto_client();
						$data["user_sales_id"] = $auto_user_rs["id"];
					}
					if($origin_rs["isowner"]){
						if(!$data["user_owner_id"] = intval($postdate["user_owner_id"]))
							throw new Exception("请选择客户来源人");
						if($data["user_owner_id"] == $_SESSION["sscrm_user"]["id"])
							throw new Exception("客户来源人不能选择自己");
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
						$obj_record = spClass("client_record");
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
					$obj_int = spClass("client_intention");
					switch($origin_id){
						case "2": //call客来源,必须匹配type=1,$fieldsval=call客来源，即蓄水call创建人
							if(!$data["user_teler_id"])
								throw new Exception("请先选择call客");
							if(!$int_rs = $obj_int->find(array("typeid"=>1, "telphone"=>$data["telphone"], "create_id"=>$data["user_teler_id"], "isdel"=>0)))
								throw new Exception("您选择的CALL客没有对应的蓄水客户");
							$obj_int->isclient($int_rs);
						break;
						case "3": //正常接电/自然到访,不必须匹配type=5，但如果其他类型蓄水存在则提示已存在
							if($int_rs = $obj_int->find(array("telphone"=>$data["telphone"], "isdel"=>0))){
								$obj_int->isclient($int_rs);
								if($int_rs["typeid"] != "5")
									throw new Exception("该客户处于蓄水客户的其他分类中，无法录入【正常接电/自然到访】中");
							}
						break;
						case "11": //渠道直推(需跟进)来源,必须匹配type=2,必须有$channel_id
						case "16": //渠道直推(直接成单)来源,必须匹配type=2,必须有$channel_id
							if(!$int_rs = $obj_int->find(array("typeid"=>2, "telphone"=>$data["telphone"], "create_id"=>$channel_rs["create_id"], "isdel"=>0)))
								throw new Exception("你选择的渠道没有对应的渠道蓄水客户");
							$obj_int->isclient($int_rs);
							if($int_rs["channelact_id"])
								throw new Exception("该蓄水客户来源于渠道活动，无法添加到该类型客户");
						break;
						case "12": //渠道活动到访,不必须匹配type=2，但如果其他类型蓄水存在则提示已存在,必须有$channel_id和$channelact_id
							if($int_rs = $obj_int->find(array("telphone"=>$data["telphone"], "isdel"=>0))){
								if(!$data['channel_id'])
									throw new Exception("请先选择渠道");
								if(!$data['channelact_id'])
									throw new Exception("请选择渠道活动");
								$obj_int->isclient($int_rs);
								if($data['channel_id'] != $int_rs["channel_id"])
									throw new Exception("你选择的蓄水客户与渠道不匹配");
								if(!$int_rs["channelact_id"])
									throw new Exception("您选择的蓄水客户并没有指定渠道活动，如有问题请联系蓄水客户的添加人");
								if($int_rs["channelact_id"] != $data['channelact_id'])
									throw new Exception("您选择的渠道活动与蓄水客户不相符，无法录入");
								if($int_rs["typeid"] != "2")
									throw new Exception("该客户处于蓄水客户的其他分类中，无法录入【渠道活动到访】中");
							}
						break;
						case "14": //同事推荐来源,必须匹配type=4,$fieldsval=同事id
							if(!$data["user_owner_id"])
								throw new Exception("请先选择客户来源人");
							if(!$int_rs = $obj_int->find(array("typeid"=>4, "telphone"=>$data["telphone"], "user_owner_id"=>$data["user_owner_id"], "isdel"=>0)))
								throw new Exception("您选择的同事推荐客户没有对应的蓄水客户");
							$obj_int->isclient($int_rs);
						break;
						case "18": //线上客户来源,必须匹配type=3
							if(!$int_rs = $obj_int->find(array("typeid"=>3, "telphone"=>$data["telphone"], "isdel"=>0)))
								throw new Exception("您选择的线上客户没有对应的蓄水客户");
							$obj_int->isclient($int_rs);
						break;
						default:
							if($int_rs = $obj_int->find(array("telphone"=>$data["telphone"], "isdel"=>0)))
								throw new Exception("该客户在蓄水客户中已存在，无法录入");
						break;
					}
					if(!$id = $obj_client->create($data))
						throw new Exception("未知错误，添加失败");
					if($postdate["channel_id"])
						$obj_channel->updatetime($postdate["channel_id"], $id);
					if($int_rs["id"]){
						if(!$obj_int->update(array("id"=>$int_rs["id"]), array("client_id"=>$id)))
							throw new Exception("CALL客户验证过程中出现问题，请稍后再试");
					}
					if($data["isauto_touser"] = 1)
						$obj_config->set_val("auto_client", $data["user_sales_id"]);
					if($origin_rs["istopool"]){
						$obj_client->createForPool($id);
					}if($origin_rs["istotransfer"]){
						$obj_client->createForTransfer($id);
					}else{
						$obj_notice->send_notice($data["user_sales_id"], "客户 ".$data[realname]." 被创建后分配到置业顾问", "客户 ".$data[realname]." 被 " . $_SESSION["sscrm_user"]["realname"] . " 创建后已分配给您");
					}
					spClass('user_log')->save_log(3, "添加了来源为 ".$origin_rs["oname"]." 的客户 ".$data['realname']." [id:$id]", array("client_id"=>$id));
					if($record_data["content"]){
						$record_data["client_id"] = $id;
						if(!$obj_record->create($record_data))
							throw new Exception("未知错误，沟通记录添加失败");
						spClass('user_log')->save_log(3, "添加了客户 ".$data['realname']." [id:$id] 的沟通记录", array("client_id"=>$id));
					}
					$obj_client->getDb()->commitTrans();
					$message = array('msg'=>"客户添加成功", 'result'=>1, "url"=>spUrl("clientsales", "myclientlist"));
					echo json_encode($message);
					exit();
				}catch (Exception $e){
					$obj_client->getDb()->rollbackTrans();
					$message = array('msg'=>$e->getMessage(), 'result'=>0);
					echo json_encode($message);
					exit();
				}
			}
			
			
			if($origin_rs["isowner"]){
				$groupdepart_condition = "crm_user.id <> {$_SESSION["sscrm_user"]["id"]}";
				if($origin_rs["isowndepart"])
					$groupdepart_condition .= " and crm_user.depart_id in({$origin_rs["isowndepart"]})";
				if($origin_rs["need_attr_str"])
					$groupdepart_condition .= " and find_in_set('{$origin_rs["need_attr_str"]}', crm_user.identity_attr)";
				$this->user_group_rs = $obj_user->getUserGroupDepart_prep($groupdepart_condition);
				unset($groupdepart_condition);
			}
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
			$this->user_sales_rs = $user_sales_rs;
			$this->validator = $obj_client->getValidatorForOriginJS();
			$this->cred_rs = $obj_cred->get_credential();
			$this->origin_id = $origin_id;
			$this->origin_rs = $origin_rs;
			$this->saveurl = spUrl("clients", "createclient");
		}catch(Exception $e){
			$this->redirect(spUrl('clientsales', 'myclientlist'), $e->getMessage());
		}
	}
	
	public function viewmycreatedclient(){
		try {
			if(!$id = intval($this->spArgs('id')))
				throw new Exception('请先选择查看项，再进行操作！');
			$url = $_SERVER['HTTP_REFERER'];
			$obj_client = spClass("client");
			$obj_origin = spClass("origin");
			$obj_user = spClass("user");
			$obj_channel = spClass("channel");
			$obj_trader = spClass('trader');
			if(!$client_rs = $obj_client->getMyCreatedClientById($id))
				throw new Exception("找不到该客户");
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
			$obj_client = spClass("client");
			if(!$this->client_rs = $obj_client->getMyCreatedClientById($client_id))
				throw new Exception("找不到该客户");
			$obj_record = spClass("client_record");
			$postdate = $this->spArgs();
			$page = intval(max($postdate['page'], 1));
			$record_rs = $obj_record->join("crm_user")->join("crm_client")->spPager($page, 20)->findAll(array("crm_client_record.client_id"=>$client_id, "crm_client_record.rtype_id"=>1, "crm_client.create_id"=>$_SESSION["sscrm_user"]["id"]), "crm_client_record.createtime asc", "crm_client_record.*, crm_user.realname as realname_create");
			$this->record_rs = $record_rs;
			$this->pager = $obj_record->spPager()->getPager();
			$this->client_id = $client_id;
			$this->url = spUrl('clients', 'clientrecordlist', array("client_id"=>$client_id));
		}catch(Exception $e){
			$this->redirect(spUrl("clients", "mycreateclientlist"), $e->getMessage());
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