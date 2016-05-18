<?php
class clientintention extends spController {
	
	public function __construct(){
		parent::__construct();
		try {
			if(!$type = $this->spArgs("type"))
				throw new Exception("type参数丢失");
			$obj_type = spClass("client_intention_type");
			if(!$this->type_rs = $obj_type->find(array("id"=>$type)))
				throw new Exception("type参数错误");
			$action = $this->spArgs("a");
			if($identity = $this->type_rs["identity"]){
				if(in_array($action, array("allclientlist", "clientrecordlist", "allrecordlist", "saleallclientrecordlist", "intentionview"))){
					if(!$_SESSION["sscrm_user"]["user_identity"][$identity."_viewall"]["enabled"])
						throw new Exception("您无权查看该页面".$identity."_viewall");
				}else{
					if(!$_SESSION["sscrm_user"]["user_identity"][$identity]["enabled"])
						throw new Exception("您无权查看该页面");
				}
			}
		}catch(Exception $e){
			$backurl = spUrl("main", "welcome");
			$this->redirect($backurl, $e->getMessage());
			exit();
		}
	}
	
	public function clientlist(){
		$obj_int = spClass("client_intention");
		$obj_country = spClass('country');
		$obj_record = spClass("client_intention_record");
		$obj_recordsale = spClass("client_record");
		$postdata = $this->spArgs();
		$page = intval(max($postdata['page'], 1));
		$condition = "crm_client_intention.create_id = {$_SESSION["sscrm_user"]["id"]} and crm_client_intention.isdel = 0";
		if($postdata['searchkey']){
			$condition .= " and (crm_client_intention.realname like '%{$postdata['searchkey']}%' or crm_client_intention.telphone like '%{$postdata['searchkey']}%')";
			$this->searchkey = $postdata['searchkey'];
		}
		if($postdata["isclient"]){
			switch($postdata["isclient"]){
				case "yes":
					$condition .= " and crm_client_intention.client_id > 0";
				break;
				case "no":
					$condition .= " and crm_client_intention.client_id = 0";
				break;
				default:
					
				break;
			}
			$this->isclient = $postdata["isclient"];
		}
		if($int_rs = $obj_int->spPager($page, 15)->findAll($condition, "createtime desc")){
			foreach($int_rs as $key => $val){
				if($val["exp_country_id"])
					$int_rs[$key]["ctname"] = $obj_country->getname($val["exp_country_id"]);
				$int_rs[$key]["record_count"] = $obj_record->getCountById($val["id"]);
				if($val["client_id"])
					$int_rs[$key]["record_sale_count"] = $obj_recordsale->getCountById($val["client_id"]);
			}
		}
		$this->int_rs = $int_rs;
		$this->pager = $obj_int->spPager()->getPager();
		$this->url = spUrl('clientintention', 'clientlist', array("type"=>$this->type_rs["id"], "searchkey"=>$this->searchkey, "isclient"=>$this->isclient));
	}
	
	public function allclientlist(){
		$obj_int = spClass("client_intention");
		$obj_country = spClass('country');
		$obj_record = spClass("client_intention_record");
		$obj_recordsale = spClass("client_record");
		$obj_user = spClass('user');
		$postdata = $this->spArgs();
		$page = intval(max($postdata['page'], 1));
		$condition = "crm_client_intention.isdel = 0";
		if($postdata['searchkey']){
			$condition .= " and (crm_client_intention.realname like '%{$postdata['searchkey']}%' or crm_client_intention.telphone like '%{$postdata['searchkey']}%')";
			$this->searchkey = $postdata['searchkey'];
		}
		if($postdata["isclient"]){
			switch($postdata["isclient"]){
				case "yes":
					$condition .= " and crm_client_intention.client_id > 0";
				break;
				case "no":
					$condition .= " and crm_client_intention.client_id = 0";
				break;
				default:
						
				break;
			}
			$this->isclient = $postdata["isclient"];
		}
		if($user_teler_id = intval($postdata['user_teler_id'])){
			$condition .= " and crm_client_intention.create_id = $user_teler_id";
			$this->user_teler_id = $user_teler_id;
		}
		if($int_rs = $obj_int->join("crm_user")->spPager($page, 15)->findAll($condition, "crm_client_intention.createtime desc", "crm_client_intention.*, crm_user.realname as realname_create")){
			foreach($int_rs as $key => $val){
				if($val["exp_country_id"])
					$int_rs[$key]["ctname"] = $obj_country->getname($val["exp_country_id"]);
				$int_rs[$key]["record_count"] = $obj_record->getCountById($val["id"]);
				if($val["client_id"])
					$int_rs[$key]["record_sale_count"] = $obj_recordsale->getCountById($val["client_id"]);
			}
		}
		$this->user_tel_rs = $obj_user->getUser_prep("find_in_set('telclient', identity_attr)");
		$this->int_rs = $int_rs;
		$this->pager = $obj_int->spPager()->getPager();
		$this->url = spUrl('clientintention', 'allclientlist', array("type"=>$this->type_rs["id"], "user_teler_id"=>$this->user_teler_id, "searchkey"=>$this->searchkey, "isclient"=>$this->isclient));
	}
	
