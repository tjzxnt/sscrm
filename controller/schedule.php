<?php
class schedule extends spController {
	
	public function mycalendar(){
		
	}
	
	public function getplan(){
		try {
			$action = $this->spArgs("action");
			$endtime = strtotime($this->spArgs("endtime"));
			$starttime = strtotime($this->spArgs("starttime"));
			$dataType = $this->spArgs("dataType");
			$status = $this->spArgs("status");
			$main_id = $this->spArgs("user_id") ? $this->spArgs("user_id") : $_SESSION["sscrm_user"]["id"];
			$obj_plan = spClass("client_plan");
			$obj_user = spClass("user");
			$obj_channel = spClass('channel');
			$obj_client = spClass('client');
			if($action != "gettodolist_calendar")
				throw new Exception("action error");
			if($dataType != '0')
				throw new Exception("datatype error");
			$condition = "find_in_set({$main_id}, crm_client_plan.main_id)";
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
			if(!$plan_rs = $obj_plan->findAll($condition, null, "IF(crm_client_plan.title = '', '无主题', crm_client_plan.title) as title, FROM_UNIXTIME(crm_client_plan.starttime, '%Y/%m/%d %H:%i:%s') as start, FROM_UNIXTIME(crm_client_plan.endtime, '%Y/%m/%d %H:%i:%s') as end, crm_client_plan.endtime, crm_client_plan.starttime, crm_client_plan.main_id, crm_client_plan.isfinish, crm_client_plan.id, crm_client_plan.client_id, crm_client_plan.channel_id"))
				throw new Exception("no result");
			foreach($plan_rs as $key => $val){
				$user_rs = $obj_user->find("find_in_set(id, '{$val["main_id"]}')", null, "group_concat(realname) as realname_main");
				$plan_rs[$key]["receiveUser"] = $user_rs["realname_main"];
				$plan_rs[$key]["type"] = "1";
				if($val["client_id"]){
					$client_rs = $obj_client->findByPk($val["client_id"], "realname");
					$plan_rs[$key]["title"] = "联系客户【{$client_rs["realname"]}】";
				}
				if($val["channel_id"]){
					$channel_rs = $obj_channel->findByPk($val["channel_id"], "mechanism");
					$plan_rs[$key]["title"] = "联系渠道【{$channel_rs["mechanism"]}】";
				}
				$plan_rs[$key]["url"] = "javascript:zxshow({$val["id"]}, '{$plan_rs[$key]["title"]}')";
				//$plan_rs[$key]["className"] = $val["isfinish"] ? "dataColor hdBackgroundColor" : "dataColor rwBackgroundColor";
				if($val["isfinish"]){
					//完成
					$plan_rs[$key]["className"] = "dataColor endBackgroundColor";
				}else{
					if($val["endtime"] <= time()){
						//过期
						$plan_rs[$key]["className"] = "dataColor hdBackgroundColor";
					}elseif($val["starttime"] <= time() && $val["endtime"] >= time()){
						//进行中
						$plan_rs[$key]["className"] = "dataColor doingBackgroundColor";
					}else{
						//未开始
						$plan_rs[$key]["className"] = "dataColor rwBackgroundColor";
					}
				}
				//$plan_rs[$key]["receiveUser"] = $val["receiveUser"];
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
	
	public function viewplan(){
		try {
			$id = intval($this->spArgs("id"));
			if(!$id)
				throw new Exception("参数丢失");
			$obj_plan = spClass("client_plan");
			$obj_user = spClass("user");
			if(!$plan_rs = $obj_plan->find(array("id"=>$id)))
				throw new Exception("找不到该日程，可能已被删除");
			$user_rs = $obj_user->find("find_in_set(id, '{$plan_rs["main_id"]}')", null, "group_concat(realname) as realname_main");
			$plan_rs["receiveUser"] = $user_rs["realname_main"];
			$this->plan_rs = $plan_rs;
			echo $this->fetch("schedule/viewplan.html");
		}catch(Exception $e){
			header("Content-Type: text/html; charset=UTF-8");
			echo $e->getMessage();
		}
		exit();
	}
	
	public function layer_createplan(){
		try {
			$obj_plan = spClass("client_plan");
			$data_date = $this->spArgs("data_date");
			$backurl = $this->spArgs("backurl") ? $this->spArgs("backurl") : spUrl("schedule", "mycalendar");
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try {
					$postdata = $this->spArgs();
					$data = array();
					$data["client_id"] = 0;
					$data["typeid"] = "1";
					$data["create_id"] = $_SESSION["sscrm_user"]["id"];
					if($postdata["main_id"])
						$data["main_id"] = implode(",", $postdata["main_id"]);
					$data["starttime"] = strtotime($postdata["starttime"]);
					$data["endtime"] = strtotime($postdata["endtime"]);
					$data["title"] = $postdata["title"];
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
					spClass('user_log')->save_log(10, "添加了日程，主题为[".$data["title"]."] [id:{$id}]");
					$message = array('msg'=>"日程添加成功", "url"=>$backurl, "ATUserCalendar"=>1, 'result'=>1);
					echo json_encode($message);
					exit();
				}catch(Exception $e){
					$message = array('msg'=>$e->getMessage(), 'result'=>0);
					echo json_encode($message);
					exit();
				}
			}
			$obj_user = spClass("user");
			$this->user_prep_rs = $obj_user->getUserGroupDepart_prep();
			$this->saveurl = spUrl("schedule", "layer_createplan");
			$this->backurl = $backurl;
			$this->data_date = $data_date;
		}catch(Exception $e){
			$this->redirect(spUrl("schedule", "mycalendar"), $e->getMessage());
		}
	}
	
	public function myplanlist(){
		try {
			$postdata = $this->spArgs();
			$obj_user = spClass("user");
			$obj_plan = spClass("client_plan");
			$obj_channel = spClass('channel');
			$obj_client = spClass('client');
			$page = intval(max($postdata['page'], 1));
			$condition = "(crm_client_plan.create_id = ".$_SESSION["sscrm_user"]["id"]." or find_in_set(".$_SESSION["sscrm_user"]["id"].", crm_client_plan.main_id))";
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
			if($plan_rs = $obj_plan->join("crm_user as create_user", "create_user.id = crm_client_plan.create_id")->spPager($page, 20)->findAll($condition, "crm_client_plan.createtime desc", "crm_client_plan.*, create_user.realname as realname_create")){
				foreach($plan_rs as $key => $val){
					$user_rs = $obj_user->find("find_in_set(id, '{$val["main_id"]}')", null, "group_concat(realname) as realname_main");
					$plan_rs[$key]["realname_main"] = $user_rs["realname_main"];
					if($val["client_id"]){
						$client_rs = $obj_client->findByPk($val["client_id"], "realname");
						$plan_rs[$key]["title"] = "联系客户【{$client_rs["realname"]}】";
					}
					if($val["channel_id"]){
						$channel_rs = $obj_channel->findByPk($val["channel_id"], "mechanism");
						$plan_rs[$key]["title"] = "联系渠道【{$channel_rs["mechanism"]}】";
					}
				}
			}
			$this->plan_rs = $plan_rs;
			$this->pager = $obj_plan->spPager()->getPager();
			$this->url = spUrl('schedule', 'myplanlist', array("status"=>$this->status));
		}catch(Exception $e){
			$this->redirect(spUrl("schedule", "myplanlist"), $e->getMessage());
		}
	}
	
	//添加客户计划
	public function createplan(){
		try {
			$obj_plan = spClass("client_plan");
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try {
					$postdata = $this->spArgs();
					$data = array();
					$data["client_id"] = 0;
					$data["typeid"] = "1";
					$data["create_id"] = $_SESSION["sscrm_user"]["id"];
					if($postdata["main_id"])
						$data["main_id"] = implode(",", $postdata["main_id"]);
					$data["starttime"] = strtotime($postdata["starttime"]);
					$data["endtime"] = strtotime($postdata["endtime"]);
					$data["title"] = $postdata["title"];
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
					spClass('user_log')->save_log(10, "添加了日程，主题为[".$data["title"]."] [id:{$id}]");
					$message = array('msg'=>"日程添加成功", "url"=>spUrl("schedule", "myplanlist"), 'result'=>1);
					echo json_encode($message);
					exit();
				}catch(Exception $e){
					$message = array('msg'=>$e->getMessage(), 'result'=>0);
					echo json_encode($message);
					exit();
				}
			}
			$obj_user = spClass("user");
			$this->user_prep_rs = $obj_user->getUserGroupDepart_prep();
		}catch(Exception $e){
			$this->redirect(spUrl("schedule", "myplanlist"), $e->getMessage());
		}
	}
	
	//修改客户计划
	public function modifyplan(){
		try {
			if(!$id = intval($this->spArgs("id")))
				throw new Exception("参数丢失");
			$obj_plan = spClass("client_plan");
			if(!$plan_rs = $obj_plan->find(array("create_id"=>$_SESSION["sscrm_user"]["id"], "id"=>$id)))
				throw new Exception("找不到该日程，可能是参数错误");
			if($plan_rs["main_id"])
				$plan_rs["main_id_array"] = explode(",", $plan_rs["main_id"]);
			if($plan_rs["typeid"] == '2'){
				$obj_client = spClass("client");
				if(!$this->client_rs = $obj_client->getClientById($plan_rs["client_id"]))
					throw new Exception("找不到该客户");
				if(date("Y-m-d", $plan_rs["createtime"]) != date("Y-m-d", time()))
					throw new Exception("只能修改当天添加的客户计划");
			}elseif($plan_rs["typeid"] == '3'){
				$obj_channel = spClass("channel");
				if(!$this->channel_rs = $obj_channel->getChannelById($plan_rs["channel_id"]))
					throw new Exception("找不到该渠道");
				if(date("Y-m-d", $plan_rs["createtime"]) != date("Y-m-d", time()))
					throw new Exception("只能修改当天添加的客户计划");
			}
			$backurl = $_SERVER['HTTP_REFERER'];
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try {
					$postdata = $this->spArgs();
					$data = array();
					if($plan_rs["typeid"] != '1'){
						$data["title"] = $plan_rs["title"];
						$data["main_id"] = $plan_rs["main_id"];
					}else{
						$data["title"] = $postdata["title"];
						if($postdata["main_id"])
							$data["main_id"] = implode(",", $postdata["main_id"]);
					}
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
					spClass('user_log')->save_log(10, "更新了日程，主题为[".$data["title"]."] [id:{$id}]", array("client_id"=>$plan_rs["client_id"], "channel_id"=>$plan_rs["channel_id"]));
					$backurl = $postdata["backurl"] ? $postdata["backurl"] : spUrl("schedule", "myplanlist");
					$message = array('msg'=>"日程修改成功", "url"=>$backurl, 'result'=>1);
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
			$obj_user = spClass("user");
			$this->user_prep_rs = $obj_user->getUserGroupDepart_prep();
			$this->display("schedule/createplan.html");
		}catch(Exception $e){
			$this->redirect(spUrl("schedule", "myplanlist"), $e->getMessage());
		}
	}
	
	//修改客户计划
	public function modifyplan_status(){
		try {
			if(!$id = intval($this->spArgs("id")))
				throw new Exception("参数丢失");
			$obj_plan = spClass("client_plan");
			if(!$plan_rs = $obj_plan->find(array("create_id"=>$_SESSION["sscrm_user"]["id"], "id"=>$id)))
				throw new Exception("找不到该日程，可能是参数错误");
			if($plan_rs["typeid"] == '2'){
				$obj_client = spClass("client");
				if(!$this->client_rs = $obj_client->getClientById($plan_rs["client_id"]))
					throw new Exception("找不到该客户");
			}elseif($plan_rs["typeid"] == '3'){
				$obj_channel = spClass("channel");
				if(!$this->channel_rs = $obj_channel->getChannelById($plan_rs["channel_id"]))
					throw new Exception("找不到该渠道");
			}
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
					spClass('user_log')->save_log(10, "将日程主题为[".$plan_rs["title"]."] [id:{$id}] 转为已完成", array("client_id"=>$plan_rs["client_id"], "channel_id"=>$plan_rs["channel_id"]));
					$backurl = $postdata["backurl"] ? $postdata["backurl"] : spUrl("schedule", "myplanlist");
					$message = array('msg'=>"日程修改成功", "url"=>$backurl, 'result'=>1);
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
			$obj_user = spClass("user");
			$user_rs = $obj_user->find("find_in_set(id, '{$plan_rs["main_id"]}')", null, "group_concat(realname) as realname_main");
			$plan_rs["realname_main"] = $user_rs["realname_main"];
			$this->plan_rs = $plan_rs;
			$this->user_prep_rs = $obj_user->getUser_prep();
		}catch(Exception $e){
			$this->redirect(spUrl("schedule", "myplanlist"), $e->getMessage());
		}
	}
	
	//修改客户计划状态
	public function deleteplan(){
		try {
			if(!$id = intval($this->spArgs("id")))
				throw new Exception("参数丢失");
			$obj_plan = spClass("client_plan");
			if(!$plan_rs = $obj_plan->join("crm_user")->find(array("crm_client_plan.create_id"=>$_SESSION["sscrm_user"]["id"], "crm_client_plan.id"=>$id), null, "crm_client_plan.*, crm_user.realname"))
				throw new Exception("找不到该日程，可能是参数错误");
			if($plan_rs["typeid"] != "1")
				throw new Exception("该日程无法删除");
			if($plan_rs["isfinish"])
				throw new Exception("该日程已经完成，无法进行该操作");
			$backurl = $_SERVER['HTTP_REFERER'];
			$postdata = $this->spArgs();
			if(!$obj_plan->delete(array("id"=>$id)))
				throw new Exception("未知错误，删除失败");
			spClass('user_log')->save_log(10, "删除了日程 将日程[{$plan_rs["title"]}] [id:{$id}]删除", array("client_id"=>$plan_rs["client_id"]));
			$backurl = $postdata["backurl"] ? $postdata["backurl"] : spUrl("schedule", "myplanlist");
			$message = array('msg'=>"日程状态删除成功", "url"=>$backurl, 'result'=>1);
			echo json_encode($message);
			exit();
		}catch(Exception $e){
			$message = array('msg'=>$e->getMessage(), 'result'=>0);
			echo json_encode($message);
			exit();
		}
	}
	
	public function calendar(){
		try {
			if(!$user_id = $this->spArgs("user_id"))
				throw new Exception("请先选择查看谁再进入该页");
			$obj_user = spClass("user");
			if(!$user_rs = $obj_user->getUserById($user_id))
				throw new Exception("找不到该员工，可能参数错误");
			$this->user_id = $user_id;
			$this->user_rs = $user_rs;
		}catch(Exception $e){
			$this->redirect(spUrl("schedule", "planlist"), $e->getMessage());
		}
		
	}
	
	public function planlist(){
		try {
			$postdata = $this->spArgs();
			$obj_user = spClass("user");
			$obj_plan = spClass("client_plan");
			$obj_dpt = spClass("department");
			$obj_channel = spClass('channel');
			$obj_client = spClass('client');
			$page = intval(max($postdata['page'], 1));
			$condition = "1";
			if($user_id = intval($postdata["user_id"])){
				$condition .= " and find_in_set({$user_id}, crm_client_plan.main_id)";
				$this->user_id = $user_id;
			}
			if(($depart_id = intval($postdata["depart_id"])) && $user_list_rs = $obj_user->findAll(array("depart_id"=>$postdata["depart_id"]), null, "id")){
				$in_condition = "0";
				foreach($user_list_rs as $val){
					$in_condition .= " or find_in_set({$val["id"]}, crm_client_plan.main_id)";
				}
				$condition .= " and ({$in_condition})";
				$this->depart_id = $depart_id;
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
			if($plan_rs = $obj_plan->join("crm_user as create_user", "create_user.id = crm_client_plan.create_id")->spPager($page, 20)->findAll($condition, "crm_client_plan.createtime desc", "crm_client_plan.*, create_user.realname as realname_create")){
				foreach($plan_rs as $key => $val){
					$user_rs = $obj_user->find("find_in_set(id, '{$val["main_id"]}')", null, "group_concat(realname) as realname_main");
					$plan_rs[$key]["realname_main"] = $user_rs["realname_main"];
					if($val["client_id"]){
						$client_rs = $obj_client->findByPk($val["client_id"], "realname");
						$plan_rs[$key]["title"] = "联系客户【{$client_rs["realname"]}】";
					}
					if($val["channel_id"]){
						$channel_rs = $obj_channel->findByPk($val["channel_id"], "mechanism");
						$plan_rs[$key]["title"] = "联系渠道【{$channel_rs["mechanism"]}】";
					}
				}
				$this->plan_rs = $plan_rs;
			}
			$this->pager = $obj_plan->spPager()->getPager();
			$this->url = spUrl('schedule', 'planlist', array("status"=>$this->status));
			$obj_user = spClass("user");
			//$this->user_prep_rs = $obj_user->getUser_prep();
			$this->user_prep_rs = $obj_user->getUserGroupDepart_prep();
			$this->depart_rs = $obj_dpt->getlist();
		}catch(Exception $e){
			$this->redirect(spUrl("schedule", "planlist"), $e->getMessage());
		}
	}
}
?>