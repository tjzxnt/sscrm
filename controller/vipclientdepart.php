<?php
class vipclientdepart extends spController {
	
	public function __construct(){
		parent::__construct();
		try {
			$this->controller = "vipclientdepart";
			$this->clisturl = spUrl($this->controller, "clientlist");
		}catch(Exception $e){
			$backurl = spUrl("main", "welcome");
			$this->redirect($backurl, $e->getMessage());
			exit();
		}
	}

	public function clientlist(){
		$obj_client = spClass('vipclient');
		$obj_origin = spClass('origin');
		$obj_user = spClass('user');
		$obj_country = spClass('country');
		$obj_record = spClass("vipclient_record");
		$obj_channel = spClass("channel");
		$obj_level = spClass("client_level");
		$obj_od = spClass('client_overtime');
		$obj_comactive = spClass('comactive');
		$obj_od = spClass("vipclient_overtime");
		$postdate = $this->spArgs();
		$page = intval(max($postdate['page'], 1));
		$condition = "crm_vip_client.isdel = 0 and crm_user.depart_id = {$_SESSION["sscrm_user"]["depart_id"]}";
		$origin_id = intval($postdate['origin_id']);
		if($origin_id > 0){
			$condition .= " and crm_vip_client.origin_id = $origin_id";
			$this->origin_id = $origin_id;
		}elseif($origin_id == -1){
			$condition .= " and crm_vip_client.channel_id > 0";
			$this->origin_id = $origin_id;
		}
		if($channel_id = intval($postdate['channel_id'])){
			$condition .= " and crm_vip_client.channel_id = $channel_id";
			$this->channel_id = $channel_id;
		}
		if($postdate['statdate']){
			$condition .= " and FROM_UNIXTIME(crm_vip_client.fullpay_arrivaltime,'%Y-%m') = '{$postdate['statdate']}'";
			$this->statdate = $postdate['statdate'];
		}
		if($postdate['starttime'] != ''){
			$condition .= " and crm_vip_client.visit_time >= ".strtotime($postdate['starttime']);
			$this->starttime = $postdate['starttime'];
		}
		if($postdate['endtime'] != ''){
			$condition .= " and crm_vip_client.visit_time <= ".strtotime($postdate['endtime']);
			$this->endtime = $postdate['endtime'];
		}
		if($postdate["searchkey"]){
			$condition .= " and (crm_vip_client.comname like '%".$postdate['searchkey']."%' or crm_vip_client_manage.realname like '%".$postdate['searchkey']."%' or crm_vip_client_manage.telphone like '%".$postdate['searchkey']."%')";
			$this->searchkey = $postdate['searchkey'];
		}
		if(intval($postdate['channel_muserid'])){
			$condition .= " and crm_channel.maintenance_id = ".intval($postdate['channel_muserid']);
			$this->channel_muserid = $postdate['channel_muserid'];
		}
		if(intval($postdate['user_sales_id'])){
			$condition .= " and crm_vip_client.create_id = ".intval($postdate['user_sales_id']);
			$this->user_sales_id = $postdate['user_sales_id'];
		}
		if($postdate['ispay']."a" != "a"){
			$condition .= " and crm_vip_client.ispay = ".intval($postdate['ispay']);
			$this->ispay = $postdate['ispay'];
		}
		if($postdate['fword']."a" != "a"){
			$condition .= " and fristPinyin(crm_vip_client_manage.realname) = '".$postdate['fword']."'";
			$this->fword = $postdate['fword'];
		}
		if($comactive_id = intval($postdate['comactive_id'])){
			$condition .= " and crm_vip_client.comactive_id = {$comactive_id}";
			$this->comactive_id = $comactive_id;
		}
		$client_sort = "crm_vip_client.createtime desc";
		if($sort = $postdate['sort']){
			switch ($sort){
				case "sp_asc":
					$client_sort = "PY asc, ".$client_sort;
				break;
				case "sp_desc":
					$client_sort = "PY desc, ".$client_sort;
				break;
				default:
					$client_sort = "crm_vip_client.createtime desc";
				break;
			}
			$this->sort = $sort;
		}
		if($client_rs = $obj_client->join("crm_client_level")->join("crm_vip_client_manage", "crm_vip_client_manage.vip_client_id = crm_vip_client.id and crm_vip_client_manage.ismain = 1")->join("crm_user")->join("crm_channel", "crm_channel.id = crm_vip_client.channel_id and crm_vip_client.channel_id > 0", "left")->join("crm_client_process")->join("crm_vip_client_overtime", "crm_vip_client_overtime.client_id = crm_vip_client.id and crm_vip_client_overtime.endtime = 0", "left")->spPager($page, 20)->findAll($condition, $client_sort, "crm_vip_client.*, crm_vip_client_manage.realname, crm_vip_client_manage.sex, crm_vip_client_manage.managepost, crm_vip_client_manage.tel_location, crm_vip_client_manage.telphone, crm_vip_client_manage.email, crm_vip_client_manage.wechat, crm_client_level.name as level_name, fristPinyin(crm_vip_client_manage.realname) as py, crm_user.realname as realname_sale, crm_vip_client_overtime.fromtime, crm_client_process.pname", "crm_vip_client.id")){
		//if($client_rs = $obj_client->join("crm_client_level")->join("crm_user", "crm_user.id = crm_vip_client.user_sales_id")->join("crm_client_overtime", "crm_client_overtime.client_id = crm_vip_client.id and crm_client_overtime.endtime = 0", "left")->join("crm_channel", "crm_channel.id = crm_vip_client.channel_id and crm_vip_client.channel_id > 0", "left")->spPager($page, 20)->findAll($condition, $client_sort, "crm_client.*, crm_client_level.name as level_name, crm_client_overtime.fromtime, crm_user.realname as realname_sale, crm_user.realname as realname_sale, fristPinyin(crm_client.realname) as py")){
			foreach($client_rs as $key => $val){
				if($val["origin_id"]){
					$origin_rs = $obj_origin->findByPk($val["origin_id"]);
					$client_rs[$key]["oname"] = $origin_rs["oname"];
					$client_rs[$key]["record_count"] = $obj_record->getCountById($val["id"]);
				}
				if($val["exp_country_id"]){
					$client_rs[$key]["ctname"] = $obj_country->getname($val["exp_country_id"]);
				}
				$client_rs[$key]["overtime_count"] = $obj_od->getCount($val["id"]);
			}
			$this->client_rs = $client_rs;
		}
		$this->user_tel_rs = $obj_user->getUser_prep("find_in_set('telclient', identity_attr)");
		$this->comactive_rs = $obj_comactive->getlist();
		$this->channel_muser_rs = $obj_user->getUser_prep("crm_user.depart_id = 2");
		$this->channel_rs = $obj_channel->getChannel_prep();
		$this->user_rs = $obj_user->getUser_prep();
		$this->pager = $obj_client->spPager()->getPager();
		$this->origin_rs = $obj_origin->get_origin();
		$this->url = spUrl('vipclientdepart', 'clientlist', array("starttime"=>$this->starttime, "endtime"=>$this->endtime, "level_id"=>$this->level_id, "statdate"=>$this->statdate, "channel_id"=>$this->channel_id, "sort"=>$this->sort, "searchkey"=>$this->searchkey, "ispay"=>$this->ispay, "channel_muserid"=>$this->channel_muserid, "user_sales_id"=>$this->user_sales_id, "comactive_id"=>$this->comactive_id));
	}
	
