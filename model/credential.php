<?php
	class credential extends spModel{
		var $pk = "id";
		var $table = "credential";
		
		public function get_credential(){
			return $this->findAll(array("isdel"=>0), "sort asc");
		}
		
		//获取可用的证件结果(添加客户源时验证)
		public function getcredById($id){
			return $this->find(array("id"=>$id, "isdel"=>0));
		}
	}
?>