<?php
	class article_coverconfig extends spModel{
		var $pk = "id"; // 每个留言唯一的标志，可以称为主键
		var $table = "articlescoverconfig"; // 数据表的名称
		  
	    var $validator = array(
			"rules" => array( // 规则
				'cover_name' => array(
					'required' => true,
					'minlength' => 2,
					'maxlength' => 30
				),
				'cover_mark' => array(
					'required' => true,
					'minlength' => 2,
					'maxlength' => 30
				),
			),
			"messages" => array( // 提示信息
				'cover_name' => array(
					'required' => '组名不能为空',
					'minlength' => '组名不能少于2个字符',
					'maxlength' => '组名不能大于30字符'
				),
				'cover_mark' => array(
					'required' => '组标识不能为空',
					'minlength' => '组标识不能少于2个字符',
					'maxlength' => '组标识不能大于30字符'
				)
			)
		);
	    
	    public function getToplist($column_id){
	    	return $this->findAll(array("column_id"=>$column_id), "sort asc");
	    }
	}
?>