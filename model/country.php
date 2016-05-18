<?php
	class country extends spModel{
		var $pk = "id";
		var $table = "country";
		
		var $join = array(
			'crm_user' => array(
				'mapkey' => 'id',
				'fkey' => 'to_overseas_id'
			)
		);
		
		var $validator = array(
			"rules" => array(
				'country' => array(
					'required' => true,
					'maxlength' => 20
				)
			),
			"messages" => array(
				'country' => array(
					'required' => '国家名不能为空',
					'maxlength' => '国家名不能大于20字符',
				)
			)
		);
		
		public function getRsById($id){
			return $this->findByPk($id);
		}
		
		public function getinfoById($id){
			return $this->find(array("isdel"=>0, "id"=>$id));
		}
		
		public function getname($id){
			$rs = $this->getinfoById($id);
			return $rs["country"];
		}
		
		public function getlist(){
			return $this->findAll(array("isdel"=>0), "sort asc");
		}
	}
?>