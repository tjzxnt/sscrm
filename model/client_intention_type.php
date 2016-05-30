<?php
	class client_intention_type extends spModel{
		var $pk = "id";
		var $table = "client_intention_type";
		
		//获取添加的类型
		public function getuselist(){
			$condition = "1 and isdel = 0 and (identity = 'none'";
			if($_SESSION["sscrm_user"]["user_identity"]){
				$condition .= "";
				foreach($_SESSION["sscrm_user"]["user_identity"] as $key => $val){
					$condition .= " or identity = '{$key}'";
				}
			}
			$condition .= ")";
			$condition .= " and (act_dep = '' or find_in_set({$_SESSION["sscrm_user"]["depart_id"]}, `act_dep`))";
			return $this->findAll($condition);
		}
		
		//获取管理权限的类型
		public function getviewlist(){
			$condition = "isdel = 0 and viewall_depdirector = '' or find_in_set({$_SESSION["sscrm_user"]["depart_id"]}, `viewall_depdirector`)";
			return $this->findAll($condition);
		}
		
		//获取全部的类型
		public function getlist(){
			$condition = "isdel = 0";
			return $this->findAll($condition);
		}
	}
?>