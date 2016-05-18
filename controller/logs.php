<?php
class logs extends spController {
	
	public function loglist(){
		$obj_log = spClass('user_log');
		$obj_type = spClass('user_log_type');
		$postdate = $this->spArgs();
		$page = intval(max($postdate['page'], 1));
		$condition = "crm_user_log.user_id = ".$_SESSION["sscrm_user"]["id"];
		if($postdate['starttime'] != ''){
			$condition .= " and crm_user_log.logtime >= ".strtotime($postdate['starttime']);
			$this->starttime = $postdate['starttime'];
		}
		if($postdate['endtime'] != ''){
			$condition .= " and crm_user_log.logtime <= ".strtotime($postdate['endtime']);
			$this->endtime = $postdate['endtime'];
		}
		if($postdate['type_id'].'a' !== 'a'){
			$condition .= " and crm_user_log.type_id = ".intval($postdate['type_id']);
			$this->type_id = intval($postdate['type_id']);
		}
		if($postdate['sort'] != '' && in_array($postdate['sort'], array("asc", "desc"))){
			$sort = "crm_user_log.logtime ".$postdate['sort'];
			$this->sort = $postdate['sort'];
		}else{
			$sort = "crm_user_log.logtime desc";
			$this->sort = "desc";
		}
		$sort .= ", crm_user_log.id {$this->sort}";
		$this->type_rs = $obj_type->getlist();
		$this->log_rs = $obj_log->join("crm_user_log_type")->spPager($page, 20)->findAll($condition, $sort, "crm_user_log.*, crm_user_log_type.tname");
		$this->pager = $obj_log->spPager()->getPager();
		$this->url = spUrl('logs', 'loglist', array("sort"=>$this->sort, "starttime"=>$this->starttime, "endtime"=>$this->endtime, "type_id"=>$this->type_id));
	}
}
?>