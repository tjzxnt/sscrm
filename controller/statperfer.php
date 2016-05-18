<?php
class statperfer extends spController {
	
	public function __construct(){
		parent::__construct();
		try {
			$obj_cpt = spClass("department_competence");
			$auth_action = array(
				"marketstat" => "marketstat",
				"marketuserinfo" => "marketstat",
				"salestat" => "salestat",
				"saleuserinfo" => "salestat",
				"mystatinfo" => "EVERYONE",
				"statsave" => "EVERYONE"
			);
			if(
				$auth_action[$this->spArgs("a")] != "EVERYONE" 
				&& 
				!in_array($auth_action[$this->spArgs("a")], $_SESSION["sscrm_user"]["auth_mark"])
				&&
				!$_SESSION["sscrm_user"]["user_identity"]["operate"]["enabled"]
				&& 
				!$_SESSION["sscrm_user"]["user_identity"]["settle"]["enabled"]
			)
				 $obj_cpt->check_login_competence("PERFORMANCE");
			$this->statperferpath = strtolower($_SERVER['HTTP_HOST']) == "localhost" ? "data/statperfer_local/" :"data/statperfer/";
			$this->marketpath = strtolower($_SERVER['HTTP_HOST']) == "localhost" ? "data/pertasks_local/markettask/" : "data/pertasks/markettask/";
			$this->salepath = strtolower($_SERVER['HTTP_HOST']) == "localhost" ? "data/pertasks_local/saletask/" : "data/pertasks/saletask/";
		}catch(Exception $e){
			$backurl = spUrl("main", "welcome");
			$this->redirect($backurl, $e->getMessage());
			exit();
		}
	}
	

	public function marketstat($statdate){
		$user = spClass('user');
		if(!$statdate)
			$statdate = $this->spArgs("statdate") ? date("Y-m", strtotime($this->spArgs("statdate"))) : date("Y-m");
		$this->statdate = $statdate;
		$user_rs = $user->marketstat($this->statdate);
		$this->user_rs = $user_rs;
	}
	
	public function marketuserinfo($id){
		try {
			$id = $id ? $id: intval($this->spArgs("id"));
			if(!$id)
				throw new Exception("缺少参数");
			$depart_id = 2;
			$statdate = $this->spArgs("statdate") ? date("Y-m", strtotime($this->spArgs("statdate"))) : date("Y-m");
			$this->statdate = $statdate;
			$user = spClass('user');
			if(!$userinfo_rs = $user->getCommonUserinfo($id, $statdate))
				throw new Exception("找不到该员工，可能已被删除");
			if($userinfo_rs["depart_id"] != $depart_id)
				throw new Exception("该员工不处于市场部，无法查看该功能");
			$user_rs = $user->marketstat($this->statdate, "crm_user.id = $id");
			$this->user_rs = $user_rs;
			$obj_client = spClass('client');
			$clientstat_rs = $obj_client->clientstat($userinfo_rs, $this->statdate);
			$this->clientstat_rs = $clientstat_rs;
			$clientstat_ext_rs = $obj_client->clientstat_ext($userinfo_rs, $this->statdate);
			$this->clientstat_ext_rs = $clientstat_ext_rs;
			if(file_exists($this->marketpath . "$statdate.php")){
				$file_exists = 1;
				include($this->marketpath . "$statdate.php");
				$task_config["all_total"] = $task_config["together_total"] + $task_config["self_total"];
				$this->task_config = $task_config;
			}
			if($userinfo_rs["isdirector"]){
				$actual = array("together_total"=>0, "self_total"=>0, "total"=>0);
				if($user_data_rs = $user->getUserByDepart($depart_id, null, "crm_user.isdirector desc, crm_user.createtime asc", 1)){
					foreach($user_data_rs as $key => $val){
						$user_data_rs[$key]["analysis"] = $obj_client->origin_analysis($val, $statdate);
						if($val["id"] == $userinfo_rs["id"])
							$userinfo_rs["analysis"] = $user_data_rs[$key]["analysis"];
						if($val["isdel"] && !intval($user_data_rs[$key]["analysis"]["together_total"])){
							unset($user_data_rs[$key]);
							continue;
						}
						$actual["together_total"] += intval($user_data_rs[$key]["analysis"]["together_total"]);
						$actual["self_total"] += intval($user_data_rs[$key]["analysis"]["self_total"]);
						$actual["total"] += intval($user_data_rs[$key]["analysis"]["total"]);
					}
				}
				$this->depart_task = 1;
			}else{
				$userinfo_rs["analysis"] = $obj_client->origin_analysis($userinfo_rs, $statdate);
				$user_data_rs[0] = $userinfo_rs;
			}
			$this->user_data_rs = $user_data_rs;
			$this->actual = $actual;
			$this->userinfo_rs = $userinfo_rs;
		}catch(Exception $e){
			$this->redirect(spUrl("statperfer", "salestat"), $e->getMessage());
			exit();
		}
	}
	
