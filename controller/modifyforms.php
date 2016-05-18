<?php
class modifyforms extends spController {
	
	private function competence_create(){
		try {
			$obj_cpt = spClass("department_competence");
			$obj_cpt->check_login_competence("MODIFYFORM_CREATE");
		}catch(Exception $e){
			$backurl = spUrl("main", "welcome");
			$this->redirect($backurl, $e->getMessage());
			exit();
		}
	}
	
	private function competence_manage(){
		try {
			$obj_cpt = spClass("department_competence");
			if(!$_SESSION["sscrm_user"]["user_identity"]["operate"]["enabled"])
				$obj_cpt->check_login_competence("MODIFYFORM_MANAGE");
		}catch(Exception $e){
			$backurl = spUrl("main", "welcome");
			$this->redirect($backurl, $e->getMessage());
			exit();
		}
	}
	
	//信息列表
	function cformlist(){
		$this->competence_create();
		$obj_form = spClass('modifyform');
		$postdate = $this->spArgs();
		$page = intval(max($postdate['page'], 1));
		$condition = "crm_modifyform.from_userid =" . $_SESSION["sscrm_user"]["id"];
		if($postdate['searchkeys']){
			$condition .= " and crm_modifyform.title like %{$postdate['searchkeys']}%";
			$this->searchkeys = $postdate['searchkeys'];
		}
		if($postdate['starttime'] != ''){
			$condition .= " and crm_modifyform.createtime >= ".strtotime($postdate['starttime']);
			$this->starttime = $postdate['starttime'];
		}
		if($postdate['endtime'] != ''){
			$condition .= " and crm_modifyform.createtime <= ".strtotime($postdate['endtime']);
			$this->endtime = $postdate['endtime'];
		}
		if($postdate['sort'] != '' && in_array($postdate['sort'], array("asc", "desc"))){
			$sort = "crm_modifyform.createtime ".$postdate['sort'];
			$this->sort = $postdate['sort'];
		}else{
			$sort = "crm_modifyform.createtime desc";
			$this->sort = "desc";
		}
		$this->form_rs = $obj_form->spPager($page, 20)->findAll($condition, $sort);
		$this->pager = $obj_form->spPager()->getPager();
		$this->url = spUrl('modifyforms', 'cformlist', array("starttime"=>$this->starttime, "endtime"=>$this->endtime, "searchkeys"=>$postdate['searchkeys']));
	}
	
	function cformcreate(){
		$this->competence_create();
		$obj_form = spClass('modifyform');
	    if($_SERVER['REQUEST_METHOD'] == 'POST'){
	    	import("Common.php");
	    	$postdate = $this->spArgs();
			$isAjax = $postdate['isAjax'];
			$data['from_userid'] = $_SESSION["sscrm_user"]["id"];
			$data['title'] = $postdate["title"];
			$data['content'] = $postdate['content'];
			$data["create_ip"] = Common::GetIP();
			$data['createtime'] = time();
			try {
				if($result = $obj_form->spValidator($data)) {
					foreach($result as $item) {
						throw new Exception($item[0]);
					}
				}
				if(!$id = $obj_form->create($data))
					throw new Exception("未知错误，添加失败");
				spClass('user_log')->save_log(6, "添加了修改单 ".$data['title']." [id:$id]");
				$url = spUrl("modifyforms", "cformlist");
				$message = array('msg'=>'修改单添加成功！','result'=>1, 'url'=>$url);
				echo json_encode($message);
				exit();
			}catch (Exception $e){
				$message = array('msg' => $msg, 'result'=>0);
				echo json_encode($message);
				exit();
			}
	    }
	    $this->validator = $obj_form->getValidatorJS();
		$this->saveurl = spUrl("modifyforms", "cformcreate");
	}
	
	function cformview(){
		$this->competence_create();
		$obj_form = spClass('modifyform');
		$postdata = $this->spArgs();
		$id = intval($postdata["id"]);
		try {
			if(!$id)
				throw new Exception("参数丢失");
			if(!$form_rs = $obj_form->join("crm_user", "crm_user.id = crm_modifyform.from_userid")->join("crm_user as fuser", "fuser.id = crm_modifyform.finish_userid", "left")->find(array("crm_modifyform.id"=>$id), null, "crm_modifyform.*, crm_user.realname as from_realname, fuser.realname as finish_realname"))
				throw new Exception("找不到该修改单，可能已被删除");
		}catch(Exception $e){
			$this->redirect(spUrl("modifyforms", "dformlist"), $e->getMessage());
			exit();
		}
		$this->id = $id;
		$this->form_rs = $form_rs;
	}
	
