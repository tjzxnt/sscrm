<?php
	class modifyform extends spModel{
		var $pk = "id";
		var $table = "modifyform";
		
		var $validator = array(
			"rules" => array(
				'title' => array(
					'required' => true,
					'minlength' => 5,
					'maxlength' => 50
				),
				'content' => array(
					'required' => true,
					'minlength' => 5,
					'maxlength' => 300
				)
			),
			"messages" => array( // 提示信息
				'title' => array(
					'required' => '修改单标题不能为空',	
					'minlength' => '修改单标题请控制在5~50字符',
					'maxlength' => '修改单标题请控制在5~50字符'
				),			
				'content' => array(
					'required' => '修改单内容不能为空',
					'minlength' => '修改单内容请控制在5~300字符',
					'maxlength' =>  '修改单内容请控制在5~300字符'
				)
			)
		);
	}
?>