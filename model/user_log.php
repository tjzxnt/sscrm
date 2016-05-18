<?php
	class user_log extends spModel{
		var $pk = "id";
		var $table = "user_log";
		
		var $join = array(
			'crm_user' => array(
				'mapkey' => 'id',
				'fkey' => 'user_id'
			),
			'crm_user_log_type' => array(
				'mapkey' => 'id',
				'fkey' => 'type_id'
			)
		);
		
		//保存日志($type_id:1.基础;2.渠道;3.客户;4.员工;)
		public function save_log($type_id, $logcontent, $postdata){
			import("Common.php");
			$data = array();
			$data["user_id"] = $_SESSION['sscrm_user']['id'];
			$data["type_id"] = $type_id;
			$data["intention_id"] = intval($postdata["intention_id"]);
			$data["client_id"] = $postdata["client_id"];
			$data["vip_client_id"] = intval($postdata["vip_client_id"]);
			$data["channel_id"] = intval($postdata["channel_id"]);
			$data["trader_id"] = intval($postdata["trader_id"]);
			$data["logcontent"] = $logcontent;
			$data["logurl"] = $_SERVER['REQUEST_URI'];
			if($_POST){
				foreach ($_POST as $k => $v){
					if(is_array($v)){
						$v = json_encode($v);
						$v = str_replace("\"", "'", $v);
					}
					$postcode[$k] = urlencode($v);
				}
				$data["logdata"] = urldecode(json_encode($postcode));
			}
			$data["logip"] = Common::GetIP();
			$data["logtime"] = time();
			if(!$_SESSION['sscrm_user']['is_stealth'])
				return $this->create($data);
			else
				return true;
		}
	}
?>