	public function salestat($statdate){
		$user = spClass('user');
		$obj_department = spClass('department');
		$obj_sep = spClass('department_sep');
		if(!$statdate)
			$statdate = $this->spArgs("statdate") ? date("Y-m", strtotime($this->spArgs("statdate"))) : date("Y-m");
		$extcondition = "";
		try {
			$depart_id = 3;
			$depart_rs = $obj_department->getinfoById($depart_id);
			if($depart_rs["is_sep"]){
				if($_SESSION["sscrm_user"]["depart_id"] == 3){
					if(!$sep_id = $_SESSION["sscrm_user"]["depart_sep_id"])
						throw new Exception("您尚未分配到".$depart_rs["dname"]."下的所在组，请尝试重新登录");
					if(!$sep_rs = $obj_sep->find(array("depart_id"=>$depart_id, "id"=>$sep_id)))
						throw new Exception("您所在的组不正确，请尝试重新登录");
				}else{
					$this->sep_all_rs = $sep_all_rs = $obj_sep->findAll(array("depart_id"=>$depart_id, "ishide"=>0), "sort asc");
					$sep_id = $this->spArgs("sep_id") ? $this->spArgs("sep_id") : $sep_all_rs[0]["id"];
					$sep_rs = $obj_sep->find(array("id"=>$sep_id), "sort asc");
					$this->sep_id = $sep_id;
					$this->viewall = 1;
				}
				$this->sep_name = "(".$sep_rs["sep_name"].")";
				$filename = $statdate."_sep_".$sep_id.".php";
				$extcondition = "depart_sep_id = $sep_id";
			}else{
				$filename = "$statdate.php";
			}
		}catch(Exception $e){
			$this->redirect(spUrl("main", "welcome"), $e->getMessage());
			exit();
		}
		$this->statdate = $statdate;
		$user_rs = $user->salestat($this->statdate, $extcondition);
		$this->user_rs = $user_rs;
	}
	
