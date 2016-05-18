<?php
	class user extends spModel{
		var $pk = "id";
		var $table = "user";
		
		var $join = array(
	 		'crm_department' => array(
	 			'mapkey' => 'id',
	 			'fkey' => 'depart_id'
	 		)
	 	);
	 	
	 	var $addrules = array(
	        'available_username' => array('user', 'checkUsername'),
	        'available_realname' => array('user', 'checkRealname'),
	    );
	    
	    var $validator = array(
			"rules" => array(
				'username' => array(
					'required' => true,
					'minlength' => 5,
					'maxlength' => 40,
					'available_username' => true
				),
				'depart_id' => array(
					'required' => true
				),
				'password' => array(
	                'required' => true,
	                'minlength' => 6
	            ),
	            'password1' => array(
					'required' => true,
					'equalTo' => 'password'
				),
				'realname' => array(
					'required' => true,
					'minlength' => 2,
					'maxlength' => 10,
					'available_realname' => true
				),
			),
			"messages" => array(
				'username' => array(
					'required' => '用户名不能为空',
					'minlength' => '用户名不能少于5个字符',
					'maxlength' => '用户名不能大于40字符',
					'available_username' => '用户名已经被注册'
				),
				'depart_id' => array(
					'required' => '请选择部门'
				),
		        'password' => array(
					'required' => '请填写密码',
					'minlength' => '密码不能少于6位字符'
				),
				'password1' => array(
					'required' => '请填写确认密码',
					'equalTo' => '两次输入的密码不一致'
				),
				'realname' => array(
					'required' => '真实姓名不能为空',
					'minlength' => '真实姓名不能少于2个字符',
					'maxlength' => '真实姓名不能大于10字符',
					'available_realname' => '真实姓名已经被注册'
				),
			)
		);
		
		public function getValidatorForCreateJS() {
			$validator = $this->validator;
			$validator['rules']['username']['remote'] = spUrl("users", "checkUsername");
			$validator['messages']['username']['remote'] = $validator['messages']['username']['available_username'];
			$validator['rules']['realname']['remote'] = spUrl("users", "checkRealname");
			$validator['messages']['realname']['remote'] = $validator['messages']['realname']['available_realname'];
			unset($validator['rules']['username']['available_username']);
			unset($validator['messages']['username']['available_username']);
			unset($validator['rules']['realname']['available_realname']);
			unset($validator['messages']['realname']['available_realname']);
			unset($validator['rules']['depart_id']);
			unset($validator['messages']['depart_id']);
			return parent::getValidatorJS($validator);
		}
		
		public function spValidatorForCreate() {
			unset($this->addrules);
			unset($this->validator['rules']['username']['available_username']);
			unset($this->validator['messages']['username']['available_username']);
			unset($this->validator['rules']['realname']['available_realname']);
			unset($this->validator['messages']['realname']['available_realname']);			
			return $this;
		}
		
	    public function getValidatorForModifyJS($id) {
			$validator = $this->validator;
			$validator['rules']['username']['remote'] = spUrl("users", "checkUsername", array("id"=>$id));
			$validator['messages']['username']['remote'] = $validator['messages']['username']['available_username'];
			$validator['rules']['realname']['remote'] = spUrl("users", "checkRealname", array("id"=>$id));
			$validator['messages']['realname']['remote'] = $validator['messages']['realname']['available_realname'];
			unset($validator['rules']['username']['available_username']);
			unset($validator['messages']['username']['available_username']);
			unset($validator['rules']['realname']['available_realname']);
			unset($validator['messages']['realname']['available_realname']);
			unset($validator['rules']['password']['required']);
			unset($validator['messages']['password']['required']);
			unset($validator['rules']['password1']['required']);
			unset($validator['messages']['password1']['required']);
			unset($validator['rules']['depart_id']);
			unset($validator['messages']['depart_id']);
			return parent::getValidatorJS($validator);
		}
		
		public function getValidatorForModify() {
			unset($this->addrules);
			unset($this->validator['rules']['password']['required']);
			unset($this->validator['messages']['password']['required']);
			unset($this->validator['rules']['password1']['required']);
			unset($this->validator['messages']['password1']['required']);
			unset($this->validator['rules']['username']['available_username']);
			unset($this->validator['messages']['username']['available_username']);
			unset($this->validator['rules']['realname']['available_realname']);
			unset($this->validator['messages']['realname']['available_realname']);
			return $this;
		}
		
		public function spValidatorForMyPwdJS() {
			$validator = $this->validator;
			$temp_validator = $validator;
			unset($validator['rules']);
			unset($validator['messages']);
			$validator['rules']['oralpassword'] = array('required' => true, 'minlength' => 6);
			$validator['messages']['oralpassword'] = array('required' => "请填写原密码", 'minlength' => "原密码长度不能小于6位");
			$validator['rules']['password'] = $temp_validator['rules']['password'];
			$validator['messages']['password'] = $temp_validator['messages']['password'];
			$validator['rules']['password1'] = $temp_validator['rules']['password1'];
			$validator['messages']['password1'] = $temp_validator['messages']['password1'];
			return parent::getValidatorJS($validator);
		}
		
		public function spValidatorForMyPwd() {
			unset($this->addrules);
			$temp_validator = $this->validator;
			unset($this->validator['rules']);
			unset($this->validator['messages']);
			$this->validator['rules']['oralpassword'] = array('required' => true, 'minlength' => 6);
			$this->validator['messages']['oralpassword'] = array('required' => "请填写原密码", 'minlength' => "原密码长度不能小于6位");
			$this->validator['rules']['password'] = $temp_validator['rules']['password'];
			$this->validator['messages']['password'] = $temp_validator['messages']['password'];
			$this->validator['rules']['password1'] = $temp_validator['rules']['password1'];
			$this->validator['messages']['password1'] = $temp_validator['messages']['password1'];
			return $this;
		}
		
		public function checkUsername($name, $id){
			try {
				$condition = array("username"=>$name);
				if($id)
					$condition["id <>"] = $id;
				if($this->find($condition))
					throw new Exception("exist");
				return true;
			}catch(Exception $e){
				return false;
			}
		}
		
		public function checkRealname($name, $id){
			try {
				$condition = array("realname"=>$name);
				if($id)
					$condition["id <>"] = $id;
				if($this->find($condition))
					throw new Exception("exist");
				return true;
			}catch(Exception $e){
				return false;
			}
		}
		
		public function getlist(){
			return $this->findAll(array("isdel"=>0), "createtime asc");
		}
		
		public function getlistGroupDepart($isowndepart, $need_attr_str = ""){
			$obj_depart = spClass("department");
			if($isowndepart)
				$owndepart_group = explode(",", $isowndepart);
			if($result_rs = $obj_depart->getlist_all()){
				foreach ($result_rs as $key => $val){
					$attr_condition = "";
					if($owndepart_group && !in_array($val["id"], $owndepart_group)){
						unlink($result_rs[$key]);
						continue;
					}
					if($need_attr_str)
						$attr_condition = "find_in_set('$need_attr_str', crm_user.identity_attr)";
					$result_rs[$key]["user_rs"] = $this->getUserByDepart($val["id"], $attr_condition);
				}
			}
			return $result_rs;
		}
		
		//根据用户id获取可用账户的信息(用户密码修改)
		public function getAvailUserinfo($id){
			return $this->join("crm_department")->find(array("crm_user.id"=>$id, "crm_user.isdel"=>0, "crm_department.isdel"=>0), null, "crm_user.*, crm_department.dname");
		}
		
		//根据用户id获取非管理员信息(员工管理模块使用)
		public function getCommonUserinfo($id, $datatime){
			$datatime = $datatime ? strtotime($datatime) : time();
			$rs = $this->join("crm_department")->find(array("crm_user.id"=>$id, "crm_department.isadmin"=>0), null, "crm_user.*, crm_department.dname");
			$rs["isqualified"] = ($rs["qualified"] && strtotime($rs["qualifiedtime"]) <= $datatime) ? 1 : 0;
			return $rs;
		}
		
		//根据用户id和部门id获取用户信息(客户添加模块使用)
		public function getDepartUserinfo($depart_id, $id){
			$condition = "crm_user.id = $id and crm_user.isdel = 0 and crm_department.isadmin = 0";
			$condition .= is_array($depart_id) ? " and crm_user.depart_id in(".implode(",", $depart_id).")" : " and crm_user.depart_id = $depart_id";
			return $this->join("crm_department")->find($condition, null, "crm_user.*, crm_department.dname");
		}
		
		//根据部门id获取会员列表
		public function getUserByDepart($depart_id, $condition_str, $sort_str, $isall = 0){
			$condition = "1";
			$condition .= is_array($depart_id) ? " and crm_user.depart_id in (". implode(",", $depart_id) .")" : " and crm_user.depart_id = $depart_id";
			if($isall != 1)
				$condition .= " and crm_user.isdel = 0";
			if($condition_str)
				$condition .= " and $condition_str";
			$sort = $sort_str ? $sort_str : "crm_user.createtime asc";
			return $this->join("crm_department")->findAll($condition, $sort, "crm_user.*, crm_department.dname");
		}
		
		public function getUser_prep($condition_ext){
			if($condition_ext){
				$condition = is_array($condition_ext) ? array_merge(array("isdel"=>0), $condition_ext) : "isdel = 0 and (".$condition_ext.")";
			}else{
				$condition = array("isdel"=>0); 
			}
			return $this->findAll($condition, "py asc", "*, fristPinyin(realname) as py");
		}
		
		public function getUserGroupDepart_prep($condition_ext){
			if($condition_ext){
				$condition = is_array($condition_ext) ? array_merge(array("isdel"=>0), $condition_ext) : "isdel = 0 and (".$condition_ext.")";
			}else{
				$condition = array("isdel"=>0);
			}
			$obj_depart = spClass("department");
			if($depart_rs = $obj_depart->getlist_all()){
				foreach($depart_rs as $key => $val){
					if(!$depart_rs[$key]["user_rs"] = $this->findAll(array_merge($condition, array("depart_id"=>$val["id"])), "py asc", "*, fristPinyin(realname) as py"))
						unset($depart_rs[$key]);
				}
			}
			return $depart_rs;
		}
		
		//根据id获取用户集合
		public function getUserById($user_id, $fields = ""){
			return $this->findByPk($user_id, $fields);
		}
		
		//登录时如果启用IP安全设置则验证
		public function checkIPsafe($result, $allowlocal = 0){
			if(!$result["issafeip"])
				return true;
			if($allowlocal && $result['loginip'] == "127.0.0.1")
				return true;
			if($result['loginip'] != long2ip($result["safeip"]))
				throw new Exception("该用户已启用了IP安全设置，您当前的IP地址 ". $result['loginip'] ." 无法登陆");
		}
		
		//获取人员的权限并转为数组
		public function getidentity_array($user_id, $user_rs){
			if(!$identity_attr = $user_rs["identity_attr"]){
				$rs = $this->find(array("id"=>$user_id), null, "identity_attr");
				$identity_attr = $rs["identity_attr"];
			}
			$result = array();
			if($identity_attr){
				if($identity_attr = explode(",", $identity_attr)){
					foreach($identity_attr as $val){
						$result[$val] = 1;
					}
				}
				return $result;
			}
		}
		
		//返回相关条件的字段组
		public function get_relative_array($noprep = 0){
			$prep = $noprep ? "" : "crm_client.";
			$relative = array(
				$prep."user_datafrom_id",
				$prep."user_channel_id",
				$prep."user_channel_assign_id",
				$prep."user_channel_contact_id",
				/*
				$prep."user_trader_id",
				$prep."user_trader_assign_id",
				$prep."user_trader_contact_id",
				*/
				$prep."user_owner_id",
				$prep."user_teler_id",
				$prep."user_preader_id",
				$prep."user_tours_id",
				$prep."user_abroad_id",
				$prep."user_sales_id"
			);
			return $relative;
		}
		
		//获取部门总监数据
		public function getdirector($depart_id, $no_user_id, $sep_id){
			$condition = array("depart_id"=>$depart_id, "isdirector"=>1);
			if($no_user_id)
				$condition["id <>"] = $no_user_id;
			if($sep_id)
				$condition["depart_sep_id"] = $sep_id;
			$user_rs = $this->find($condition);
			return $user_rs;
		}
		
		//市场部业绩统计
		public function marketstat($statdate, $extcondition){
			$condition = "depart_id = 2";
			if($extcondition)
				$condition .= " and $extcondition";
			if($user_rs = $this->findAll($condition, "isdirector desc, createtime asc")){
				$obj_client = spClass("client");
				$obj_active = spClass("channel_active");
				foreach($user_rs as $key => $val){
					if(!$val["id"])
						continue;
					//$user_rs[$key]["client_deal"] = $obj_client->client_deal_count($val, $statdate);
					//$user_rs[$key]["client_noaccount"] = $obj_client->client_deal_noaccount_count($val, $statdate);
					//$user_rs[$key]["client_account"] = $obj_client->client_deal_account_count($val, $statdate);
					
					$user_rs[$key]["client_channel"]["total"] = $obj_client->channel_active_count($val, $statdate);
					$user_rs[$key]["client_channel"]["total_visit"] = $obj_client->channel_active_visit_count($val, $statdate);
					$user_rs[$key]["channel_active"]["total"] = $obj_active->channel_active_count($val, $statdate);
					$user_rs[$key]["origin_analysis"] = $obj_client->origin_analysis($val, $statdate);
					if($val["isdel"] && !intval($user_rs[$key]["origin_analysis"]["together_total"])){
						unset($user_rs[$key]);
						continue;
					}
					$user_rs[$key]["identity_array"] = $this->getidentity_array($val["id"], $val);
					$user_rs[$key] = array_merge($user_rs[$key], $obj_client->statperfer_data_origin($val, $statdate));
				}
			}
			return $user_rs;
		}
		
		//销售部业绩统计
		public function salestat($statdate, $extcondition = ""){
			$condition = "depart_id = 3";
			if($extcondition)
				$condition .= " and $extcondition";
			if($user_rs = $this->findAll($condition, "isdirector desc, createtime asc")){
				$obj_client = spClass("client");
				foreach($user_rs as $key => $val){
					if(!$val["id"])
						continue;
					//$user_rs[$key]["client_deal"] = $obj_client->client_deal_count($val, $statdate);
					//$user_rs[$key]["client_noaccount"] = $obj_client->client_deal_noaccount_count($val, $statdate);
					//$user_rs[$key]["client_account"] = $obj_client->client_deal_account_count($val, $statdate);
					$user_rs[$key]["origin_analysis"] = $obj_client->origin_analysis($val, $statdate);
					if($val["isdel"] && !intval($user_rs[$key]["origin_analysis"]["together_total"])){
						unset($user_rs[$key]);
						continue;
					}
					$user_rs[$key]["identity_array"] = $this->getidentity_array($val["id"], $val);
					/*我的客户来源数据 start*/
					$user_rs[$key] = array_merge($user_rs[$key], $obj_client->statperfer_data_origin($val, $statdate));
					/*
					$user_rs[$key]["client_call_deal"] = $obj_client->client_deal_account_origin_call($val, $statdate);
					$user_rs[$key]["client_call_visit"]["total"] = $obj_client->client_origin_call_visit_count($val, $statdate);
					$user_rs[$key]["client_push_deal"] = $obj_client->client_deal_account_origin_push($val, $statdate);
					$user_rs[$key]["client_push_visit"]["total"] = $obj_client->client_origin_push_visit_count($val, $statdate);
					$user_rs[$key]["client_trader_deal"] = $obj_client->client_deal_account_origin_trader($val, $statdate);
					$user_rs[$key]["client_trader_visit"]["total"] = $obj_client->client_origin_trader_visit_count($val, $statdate);
					$user_rs[$key]["client_intro_deal"] = $obj_client->client_deal_account_origin_intro($val, $statdate);
					$user_rs[$key]["client_origin_deal"] = $obj_client->client_deal_account_origin_channel_all($val, $statdate);
					$user_rs[$key]["sale_client_call_deal"] = $obj_client->sale_client_deal_account_origin_call($val, $statdate);
					$user_rs[$key]["sale_client_push_deal"] = $obj_client->sale_client_deal_account_origin_push($val, $statdate);
					$user_rs[$key]["sale_client_trader_deal"] = $obj_client->sale_client_deal_account_origin_trader($val, $statdate);
					$user_rs[$key]["sale_client_intro_deal"] = $obj_client->sale_client_deal_account_origin_intro($val, $statdate);
					$user_rs[$key]["sale_client_channel_deal"] = $obj_client->sale_client_deal_account_origin_channel($val, $statdate);
					$user_rs[$key]["sale_client_channel_recom_deal"] = $obj_client->sale_client_deal_account_origin_channel_recom($val, $statdate);
					$user_rs[$key]["sale_client_travel_deal"] = $obj_client->sale_client_deal_account_origin_travel($val, $statdate);
					$user_rs[$key]["sale_client_publicity_deal"] = $obj_client->sale_client_deal_account_origin_publicity($val, $statdate);
					$user_rs[$key]["sale_client_origin_deal"] = $obj_client->sale_client_deal_account_origin_channel_all($val, $statdate);
					*/
					/*我的客户来源数据 end*/
					
				}
			}
			return $user_rs;
		}
		
		//市场部业绩统计
		public function otherstat($statdate, $extcondition){
			$condition = "depart_id not in(2,3)";
			if($extcondition)
				$condition .= " and $extcondition";
			if($user_rs = $this->findAll($condition, "isdirector desc, createtime asc")){
				$obj_client = spClass("client");
				foreach($user_rs as $key => $val){
					if(!$val["id"])
						continue;
					//$user_rs[$key]["client_deal"] = $obj_client->client_deal_count($val, $statdate);
					//$user_rs[$key]["client_noaccount"] = $obj_client->client_deal_noaccount_count($val, $statdate);
					//$user_rs[$key]["client_account"] = $obj_client->client_deal_account_count($val, $statdate);
					$user_rs[$key]["origin_analysis"] = $obj_client->origin_analysis($val, $statdate);
					if($val["isdel"] && !intval($user_rs[$key]["origin_analysis"]["together_total"])){
						unset($user_rs[$key]);
						continue;
					}
					$user_rs[$key]["identity_array"] = $this->getidentity_array($val["id"], $val);
					$user_rs[$key] = array_merge($user_rs[$key], $obj_client->statperfer_data_origin($val, $statdate));
				}
			}
			return $user_rs;
		}
	}
?>