<?php
	class client_false extends spModel{
		var $pk = "id";
		var $table = "client_false";
		
		var $join = array(
			'crm_user' => array(
				'mapkey' => 'id',
				'fkey' => 'create_id'
			),
			'crm_client' => array(
				'mapkey' => 'id',
				'fkey' => 'client_id'
			)
		);
		
		var $validator = array(
			"rules" => array(
				'unsoldreason' => array(
					'required' => true,
					'minlength' => 10,
					'maxlength' => 30,
				)
			),
			"messages" => array(
				'unsoldreason' => array(
					'required' => '未成交原因不能为空',
					'minlength' => '未成交原因不能少于10个字符',
					'maxlength' => '未成交原因不能大于200字符'
				)
			)
		);
	}
?>