	//信息列表
	function dformlist(){
		$this->competence_manage();
		$obj_form = spClass('modifyform');
		$postdate = $this->spArgs();
		$page = intval(max($postdate['page'], 1));
		$condition = "1";
		if($postdate['searchkeys']){
			$condition .= " and crm_modifyform.title like %{$postdate['searchkeys']}%";
			$this->searchkeys = $postdate['searchkeys'];
		}
		if($postdate['starttime'] != ''){
			$condition .= " and crm_modifyform.createtime >= ".strtotime($postdate['starttime']);
			$this->starttime = $postdate['starttime'];
		}
		if($postdate['endtime'] != ''){
			$condition .= " and crm_modifyform.createtime <= ".strtotime($postdate['endtime']);
			$this->endtime = $postdate['endtime'];
		}
		if($postdate['sort'] != '' && in_array($postdate['sort'], array("asc", "desc"))){
			$sort = "crm_modifyform.createtime ".$postdate['sort'];
			$this->sort = $postdate['sort'];
		}else{
			$sort = "crm_modifyform.createtime desc";
			$this->sort = "desc";
		}
		$this->form_rs = $obj_form->spPager($page, 20)->findAll($condition, $sort);
		$this->pager = $obj_form->spPager()->getPager();
		$this->url = spUrl('modifyforms', 'mformlist', array("starttime"=>$this->starttime, "endtime"=>$this->endtime, "searchkeys"=>$postdate['searchkeys']));
	}
	
	function dformdeal(){
		$this->competence_manage();
		$obj_form = spClass('modifyform');
		$postdata = $this->spArgs();
		$id = intval($postdata["id"]);
		try {
			if(!$id)
				throw new Exception("参数丢失");
			if(!$form_rs = $obj_form->join("crm_user", "crm_user.id = crm_modifyform.from_userid")->find(array("crm_modifyform.id"=>$id), null, "crm_modifyform.*, crm_user.realname as from_realname"))
				throw new Exception("找不到该修改单，可能已被删除");
			if($form_rs["isfinish"])
				throw new Exception("该修改单已完成，无法进行该操作");
		}catch(Exception $e){
			$this->redirect(spUrl("modifyforms", "dformlist"), $e->getMessage());
			exit();
		}
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			import("Common.php");
			$postdate = $this->spArgs();
			$isAjax = $postdate['isAjax'];
			$data['finish_userid'] = $_SESSION["sscrm_user"]["id"];
			$data['isfinish'] = 1;
			$data["finish_ip"] = Common::GetIP();
			$data['finishtime'] = time();
			$data['remark'] = $postdata["remark"];
			try {
				if(!$data['remark'])
					throw new Exception("修改方式不能为空");
				if(!$obj_form->update(array("id"=>$id), $data))
					throw new Exception("未知错误，添加失败");
				spClass('user_log')->save_log(6, "处理了修改单 ".$form_rs['title']." [id:$id]");
				$url = spUrl("modifyforms", "dformlist");
				$message = array('msg'=>'修改单更新成功！','result'=>1, 'url'=>$url);
				echo json_encode($message);
				exit();
			}catch (Exception $e){
				$message = array('msg' => $e->getMessage(), 'result'=>0);
				echo json_encode($message);
				exit();
			}
		}
		$this->id = $id;
		$this->form_rs = $form_rs;
		$this->validator = $obj_form->getValidatorJS();
		$this->saveurl = spUrl("modifyforms", "dformdeal");
	}
	
	function dformview(){
		$this->competence_manage();
		$obj_form = spClass('modifyform');
		$postdata = $this->spArgs();
		$id = intval($postdata["id"]);
		try {
			if(!$id)
				throw new Exception("参数丢失");
			if(!$form_rs = $obj_form->join("crm_user", "crm_user.id = crm_modifyform.from_userid")->join("crm_user as fuser", "fuser.id = crm_modifyform.finish_userid", "left")->find(array("crm_modifyform.id"=>$id), null, "crm_modifyform.*, crm_user.realname as from_realname, fuser.realname as finish_realname"))
				throw new Exception("找不到该修改单，可能已被删除");
		}catch(Exception $e){
			$this->redirect(spUrl("modifyforms", "dformlist"), $e->getMessage());
			exit();
		}
		$this->id = $id;
		$this->form_rs = $form_rs;
	}
}
?>