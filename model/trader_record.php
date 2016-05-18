<?php
	class trader_record extends spModel{
		var $pk = "id";
		var $table = "trader_record";
		
		var $join = array(
			'crm_trader' => array(
				'mapkey' => 'id',
				'fkey' => 'trader_id'
			),
			'crm_user' => array(
				'mapkey' => 'id',
				'fkey' => 'create_id'
			)
		);
		
		var $validator = array(
			"rules" => array(
				'acttime' => array(
					'required' => true,
				),
				'content' => array(
					'required' => true,
					'minlength' => 4,
					'maxlength' => 600
				)
			),
			"messages" => array(
				'acttime' => array(
					'required' => '沟通时间不能为空',
				),
				'content' => array(
					'required' => '沟通记录不能为空',
					'minlength' => '沟通记录不能少于4个字符',
					'maxlength' => '沟通记录不能大于600字符'
				)
			)
		);
	}
?>