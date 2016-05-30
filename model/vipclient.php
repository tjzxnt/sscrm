<?php
	class vipclient extends spModel{
		var $pk = "id";
		var $table = "vip_client";
		
		var $join = array(
			'crm_user' => array(
				'mapkey' => 'id',
				'fkey' => 'create_id'
			),
			'crm_credential' => array(
				'mapkey' => 'id',
				'fkey' => 'cred_id'
			),
			'crm_country' => array(
				'mapkey' => 'id',
				'fkey' => 'exp_country_id'
			),
			'crm_client_process' => array(
				'mapkey' => 'id',
				'fkey' => 'process_id'
			),
			'crm_channel' => array(
				'mapkey' => 'id',
				'fkey' => 'channel_id'
			),
			'crm_origin' => array(
				'mapkey' => 'id',
				'fkey' => 'origin_id'
			),
			'crm_client_level' => array(
				'mapkey' => 'id',
				'fkey' => 'level_id'
			)
		);
		
		var $validator = array(
			"rules" => array(
				'comname' => array(
					'required' => true,
					'minlength' => 2,
					'maxlength' => 100
				),
				"visit_time" => array(
					"required" => true
				),
				'exp_country' => array(
					'minlength' => 2,
					'maxlength' => 20
				),
				'demand' => array(
					'minlength' => 2,
					'maxlength' => 50
				),
				"feedback" => array(
					'maxlength' => 200
				)
			),
			"messages" => array(
				'comname' => array(
					'required' => '公司名不能为空',
					'minlength' => '公司名不能少于2个字符',
					'maxlength' => '公司名不能大于20字符'
				),
				"visit_time" => array(
					"required" => '首次面资时间必填'
				),
				'exp_country' => array(
					'minlength' => '意向国家不能少于2个字符',
					'maxlength' => '意向国家不能大于20字符'
				),
				'demand' => array(
					'minlength' => '客户需求2~50个字符',
					'maxlength' => '客户需求2~50个字符'
				),
				"feedback" => array(
					'maxlength' => '反馈不能超过200字符'
				)
			)
		);
		
		public function getValidatorForChannelJS(){
			$validator = $this->validator;
			/*
			$validator['rules']['channel_name'] = array('required' => true, 'maxlength' => 50);
			$validator['messages']['channel_name'] = array('required' => "请填写渠道机构", 'maxlength' => "渠道机构不能超过50字符");
			$validator['rules']['channelact_name'] = array('required' => true, 'maxlength' => 50);
			$validator['messages']['channelact_name'] = array('required' => "请填写渠道活动", 'maxlength' => "渠道活动不能超过50字符");
			*/
			return parent::getValidatorJS($validator);
		}
		
		public function getValidatorForChannel(){
			/*
			$this->validator['rules']['channel_name'] = array('required' => true, 'maxlength' => 50);
			$this->validator['messages']['channel_name'] = array('required' => "请填写渠道机构", 'maxlength' => "渠道机构不能超过50字符");
			$this->validator['rules']['channelact_name'] = array('required' => true, 'maxlength' => 50);
			$this->validator['messages']['channelact_name'] = array('required' => "请填写渠道活动", 'maxlength' => "渠道活动不能超过50字符");
			*/
			return $this;
		}
		
		public function getValidatorForChannelUpdateJS(){
			$validator = $this->validator;
			return parent::getValidatorJS($validator);
		}
		
		public function getValidatorForChannelUpdate(){
			return $this;
		}
		
		public function getValidatorForOriginJS(){
			$validator = $this->validator;
			return parent::getValidatorJS($validator);
		}
		
		public function getValidatorForOrigin(){
			return $this;
		}
		
		public function getValidatorForUnPersonJS(){
			$validator = $this->validator;
			return parent::getValidatorJS($validator);
		}
		
		public function getValidatorForUnPerson(){
			return $this;
		}
		
		public function getValidatorForBusinessJS(){
			$validator['rules']['listings'] = array('required' => true, 'maxlength' => 50);
			$validator['messages']['listings'] = array('required' => "请填写房源名", 'maxlength' => "房源名不能超过50字符");
			$validator['rules']['listingstype'] = array('required' => true, 'maxlength' => 50);
			$validator['messages']['listingstype'] = array('required' => "请填写房源类型", 'maxlength' => "房源类型不能超过50字符");
			$validator['rules']['listingsarea'] = array('required' => true, 'maxlength' => 50);
			$validator['messages']['listingsarea'] = array('required' => "请填写房源面积", 'maxlength' => "房源面积不能超过50字符");
			$validator['rules']['bargain'] = array('required' => true, 'maxlength' => 50);
			$validator['messages']['bargain'] = array('required' => "请填写合同编号", 'maxlength' => "合同编号不能超过50字符");
			$validator['rules']['service_price_standard'] = array('required' => true);
			$validator['messages']['service_price_standard'] = array('required' => "请填写标准总价");
			$validator['rules']['service_rate_standard'] = array('required' => true, 'maxlength' => 10);
			$validator['messages']['service_rate_standard'] = array('required' => "请填写服务费比例", 'maxlength' => "服务费比例不能超过10字符");
			$validator['rules']['service_rate_preferent'] = array('required' => true, 'maxlength' => 10);
			$validator['messages']['service_rate_preferent'] = array('required' => "请填写优惠后服务费比例", 'maxlength' => "优惠后服务费比例不能超过10字符");
			/*
			$validator['rules']['service_price_preferential'] = array('required' => true);
			$validator['messages']['service_price_preferential'] = array('required' => "请填写优惠金额");
			*/
			$validator['rules']['service_price_real'] = array('required' => true);
			$validator['messages']['service_price_real'] = array('required' => "请填写实际总服务费");
			$validator['rules']['preferential_reason'] = array('maxlength' => 100);
			$validator['messages']['preferential_reason'] = array('maxlength' => "合同编号不能超过100字符");
			$validator['rules']['dealtime'] = array('required' => true);
			$validator['messages']['dealtime'] = array('required' => "请填写成交时间");
			return parent::getValidatorJS($validator);
		}
		
		public function getValidatorForBusiness(){
			unset($this->validator);
			$this->validator['rules']['listings'] = array('required' => true, 'maxlength' => 50);
			$this->validator['messages']['listings'] = array('required' => "请填写房源名", 'maxlength' => "房源名不能超过50字符");
			$this->validator['rules']['listingstype'] = array('required' => true, 'maxlength' => 50);
			$this->validator['messages']['listingstype'] = array('required' => "请填写房源类型", 'maxlength' => "房源类型不能超过50字符");
			$this->validator['rules']['listingsarea'] = array('required' => true, 'maxlength' => 50);
			$this->validator['messages']['listingsarea'] = array('required' => "请填写房源面积", 'maxlength' => "房源面积不能超过50字符");
			$this->validator['rules']['bargain'] = array('required' => true, 'maxlength' => 50);
			$this->validator['messages']['bargain'] = array('required' => "请填写合同编号", 'maxlength' => "合同编号不能超过50字符");
			$this->validator['rules']['service_price_standard'] = array('required' => true);
			$this->validator['messages']['service_price_standard'] = array('required' => "请填写标准总价");
			$this->validator['rules']['service_rate_standard'] = array('required' => true, 'maxlength' => 10);
			$this->validator['messages']['service_rate_standard'] = array('required' => "请填写服务费比例", 'maxlength' => "服务费比例不能超过10字符");
			$this->validator['rules']['service_rate_preferent'] = array('required' => true, 'maxlength' => 10);
			$this->validator['messages']['service_rate_preferent'] = array('required' => "请填写优惠后服务费比例", 'maxlength' => "优惠后服务费比例不能超过10字符");
			/*
			$this->validator['rules']['service_price_preferential'] = array('required' => true);
			$this->validator['messages']['service_price_preferential'] = array('required' => "请填写优惠金额");
			*/
			$this->validator['rules']['service_price_real'] = array('required' => true);
			$this->validator['messages']['service_price_real'] = array('required' => "请填写实际总服务费");
			$this->validator['rules']['preferential_reason'] = array('maxlength' => 100);
			$this->validator['messages']['preferential_reason'] = array('maxlength' => "合同编号不能超过100字符");
			$this->validator['rules']['dealtime'] = array('required' => true);
			$this->validator['messages']['dealtime'] = array('required' => "请填写成交时间");
			return $this;
		}
		
		public function getValidatorForFundJS(){
			$validator['rules']['intent_price'] = array('required' => true);
			$validator['messages']['intent_price'] = array('required' => "请填写意向金应交数");
			$validator['rules']['intent_paytype'] = array('required' => true);
			$validator['messages']['intent_paytype'] = array('required' => "请填写意向金付款方式");
			$validator['rules']['intent_payabletime'] = array('required' => true);
			$validator['messages']['intent_payabletime'] = array('required' => "请填写意向金应交日期");
			$validator['rules']['intent_arrivaltime'] = array('required' => true);
			$validator['messages']['intent_arrivaltime'] = array('required' => "请填写意向金到账日期");
			$validator['rules']['intent_payreal'] = array('required' => true);
			$validator['messages']['intent_payreal'] = array('required' => "请填写意向金实交数");
			/*
			$validator['rules']['service_price'] = array('required' => true);
			$validator['messages']['service_price'] = array('required' => "请填写服务费应交数");
			$validator['rules']['service_paytype'] = array('required' => true);
			$validator['messages']['service_paytype'] = array('required' => "请填写服务费付款方式");
			$validator['rules']['service_payabletime'] = array('required' => true);
			$validator['messages']['service_payabletime'] = array('required' => "请填写服务费应交日期");
			$validator['rules']['service_arrivaltime'] = array('required' => true);
			$validator['messages']['service_arrivaltime'] = array('required' => "请填写服务费到账日期");
			$validator['rules']['service_payreal'] = array('required' => true);
			$validator['messages']['service_payreal'] = array('required' => "请填写服务费实交数");
			*/
			return parent::getValidatorJS($validator);
		}
		
		public function getValidatorForFund(){
			unset($this->validator);
			$this->validator['rules']['intent_price'] = array('required' => true);
			$this->validator['messages']['intent_price'] = array('required' => "请填写意向金应交数");
			$this->validator['rules']['intent_paytype'] = array('required' => true);
			$this->validator['messages']['intent_paytype'] = array('required' => "请填写意向金付款方式");
			$this->validator['rules']['intent_payabletime'] = array('required' => true);
			$this->validator['messages']['intent_payabletime'] = array('required' => "请填写意向金应交日期");
			$this->validator['rules']['intent_arrivaltime'] = array('required' => true);
			$this->validator['messages']['intent_arrivaltime'] = array('required' => "请填写意向金到账日期");
			$this->validator['rules']['intent_payreal'] = array('required' => true);
			$this->validator['messages']['intent_payreal'] = array('required' => "请填写意向金实交数");
			/*
			$this->validator['rules']['service_price'] = array('required' => true);
			$this->validator['messages']['service_price'] = array('required' => "请填写服务费应交数");
			$this->validator['rules']['service_paytype'] = array('required' => true);
			$this->validator['messages']['service_paytype'] = array('required' => "请填写服务费付款方式");
			$this->validator['rules']['service_payabletime'] = array('required' => true);
			$this->validator['messages']['service_payabletime'] = array('required' => "请填写服务费应交日期");
			$this->validator['rules']['service_arrivaltime'] = array('required' => true);
			$this->validator['messages']['service_arrivaltime'] = array('required' => "请填写服务费到账日期");
			$this->validator['rules']['service_payreal'] = array('required' => true);
			$this->validator['messages']['service_payreal'] = array('required' => "请填写服务费实交数");
			*/
			return $this;
		}
		
		public function getValidatorForHouseJS(){
			$validator['rules']['listings'] = array('required' => true, 'maxlength' => 50);
			$validator['messages']['listings'] = array('required' => "请填写房源名", 'maxlength' => "房源名不能超过50字符");
			$validator['rules']['listingstype'] = array('required' => true, 'maxlength' => 50);
			$validator['messages']['listingstype'] = array('required' => "请填写房源类型", 'maxlength' => "房源类型不能超过50字符");
			$validator['rules']['listingsarea'] = array('required' => true, 'maxlength' => 50);
			$validator['messages']['listingsarea'] = array('required' => "请填写房源面积", 'maxlength' => "房源面积不能超过50字符");
			$validator['rules']['houseunit'] = array('required' => true, "maxlength"=>50);
			$validator['messages']['houseunit'] = array('required' => "请填写房款货币单位", "maxlength"=>"房款货币单位不能超过10字符");
			$validator['rules']['housefund'] = array('required' => true);
			$validator['messages']['housefund'] = array('required' => "请填写房款");
			$validator['rules']['houselenders'] = array('required' => true, "maxlength"=>50);
			$validator['messages']['houselenders'] = array('required' => "请填写贷款行，如果没有贷款请填写“无”", "maxlength"=>"贷款行不能超过50字符");
			$validator['rules']['houselender_rate'] = array('required' => true, "maxlength"=>10);
			$validator['messages']['houselender_rate'] = array('required' => "请填写贷款成数，如果没有贷款请填写“无”", "maxlength"=>"贷款成数不能超过10字符");
			$validator['rules']['house_firstpay'] = array('required' => true);
			$validator['messages']['house_firstpay'] = array('required' => "请填写首付款");
			return parent::getValidatorJS($validator);
		}
		
		public function getValidatorForHouse(){
			unset($this->validator);
			$this->validator['rules']['listings'] = array('required' => true, 'maxlength' => 50);
			$this->validator['messages']['listings'] = array('required' => "请填写房源名", 'maxlength' => "房源名不能超过50字符");
			$this->validator['rules']['listingstype'] = array('required' => true, 'maxlength' => 50);
			$this->validator['messages']['listingstype'] = array('required' => "请填写房源类型", 'maxlength' => "房源类型不能超过50字符");
			$this->validator['rules']['listingsarea'] = array('required' => true, 'maxlength' => 50);
			$this->validator['messages']['listingsarea'] = array('required' => "请填写房源面积", 'maxlength' => "房源面积不能超过50字符");
			$this->validator['rules']['houseunit'] = array('required' => true, "maxlength"=>50);
			$this->validator['messages']['houseunit'] = array('required' => "请填写房款货币单位", "maxlength"=>"房款货币单位不能超过10字符");
			$this->validator['rules']['housefund'] = array('required' => true);
			$this->validator['messages']['housefund'] = array('required' => "请填写房款");
			$this->validator['rules']['houselenders'] = array('required' => true, "maxlength"=>50);
			$this->validator['messages']['houselenders'] = array('required' => "请填写贷款行，如果没有贷款请填写“无”", "maxlength"=>"贷款行不能超过50字符");
			$this->validator['rules']['houselender_rate'] = array('required' => true, "maxlength"=>10);
			$this->validator['messages']['houselender_rate'] = array('required' => "请填写贷款成数，如果没有贷款请填写“无”", "maxlength"=>"贷款成数不能超过10字符");
			$this->validator['rules']['house_firstpay'] = array('required' => true);
			$this->validator['messages']['house_firstpay'] = array('required' => "请填写首付款");
			return $this;
		}

		public function getMyClientById($client_id){
			$rs = $this->join("crm_vip_client_manage", "crm_vip_client_manage.vip_client_id = crm_vip_client.id and crm_vip_client_manage.ismain = 1")->find(array("crm_vip_client.id"=>$client_id, "crm_vip_client.create_id"=>$_SESSION["sscrm_user"]["id"]), null, "crm_vip_client.*, crm_vip_client_manage.realname, crm_vip_client_manage.sex, crm_vip_client_manage.managepost, crm_vip_client_manage.tel_location, crm_vip_client_manage.telphone, crm_vip_client_manage.email, crm_vip_client_manage.wechat");
			if($rs["exp_country_id"]){
				$obj_country = spClass("country");
				$rs["exp_country"] = $obj_country->getname($rs["exp_country_id"]);
			}
			return $rs;
		}
		
		public function getClientById($client_id){
			$rs = $this->join("crm_vip_client_manage", "crm_vip_client_manage.vip_client_id = crm_vip_client.id and crm_vip_client_manage.ismain = 1")->find(array("crm_vip_client.id"=>$client_id), null, "crm_vip_client.*, crm_vip_client_manage.realname, crm_vip_client_manage.sex, crm_vip_client_manage.managepost, crm_vip_client_manage.tel_location, crm_vip_client_manage.telphone, crm_vip_client_manage.email, crm_vip_client_manage.wechat");
			if($rs["exp_country_id"]){
				$obj_country = spClass("country");
				$rs["exp_country"] = $obj_country->getname($rs["exp_country_id"]);
			}
			return $rs;
		}
		
		public function getOverSeasClientById($client_id){
			//$rs = $this->join("crm_credential")->join("crm_user as sale_user", "sale_user.id = crm_client.user_sales_id")->join("crm_user as over_user", "over_user.id = crm_client.user_overseas_id", "left")->find(array("crm_client.id"=>$client_id), null, "crm_client.*, crm_credential.cname as credential_name, sale_user.realname as realname_sale");
			$rs = $this->join("crm_vip_client_manage", "crm_vip_client_manage.vip_client_id = crm_vip_client.id and crm_vip_client_manage.ismain = 1")->join("crm_user")->find(array("crm_vip_client.id"=>$client_id, "is_protocol"=>1), null, "crm_vip_client.*, crm_vip_client_manage.realname, crm_vip_client_manage.sex, crm_vip_client_manage.managepost, crm_vip_client_manage.tel_location, crm_vip_client_manage.telphone, crm_vip_client_manage.email, crm_vip_client_manage.wechat, crm_user.realname as realname_sale");
			if($rs["exp_country_id"]){
				$obj_country = spClass("country");
				$rs["exp_country"] = $obj_country->getname($rs["exp_country_id"]);
			}
			return $rs;
		}
		
		//回访超时,插入记录
		public function record_overtime(){
			$sql = "SELECT * FROM
					(
						SELECT crm_vip_client.id, crm_vip_client.create_id, IF(maxrecord.acttime, maxrecord.acttime, crm_vip_client.createtime) as recordtime FROM crm_vip_client
						LEFT JOIN
						(
							SELECT crm_vip_client_record.client_id, MAX(crm_vip_client_record.acttime) as acttime FROM crm_vip_client_record GROUP BY crm_vip_client_record.client_id
						) as maxrecord
						ON
						maxrecord.client_id = crm_vip_client.id
						where crm_vip_client.is_protocol = 0
						order by crm_vip_client.id asc
					) as client_vip_recordtime
					where client_vip_recordtime.id not in(
						SELECT client_id FROM crm_vip_client_overtime WHERE endtime = 0
					)
					and
					client_vip_recordtime.recordtime <=  unix_timestamp(now()) -  60*60*24*3
					";
			if($overtime_rs = $this->findSql($sql)){
				$obj_overtime = spClass("vipclient_overtime");
				foreach($overtime_rs as $val){
					$obj_overtime->addovertime($val);
				}
			}
		}
	}
?>