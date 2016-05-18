<?php
	class entrance_line extends spModel{
		
		var $pk = "id";
		var $table = "entrance_line";
		
		var $validator = array(
			"rules" => array(
				'name' => array(
					'required' => true,
					'maxlength' => 30,
				),
				'mark' => array(
					'required' => true,
					'maxlength' => 20,
				),
				'short' => array(
					'maxlength' => 100,
				)
			),
			"messages" => array( // 提示信息
				'name' => array(
					'required' => '入口线路名不能为空',	
					'maxlength' => '入口线路名不能大于30字符',
				),			
				'mark' => array(
					'required' => '标识不能为空',
					'minlength' =>  '标识不能大于20个字符'
				),			
				'short' => array(
					'maxlength' => '简述长度不能大于100字符'
				)
			)
		);
		
		public function getlist($entrance_id){
			return $this->findAll(array("entrance_id"=>intval($entrance_id), "ishide"=>0), "sort asc");
		}
		
		//添加时js验证
		public function getValidatorJS() {
			$validator = $this->validator;
			return parent::getValidatorJS($validator);
		}
	}
?>