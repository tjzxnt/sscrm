<?php
class channelverifys extends spController {
	
	public function __construct(){
		parent::__construct();
		try {
			$obj_cpt = spClass("department_competence");
			if(!$_SESSION["sscrm_user"]["user_identity"]["operate"]["enabled"]){
				$obj_cpt->check_login_competence("CHANNELVERIRY");
				if(!in_array($_SESSION["sscrm_user"]["depart_id"], array(6)))
					throw new Exception("你无权查看该页面");
				if(!$_SESSION["sscrm_user"]["isdirector"])
					throw new Exception("您无权查看该页面");
			}
		}catch(Exception $e){
			$backurl = spUrl("main", "welcome");
			$this->redirect($backurl, $e->getMessage());
			exit();
		}
	}
	
	public function verifychannellist(){
		$obj_channel = spClass('channel');
		$obj_type = spClass("channel_type");
		$obj_user = spClass('user');
		$depart_id = 2;
		$postdate = $this->spArgs();
		$page = intval(max($postdate['page'], 1));
		$user_rs = $obj_user->getUser_prep("crm_user.depart_id = $depart_id");
		$condition = "crm_channel.ishide = 0";
		if($postdate['searchkey'] != ''){
			$condition .= " and (crm_channel.mechanism like '%{$postdate['searchkey']}%' or crm_channel.main_contact like '%{$postdate['searchkey']}%' or crm_channel.main_tel like '%{$postdate['searchkey']}%')";
		}
		if($postdate['typeid']."a" != 'a'){
			$condition .= " and crm_channel.typeid = " . intval($postdate['typeid']);
			$this->typeid = $postdate['typeid'];
		}
		if($main_id = intval($postdate["main_id"])){
			$condition .= " and crm_channel.maintenance_id = $main_id";
			$this->main_id = $main_id;
		}
		if($from_id = intval($postdate["from_id"])){
			$condition .= " and crm_channel.from_id = $from_id";
			$this->from_id = $from_id;
		}
		$sort = 'crm_channel.createtime desc';
		if($postdate['sort'].'a' !== 'a'){
			switch ($postdate['sort']){
				case "overdate_desc":
					$sort = "overdate desc, ".$sort;
					$this->sort = $postdate['sort'];
				break;
				case "recordtime_desc":
					$sort = "recordtime desc, ".$sort;
					$this->sort = $postdate['sort'];
				break;
				default:
		
				break;
			}
		}
		if($channel_rs = $obj_channel->join("crm_user as cuser", "cuser.id = crm_channel.create_id")->join("crm_user as fuser", "fuser.id = crm_channel.from_id")->join("crm_user as muser", "muser.id = crm_channel.maintenance_id")->join("crm_user", "crm_user.id = crm_channel.maintenance_id")->spPager($page, 20)->findAll($condition, $sort, "crm_channel.*, cuser.realname as c_realname, fuser.realname as f_realname, muser.realname as m_realname, IF(crm_channel.issign > 0, 0, datediff(curdate(), IF(crm_channel.sign_enddate > '0000-00-00', crm_channel.sign_enddate, FROM_UNIXTIME(crm_channel.createtime, '%Y-%m-%d')))) as overdate")){
			foreach($channel_rs as $key => $val){
				if($val["typeid"])
					$channel_rs[$key]["typename"] = $obj_type->getname($val["typeid"]);
				if($val["type2id"])
					$channel_rs[$key]["type2name"] = $obj_type->getname($val["type2id"]);
			}
		}
		$this->channel_rs = $channel_rs;
		$this->pager = $obj_channel->spPager()->getPager();
		$this->type_rs = $obj_type->getlist();
		$this->searchkey = $postdate['searchkey'];
		$this->user_rs = $user_rs;
		$this->url = spUrl('channelverifys', 'verifychannellist', array("searchkey"=>$this->searchkey, "typeid"=>$this->typeid, "from_id"=>$from_id, "main_id"=>$main_id, "sort"=>$this->sort,"inuse"=>$this->inuse));
	}
	
