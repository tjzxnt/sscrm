<?php
	class article_extfield extends spModel{
		var $pk = "id"; // 每个留言唯一的标志，可以称为主键
		var $table = "articlesextfield"; // 数据表的名称
		
		var $join = array(
			'crm_articlesextfieldtype' => array(
				'mapkey' => 'id',
				'fkey' => 'field_type'
			),
			'crm_articlesextval' => array(
				'mapkey' => 'field_id',
				'fkey' => 'id'
			)
		);
		
	    var $validator = array(
			"rules" => array( // 规则
				'field_mark' => array(  // 这里是对name的验证规则
					'required' => true, // name不能为空
					'maxlength' => 30 // name长度不能大于15
				),
				'field_name' => array(  // 这里是对name的验证规则
					'required' => true, // name不能为空
					'maxlength' => 30 // name长度不能大于15
				)
			),
			"messages" => array( // 提示信息
				'field_mark' => array(
					'required' => '字段标识不能为空',
					'maxlength' => '字段标识不能大于30字符'
				),
				'field_name' => array(
					'required' => '字段名不能为空',
					'maxlength' => '字段名不能大于30字符'
				)
			)
		);
	    
	    public function getTolist($column_id){
	    	if(!$column_id)
	    		return false;
	    	return $this->findAll(array("column_id"=>$column_id, "istolist"=>1, "ishide"=>0), "sort asc");
	    }
	}
?>