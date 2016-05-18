<?php
	class client_fund extends spModel{
		var $pk = "id";
		var $table = "client_fund";
		
		var $join = array(
			'crm_client' => array(
				'mapkey' => 'id',
				'fkey' => 'client_id'
			),
			'crm_user' => array(
				'mapkey' => 'id',
				'fkey' => 'create_id'
			),
			'crm_client_fund_type' => array(
				'mapkey' => 'id',
				'fkey' => 'fund_type'
			),
		);
		
		var $validator = array(
			"rules" => array(
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
		
		public function getHousefundTotal($client_id){
			$rs = $this->find("client_id=$client_id and (fund_type = 1 or fund_type = 2)", null, "sum(pay_real) as total");
			return intval($rs["total"]);
		}
	}
?>