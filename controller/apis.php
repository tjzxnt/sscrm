<?php
class apis extends spController {
	
	public function get_channeltype2(){
		try {
			if(!$typeid = intval($this->spArgs("typeid")))
				throw new Exception("参数错误");
			$obj_ctype = spClass("channel_type");
			$ctype_rs = $obj_ctype->findAll(array("parent_id"=>$typeid, "ishide"=>0), "sort asc");
			echo json_encode(array("data_rs"=>$ctype_rs, "result"=>1));
			exit();
		}catch(Exception $e){
			echo json_encode(array("msg"=>$e->getMessage(), "result"=>0));
			exit();
		}
	}
	
	public function vipclient_to_manage(){
		header("Content-Type: text/html; charset=UTF-8");
		$obj_client = spClass("vipclient");
		$obj_manage = spClass("vipclient_manage");
		if($client_rs = $obj_client->findAll()){
			try {
				$num = 0;
				$obj_manage->getDb()->beginTrans();
				foreach($client_rs as $val){
					if($obj_manage->find(array("vip_client_id"=>$val["id"], "telphone"=>$val["telphone"])))
						continue;
					$manage_rs = $obj_manage->find(array("vip_client_id"=>$val["id"], "isdel"=>0));
					$data = array();
					$data["vip_client_id"] = $val["id"];
					$data["ismain"] = $manage_rs ? 0 : 1;
					$data["realname"] = $val["realname"];
					$data["sex"] = $val["sex"];
					$data["tel_location"] = $val["tel_location"];
					$data["telphone"] = $val["telphone"];
					$data["email"] = $val["email"];
					$data["wechat"] = $val["wechat"];
					$data["createtime"] = $val["createtime"];
					if($result = $obj_manage->spValidator($data)){
						foreach($result as $item) {
							throw new Exception("id:{$val["id"]} msg:{$item[0]}");
							break;
						}
					}
					$obj_manage->create($data);
					$num++;
				}
				$obj_manage->getDb()->commitTrans();
				echo "ok: {$num} rows be created";
			}catch(Exception $e){
				echo "fail:".$e->getMessage();
				exit();
			}
		}
	}
	
	public function getplan(){
		try {
			$action = $this->spArgs("action");
			$endtime = strtotime($this->spArgs("endtime"));
			$starttime = strtotime($this->spArgs("starttime"));
			$dataType = $this->spArgs("dataType");
			$status = $this->spArgs("status");
			$obj_plan = spClass("client_plan");
			if($action != "gettodolist_calendar")
				throw new Exception("action error");
			if($dataType != '0')
				throw new Exception("datatype error");
			$condition = "crm_client_plan.main_id = {$_SESSION["sscrm_user"]["id"]}";
			$condition .= " and (crm_client_plan.endtime >= {$starttime} and crm_client_plan.starttime <= {$endtime})";
			if($status){
				switch ($status){
					case "doing":
						$condition .= " and crm_client_plan.isfinish = 0 and crm_client_plan.starttime <= ".time();
						$this->status = $status;
						break;
					case "going":
						$condition .= " and crm_client_plan.isfinish = 0 and crm_client_plan.starttime <= ".time()." and crm_client_plan.endtime >=" . time();
						$this->status = $status;
						break;
					case "overdate":
						$condition .= " and crm_client_plan.isfinish = 0 and crm_client_plan.endtime <= ".time();
						$this->status = $status;
						break;
					case "waiting":
						$condition .= " and crm_client_plan.isfinish = 0 and crm_client_plan.starttime >= ".time();
						$this->status = $status;
						break;
					case "finish":
						$condition .= " and crm_client_plan.isfinish = 1";
						$this->status = $status;
						break;
				}
			}
			if(!$plan_rs = $obj_plan->join("crm_user", "crm_user.id = crm_client_plan.main_id")->findAll($condition, null, "IF(crm_client_plan.title = '', '无主题', crm_client_plan.title) as title, FROM_UNIXTIME(crm_client_plan.starttime, '%Y/%m/%d %H:%i:%s') as start, FROM_UNIXTIME(crm_client_plan.endtime, '%Y/%m/%d %H:%i:%s') as end, crm_user.realname as receiveUser"))
				throw new Exception("no result");
			foreach($plan_rs as $key => $val){
				$plan_rs[$key]["type"] = "1";
				$plan_rs[$key]["url"] = "javascript:void(0);";
				$plan_rs[$key]["className"] = "dataColor rwBackgroundColor";
				$plan_rs[$key]["receiveUser"] = $val["receiveUser"];
			}
			$result["issuccess"] = "true";
			$result["hint"] = $plan_rs;
			echo json_encode($result);
			exit();
		}catch(Exception $e){
			echo json_encode(array("issuccess"=>"false"));
			exit();
		}
	}
	
	public function rencent_plan(){
		$obj_plan = spClass("client_plan");
		$obj_user = spClass("user");
		if($plan_recent_rs = $obj_plan->join("crm_user as create_user", "create_user.id = crm_client_plan.create_id")->findAll(null, "crm_client_plan.createtime desc", "crm_client_plan.*, create_user.realname as realname_create", null, 5)){
			foreach($plan_recent_rs as $key => $val){
				$user_rs = $obj_user->find("find_in_set(id, '{$val["main_id"]}')", null, "group_concat(realname) as realname_main");
				$plan_recent_rs[$key]["realname_main"] = $user_rs["realname_main"];
			}
			$this->plan_recent_rs = $plan_recent_rs;
		}
		$html = $this->fetch("library/index_rencent_plan.html");
		$result = array("html"=>$html, "result"=>1);
		echo json_encode($result);
		exit();
	}
	
