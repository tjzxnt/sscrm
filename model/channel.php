<?php
	class channel extends spModel{
		var $pk = "id";
		var $table = "channel";
		
		var $join = array(
			'crm_channel_type' => array(
				'mapkey' => 'id',
				'fkey' => 'typeid'
			),
			'crm_channel_level' => array(
				'mapkey' => 'id',
				'fkey' => 'level_id'
			)
		);
		
		var $addrules = array(
			'available_name' => array('channels', 'checkName')
		);
		
		var $validator = array(
			"rules" => array(
				'mechanism' => array(
					'required' => true,
					'minlength' => 2,
					'maxlength' => 40,
					'available_name' => true
				),
				'main_contact' => array(
					'required' => true,
					'minlength' => 2,
					'maxlength' => 20
				),
				'main_tel' => array(
					'required' => true,
					'minlength' => 11,
					'maxlength' => 11
				)
			),
			"messages" => array(
				'mechanism' => array(
					'required' => '机构名不能为空',
					'minlength' => '机构名不能少于2个字符',
					'maxlength' => '机构名不能大于40字符',
					'available_name' => '机构名已经被注册'
				),
				'main_contact' => array(
					'required' => '主联系人姓名不能为空',
					'minlength' => '主联系人姓名在2~10个字符之间',
					'maxlength' => '主联系人姓名在2~10个字符之间',
				),
				'main_tel' => array(
					'required' => '主联系人电话不能为空',
					'minlength' => '请输入11位的主联系人电话',
					'maxlength' => '请输入11位的主联系人电话',
				)
			)
		);

		public function getValidatorForCreateJS() {
			$validator = $this->validator;
			$validator['rules']['mechanism']['remote'] = spUrl("channels", "checkName");
			$validator['messages']['mechanism']['remote'] = $validator['messages']['mechanism']['available_name'];
			$validator['rules']['main_tel']['remote'] = spUrl("channels", "checkTel");
			$validator['messages']['main_tel']['remote'] = "该主联系人电话已经被注册";
			unset($validator['rules']['mechanism']['available_name']);
			unset($validator['messages']['mechanism']['available_name']);
			return parent::getValidatorJS($validator);
		}
		
		public function spValidatorForOPT() {
			unset($this->addrules);
			unset($this->validator['rules']['mechanism']['available_name']);
			unset($this->validator['messages']['mechanism']['available_name']);
			$this->validator['rules']['from_id']["required"] = true;
			$this->validator['messages']['from_id']["required"] = "请选择渠道来源人";
			$this->validator['rules']['maintenance_id']["required"] = true;
			$this->validator['messages']['maintenance_id']["required"] = "请选择渠道维护人";
			$this->validator['rules']['typeid']["required"] = true;
			$this->validator['messages']['typeid']["required"] = "请选择渠道分类";
			return $this;
		}
		
		public function getValidatorForModifyJS($id) {
			$validator = $this->validator;
			$validator['rules']['mechanism']['remote'] = spUrl("channels", "checkName", array("id"=>$id));
			$validator['messages']['mechanism']['remote'] = $validator['messages']['mechanism']['available_name'];
			$validator['rules']['main_tel']['remote'] = spUrl("channels", "checkTel", array("id"=>$id));
			$validator['messages']['main_tel']['remote'] = "该主联系人电话已经被注册";
			unset($validator['rules']['mechanism']['available_name']);
			unset($validator['messages']['mechanism']['available_name']);
			return parent::getValidatorJS($validator);
		}
		
		public function checkName($name, $id){
			try {
				$condition = array("mechanism"=>$name);
				if($id)
					$condition["id <>"] = $id;
				if($this->find($condition))
					throw new Exception("exist");
				return true;
			}catch(Exception $e){
				return false;
			}
		}
		
		public function checkTel($tel, $id){
			try {
				$condition = array("main_tel"=>$tel);
				if($id)
					$condition["id <>"] = $id;
				if($this->find($condition))
					throw new Exception("exist");
				return true;
			}catch(Exception $e){
				return false;
			}
		}
		
		//是否允许该用户设置为指定销售
		public function is_tosale_allow($user_id){
			$obj_user = spClass("user");
			$user_rs = $obj_user->find(array("id"=>$user_id, "isdel"=>0));
			if(!in_array($user_rs["depart_id"], array(2,3)))
				return false;
			return $user_rs;
		}
		
		//获取我的渠道
		public function getMychannel($channel_id){
			return $this->find(array("id"=>$channel_id, "maintenance_id"=>$_SESSION["sscrm_user"]["id"]));
		}
		
		//获取活动的所在的我的渠道
		public function get_act_mychannel($channel_id){
			if(!$channel_id)
				throw new Exception('请先选择渠道，再进入该页面！');
			if(!$channel_rs = $this->getMychannel($channel_id))
				throw new Exception("未知错误，找不到该渠道");
			$obj_type = spClass("channel_type");
			if($channel_rs["typeid"]){
				$channel_rs["type_rs"] = $obj_type->find(array("id"=>$channel_rs["typeid"], "parent_id"=>0));
			}
			return $channel_rs;
		}
		
		//获取活动的所在的我的渠道
		public function get_act_allchannel($channel_id){
			if(!$channel_id)
				throw new Exception('请先选择渠道，再进入该页面！');
			if(!$channel_rs = $this->getChannelById($channel_id))
				throw new Exception("未知错误，找不到该渠道");
			$obj_type = spClass("channel_type");
			if($channel_rs["typeid"]){
				$channel_rs["type_rs"] = $obj_type->find(array("id"=>$channel_rs["typeid"], "parent_id"=>0));
			}
			return $channel_rs;
		}
		
		public function getChannelById($id){
			return $this->join("crm_user as main_user", "main_user.id = crm_channel.maintenance_id")->join("crm_user as user_sale", "user_sale.id = crm_channel.to_sale_id and user_sale.isdel = 0", "left")->find(array("crm_channel.id"=>$id), null, "crm_channel.*, main_user.realname as realname_main, user_sale.realname as realname_sale");
		}
		
		public function getChannelByUser($user_id){
			return $this->findAll(array("create_id"=>$user_id));
		}
		
		public function getlistGroupUser(){
			$obj_user = spClass("user");
			if($group_rs = $obj_user->getUserByDepart(2)){
				foreach($group_rs as $key => $val){
					$group_rs[$key]["channel_rs"] = $this->getChannelByUser($val["id"]);
				}
				return $group_rs;
			}
		}
		
		public function getlist_prep($ext_condition){
			$condition = "ishide = 0 and issign = 1";
			if($ext_condition)
				$condition .= " and $ext_condition";
			return $this->findAll($condition, "py asc", "*, fristPinyin(mechanism) as py");
		}
		
		public function getlistGroupOne($userid){
			$obj_user = spClass("user");
			$key = 0;
			$group_rs[$key] = $obj_user->getCommonUserinfo($userid);
			$group_rs[$key]["channel_rs"] = $this->getChannelByUser($userid);
			return $group_rs;
		}
		
		public function getinfoById($id){
			return $this->find(array("id"=>$id));
		}
		
		public function getname($id){
			$rs = $this->getinfoById($id);
			return $rs["mechanism"];
		}
		
		//更新渠道动态时间，防止超时
		public function updatetime($channel_id, $client_id){
			$this->update(array("id"=>$channel_id), array("saletime"=>time(), "saleid"=>$client_id));
		}
		
		//更新渠道签约数据
		public function updatesign(){
			$obj_sign = spClass("channel_sign");
			$nowdate = date("Y-m-d");
			if($sign_rs = $obj_sign->findAll(array("startdate <="=>$nowdate, "enddate >="=>$nowdate, "isdel"=>0))){
				foreach($sign_rs as $val){
					if($this->find(array("id"=>$val["channel_id"], "sign_acttime <>"=>$val["createtime"]))){
						$data = array();
						$data["issign"] = 1;
						$data["sign_startdate"] = $val["startdate"];
						$data["sign_enddate"] = $val["enddate"];
						$data["sign_date"] = $val["signdate"];
						$data["sign_acttime"] = $val["createtime"];
						$data["sign_subid"] = $val["user_id"];
						$this->update(array("id"=>$val["channel_id"]), $data);
					}
				}
			}
		}
		
		public function getChannel_prep($condition_ext){
			if($condition_ext){
				$condition = is_array($condition_ext) ? array_merge(array("issign"=>1, "ishide"=>0), $condition_ext) : "issign = 1 and ishide = 0 and (".$condition_ext.")";
			}else{
				$condition = array("issign"=>1, "ishide"=>0);
			}
			return $this->findAll($condition, "py asc", "id, mechanism, fristPinyin(mechanism) as py");
		}
		
		public function getAllChannel_prep($condition_ext){
			if($condition_ext){
				$condition = is_array($condition_ext) ? array_merge(array("ishide"=>0), $condition_ext) : "ishide = 0 and (".$condition_ext.")";
			}else{
				$condition = array("ishide"=>0);
			}
			return $this->findAll($condition, "py asc", "id, mechanism, fristPinyin(mechanism) as py, issign");
		}
		
		//将渠道重新分配
		public function resend($channel_rs, $toid){
			$data = array();
			$data["maintenance_id"] = $toid;
			$obj_user = spClass("user");
			if(!$main_rs = $obj_user->getDepartUserinfo(2, $toid))
				throw new Exception("找不到该被分配人，分配失败");
			if(!$from_rs = $obj_user->getUserById($channel_rs["maintenance_id"]))
				throw new Exception("找不到该来源人，分配失败");
			if(!$this->update(array("id"=>$channel_rs["id"]), $data))
				throw new Exception("未知错误，分配失败");
			spClass("user_notice")->send_notice($toid, "将渠道 ".$channel_rs[mechanism]." 重新分配", "渠道 ".$channel_rs[mechanism]." 被 " . $_SESSION["sscrm_user"]["realname"] . " 分配给您");
			spClass('user_log')->save_log(2, "将渠道 ".$channel_rs["mechanism"]." [id:".$channel_rs["id"]."] 由 ".$from_rs[realname]." [id:".$from_rs["id"]."] 分配给了 ".$main_rs[realname]." [id:".$main_rs["id"]."]", array("channel_id"=>$channel_rs["channel_id"]));
		}
		
		//处理超时渠道,暂无用
		public function overtime(){
			/*
			if(strtolower($_SERVER['HTTP_HOST']) !== "localhost")
				return false;
			*/
			return true;
			global $app_config;
			$overdate = $app_config["channel_overdate"];
			$data = array();
			$data["ishide"] = 1;
			$data["hidetime"] = time();
			$data["hidereason"] = "[系统]超过 " . $overdate . " 天未成交客户";
			if($over_rs = $this->findAll("ishide = 0 and IF(saletime > createtime, datediff(curdate(), FROM_UNIXTIME(saletime, '%Y-%m-%d')) > $overdate, datediff(curdate(), FROM_UNIXTIME(createtime, '%Y-%m-%d')) >= $overdate)", null, "id, mechanism, maintenance_id, createtime")){
				foreach($over_rs as $val){
					spClass("user_notice")->send_notice($val["maintenance_id"], "渠道 ".$val[mechanism]." 因过期而被取消", "渠道 ".$val[mechanism]." 的创建时间是 " . date("Y-m-d H:i", $val[createtime]) . ",最近添加客户的时间是 " . ($val[saletime] ? date("Y-m-d H:i", $val[saletime]) : "-") . "，因 " . $overdate . " 天无添加客户而被取消", 1);
					$this->update(array("id"=>$val["id"]), $data);
				}
			}
		}
	}
?>