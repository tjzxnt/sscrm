<?php
	class entrance extends spModel{
		var $pk = "id";
		var $table = "entrance";
		
		public function init_condition(){
			if($GLOBALS['spConfig']['entrance']['enabled'])
				$condition = "(entrance_id = " . $_SESSION["sys_entrance"]["id"] . " or entrance_id = 0)";
			else
				$condition = "1";
			return $condition;
		}
		
		public function getlist(){
			return $this->findAll(array("ishide"=>0), "sort asc");
		}
		
		public function getinfoById($id){
			return $this->find(array("id"=>$id, "ishide"=>0), "sort asc");
		}
		
		public function getByMark($mark){
			if(!$ent_rs = $this->find(array("mark"=>$mark, "ishide"=>0)))
				$ent_rs = $this->getDefaultMark();
			return $ent_rs;
		}
		
		public function getDefaultMark(){
			return $this->find(array("ishide"=>0), "sort asc");
		}
		
		public function getEntidByLineid($line_id){
			$obj_line = spClass("entrance_line");
			$line_rs = $obj_line->find(array("id"=>$line_id), null, "entrance_id");
			return intval($line_rs["entrance_id"]);
		}
	}
?>