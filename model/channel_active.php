<?php
	class channel_active extends spModel{
		var $pk = "id";
		var $table = "channel_active";
		
		var $join = array(
			'crm_user' => array(
				'mapkey' => 'id',
				'fkey' => 'create_id'
			),
			'crm_channel' => array(
				'mapkey' => 'id',
				'fkey' => 'channel_id'
			)
		);
		
		var $validator = array(
			"rules" => array(
				'actname' => array(
					'required' => true,
					'minlength' => 4,
					'maxlength' => 30,
				),
				'acttime' => array(
					'required' => true,
					'minlength' => 4,
					'maxlength' => 30,
				)
			),
			"messages" => array(
				'actname' => array(
					'required' => '活动名不能为空',
					'minlength' => '活动名不能少于4个字符',
					'maxlength' => '活动名不能大于30字符'
				),
				'acttime' => array(
					'required' => '活动时间不能为空',
					'minlength' => '活动时间不能少于2个字符',
					'maxlength' => '活动时间不能大于30个字符',
				)
			)
		);
		
		public function get_mychannel_active($id){
			return $this->join("crm_channel")->find(array("crm_channel_active.id"=>$id, "crm_channel.create_id"=>$_SESSION["sscrm_user"]["id"], "crm_channel_active.create_id"=>$_SESSION["sscrm_user"]["id"]), null, "crm_channel_active.*");
		}
		
		//根据渠道和id来查找可用的渠道活动
		public function get_actives_by_channelid_id($channel_id, $id){
			return $this->find(array("crm_channel_active.id"=>$id, "crm_channel_active.channel_id"=>$channel_id), null, "crm_channel_active.*");
		}
		
		//查找指定渠道的可用活动集合
		public function get_actives_by_channelid($channel_id){
			return $this->join("crm_user")->findAll(array("crm_channel_active.channel_id"=>$channel_id), "crm_channel_active.createtime desc", "crm_channel_active.id, crm_channel_active.actname, crm_channel_active.acttime, crm_user.realname");
		}
		
		//来源于我举办活动的总数
		public function channel_active_count($user_rs, $statdate){
			$condition = "crm_channel_active.create_id = " . intval($user_rs["id"]);
			$condition .= " and date_format(crm_channel_active.acttime,'%Y-%m') = '".date("Y-m", strtotime($statdate))."'";
			$count_rs = $this->find($condition, null, "count(crm_channel_active.id) as total");
			return intval($count_rs["total"]);
		}
		
		public function getinfoById($id){
			return $this->find(array("id"=>$id));
		}
		
		public function getname($id){
			$rs = $this->getinfoById($id);
			return $rs["actname"];
		}
		
		public function getCountById($channel_id){
			$rs = $this->find(array("channel_id"=>$channel_id), null, "count(id) as total");
			return intval($rs["total"]);
		}
	}
?>