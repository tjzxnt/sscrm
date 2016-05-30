<?php
	class client_intention_level extends spModel{
		var $pk = "id";
		var $table = "client_intention_level";
	
		public function getlist(){
			return $this->findAll(array("isdel"=>0), "sort asc");
		}
		
		public function getName($id){
			$rs = $this->find(array("id"=>$id), null, "name");
			return $rs["name"];
		}
	}
?>