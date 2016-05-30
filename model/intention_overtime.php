<?php
	class intention_overtime extends spModel{
		var $pk = "id";
		var $table = "intention_overtime";
		
		var $join = array(
			'crm_user' => array(
				'mapkey' => 'id',
				'fkey' => 'user_id'
			),
			'crm_client_intention' => array(
				'mapkey' => 'id',
				'fkey' => 'intention_id'
			)
		);
		
		var $validator = array(
			"rules" => array(
				'user_id' => array(
					'required' => true
				),
				'client_id' => array(
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
				'client_id' => array(
					'required' => "客户id必填"
				),
				'fromtime' => array(
					'required' => "来自时间必填"
				)
			)
		);
		
		public function addovertime($intention_rs){
			try {
				$data = array();
				$data["user_id"] = $intention_rs["user_sales_id"];
				$data["client_id"] = $intention_rs["id"];
				$data["fromtime"] = $intention_rs["recordtime"];
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
		
		public function getCount($client_id){
			$rs = $this->find(array("intention_id"=>$client_id), null, "count(id) as total");
			return intval($rs["total"]);
		}
	}
?>