<?php
	class client_order_fund extends spModel{
		var $pk = "id";
		var $table = "client_order_fund";
		
		var $join = array(
			'crm_client' => array(
				'mapkey' => 'id',
				'fkey' => 'client_id'
			),
			'crm_user' => array(
				'mapkey' => 'id',
				'fkey' => 'create_id'
			)
		);
		
		var $validator = array(
			"rules" => array(
				'fund_type' => array(
					'required' => true,
					"maxlength" => 20
				),
				'pay_standard' => array(
					'required' => true,
				),
				'payabletime' => array(
					'required' => true,
				),
				'arrivaltime' => array(
					'required' => true,
				),
				'pay_real' => array(
					'required' => true,
				)
			),
			"messages" => array(
				'fund_type' => array(
					'required' => '款项类型不能为空',
					'required' => '款项类型不能超过20字',
				),
				'pay_standard' => array(
					'required' => '应交数不能为空',
				),
				'payabletime' => array(
					'required' => '应交日期不能为空',
				),
				'arrivaltime' => array(
					'required' => '到账日期不能为空',
				),
				'pay_real' => array(
					'required' => '实交数不能为空',
				)
			)
		);
		
		public function getfundTotal($client_id){
			$rs = $this->find("client_id=$client_id", null, "sum(pay_real) as total");
			return intval($rs["total"]);
		}
	}
?>