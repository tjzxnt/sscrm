<?php
	class user_log_type extends spModel{
		var $pk = "id";
		var $table = "user_log_type";
		
		public function getlist(){
			return $this->findAll(array("isdel"=>0), "sort asc");
		}
	}
?>