<?php
	class client_process extends spModel{
		var $pk = "id";
		var $table = "client_process";
		
		public function getlist(){
			return $this->findAll(null, "sort asc");
		}
	}
?>