<?php
	class channel_plan extends spModel{
		var $pk = "id";
		var $table = "channel_plan";
		
		var $join = array(
			'crm_channel' => array(
				'mapkey' => 'id',
				'fkey' => 'channel_id'
			),
			'crm_user' => array(
				'mapkey' => 'id',
				'fkey' => 'create_id'
			)
		);
		
		var $validator = array(
			"rules" => array(
				'content' => array(
					'required' => true,
					'minlength' => 4,
					'maxlength' => 200
				),
				'starttime' => array(
					'required' => true,
				),
				'endtime' => array(
					'required' => true,
				)
			),
			"messages" => array(
				'content' => array(
					'required' => '计划内容不能为空',
					'minlength' => '计划内容不能少于4个字符',
					'maxlength' => '计划内容不能大于200字符'
				),
				'starttime' => array(
					'required' => '请选择计划开始时间',
				),
				'endtime' => array(
					'required' => '请选择计划结束时间',
				)
			)
		);
	}
?>