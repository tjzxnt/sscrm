<?php
	class vipclient_record extends spModel{
		var $pk = "id";
		var $table = "vip_client_record";
		
		var $join = array(
			'crm_vip_client' => array(
				'mapkey' => 'id',
				'fkey' => 'client_id'
			),
			'crm_user' => array(
				'mapkey' => 'id',
				'fkey' => 'create_id'
			),
			'crm_vip_client_record_type' => array(
				'mapkey' => 'id',
				'fkey' => 'rtype_id'
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
		
		public function getCountById($client_id){
			$rs = $this->find(array("client_id"=>$client_id), null, "count(id) as total");
			return intval($rs["total"]);
		}
	}
?>