<?php
	class article_readuser extends spModel{
		var $pk = "id";
		var $table = "article_readuser";
		
		public function read_it($pid, $article_rs){
			$data = array();
			$data["pid"] = $pid;
			$data["userid"] = $_SESSION["sscrm_user"]["id"];
			$data["createtime"] = time();
			if(!$data["pid"])
				throw new Exception("新闻参数丢失，会员读取记录失败");
			if(!$data["userid"])
				throw new Exception("会员未登录，会员读取记录失败");
			if(!$this->find(array("pid"=>$pid, "userid"=>$data["userid"]))){
				if(!$this->create($data))
					throw new Exception("未知错误，会员读取记录失败");
				$title = "[".$article_rs["name"]."]";
				spClass('user_log')->save_log(1, "系统公告 {$title}[id:{$pid}]转为已读取");
			}
			return true;
		}
	}
?>