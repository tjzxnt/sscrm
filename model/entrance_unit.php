<?php
	class entrance_unit extends spModel{
		
		var $pk = "id";
		var $table = "entrance_unit";
		
		var $validator = array(
			"rules" => array(
				'rate' => array(
					'required' => true
				)
			),
			"messages" => array( // 提示信息
				'rate' => array(
					'required' => '汇率不能为空'
				)
			)
		);
		
		//获取汇率列表
		public function getlist(){
			return $this->findAll(array("ishide"=>0), "sort asc");
		}
		
		//获取汇率
		public function get_rate($mark){
			$rate_rs = $this->find(array("mark"=>$mark, "ishide"=>0), null, "rate");
			return floatval($rate_rs["rate"]);
		}
	}
?>