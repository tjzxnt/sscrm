<?php
	class articlefilter extends spModel{
		var $pk = "pc_id"; // 每个留言唯一的标志，可以称为主键
		var $table = "articlesfilter"; // 数据表的名称
		
	    var $validator = array(
			"rules" => array( // 规则
				'pc_name' => array(  // 这里是对name的验证规则
					'required' => true, // name不能为空
					'minlength' => 2,  // name长度不能小于5
					'maxlength' => 30 // name长度不能大于15
				)
			),
			"messages" => array( // 提示信息
				'pc_name' => array(
					'required' => '标题不能为空',
					'minlength' => '标题不能少于2个字符',
					'maxlength' => '标题不能大于30字符'
				)
			)
		);
	    
	    public function getToplist($column_id){
	    	return $this->findAll(array("markid"=>$column_id, "fid"=>0));
	    }
	    
	    public function getSublist($column_id, $fid = 0){
	    	return $this->findAll(array("markid"=>$column_id, "fid"=>$fid));
	    }
	}
?>