	public function saleuserinfo($id){
		try {
			$id = $id ? $id: intval($this->spArgs("id"));
			if(!$id)
				throw new Exception("缺少参数");
			$depart_id = 3;
			$statdate = $this->spArgs("statdate") ? date("Y-m", strtotime($this->spArgs("statdate"))) : date("Y-m");
			$this->statdate = $statdate;
			$user = spClass('user');
			$obj_department = spClass('department');
			$obj_sep = spClass('department_sep');
			if(!$userinfo_rs = $user->getCommonUserinfo($id, $statdate))
				throw new Exception("找不到该员工，可能已被删除");
			if($userinfo_rs["depart_id"] != $depart_id)
				throw new Exception("该员工不处于销售部，无法查看该功能");
			$user_rs = $user->salestat($this->statdate, "crm_user.id = $id");
			$this->user_rs = $user_rs;
			$obj_client = spClass('client');
			$clientstat_rs = $obj_client->clientstat($userinfo_rs, $this->statdate);
			$this->clientstat_rs = $clientstat_rs;
			$clientstat_ext_rs = $obj_client->clientstat_ext($userinfo_rs, $this->statdate);
			$this->clientstat_ext_rs = $clientstat_ext_rs;
			try {
				$extcondition = "";
				$depart_rs = $obj_department->getinfoById($depart_id);
				if($depart_rs["is_sep"]){
					if($_SESSION["sscrm_user"]["depart_id"] == 3){
						if(!$sep_id = $_SESSION["sscrm_user"]["depart_sep_id"])
							throw new Exception("您尚未分配到".$depart_rs["dname"]."下的所在组，请尝试重新登录");
						if(!$sep_rs = $obj_sep->find(array("depart_id"=>$depart_id, "id"=>$sep_id)))
							throw new Exception("您所在的组不正确，请尝试重新登录");
					}else{
						$sep_id = $user_rs[0]["depart_sep_id"];
						$sep_rs = $obj_sep->find(array("id"=>$sep_id), "sort asc");
						$this->sep_id = $sep_id;
						$this->viewall = 1;
					}
					$this->sep_name = "(".$sep_rs["sep_name"].")";
					$filename = $statdate."_sep_".$sep_id.".php";
					$extcondition = "depart_sep_id = $sep_id";
				}else{
					$filename = "$statdate.php";
				}
			}catch(Exception $e){
				$this->redirect(spUrl("main", "welcome"), $e->getMessage());
				exit();
			}
			if(file_exists($this->salepath . $filename)){
				$file_exists = 1;
				include($this->salepath . $filename);
				$task_config["all_total"] = $task_config["together_total"] + $task_config["self_total"];
				$this->task_config = $task_config;
			}
			if($userinfo_rs["isdirector"]){
				$actual = array("together_total"=>0, "self_total"=>0, "total"=>0);
				$data_condition = "find_in_set('getclient', crm_user.identity_attr)";
				if($extcondition)
					$data_condition .= " and $extcondition";
				if($user_data_rs = $user->getUserByDepart($depart_id, $data_condition, "crm_user.isdirector desc, crm_user.createtime asc", 1)){
					foreach($user_data_rs as $key => $val){
						$user_data_rs[$key]["analysis"] = $obj_client->origin_analysis($val, $statdate);
						if($val["id"] == $userinfo_rs["id"])
							$userinfo_rs["analysis"] = $user_data_rs[$key]["analysis"];
						if($val["isdel"] && !intval($user_data_rs[$key]["analysis"]["together_total"])){
							unset($user_data_rs[$key]);
							continue;
						}
						$actual["together_total"] += intval($user_data_rs[$key]["analysis"]["together_total"]);
						$actual["self_total"] += intval($user_data_rs[$key]["analysis"]["self_total"]);
						$actual["total"] += intval($user_data_rs[$key]["analysis"]["total"]);
					}
				}
				$this->depart_task = 1;
			}else{
				$userinfo_rs["analysis"] = $obj_client->origin_analysis($userinfo_rs, $statdate);
				$user_data_rs[0] = $userinfo_rs;
			}
			$this->user_data_rs = $user_data_rs;
			$this->actual = $actual;
			$this->userinfo_rs = $userinfo_rs;
		}catch(Exception $e){
			$this->redirect(spUrl("statperfer", "salestat"), $e->getMessage());
			exit();
		}
	}
	
	public function overseastat($statdate, $config){
		$this->department = "海外部";
		$this->depart_id = 4;
		$this->action = "overseastat";
		$this->info_action = "overseauserinfo";
		$this->otherlist($statdate, $config);
	}
	
	public function overseauserinfo($id, $statdate, $config){
		$id = $id ? $id: intval($this->spArgs("id"));
		$this->department = "海外部";
		$this->depart_id = 4;
		$this->action = "overseastat";
		$this->info_action = "overseauserinfo";
		$this->otherinfo($id, $statdate, $config);
	}
	
	public function butlerstat($statdate, $config){
		$this->department = "行政部";
		$this->depart_id = 6;
		$this->action = "butlerstat";
		$this->info_action = "butleruserinfo";
		$this->otherlist($statdate, $config);
	}
	
	public function butleruserinfo($id, $statdate, $config){
		$id = $id ? $id: intval($this->spArgs("id"));
		$this->department = "行政部";
		$this->depart_id = 6;
		$this->action = "butlerstat";
		$this->info_action = "butleruserinfo";
		$this->otherinfo($id, $statdate, $config);
	}
	
	public function techstat($statdate, $config){
		$this->department = "技术部";
		$this->depart_id = 5;
		$this->action = "techstat";
		$this->info_action = "techuserinfo";
		$this->otherlist($statdate, $config);
	}
	
	public function techuserinfo($id, $statdate, $config){
		$id = $id ? $id: intval($this->spArgs("id"));
		$this->department = "技术部";
		$this->depart_id = 5;
		$this->action = "techstat";
		$this->info_action = "techuserinfo";
		$this->otherinfo($id, $statdate, $config);
	}
	
