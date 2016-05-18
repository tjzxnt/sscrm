<?php
	class client_intention_record extends spModel{
		var $pk = "id";
		var $table = "client_intention_record";
		
		var $join = array(
			'crm_client_intention' => array(
				'mapkey' => 'id',
				'fkey' => 'intention_id'
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
		
		public function getCountById($intention_id){
			$rs = $this->find(array("intention_id"=>$intention_id), null, "count(id) as total");
			return intval($rs["total"]);
		}
		
		public function getCountByClientId($client_id){
			$rs = $this->join("crm_client_intention")->find(array("crm_client_intention.client_id"=>$client_id), null, "count(crm_client_intention_record.id) as total");
			return intval($rs["total"]);
		}
	}
?>