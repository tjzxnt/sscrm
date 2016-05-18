<?php
	class origin extends spModel{
		var $pk = "id";
		var $table = "origin";
		
		public function get_origin($type){
			$condition = array("isdel"=>0);
			/*
			switch($type){
				case "person":
					$condition["depart_id >"] = 0;
				break;
				case "unperson":
					$condition["depart_id"] = 0;
				break;
				default:
					
				break;
			}
			*/
			return $this->findAll($condition, "sort asc");
		}
		
		public function get_department_origin($department_id){
			$obj_identity = spClass("user_identity");
			/*
			$condition = "isdel = 0 and find_in_set($department_id, need_depart_str) and (need_attr_str = ''";
			if($identity_rs = $obj_identity->getindentitylist()){
				foreach($identity_rs as $key => $val){
					if($val["enabled"])
						$condition .= " or need_attr_str = '$key'";
				}
			}
			$condition .= ")";
			*/
			$condition = "isdel = 0 and find_in_set($department_id, need_depart_str)";
			return $this->findAll($condition, "sort asc");
		}
		
		public function getlist(){
			$obj_identity = spClass("user_identity");
			$condition = "isdel = 0";
			return $this->findAll($condition, "sort asc");
		}
		
		public function get_origin_depart_id($origin_id){
			$rs = $this->find(array("id"=>$origin_id, "isdel"=>0));
			return intval($rs["depart_id"]);
		}
		
		public function getOriginById($id){
			return $this->find(array("id"=>$id, "isdel"=>0), "sort asc");
		}
		
		//客户是否可以直接分配到自己（添加客户）
		private function getclient_to_self($origin_id, $origin_rs){
			$obj_identity = spClass("user_identity");
			/*
			//去除账号附属和账号权限
			if($_SESSION["sscrm_user"]["depart_id"] != $origin_rs["sale_to_self_depart"] || !$obj_identity->checkidentity("getclient"))
				return 0;
			*/
			if($origin_rs["sale_to_self"]){
				$obj_user = spClass("user");
				$user_rs = $obj_user->getCommonUserinfo($_SESSION["sscrm_user"]["id"]);
				return $user_rs;
			}
		}
		
		//客户是否分配到权限上级（添加客户）
		private function getclient_to_assign($origin_id, $origin_rs){
			return false;
			$obj_identity = spClass("user_identity");
			if(!$obj_identity->checkidentity("telclient"))
				return 0;
			if($telclient_rs = $obj_identity->getindentity("telclient")){
				$obj_user = spClass("user");
				$user_rs = $obj_user->getCommonUserinfo($telclient_rs["identity_puserid"]);
				return $user_rs;
			}
		}
		
		//客户添加时是否可以指派到某人（添加客户）
		public function setclient_to_assign($origin_id){
			$origin_rs = $this->getOriginById($origin_id);
			if($user_rs = $this->getclient_to_self($origin_id, $origin_rs))
				return $user_rs;
			elseif($user_rs = $this->getclient_to_assign($origin_id, $origin_rs))
				return $user_rs;
		}
		
		//获取客户的来源信息
		public function getClientOrigin($client_id){
			return $this->join("crm_client", "crm_client.origin_id = crm_origin.id")->find(array("crm_client.id"=>$client_id), null, "crm_origin.*");
		}
		
		public function getClientViewFrom($origin_rs, $client_rs){
			$obj_user = spClass("user");
			$obj_channel = spClass("channel");
			$obj_trader = spClass('trader');
			$obj_active = spClass("channel_active");
			$obj_travel = spClass("travel");
			$obj_comactive = spClass("comactive");
			if($client_rs["user_owner_id"])
				$result["owner_rs"] = $obj_user->getUserById($client_rs["user_owner_id"]);
			if($client_rs["user_teler_id"])
				$result["teler_rs"] = $obj_user->getUserById($client_rs["user_teler_id"]);
			if($client_rs["user_preader_id"])
				$result["preader_rs"] = $obj_user->getUserById($client_rs["user_preader_id"]);
			if($origin_rs["isownchannel"] || $origin_rs["isselfchannel"]){
				$result["channel_rs"] = $obj_channel->getChannelById($client_rs["channel_id"]);
				if($client_rs["channel_id"] && $client_rs["channelact_id"]){
					if($act_rs = $obj_active->get_actives_by_channelid_id($client_rs["channel_id"], $client_rs["channelact_id"]))
						$result["act_rs"] = $act_rs;
				}
			}
			if($origin_rs["isowntrader"] || $origin_rs["isselftrader"])
				$result["trader_rs"] = $obj_trader->getTraderById($client_rs["trader_id"]);
			if($origin_rs["istravel"])
				$result["travel_rs"] = $obj_travel->getTravelById($client_rs["travel_id"]);
			if($client_rs["user_datafrom_id"])
				$result["datafrom_rs"] = $obj_user->getCommonUserinfo($client_rs["user_datafrom_id"]);
			if($client_rs["user_tours_id"])
				$result["tour_rs"] = $obj_user->getCommonUserinfo($client_rs["user_tours_id"]);
			if($client_rs["user_abroad_id"])
				$result["abroad_rs"] = $obj_user->getCommonUserinfo($client_rs["user_abroad_id"]);
			if($origin_rs["iscomactive"])
				$result["comactive_rs"] = $obj_comactive->getActive($client_rs["comactive_id"]);
			return $result;
		}
	}
?>