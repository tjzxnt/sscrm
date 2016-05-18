<?php
	class department extends spModel{
		var $pk = "id";
		var $table = "department";
		
		public function getlist(){
			return $this->findAll(array("isadmin"=>0, "isdel"=>0), "sort asc");
		}
		
		public function getlist_all(){
			return $this->findAll(array("isdel"=>0), "sort asc");
		}
		
		//获取可用的部门信息
		public function getinfoById($id){
			return $this->find(array("id"=>$id, "isdel"=>0));
		}
		
		public function checkuser_use($id){
			return  $this->find(array("id"=>$id, "isadmin"=>0, "isdel"=>0)) ? true : false;
		}		
	}
?>