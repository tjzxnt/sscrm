<?php
	class user_notice extends spModel{
		var $pk = "id";
		var $table = "user_notice";
		
		var $join = array(
			'crm_user' => array(
				'mapkey' => 'id',
				'fkey' => 'fromid'
			)
		);
		
		public function get_notice(){
			return $this->find(array("toid"=>$_SESSION['sscrm_user']['id'], "isread"=>0, "isdel"=>0), null, "count(id) as total");
		}
		
		//暂时停止通知，没啥用、、
		public function send_notice($toid, $title, $content, $nofromid = 0){
			$data = array();
			$data["fromid"] = $_SESSION['sscrm_user']['id'];
			$data["toid"] = $toid;
			$data["title"] = $title;
			$data["content"] = $content;
			$data["createtime"] = time();
			$data["nofromid"] = intval($nofromid);
			$data["isread"] = 0;
			$data["isdel"] = 0;
			//return $this->create($data);
		}
	}
?>