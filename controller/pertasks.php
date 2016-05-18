<?php
class pertasks extends spController {
	
	public function __construct(){
		parent::__construct();
		try {
			$obj_cpt = spClass("department_competence");
			$auth_action = array(
				"markettasklist" => "markettask",
				"saletasklist" => "saletask",
				"departasklist" => "EVERYONE"
			);
			if($auth_action[$this->spArgs("a")] != "EVERYONE" && !in_array($auth_action[$this->spArgs("a")], $_SESSION["sscrm_user"]["auth_mark"]))
				$obj_cpt->check_login_competence("PERTASK");
			$this->marketpath = strtolower($_SERVER['HTTP_HOST']) == "localhost" ? "data/pertasks_local/markettask/" : "data/pertasks/markettask/";
			$this->salepath = strtolower($_SERVER['HTTP_HOST']) == "localhost" ? "data/pertasks_local/saletask/" : "data/pertasks/saletask/";
		}catch(Exception $e){
			$backurl = spUrl("main", "welcome");
			$this->redirect($backurl, $e->getMessage());
			exit();
		}
	}
	
	public function markettasklist(){
		$obj_user = spClass("user");
		$obj_client = spClass("client");
		$obj_department = spClass('department');
		$obj_sep = spClass('department_sep');
		$statdate = $this->spArgs("statdate") ? date("Y-m", strtotime($this->spArgs("statdate"))) : date("Y-m");
		try {
			$depart_id = 2;
			$depart_rs = $obj_department->getinfoById($depart_id);
			if($depart_rs["is_sep"]){
				if(!$sep_id = $_SESSION["sscrm_user"]["depart_sep_id"])
					throw new Exception("您尚未分配到".$depart_rs["dname"]."下的所在组，请尝试重新登录");
				if(!$sep_rs = $obj_sep->find(array("depart_id"=>$depart_id, "id"=>$sep_id)))
					throw new Exception("您所在的组不正确，请尝试重新登录");
				$sep_name = "(".$sep_rs["sep_name"].")";
				$filename = $statdate."_sep_".$sep_id.".php";
			}else{
				$filename = "$statdate.php";
			}
		}catch(Exception $e){
			$this->redirect(spUrl("main", "welcome"), $e->getMessage());
			exit();
		}
		if(file_exists($this->marketpath .$filename)){
			$file_exists = 1;
			include($this->marketpath . $filename);
			$task_config["all_total"] = $task_config["together_total"] + $task_config["self_total"];
			$this->task_config = $task_config;
		}
		if($_SERVER['REQUEST_METHOD'] == 'POST') {
			try {
				if($file_exists)
					throw new Exception("市场部 $sep_name $statdate 任务已下达，无法再次提交");
				$data = array();
				$postdata = $this->spArgs();
				/*
				$together_total = 0;
				foreach($postdata["together"] as $key => $val){
					$data["together"][$key] = intval(str_replace(",", "", $val));
					if($data["together"][$key] <= 0)
						throw new Exception("合力成单金额不能为空");
					$together_total += $data["together"][$key];
				}
				$data["together_total"] = intval(str_replace(",", "", $postdata["together_total"]));
				if($data["together_total"] != $together_total)
					throw new Exception("合力成单总数不正确");
				*/
				$self_total = 0;
				foreach($postdata["self"] as $key => $val){
					$data["self"][$key] = intval(str_replace(",", "", $val));
					$self_total += $data["self"][$key];
				}
				$data["self_total"] = intval(str_replace(",", "", $postdata["self_total"]));
				if($data["self_total"] <= 0)
					throw new Exception("部门总任务不能为空");
				if($data["self_total"] != $self_total)
					throw new Exception("部门总任务不正确");
				$str = "<?php\r\n\$task_config = ".var_export($data, true).';';
				$fp = fopen($this->marketpath . $filename, 'w');
				if (!fputs($fp,$str))
					throw new Exception("未知错误，$statdate 任务更新失败");
				spClass('user_log')->save_log(9, "添加了市场部 $sep_name $statdate 的业绩任务");
				$message = array('msg'=>"$statdate 任务更新成功！",'result'=>1, 'url'=>$url);
				echo json_encode($message);
				exit();
			}catch(Exception $e){
				$message = array('msg'=>$e->getMessage(),'result'=>0);
				echo json_encode($message);
				exit();
			}
		}
		$this->statdate = $statdate;
		$actual = array("self_total"=>0);
		$condition = "1";
		if($sep_id)
			$condition .= " and crm_user.depart_sep_id = $sep_id";
		if($user_data_rs = $obj_user->getUserByDepart(2, $condition, "crm_user.isdirector desc, crm_user.createtime asc", 1)){
			foreach($user_data_rs as $key => $val){
				$user_data_rs[$key]["analysis"] = $obj_client->origin_analysis($val, $statdate);
				if($val["isdel"] && !intval($user_data_rs[$key]["analysis"]["together_total"])){
					unset($user_data_rs[$key]);
					continue;
				}
				$actual["together_total"] += intval($user_data_rs[$key]["analysis"]["together_total"]);
				$actual["self_total"] += intval($user_data_rs[$key]["analysis"]["self_total"]);
				$actual["total"] += intval($user_data_rs[$key]["analysis"]["total"]);
			}
		}
		$this->user_data_rs = $user_data_rs;
		$this->actual = $actual;
		$this->depart_task = 1;
		$this->depart_name = "市场部";
		$this->action = "markettasklist";
		$this->saveurl = spUrl("pertasks", "markettasklist");
		if($file_exists)
			$this->display("pertasks/tasklist_view.html");
		else
			$this->display("pertasks/tasklist.html");
	}
	
