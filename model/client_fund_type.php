<?php
	class client_fund_type extends spModel{
		var $pk = "id";
		var $table = "client_fund_type";
		
		public function getunhouseinfo($id){
			return $this->find(array("id"=>$id, "ishouse"=>0, "isdel"=>0), "sort asc");
		}
		
		public function getunhouselist(){
			return $this->findAll(array("ishouse"=>0, "isdel"=>0), "sort asc");
		}
		
		public function getlist(){
			return $this->findAll(array("isdel"=>0), "sort asc");
		}
	}
?>