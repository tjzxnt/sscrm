<?php
	class travel extends spModel{
		var $pk = "id";
		var $table = "travel";
		
		var $join = array(
			'crm_user' => array(
				'mapkey' => 'id',
				'fkey' => 'create_id'
			)
		);
		
		var $addrules = array(
			'available_name' => array('mtravels', 'checkName')
		);
		
		var $validator = array(
			"rules" => array(
				'travelname' => array(
					'required' => true,
					'minlength' => 4,
					'maxlength' => 40,
					'available_name' => true
				)
			),
			"messages" => array(
				'travelname' => array(
					'required' => '旅行社名不能为空',
					'minlength' => '旅行社名不能少于4个字符',
					'maxlength' => '旅行社名不能大于40字符',
					'available_name' => '旅行社名已经被注册'
				)
			)
		);
		
		public function spValidatorForOPT() {
			unset($this->addrules);
			unset($this->validator['rules']['travelname']['available_name']);
			unset($this->validator['messages']['travelname']['available_name']);
			return $this;
		}
		
		public function getValidatorForCreateJS() {
			$validator = $this->validator;
			$validator['rules']['travelname']['remote'] = spUrl("mtravels", "checkName");
			$validator['messages']['travelname']['remote'] = $validator['messages']['travelname']['available_name'];
			unset($validator['rules']['travelname']['available_name']);
			unset($validator['messages']['travelname']['available_name']);
			return parent::getValidatorJS($validator);
		}
		
		public function getValidatorForModifyJS($id) {
			$validator = $this->validator;
			$validator['rules']['travelname']['remote'] = spUrl("channels", "checkName", array("id"=>$id));
			$validator['messages']['travelname']['remote'] = $validator['messages']['travelname']['available_name'];
			unset($validator['rules']['travelname']['available_name']);
			unset($validator['messages']['travelname']['available_name']);
			return parent::getValidatorJS($validator);
		}
		
		public function checkName($name, $id){
			try {
				$condition = array("travelname"=>$name);
				if($id)
					$condition["id <>"] = $id;
				if($this->find($condition))
					throw new Exception("exist");
				return true;
			}catch(Exception $e){
				return false;
			}
		}
		
		public function getTravelById($id){
			return $this->join("crm_user")->find(array("crm_travel.id"=>$id), null, "crm_travel.*, crm_user.realname as realname_create");
		}
		
		public function getlist_prep(){
			return $this->findAll(array("ishide"=>0), "py asc", "*, fristPinyin(travelname) as py");
		}
		
		public function getinfoById($id){
			return $this->find(array("id"=>$id));
		}
		
		public function getname($id){
			$rs = $this->getinfoById($id);
			return $rs["travelname"];
		}
	}
?>