	public function test_record_overtime(){
		header("Content-Type: text/html; charset=UTF-8");
		$obj_client = spClass("client");
		$obj_client->record_overtime();
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
	
	//验证意向客户并返回接口
	public function intention_check(){
		try{
			$obj_ass = spClass("client_ass_intention");
			$obj_int = spClass("client_intention");
			$obj_type = spClass("client_intention_type");
			$postdata = $this->spArgs();
			$origin_id = $postdata["origin_id"];
			$type = $postdata["type"];
			$fieldsval = $postdata["fieldsval"];
			$telphone = $postdata["telphone"];
			$channel_id = $postdata["channel_id"];
			if(!$origin_id)
				throw new Exception("origin_id丢失");
			if(!$type)
				throw new Exception("type丢失");
			if(!$telphone)
				throw new Exception("请输入要匹配的电话号码丢失");
			if(!$ass_rs = $obj_ass->find(array("origin_id"=>$origin_id, "isdel"=>0)))
				throw new Exception("该类型客户不用验证意向客户");
			if(!$type_rs = $obj_type->find(array("id"=>$type)))
				throw new Exception("类型参数不正确");
			if($type_rs["ischannel"]){
				if(!$channel_id)
					throw new Exception("渠道参数丢失");
				$obj_channel = spClass("channel");
				if(!$channel_rs = $obj_channel->getChannelById($channel_id))
					throw new Exception("找不到该渠道，可能已被删除");
				$channel_rs["create_id"] = intval($channel_rs["create_id"]);
				$fieldsval = $channel_rs["create_id"];
			}
			if($ass_rs["fields"] == "SEARCHBYTEL"){
				$int_temp_rs = $obj_int->getintention($type, $telphone);
				$ass_field = intval($int_temp_rs["create_id"]);
			}else{
				/*
				$int_temp_rs = $obj_int->getintention($type, $telphone);
				$ass_field = intval($int_temp_rs["create_id"]);
				*/
				$ass_field = $fieldsval;
			}
			if(!$ass_field){
				if($ass_rs["ismustass"])
					throw new Exception($ass_rs["checkerror"]);
			}
			eval("\$ass_createid = $ass_field;");
			if(!$int_rs = $obj_int->checkintention($type, $ass_createid, $postdata)){
				if($ass_rs["ismustass"])
					throw new Exception($ass_rs["checkerror"]);
			}
			if($int_rs){
				$data = array();
				$data["realname"] = $int_rs["realname"];
				$data["sex"] = $int_rs["sex"];
				$data["tel_location"] = $int_rs["tel_location"];
				$data["telphone"] = $int_rs["telphone"];
				$data["cred_id"] = $int_rs["cred_id"];
				$data["cred_license"] = $int_rs["cred_license"];
				$data["address"] = $int_rs["address"];
				$data["profession"] = $int_rs["profession"];
				$data["email"] = $int_rs["email"];
				$data["wechat"] = $int_rs["wechat"];
				$data["exp_country_id"] = $int_rs["exp_country_id"];
				$data["demand"] = $int_rs["demand"];
				$data["feedback"] = $int_rs["feedback"];
			}else
				$data = "";
			echo json_encode(array("data"=>$data, "result"=>1));
			exit();
		}catch(Exception $e){
			echo json_encode(array("msg"=>$e->getMessage(), "result"=>0));
			exit();
		}
	}
	
	public function getAnalysis(){
		try{
			if(!$code = $this->spArgs("code"))
				throw new Exception("miss code");
			$count = 0;
			switch($code){
				/*日程管理start*/
				case "schedule_myplanlist":
					$obj_plan = spClass("client_plan");
					$condition = "(crm_client_plan.create_id = ".$_SESSION["sscrm_user"]["id"]." or find_in_set(".$_SESSION["sscrm_user"]["id"].", crm_client_plan.main_id))";
					$all_rs = $obj_plan->find($condition, null, "count(id) as total");
					$all = intval($all_rs["total"]);
					$condition_overdate = " and crm_client_plan.isfinish = 0 and crm_client_plan.endtime <= ".time();
					$overdate_rs = $obj_plan->find($condition.$condition_overdate, null, "count(id) as total");
					$overdate = intval($overdate_rs["total"]);
					$condition_ing = " and crm_client_plan.isfinish = 0 and crm_client_plan.starttime <= ".time();
					$ing_rs = $obj_plan->find($condition.$condition_ing, null, "count(id) as total");
					$ing = intval($ing_rs["total"]);
					$condition_wait = " and crm_client_plan.isfinish = 0 and crm_client_plan.starttime >= ".time();
					$wait_rs = $obj_plan->find($condition.$condition_wait, null, "count(id) as total");
					$wait = intval($wait_rs["total"]);
					$per = $all > 0 ? round(($all - $overdate) / $all * 100, 2) : 0;
					$html = "<span class=\"no-text\">过期：{$overdate}/进行中：{$ing}/待进行：{$wait}/全部：{$all}</span><span class=\"p-text\">完成率：{$per}%</span>";
				break;
				case "schedule_planlist":
					$obj_plan = spClass("client_plan");
					$condition = "1";
					$all_rs = $obj_plan->find($condition, null, "count(id) as total");
					$all = intval($all_rs["total"]);
					$condition_overdate = " and crm_client_plan.isfinish = 0 and crm_client_plan.endtime <= ".time();
					$overdate_rs = $obj_plan->find($condition.$condition_overdate, null, "count(id) as total");
					$overdate = intval($overdate_rs["total"]);
					$condition_ing = " and crm_client_plan.isfinish = 0 and crm_client_plan.starttime <= ".time();
					$ing_rs = $obj_plan->find($condition.$condition_ing, null, "count(id) as total");
					$condition_wait = " and crm_client_plan.isfinish = 0 and crm_client_plan.starttime >= ".time();
					$wait_rs = $obj_plan->find($condition.$condition_wait, null, "count(id) as total");
					$wait = intval($wait_rs["total"]);
					$ing = intval($ing_rs["total"]);
					$per = $all > 0 ? round(($all - $overdate) / $all * 100, 2) : 0;
					$html = "<span class=\"no-text\">过期：{$overdate}/进行中：{$ing}/待进行：{$wait}/全部：{$all}</span><span class=\"p-text\">完成率：{$per}%</span>";
					break;
				/*日程管理end*/
				
				/*蓄水客户start*/
				case "clientintention_clientlist": //我的蓄水客户
					$obj_int = spClass("client_intention");
					$all_rs = $obj_int->find("isdel = 0 and create_id = {$_SESSION["sscrm_user"]["id"]}", null, "count(id) as total");
					$client_rs = $obj_int->find("isdel = 0 and create_id = {$_SESSION["sscrm_user"]["id"]} and (client_id > 0 or vip_client_id > 0)", null, "count(id) as total");
					$overtime_rs = spClass('client_intention_overtime')->find("endtime = 0 and user_id = {$_SESSION["sscrm_user"]["id"]}", null, "count(id) as total");
					$all = intval($all_rs["total"]);
					$client = intval($client_rs["total"]);
					$per = $all > 0 ? round($client / $all * 100, 2) : 0;
					$overtime = intval($overtime_rs["total"]);
					$html = "<span class=\"no-text\">过期：{$overtime}/到访：{$client}/全部：{$all}</span><span class=\"p-text\">转化率：{$per}%</span>";
				break;
				case "clientintention_allclientlist": //部门蓄水客户
					$obj_int = spClass("client_intention");
					$all_rs = $obj_int->join("crm_user")->find("crm_client_intention.isdel = 0 and crm_user.depart_id = {$_SESSION["sscrm_user"]["depart_id"]}", null, "count(crm_client_intention.id) as total");
					$client_rs = $obj_int->join("crm_user")->find("crm_client_intention.isdel = 0 and crm_user.depart_id = {$_SESSION["sscrm_user"]["depart_id"]} and (crm_client_intention.client_id > 0 or crm_client_intention.vip_client_id > 0)", null, "count(crm_client_intention.id) as total");
					$overtime_rs = spClass('client_intention_overtime')->join("crm_user")->find("crm_client_intention_overtime.endtime = 0 and crm_user.depart_id = {$_SESSION["sscrm_user"]["depart_id"]}", null, "count(crm_client_intention_overtime.id) as total");
					$all = intval($all_rs["total"]);
					$client = intval($client_rs["total"]);
					$per = $all > 0 ? round($client / $all * 100, 2) : 0;
					$overtime = intval($overtime_rs["total"]);
					$html = "<span class=\"no-text\">过期：{$overtime}/到访：{$client}/全部：{$all}</span><span class=\"p-text\">转化率：{$per}%</span>";
				break;
				case "clientintention_allrecordlist": //部门回访记录
					$obj_record = spClass("client_intention_record");
					$record_rs = $obj_record->join("crm_user")->find("crm_user.depart_id = {$_SESSION["sscrm_user"]["depart_id"]}", null, "count(crm_client_intention_record.id) as total");
					$count = intval($record_rs["total"]);
					$html = "<span class=\"no-text\">回访总数：$count</span>";
				break;
				case "clientintention_allplanlist": //部门计划列表
					$obj_plan = spClass("client_plan");
					$plan_rs = $obj_plan->join("crm_user")->find("crm_user.depart_id = {$_SESSION["sscrm_user"]["depart_id"]} and crm_client_plan.typeid = 3 and crm_client_plan.isfinish = 0", null, "count(crm_client_plan.id) as total");
					$count = intval($plan_rs["total"]);
					$html = "<span class=\"no-text\">未完成：{$count}</span>";
				break;
				case "clientintention_m_allclientlist": //全部蓄水客户
					$obj_int = spClass("client_intention");
					$all_rs = $obj_int->find("isdel = 0", null, "count(id) as total");
					$client_rs = $obj_int->find("isdel = 0 and (client_id > 0 or vip_client_id > 0)", null, "count(id) as total");
					$overtime_rs = spClass('client_intention_overtime')->find("endtime = 0", null, "count(id) as total");
					$all = intval($all_rs["total"]);
					$client = intval($client_rs["total"]);
					$per = $all > 0 ? round($client / $all * 100, 2) : 0;
					$overtime = intval($overtime_rs["total"]);
					$html = "<span class=\"no-text\">过期：{$overtime}/到访：{$client}/全部：{$all}</span><span class=\"p-text\">转化率：{$per}%</span>";
				break;
				case "clientintention_m_allrecordlist"://全部回访记录
					$record_rs = spClass("client_intention_record")->find(null, null, "count(id) as total");
					$count = intval($record_rs["total"]);
					$html = "<span class=\"no-text\">回访总数：$count</span>";
				break;
				case "clientintention_m_allplanlist"://全部蓄水客户
					$obj_plan = spClass("client_plan");
					$plan_rs = $obj_plan->find("typeid = 3 and isfinish = 0", null, "count(id) as total");
					$count = intval($plan_rs["total"]);
					$html = "<span class=\"no-text\">未完成：{$count}</span>";
				break;
				case "clientintention_allclientlist_1":
					$obj_int = spClass("client_intention");
					$all_rs = $obj_int->join("crm_user")->find("crm_client_intention.isdel = 0 and crm_client_intention.typeid = 1 and crm_user.depart_id = {$_SESSION["sscrm_user"]["depart_id"]}", null, "count(crm_client_intention.id) as total");
					$client_rs = $obj_int->join("crm_user")->find("crm_client_intention.isdel = 0 and crm_client_intention.typeid = 1 and crm_user.depart_id = {$_SESSION["sscrm_user"]["depart_id"]} and (crm_client_intention.client_id > 0 or crm_client_intention.vip_client_id > 0)", null, "count(crm_client_intention.id) as total");
					$overtime_rs = spClass('client_intention_overtime')->join("crm_client_intention")->join("crm_user")->find("crm_client_intention_overtime.endtime = 0 and crm_client_intention.typeid = 1 and crm_user.depart_id = {$_SESSION["sscrm_user"]["depart_id"]}", null, "count(crm_client_intention_overtime.id) as total");
					$all = intval($all_rs["total"]);
					$client = intval($client_rs["total"]);
					$per = $all > 0 ? round($client / $all * 100, 2) : 0;
					$overtime = intval($overtime_rs["total"]);
					$html = "<span class=\"no-text\">过期：{$overtime}/到访：{$client}/全部：{$all}</span><span class=\"p-text\">转化率：{$per}%</span>";
				break;
				case "clientintention_allrecordlist_1":
					$obj_record = spClass("client_intention_record");
					$record_rs = $obj_record->join("crm_client_intention")->find("crm_client_intention.typeid = 1", null, "count(crm_client_intention_record.id) as total");
					$count = intval($record_rs["total"]);
					$html = "<span class=\"no-text\">回访总数：$count</span>";
				break;
				/*蓄水客户end*/
				
				/*到访客户start*/
				case "clientsrelated_clientlist"://与我相关客户
					$obj_user = spClass('user');
					$obj_client = spClass('client');
					$user_id = $_SESSION["sscrm_user"]["id"];
					$relative_array = $obj_user->get_relative_array();
					$condition = "(0";
					if($relative_array){
						foreach($relative_array as $val){
							$condition .= " or IF($val > 0, $val = $user_id, 0)";
						}
					}
					$condition .= ")";
					$condition .= " and crm_client.isdel = 0";
					$client_rs = $obj_client->find($condition, null, "count(id) as total");
					$count = intval($client_rs["total"]);
					$html = "<span class=\"no-text\">全部：{$count}</span>";
				break;
				case "clientall_clientlist": //系统客户管理
				case "clientadm_clientlist": //行政查看系统客户
				case "clientdepart_oversea_clientlist": //海外查看系统客户
					$obj_client = spClass('client');
					$obj_od = spClass("client_overtime");
					$condition = "crm_client.isdel = 0";
					$client_rs = $obj_client->find($condition, null, "count(id) as total");
					$total = intval($client_rs["total"]);
					$payed_rs = $obj_client->find($condition." and ispay >= 1", null, "count(id) as total");
					$payedcount = intval($payed_rs["total"]);
					$per = $total > 0 ? round($payedcount / $total * 100, 2) : 0;
					$over_rs = $obj_client->find("crm_client.isdel = 0 and crm_client.isoverdate = 1", null, "count(id) as total");
					$overcount = intval($over_rs["total"]);
					$od_rs = $obj_od->find("endtime = 0", null, "count(id) as total");
					$odcount = intval($od_rs["total"]);
					$html = "<span class=\"no-text\">全部:{$total}/无意向:{$overcount}/过期中:{$odcount}/成交数:{$payedcount}</span><span class=\"p-text\">成交率：{$per}%</span>";
				break;
				case "clientall_planlist": //系统全部计划任务
					$obj_plan = spClass("client_plan");
					$condition = "crm_client_plan.typeid = 2";
					$plan_rs = $obj_plan->find($condition, null, "count(id) as total");
					$total = intval($plan_rs["total"]);
					$html = "<span class=\"no-text\">全部：{$total}</span>";
				break;
				case "clientall_allrecordlist"; //系统全部回访记录
				case "clientdepart_oversea_allrecordlist": //海外查看全部回访记录
					$obj_record = spClass('client_record');
					$condition = "crm_client_record.rtype_id = 1";
					$record_rs = $obj_record->find($condition, null, "count(id) as total");
					$total = intval($record_rs["total"]);
					$html = "<span class=\"no-text\">全部：{$total}</span>";
				break;
				case "clientall_allodlist":
					$obj_od = spClass("client_overtime");
					$od_rs = $obj_od->find(null, null, "count(id) as total");
					$total = intval($od_rs["total"]);
					$remain_rs = $obj_od->find("endtime = 0", null, "count(id) as total");
					$remain_total = intval($remain_rs["total"]);
					$html = "<span class=\"no-text\">全部：{$total}/未处理：{$remain_total}</span>";
				break;
				case "clientsales_mypayclientlist": //我的成交客户
					$obj_client = spClass('client');
					$condition = "crm_client.user_sales_id = ".$_SESSION["sscrm_user"]["id"] . " and crm_client.isoverdate = 0 and crm_client.isdel = 0 and crm_client.ispool = 0";
					$conditionpayed = " and crm_client.ispay >= 1";
					$payed_rs = $obj_client->find($condition.$conditionpayed, null, "count(id) as total");
					$payedcount = intval($payed_rs["total"]);
					$client_rs = $obj_client->find($condition, null, "count(id) as total");
					$total = intval($client_rs["total"]);
					$per = $total > 0 ? round($payedcount / $total * 100, 2) : 0;
					$html = "<span class=\"no-text\">全部：{$count}</span><span class=\"p-text\">转化率：{$per}%</span>";
				break;
				case "clientsales_myclientlist": //我的跟进客户
					$obj_client = spClass('client');
					$condition = "crm_client.user_sales_id = ".$_SESSION["sscrm_user"]["id"] . " and crm_client.isoverdate = 0 and crm_client.isdel = 0 and crm_client.ispool = 0";
					$client_rs = $obj_client->find($condition, null, "count(id) as total");
					$total = intval($client_rs["total"]);
					$html = "<span class=\"no-text\">全部：{$total}</span>";
				break;
				case "clientsales_allodlist":
					$obj_od = spClass("client_overtime");
					$condition = "crm_client_overtime.user_id = ".$_SESSION["sscrm_user"]["id"];
					$od_rs = $obj_od->find($condition, null, "count(id) as total");
					$total = intval($od_rs["total"]);
					$html = "<span class=\"no-text\">全部：{$total}</span>";
				break;
				case "clientdepart_allodlist":
					$obj_od = spClass("client_overtime");
					$condition = "1";
					$od_rs = $obj_od->find($condition, null, "count(id) as total");
					$total = intval($od_rs["total"]);
					$html = "<span class=\"no-text\">全部：{$total}</span>";
				break;
				case "clientsover_clientlist": //部门已无意向客户
					$obj_client = spClass('client');
					$condition = "crm_client.isdel = 0 and crm_client.isoverdate = 1";
					$depart_id = 3;
					$depart_rs = spClass('department')->getinfoById($depart_id);
					$obj_sep = spClass('department_sep');
					if($depart_rs["is_sep"]){
						if(!$sep_id = $_SESSION["sscrm_user"]["depart_sep_id"])
							throw new Exception("您尚未分配到".$depart_rs["dname"]."下的所在组，请尝试重新登录");
						if(!$sep_rs =$obj_sep->find(array("depart_id"=>$depart_id, "id"=>$sep_id)))
							throw new Exception("您所在的组不正确，请尝试重新登录");
						$condition .= " and crm_user.depart_sep_id = $sep_id";
					}
					$client_rs = $obj_client->find($condition, null, "count(id) as total");
					$total = intval($client_rs["total"]);
					$html = "<span class=\"no-text\">全部：{$total}</span>";
				break;
				case "clientdepart_payclientlist": //部门已成交客户
					$obj_client = spClass('client');
					$condition = "crm_client.isdel = 0 and crm_client.isoverdate = 0";
					$conditionpayed = " and crm_client.ispay >= 1";
					$depart_id = 3;
					$depart_rs = spClass('department')->getinfoById($depart_id);
					$obj_sep = spClass('department_sep');
					if($depart_rs["is_sep"]){
						if(!$sep_id = $_SESSION["sscrm_user"]["depart_sep_id"])
							throw new Exception("您尚未分配到".$depart_rs["dname"]."下的所在组，请尝试重新登录");
						if(!$sep_rs =$obj_sep->find(array("depart_id"=>$depart_id, "id"=>$sep_id)))
							throw new Exception("您所在的组不正确，请尝试重新登录");
						$condition .= " and crm_user.depart_sep_id = $sep_id";
					}
					$payed_rs = $obj_client->find($condition.$conditionpayed, null, "count(id) as total");
					$payedcount = intval($payed_rs["total"]);
					$client_rs = $obj_client->find($condition, null, "count(id) as total");
					$total = intval($client_rs["total"]);
					$per = $total > 0 ? round($payedcount / $total * 100, 2) : 0;
					$html = "<span class=\"no-text\">全部：{$payedcount}</span><span class=\"p-text\">转化率：{$per}%</span>";
				break;
				case "clientdepart_clientlist": //部门跟进客户
					$obj_client = spClass('client');
					$obj_od = spClass("client_overtime");
					$condition = "crm_client.isdel = 0";
					$depart_id = 3;
					$depart_rs = spClass('department')->getinfoById($depart_id);
					$obj_sep = spClass('department_sep');
					$extcondition = "";
					if($depart_rs["is_sep"]){
						if(!$sep_id = $_SESSION["sscrm_user"]["depart_sep_id"])
							throw new Exception("您尚未分配到".$depart_rs["dname"]."下的所在组，请尝试重新登录");
						if(!$sep_rs =$obj_sep->find(array("depart_id"=>$depart_id, "id"=>$sep_id)))
							throw new Exception("您所在的组不正确，请尝试重新登录");
						$extcondition = " and crm_user.depart_sep_id = $sep_id";
					}
					$client_rs = $obj_client->find($condition.$extcondition, null, "count(id) as total");
					$total = intval($client_rs["total"]);
					$over_rs = $obj_client->find($condition." and crm_client.isoverdate = 1".$extcondition, null, "count(id) as total");
					$over_total = intval($over_rs["total"]);
					$od_rs = $obj_od->join("crm_client")->find($condition." and crm_client_overtime.endtime = 0", null, "count(crm_client_overtime.id) as total");
					$odcount = intval($od_rs["total"]);
					$html = "<span class=\"no-text\">全部：{$total}/无效：{$over_total}/过期中：{$odcount}</span>";
				break;
				case "clientdepart_allrecordlist": //部门全部回访记录
					$obj_record = spClass('client_record');
					$condition = "crm_client_record.rtype_id = 1";
					$depart_id = 3;
					$depart_rs = spClass('department')->getinfoById($depart_id);
					$obj_sep = spClass('department_sep');
					if($depart_rs["is_sep"]){
						if(!$sep_id = $_SESSION["sscrm_user"]["depart_sep_id"])
							throw new Exception("您尚未分配到".$depart_rs["dname"]."下的所在组，请尝试重新登录");
						if(!$sep_rs =$obj_sep->find(array("depart_id"=>$depart_id, "id"=>$sep_id)))
							throw new Exception("您所在的组不正确，请尝试重新登录");
						$condition .= " and crm_user.depart_sep_id = $sep_id";
					}
					$record_rs = $obj_record->join("crm_user")->find($condition, null, "count(crm_client_record.id) as total");
					$total = intval($record_rs["total"]);
					$html = "<span class=\"no-text\">全部：{$total}</span>";
				break;
				case "clientoverseas_myclientlist":
					$obj_client = spClass('client');
					$condition = "crm_client.isdel = 0 and crm_client.is_protocol = 1";
					$client_rs = $obj_client->find($condition, null, "count(id) as total");
					$total = intval($client_rs["total"]);
					$pay_rs = $obj_client->find($condition." and ispay = 2", null, "count(id) as total");
					$paytotal = intval($pay_rs["total"]);
					$html = "<span class=\"no-text\">全部：{$total}/可结佣：{$paytotal}</span>";
				break;
				case "channels_clientlist":
					$obj_client = spClass('client');
					$condition = "crm_client.channel_id > 0 and crm_channel.maintenance_id = {$_SESSION["sscrm_user"]["id"]} and crm_client.isdel = 0";
					$client_rs = $obj_client->join("crm_channel")->find($condition, null, "count(crm_client.id) as total");
					$total = intval($client_rs["total"]);
					$eff_rs = $obj_client->join("crm_channel")->find($condition." and crm_client.isoverdate = 0", null, "count(crm_client.id) as total");
					$eff_total = intval($eff_rs["total"]);
					$html = "<span class=\"no-text\">全部：{$total}/有效：{$eff_total}</span>";
				break;
				case "channeldeparts_clientlist": //渠道客户管理
					$obj_client = spClass('client');
					$condition = "crm_client.channel_id > 0 and crm_client.isdel = 0";
					$client_rs = $obj_client->join("crm_channel")->find($condition, null, "count(crm_client.id) as total");
					$total = intval($client_rs["total"]);
					$eff_rs = $obj_client->join("crm_channel")->find($condition." and crm_client.isoverdate = 0", null, "count(crm_client.id) as total");
					$eff_total = intval($eff_rs["total"]);
					$html = "<span class=\"no-text\">全部：{$total}/有效：{$eff_total}</span>";
				break;
				case "clientdeal_clientlist": //成交客户管理
					$obj_client = spClass('client');
					$condition = "crm_client.isdel = 0";
					$payed_rs = $obj_client->find($condition." and ispay >= 1", null, "count(id) as total");
					$payedcount = intval($payed_rs["total"]);
					$html = "<span class=\"no-text\">全部：{$payedcount}</span>";
				break;
				case "clientsales_planlist":
					$obj_plan = spClass("client_plan");
					$condition = "crm_client_plan.main_id = {$_SESSION["sscrm_user"]["id"]} and crm_client_plan.typeid = 2";
					$total_rs = $obj_plan->find($condition, null, "count(id) as total");
					$total = intval($total_rs["total"]);
					$condition_over = $condition . " and crm_client_plan.isfinish = 0 and crm_client_plan.endtime <= ".time();
					$over_rs = $obj_plan->find($condition_over, null, "count(id) as total");
					$over = intval($over_rs["total"]);
					$html = "<span class=\"no-text\">全部：{$total}/超时：{$over}</span>";
				break;
				case "clientdepart_planlist":
					$obj_plan = spClass("client_plan");
					$condition = "crm_client_plan.typeid = 2";
					$total_rs = $obj_plan->find($condition, null, "count(id) as total");
					$total = intval($total_rs["total"]);
					$condition_over = $condition . " and crm_client_plan.isfinish = 0 and crm_client_plan.endtime <= ".time();
					$over_rs = $obj_plan->find($condition_over, null, "count(id) as total");
					$over = intval($over_rs["total"]);
					$html = "<span class=\"no-text\">全部：{$total}/超时：{$over}</span>";
				break;
				/*到访客户end*/
				
				/*大客户管理start*/
				case "vipclients_clientlist":
					$obj_client = spClass('vipclient');
					$obj_od = spClass("vipclient_overtime");
					$condition = "crm_vip_client.create_id = ".$_SESSION["sscrm_user"]["id"] . " and crm_vip_client.isdel = 0";
					$total_rs = $obj_client->find($condition, null, "count(id) as total");
					$total = intval($total_rs["total"]);
					$condition_od = " and crm_vip_client_overtime.endtime = 0";
					$od_rs = $obj_od->join("crm_vip_client")->find($condition.$condition_od, null, "count(crm_vip_client_overtime.id) as total");
					$total_od = intval($od_rs["total"]);
					$html = "<span class=\"no-text\">全部：{$total}/过期：{$total_od}</span>";
				break;
				case "vipclientoverseas_myclientlist":
					$obj_client = spClass('vipclient');
					$condition = "crm_vip_client.isdel = 0 and crm_vip_client.is_protocol = 1";
					$total_rs = $obj_client->find($condition_over, null, "count(id) as total");
					$total = intval($total_rs["total"]);
					$condition_payed = " and crm_vip_client.ispay = 2";
					$payed_rs = $obj_client->find($condition.$condition_payed, null, "count(crm_vip_client.id) as total");
					$total_payed = intval($payed_rs["total"]);
					$html = "<span class=\"no-text\">全部：{$total}/可结佣：{$total_payed}</span>";
				break;
				case "vipclientall_clientlist":
				case "vipclientadm_clientlist":
					$obj_client = spClass('vipclient');
					$obj_od = spClass("vipclient_overtime");
					$condition = "crm_vip_client.isdel = 0";
					$total_rs = $obj_client->find($condition_over, null, "count(id) as total");
					$total = intval($total_rs["total"]);
					$condition_od = " and crm_vip_client_overtime.endtime = 0";
					$od_rs = $obj_od->join("crm_vip_client")->find($condition.$condition_od, null, "count(crm_vip_client_overtime.id) as total");
					$total_od = intval($od_rs["total"]);
					$html = "<span class=\"no-text\">全部：{$total}/过期：{$total_od}</span>";
				break;
				case "vipclientall_allrecordlist":
					$obj_record = spClass("vipclient_record");
					$condition = "crm_vip_client_record.rtype_id = 1";
					$total_rs = $obj_record->find($condition, null, "count(id) as total");
					$total = intval($total_rs["total"]);
					$html = "<span class=\"no-text\">全部：{$total}</span>";
				break;
				case "vipclientall_allodlist":
					$obj_od = spClass("vipclient_overtime");
					$condition = "1";
					$total_rs = $obj_od->find($condition, null, "count(id) as total");
					$total = intval($total_rs["total"]);
					$html = "<span class=\"no-text\">全部：{$total}</span>";
				break;
				case "vipclients_allrecordlist":
					$obj_record = spClass("vipclient_record");
					$condition = "crm_vip_client_record.rtype_id = 1 and crm_vip_client_record.create_id = {$_SESSION["sscrm_user"]["id"]}";
					$total_rs = $obj_record->find($condition, null, "count(id) as total");
					$total = intval($total_rs["total"]);
					$html = "<span class=\"no-text\">全部：{$total}</span>";
				break;
				/*大客户管理end*/
				
				/*渠道管理start*/
				case "channels_channelsignlist": //我的推荐渠道
					$obj_channel = spClass('channel');
					$condition = "crm_channel.ishide = 0 and crm_channel.isoverdate = 0 and crm_channel.from_id = ".$_SESSION["sscrm_user"]["id"];
					$total_rs = $obj_channel->find($condition, null, "count(id) as total");
					$total = intval($total_rs["total"]);
					$condition_nosign = " and crm_channel.issign = 0";
					$nosign_rs = $obj_channel->find($condition.$condition_nosign, null, "count(id) as total");
					$nosign = intval($nosign_rs["total"]);
					$html = "<span class=\"no-text\">全部：{$total}/未签约：{$nosign}</span>";
				break;
				case "channels_channellist": //我的维护渠道
					$obj_channel = spClass('channel');
					$condition = "crm_channel.ishide = 0 and crm_channel.isoverdate = 0 and crm_channel.maintenance_id = ".$_SESSION["sscrm_user"]["id"];
					$total_rs = $obj_channel->find($condition, null, "count(id) as total");
					$total = intval($total_rs["total"]);
					$condition_nosign = " and crm_channel.issign = 0";
					$nosign_rs = $obj_channel->find($condition.$condition_nosign, null, "count(id) as total");
					$nosign = intval($nosign_rs["total"]);
					$html = "<span class=\"no-text\">全部：{$total}/未签约：{$nosign}</span>";
				break;
				case "channels_allodlist": //我的过期记录
					$obj_od = spClass("channel_overtime");
					$condition = "crm_channel.maintenance_id = {$_SESSION["sscrm_user"]["id"]} and crm_channel.ishide = 0 and crm_channel.isoverdate = 0";
					$total_rs = $obj_od->join("crm_channel")->find($condition, null, "count(crm_channel_overtime.id) as total");
					$total = intval($total_rs["total"]);
					$condition_remain = " and crm_channel_overtime.endtime = 0";
					$remain_rs = $obj_od->join("crm_channel")->find($condition.$condition_remain, null, "count(crm_channel_overtime.id) as total");
					$remain = intval($remain_rs["total"]);
					$html = "<span class=\"no-text\">全部：{$total}/未处理：{$remain}</span>";
				break;
				case "channels_planlist":
					$obj_plan = spClass("client_plan");
					$condition = "crm_channel.maintenance_id = {$_SESSION["sscrm_user"]["id"]} and crm_client_plan.typeid = 3";
					$total_rs = $obj_plan->join("crm_channel")->find($condition, null, "count(crm_client_plan.id) as total");
					$total = intval($total_rs["total"]);
					$html = "<span class=\"no-text\">全部：{$total}</span>";
				break;
				case "channelverifys_verifychannellist": //渠道签约管理
				case "channeldeparts_channellist": //市场部渠道
				case "channelall_channellist": //系统渠道管理
					$obj_channel = spClass('channel');
					$condition = "crm_channel.ishide = 0";
					$total_rs = $obj_channel->join("crm_user as muser", "muser.id = crm_channel.maintenance_id")->find($condition, null, "count(crm_channel.id) as total");
					$total = intval($total_rs["total"]);
					$condition_nosign = " and crm_channel.issign = 0";
					$nosign_rs = $obj_channel->join("crm_user as muser", "muser.id = crm_channel.maintenance_id")->find($condition.$condition_nosign, null, "count(crm_channel.id) as total");
					$nosign = intval($nosign_rs["total"]);
					$condition_over = " and crm_channel.isoverdate = 1";
					$over_rs = $obj_channel->join("crm_user as muser", "muser.id = crm_channel.maintenance_id")->find($condition.$condition_over, null, "count(crm_channel.id) as total");
					$over = intval($over_rs["total"]);
					$html = "<span class=\"no-text\">全部：{$total}/无意向：{$over}/未签约：{$nosign}</span>";
				break;
				case "channelsover_channellist":
					$obj_channel = spClass('channel');
					$condition = "crm_channel.ishide = 0 and crm_channel.isoverdate = 1";
					$total_rs = $obj_channel->find($condition, null, "count(crm_channel.id) as total");
					$total = intval($total_rs["total"]);
					$html = "<span class=\"no-text\">全部：{$total}</span>";
				break;
				case "channeldeparts_verifychannellist":
					$obj_channel = spClass('channel');
					$condition = "crm_channel.ishide = 0 and crm_channel.isoverdate = 0 and crm_channel.issign = 0";
					$total_rs = $obj_channel->find($condition, null, "count(id) as total");
					$total = intval($total_rs["total"]);
					$html = "<span class=\"no-text\">全部：{$total}</span>";
				break;
				case "channelall_allrecordlist":
				case "channeldeparts_allrecordlist":
					$obj_record = spClass("channel_record");
					$condition = "crm_channel.ishide = 0";
					$total_rs = $obj_record->join("crm_channel")->find($condition, null, "count(crm_channel_record.id) as total");
					$total = intval($total_rs["total"]);
					$html = "<span class=\"no-text\">全部：{$total}</span>";
				break;
				case "channeldeparts_allodlist":
				case "channelall_allodlist":
					$obj_od = spClass("channel_overtime");
					$condition = "crm_channel.ishide = 0";
					$total_rs = $obj_od->join("crm_channel")->join("crm_user")->find($condition, null, "count(crm_channel_overtime.id) as total");
					$total = intval($total_rs["total"]);
					$condition_remain = " and crm_channel_overtime.endtime = 0";
					$remain_rs = $obj_od->join("crm_channel")->find($condition.$condition_remain, null, "count(crm_channel_overtime.id) as total");
					$remain = intval($remain_rs["total"]);
					$html = "<span class=\"no-text\">全部：{$total}/未处理：{$remain}</span>";
					break;
				case "channelall_planlist":
				case "channeldeparts_planlist":
					$obj_plan = spClass("client_plan");
					$condition = "crm_client_plan.typeid = 3";
					$total_rs = $obj_plan->join("crm_channel")->find($condition, null, "count(crm_client_plan.id) as total");
					$total = intval($total_rs["total"]);
					$html = "<span class=\"no-text\">全部：{$total}</span>";
				break;
				case "channelall_allsignlist":
				case "channelverifys_allsignlist":
					$obj_sign = spClass("channel_sign");
					$condition = "crm_channel_sign.isdel = 0";
					$total_rs = $obj_sign->join("crm_channel")->find($condition, null, "count(crm_channel_sign.id) as total");
					$total = intval($total_rs["total"]);
					$html = "<span class=\"no-text\">全部：{$total}</span>";
				break;
				case "channelall_allactlist":
					$obj_active = spClass("channel_active");
					$total_rs = $obj_active->join("crm_channel")->find($condition, null, "count(crm_channel_active.id) as total");
					$total = intval($total_rs["total"]);
					$html = "<span class=\"no-text\">全部：{$total}</span>";
				break;
				/*渠道管理end*/
				default:
					throw new Exception("code error");
				break;
			}
			echo json_encode(array("html"=>$html, "result"=>1));
			exit();
		}catch(Exception $e){
			echo json_encode(array("err"=>$e->getMessage(), "result"=>0));
			exit();
		}
	}
}
?>