	public function saletasklist(){
		$obj_user = spClass("user");
		$obj_client = spClass("client");
		$obj_department = spClass('department');
		$obj_sep = spClass('department_sep');
		$statdate = $this->spArgs("statdate") ? date("Y-m", strtotime($this->spArgs("statdate"))) : date("Y-m");
		try {
			$depart_id = 3;
			$depart_rs = $obj_department->getinfoById($depart_id);
			if($depart_rs["is_sep"]){
				if(!$sep_id = $_SESSION["sscrm_user"]["depart_sep_id"])
					throw new Exception("您尚未分配到".$depart_rs["dname"]."下的所在组，请尝试重新登录");
				if(!$sep_rs = $obj_sep->find(array("depart_id"=>$depart_id, "id"=>$sep_id)))
					throw new Exception("您所在的组不正确，请尝试重新登录");
				$sep_name = "(".$sep_rs["sep_name"].")";
				$filename = $statdate."_sep_".$sep_id.".php";
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
		if($_SERVER['REQUEST_METHOD'] == 'POST') {
			try {
				if($file_exists)
					throw new Exception("销售部 $sep_name $statdate 任务已下达，无法再次提交");
				$data = array();
				$postdata = $this->spArgs();
				/* 取消合力成单
				$together_total = 0;
				foreach($postdata["together"] as $key => $val){
					$data["together"][$key] = intval(str_replace(",", "", $val));
					$together_total += $data["together"][$key];
				}
				$data["together_total"] = intval(str_replace(",", "", $postdata["together_total"]));
				if($data["together_total"] <= 0)
					throw new Exception("合力成单总数不能为空");
				if($data["together_total"] != $together_total)
					throw new Exception("合力成单总数不正确");
				*/
				$self_total = 0;
				foreach($postdata["self"] as $key => $val){
					$data["self"][$key] = intval(str_replace(",", "", $val));
					$self_total += $data["self"][$key];
				}
				$data["self_total"] = intval(str_replace(",", "", $postdata["self_total"]));
				if($data["self_total"] <= 0)
					throw new Exception("部门总任务不能为空");
				if($data["self_total"] != $self_total)
					throw new Exception("部门总任务不正确");
				$str = "<?php\r\n\$task_config = ".var_export($data, true).';';
				$fp = fopen($this->salepath . $filename, 'w');
				if (!fputs($fp,$str))
					throw new Exception("未知错误，$statdate 任务更新失败");
				spClass('user_log')->save_log(9, "添加了销售部 $sep_name $statdate 的业绩任务");
				$message = array('msg'=>"$statdate 任务更新成功！",'result'=>1, 'url'=>$url);
				echo json_encode($message);
				exit();
			}catch(Exception $e){
				$message = array('msg'=>$e->getMessage(),'result'=>0);
				echo json_encode($message);
				exit();
			}
		}
		$this->statdate = $statdate;
		$actual = array("self_total"=>0);
		$condition = "find_in_set('getclient', crm_user.identity_attr)";
		if($sep_id)
			$condition .= " and crm_user.depart_sep_id = $sep_id";
		if($user_data_rs = $obj_user->getUserByDepart(3, $condition, "crm_user.isdirector desc, crm_user.createtime asc", 1)){
			foreach($user_data_rs as $key => $val){
				$user_data_rs[$key]["analysis"] = $obj_client->origin_analysis($val, $statdate);
				if($val["isdel"] && !intval($user_data_rs[$key]["analysis"]["together_total"])){
					unset($user_data_rs[$key]);
					continue;
				}
				$actual["together_total"] += intval($user_data_rs[$key]["analysis"]["together_total"]);
				$actual["self_total"] += intval($user_data_rs[$key]["analysis"]["self_total"]);
				$actual["total"] += intval($user_data_rs[$key]["analysis"]["total"]);
			}
		}
		$this->user_data_rs = $user_data_rs;
		$this->actual = $actual;
		$this->depart_task = 1;
		$this->depart_name = "销售部 $sep_name";
		$this->action = "saletasklist";
		$this->saveurl = spUrl("pertasks", "saletasklist");
		if($file_exists)
			$this->display("pertasks/tasklist_view.html");
		else
			$this->display("pertasks/tasklist.html");
	}
	
	public function departasklist(){
		try{
			$obj_user = spClass("user");
			$obj_client = spClass("client");
			$obj_department = spClass('department');
			$obj_sep = spClass('department_sep');
			$statdate = $this->spArgs("statdate") ? date("Y-m", strtotime($this->spArgs("statdate"))) : date("Y-m");
			$depart_id = $_SESSION["sscrm_user"]["depart_id"];
			$user_id = $_SESSION["sscrm_user"]["id"];
			$depart_rs = $obj_department->getinfoById($depart_id);
			if($depart_rs["is_sep"]){
				if(!$sep_id = $_SESSION["sscrm_user"]["depart_sep_id"])
					throw new Exception("您尚未分配到".$depart_rs["dname"]."下的所在组，请尝试重新登录");
				if(!$sep_rs = $obj_sep->find(array("depart_id"=>$depart_id, "id"=>$sep_id)))
					throw new Exception("您所在的组不正确，请尝试重新登录");
				$this->sep_name = "(".$sep_rs["sep_name"].")";
				$filename = $statdate."_sep_".$sep_id.".php";
			}else{
				$filename = "$statdate.php";
			}
			switch($depart_id){
				case "2":
					$statpath = $this->marketpath . $filename;
				break;
				case "3":
					
					$statpath = $this->salepath . $filename;
				break;
				default:
					throw new Exception("您所在的部门无权访问该页面");
				break;
			}
			if(file_exists($statpath)){
				$file_exists = 1;
				include($statpath);
				$task_config["all_total"] = $task_config["together_total"] + $task_config["self_total"];
				$this->task_config = $task_config;
			}
			$this->statdate = $statdate;
			$actual = array("together_total"=>0, "self_total"=>0, "total"=>0);
			$condition = "find_in_set('getclient', crm_user.identity_attr) and crm_user.id = $user_id";
			if($sep_id)
				$condition .= " and crm_user.depart_sep_id = $sep_id";
			if($user_data_rs = $obj_user->getUserByDepart($depart_id, $condition, "crm_user.isdirector desc, crm_user.createtime asc", 1)){
				foreach($user_data_rs as $key => $val){
					$user_data_rs[$key]["analysis"] = $obj_client->origin_analysis($val, $statdate);
					if($val["isdel"] && !intval($user_data_rs[$key]["analysis"]["together_total"])){
						unset($user_data_rs[$key]);
						continue;
					}
					$actual["together_total"] += intval($user_data_rs[$key]["analysis"]["together_total"]);
					$actual["self_total"] += intval($user_data_rs[$key]["analysis"]["self_total"]);
					$actual["total"] += intval($user_data_rs[$key]["analysis"]["total"]);
				}
			}
			$this->user_data_rs = $user_data_rs;
		}catch(Exception $e){
			$backurl = spUrl("main", "welcome");
			$this->redirect($backurl, $e->getMessage());
			exit();
		}
	}
}
?>