	public function viewclient(){
		try {
			$url = $_SERVER['HTTP_REFERER'];
			if(!$id = intval($this->spArgs('id')))
				throw new Exception('请先选择查看项，再进行操作！');
			$obj_client = spClass("vipclient");
			$obj_origin = spClass("origin");
			$obj_user = spClass("user");
			$obj_channel = spClass("channel");
			$obj_trader = spClass('trader');
			if(!$client_rs = $obj_client->join("crm_user")->find("crm_vip_client.id= $id and crm_user.depart_id = {$_SESSION["sscrm_user"]["depart_id"]}"))
				throw new Exception("找不到该客户");
			$this->client_rs = $client_rs;
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
			$this->display("vipclients/viewclient.html");
		}catch(Exception $e){
			$this->redirect($url, $e->getMessage());
		}
	}
	
	public function clientrecordlist(){
		try {
			if(!$client_id = intval($this->spArgs("client_id")))
				throw new Exception("参数丢失");
			$obj_client = spClass("vipclient");
			if(!$this->client_rs = $obj_client->join("crm_user")->find("crm_vip_client.id= $client_id and crm_user.depart_id = {$_SESSION["sscrm_user"]["depart_id"]}"))
				throw new Exception("找不到该客户");
			$obj_record = spClass("vipclient_record");
			$postdate = $this->spArgs();
			$page = intval(max($postdate['page'], 1));
			$record_rs = $obj_record->join("crm_user")->join("crm_client")->spPager($page, 20)->findAll(array("crm_vip_client_record.client_id"=>$client_id, "crm_vip_client_record.rtype_id"=>1, "crm_user.depart_id"=>$_SESSION["sscrm_user"]["depart_id"]), "crm_vip_client_record.createtime asc", "crm_vip_client_record.*, crm_user.realname as realname_create");
			$this->record_rs = $record_rs;
			$this->pager = $obj_record->spPager()->getPager();
			$this->client_id = $client_id;
			$this->url = spUrl('vipclientdepart', 'clientrecordlist', array("client_id"=>$client_id));
		}catch(Exception $e){
			$this->redirect(spUrl("vipclientdepart", "clientlist"), $e->getMessage());
		}
	}
	
