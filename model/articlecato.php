<?php
	class articlecato extends spModel{
		var $pk = "pc_id"; // 每个留言唯一的标志，可以称为主键
		var $table = "articlescato"; // 数据表的名称
		  
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
	    
	    public function getuppath($id){
	    	return "uploads/cato/$id/";
	    }
	    
	    public function checkupfile($mark, $type = array("gif", "png", "jpg", "png")){
	    	if (!empty($_FILES[$mark]['name'])){
	    		$filename = basename($_FILES[$mark]['name']);       //文件名
	    		$file_ext = strtolower(substr($filename, strrpos($filename, '.') + 1));
	    		if(!in_array($file_ext, $type))
	    			throw new Exception('您选择的文件类型不正确'.$type);
	    	}
	    }
	    
	    public function upfile($mark, $id, $column_config, $field = array("picpath", "picfile"), $type = array("gif", "png", "jpg", "png"), $is_oral = 1){
	    	if (!empty($_FILES[$mark]['name']) && $id){
	    		import("Common.php");
	    		import("Image.php");
	    		$flag = true;
	    		$userfile_name = $_FILES[$mark]['name'];            //上传图片的全路径
	    		$userfile_tmp  = $_FILES[$mark]['tmp_name'];        //临时文件的全路径
	    		$userfile_size = $_FILES[$mark]['size'];            //上传文件的大小
	    		$filename = basename($_FILES[$mark]['name']);       //文件名
	    		$file_ext = strtolower(substr($filename, strrpos($filename, '.') + 1));
	    		$this->checkupfile($mark, $type);
	    		$pic_name = Common::randomkeys(16);
	    		$dir_str = $this->getuppath($id);
	    		$file_dir ='../'.$dir_str;
	    		if(!is_dir($file_dir)){
	    			Common::rmkdir($file_dir);
	    			if($mark)
	    				chmod(dirname($file_dir)."/", 0777);
	    			chmod($file_dir, 0777);
	    		}
	    		$image_location = $file_dir . $pic_name . '.' . $file_ext;
	    		if(!move_uploaded_file($_FILES[$mark]['tmp_name'], $image_location))
	    			throw new Exception('未知错误，上传失败');
	    		if($column_config['cato_thumb_width'] && $column_config['cato_thumb_height']){
	    			$image = new Image();
	    			$image->open($image_location);
	    			$thumb_image_location = $file_dir . 'thumb_' . $pic_name . '.' .$file_ext;
	    			$thumb_width = intval($column_config['cato_thumb_width']);
	    			$thumb_height = intval($column_config['cato_thumb_height']);
	    			$image->thumbImage($thumb_image_location, $thumb_width, $thumb_height);
	    			$thumb = 1;
	    		}
	    		if(is_array($field)){
	    			$fieldstr = implode(",", $field);
	    			$data["data"][$field[0]] = $dir_str;
	    			$data["data"][$field[1]] = $pic_name . '.' .$file_ext;
	    			if($is_oral){
	    				$rs = $this->find(array("pc_id"=>$id), null, $fieldstr);
	    				$data["oralfile"][] = "../" . implode(null, $rs);
	    				$data["oralfile"][] = "../" . implode("thumb_", $rs);
	    			}
	    		}else{
	    			$data["data"][$field] = $dir_str . $pic_name . '.' .$file_ext;
	    			if($is_oral){
	    				$rs = $this->find(array("pc_id"=>$id), null, $field);
	    				$data["oralfile"][] = "../" . $rs[$field];
	    			}
	    		}
	    		$data["source"]['realpath'][] = $file_dir . '.' .$data['filename'];
	    		if($thumb)
	    			$data["source"]['realpath'][] = $file_dir . '.thumb_' .$data['filename'];
	    		return $data;
	    	}
	    }

	    public function del_cato($id){
	    	import("Common.php");
	    	$condition = "pc_id = $id";
	    	$this->delete($condition);
	    	$uppath = "../" . $this->getuppath($id);
	    	Common::deldir($uppath);
	    }
	}
?>