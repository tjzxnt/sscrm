<?php
	class department_identity extends spModel{
		var $pk = "id";
		var $table = "department_identity";
		
		public function autoidentity($depart_id){
			if($rs = $this->findAll("find_in_set($depart_id, depart_id) and isdel = 0", "sort asc", "iname, imark")){
				$result = array();
				foreach($rs as $key => $val){
					$result[$val["imark"]]["enabled"] = 1;
					$result[$val["imark"]]["iname"] = $val["iname"];
				}
				return $result;
			}
		}
		
		public function checkidentity($identity){
			if($_SESSION["sscrm_user"]["depart_identity"][$identity]["enabled"])
				return true;
			return false;
		}
	}
?>