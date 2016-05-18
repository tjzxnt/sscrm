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
	
	public function test(){
		
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
	
	//验证意向客户并返回接口
	public function intention_check(){
		try{
			$obj_ass = spClass("client_ass_intention");
			$obj_int = spClass("client_intention");
			$postdata = $this->spArgs();
			$origin_id = $postdata["origin_id"];
			$type = $postdata["type"];
			$fieldsval = $postdata["fieldsval"];
			$telphone = $postdata["telphone"];
			if(!$origin_id)
				throw new Exception("origin_id丢失");
			if(!$type)
				throw new Exception("type丢失");
			if(!$fieldsval)
				throw new Exception("fieldsval丢失");
			if(!$telphone)
				throw new Exception("telphone丢失");
			if(!$ass_rs = $obj_ass->find(array("origin_id"=>$origin_id, "isdel"=>0)))
				throw new Exception("该类型客户不用验证意向客户");
			if(!$int_rs = $obj_int->checkintention($type, $fieldsval, $telphone))
				throw new Exception($ass_rs["checkerror"]);
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
			echo json_encode(array("data"=>$data, "result"=>1));
			exit();
		}catch(Exception $e){
			echo json_encode(array("msg"=>$e->getMessage(), "result"=>0));
			exit();
		}
	}
}
?>