	public function advertstat($statdate, $config){
		$this->department = "广宣部";
		$this->depart_id = 8;
		$this->action = "advertstat";
		$this->info_action = "advertuserinfo";
		$this->otherlist($statdate, $config);
	}
	
	public function advertuserinfo($id, $statdate, $config){
		$id = $id ? $id: intval($this->spArgs("id"));
		$this->department = "广宣部";
		$this->depart_id = 8;
		$this->action = "advertstat";
		$this->info_action = "advertuserinfo";
		$this->otherinfo($id, $statdate, $config);
	}
	
	//我的业绩统计
	public function mystatinfo(){
		$depart_id = $_SESSION["sscrm_user"]["depart_id"];
		$user_id = $_SESSION["sscrm_user"]["id"];
		$this->real_action = "mystatinfo";
		switch($depart_id){
			case "2":
				$this->marketuserinfo($user_id);
				$this->display("statperfer/marketuserinfo.html");
			break;
			case "3":
				$this->saleuserinfo($user_id);
				$this->display("statperfer/saleuserinfo.html");
			break;
			case "4":
				$this->overseauserinfo($user_id);
			break;
			case "5":
				$this->techuserinfo($user_id);
			break;
			case "6":
				$this->butleruserinfo($user_id);
			break;
		}
	}
	
	public function channelstat(){
		$this->mtype = "渠道";
		$this->nowaction = "channelstat";
		$this->actionlist = "channelstat";
		$this->actioninfo = "channelinfostat";
		$this->statdate = $this->spArgs("statdate") ? date("Y-m", strtotime($this->spArgs("statdate"))) : date("Y-m");
		$obj_client = spClass('client');
		$this->agency_rs =  $obj_client->channel_analysis($this->statdate);
		$this->display("statperfer/agencystat.html");
	}
	
	public function channelinfostat(){
		$this->mtype = "渠道";
		$this->nowaction = "channelinfostat";
		$this->actionlist = "channelstat";
		$this->actioninfo = "channelinfostat";
		$this->statdate = $this->spArgs("statdate") ? date("Y-m", strtotime($this->spArgs("statdate"))) : date("Y-m");
		try {
			$id = intval($this->spArgs("id"));
			$obj_client = spClass('client');
			$obj_channel = spClass('channel');
			$this->agency_rs =  $obj_client->channel_analysis($this->statdate, "crm_channel.id = $id");
			if(!$info_rs = $obj_channel->findByPk($id, "*, mechanism as agency_name"))
				throw new Exception("找不到该渠道，可能参数错误");
			$this->info_rs = $info_rs;
			$this->visit_rs = $obj_client->channel_visit_analysis($this->statdate, $id);
			$this->order_rs = $obj_client->channel_order_analysis($this->statdate, $id);
			$this->display("statperfer/agencyinfostat.html");
		}catch(Exception $e){
			$this->redirect(spUrl("statperfer", $this->actionlist), $e->getMessage());
			exit();
		}
	}
	
	public function traderstat(){
		$this->mtype = "分销商";
		$this->nowaction = "traderstat";
		$this->actionlist = "traderstat";
		$this->actioninfo = "traderinfostat";
		$this->statdate = $this->spArgs("statdate") ? date("Y-m", strtotime($this->spArgs("statdate"))) : date("Y-m");
		$obj_client = spClass('client');
		$this->agency_rs = $obj_client->trader_analysis($this->statdate);
		$this->display("statperfer/agencystat.html");
	}
	
	public function traderinfostat(){
		$this->mtype = "分销商";
		$this->nowaction = "traderinfostat";
		$this->actionlist = "traderstat";
		$this->actioninfo = "traderinfostat";
		$this->statdate = $this->spArgs("statdate") ? date("Y-m", strtotime($this->spArgs("statdate"))) : date("Y-m");
		try {
			$id = intval($this->spArgs("id"));
			$obj_client = spClass('client');
			$obj_trader = spClass('trader');
			$this->agency_rs =  $obj_client->trader_analysis($this->statdate, "crm_trader.id = $id");
			if(!$info_rs = $obj_trader->findByPk($id, "*, tradername as agency_name"))
				throw new Exception("找不到该分销商，可能参数错误");
			$this->info_rs = $info_rs;
			$this->visit_rs = $obj_client->trader_visit_analysis($this->statdate, $id);
			$this->order_rs = $obj_client->trader_order_analysis($this->statdate, $id);
			$this->display("statperfer/agencyinfostat.html");
		}catch(Exception $e){
			$this->redirect(spUrl("statperfer", $this->actionlist), $e->getMessage());
			exit();
		}
	}
	
