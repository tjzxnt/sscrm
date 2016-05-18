<?php
	class article extends spModel{
		var $pk = "pid";
		var $table = "articles";
		
		var $join = array(
	 		'crm_articlescato' => array(
	 			'mapkey' => 'pc_id',
	 			'fkey' => 'pc_id'
	 		),
			"crm_entrance" => array(
				'mapkey' => 'id',
				'fkey' => 'entrance_id'
			),
			"crm_entrance_line" => array(
				'mapkey' => 'id',
				'fkey' => 'entrance_line_id'
			)
	 	);
		
	    var $validator = array(
			"rules" => array(
				'name' => array(
					'required' => true,
					'minlength' => 2,
					'maxlength' => 50
				)
			),
			"messages" => array( // 提示信息
				'name' => array(
					'required' => '标题不能为空',
					'minlength' => '标题不能少于2个字符',
					'maxlength' => '标题不能大于50字符'
				)
			)
		);
		
		public function getValidatorJS() {
			$validator = $this->validator;
			unset($validator['rules']['order']['is_point']);
			unset($validator['messages']['order']['is_point']);
			return parent::getValidatorJS($validator);
		}
			
	    public function getValidatorJSedit($id) {
			$validator = $this->validator;
			unset($validator['rules']['order']['is_point']);
			unset($validator['messages']['order']['is_point']);
			return parent::getValidatorJS($validator);
		}
		
		public function spValidatoredit() {			
			return $this;
		}
		
		public function getuppath($id, $mark = ""){
			return $mark ? "uploads/source/$id/$mark/" : "uploads/source/$id/";
		}
		
		public function checkupfile($mark, $type = array("gif", "png", "jpg", "png")){
			if (!empty($_FILES[$mark]['name'])){
				$filename = basename($_FILES[$mark]['name']);       //文件名
				$file_ext = strtolower(substr($filename, strrpos($filename, '.') + 1));
				if(!in_array($file_ext, $type))
					throw new Exception('您选择的文件类型不正确'.$type);
			}
		}
		
		public function upfile($mark, $id, $column_config, $field = array("picurlpath", "picurlfile"), $type = array("gif", "png", "jpg", "png"), $is_oral = 1){
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
				$dir_str = $this->getuppath($id, $mark);
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
				if($column_config['thumb_width'] && $column_config['thumb_height']){
					$image = new Image();
					$image->open($image_location);
					$thumb_image_location = $file_dir . 'thumb_' . $pic_name . '.' .$file_ext;
					$thumb_width = intval($column_config['thumb_width']);
					$thumb_height = intval($column_config['thumb_height']);
					$image->thumbImage($thumb_image_location, $thumb_width, $thumb_height);
					$thumb = 1;
				}
				if(is_array($field)){
					$fieldstr = implode(",", $field);
					$data["data"][$field[0]] = $dir_str;
					$data["data"][$field[1]] = $pic_name . '.' .$file_ext;
					if($is_oral){
						$rs = $this->find(array("pid"=>$id), null, $fieldstr);
						$data["oralfile"][] = "../" . implode(null, $rs);
						$data["oralfile"][] = "../" . implode("thumb_", $rs);
					}
				}else{
					$data["data"][$field] = $dir_str . $pic_name . '.' .$file_ext;
					if($is_oral){
						$rs = $this->find(array("pid"=>$id), null, $field);
						$data["oralfile"][] = "../" . $rs[$field];
					}
				}
				$data["source"]['realpath'][] = $file_dir . '.' .$data['filename'];
				if($thumb)
					$data["source"]['realpath'][] = $file_dir . '.thumb_' .$data['filename'];
				return $data;
			}
		}
		
		public function delupfile($mark, $id, $field = array("filepath", "filename")){
			if(is_array($field)){
				$fieldstr = implode(",", $field);
				if($rs = $this->find(array("pid"=>$id), null, $fieldstr)){
					unlink("../" . implode(null, $rs));
					unlink("../" . implode("thumb_", $rs));
					foreach($field as $val){
						$data[$val] = "";
					}
					$this->update(array("pid"=>$id), $data);
				}
			}else{
				if($rs = $this->find(array("pid"=>$id), null, $field))
					unlink("../" . $rs[$field]);
				$this->update(array("pid"=>$id), array($field=>""));
			}
		}
		
		public function del_article($id, $column_rs = ""){
			import("Common.php");
			$obj_extval = spClass('article_extval');
			$condition = ($column_rs["isentrance"] && $GLOBALS['G_SP']["entrance"]["enabled"]) ? "(entrance_id = " . $_SESSION["sys_entrance"]["id"] . " or entrance_id = 0)" : "1";
			$condition .= " and pid = $id";
			$this->delete($condition);
			$obj_extval->delete(array("article_id"=>$id));
			$uppath = "../" . $this->getuppath($id);
			Common::deldir($uppath);
		}
		
		public function getline($line_id){
			if($line_rs = spClass("entrance_line")->find(array("id"=>$line_id, "ishide"=>0), null, "name"))
				return $line_rs;
		}
		
		public function getentrance($ent_id){
			if($ent_rs = spClass("entrance")->find(array("id"=>$ent_id, "ishide"=>0), null, "name"))
				return $ent_rs;
		}
		
		public function getExtfield($id, $field_mark){
			$obj_extval = spClass("article_extval");
			$val_rs = $obj_extval->join("crm_articlesextfield", "crm_articlesextfield.id = crm_articlesextval.field_id and crm_articlesextfield.field_mark = '$field_mark'")->find(array("crm_articlesextval.article_id"=>$id), null, "crm_articlesextval.field_val");
			return $val_rs["field_val"];
		}
	}
?>