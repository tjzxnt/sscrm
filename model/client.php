<?php
	class client extends spModel{
		var $pk = "id";
		var $table = "client";
		
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
			),
			'crm_client_seehouse' => array(
				'mapkey' => 'id',
				'fkey' => 'seehouse'
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
				"visit_time" => array(
					"required" => '到访时间必填'
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
		
		//设为失败，并放入进程池
		public function setFalse($client_id){
			$this->update(array("id"=>$client_id), array("ispay"=>-1, "ispool"=>1, "process_id"=>4, "user_sales_id"=>0, "isauto_touser"=>0, "pooltime"=>time()));
		}
		
		//设为待转接客户
		public function setTransfer($client_id){
			$this->update(array("id"=>$client_id), array("istransfer"=>1, "process_id"=>5, "user_sales_id"=>0, "isauto_touser"=>0));
		}
		
		//将进程池客户放入我的客户中
		public function pooltome($client_id){
			$obj_identity = spClass("user_identity");
			/*
			//暂去除个人权限
			if(!$obj_identity->checkidentity("getclient"))
				throw new Exception("您没有接触客户的权限");
			*/
			$this->update(array("id"=>$client_id), array("ispay"=>0, "ispool"=>0, "process_id"=>1, "user_sales_id"=>$_SESSION["sscrm_user"]["id"], "isauto_touser"=>0, "poolouttime"=>time()));
		}
		
		public function getMyCreatedClientById($client_id){
			$rs = $this->join("crm_credential")->join("crm_user as channel_user", "channel_user.id = crm_client.user_channel_id", "left")->join("crm_user as sale_user", "sale_user.id = crm_client.user_sales_id", "left")->find(array("crm_client.id"=>$client_id, "crm_client.create_id"=>$_SESSION['sscrm_user']["id"]), null, "crm_client.*, crm_credential.cname as credential_name, channel_user.realname as realname_channel, sale_user.realname as realname_sale");
			if($rs["exp_country_id"]){
				$obj_country = spClass("country");
				$rs["exp_country"] = $obj_country->getname($rs["exp_country_id"]);
			}
			if($rs["channel_id"] && $rs["channelact_id"]){
				$obj_active = spClass("channel_active");
				if($act_rs = $obj_active->get_actives_by_channelid_id($rs["channel_id"], $rs["channelact_id"]))
					$rs["actname"] = $act_rs["actname"];
			}
			return $rs;
		}
		
		public function getClientById($client_id){
			$rs = $this->join("crm_credential")->join("crm_user as channel_user", "channel_user.id = crm_client.user_channel_id", "left")->join("crm_user as sale_user", "sale_user.id = crm_client.user_sales_id", "left")->find(array("crm_client.id"=>$client_id), null, "crm_client.*, crm_credential.cname as credential_name, channel_user.realname as realname_channel, sale_user.realname as realname_sale");
			if($rs["exp_country_id"]){
				$obj_country = spClass("country");
				$rs["exp_country"] = $obj_country->getname($rs["exp_country_id"]);
			}
			return $rs;
		}
		
		public function getPoolClientById($client_id){
			$rs = $this->join("crm_credential")->join("crm_channel", "crm_channel.id = crm_client.channel_id", "left")->join("crm_channel_active", "crm_channel_active.id = crm_client.channelact_id", "left")->join("crm_channel_user", "crm_channel_user.id = crm_client.channel_referrals", "left")->join("crm_user as channel_user", "channel_user.id = crm_client.user_channel_id", "left")->find(array("crm_client.id"=>$client_id, "crm_client.ispool"=>1), null, "crm_client.*, crm_credential.cname as credential_name, channel_user.realname as realname_channel, crm_channel.mechanism, crm_channel_active.actname as channelact_name, crm_channel_user.realname as realname_channel_user");
			if($rs["exp_country_id"]){
				$obj_country = spClass("country");
				$rs["exp_country"] = $obj_country->getname($rs["exp_country_id"]);
			}
			return $rs;
		}
		
		public function getTransferClientById($client_id){
			$rs = $this->join("crm_credential")->join("crm_channel", "crm_channel.id = crm_client.channel_id", "left")->join("crm_channel_active", "crm_channel_active.id = crm_client.channelact_id", "left")->join("crm_channel_user", "crm_channel_user.id = crm_client.channel_referrals", "left")->join("crm_user as channel_user", "channel_user.id = crm_client.user_channel_id", "left")->find(array("crm_client.id"=>$client_id, "crm_client.istransfer"=>1), null, "crm_client.*, crm_credential.cname as credential_name, channel_user.realname as realname_channel, crm_channel.mechanism, crm_channel_active.actname as channelact_name, crm_channel_user.realname as realname_channel_user");
			return $rs;
		}
		
		public function getMySalesClientById($client_id){
			$rs = $this->join("crm_credential")->join("crm_channel", "crm_channel.id = crm_client.channel_id", "left")->join("crm_channel_active", "crm_channel_active.id = crm_client.channelact_id", "left")->join("crm_channel_user", "crm_channel_user.id = crm_client.channel_referrals", "left")->join("crm_user as channel_user", "channel_user.id = crm_client.user_channel_id", "left")->join("crm_user as sale_user", "sale_user.id = crm_client.user_sales_id", "left")->find(array("crm_client.id"=>$client_id, "crm_client.user_sales_id"=>$_SESSION['sscrm_user']["id"]), null, "crm_client.*, crm_credential.cname as credential_name, channel_user.realname as realname_channel, sale_user.realname as realname_sale, crm_channel.mechanism, crm_channel_active.actname as channelact_name, crm_channel_user.realname as realname_channel_user");
			if($rs["exp_country_id"]){
				$obj_country = spClass("country");
				$rs["exp_country"] = $obj_country->getname($rs["exp_country_id"]);
			}
			return $rs;
		}
		
		public function getOverSeasClientById($client_id){
			//$rs = $this->join("crm_credential")->join("crm_user as sale_user", "sale_user.id = crm_client.user_sales_id")->join("crm_user as over_user", "over_user.id = crm_client.user_overseas_id", "left")->find(array("crm_client.id"=>$client_id), null, "crm_client.*, crm_credential.cname as credential_name, sale_user.realname as realname_sale");
			$rs = $this->join("crm_credential")->join("crm_user as sale_user", "sale_user.id = crm_client.user_sales_id")->find(array("crm_client.id"=>$client_id, "is_protocol"=>1), null, "crm_client.*, crm_credential.cname as credential_name, sale_user.realname as realname_sale");
			if($rs["exp_country_id"]){
				$obj_country = spClass("country");
				$rs["exp_country"] = $obj_country->getname($rs["exp_country_id"]);
			}
			return $rs;
		}
		
		public function createForPool($client_id){
			$obj_false = spClass("client_false");
			$this->setFalse($client_id);
			$data = array();
			$data["client_id"] = $client_id;
			$data["create_id"] = $_SESSION["sscrm_user"]["id"];
			$data["unsoldreason"] = "创建时放入进程池";
			$data["createtime"] = time();
			if(!$obj_false->create($data))
				throw new Exception("未知错误，客户更新失败");
		}
		
		public function createForTransfer($client_id){
			$this->setTransfer($client_id);
		}
		
		//客户被分配操作
		public function transfer($data, $origin_rs){
			if(!$data["user_sales_id"])
				throw new Exception("请先选择置业顾问再进行该操作");
			$id = intval($data["id"]);
			unset($data["id"]);
			if(!$id)
				throw new Exception("参数错误");
			$data["process_id"] = 1;
			$data["istransfer"] = 0;
			$data["transfertime"] = time();
			$obj_user = spClass("user");
			if(!$sale_rs = $obj_user->find(array("id"=>$data["user_sales_id"]), null, "realname, depart_id"))
				throw new Exception("找不到该置业顾问，可能已被删除");
			if(!$origin_rs["transfer_department"])
				throw new Exception("未设置分配部门组，无法进行该操作");
			$depart_array = explode(",", $origin_rs["transfer_department"]);
			if(!in_array($sale_rs["depart_id"], $depart_array))
				throw new Exception("不允许将客户分配给此人");
			if(!$this->update(array("id"=>$id), $data))
				throw new Exception("未知错误，分配失败");
			return $sale_rs;
		}
		
		//来源于我的渠道的客户总数
		public function channel_active_count($user_rs, $statdate){
			$condition = "crm_client.channel_id > 0 and crm_channel.maintenance_id = " . intval($user_rs["id"]);
			if($statdate)
				$condition .= " and FROM_UNIXTIME(crm_client.createtime,'%Y-%m') = '".date("Y-m", strtotime($statdate))."'";
			$count_rs = $this->join("crm_channel")->find($condition, null, "count(crm_client.id) as total");
			return intval($count_rs["total"]);
		}
		
		//来源于我的渠道的到访客户总数
		public function channel_active_visit_count($user_rs, $statdate){
			$condition = "crm_client.visit_time > 0 and crm_client.channel_id > 0 and crm_channel.maintenance_id = {$user_rs["id"]}";
			if($statdate)
				$condition .= " and FROM_UNIXTIME(crm_client.visit_time,'%Y-%m') = '".date("Y-m", strtotime($statdate))."'";
			$count_rs = $this->join("crm_channel")->find($condition, null, "count(crm_client.id) as total");
			return intval($count_rs["total"]);
		}
		
		//来源于我的签约渠道的到访客户总数
		public function channel_sign_visit_count($user_rs, $statdate){
			$condition = "crm_client.visit_time > 0 and crm_client.channel_id > 0 and crm_channel.from_id = {$user_rs["id"]}";
			if($statdate)
				$condition .= " and FROM_UNIXTIME(crm_client.visit_time,'%Y-%m') = '".date("Y-m", strtotime($statdate))."'";
			$count_rs = $this->join("crm_channel")->find($condition, null, "count(crm_client.id) as total");
			return intval($count_rs["total"]);
		}
		
		//来源于我的直接客户成交结果集
		public function client_deal_count($user_rs, $statdate){
			$condition = "crm_client.user_sales_id = ".intval($user_rs["id"])." and crm_client.dealtime > 0 and (crm_client.ispay = 1 or crm_client.ispay = 2)";
			if($statdate)
				$condition .= " and FROM_UNIXTIME(crm_client.dealtime,'%Y-%m') = '".date("Y-m", strtotime($statdate))."'";
			$count_rs = $this->find($condition, null, "count(crm_client.id) as total, sum(crm_client.service_price_standard) as total_standard, sum(crm_client.service_price_real) as total_service");
			return $count_rs;
		}
		
		//来源于我的直接客户成交未到账结果集
		public function client_deal_noaccount_count($user_rs, $statdate){
			$condition = "crm_client.user_sales_id = ".intval($user_rs["id"])." and crm_client.dealtime > 0 and crm_client.ispay = 1";
			$condition .= " and FROM_UNIXTIME(crm_client.dealtime,'%Y-%m') = '".date("Y-m", strtotime($statdate))."'";
			$count_rs = $this->find($condition, null, "count(crm_client.id) as total, sum(crm_client.service_price_standard) as total_standard, sum(crm_client.service_price_real) as total_service");
			return $count_rs;
		}
		
		//来源于我的直接客户成交到账结果集
		public function client_deal_account_count($user_rs, $statdate){
			$condition = "crm_client.user_sales_id = ".intval($user_rs["id"])." and crm_client.dealtime > 0 and crm_client.ispay = 2";
			$condition .= " and FROM_UNIXTIME(crm_client.fullpay_arrivaltime,'%Y-%m') = '".date("Y-m", strtotime($statdate))."'";
			$count_rs = $this->find($condition, null, "count(crm_client.id) as total, sum(crm_client.service_price_standard) as total_standard, sum(crm_client.service_price_real) as total_service");
			return $count_rs;
		}
		
		//来源于我的电话邀约成交结果集
		public function client_deal_account_origin_call($user_rs, $statdate, $info = 0){
			$condition = "crm_client.origin_id = 2 and crm_client.dealtime > 0 and crm_client.ispay = 2 and crm_client.user_teler_id = ".intval($user_rs["id"]);
			$condition .= " and FROM_UNIXTIME(crm_client.fullpay_arrivaltime,'%Y-%m') = '".date("Y-m", strtotime($statdate))."'";
			if($info == 1){
				$count_rs = $this->join("crm_origin")->join("crm_country")->findAll($condition, null, "crm_client.*, crm_origin.oname, crm_country.country");
			}else{
				$field = "count(crm_client.id) as total, sum(crm_client.service_price_standard) as total_standard, sum(crm_client.service_price_real) as total_service";
				$count_rs = $this->find($condition, null, $field);
			}
			return $count_rs;
		}
		
		//来源于我的电话邀约来访数
		public function client_origin_call_visit_count($user_rs, $statdate){
			$condition = "crm_client.origin_id = 2 and crm_client.user_teler_id = ".intval($user_rs["id"]);
			$condition .= " and FROM_UNIXTIME(crm_client.visit_time,'%Y-%m') = '".date("Y-m", strtotime($statdate))."'";
			$count_rs = $this->find($condition, null, "count(crm_client.id) as total");
			return intval($count_rs["total"]);
		}
		
		//来源于我的地推邀约成交结果集
		public function client_deal_account_origin_push($user_rs, $statdate, $info = 0){
			$condition = "crm_client.origin_id = 5 and crm_client.dealtime > 0 and crm_client.ispay = 2 and crm_client.user_preader_id = ".intval($user_rs["id"]);
			$condition .= " and FROM_UNIXTIME(crm_client.fullpay_arrivaltime,'%Y-%m') = '".date("Y-m", strtotime($statdate))."'";
			if($info == 1){
				$count_rs = $this->findAll($condition, null);
			}else{
				$field = "count(crm_client.id) as total, sum(crm_client.service_price_standard) as total_standard, sum(crm_client.service_price_real) as total_service";
				$count_rs = $this->find($condition, null, $field);
			}
			return $count_rs;
		}
		
		//来源于我的地推邀约来访数
		public function client_origin_push_visit_count($user_rs, $statdate){
			$condition = "crm_client.origin_id = 5 and crm_client.user_preader_id = ".intval($user_rs["id"]);
			$condition .= " and FROM_UNIXTIME(crm_client.visit_time,'%Y-%m') = '".date("Y-m", strtotime($statdate))."'";
			$count_rs = $this->find($condition, null, "count(crm_client.id) as total");
			return intval($count_rs["total"]);
		}
		
		//来源于我的渠道成交结果集
		public function client_deal_account_origin_channel_all($user_rs, $statdate, $info = 0){
			$condition = "(crm_client.origin_id = 11 or crm_client.origin_id = 12) and crm_client.dealtime > 0 and crm_client.ispay = 2 and (crm_channel.maintenance_id = ".intval($user_rs["id"])." or crm_client.user_channel_assign_id = ".intval($user_rs["id"]).")";
			$condition .= " and FROM_UNIXTIME(crm_client.fullpay_arrivaltime,'%Y-%m') = '".date("Y-m", strtotime($statdate))."'";
			if($info == 1){
				$count_rs = $this->join("crm_channel")->findAll($condition, null, "crm_client.*");
			}else{
				$field = "count(crm_client.id) as total, sum(crm_client.service_price_standard) as total_standard, sum(crm_client.service_price_real) as total_service";
				$count_rs = $this->join("crm_channel")->find($condition, null, $field);
			}
			return $count_rs;
		}
		
		//来源于我的渠道直推成交结果集
		public function client_deal_account_origin_channel_recom($user_rs, $statdate, $info = 0){
			$condition = "crm_client.origin_id = 11 and crm_client.dealtime > 0 and crm_client.ispay = 2 and crm_channel.maintenance_id = ".intval($user_rs["id"]);
			$condition .= " and FROM_UNIXTIME(crm_client.fullpay_arrivaltime,'%Y-%m') = '".date("Y-m", strtotime($statdate))."'";
			if($info == 1){
				$count_rs = $this->join("crm_channel")->findAll($condition, null, "crm_client.*");
			}else{
				$field = "count(crm_client.id) as total, sum(crm_client.service_price_standard) as total_standard, sum(crm_client.service_price_real) as total_service";
				$count_rs = $this->join("crm_channel")->find($condition, null, $field);
			}
			return $count_rs;
		}
		
		//来源于我的渠道活动成交结果集
		public function client_deal_account_origin_channel($user_rs, $statdate, $info = 0){
			$condition = "crm_client.origin_id = 12 and crm_client.dealtime > 0 and crm_client.ispay = 2 and crm_channel.maintenance_id = ".intval($user_rs["id"]);
			$condition .= " and FROM_UNIXTIME(crm_client.fullpay_arrivaltime,'%Y-%m') = '".date("Y-m", strtotime($statdate))."'";
			if($info == 1){
				$count_rs = $this->join("crm_channel")->findAll($condition, null, "crm_client.*");
			}else{
				$field = "count(crm_client.id) as total, sum(crm_client.service_price_standard) as total_standard, sum(crm_client.service_price_real) as total_service";
				$count_rs = $this->join("crm_channel")->find($condition, null, $field);
			}
			return $count_rs;
		}
		
		//来源于我的分销公司成交结果集
		public function client_deal_account_origin_trader($user_rs, $statdate, $info = 0){
			$condition = "crm_client.origin_id = 13 and crm_client.dealtime > 0 and crm_client.ispay = 2 and (crm_client.user_trader_id = ".intval($user_rs["id"]) . " or crm_client.user_trader_assign_id = " . intval($user_rs["id"]) . ")";
			$condition .= " and FROM_UNIXTIME(crm_client.fullpay_arrivaltime,'%Y-%m') = '".date("Y-m", strtotime($statdate))."'";
			if($info == 1){
				$count_rs = $this->findAll($condition, null);
			}else{
				$field = "count(crm_client.id) as total, sum(crm_client.service_price_standard) as total_standard, sum(crm_client.service_price_real) as total_service";
				$count_rs = $this->find($condition, null, $field);
			}
			return $count_rs;
		}
		
		//来源于我的分销公司来访数
		public function client_origin_trader_visit_count($user_rs, $statdate){
			$condition = "crm_client.origin_id = 13 and crm_client.user_trader_id = ".intval($user_rs["id"]);
			$condition .= " and FROM_UNIXTIME(crm_client.visit_time,'%Y-%m') = '".date("Y-m", strtotime($statdate))."'";
			$count_rs = $this->find($condition, null, "count(crm_client.id) as total");
			return intval($count_rs["total"]);
		}
		
		//来源于我的介绍结果集
		public function client_deal_account_origin_intro($user_rs, $statdate, $info = 0){
			$condition = "crm_client.origin_id = 14 and crm_client.dealtime > 0 and crm_client.ispay = 2 and crm_client.user_owner_id = ".intval($user_rs["id"]);
			$condition .= " and FROM_UNIXTIME(crm_client.fullpay_arrivaltime,'%Y-%m') = '".date("Y-m", strtotime($statdate))."'";
			if($info == 1){
				$count_rs = $this->findAll($condition, null);
			}else{
				$field = "count(crm_client.id) as total, sum(crm_client.service_price_standard) as total_standard, sum(crm_client.service_price_real) as total_service";
				$count_rs = $this->find($condition, null, $field);
			}
			return $count_rs;
		}
		
		//来源于我的资料成交来源结果集
		public function client_deal_account_datafrom($user_rs, $statdate, $info = 0){
			$condition = "crm_client.origin_id = 2 and crm_client.dealtime > 0 and crm_client.ispay = 2 and crm_client.user_datafrom_id = ".intval($user_rs["id"]);
			$condition .= " and FROM_UNIXTIME(crm_client.fullpay_arrivaltime,'%Y-%m') = '".date("Y-m", strtotime($statdate))."'";
			if($info == 1){
				$count_rs = $this->findAll($condition, null);
			}else{
				$field = "count(crm_client.id) as total, sum(crm_client.service_price_standard) as total_standard, sum(crm_client.service_price_real) as total_service";
				$count_rs = $this->find($condition, null, $field);
			}
			return $count_rs;
		}
		
		
		//来源于电话邀约成交结果集
		public function sale_client_deal_account_origin_call($user_rs, $statdate, $info = 0){
			$condition = "crm_client.origin_id = 2 and crm_client.dealtime > 0 and crm_client.ispay = 2 and crm_client.user_sales_id = ".intval($user_rs["id"]);
			$condition .= " and FROM_UNIXTIME(crm_client.fullpay_arrivaltime,'%Y-%m') = '".date("Y-m", strtotime($statdate))."'";
			if($info == 1){
				$count_rs = $this->join("crm_origin")->join("crm_country")->findAll($condition, null, "crm_client.*, crm_origin.oname, crm_country.country");
			}else{
				$field = "count(crm_client.id) as total, sum(crm_client.service_price_standard) as total_standard, sum(crm_client.service_price_real) as total_service";
				$count_rs = $this->find($condition, null, $field);
			}
			return $count_rs;
		}
		
		//来源于地推邀约成交结果集
		public function sale_client_deal_account_origin_push($user_rs, $statdate, $info = 0){
			$condition = "crm_client.origin_id = 5 and crm_client.dealtime > 0 and crm_client.ispay = 2 and crm_client.user_sales_id = ".intval($user_rs["id"]);
			$condition .= " and FROM_UNIXTIME(crm_client.fullpay_arrivaltime,'%Y-%m') = '".date("Y-m", strtotime($statdate))."'";
			if($info == 1){
				$count_rs = $this->join("crm_origin")->join("crm_country")->findAll($condition, null, "crm_client.*, crm_origin.oname, crm_country.country");
			}else{
				$field = "count(crm_client.id) as total, sum(crm_client.service_price_standard) as total_standard, sum(crm_client.service_price_real) as total_service";
				$count_rs = $this->find($condition, null, $field);
			}
			return $count_rs;
		}
		
		//来源于同事介绍结果集
		public function sale_client_deal_account_origin_intro($user_rs, $statdate, $info = 0){
			$condition = "crm_client.origin_id = 14 and crm_client.dealtime > 0 and crm_client.ispay = 2 and crm_client.user_sales_id = ".intval($user_rs["id"]);
			$condition .= " and FROM_UNIXTIME(crm_client.fullpay_arrivaltime,'%Y-%m') = '".date("Y-m", strtotime($statdate))."'";
			if($info == 1){
				$count_rs = $this->join("crm_origin")->join("crm_country")->findAll($condition, null, "crm_client.*, crm_origin.oname, crm_country.country");
			}else{
				$field = "count(crm_client.id) as total, sum(crm_client.service_price_standard) as total_standard, sum(crm_client.service_price_real) as total_service";
				$count_rs = $this->find($condition, null, $field);
			}
			return $count_rs;
		}
		
		//来源于渠道成交结果集(可返佣)
		public function sale_client_deal_account_origin_channel_all($user_rs, $statdate, $info = 0, $justdeal = 0){
			$condition = "(crm_client.origin_id = 11 or crm_client.origin_id = 12) and crm_client.dealtime > 0 and crm_client.user_sales_id = ".intval($user_rs["id"]);
			if($justdeal)
				$condition .= " and (crm_client.ispay = 1 or crm_client.ispay = 2)";
			else
				$condition .= " and crm_client.ispay = 2";
			if($statdate)
				$condition .= " and FROM_UNIXTIME(crm_client.fullpay_arrivaltime,'%Y-%m') = '".date("Y-m", strtotime($statdate))."'";
			if($info == 1){
				$count_rs = $this->join("crm_origin")->join("crm_country")->findAll($condition, null, "crm_client.*, crm_origin.oname, crm_country.country");
			}else{
				$field = "count(crm_client.id) as total, sum(crm_client.service_price_standard) as total_standard, sum(crm_client.service_price_real) as total_service";
				$count_rs = $this->find($condition, null, $field);
			}
			return $count_rs;
		}
		
		//来源于渠道活动成交结果集
		public function sale_client_deal_account_origin_channel($user_rs, $statdate, $info = 0){
			$condition = "crm_client.origin_id = 12 and crm_client.dealtime > 0 and crm_client.ispay = 2 and crm_client.user_sales_id = ".intval($user_rs["id"]);
			$condition .= " and FROM_UNIXTIME(crm_client.fullpay_arrivaltime,'%Y-%m') = '".date("Y-m", strtotime($statdate))."'";
			if($info == 1){
				$count_rs = $this->join("crm_origin")->join("crm_country")->findAll($condition, null, "crm_client.*, crm_origin.oname, crm_country.country");
			}else{
				$field = "count(crm_client.id) as total, sum(crm_client.service_price_standard) as total_standard, sum(crm_client.service_price_real) as total_service";
				$count_rs = $this->find($condition, null, $field);
			}
			return $count_rs;
		}
		
		//来源于渠道直推成交结果集
		public function sale_client_deal_account_origin_channel_recom($user_rs, $statdate, $info = 0){
			$condition = "crm_client.origin_id = 11 and crm_client.dealtime > 0 and crm_client.ispay = 2 and crm_client.user_sales_id = ".intval($user_rs["id"]);
			$condition .= " and FROM_UNIXTIME(crm_client.fullpay_arrivaltime,'%Y-%m') = '".date("Y-m", strtotime($statdate))."'";
			if($info == 1){
				$count_rs = $this->join("crm_origin")->join("crm_country")->findAll($condition, null, "crm_client.*, crm_origin.oname, crm_country.country");
			}else{
				$field = "count(crm_client.id) as total, sum(crm_client.service_price_standard) as total_standard, sum(crm_client.service_price_real) as total_service";
				$count_rs = $this->find($condition, null, $field);
			}
			return $count_rs;
		}
		
		//来源于分销公司成交结果集
		public function sale_client_deal_account_origin_trader($user_rs, $statdate, $info = 0){
			$condition = "crm_client.origin_id = 13 and crm_client.dealtime > 0 and crm_client.ispay = 2 and crm_client.user_sales_id = ".intval($user_rs["id"]);
			$condition .= " and FROM_UNIXTIME(crm_client.fullpay_arrivaltime,'%Y-%m') = '".date("Y-m", strtotime($statdate))."'";
			if($info == 1){
				$count_rs = $this->join("crm_origin")->join("crm_country")->findAll($condition, null, "crm_client.*, crm_origin.oname, crm_country.country");
			}else{
				$field = "count(crm_client.id) as total, sum(crm_client.service_price_standard) as total_standard, sum(crm_client.service_price_real) as total_service";
				$count_rs = $this->find($condition, null, $field);
			}
			return $count_rs;
		}
		
		//来源于旅行社结果集
		public function sale_client_deal_account_origin_travel($user_rs, $statdate, $info = 0){
			$condition = "crm_client.origin_id = 15 and crm_client.dealtime > 0 and crm_client.ispay = 2 and crm_client.user_sales_id = ".intval($user_rs["id"]);
			$condition .= " and FROM_UNIXTIME(crm_client.fullpay_arrivaltime,'%Y-%m') = '".date("Y-m", strtotime($statdate))."'";
			if($info == 1){
				$count_rs = $this->join("crm_origin")->join("crm_country")->findAll($condition, null, "crm_client.*, crm_origin.oname, crm_country.country");
			}else{
				$field = "count(crm_client.id) as total, sum(crm_client.service_price_standard) as total_standard, sum(crm_client.service_price_real) as total_service";
				$count_rs = $this->find($condition, null, $field);
			}
			return $count_rs;
		}
		
		//来源于广宣到店结果集
		public function sale_client_deal_account_origin_publicity($user_rs, $statdate, $info = 0){
			$condition = "crm_client.origin_id = 3 and crm_client.dealtime > 0 and crm_client.ispay = 2 and crm_client.user_sales_id = ".intval($user_rs["id"]);
			$condition .= " and FROM_UNIXTIME(crm_client.fullpay_arrivaltime,'%Y-%m') = '".date("Y-m", strtotime($statdate))."'";
			if($info == 1){
				$count_rs = $this->join("crm_origin")->join("crm_country")->findAll($condition, null, "crm_client.*, crm_origin.oname, crm_country.country");
			}else{
				$field = "count(crm_client.id) as total, sum(crm_client.service_price_standard) as total_standard, sum(crm_client.service_price_real) as total_service";
				$count_rs = $this->find($condition, null, $field);
			}
			return $count_rs;
		}
		
		//业绩成单统计
		public function clientstat($user_rs, $statdate){
			$user_id = intval($user_rs["id"]);
			$condition = "crm_client.fullpay_arrivaltime > 0 and crm_client.ispay = 2";
			$condition .= " and (
			crm_client.user_datafrom_id = $user_id 
			or crm_client.user_teler_id = $user_id 
			or crm_client.user_preader_id = $user_id 
			or crm_client.user_owner_id = $user_id 
			or crm_client.user_channel_id = $user_id 
			or crm_client.user_channel_assign_id = $user_id
			or crm_client.user_channel_contact_id = $user_id  
			or crm_client.user_trader_id = $user_id 
			or crm_client.user_trader_assign_id = $user_id 
			or crm_client.user_trader_contact_id = $user_id
			or crm_client.user_sales_id = $user_id 
			or crm_client.user_tours_id = $user_id 
			or crm_client.user_abroad_id = $user_id)";
			$condition .= " and FROM_UNIXTIME(crm_client.fullpay_arrivaltime,'%Y-%m') = '".date("Y-m", strtotime($statdate))."'";
			//$condition .= " and FROM_UNIXTIME(crm_client.fullpay_arrivaltime,'%Y-%m-%d') > '".date("Y-m-20", strtotime($statdate." -1 month"))."' and FROM_UNIXTIME(crm_client.fullpay_arrivaltime,'%Y-%m-%d') <= '".date("Y-m-20", strtotime($statdate))."'";
			if($clientstat_rs = $this->join("crm_country")->join("crm_origin")->findAll($condition, "crm_client.fullpay_arrivaltime asc", "crm_client.*, (crm_client.service_price_standard) as payreal, crm_country.country, crm_origin.oname, crm_origin.data_reward, crm_origin.traderassign_reward, crm_origin.tradercontact_reward, crm_origin.tradermain_reward, crm_origin.channelassign_reward, crm_origin.channelcontact_reward, crm_origin.channelmain_reward, crm_origin.usertours_reward, crm_origin.userabroad_reward, crm_origin.references_reward, crm_origin.teler_reward, crm_origin.preader_reward, crm_origin.sale_reward")){
				$total = 0;
				$pay_total = 0;
				foreach($clientstat_rs as $key => $val){
					$award_rate = 0;
					if($val["user_datafrom_id"] == $user_id){
						$award_rate += $val["data_reward"];
						$clientstat_rs[$key]["awardit"]["datafrom"] = 1;
					}
					if($val["user_owner_id"] == $user_id){
						$award_rate += $val["references_reward"];
						$clientstat_rs[$key]["awardit"]["references"] = 1;
					}
					if($val["user_teler_id"] == $user_id){
						$award_rate += $val["teler_reward"];
						$clientstat_rs[$key]["awardit"]["teler"] = 1;
					}
					if($val["user_preader_id"] == $user_id){
						$award_rate += $val["preader_reward"];
						$clientstat_rs[$key]["awardit"]["preader"] = 1;
					}
					if($val["user_channel_assign_id"] == $user_id){
						$award_rate += $val["channelassign_reward"];
						$clientstat_rs[$key]["awardit"]["channelassign"] = 1;
					}
					if($val["user_channel_contact_id"] == $user_id){
						$award_rate += $val["channelcontact_reward"];
						$clientstat_rs[$key]["awardit"]["channelcontact"] = 1;
					}
					if($val["user_channel_id"] == $user_id){
						$award_rate += $val["channelmain_reward"];
						$clientstat_rs[$key]["awardit"]["channelmain"] = 1;
					}
					if($val["user_trader_assign_id"] == $user_id){
						$award_rate += $val["traderassign_reward"];
						$clientstat_rs[$key]["awardit"]["traderassign"] = 1;
					}
					if($val["user_trader_contact_id"] == $user_id){
						$award_rate += $val["tradercontact_reward"];
						$clientstat_rs[$key]["awardit"]["tradercontact"] = 1;
					}
					if($val["user_trader_id"] == $user_id){
						$award_rate += $val["tradermain_reward"];
						$clientstat_rs[$key]["awardit"]["tradermain"] = 1;
					}
					if($val["user_sales_id"] == $user_id){
						$award_rate += $val["sale_reward"];
						$clientstat_rs[$key]["awardit"]["sale"] = 1;
					}
					if($val["user_abroad_id"] == $user_id){
						$award_rate += $val["userabroad_reward"];
						$clientstat_rs[$key]["awardit"]["userabroad"] = 1;
					}
					if($val["user_tours_id"] == $user_id){
						$award_rate += $val["usertours_reward"];
						$clientstat_rs[$key]["awardit"]["usertours"] = 1;
					}
					$clientstat_rs[$key]["all_award"] = $award_rate;
					$clientstat_rs[$key]["all_pay"] = $val["payreal"] * $award_rate / 10000;
					$total += $clientstat_rs[$key]["all_pay"];
					$pay_total += $val["payreal"];
				}
				$stat_rs["rs"] = $clientstat_rs;
				$stat_rs["total"] = $total;
				$stat_rs["pay_total"] = $pay_total;
			}
			return $stat_rs;
		}
		
		//业绩奖惩成单统计
		public function clientstat_ext($user_rs, $statdate){
			$obj_channel = spClass("channel");
			$user_id = intval($user_rs["id"]);
			$condition = "crm_client.fullpay_arrivaltime > 0 and crm_client.ispay = 2 and crm_client.service_price_standard >= 20000000";
			$condition .= " and (
			crm_client.user_datafrom_id = $user_id 
			or crm_client.user_teler_id = $user_id 
			or crm_client.user_preader_id = $user_id 
			or crm_client.user_owner_id = $user_id 
			or crm_client.user_channel_id = $user_id 
			or crm_client.user_channel_assign_id = $user_id 
			or crm_client.user_trader_id = $user_id 
			or crm_client.user_trader_assign_id = $user_id 
			or crm_client.user_sales_id = $user_id 
			or crm_client.user_tours_id = $user_id 
			or crm_client.user_abroad_id = $user_id)";
			$condition .= " and FROM_UNIXTIME(crm_client.fullpay_arrivaltime,'%Y-%m') = '".date("Y-m", strtotime($statdate))."'";
			if($clientstat_rs = $this->join("crm_country")->join("crm_origin")->findAll($condition, "crm_client.fullpay_arrivaltime asc", "crm_client.*, (crm_client.service_price_standard) as payreal, crm_country.country, crm_origin.oname")){
				$total_get_price = 0;
				foreach($clientstat_rs as $key => $val){
					$ext_total = array("total_per"=>0, "total_price"=>0, "total_get_price"=>0); //额外分成总金额比例（万分之）、金额和获得的总额
					$per = array(
						"sale" => 0, //销售分成
						"from" => 0, //资料来源分成
						"userabroad" => 0, //海外负责人分成
						"usertours" => 0, //跟团销售分成
						"preader" => 0, //地推分成
						"channel" => 0, //渠道维护分成
						"channel_assign" => 0, //渠道签约分成
						"trader" => 0, //分销维护分成
						"trader_assign" => 0, //分销签约分成
						"colleague" => 0, //同事介绍分成
						"total" => 0 //获得的总比例额
					);
					switch($val["payreal"]){
						case $val["payreal"] >= 20000000 && $val["payreal"] < 50000000:
							$ext_total["total_per"] = 20;
							$ext_total["total_price"] = $val["payreal"] * $ext_total["total_per"] / 10000;
						break;
						case $val["payreal"] >= 50000000 && $val["payreal"] < 100000000:
							$ext_total["total_per"] = 40;
							$ext_total["total_price"] = $val["payreal"] * $ext_total["total_per"] / 10000;
						break;
						case $val["payreal"] >= 100000000:
							$ext_total["total_per"] = 70;
							$ext_total["total_price"] = $val["payreal"] * $ext_total["total_per"] / 10000;
						break;
					}
					switch($val["origin_id"]){
						case "2": //电话邀约
							$per["userabroad"] = 10;
							$per["usertours"] = 10;
							$per["sale"] = 60;
							$per["from"] = 20;
						break;
						case "3": //广宣到店无奖励
							
						break;
						case "5": //地推邀约
							$per["userabroad"] = 10;
							$per["usertours"] = 10;
							$per["preader"] = 20;
							$per["sale"] = 60;
						break;
						case "11":
						case "12":
							$per["userabroad"] = 10;
							$per["usertours"] = 10;
							if($val["user_channel_id"] == $val["user_channel_assign_id"]){
								$per["channel"] = 40;
								$per["sale"] = 40;
							}else{
								$per["channel"] = 26.66;
								$per["channel_assign"] = 26.66;
								$per["sale"] = 26.66;
							}
						break;
						case "13":
							$per["userabroad"] = 10;
							$per["usertours"] = 10;
							if($val["user_trader_id"] == $val["user_trader_assign_id"]){
								$per["trader"] = 40;
								$per["sale"] = 40;
							}else{
								$per["trader"] = 26.66;
								$per["trader_assign"] = 26.66;
								$per["sale"] = 26.66;
							}
						break;
						case "14":
							$per["userabroad"] = 10;
							$per["usertours"] = 10;
							$per["sale"] = 40;
							$per["colleague"] = 40;
						break;
						case "15":
							$per["userabroad"] = 10;
							$per["usertours"] = 10;
							$per["sale"] = 80;
						break;
					}
					if($val["user_datafrom_id"] == $user_id){
						$per["total"] += $per["from"];
						$clientstat_rs[$key]["awardit"]["from"] = 1;
					}
					if($val["user_owner_id"] == $user_id){
						$per["total"] += $per["colleague"];
						$clientstat_rs[$key]["awardit"]["colleague"] = 1;
					}
					if($val["user_preader_id"] == $user_id){
						$per["total"] += $per["preader"];
						$clientstat_rs[$key]["awardit"]["preader"] = 1;
					}
					if($val["user_channel_assign_id"] == $user_id){
						$per["total"] += $per["channel_assign"];
						$clientstat_rs[$key]["awardit"]["channel_assign"] = 1;
					}
					if($val["user_channel_id"] == $user_id){
						$per["total"] += $per["channel"];
						$clientstat_rs[$key]["awardit"]["channel"] = 1;
					}
					if($val["user_trader_assign_id"] == $user_id){
						$per["total"] += $per["trader_assign"];
						$clientstat_rs[$key]["awardit"]["trader_assign"] = 1;
					}
					if($val["user_trader_id"] == $user_id){
						$per["total"] += $per["trader"];
						$clientstat_rs[$key]["awardit"]["trader"] = 1;
					}
					if($val["user_sales_id"] == $user_id){
						$per["total"] += $per["sale"];
						$clientstat_rs[$key]["awardit"]["sale"] = 1;
					}
					if($val["user_abroad_id"] == $user_id){
						$per["total"] += $per["userabroad"];
						$clientstat_rs[$key]["awardit"]["userabroad"] = 1;
					}
					if($val["user_tours_id"] == $user_id){
						$per["total"] += $per["usertours"];
						$clientstat_rs[$key]["awardit"]["usertours"] = 1;
					}
					$ext_total["total_get_price"] = intval($ext_total["total_price"] * $per["total"] / 100);
					$total_get_price += $ext_total["total_get_price"];
					$clientstat_rs[$key]["per"] = $per;
					$clientstat_rs[$key]["ext_total"] = $ext_total;
				}
				$stat_rs["total_get_price"] = $total_get_price;
				$stat_rs["rs"] = $clientstat_rs;
			}
			return $stat_rs;
		}
		
		//业绩成单人员明细统计
		public function clientuserstat($date, $pager, $search){
			$obj_user = spClass("user");
			$condition = "crm_client.fullpay_arrivaltime > 0 and crm_client.ispay = 2";
			if($startdate = $date["starttime"])
				$condition .= " and FROM_UNIXTIME(crm_client.fullpay_arrivaltime,'%Y-%m') >= '".date("Y-m", strtotime($startdate))."'";
			if($enddate = $date["endtime"])
				$condition .= " and FROM_UNIXTIME(crm_client.fullpay_arrivaltime,'%Y-%m') <= '".date("Y-m", strtotime($enddate))."'";
			$page = intval(max($pager['page'], 1));
			$pagesize = intval($pager['pagesize']);
			if($search["searchkey"])
				$condition .= " and (crm_client.realname like '%$search[searchkey]%' or crm_client.cred_license like '%$search[searchkey]%')";
			if($relate_id = intval($search["relate_id"])){
				$condition .= " and (
					crm_client.user_datafrom_id = $relate_id
					or crm_client.user_owner_id = $relate_id
					or crm_client.user_teler_id = $relate_id
					or crm_client.user_preader_id = $relate_id
					or crm_client.user_channel_assign_id = $relate_id
					or crm_client.user_channel_contact_id = $relate_id
					or crm_client.user_channel_id = $relate_id
					or crm_client.user_trader_assign_id = $relate_id
					or crm_client.user_trader_contact_id = $relate_id
					or crm_client.user_trader_id = $relate_id
					or crm_client.user_sales_id = $relate_id
					or crm_client.user_abroad_id = $relate_id
					or crm_client.user_tours_id = $relate_id
				)";
			}
			if($clientstat_rs = $this->join("crm_country")->join("crm_origin")->join("crm_credential")->spPager($page, $pagesize)->findAll($condition, "crm_client.fullpay_arrivaltime asc", "crm_client.*, (crm_client.service_price_standard) as payreal, crm_country.country as country_name, crm_origin.oname, crm_credential.cname as cred_name")){
				foreach($clientstat_rs as $key => $val){
					if($val["user_datafrom_id"]){
						$clientstat_rs[$key]["user_datafrom_rs"] = $obj_user->getUserById($val["user_datafrom_id"], "realname");
					}
					if($val["user_owner_id"]){
						$clientstat_rs[$key]["user_owner_rs"] = $obj_user->getUserById($val["user_owner_id"], "realname");
					}
					if($val["user_teler_id"]){
						$clientstat_rs[$key]["user_teler_rs"] = $obj_user->getUserById($val["user_teler_id"], "realname");
					}
					if($val["user_preader_id"]){
						$clientstat_rs[$key]["user_preader_rs"] = $obj_user->getUserById($val["user_preader_id"], "realname");
					}
					if($val["user_channel_assign_id"]){
						$clientstat_rs[$key]["user_channel_assign_rs"] = $obj_user->getUserById($val["user_channel_assign_id"], "realname");
					}
					if($val["user_channel_contact_id"]){
						$clientstat_rs[$key]["user_channel_contact_rs"] = $obj_user->getUserById($val["user_channel_contact_id"], "realname");
					}
					if($val["user_channel_id"]){
						$clientstat_rs[$key]["user_channel_rs"] = $obj_user->getUserById($val["user_channel_id"], "realname");
					}
					if($val["user_trader_assign_id"]){
						$clientstat_rs[$key]["user_trader_assign_rs"] = $obj_user->getUserById($val["user_trader_assign_id"], "realname");
					}
					if($val["user_trader_contact_id"]){
						$clientstat_rs[$key]["user_trader_contact_rs"] = $obj_user->getUserById($val["user_trader_contact_id"], "realname");
					}
					if($val["user_trader_id"]){
						$clientstat_rs[$key]["user_trader_rs"] = $obj_user->getUserById($val["user_trader_id"], "realname");
					}
					if($val["user_sales_id"]){
						$clientstat_rs[$key]["user_sales_rs"] = $obj_user->getUserById($val["user_sales_id"], "realname");
					}
					if($val["user_abroad_id"]){
						$clientstat_rs[$key]["user_abroad_rs"] = $obj_user->getUserById($val["user_abroad_id"], "realname");
					}
					if($val["user_tours_id"]){
						$clientstat_rs[$key]["user_tours_rs"] = $obj_user->getUserById($val["user_tours_id"], "realname");
					}
					$clientstat_rs[$key]['realname'] = str_replace($search[searchkey], "<em>$search[searchkey]</em>", $val['realname']);
					$clientstat_rs[$key]['cred_license'] = str_replace($search[searchkey], "<em>$search[searchkey]</em>", $val['cred_license']);
				}
			}
			return $clientstat_rs;
		}
		
		//统计客户来源数据
		public function statperfer_data_origin($user_rs, $statdate){
			$result = array();
			$result["client_call_deal"] = $this->client_deal_account_origin_call($user_rs, $statdate);
			$result["client_call_visit"]["total"] = $this->client_origin_call_visit_count($user_rs, $statdate);
			$result["client_push_deal"] = $this->client_deal_account_origin_push($user_rs, $statdate);
			$result["client_push_visit"]["total"] = $this->client_origin_push_visit_count($user_rs, $statdate);
			$result["client_trader_deal"] = $this->client_deal_account_origin_trader($user_rs, $statdate);
			$result["client_trader_visit"]["total"] = $this->client_origin_trader_visit_count($user_rs, $statdate);
			$result["client_intro_deal"] = $this->client_deal_account_origin_intro($user_rs, $statdate);
			$result["client_datafrom_deal"] = $this->client_deal_account_datafrom($user_rs, $statdate);
			$result["client_channel_deal"] = $this->client_deal_account_origin_channel_all($user_rs, $statdate);
			$result["sale_client_call_deal"] = $this->sale_client_deal_account_origin_call($user_rs, $statdate);
			$result["sale_client_push_deal"] = $this->sale_client_deal_account_origin_push($user_rs, $statdate);
			$result["sale_client_trader_deal"] = $this->sale_client_deal_account_origin_trader($user_rs, $statdate);
			$result["sale_client_intro_deal"] = $this->sale_client_deal_account_origin_intro($user_rs, $statdate);
			$result["sale_client_channel_deal"] = $this->sale_client_deal_account_origin_channel($user_rs, $statdate);
			$result["sale_client_channel_recom_deal"] = $this->sale_client_deal_account_origin_channel_recom($user_rs, $statdate);
			$result["sale_client_travel_deal"] = $this->sale_client_deal_account_origin_travel($user_rs, $statdate);
			$result["sale_client_publicity_deal"] = $this->sale_client_deal_account_origin_publicity($user_rs, $statdate);
			return $result;
		}
		
		//统计合力成单数据
		public function origin_together_analysis($user_rs, $statdate){
			$together_total = 0;
			$depart_cooperation = array(2,3);
			if(!in_array($user_rs["depart_id"], $depart_cooperation))
				return false;
			switch($user_rs["depart_id"]){
				case "2":
					$condition = "(crm_client.user_channel_id = $user_rs[id] or crm_client.user_trader_id = $user_rs[id])";
				break;
				case "3":
					$condition = "crm_client.user_sales_id = $user_rs[id]";
				break;
				default:
					return false;
				break;
			}
			$condition .= " and (crm_client.user_channel_id > 0 or crm_client.user_trader_id > 0) and crm_client.user_sales_id > 0";
			$condition .= " and FROM_UNIXTIME(crm_client.fullpay_arrivaltime,'%Y-%m') = '".date("Y-m", strtotime($statdate))."'";
			$from_str = "IF(crm_client.user_channel_id > 0, crm_client.user_channel_id, crm_client.user_trader_id)";
			$client_rs = $this->join("crm_user as from_user", "from_user.id = $from_str")->join("crm_user", "crm_user.id = crm_client.user_sales_id")->findAll($condition, null, "crm_client.id, crm_client.user_channel_id, crm_client.user_trader_id, crm_client.user_sales_id, crm_client.service_price_standard, crm_client.service_price_standard, crm_user.depart_id as sale_depart_id, $from_str as from_id, from_user.depart_id as from_depart_id");
			foreach($client_rs as $key => $val){
				if($val["from_depart_id"] != $val["sale_depart_id"] && in_array($val["from_depart_id"], $depart_cooperation) && in_array($val["sale_depart_id"], $depart_cooperation)){
					$together_total += intval($val["service_price_standard"]);
				}
			}
			return $together_total;
		}
		
		//统计总成单数据
		public function origin_total_analysis($user_rs, $statdate){
			$total = 0;
			$depart_cooperation = array(2,3);
			if(!in_array($user_rs["depart_id"], $depart_cooperation))
				return false;
			$condition = "(crm_client.user_sales_id = $user_rs[id] or crm_client.user_channel_id = $user_rs[id] or crm_client.user_trader_id = $user_rs[id])";
			$condition .= " and FROM_UNIXTIME(crm_client.fullpay_arrivaltime,'%Y-%m') = '".date("Y-m", strtotime($statdate))."'";
			$client_rs = $this->find($condition, null, "sum(service_price_standard) as total");
			$total = intval($client_rs["total"]);
			return $total;
		}
		
		//分析成单来源
		public function origin_analysis($user_rs, $statdate){
			$result = array("together_total"=>0, "self_total"=>0, "total"=>0);
			$result["together_total"] = $this->origin_together_analysis($user_rs, $statdate);
			$result["total"] = $this->origin_total_analysis($user_rs, $statdate);
			$result["self_total"] = $result["total"] - $result["together_total"];
			return $result;
		}
		
		//统计渠道客户数据
		public function channel_analysis($statdate, $ext_condition){
			$obj_channel = spClass('channel');
			$condition = "crm_client.channel_id > 0";
			$condition .= " and FROM_UNIXTIME(crm_client.fullpay_arrivaltime,'%Y-%m') = '".date("Y-m", strtotime($statdate))."'";
			$pay = array();
			if($client_pay_rs = $this->join("crm_channel", "crm_channel.id = crm_client.channel_id")->findAll($condition, null, "crm_client.id, crm_client.channel_id, crm_client.service_price_standard, crm_client.origin_id, crm_channel.typeid, crm_channel.type2id")){
				foreach($client_pay_rs as $key => $val){
					if($val["typeid"] == 1){ //同业
						if($val["type2id"] == 3){ //同业一级代理
							$rebate = 300;
						}elseif($val["type2id"] == 4){ //同业二级代理
							$rebate = 250;
						}
					}elseif($val["typeid"] == 2){ //异业
						if($val["origin_id"] == 16){ //直推直接成单
							$rebate = 300;
						}elseif($val["origin_id"] == 11){ //直推公司介入 
							$ext_condition = "crm_client.channel_id = ".$val["channel_id"] . " and crm_client.origin_id = 11 and crm_client.fullpay_arrivaltime > 0";
							$sortTable = "SELECT (@i:=@i+1) as sortid, crm_client.id, crm_client.realname FROM `crm_client`,(select @i:=0) as it WHERE " . $ext_condition . " order by crm_client.fullpay_arrivaltime asc";
							$sql = "select sortid from ($sortTable) where id = $val[id]";
							$sort_rs = $this->findSql($sql);
							$rebate = ($sort_rs["sortid"] >= 3) ? 150 : 100;
						}elseif($val["origin_id"] == 12){ //渠道活动到访
							$ext_condition = "crm_client.channel_id = ".$val["channel_id"] . " and crm_client.origin_id = 12 and crm_client.fullpay_arrivaltime > 0";
							$sortTable = "SELECT (@i:=@i+1) as sortid, crm_client.id, crm_client.realname FROM `crm_client`,(select @i:=0) as it WHERE " . $ext_condition . " order by crm_client.fullpay_arrivaltime asc";
							$sql = "select sortid from ($sortTable) where id = $val[id]";
							$sort_rs = $this->findSql($sql);
							$rebate = ($sort_rs["sortid"] >= 5) ? 200 : 150;
						}
					}
					$pay[$val[channel_id]]["total"] += $val["service_price_standard"];
					$pay[$val[channel_id]]["re_total"] += $val["service_price_standard"] * $rebate / 10000;
					$pay[$val[channel_id]]["count"]++;
				}
			}
			$condition = "crm_client.channel_id > 0";
			$condition .= " and FROM_UNIXTIME(crm_client.visit_time,'%Y-%m') = '".date("Y-m", strtotime($statdate))."'";
			$visit = array();
			if($client_visit_rs = $this->findAll($condition, null, "crm_client.channel_id, count(crm_client.id) as visit_count", "crm_client.channel_id")){
				foreach($client_visit_rs as $key => $val){
					$visit[$val[channel_id]]["total"] = $val["visit_count"];
				}
			}
			$condition_agen = "ishide = 0";
			if($ext_condition)
				$condition_agen .= " and $ext_condition";
			if($agency_rs = $obj_channel->findAll($condition_agen, null, "crm_channel.id, crm_channel.mechanism as agency")){
				foreach($agency_rs as $key => $val){
					$agency_rs[$key]["pay_rs"] = $pay[$val["id"]];
					$agency_rs[$key]["visit_rs"] = $visit[$val["id"]];
				}
			}
			return $agency_rs;
		}
		
		//统计渠道客户来访数据
		public function channel_visit_analysis($statdate, $id){
			$condition = "crm_client.channel_id = $id";
			$condition .= " and FROM_UNIXTIME(crm_client.visit_time,'%Y-%m') = '".date("Y-m", strtotime($statdate))."'";
			$visit_rs = $this->join("crm_user", "crm_user.id = crm_client.user_sales_id", "left")->findAll($condition, "crm_client.visit_time desc", "crm_client.*, crm_user.realname as sale_name");
			return $visit_rs;
		}
		
		//统计渠道签单明细
		public function channel_order_analysis($statdate, $id){
			$condition = "crm_client.channel_id = $id";
			$condition .= " and FROM_UNIXTIME(crm_client.fullpay_arrivaltime, '%Y-%m') = '".date("Y-m", strtotime($statdate))."'";
			$order_rs = $this->join("crm_origin")->join("crm_country")->join("crm_user", "crm_user.id = crm_client.user_sales_id", "left")->findAll($condition, "crm_client.fullpay_arrivaltime desc", "crm_client.*, crm_client.channel_rebate as rebate, crm_user.realname as sale_name, crm_origin.oname, crm_country.country");
			return $order_rs;
		}
		
		//统计分销商客户数据
		public function trader_analysis($statdate, $ext_condition){
			$obj_trader = spClass('trader');
			$condition = "crm_client.trader_id > 0";
			$condition .= " and FROM_UNIXTIME(crm_client.fullpay_arrivaltime,'%Y-%m') = '".date("Y-m", strtotime($statdate))."'";
			$pay = array();
			if($client_pay_rs = $this->findAll($condition, null, "crm_client.id, crm_client.trader_id, crm_client.service_price_standard, crm_client.trader_rebate")){
				foreach($client_pay_rs as $key => $val){
					$pay[$val[trader_id]]["total"] += $val["service_price_standard"];
					$pay[$val[trader_id]]["re_total"] += $val["service_price_standard"] * $val["trader_rebate"] / 10000;
					$pay[$val[trader_id]]["count"]++;
				}
			}
			$condition = "crm_client.trader_id > 0";
			$condition .= " and FROM_UNIXTIME(crm_client.visit_time,'%Y-%m') = '".date("Y-m", strtotime($statdate))."'";
			$visit = array();
			if($client_visit_rs = $this->findAll($condition, null, "crm_client.trader_id, count(crm_client.id) as visit_count", "crm_client.trader_id")){
				foreach($client_visit_rs as $key => $val){
					$visit[$val[trader_id]]["total"] = $val["visit_count"];
				}
			}
			$condition_agen = "ishide = 0";
			if($ext_condition)
				$condition_agen .= " and $ext_condition";
			if($agency_rs = $obj_trader->findAll($condition_agen, null, "crm_trader.id, crm_trader.tradername as agency")){
				foreach($agency_rs as $key => $val){
					$agency_rs[$key]["pay_rs"] = $pay[$val["id"]];
					$agency_rs[$key]["visit_rs"] = $visit[$val["id"]];
				}
			}
			return $agency_rs;
		}
		
		//统计分销商客户来访数据
		public function trader_visit_analysis($statdate, $id){
			$condition = "crm_client.trader_id = $id";
			$condition .= " and FROM_UNIXTIME(crm_client.visit_time,'%Y-%m') = '".date("Y-m", strtotime($statdate))."'";
			$visit_rs = $this->join("crm_user", "crm_user.id = crm_client.user_sales_id", "left")->findAll($condition, "crm_client.visit_time desc", "crm_client.*, crm_user.realname as sale_name");
			return $visit_rs;
		}
		
		//统计分销商签单明细
		public function trader_order_analysis($statdate, $id){
			$condition = "crm_client.trader_id = $id";
			$condition .= " and FROM_UNIXTIME(crm_client.fullpay_arrivaltime, '%Y-%m') = '".date("Y-m", strtotime($statdate))."'";
			$order_rs = $this->join("crm_origin")->join("crm_country")->join("crm_user", "crm_user.id = crm_client.user_sales_id", "left")->findAll($condition, "crm_client.fullpay_arrivaltime desc", "crm_client.*, crm_client.trader_rebate as rebate, crm_user.realname as sale_name, crm_origin.oname, crm_country.country");
			return $order_rs;
		}
		
		//统计旅行社客户数据
		public function travel_analysis($statdate, $ext_condition){
			$obj_travel = spClass('travel');
			$condition = "crm_client.travel_id > 0";
			$condition .= " and FROM_UNIXTIME(crm_client.fullpay_arrivaltime,'%Y-%m') = '".date("Y-m", strtotime($statdate))."'";
			$pay = array();
			if($client_pay_rs = $this->findAll($condition, null, "crm_client.id, crm_client.travel_id, crm_client.service_price_standard, crm_client.travel_rebate")){
				foreach($client_pay_rs as $key => $val){
					$pay[$val[travel_id]]["total"] += $val["service_price_standard"];
					$pay[$val[travel_id]]["re_total"] += $val["service_price_standard"] * $val["travel_rebate"] / 10000;
					$pay[$val[travel_id]]["count"]++;
				}
			}
			$condition = "crm_client.travel_id > 0";
			$condition .= " and FROM_UNIXTIME(crm_client.visit_time,'%Y-%m') = '".date("Y-m", strtotime($statdate))."'";
			$visit = array();
			if($client_visit_rs = $this->findAll($condition, null, "crm_client.travel_id, count(crm_client.id) as visit_count", "crm_client.travel_id")){
				foreach($client_visit_rs as $key => $val){
					$visit[$val[travel_id]]["total"] = $val["visit_count"];
				}
			}
			$condition_agen = "ishide = 0";
			if($ext_condition)
				$condition_agen .= " and $ext_condition";
			if($agency_rs = $obj_travel->findAll($condition_agen, null, "crm_travel.id, crm_travel.travelname as agency")){
				foreach($agency_rs as $key => $val){
					$agency_rs[$key]["pay_rs"] = $pay[$val["id"]];
					$agency_rs[$key]["visit_rs"] = $visit[$val["id"]];
				}
			}
			return $agency_rs;
		}
		
		//统计旅行社客户来访数据
		public function travel_visit_analysis($statdate, $id){
			$condition = "crm_client.travel_id = $id";
			$condition .= " and FROM_UNIXTIME(crm_client.visit_time,'%Y-%m') = '".date("Y-m", strtotime($statdate))."'";
			$visit_rs = $this->join("crm_user", "crm_user.id = crm_client.user_sales_id", "left")->findAll($condition, "crm_client.visit_time desc", "crm_client.*, crm_user.realname as sale_name");
			return $visit_rs;
		}
		
		//统计旅行社签单明细
		public function travel_order_analysis($statdate, $id){
			$condition = "crm_client.travel_id = $id";
			$condition .= " and FROM_UNIXTIME(crm_client.fullpay_arrivaltime, '%Y-%m') = '".date("Y-m", strtotime($statdate))."'";
			$order_rs = $this->join("crm_origin")->join("crm_country")->join("crm_user", "crm_user.id = crm_client.user_sales_id", "left")->findAll($condition, "crm_client.fullpay_arrivaltime desc", "crm_client.*, crm_client.travel_rebate as rebate, crm_user.realname as sale_name, crm_origin.oname, crm_country.country");
			return $order_rs;
		}
		
		//更新客户动态时间，防止超时(超过xx天自动转为无意向)
		public function overtime(){
			/*
			 if(strtolower($_SERVER['HTTP_HOST']) !== "localhost")
				return false;
			*/
			global $app_config;
			$overdate = $app_config["client_overdate"];
			$data = array();
			$data["isoverdate"] = 1;
			$data["overdatetime"] = time();
			$data["overdatereason"] = "[系统]超过 " . $overdate . " 天未成单客户";
			if($over_rs = $this->findAll("is_protocol = 0 and isoverdate = 0 and datediff(curdate(), FROM_UNIXTIME(overdatestart, '%Y-%m-%d')) > $overdate", null, "id, user_sales_id, realname, sex, telphone, overdatestart")){
				foreach($over_rs as $val){
					spClass("user_notice")->send_notice($val["user_sales_id"], "客户 ".$val[realname]."[" . $val[telphone] . "] 因过期而被取消", "客户 ".$val[realname]."[" . $val[telphone] . "]{".$val[id]."} 由您跟进的开始时间是 " . date("Y-m-d H:i", $val[overdatestart]) . ", 于 " . date("Y-m-d H:i", $data["overdatetime"]) . " 因 " . $overdate . " 天无成交而过期", 1);
					$this->update(array("id"=>$val["id"]), $data);
				}
			}
		}
		
		//将过期客户分配
		public function overtransfer($client_rs, $toid){
			$data = array();
			$data["user_sales_id"] = $toid;
			$data["overdatestart"] = time();
			$data["isoverdate"] = 0;
			$obj_user = spClass("user");
			if(!$sale_rs = $obj_user->getDepartUserinfo(3, $toid))
				throw new Exception("找不到该被分配人，分配失败");
			$identity_array = $obj_user->getidentity_array($data["transfer"], $sale_rs);
			if(!$identity_array["getclient"])
				throw new Exception("该员工无法接触客户，分配失败");
			if(!$this->update(array("id"=>$client_rs["id"]), $data))
				throw new Exception("未知错误，分配失败");
			spClass("user_notice")->send_notice($toid, "过期客户 ".$client_rs[realname]." 被分配到置业顾问", "过期客户 ".$client_rs[realname]." 被 " . $_SESSION["sscrm_user"]["realname"] . " 分配给您");
			spClass('user_log')->save_log(3, "将过期客户 ".$client_rs["realname"]." [id:".$client_rs["id"]."] 分配给了 ".$sale_rs[realname]." [id:".$sale_rs["id"]."]", array("id"=>$client_rs["id"], "transfer"=>$toid));
		}
		
		//将过期客户分配
		public function reassigned($client_rs, $toid){
			$data = array();
			$data["user_sales_id"] = $toid;
			$data["overdatestart"] = time();
			$data["isoverdate"] = 0;
			$obj_user = spClass("user");
			if(!$sale_rs = $obj_user->getDepartUserinfo(3, $toid))
				throw new Exception("找不到该被分配人，分配失败");
			if(!$from_rs = $obj_user->getUserById($client_rs["user_sales_id"]))
				throw new Exception("找不到该来源人，分配失败");
			$identity_array = $obj_user->getidentity_array($data["transfer"], $sale_rs);
			if(!$identity_array["getclient"])
				throw new Exception("该员工无法接触客户，分配失败");
			if(!$this->update(array("id"=>$client_rs["id"]), $data))
				throw new Exception("未知错误，分配失败");
			spClass("user_notice")->send_notice($toid, "将客户 ".$client_rs[realname]." 重新分配", "客户 ".$client_rs[realname]." 被 " . $_SESSION["sscrm_user"]["realname"] . " 分配给您");
			spClass('user_log')->save_log(3, "将客户 ".$client_rs["realname"]." [id:".$client_rs["id"]."] 由 ".$from_rs[realname]." [id:".$from_rs["id"]."] 分配给了 ".$sale_rs[realname]." [id:".$sale_rs["id"]."]", array("client_id"=>$client_rs["id"]));
		}
		
		//回访超时,插入记录
		public function record_overtime(){
			$sql = "SELECT * FROM
					(
						SELECT crm_client.id, crm_client.user_sales_id, IF(maxrecord.acttime, maxrecord.acttime, crm_client.createtime) as recordtime FROM crm_client
						LEFT JOIN
						(
							SELECT crm_client_record.client_id, MAX(crm_client_record.acttime) as acttime FROM crm_client_record GROUP BY crm_client_record.client_id
						) as maxrecord
						ON
						maxrecord.client_id = crm_client.id
						where crm_client.is_protocol = 0 and crm_client.isoverdate = 0
						order by crm_client.id asc
					) as client_recordtime
					where client_recordtime.id not in(
						SELECT client_id FROM crm_client_overtime WHERE endtime = 0
					) 
					and 
					client_recordtime.recordtime <=  unix_timestamp(now()) -  60*60*24*3
					";
			if($overtime_rs = $this->findSql($sql)){
				$obj_overtime = spClass("client_overtime");
				foreach($overtime_rs as $val){
					$obj_overtime->addovertime($val);
				}
			}
		}
	}
?>