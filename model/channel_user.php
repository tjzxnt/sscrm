<?php
	class channel_user extends spModel{
		var $pk = "id";
		var $table = "channel_user";
		
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
				'realname' => array(
					'required' => true,
					'minlength' => 2,
					'maxlength' => 10,
				),
				'telphone' => array(
					'required' => true,
					'minlength' => 8,
					'maxlength' => 20,
				)
			),
			"messages" => array(
				'realname' => array(
					'required' => '联系人不能为空',
					'minlength' => '联系人不能少于2个字符',
					'maxlength' => '联系人不能大于10字符'
				),
				'telphone' => array(
					'required' => '联系电话不能为空',
					'minlength' => '联系电话不能少于8个字符',
					'maxlength' => '联系电话不能大于20个字符',
				)
			)
		);
		
		public function get_mychannel_user($id){
			return $this->join("crm_channel")->find(array("crm_channel_user.id"=>$id, "crm_channel.create_id"=>$_SESSION["sscrm_user"]["id"], "crm_channel_user.create_id"=>$_SESSION["sscrm_user"]["id"]), null, "crm_channel_user.*");
		}
		
		//根据渠道和id来查找可用的渠道活动
		public function get_user_by_channelid_id($channel_id, $id){
			return $this->find(array("crm_channel_user.id"=>$id, "crm_channel_user.channel_id"=>$channel_id), null, "crm_channel_user.*");
		}
		
		//查找指定渠道的可用联系人
		public function get_user_by_channelid($channel_id){
			return $this->join("crm_user")->findAll(array("crm_channel_user.channel_id"=>$channel_id), "crm_channel_user.createtime desc", "crm_channel_user.id, crm_channel_user.realname, crm_channel_user.telphone, crm_user.realname as realname_create");
		}
	}
?>