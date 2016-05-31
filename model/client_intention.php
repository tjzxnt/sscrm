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
			),
			'crm_client_intention_type' => array(
				'mapkey' => 'id',
				'fkey' => 'typeid'
			),
			'crm_client_intention_level' => array(
				'mapkey' => 'id',
				'fkey' => 'level_id'
			)
		);
		
		var $validator = array(
			"rules" => array(
				'typeid' => array(
					'required' => true,
				),
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
				'typeid' => array(
					'required' => '蓄水客户类型不能为空',
				),
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
		
		public function getintention($type, $tel){
			if(!$type || !$tel)
				throw new Exception("检测过程中参数丢失");
			$condition = "isdel = 0";
			$condition .= " and typeid = $type and telphone = $tel";
			$int_rs = $this->find($condition);
			if($int_rs["client_id"])
				throw new Exception("该客户已成为到访客户");
			if($int_rs["vip_client_id"])
				throw new Exception("该客户已成为大客户");
			return $int_rs;
		}
		
		public function isclient($int_rs){
			if($int_rs["client_id"])
				throw new Exception("该蓄水客户已被录入到访客户中，无法再次录入");
			if($int_rs["vip_client_id"])
				throw new Exception("该蓄水客户已被录入到大客户中，无法再次录入");
		}
		
		//已弃用
		public function checkintention($type, $user_id, $data){
			throw new Exception("该方法已弃用，如看到该提示请联系ERP管理员");
			$rs = $this->join("crm_client_intention_type")->find(array("crm_client_intention.typeid"=>$type, "crm_client_intention.create_id"=>$user_id, "crm_client_intention.telphone"=>$data["telphone"], "crm_client_intention.isdel"=>0), null, "crm_client_intention.*, crm_client_intention_type.name as typename, crm_client_intention_type.ischannel");
			if($rs["client_id"])
				throw new Exception("该{$rs["typename"]}蓄水客户已被录入到客户中，无法再次录入");
			if($rs["vip_client_id"])
				throw new Exception("该{$rs["typename"]}蓄水客户已被录入到大客户中，无法再次录入");
			if($rs["ischannel"]){
				if(!$rs["channel_id"])
					throw new Exception("该{$rs["typename"]}蓄水客户未选择渠道，请联系蓄水客户的添加人");
				if($data["channel_id"] != $rs["channel_id"])
					throw new Exception("你选择的渠道没有对应的{$rs["typename"]}蓄水客户");
				$data["channelact_id"] = intval($data["channelact_id"]);
				if($data["channelact_id"] && !$rs["channelact_id"])
					throw new Exception("该{$rs["typename"]}蓄水客户的来源于活动，无法添加到该类型客户，如有问题请联系蓄水客户的添加人");
				if(!$data["channelact_id"] && $rs["channelact_id"])
					throw new Exception("该{$rs["typename"]}蓄水客户的不是来源于渠道活动，无法添加到该类型客户，如有问题请联系蓄水客户的添加人");
				if(intval($data["channelact_id"]) != $rs["channelact_id"])
					throw new Exception("该{$rs["typename"]}蓄水客户的渠道活动与您添加的不相符，如有问题请联系蓄水客户的添加人");
			}
			return $rs;
		}
		
		//回访超时,插入记录
		public function record_overtime(){
			$sql = "SELECT * FROM
					(
						SELECT crm_client_intention.id, crm_client_intention.create_id, IF(maxrecord.acttime, maxrecord.acttime, crm_client_intention.createtime) as recordtime FROM crm_client_intention
						LEFT JOIN
						(
							SELECT crm_client_intention_record.intention_id, MAX(crm_client_intention_record.acttime) as acttime FROM crm_client_intention_record GROUP BY crm_client_intention_record.intention_id
						) as maxrecord
						ON
						maxrecord.intention_id = crm_client_intention.id
						where crm_client_intention.client_id = 0 and crm_client_intention.vip_client_id = 0
						order by crm_client_intention.id asc
					) as client_intention_recordtime
					where client_intention_recordtime.id not in(
						SELECT intention_id FROM crm_client_intention_overtime WHERE endtime = 0
					)
					and
					client_intention_recordtime.recordtime <=  unix_timestamp(now()) -  60*60*24*7
					";
			if($overtime_rs = $this->findSql($sql)){
				$obj_overtime = spClass("client_intention_overtime");
				foreach($overtime_rs as $val){
					$obj_overtime->addovertime($val);
				}
			}
		}
	}
?>