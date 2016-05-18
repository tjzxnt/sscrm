<?php
	class article_extval extends spModel{
		var $pk = "id";
		var $table = "articlesextval";
		
		var $join = array(
	 		'crm_articlesextfield' => array(
	 			'mapkey' => 'id',
	 			'fkey' => 'field_id'
	 		)
	 	);
		
		//后台更新用
		public function extval_update($data, $column_id, $article_id){
			foreach($data as $key => $val){
				if(is_array($val))
					$val = implode(",", $val);
				if($extval_rs = $this->find(array("column_id"=>$column_id, "article_id"=>$article_id, "field_id"=>$key), null, "id")){
					$this->update(array("id"=>$extval_rs["id"]), array("column_id"=>$column_id, "field_val"=>str_replace('../upload/', '/upload/', $val)));
				}else{
					$this->create(array("column_id"=>$column_id, "article_id"=>$article_id, "field_id"=>$key, "field_val"=>str_replace('../upload/', '/upload/', $val)));
				}
			}
		}
		
		//后台读取用
		public function extval_get($article_id){
			if($result = $this->findAll(array("article_id"=>$article_id), null, "field_id, field_val")){
				foreach($result as $key => $val)
					$rs[$val["field_id"]] = str_replace('/upload/', '../upload/', $val["field_val"]);
				return $rs;
			}
		}
		
		//前台读取用
		public function extval_get_mark($article_id){
			if($result = $this->join("crm_articlesextfield")->findAll(array("crm_articlesextval.article_id"=>$article_id), null, "crm_articlesextval.field_id, crm_articlesextfield.field_mark, crm_articlesextval.field_val")){
				foreach($result as $key => $val)
					$rs[$val["field_mark"]] = str_replace('/upload/', WEB_ROOT . '/upload/', $val["field_val"]);
				return $rs;
			}
		}
	}
?>