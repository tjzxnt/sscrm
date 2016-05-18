<?php
	class channel_record extends spModel{
		var $pk = "id";
		var $table = "channel_record";
		
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
		
		public function getCountById($channel_id){
			$rs = $this->find(array("channel_id"=>$channel_id), null, "count(id) as total");
			return intval($rs["total"]);
		}
	}
?>