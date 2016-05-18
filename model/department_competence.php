<?php
	class department_competence extends spModel{
		var $pk = "id";
		var $table = "department_competence";
		
		public function get_competnet($competence){
			if($competence == "ALL"){
				$condition = array("isdel"=>0);
				$rs = $this->findAll($condition, "sort asc", "cname, mark");
			}elseif($competence){
				$competence_array = explode(",", $competence);
				foreach($competence_array as $val)
					$condition_array[] = "mark = '$val'";
				$condition = "isdel = 0 and (". implode(" OR ", $condition_array).")";
				$rs = $this->findAll($condition, "sort asc", "cname, mark");
			}
			if($rs){
				$result = array();
				foreach($rs as $val){
					$result[$val["mark"]] = $val["cname"];
				}
			}
			return $result;
		}
		
		public function check_login_competence($competence){
			if(!$_SESSION["sscrm_user"]["competence"][$competence]){
				if(!$rs = $this->find(array("mark"=>$competence, "isdel"=>0), null, "cname"))
					throw new Exception("找不到该权限值，500错误");
				throw new Exception("您没有访问该模块的权限，权限名[ " . $rs["cname"] . " ]");
			}
		}
	}
?>