	public function allrecordlist(){
		try {
			$obj_user = spClass('user');
			$obj_record = spClass("vipclient_record");
			$postdate = $this->spArgs();
			$page = intval(max($postdate['page'], 1));
			$condition = "crm_vip_client_record.rtype_id = 1 and crm_user.depart_id = {$_SESSION["sscrm_user"]["depart_id"]}";
			if($postdate['starttime'] != ''){
				$condition .= " and crm_vip_client_record.acttime >= ".strtotime($postdate['starttime']);
				$this->starttime = $postdate['starttime'];
			}
			if($postdate['endtime'] != ''){
				$condition .= " and crm_vip_client_record.acttime <= ".strtotime($postdate['endtime']);
				$this->endtime = $postdate['endtime'];
			}
			if(intval($postdate['create_id'])){
				$condition .= " and crm_vip_client.create_id = ".intval($postdate['create_id']);
				$this->create_id = $postdate['create_id'];
			}
			if($client_id = intval($this->spArgs("client_id"))){
				$obj_client = spClass("client");
				if($this->client_rs = $obj_client->find(array("id"=>$client_id))){
					$condition .= " and crm_vip_client_record.client_id = $client_id";
					$this->client_id = $client_id;
				}
			}
			$record_rs = $obj_record->join("crm_user")->join("crm_vip_client")->join("crm_vip_client_manage", "crm_vip_client_manage.vip_client_id = crm_vip_client.id and crm_vip_client_manage.ismain = 1")->spPager($page, 20)->findAll($condition, "crm_vip_client_record.acttime desc", "crm_vip_client_record.*, crm_vip_client.comname, crm_vip_client_manage.telphone, crm_user.realname as realname_create");
			$this->record_rs = $record_rs;
			$this->pager = $obj_record->spPager()->getPager();
			$this->url = spUrl('vipclientdepart', 'allrecordlist', array("starttime"=>$this->starttime, "endtime"=>$this->endtime, "create_id"=>$this->create_id, "client_id"=>$this->client_id));
			$this->user_rs = $obj_user->getUser_prep("crm_user.depart_id = 2 or crm_user.depart_id = 3");
			$this->controller = "vipclientdepart";
		}catch(Exception $e){
			$this->redirect(spUrl("vipclientdepart", "mycreateclientlist"), $e->getMessage());
		}
	}
	
	//查看过期
	public function odlist(){
		try {
			if(!$id = intval($this->spArgs('id')))
				throw new Exception('请先选择客户，再进行操作！');
			$backurl = spUrl("clientsales", "clientlist");
			$obj_client = spClass("vipclient");
			$obj_origin = spClass("origin");
			$obj_user = spClass("user");
			if(!$client_rs = $obj_client->join("crm_user")->find("crm_vip_client.id= $id and crm_user.depart_id = {$_SESSION["sscrm_user"]["depart_id"]}"))
				throw new Exception("找不到该客户");
			$postdata = $this->spArgs();
			$obj_od = spClass("vipclient_overtime");
			$page = intval(max($postdata['page'], 1));
			$this->od_rs = $obj_od->join("crm_user")->spPager($page, 20)->findAll(array("client_id"=>$id), "createtime desc", "crm_vip_client_overtime.*, crm_user.realname as realname");
			$this->client_rs = $client_rs;
			$this->id = $id;
			$this->pager = $obj_od->spPager()->getPager();
			$this->url = spUrl("vipclientdepart", "odlist", array("id"=>$id));
			$this->backurl = spUrl("vipclientdepart", "clientlist");
			$this->display("vipclients/odlist.html");
		}catch(Exception $e){
			$this->redirect(spUrl("vipclientdepart", "clientlist"), $e->getMessage());
		}
	}
	
	//全部过期记录
	public function allodlist(){
		try {
			$obj_user = spClass("user");
			$postdata = $this->spArgs();
			$obj_od = spClass("vipclient_overtime");
			$page = intval(max($postdata['page'], 1));
			$condition = "crm_user.depart_id = {$_SESSION["sscrm_user"]["depart_id"]}";
			if($postdata["endtime"]){
				switch($postdata["endtime"]){
					case "ing":
						$condition .= " and crm_vip_client_overtime.endtime = 0";
						break;
					case "end":
						$condition .= " and crm_vip_client_overtime.endtime > 0";
						break;
				}
				$this->endtime = $postdata["endtime"];
			}
			if($postdata["create_id"]){
				$condition .= " and crm_vip_client.create_id = {$postdata["create_id"]}";
				$this->create_id = $postdata["create_id"];
			}
			$this->od_rs = $obj_od->join("crm_user")->join("crm_vip_client")->join("crm_vip_client_manage", "crm_vip_client_manage.vip_client_id = crm_vip_client.id and crm_vip_client_manage.ismain = 1")->spPager($page, 15)->findAll($condition, "crm_vip_client_overtime.createtime desc", "crm_vip_client_overtime.*, crm_vip_client.comname, crm_vip_client_manage.telphone, crm_user.realname as realname");
			$this->pager = $obj_od->spPager()->getPager();
			$this->url = spUrl("vipclientdepart", "allodlist", array("endtime"=>$this->endtime, "create_id"=>$this->create_id));
			$this->controller = "vipclientdepart";
			$this->user_prep_rs = $obj_user->getUser_prep("crm_user.depart_id = 2 or crm_user.depart_id = 3");
			$this->backurl = spUrl("vipclientdepart", "clientlist");
			$this->display("vipclients/odlist.html");
		}catch(Exception $e){
			$this->redirect(spUrl("vipclientdepart", "clientlist"), $e->getMessage());
		}
	}
}
?>