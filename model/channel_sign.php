<?php
	class channel_sign extends spModel{
		var $pk = "id";
		var $table = "channel_sign";
		
		var $join = array(
			'crm_user' => array(
				'mapkey' => 'id',
				'fkey' => 'user_id'
			),
			'crm_channel' => array(
				'mapkey' => 'id',
				'fkey' => 'channel_id'
			)
		);
		
		//根据签约人，查找渠道签成量
		public function sign_total($user_rs, $statdate){
			$condition = "crm_channel.from_id = {$user_rs["id"]}";
			if($statdate)
				$condition .= " and DATE_FORMAT(crm_channel_sign.signdate,'%Y-%m') = '".date("Y-m", strtotime($statdate))."'";
			$count_rs = $this->join("crm_channel")->find($condition, null, "count(crm_channel_sign.id) as total");
			return $count_rs;
		}
		
		//根据签约人，查找已签成渠道量
		public function signed_channel_total($user_rs, $statdate){
			$condition = "crm_channel.from_id = {$user_rs["id"]}";
			if($statdate)
				$condition .= " and DATE_FORMAT(crm_channel_sign.startdate,'%Y-%m-01') <= '{$statdate}' and LAST_DAY(DATE_FORMAT(crm_channel_sign.enddate,'%Y-%m-01')) >= '{$statdate}'";
			$count_rs = $this->join("crm_channel")->find($condition, null, "count(DISTINCT crm_channel_sign.channel_id) as total");
			return $count_rs;
		}
		
		//根据维护人，查找已签成渠道量
		public function main_channel_total($user_rs, $statdate){
			$condition = "crm_channel.maintenance_id = {$user_rs["id"]}";
			if($statdate)
				$condition .= " and DATE_FORMAT(crm_channel_sign.startdate,'%Y-%m-01') <= '{$statdate}' and LAST_DAY(DATE_FORMAT(crm_channel_sign.enddate,'%Y-%m-01')) >= '{$statdate}'";
			$count_rs = $this->join("crm_channel")->find($condition, null, "count(DISTINCT crm_channel_sign.channel_id) as total");
			return $count_rs;
		}
	}
?>