	public function create(){
		$obj_int = spClass("client_intention");
		$obj_client = spClass("client");
		$obj_country = spClass('country');
		$postdata = $this->spArgs();
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			try {
				$data = array();
				$isAjax = $postdata['isAjax'];
				$data['typeid'] = $this->type_rs["id"];
				$data['create_id'] = $_SESSION["sscrm_user"]["id"];
				$data['realname'] = $postdata['realname'];
				$data['sex'] = intval($postdata['sex']);
				$data['tel_location'] = $postdata["tel_location"];
				$data['telphone'] = $postdata['telphone'];
				$data['address'] = $postdata['address'];
				$data['profession'] = $postdata['profession'];
				$data['cred_id'] = intval($postdata['cred_id']);
				$data['cred_license'] = $postdata['cred_license'];
				$data['email'] = $postdata['email'];
				$data['wechat'] = $postdata['wechat'];
				$data['exp_country_id'] = intval($postdata['exp_country_id']);
				$data['demand'] = $postdata['demand'];
				$data["feedback"] = $postdata["feedback"];
				$data['createtime'] = time();
				$record_data['content'] = $postdata["content"];
				if($result = $obj_int->spValidator($data)) {
					foreach($result as $item) {
						throw new Exception($item[0]);
					}
				}
				if($obj_int->find(array("telphone"=>$data['telphone'])))
					throw new Exception("该电话号码已存在，无法再次录入系统");
				if($obj_client->find(array("telphone"=>$data['telphone'])))
					throw new Exception("该电话号码已录入跟进客户中，无法再次录入系统");
				if($record_data['content']){
					$obj_record = spClass("client_intention_record");
					$record_data["intention_id"] = 0;
					$record_data["create_id"] = $data['create_id'];
					$record_data["acttime"] = $record_data["createtime"] = time();
					if($result = $obj_record->spValidator($record_data)){
						foreach($result as $item) {
							throw new Exception($item[0]);
							break;
						}
					}
				}
				$obj_int->getDb()->beginTrans();
				if(!$id = $obj_int->create($data))
					throw new Exception("未知错误，添加失败！");
				spClass('user_log')->save_log(11, "添加了意向客户 ".$data['realname']." [id:$id]", array("intention_id"=>$id));
				if($record_data['content']){
					$record_data["intention_id"] = $id;
					if(!$obj_record->create($record_data))
						throw new Exception("未知错误，沟通记录添加失败");
					spClass('user_log')->save_log(11, "添加了意向客户 ".$data['realname']." [id:$id] 的沟通记录", array("intention_id"=>$id));
				}
				$obj_int->getDb()->commitTrans();
				$url = spUrl("clientintention", "clientlist", array('type'=>$this->type_rs["id"]));
				$message = array('msg'=>'添加成功！','result'=>1, 'url'=>$url);
				echo json_encode($message);
				exit();
			}catch(Exception $e){
				$obj_int->getDb()->rollbackTrans();
				$message = array('msg' => $e->getMessage(), 'result'=>0);
				echo json_encode($message);
				exit();
			}
		}
		$this->cred_rs = spClass("credential")->get_credential();
		$this->country_rs = $obj_country->getlist();
		$this->saveurl = spUrl("clientintention", "create");
	}
	
	public function modify(){
		try {
			$obj_int = spClass("client_intention");
			$obj_client = spClass("client");
			$obj_country = spClass('country');
			$postdata = $this->spArgs();
			if(!$id = intval($postdata["id"]))
				throw new Exception("意向客户参数丢失");
			if(!$int_rs = $obj_int->find(array("id"=>$id, "create_id"=>$_SESSION["sscrm_user"]["id"])))
				throw new Exception("找不到该意向客户，可能已被删除");
			if($int_rs["client_id"])
				throw new Exception("该意向客户已经被添加到系统客户中，无法修改");
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try {
					$data = array();
					$isAjax = $postdata['isAjax'];
					$data['realname'] = $postdata['realname'];
					$data['tel_location'] = $postdata["tel_location"];
					$data['telphone'] = $int_rs["telphone"];
					$data['address'] = $postdata['address'];
					$data['profession'] = $postdata['profession'];
					$data['cred_id'] = intval($postdata['cred_id']);
					$data['cred_license'] = $postdata['cred_license'];
					$data['email'] = $postdata['email'];
					$data['wechat'] = $postdata['wechat'];
					$data['exp_country_id'] = intval($postdata['exp_country_id']);
					$data['demand'] = $postdata['demand'];
					if($result = $obj_int->spValidator($data)) {
						foreach($result as $item) {
							throw new Exception($item[0]);
						}
					}
					$obj_int->getDb()->beginTrans();
					if(!$obj_int->update(array("id"=>$id), $data))
						throw new Exception("未知错误，添加失败！");
					spClass('user_log')->save_log(11, "更新了意向客户 ".$data['realname']." [id:$id]的资料", array("intention_id"=>$id));
					$obj_int->getDb()->commitTrans();
					$url = spUrl("clientintention", "clientlist", array('type'=>$this->type_rs["id"]));
					$message = array('msg'=>'更新成功！','result'=>1, 'url'=>$url);
					echo json_encode($message);
					exit();
				}catch(Exception $e){
					$obj_int->getDb()->rollbackTrans();
					$message = array('msg' => $e->getMessage(), 'result'=>0);
					echo json_encode($message);
					exit();
				}
			}
			$this->id = $id;
			$this->int_rs = $int_rs;
			$this->country_rs = $obj_country->getlist();
			$this->cred_rs = spClass("credential")->get_credential();
			$this->saveurl = spUrl("clientintention", "modify");
			$this->display("clientintention/create.html");
			exit();
		}catch(Exception $e){
			$this->redirect(spUrl("clientintention", "clientlist", array('type'=>$this->type_rs["id"])), $e->getMessage());
			exit();
		}
	}
	
	public function view(){
		try {
			$obj_int = spClass("client_intention");
			$obj_client = spClass("client");
			$obj_country = spClass('country');
			$postdata = $this->spArgs();
			if(!$id = intval($postdata["id"]))
				throw new Exception("意向客户参数丢失");
			if(!$int_rs = $obj_int->join("crm_credential")->find(array("crm_client_intention.id"=>$id, "crm_client_intention.create_id"=>$_SESSION["sscrm_user"]["id"]), null, "crm_client_intention.*, crm_credential.cname"))
				throw new Exception("找不到该意向客户，可能已被删除");
			if($int_rs["exp_country_id"])
				$this->country_rs = $obj_country->find(array("id"=>$int_rs["exp_country_id"]));
			$this->int_rs = $int_rs;
			$this->display("clientintention/intentionview.html");
			exit();
		}catch(Exception $e){
			$this->redirect(spUrl("clientintention", "clientlist", array('type'=>$this->type_rs["id"])), $e->getMessage());
			exit();
		}
	}
	
	public function intentionview(){
		try {
			$obj_int = spClass("client_intention");
			$obj_client = spClass("client");
			$obj_country = spClass('country');
			$postdata = $this->spArgs();
			if(!$id = intval($postdata["id"]))
				throw new Exception("意向客户参数丢失");
			if(!$int_rs = $obj_int->join("crm_credential")->find(array("crm_client_intention.id"=>$id), null, "crm_client_intention.*, crm_credential.cname"))
				throw new Exception("找不到该意向客户，可能已被删除");
			if($int_rs["exp_country_id"])
				$this->country_rs = $obj_country->find(array("id"=>$int_rs["exp_country_id"]));
			$this->int_rs = $int_rs;
		}catch(Exception $e){
			$this->redirect(spUrl("clientintention", "clientlist", array('type'=>$this->type_rs["id"])), $e->getMessage());
			exit();
		}
	}
	
	public function recordlist(){
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_int = spClass("client_intention");
			if(!$this->int_rs = $obj_int->find(array("id"=>$client_id, "create_id"=>$_SESSION["sscrm_user"]["id"])))
				throw new Exception("找不到该意向客户");
			$obj_record = spClass("client_intention_record");
			$postdata = $this->spArgs();
			$page = intval(max($postdata['page'], 1));
			$record_rs = $obj_record->join("crm_user")->join("crm_client_intention")->spPager($page, 20)->findAll(array("crm_client_intention_record.intention_id"=>$client_id), "crm_client_intention_record.createtime desc", "crm_client_intention_record.*, crm_user.realname as realname_create");
			$this->record_rs = $record_rs;
			$this->pager = $obj_record->spPager()->getPager();
			$this->client_id = $client_id;
			$this->url = spUrl('clientintention', 'recordlist', array("type"=>$this->type_rs["id"], "client_id"=>$client_id));
		}catch(Exception $e){
			$this->redirect(spUrl("clientintention", "clientlist", array("type"=>$this->type_rs["id"])), $e->getMessage());
		}
	}
	
	public function clientrecordlist(){
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_int = spClass("client_intention");
			if(!$this->int_rs = $obj_int->find(array("id"=>$client_id)))
				throw new Exception("找不到该意向客户");
			$obj_record = spClass("client_intention_record");
			$postdata = $this->spArgs();
			$page = intval(max($postdata['page'], 1));
			$record_rs = $obj_record->join("crm_user")->join("crm_client_intention")->spPager($page, 15)->findAll(array("crm_client_intention_record.intention_id"=>$client_id), "crm_client_intention_record.createtime desc", "crm_client_intention_record.*, crm_user.realname as realname_create");
			$this->record_rs = $record_rs;
			$this->pager = $obj_record->spPager()->getPager();
			$this->client_id = $client_id;
			$this->url = spUrl('clientintention', 'clientrecordlist', array("type"=>$this->type_rs["id"], "client_id"=>$client_id));
		}catch(Exception $e){
			$this->redirect(spUrl("clientintention", "clientrecordlist", array("type"=>$this->type_rs["id"])), $e->getMessage());
		}
	}
	
	public function createrecord(){
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_int = spClass("client_intention");
			if(!$this->int_rs = $obj_int->find(array("id"=>$client_id, "create_id"=>$_SESSION["sscrm_user"]["id"])))
				throw new Exception("找不到该意向客户");
			$obj_record = spClass("client_intention_record");
			$postdate = $this->spArgs();
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try {
					$data = array();
					$data["intention_id"] = $client_id;
					$data["create_id"] = $_SESSION["sscrm_user"]["id"];
					$data["content"] = $postdate["content"];
					$data["acttime"] = $data["createtime"] = time();
					if($result = $obj_record->spValidator($data)){
						foreach($result as $item) {
							throw new Exception($item[0]);
							break;
						}
					}
					if(!$obj_record->create($data))
						throw new Exception("未知错误，沟通记录添加失败");
					spClass('user_log')->save_log(11, "添加了意向客户 ".$this->int_rs['realname']." [id:$client_id] 的沟通记录", array("intention_id"=>$client_id));
					$backurl = spUrl("clientintention", "recordlist", array("type"=>$this->type_rs["id"], "client_id"=>$client_id));
					$message = array('msg'=>"沟通记录添加成功", 'result'=>1, "url"=>$backurl);
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
			$this->saveurl = spUrl("clientintention", "createrecord");
		}catch(Exception $e){
			$this->redirect(spUrl("clientintention", "clientlist", array("type"=>$this->type_rs["id"])), $e->getMessage());
		}
	}
	
	public function modifyrecord(){
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			if(!$id = intval($this->spArgs("id")))
				throw new Exception("参数丢失");
			$obj_int = spClass("client_intention");
			if(!$this->int_rs = $obj_int->find(array("id"=>$client_id, "create_id"=>$_SESSION["sscrm_user"]["id"])))
				throw new Exception("找不到该意向客户");
			$obj_record = spClass("client_intention_record");
			if(!$record_rs = $obj_record->find(array("intention_id"=>$client_id, "id"=>$id)))
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
					spClass('user_log')->save_log(11, "修改了意向客户 ".$this->int_rs['realname']." [id:$client_id] 的沟通记录[id:$id]", array("intention_id"=>$client_id));
					$message = array('msg'=>"沟通记录修改成功", 'result'=>1, "url"=>spUrl("clientintention", "recordlist", array("type"=>$this->type_rs["id"], "client_id"=>$client_id)));
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
			$this->saveurl = spUrl("clientintention", "modifyrecord");
			$this->id = $id;
			$this->record_rs = $record_rs;
			$this->display("clientintention/createrecord.html");
		}catch(Exception $e){
			$this->redirect(spUrl("clientintention", "clientlist", array("type"=>$this->type_rs["id"])), $e->getMessage());
		}
	}
	
	public function allrecordlist(){
		try {
			$obj_record = spClass("client_intention_record");
			$obj_user = spClass('user');
			$postdata = $this->spArgs();
			$page = intval(max($postdata['page'], 1));
			$condition = "1";
			if($postdata['starttime'] != ''){
				$condition .= " and crm_client_intention_record.acttime >= ".strtotime($postdata['starttime']);
				$this->starttime = $postdata['starttime'];
			}
			if($postdata['endtime'] != ''){
				$condition .= " and crm_client_intention_record.acttime <= ".strtotime($postdata['endtime']);
				$this->endtime = $postdata['endtime'];
			}
			if($user_teler_id = intval($postdata['user_teler_id'])){
				$condition .= " and crm_client_intention.create_id = $user_teler_id";
				$this->user_teler_id = $user_teler_id;
			}
			$record_rs = $obj_record->join("crm_user")->join("crm_client_intention")->spPager($page, 20)->findAll($condition, "crm_client_intention_record.acttime desc", "crm_client_intention_record.*, crm_client_intention.realname, crm_client_intention.telphone, crm_user.realname as realname_create, crm_user.sex");
			$this->record_rs = $record_rs;
			$this->pager = $obj_record->spPager()->getPager();
			$this->url = spUrl('clientintention', 'allrecordlist', array("type"=>$this->type_rs["id"], "starttime"=>$this->starttime, "endtime"=>$this->endtime, "user_teler_id"=>$this->user_teler_id));
			$this->user_teler_rs = $obj_user->getUser_prep("find_in_set('telclient', crm_user.identity_attr)");
			$this->controller = "clientintention";
		}catch(Exception $e){
			$this->redirect(spUrl("clientintention", "clientlist", array("type"=>$this->type_rs["id"])), $e->getMessage());
		}
	}
	
	public function saleclientrecordlist(){
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_client = spClass("client");
			$obj_int = spClass("client_intention");
			if(!$this->int_rs = $obj_int->find(array("client_id"=>$client_id, "create_id"=>$_SESSION["sscrm_user"]["id"])))
				throw new Exception("找不到该意向客户");
			if(!$this->client_rs = $obj_client->getClientById($client_id))
				throw new Exception("找不到该客户");
			$obj_record = spClass("client_record");
			$postdate = $this->spArgs();
			$page = intval(max($postdate['page'], 1));
			$record_rs = $obj_record->join("crm_user")->join("crm_client")->spPager($page, 20)->findAll(array("crm_client_record.client_id"=>$client_id, "crm_client_record.rtype_id"=>1), "crm_client_record.createtime asc", "crm_client_record.*, crm_user.realname as realname_create");
			$this->record_rs = $record_rs;
			$this->pager = $obj_record->spPager()->getPager();
			$this->client_id = $client_id;
			$this->url = spUrl('clientintention', 'saleclientrecordlist', array("type"=>$this->type_rs["id"], "client_id"=>$client_id));
			$this->display("clients/clientrecordlist.html");
		}catch(Exception $e){
			$this->redirect(spUrl("clientintention", "clientlist", array("type"=>$this->type_rs["id"])), $e->getMessage());
		}
	}
	
	public function saleallclientrecordlist(){
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_client = spClass("client");
			$obj_int = spClass("client_intention");
			if(!$this->int_rs = $obj_int->find(array("client_id"=>$client_id)))
				throw new Exception("找不到该意向客户");
			if(!$this->client_rs = $obj_client->getClientById($client_id))
				throw new Exception("找不到该客户");
			$obj_record = spClass("client_record");
			$postdate = $this->spArgs();
			$page = intval(max($postdate['page'], 1));
			$record_rs = $obj_record->join("crm_user")->join("crm_client")->spPager($page, 20)->findAll(array("crm_client_record.client_id"=>$client_id, "crm_client_record.rtype_id"=>1), "crm_client_record.createtime asc", "crm_client_record.*, crm_user.realname as realname_create");
			$this->record_rs = $record_rs;
			$this->pager = $obj_record->spPager()->getPager();
			$this->client_id = $client_id;
			$this->url = spUrl('clientintention', 'saleallclientrecordlist', array("type"=>$this->type_rs["id"], "client_id"=>$client_id));
			$this->display("clients/clientrecordlist.html");
		}catch(Exception $e){
			$this->redirect(spUrl("clientintention", "clientlist", array("type"=>$this->type_rs["id"])), $e->getMessage());
		}
	}
}
?>