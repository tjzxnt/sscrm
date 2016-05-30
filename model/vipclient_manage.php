<?php
	class vipclient_manage extends spModel{
		var $pk = "id";
		var $table = "vip_client_manage";
		
		var $validator = array(
			"rules" => array(
				'realname' => array(
					'required' => true,
					'minlength' => 2,
					'maxlength' => 20
				),
				'managepost' => array(
					'required' => true,
					'maxlength' => 20
				),
				'tel_location' => array(
					'maxlength' => 10
				),
				'telphone' => array(
					'required' => true,
					'minlength' => 8,
					'maxlength' => 14
				)
			),
			"messages" => array(
				'realname' => array(
					'required' => '客户名不能为空',
					'minlength' => '客户名不能少于2个字符',
					'maxlength' => '客户名不能大于20字符'
				),
				'managepost' => array(
					'required' => '主联系人职务必填',
					'maxlength' => '主联系人职务不能超过20字符'
				),
				'tel_location' => array(
					'maxlength' => '电话所在地不能超过10字符'
				),
				'telphone' => array(
					'required' => '客户电话不能为空',
					'minlength' => '请输入8~14位客户电话',
					'maxlength' => '请输入8~14位客户电话'
				)
			)
		);
		
	}
?>