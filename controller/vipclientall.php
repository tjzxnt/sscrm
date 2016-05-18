<?php
class vipclientall extends spController {
	
	public function __construct(){
		parent::__construct();
		try {
			$obj_cpt = spClass("department_competence");
			if(!$_SESSION["sscrm_user"]["user_identity"]["operate"]["enabled"])
				$obj_cpt->check_login_competence("CLIENTALL");
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
		$postdate = $this->spArgs();
		$page = intval(max($postdate['page'], 1));
		$condition = "crm_vip_client.isdel = 0";
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
			$condition .= " and (crm_vip_client.realname like '%".$postdate['searchkey']."%' or crm_vip_client.telphone like '%".$postdate['searchkey']."%')";
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
			$condition .= " and fristPinyin(crm_vip_client.realname) = '".$postdate['fword']."'";
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
		if($client_rs = $obj_client->join("crm_user")->join("crm_channel", "crm_channel.id = crm_vip_client.channel_id and crm_vip_client.channel_id > 0", "left")->spPager($page, 20)->findAll($condition, $client_sort, "crm_vip_client.*, crm_user.realname as realname_sale, crm_user.realname as realname_sale, fristPinyin(crm_vip_client.realname) as py")){
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
		$this->url = spUrl('vipclientall', 'clientlist', array("starttime"=>$this->starttime, "endtime"=>$this->endtime, "level_id"=>$this->level_id, "statdate"=>$this->statdate, "channel_id"=>$this->channel_id, "sort"=>$this->sort, "searchkey"=>$this->searchkey, "ispay"=>$this->ispay, "channel_muserid"=>$this->channel_muserid, "user_sales_id"=>$this->user_sales_id, "comactive_id"=>$this->comactive_id));
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
			if(!$client_rs = $obj_client->getClientById($id))
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
			if(!$this->client_rs = $obj_client->getClientById($client_id))
				throw new Exception("找不到该客户");
			$obj_record = spClass("vipclient_record");
			$postdate = $this->spArgs();
			$page = intval(max($postdate['page'], 1));
			$record_rs = $obj_record->join("crm_user")->join("crm_client")->spPager($page, 20)->findAll(array("crm_vip_client_record.client_id"=>$client_id, "crm_vip_client_record.rtype_id"=>1), "crm_vip_client_record.createtime asc", "crm_vip_client_record.*, crm_user.realname as realname_create");
			$this->record_rs = $record_rs;
			$this->pager = $obj_record->spPager()->getPager();
			$this->client_id = $client_id;
			$this->url = spUrl('vipclients', 'clientrecordlist', array("client_id"=>$client_id));
		}catch(Exception $e){
			$this->redirect(spUrl("vipclients", "clientlist"), $e->getMessage());
		}
	}
	
	public function allrecordlist(){
		try {
			$obj_user = spClass('user');
			$obj_record = spClass("vipclient_record");
			$postdate = $this->spArgs();
			$page = intval(max($postdate['page'], 1));
			$condition = "crm_client_record.rtype_id = 1";
			if($postdate['starttime'] != ''){
				$condition .= " and crm_client_record.acttime >= ".strtotime($postdate['starttime']);
				$this->starttime = $postdate['starttime'];
			}
			if($postdate['endtime'] != ''){
				$condition .= " and crm_client_record.acttime <= ".strtotime($postdate['endtime']);
				$this->endtime = $postdate['endtime'];
			}
			if(intval($postdate['user_sales_id'])){
				$condition .= " and crm_vip_client.user_sales_id = ".intval($postdate['user_sales_id']);
				$this->user_sales_id = $postdate['user_sales_id'];
			}
			$record_rs = $obj_record->join("crm_user")->join("crm_client")->spPager($page, 20)->findAll($condition, "crm_client_record.acttime desc", "crm_client_record.*, crm_vip_client.realname, crm_vip_client.telphone, crm_user.realname as realname_create");
			$this->record_rs = $record_rs;
			$this->pager = $obj_record->spPager()->getPager();
			$this->url = spUrl('clientall', 'allrecordlist', array("starttime"=>$this->starttime, "endtime"=>$this->endtime, "user_sales_id"=>$this->user_sales_id));
			$this->user_rs = $obj_user->getUser_prep("crm_user.depart_id = 3 and find_in_set('getclient', crm_user.identity_attr)");
			$this->controller = "clientall";
			$this->display("clientdepart/allrecordlist.html");
		}catch(Exception $e){
			$this->redirect(spUrl("clientall", "mycreateclientlist"), $e->getMessage());
		}
	}
}
?>