	public function travelstat(){
		$this->mtype = "旅行社";
		$this->nowaction = "travelstat";
		$this->actionlist = "travelstat";
		$this->actioninfo = "travelinfostat";
		$this->statdate = $this->spArgs("statdate") ? date("Y-m", strtotime($this->spArgs("statdate"))) : date("Y-m");
		$obj_client = spClass('client');
		$this->agency_rs = $obj_client->travel_analysis($this->statdate);
		$this->display("statperfer/agencystat.html");
	}
	
	public function travelinfostat(){
		$this->mtype = "旅行社";
		$this->nowaction = "travelstat";
		$this->actionlist = "travelstat";
		$this->actioninfo = "travelinfostat";
		$this->statdate = $this->spArgs("statdate") ? date("Y-m", strtotime($this->spArgs("statdate"))) : date("Y-m");
		try {
			$id = intval($this->spArgs("id"));
			$obj_client = spClass('client');
			$obj_travel = spClass('travel');
			$this->agency_rs =  $obj_client->travel_analysis($this->statdate, "crm_travel.id = $id");
			if(!$info_rs = $obj_travel->findByPk($id, "*, travelname as agency_name"))
				throw new Exception("找不到该旅行社，可能参数错误");
			$this->info_rs = $info_rs;
			$this->visit_rs = $obj_client->travel_visit_analysis($this->statdate, $id);
			$this->order_rs = $obj_client->travel_order_analysis($this->statdate, $id);
			$this->display("statperfer/agencyinfostat.html");
		}catch(Exception $e){
			$this->redirect(spUrl("statperfer", $this->actionlist), $e->getMessage());
			exit();
		}
	}
	
	//保存统计快照
	public function statsave(){
		try {
			import("Common.php");
			$statdate = date("Y-m", strtotime("-1 month"));
			$this->statlist_save(array("stat_list"=>"marketstat", "stat_info"=>"marketuserinfo", "depart_id"=>2), $statdate);
			$this->statlist_save(array("stat_list"=>"salestat", "stat_info"=>"saleuserinfo", "depart_id"=>3), $statdate);
			$this->statlist_save(array("stat_list"=>"overseastat", "stat_info"=>"overseauserinfo", "depart_id"=>4), $statdate);
			$this->statlist_save(array("stat_list"=>"butlerstat", "stat_info"=>"overseauserinfo", "depart_id"=>6), $statdate);
			$this->statlist_save(array("stat_list"=>"techstat", "stat_info"=>"butleruserinfo", "depart_id"=>5), $statdate);
			$this->statlist_save(array("stat_list"=>"advertstat", "stat_info"=>"advertuserinfo", "depart_id"=>8), $statdate);
			
			$this->statinfo_save(array("stat_list"=>"advertstat", "stat_info"=>"advertuserinfo", "depart_id"=>8), $statdate);
			$this->statinfo_save(array("stat_list"=>"salestat", "stat_info"=>"saleuserinfo", "depart_id"=>3), $statdate);
			$this->statinfo_save(array("stat_list"=>"overseastat", "stat_info"=>"overseauserinfo", "depart_id"=>4), $statdate);
			$this->statinfo_save(array("stat_list"=>"butlerstat", "stat_info"=>"overseauserinfo", "depart_id"=>6), $statdate);
			$this->statinfo_save(array("stat_list"=>"techstat", "stat_info"=>"butleruserinfo", "depart_id"=>5), $statdate);
			$this->statinfo_save(array("stat_list"=>"advertstat", "stat_info"=>"advertuserinfo", "depart_id"=>8), $statdate);
		}catch(Exception $e){
			header("Content-type: text/html; charset=utf-8");
			echo $e->getMessage();
			exit();
		}
	}

