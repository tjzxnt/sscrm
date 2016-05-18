<?php
class clientrecordover extends spController {
	
	public function alertnotice(){
		$obj_ot = spClass("client_overtime");
		$ot_rs = $obj_ot->join("crm_client")->find(array("crm_client.user_sales_id"=>$_SESSION["sscrm_user"]["id"], "endtime"=>0), null, "count(crm_client_overtim.id) as total");
		$notice_count = intval($ot_rs["total"]);
		if($notice_count)
			$message = array('total'=>$notice_count, 'result'=>1);
		else
			$message = array('total'=>0, 'result'=>0);
	}
	
	public function noticelist(){
		$obj_notice = spClass('user_notice');
		$postdate = $this->spArgs();
		$page = intval(max($postdate['page'], 1));
		$condition = "crm_user_notice.toid = ".$_SESSION["sscrm_user"]["id"];
		if($postdate['isread'].'a' !== 'a'){
			$condition .= " and crm_user_notice.isread = ".intval($postdate['isread']);
			$this->isread = intval($postdate['isread']);
		}
		$this->notice_rs = $obj_notice->join("crm_user")->spPager($page, 20)->findAll($condition, 'crm_user_notice.createtime desc', "crm_user_notice.*, crm_user.realname");
		$this->pager = $obj_notice->spPager()->getPager();
		$this->url = spUrl('notices', 'noticelist', array("isread"=>$this->isread));
	}
	
	public function modifyread(){
		$obj_notice = spClass('user_notice');
		$id = $this->spArgs("id");
		$isread = intval($this->spArgs("isread")) == 1 ? "1" : "0";
		$isAjax = intval($this->spArgs("isAjax"));
		try {
			if(is_array($id)){
				if(count($id) < 1)
					throw new Exception("请先选择更新项再进行操作");
				foreach($id as $key => $val){
					if(!$obj_notice->update(array("toid"=>$_SESSION["sscrm_user"]["id"], "id"=>$val), array("isread"=>$isread)))
						unset($id[$key]);
				}
				if(count($id) < 1)
					throw new Exception("通知状态更新失败");
				sort($id);
				spClass('user_log')->save_log(5, "将通知 [id:" . implode(",", $id) . "] 的状态变更为 " . ($isread == 1 ? "已读" : "未读"));
				if($isAjax){
					$message = array('result'=>1);
					echo json_encode($message);
					exit();
				}else{
					$this->redirect($_SERVER['HTTP_REFERER'], "通知状态更新成功");
					exit();
				}
			}else{
				if(!$id = intval($id))
					throw new Exception("请先选择更新项再进行操作");
				if(!$obj_notice->update(array("toid"=>$_SESSION["sscrm_user"]["id"], "id"=>$id), array("isread"=>$isread)))
					throw new Exception("通知状态更新失败");
				spClass('user_log')->save_log(5, "将通知 [id:$id] 的状态变更为 " . ($isread == 1 ? "已读" : "未读"));
				if($isAjax){
					$message = array('result'=>1);
					echo json_encode($message);
					exit();
				}else{
					$this->redirect($_SERVER['HTTP_REFERER'], "通知状态更新成功");
					exit();
				}
			}
		}catch(Exception $e){
			if($isAjax){
				$message = array('msg'=>$e->getMessage(), 'result'=>0);
				echo json_encode($message);
				exit();
			}else{
				$this->redirect($_SERVER['HTTP_REFERER'], $e->getMessage());
				exit();
			}
		}
		
	}
}
?>