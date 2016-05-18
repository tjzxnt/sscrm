<?php
	class comactive extends spModel{
		var $pk = "id";
		var $table = "comactive";
		
		public function getlist(){
			return $this->findAll(array("isdel"=>0));
		}
		
		public function getActive($id){
			return $this->find(array("id"=>$id, "isdel"=>0));
		}
	}
?>