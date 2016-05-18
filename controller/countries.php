<?php
class countries extends spController {
	
	public function __construct(){
		parent::__construct();
		try {
			$obj_cpt = spClass("department_competence");
			if(!$_SESSION["sscrm_user"]["user_identity"]["operate"]["enabled"])
				$obj_cpt->check_login_competence("COUNTRY");
		}catch(Exception $e){
			$backurl = spUrl("main", "welcome");
			$this->redirect($backurl, $e->getMessage());
			exit();
		}
	}
	
	public function countrylist(){
		$obj_country = spClass('country');
		$postdate = $this->spArgs();
		$page = intval(max($postdate['page'], 1));
		$this->country_rs = $obj_country->join("crm_user")->spPager($page, 20)->findAll($condition, 'crm_country.sort asc', "crm_country.*, crm_user.realname as realname_overseas");
		$this->pager = $obj_country->spPager()->getPager();
		$this->url = spUrl('countries', 'countrylist');
	}
	
	public function createcountry(){
		try {
			$obj_user = spClass('user');
			$obj_country = spClass('country');
			$postdate = $this->spArgs();
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try {
					$data = array();
					$data['create_id'] = $_SESSION["sscrm_user"]["id"];
					$data['createtime'] = time();
					$data["country"] = $postdate["country"];
					$data["housefundtime"] = intval($postdate["housefundtime"]);
					$data['to_overseas_id'] = intval($postdate['to_overseas_id']);
					$data['sort'] = intval($postdate['sort']);
					if($result = $obj_country->spValidator($data)){
						foreach($result as $item) {
							throw new Exception($item[0]);
							break;
						}
					}
					if($data['housefundtime'] < 1)
						throw new Exception("房款次数不能小于1");
					if($data['to_overseas_id']){
						if(!$obj_user->getDepartUserinfo(4, $data['to_overseas_id']))
							throw new Exception("找不到所选择的海外专员");
					}else
						throw new Exception("请选择海外专员");
					if(!$obj_country->create($data))
						throw new Exception("未知错误，添加失败");
					$message = array('msg'=>"房源国家添加成功", 'result'=>1, "url"=>spUrl("countries", "countrylist"));
					echo json_encode($message);
					exit();
				}catch (Exception $e){
					$message = array('msg'=>$e->getMessage(), 'result'=>0);
					echo json_encode($message);
					exit();
				}
			}
			$this->validator = $obj_country->getValidatorJS();
			$this->user_rs = $obj_user->getUserByDepart(4);
			$this->saveurl = spUrl("countries", "createcountry");
		}catch(Exception $e){
			$this->redirect(spUrl('countries', 'countrylist'), $e->getMessage());
		}
	}
	
	public function modifycountry(){
		try {
			$obj_user = spClass('user');
			$obj_country = spClass('country');
			$postdate = $this->spArgs();
			$id = intval($postdate["id"]);
			if(!$id)
				throw new Exception("参数丢失");
			if(!$country_rs = $obj_country->getRsById($id))
				throw new Exception("找不到该国家");
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try {
					$data = array();
					$data["country"] = $postdate["country"];
					$data["housefundtime"] = intval($postdate["housefundtime"]);
					$data['to_overseas_id'] = intval($postdate['to_overseas_id']);
					$data['sort'] = intval($postdate['sort']);
					if($result = $obj_country->spValidator($data)){
						foreach($result as $item) {
							throw new Exception($item[0]);
							break;
						}
					}
					if($data['housefundtime'] < 1)
						throw new Exception("房款次数不能小于1");
					if($data['to_overseas_id']){
						if(!$obj_user->getDepartUserinfo(4, $data['to_overseas_id']))
							throw new Exception("找不到所选择的海外专员");
					}else
						throw new Exception("请选择海外专员");
					if(!$obj_country->update(array("id"=>$id), $data))
						throw new Exception("未知错误，更新失败");
					$message = array('msg'=>"房源国家更新成功", 'result'=>1, "url"=>spUrl("countries", "countrylist"));
					echo json_encode($message);
					exit();
				}catch (Exception $e){
					$message = array('msg'=>$e->getMessage(), 'result'=>0);
					echo json_encode($message);
					exit();
				}
			}
			$this->id = $id;
			$this->country_rs = $country_rs;
			$this->validator = $obj_country->getValidatorJS();
			$this->user_rs = $obj_user->getUserByDepart(4);
			$this->saveurl = spUrl("countries", "modifycountry");
			$this->display("countries/createcountry.html");
		}catch(Exception $e){
			$this->redirect(spUrl('countries', 'countrylist'), $e->getMessage());
		}
	}
}
?>