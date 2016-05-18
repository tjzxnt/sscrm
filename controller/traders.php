<?php
class traders extends spController {
	
	public function __construct(){
		parent::__construct();
		try {
			$obj_cpt = spClass("department_competence");
			$obj_cpt->check_login_competence("TRADER");
		}catch(Exception $e){
			$backurl = spUrl("main", "welcome");
			$this->redirect($backurl, $e->getMessage());
			exit();
		}
	}
	
	public function traderlist(){
		$obj_trader = spClass('trader');
		$postdate = $this->spArgs();
		$page = intval(max($postdate['page'], 1));
		$condition = "crm_trader.maintenance_id = ".$_SESSION["sscrm_user"]["id"];
		if($postdate['searchkey'] != '')
			$condition .= " and crm_trader.tradername like '%{$postdate['searchkey']}%'";
		$this->trader_rs = $obj_trader->spPager($page, 20)->findAll($condition, 'crm_trader.createtime desc', "crm_trader.*");
		$this->pager = $obj_trader->spPager()->getPager();
		$this->searchkey = $postdate['searchkey'];
		$this->url = spUrl('traders', 'traderlist', array("searchkey"=>$this->searchkey));
	}
	
	public function userlist(){
		try{
			$obj_trader = spClass('trader');
			$traderid = intval($this->spArgs("traderid"));
			$trader_rs = $obj_trader->get_act_trader($traderid);
			$obj_user = spClass('trader_user');
			$postdate = $this->spArgs();
			$page = intval(max($postdate['page'], 1));
			$condition = "crm_trader_user.trader_id = ".$traderid;
			if($postdate['searchkey'] != '')
				$condition .= " and (crm_trader_user.realname like '%{$postdate['searchkey']}%')";
			$this->user_rs = $obj_user->spPager($page, 20)->findAll($condition, 'crm_trader_user.createtime desc', "crm_trader_user.*");
			$this->pager = $obj_user->spPager()->getPager();
			$this->searchkey = $postdate['searchkey'];
			$this->trader_rs = $trader_rs;
			$this->traderid = $traderid;
			$this->url = spUrl('traders', 'userlist', array("traderid"=>$traderid, "searchkey"=>$this->searchkey));
		}catch(Exception $e){
			$this->redirect(spUrl("traders", "traderlist"), $e->getMessage());
		}
	}
	
	public function createuser(){
		try {
			$obj_trader = spClass('trader');
			$traderid = intval($this->spArgs("traderid"));
			$trader_rs = $obj_trader->get_act_trader($traderid);
			$obj_user = spClass('trader_user');
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try {
					$postdate = $this->spArgs();
					$data = array();
					//客户端提交方法
					$data['create_id'] = $_SESSION["sscrm_user"]["id"];
					$data['trader_id'] = $traderid;
					$data['realname'] = $postdate['realname'];
					$data['telphone'] = $postdate['telphone'];
					$data['createtime'] = time();
					if($result = $obj_user->spValidator($data)){
						foreach($result as $item) {
							throw new Exception($item[0]);
							break;
						}
					}
					if(!$id = $obj_user->create($data))
						throw new Exception("未知错误，添加失败");
					spClass('user_log')->save_log(7, "添加了分销商 ".$trader_rs["tradername"]." 的联系人 ".$data['realname']." [id:$id]");
					$url = spUrl("traders", "userlist", array("traderid"=>$traderid));
					$message = array('msg'=>'分销商联系人添加成功','result'=>1, 'url'=>$url);
					echo json_encode($message);
					exit();
				}catch(Exception $e){
					$message = array('msg'=>$e->getMessage() ,'result'=>0);
					echo json_encode($message);
					exit();
				}
			}
			$this->validator = $obj_user->getValidatorJS();
			$this->traderid = $traderid;
			$this->trader_rs = $trader_rs;
			$this->saveurl = spUrl("traders", "createuser");
		}catch(Exception $e){
			$this->redirect(spUrl("traders", "traderlist"), $e->getMessage());
		}
	}

	public function traderrecordlist(){
		try {
			$obj_trader = spClass('trader');
			$traderid = intval($this->spArgs("traderid"));
			$trader_rs = $obj_trader->get_act_trader($traderid);
			$obj_record = spClass("trader_record");
			$postdate = $this->spArgs();
			$page = intval(max($postdate['page'], 1));
			$record_rs = $obj_record->join("crm_user")->join("crm_trader")->spPager($page, 20)->findAll(array("crm_trader_record.trader_id"=>$traderid), "crm_trader_record.createtime asc", "crm_trader_record.*, crm_user.realname as realname_create");
			$this->record_rs = $record_rs;
			$this->trader_rs = $trader_rs;
			$this->pager = $obj_record->spPager()->getPager();
			$this->traderid = $traderid;
			$this->url = spUrl('traders', 'traderrecordlist', array("traderid"=>$traderid));
		}catch(Exception $e){
			$this->redirect(spUrl("traders", "traderlist"), $e->getMessage());
		}
	}
	
	public function createrecord(){
		try {
			$obj_trader = spClass('trader');
			$traderid = intval($this->spArgs("traderid"));
			$trader_rs = $obj_trader->get_act_trader($traderid);
			$obj_record = spClass("trader_record");
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try {
					$postdate = $this->spArgs();
					$data = array();
					$data["trader_id"] = $traderid;
					$data["create_id"] = $_SESSION["sscrm_user"]["id"];
					$data["content"] = $postdate["content"];
					$data["acttime"] = strtotime($postdate["acttime"]);
					$data["createtime"] = time();
					if($result = $obj_record->spValidator($data)){
						foreach($result as $item) {
							throw new Exception($item[0]);
							break;
						}
					}
					if(!$obj_record->create($data))
						throw new Exception("未知错误，沟通记录添加失败");
					$obj_trader->update(array("id"=>$traderid), array("recordtime"=>time()));
					spClass('user_log')->save_log(7, "添加了分销商 ".$trader_rs['tradername']." [id:$traderid] 的沟通记录", array("trader_id"=>$traderid));
					$message = array('msg'=>"沟通记录添加成功", 'result'=>1, "url"=>spUrl("traders", "traderrecordlist", array("traderid"=>$traderid)));
					echo json_encode($message);
					exit();
				}catch(Exception $e){
					$message = array('msg'=>$e->getMessage(), 'result'=>0);
					echo json_encode($message);
					exit();
				}
			}
			$this->trader_rs = $trader_rs;
			$this->validator = $obj_record->getValidatorJS();
			$this->traderid = $traderid;
			$this->saveurl = spUrl("traders", "createrecord");
		}catch(Exception $e){
			$this->redirect(spUrl("traders", "traderlist"), $e->getMessage());
		}
	}
	
	public function ajaxActsByChannel(){
		try {
			$obj_channel = spClass("channel");
			$obj_active = spClass('channel_active');
			$channelid = intval($this->spArgs("channelid"));
			if(!$channelid)
				throw new Exception("参数错误");
			if(!$act_rs = $obj_active->get_actives_by_channelid($channelid))
				throw new Exception("该渠道暂无任何活动");
			echo json_encode(array('data'=>$act_rs, 'result'=>1));
			exit();
		}catch(Exception $e){
			echo json_encode(array('msg'=>$e->getMessage(), 'result'=>0));
			exit();
		}
	}
}
?>