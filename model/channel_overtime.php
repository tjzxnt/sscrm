<?php
	class channel_overtime extends spModel{
		var $pk = "id";
		var $table = "channel_overtime";
		
		var $join = array(
			'crm_user' => array(
				'mapkey' => 'id',
				'fkey' => 'user_id'
			),
			'crm_channel' => array(
				'mapkey' => 'id',
				'fkey' => 'channel_id'
			)
		);
		
		var $validator = array(
			"rules" => array(
				'user_id' => array(
					'required' => true
				),
				'channel_id' => array(
					'required' => true
				),
				'fromtime' => array(
					'required' => true
				)
			),
			"messages" => array(
				'user_id' => array(
					'required' => "用户id必填"
				),
				'channel_id' => array(
					'required' => "渠道id必填"
				),
				'fromtime' => array(
					'required' => "来自时间必填"
				)
			)
		);
		
		public function addovertime($channel_rs){
			try {
				$data = array();
				$data["user_id"] = $channel_rs["maintenance_id"];
				$data["channel_id"] = $channel_rs["id"];
				$data["fromtime"] = $channel_rs["recordtime"];
				$data["endtime"] = 0;
				$data["createtime"] = time();
				if($result = $this->spValidator($data)){
					foreach($result as $item) {
						throw new Exception($item[0]);
						break;
					}
				}
				$this->create($data);
			}catch(Exception $e){
				
			}
		}
		
		public function endovertime($id){
			try {
				$data = array();
				$data["endtime"] = time();
				$this->update(array("id"=>$id), $data);
			}catch(Exception $e){
			
			}
		}
		
		public function getCount($channel_id){
			$rs = $this->find(array("channel_id"=>$channel_id), null, "count(id) as total");
			return intval($rs["total"]);
		}
	}
?>