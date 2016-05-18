<?php
	class channel_type extends spModel{
		var $pk = "id";
		var $table = "channel_type";
		
		//根据父类获取列表
		public function getlist($parent_id = 0){
			$condition = array("ishide"=>0);
			$condition["parent_id"] = $parent_id;
			return $this->findAll($condition, "sort asc", "crm_channel_type.*");
		}
		
		//根据id获取名字
		public function getname($id){
			$rs = $this->find(array("id"=>$id), null, "crm_channel_type.name");
			return $rs["name"];
		}
		
		//根据id获取结果集
		public function getinfo($id){
			$rs = $this->find(array("id"=>$id));
			return $rs;
		}
	}
?>