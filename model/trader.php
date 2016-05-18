<?php
	class trader extends spModel{
		var $pk = "id";
		var $table = "trader";
		
		/*
		var $join = array(
			'crm_user' => array(
				'mapkey' => 'id',
				'fkey' => 'create_id'
			)
		);
		*/
		
		var $addrules = array(
			'available_name' => array('traders', 'checkName')
		);
		
		var $validator = array(
			"rules" => array(
				'tradername' => array(
					'required' => true,
					'minlength' => 4,
					'maxlength' => 40,
					'available_name' => true
				)
			),
			"messages" => array(
				'tradername' => array(
					'required' => '分销商名不能为空',
					'minlength' => '分销商名不能少于4个字符',
					'maxlength' => '分销商名不能大于40字符',
					'available_name' => '分销商名已经被注册'
				)
			)
		);

		public function getValidatorForCreateJS() {
			$validator = $this->validator;
			$validator['rules']['tradername']['remote'] = spUrl("mtraders", "checkName");
			$validator['messages']['tradername']['remote'] = $validator['messages']['tradername']['available_name'];
			unset($validator['rules']['tradername']['available_name']);
			unset($validator['messages']['tradername']['available_name']);
			return parent::getValidatorJS($validator);
		}
		
		public function spValidatorForOPT() {
			unset($this->addrules);
			unset($this->validator['rules']['tradername']['available_name']);
			unset($this->validator['messages']['tradername']['available_name']);
			$this->validator['rules']['from_id']["required"] = true;
			$this->validator['messages']['from_id']["required"] = "请选择分销商来源人";
			$this->validator['rules']['maintenance_id']["required"] = true;
			$this->validator['messages']['maintenance_id']["required"] = "请选择分销商维护人";
			return $this;
		}
		
		public function getValidatorForModifyJS($id) {
			$validator = $this->validator;
			$validator['rules']['tradername']['remote'] = spUrl("traders", "checkName", array("id"=>$id));
			$validator['messages']['tradername']['remote'] = $validator['messages']['tradername']['available_name'];
			unset($validator['rules']['tradername']['available_name']);
			unset($validator['messages']['tradername']['available_name']);
			return parent::getValidatorJS($validator);
		}
		
		public function checkName($name, $id){
			try {
				$condition = array("tradername"=>$name);
				if($id)
					$condition["id <>"] = $id;
				if($this->find($condition))
					throw new Exception("exist");
				return true;
			}catch(Exception $e){
				return false;
			}
		}
		
		//获取我的渠道
		public function getMytrader($trader_id){
			return $this->find(array("id"=>$trader_id, "maintenance_id"=>$_SESSION["sscrm_user"]["id"]));
		}
		
		//获取联系人的所在的我的分销商
		public function get_act_trader($trader_id){
			if(!$trader_id)
				throw new Exception('请先选择分销商，再进入该页面！');
			if(!$trader_rs = $this->getMytrader($trader_id))
				throw new Exception("未知错误，找不到该分销商");
			return $trader_rs;
		}
		
		public function getlist_prep(){
			return $this->findAll(array("ishide"=>0), "py asc", "*, fristPinyin(tradername) as py");
		}
		
		public function getTraderById($id){
			return $this->join("crm_user as main_user", "main_user.id = crm_trader.maintenance_id")->find(array("crm_trader.id"=>$id), null, "crm_trader.*, main_user.realname as realname_main");
		}
		
		public function getTraderByUser($user_id){
			return $this->findAll(array("create_id"=>$user_id));
		}
		
		public function getlistGroupUser(){
			$obj_user = spClass("user");
			if($group_rs = $obj_user->getUserByDepart(2)){
				foreach($group_rs as $key => $val){
					$group_rs[$key]["trader_rs"] = $this->getTraderByUser($val["id"]);
				}
				return $group_rs;
			}
		}
		
		public function getlistGroupOne($userid){
			$obj_user = spClass("user");
			$key = 0;
			$group_rs[$key] = $obj_user->getCommonUserinfo($userid);
			$group_rs[$key]["trader_rs"] = $this->getTraderByUser($userid);
			return $group_rs;
		}
		
		public function getinfoById($id){
			return $this->find(array("id"=>$id));
		}
		
		public function getname($id){
			$rs = $this->getinfoById($id);
			return $rs["tradername"];
		}
	}
?>