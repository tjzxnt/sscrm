<?php
	class user_identity extends spModel{
		var $pk = "id";
		var $table = "user_identity";
		
		public function getlist($depart_id){
			return $this->findAll("find_in_set($depart_id, depart_id) and isdel = 0", "sort asc");
		}
		
		public function getinfo($id, $depart_id = 0){
			$condition = "id = $id and isdel = 0";
			if($depart_id)
				$condition .= " and find_in_set($depart_id, depart_id)";
			return $this->find($condition);
		}
		
		public function setidentity($rs){
			if($rs["identity_attr"]){
				$identity_array = explode(",", $rs["identity_attr"]);
				foreach($identity_array as $val){
					$result[$val]["enabled"] = 1;
					//$result[$rs["identity_attr"]]["identity_puserid"] = $rs["identity_puserid"];
				}
			}
			return $result;
		}
		
		public function checkidentity($identity){
			if($_SESSION["sscrm_user"]["user_identity"][$identity]["enabled"])
				return true;
			return false;
		}
		
		public function getindentity($identity){
			return $_SESSION["sscrm_user"]["user_identity"][$identity];
		}
		
		public function getindentitylist(){
			return $_SESSION["sscrm_user"]["user_identity"];
		}
		
		//检测个人权限
		public function check_login_competence($competence){
			if(!$_SESSION["sscrm_user"]["user_identity"][$competence]["enabled"]){
				if(!$rs = $this->find(array("imark"=>$competence, "isdel"=>0), null, "iname"))
					throw new Exception("找不到该个人权限值，500错误");
				throw new Exception("您没有访问该模块的个人权限，权限名[ " . $rs["iname"] . " ]");
			}
		}
	}
?>