	public function verifychannelinfo(){
		try {
			$url = $_SERVER['HTTP_REFERER'];
			$obj_channel = spClass("channel");
			$obj_sign = spClass("channel_sign");
			$obj_type = spClass("channel_type");
			$obj_user = spClass('user');
			$depart_id = $_SESSION["sscrm_user"]["depart_id"];
			if(!$channel_id = intval($this->spArgs("channel_id")))
				throw new Exception("渠道参数丢失");
			$channel_rs = $obj_channel
			->join("crm_user as create_user", "create_user.id = crm_channel.create_id")
			->join("crm_user as contact_user", "contact_user.id = crm_channel.contact_id")
			->join("crm_user as from_user", "from_user.id = crm_channel.from_id")
			->join("crm_user as maintenance_user", "maintenance_user.id = crm_channel.maintenance_id")
			->find(array("crm_channel.id"=>$channel_id), null, "crm_channel.*");
			if(!$channel_rs)
				throw new Exception("找不到该渠道，可能已被删除");
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try {
					$postdata = $this->spArgs();
					$data = array();
					$data["mechanism"] = $postdata["mechanism"];
					$data["from_id"] = intval($postdata["from_id"]);
					$data["contact_id"] = intval($postdata['contact_id']) ? intval($postdata['contact_id']) : $data['from_id'];
					$data["maintenance_id"] = intval($postdata["maintenance_id"]);
					$data["typeid"] = intval($postdata["typeid"]);
					$data["type2id"] = intval($postdata["type2id"]);
					$data["main_contact"] = $postdata["main_contact"];
					$data["main_tel"] = $postdata["main_tel"];
					$sign_data["channel_id"] = $channel_id;
					$sign_data["startdate"] = $postdata["startdate"];
					$sign_data["enddate"] = $postdata["enddate"];
					$sign_data["signdate"] = $postdata["signdate"];
					$sign_data["createtime"] = time();
					$sign_data["user_id"] = $_SESSION["sscrm_user"]["id"];
					if($result = $obj_channel->spValidatorForOPT()->spValidator($data)){
						foreach($result as $item) {
							throw new Exception($item[0]);
							break;
						}
					}
					if(!$data["type2id"])
						throw new Exception("无二级分类，无法签约");
					if(!$sign_data["signdate"])
						throw new Exception("请选择签约时间");
					if(!$sign_data["startdate"])
						throw new Exception("请选择生效开始时间");
					if(!$sign_data["enddate"])
						throw new Exception("请选择生效结束时间");
					if(strtotime($sign_data["startdate"]) > strtotime($sign_data["enddate"]))
						throw new Exception("生效开始时间不能大于结束时间");
					if($obj_sign->find(array("channel_id"=>$channel_id, "startdate <="=>$sign_data["startdate"], "enddate >="=>$sign_data["startdate"])))
						throw new Exception("生效的开始时间已经重合，无法签约");
					if($obj_sign->find(array("channel_id"=>$channel_id, "startdate <="=>$sign_data["enddate"], "enddate >="=>$sign_data["enddate"])))
						throw new Exception("生效的结束时间已经重合，无法签约");
					if($obj_sign->find(array("channel_id"=>$channel_id, "startdate >"=>$sign_data["startdate"], "enddate <"=>$sign_data["enddate"])))
						throw new Exception("生效的时间已经重合，无法签约");
					if(!$obj_user->find(array("id"=>$data["maintenance_id"], "depart_id"=>2)))
						throw new Exception("该渠道的维护人不正确，无法签约");
					$obj_sign->getDb()->beginTrans();
					if(!$obj_sign->create($sign_data))
						throw new Exception("未知错误，签约操作失败");
					if(!$obj_channel->update(array("id"=>$channel_id), $data))
						throw new Exception("未知错误，渠道更新失败");
					spClass('user_log')->save_log(2, "将渠道 ".$data['mechanism']." [id:$channel_id] 转为了签约状态");
					$obj_channel->updatesign();
					$obj_sign->getDb()->commitTrans();
					$message = array('msg'=>"操作成功", "url"=>spUrl("channelverifys", "verifychannellist"), 'result'=>1);
					echo json_encode($message);
					exit();
				}catch(Exception $e){
					$obj_sign->getDb()->rollbackTrans();
					$message = array('msg'=>$e->getMessage(), 'result'=>0);
					echo json_encode($message);
					exit();
				}
			}
			$this->channel_id = $channel_id;
			$this->type_rs = $obj_type->getlist();
			$this->type2_rs = $obj_type->getlist($channel_rs[typeid]);
			$this->channel_rs = $channel_rs;
			$user = spClass("user");
			$this->tuser_rs = $user->getUser_prep();
			$this->muser_rs = $user->getUser_prep("crm_user.depart_id = 2");
			$this->contact_rs = $user->getUser_prep("find_in_set('telclient', identity_attr)");
		}catch(Exception $e){
			$this->redirect($url, $e->getMessage());
		}
	}
	
	public function signlist(){
		try {
			$url = $_SERVER['HTTP_REFERER'];
			$obj_channel = spClass("channel");
			$obj_sign = spClass("channel_sign");
			$obj_type = spClass("channel_type");
			$obj_user = spClass('user');
			$postdate = $this->spArgs();
			if(!$channel_id = intval($this->spArgs("channel_id")))
				throw new Exception("渠道参数丢失");
			$channel_rs = $obj_channel
			->join("crm_user as create_user", "create_user.id = crm_channel.create_id")
			->join("crm_user as contact_user", "contact_user.id = crm_channel.contact_id")
			->join("crm_user as from_user", "from_user.id = crm_channel.from_id")
			->join("crm_user as maintenance_user", "maintenance_user.id = crm_channel.maintenance_id")
			->find(array("crm_channel.id"=>$channel_id), null, "crm_channel.*");
			if(!$channel_rs)
				throw new Exception("找不到该渠道，可能已被删除");
			$condition = array("crm_channel_sign.channel_id"=>$channel_id, "crm_channel_sign.isdel"=>0);
			$sort = "crm_channel_sign.signdate desc";
			if($postdate['sort'].'a' !== 'a'){
				switch ($postdate['sort']){
					case "createtime_desc":
						$sort = "crm_channel_sign.createtime desc, ".$sort;
						$this->sort = $postdate['sort'];
						break;
					default:
			
					break;
				}
			}
			$this->sign_rs = $obj_sign->join("crm_user as cuser", "cuser.id = crm_channel_sign.user_id")->spPager($page, 20)->findAll($condition, $sort, "crm_channel_sign.*, cuser.realname");
			$this->pager = $obj_sign->spPager()->getPager();
			$this->channel_rs = $channel_rs;
			$this->channel_id = $channel_id;
			$this->backurl = spUrl("channelverifys", "channellist");
			$this->controller = "channelverifys";
			$this->url = spUrl('channelverifys', 'signlist', array("sort"=>$this->sort));
		}catch(Exception $e){
			$this->redirect($url, $e->getMessage());
		}
	}
	
	public function allsignlist(){
		try {
			$url = $_SERVER['HTTP_REFERER'];
			$obj_channel = spClass("channel");
			$obj_sign = spClass("channel_sign");
			$obj_type = spClass("channel_type");
			$obj_user = spClass('user');
			$postdate = $this->spArgs();
			$condition = array("crm_channel_sign.isdel"=>0);
			if($sign_id = intval($postdate["sign_id"])){
				$condition["crm_channel.from_id"] = $sign_id;
				$this->sign_id = $sign_id;
			}
			if($main_id = intval($postdate["main_id"])){
				$condition["crm_channel.maintenance_id"] = $main_id;
				$this->main_id = $main_id;
			}
			$sort = "crm_channel_sign.signdate desc";
			if($postdate['sort'].'a' !== 'a'){
				switch ($postdate['sort']){
					case "createtime_desc":
						$sort = "crm_channel_sign.createtime desc, ".$sort;
						$this->sort = $postdate['sort'];
						break;
					default:
							
					break;
				}
			}
			$this->sign_rs = $obj_sign->join("crm_user")->join("crm_channel")->join("crm_user as c_cuser", "c_cuser.id = crm_channel.from_id")->join("crm_user as c_muser", "c_muser.id = crm_channel.maintenance_id")->spPager($page, 20)->findAll($condition, $sort, "crm_channel_sign.*, crm_user.realname, c_cuser.realname as cc_realname, c_muser.realname as cm_realname, crm_channel.mechanism, crm_channel.main_contact, crm_channel.main_tel");
			$this->pager = $obj_sign->spPager()->getPager();
			$this->url = spUrl('channelverifys', 'allsignlist', array("sign_id"=>$this->sign_id, "main_id"=>$this->main_id, "sort"=>$this->sort));
			$this->backurl = spUrl("channelverifys", "channellist");
			$this->controller = "channelverifys";
			$this->user_sign_prep_rs = $obj_user->getUser_prep();
			$this->user_main_prep_rs = $obj_user->getUser_prep("crm_user.depart_id = 2");
			$this->display("channelverifys/allsignlist.html");
		}catch(Exception $e){
			$this->redirect($url, $e->getMessage());
		}
	}
}
?>