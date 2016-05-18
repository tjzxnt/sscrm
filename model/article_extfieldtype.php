<?php
	class article_extfieldtype extends spModel{
		var $pk = "id"; // 每个留言唯一的标志，可以称为主键
		var $table = "articlesextfieldtype"; // 数据表的名称
		
		public function getlist(){
			return $this->findAll(null, "sort asc");
		}
	}
?>