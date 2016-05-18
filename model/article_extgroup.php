<?php
	class article_extgroup extends spModel{
		var $pk = "id"; // 每个留言唯一的标志，可以称为主键
		var $table = "articlesextgroup"; // 数据表的名称
		  
	    var $validator = array(
			"rules" => array( // 规则
				'group_name' => array(  // 这里是对name的验证规则
					'required' => true, // name不能为空
					'minlength' => 2,  // name长度不能小于5
					'maxlength' => 30 // name长度不能大于15
				)
			),
			"messages" => array( // 提示信息
				'group_name' => array(
					'required' => '标题不能为空',
					'minlength' => '标题不能少于2个字符',
					'maxlength' => '标题不能大于30字符'
				)
			)
		);
	    
	    //后台读取用
	    public function get_extfield_format($column_id){
	    	$obj_extfield = spClass("article_extfield");
	    	if($ext_group_rs = $this->findAll(array("column_id"=>$column_id), "sort asc")){
	    		foreach($ext_group_rs as $key => $val){
	    			if($ext_group_rs[$key]["extfield"] = $obj_extfield->findAll(array("group_id"=>$val["id"], "ishide"=>0), "sort asc", "*, concat('extfield[', id, ']') as format_mark, concat('extfield_', id) as format_id")){
	    				foreach($ext_group_rs[$key]["extfield"] as $k => $v){
	    					$ext_group_rs[$key]["extfield"][$k] = str_replace('/upload/', '../upload/', $v);
	    				}
	    			}
	    		}
	    	}
	    	return $ext_group_rs;
	    }
	}
?>