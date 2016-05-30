<?php
	class client_seehouse extends spModel{
		var $pk = "id";
		var $table = "client_seehouse";
	
		public function getlist(){
			return $this->findAll(array("isdel"=>0), "sort asc");
		}
		
		public function getName($id){
			$rs = $this->find(array("id"=>$id), null, "see_status");
			return $rs["see_status"];
		}
	}
?>