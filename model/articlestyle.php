<?php
	class articlestyle extends spModel{
		var $pk = "sid"; // 每个留言唯一的标志，可以称为主键
		var $table = "articlestyle"; // 数据表的名称
		
		var $validator = array(
			"rules" => array(
				'sname' => array(
					'required' => true
				)
			),
			"messages" => array( // 提示信息
				'sname' => array(
					'required' => '栏目名不能为空'
				)
			)
		);
		
		public function init_condition($column_rs, $type = "str"){
			if($GLOBALS['spConfig']['entrance']['enabled'] && $column_rs["isentrance"])
				$condition = "(entrance_id = " . $_SESSION["sys_entrance"]["id"] . " or entrance_id = 0)";
			else
				$condition = "1";
			return $condition;
		}
		
		public function get_entid($column_rs){
			$entid = 0;
			if($GLOBALS['spConfig']['entrance']['enabled'] && $column_rs["isentrance"])
				$entid =  $_SESSION["sys_entrance"]["id"];
			return $entid;
		}
		
		public function check_line($column_rs){
			if($GLOBALS['spConfig']['entrance']['enabled'] && $GLOBALS['spConfig']['entrance']['entrance_line'] && $column_rs["isentrance"] && $column_rs["isentranceline"])
				return true;
			return false;
		}
		
		public function get_ent_line($column_rs){
			if($this->check_line($column_rs))
				return spClass("entrance_line")->findAll(array("entrance_id"=>$_SESSION["sys_entrance"]["id"], "ishide"=>0), "sort asc");
		}
	}
?>