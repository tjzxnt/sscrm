<?php
	class client_plan extends spModel{
		var $pk = "id";
		var $table = "client_plan";
		
		var $join = array(
			'crm_client' => array(
				'mapkey' => 'id',
				'fkey' => 'client_id'
			),
			'crm_channel' => array(
				'mapkey' => 'id',
				'fkey' => 'channel_id'
			),
			'crm_user' => array(
				'mapkey' => 'id',
				'fkey' => 'main_id'
			)
		);
		
		var $validator = array(
			"rules" => array(
				'title' => array(
					'required' => true,
					'minlength' => 2,
					'maxlength' => 20
				),
				'content' => array(
					'required' => true,
					'minlength' => 4,
					'maxlength' => 200
				),
				'main_id' => array(
					'required' => true
				),
				'starttime' => array(
					'required' => true,
				),
				'endtime' => array(
					'required' => true,
				)
			),
			"messages" => array(
				'title' => array(
					'required' => '主题不能为空',
					'minlength' => '主题不能少于2个字符',
					'maxlength' => '主题不能大于20字符'
				),
				'content' => array(
					'required' => '计划内容不能为空',
					'minlength' => '计划内容不能少于4个字符',
					'maxlength' => '计划内容不能大于200字符'
				),
				'main_id' => array(
					'required' => '参与人不能为空',
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