	//业绩列表快照保存
	private function statlist_save($dconfig, $statdate){
		$stat_list = $dconfig["stat_list"];
		$stat_info = $dconfig["stat_info"];
		$depart_id = $dconfig["depart_id"];
		$this->$stat_list($statdate, array("nodisplay" => 1));
		if(in_array($stat_list, array("marketstat", "salestat"))){
			$stathtml = $this->fetch("statperfer/$stat_list.html");
		}else{
			$stathtml = $this->fetch("statperfer/otherstat.html");
		}
		$file_dir = $this->statperferpath . "$stat_list/" . $statdate;
		if(!is_dir($file_dir))
			Common::rmkdir($file_dir);
		$file_path = $file_dir."/list.html";
		$fp = fopen($file_path, 'w');
		if (!fputs($fp,$stathtml))
			throw new Exception("未知错误，$stat_list $statdate 业绩快照更新失败");
		fclose($fp);
		unset($fp);
	}
	
	private function statinfo_save($dconfig, $statdate){
		$stat_list = $dconfig["stat_list"];
		$stat_info = $dconfig["stat_info"];
		$depart_id = $dconfig["depart_id"];
		$user = spClass('user');
		$obj_client = spClass('client');
		if($user_data_rs = $user->getUserByDepart($depart_id, "find_in_set('getclient', crm_user.identity_attr)", "crm_user.isdirector desc, crm_user.createtime asc", 1)){
			foreach($user_data_rs as $key => $val){
				$user_data_rs[$key]["analysis"] = $obj_client->origin_analysis($val, $statdate);
				if($val["isdel"] && !intval($user_data_rs[$key]["analysis"]["together_total"])){
					unset($user_data_rs[$key]);
					continue;
				}
				$this->$stat_info($val[id], $statdate, array("nodisplay" => 1));
				if(in_array($stat_info, array("marketuserinfo", "saleuserinfo"))){
					$statinfohtml = $this->fetch("statperfer/$stat_info.html");
				}else{
					$statinfohtml = $this->fetch("statperfer/otheruserinfo.html");
				}
				$file_dir = $this->statperferpath . "$stat_list/" . $statdate . "/info";
				if(!is_dir($file_dir))
					Common::rmkdir($file_dir);
				$file_path = $file_dir."/$val[id].html";
				$fp = fopen($file_path, 'w');
				if (!fputs($fp, $statinfohtml))
					throw new Exception("未知错误，$stat_info $statdate 业绩快照更新失败");
				fclose($fp);
				unset($fp);
			}
		}
	}
	
	//其他部门的业绩统计列表
	private function otherlist($statdate, $config){
		$user = spClass('user');
		if(!$statdate)
			$statdate = $this->spArgs("statdate") ? date("Y-m", strtotime($this->spArgs("statdate"))) : date("Y-m");
		$this->statdate = $statdate;
		$user_rs = $user->otherstat($this->statdate, "depart_id = ".$this->depart_id);
		$this->user_rs = $user_rs;
		if(!$config["nodisplay"])
			$this->display("statperfer/otherstat.html");
	}
	
	//其他部门的业绩详细
	private function otherinfo($id, $statdate, $config){
		try {
			if(!$id)
				throw new Exception("缺少参数");
			$user = spClass('user');
			if(!$userinfo_rs = $user->getCommonUserinfo($id))
				throw new Exception("找不到该员工，可能已被删除");
			if(in_array($userinfo_rs["depart_id"], array(2,3)))
				throw new Exception("该员工处于销售部或海外部，无法查看该功能");
			$this->userinfo_rs = $userinfo_rs;
			if(!$statdate)
				$statdate = $this->spArgs("statdate") ? date("Y-m", strtotime($this->spArgs("statdate"))) : date("Y-m");
			$this->statdate = $statdate;
			$user_rs = $user->otherstat($this->statdate, "crm_user.id = $id and depart_id = ".$this->depart_id);
			$this->user_rs = $user_rs;
			$obj_client = spClass('client');
			$clientstat_rs = $obj_client->clientstat($userinfo_rs, $this->statdate);
			$this->clientstat_rs = $clientstat_rs;
			$clientstat_ext_rs = $obj_client->clientstat_ext($userinfo_rs, $this->statdate);
			$this->clientstat_ext_rs = $clientstat_ext_rs;
			if(!$config["nodisplay"])
				$this->display("statperfer/otheruserinfo.html");
		}catch(Exception $e){
			$this->redirect(spUrl("statperfer", $this->action), $e->getMessage());
			exit();
		}
	}
}
?>