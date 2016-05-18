<?php
	class client_intention extends spModel{
		var $pk = "id";
		var $table = "client_intention";
		
		var $join = array(
			'crm_user' => array(
				'mapkey' => 'id',
				'fkey' => 'create_id'
			),
			'crm_credential' => array(
				'mapkey' => 'id',
				'fkey' => 'cred_id'
			)
		);
		
		var $validator = array(
			"rules" => array(
				'realname' => array(
					'required' => true,
					'minlength' => 2,
					'maxlength' => 20
				),
				'tel_location' => array(
					'maxlength' => 10
				),
				'telphone' => array(
					'required' => true,
					'minlength' => 8,
					'maxlength' => 14
				),
				'demand' => array(
					'minlength' => 2,
					'maxlength' => 10
				),
				"feedback" => array(
					'maxlength' => 200
				)
			),
			"messages" => array(
				'realname' => array(
					'required' => '客户名不能为空',
					'minlength' => '客户名不能少于2个字符',
					'maxlength' => '客户名不能大于20字符'
				),
				'tel_location' => array(
					'maxlength' => '电话所在地不能超过10字符'
				),
				'telphone' => array(
					'required' => '客户电话不能为空',
					'minlength' => '请输入8~14位客户电话',
					'maxlength' => '请输入8~14位客户电话'
				),
				'exp_country' => array(
					'minlength' => '意向国家不能少于2个字符',
					'maxlength' => '意向国家不能大于20字符'
				),
				'demand' => array(
					'minlength' => '客户需求2~10个字符',
					'maxlength' => '客户需求2~10个字符'
				),
				"feedback" => array(
					'maxlength' => '反馈不能超过200字符'
				)
			)
		);
		
		public function checkintention($type, $user_id, $tel){
			$rs = $this->find(array("typeid"=>$type, "create_id"=>$user_id, "telphone"=>$tel, "isdel"=>0));
			if($rs["client_id"])
				throw new Exception("该CALL客意向客户已被录入到客户中，无法再次录入");
			if($rs["vip_client_id"])
				throw new Exception("该CALL客意向客户已被录入到大客户中，无法再次录入");
			return $rs;
		}
	}
?>