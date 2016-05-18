<?php
	class config extends spModel{
		var $pk = "id";
		var $table = "config";
		
		public function get_val($mark){
			return $this->find(array("cname"=>$mark));
		}
		
		public function auto_client(){
			$obj_user = spClass("user");
			$config_rs = $this->get_val("auto_client");
			$auto_id = $config_rs["cval"];
			if(!$user_list_rs = $obj_user->getUserByDepart(3, "crm_user.identity_attr = 'getclient'"))
				throw new Exception("系统中没有可用销售，操作失败");
			if(!$user_rs = $obj_user->getDepartUserinfo(3, $auto_id))
				$auto_id = 0;
			if($count = count($user_list_rs) == 1 || $auto_id == 0){
				$user_rs = $user_list_rs[0];
			}else{
				foreach($user_list_rs as $key => $val){
					if($val["id"] == $auto_id){
						$user_rs = array_key_exists(($key+1), $user_list_rs) ? $user_list_rs[$key+1] : $user_list_rs[0];
						break;
					}
				}
			}
			if(!$user_rs)
				throw new Exception("没有找到可用销售，请联系系统管理人员");
			return $user_rs;
		}
		
		public function set_val($cname, $value){
			return $this->update(array("cname"=>$cname), array("cval"=>$value));
		}
	}
?>