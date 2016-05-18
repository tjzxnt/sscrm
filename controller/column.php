<?php
class column extends spController {
	
	public function __construct(){
		parent::__construct();
		try {
			if(!$_SESSION["sscrm_user"]["user_identity"]["article"]["enabled"])
				throw new Exception("您无权查看该页面");
		}catch(Exception $e){
			$backurl = spUrl("main", "welcome");
			$this->redirect($backurl, $e->getMessage());
			exit();
		}
	}
	
	function columnlist(){
		$articlestyle = spClass('articlestyle');
		$postdata = $this->spArgs();
		if ($postdata['searchkeys']) {
			$condition['sname like'] = "%{$postdata['searchkeys']}%";
		}
		import("Common.php");
		$list_temp = $articlestyle->findAll($condition, "frontdisplay desc, columnorder asc, sid desc", "sid, sname, mark, isentrance, isentranceline, islist, ishtml, frontdisplay, pagesize, parentid, is_extfield, iscover, columnorder");
		$list_rs = Common::TypeList($list_type, $list_temp, "sid", 0, 1, "parentid");
		$this->list_rs = $list_rs;
		$this->url = spUrl('column', 'columnlist', array('searchkeys'=>$postdata['searchkeys']));
		$this->searchkeys = $postdata['searchkeys'];
	}
	
	function createcolumn(){
		$articlestyle = spClass('articlestyle');
		$id = intval($this->spArgs('id'));
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			import("pinyin.php");
			$data = array();
			$postdata = $this->spArgs();
			$isAjax = $postdata['isAjax'];
			$data['sname'] = trim($postdata['sname']);
			$data['keywords'] = $postdata['keywords'];
			$data['description'] = $postdata['description'];
			$data['cinfo'] = $postdata['cinfo'];
			$data['isentrance'] = intval($postdata['isentrance']);
			$data['isentranceline'] = intval($postdata['isentranceline']);
			$data['parentid'] = intval($postdata['parentid']);
			$data['mark'] = trim($postdata['mark']) ? trim($postdata['mark']) : pinyin($data['sname']);
			$data['fname'] = intval($postdata['fname']);
			$data['ismetatitle'] = intval($postdata['ismetatitle']);
			$data['ismeta'] = intval($postdata['ismeta']);
			$data['order'] = intval($postdata['order']);
			$data['isindex'] = intval($postdata['isindex']);
			$data['isrelate'] = intval($postdata['isrelate']);
			$data['relatestr'] = $postdata['relatestr'];
			$data['subcolumntype'] = intval($postdata['subcolumntype']);
			$data['subcolumn'] = $postdata['subcolumn'];
			$data['islist'] = intval($postdata['islist']);
			$data['pagesize'] = intval($postdata['pagesize']) > 0 ? intval($postdata['pagesize']) : 10;
			$data['iseditor'] = intval($postdata['iseditor']);
			$data['isfrom'] = intval($postdata['isfrom']);
			$data['iscover'] = intval($postdata['iscover']);
			$data['ispic'] = intval($postdata['ispic']);
			$data['ispic2'] = intval($postdata['ispic2']);
			$data['ispic3'] = intval($postdata['ispic3']);
			$data['thumb_width'] = intval($postdata['thumb_width']);
			$data['thumb_height'] = intval($postdata['thumb_height']);
			$data['isflv'] = intval($postdata['isflv']);
			$data['ispackage'] = intval($postdata['ispackage']);
			$data['istop'] = intval($postdata['istop']);
			$data['isrecomd'] = intval($postdata['isrecomd']);
			$data['isspecrecomd'] = intval($postdata['isspecrecomd']);
			$data['stj_news'] = intval($postdata['stj_news']);
			$data['stj_hot'] = intval($postdata['stj_hot']);
			$data['stj_soldout'] = intval($postdata['stj_soldout']);
			$data['istag'] = intval($postdata['istag']);
			$data['ishits'] = intval($postdata['ishits']);
			$data['iscanhide'] = intval($postdata['iscanhide']);
			$data['isprice'] = intval($postdata['isprice']);
			$data['isprice2'] = intval($postdata['isprice2']);
			$data['isdescription'] = intval($postdata['isdescription']);
			$data['isshort'] = intval($postdata['isshort']);
			$data['islink'] = intval($postdata['islink']);
			$data['islinkpic'] = intval($postdata['islinkpic']);
			$data['isfilter2'] = intval($postdata['isfilter2']);
			$data['isposttime'] = intval($postdata['isposttime']);
			$data['iscato'] = intval($postdata['iscato']);
			$data['iscatostitle'] = intval($postdata['iscatostitle']);
			$data['iscatopic'] = intval($postdata['iscatopic']);
			$data['iscatodesc'] = intval($postdata['iscatodesc']);
			$data['iscatoinfo'] = intval($postdata['iscatoinfo']);
			$data['cato_thumb_width'] = intval($postdata['cato_thumb_width']);
			$data['cato_thumb_height'] = intval($postdata['cato_thumb_height']);
			$data['iscato2'] = intval($postdata['iscato2']);
			$data['ishtml'] = intval($postdata['ishtml']);
			if ($postdata['extinput']) {
				$data['extinput'] = $postdata['extinput'];
			}
			$data["is_extfield"] = intval($postdata["is_extfield"]);
			$data['frontdisplay'] = intval($postdata['frontdisplay']);
			$postdata['displaylist'] = str_replace("{tplpath}/", "", $postdata['displaylist']);
			$postdata['displaydetail'] = str_replace("{tplpath}/", "", $postdata['displaydetail']);
			if ($postdata['displaylist']) $data['displaylist'] = $postdata['displaylist'];
			if ($postdata['displaydetail']) $data['displaydetail'] = $postdata['displaydetail'];
			if ($postdata['freeother']) {
				$data['freeother'] = $postdata['freeother'];
			}
			if ($postdata['freeother2']) {
				$data['freeother2'] = $postdata['freeother2'];
			}
			if ($postdata['freeother3']) {
				$data['freeother3'] = $postdata['freeother3'];
			}
			if ($postdata['outlink']) {
				$data['outlink'] = $postdata['outlink'];
			}
			$data['linkfield'] = intval($postdata['linkfield']);
			$data['remark'] = $postdata['remark'];
			$data['columnorder'] = intval($postdata['columnorder']);
			$data['is_config'] = intval($postdata['is_config']);
			/*
			if ($articlestyle->find(array("mark"=>$data['mark']))) {
				$data['mark'] = $data['mark'].rand(100,999);
				/
				$message = array('msg'=>"标识 ".$data['mark']." 已被占用" ,'result'=>-1);	
				echo json_encode($message);
				exit();
				/
			}
			*/
			if($result = $articlestyle->spValidator($data)) {
				foreach($result as $item) {
					$msg .= implode('<br>', $item) . '<br>';
				}
			}
			if($msg) {
				if('1' == $isAjax) {
					$message = array('msg' => $msg, 'result'=>-1);
					echo json_encode($message);
					exit();
				}
				else {
					$this->errMsg = $msg;
				}
			}
			else {
				if($articlestyle->create($data)) {
					$url = spUrl("column", "columnlist");
					if('1' == $isAjax) {
						$message = array('msg'=>'添加成功！','result'=>0, 'url'=>$url);
						echo json_encode($message);
						exit();
					}
					else {
						$this->redirect($url);
					}
				}
				else {
					$msg = '添加失败！';
					if('1' == $isAjax) {
						$message = array('msg'=>$msg ,'result'=>-1);	
						echo json_encode($message);
						exit();
					}
					else {
						$this->errMsg = $msg;
					}
				}
			}
		}
		if($id){
			$column_rs = $articlestyle->findByPk($id);
			$this->column_rs = $column_rs;
		}
		$list_rs = $articlestyle->findAll(array("parentid"=>0), "columnorder asc");
		$this->list_rs = $list_rs;
		$this->validator = $articlestyle->getValidatorJS();
	    $this->saveurl = spUrl("column", "createcolumn");
	}
	
	function updatecolumn(){
		$id = intval($this->spArgs('id'));
		if($id){
			$articlestyle = spClass('articlestyle');
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				import("pinyin.php");
				$data = array();
				$postdata = $this->spArgs();
				$isAjax = $postdata['isAjax'];
				$data['sname'] = $postdata['sname'];
				$data['keywords'] = $postdata['keywords'];
				$data['description'] = $postdata['description'];
				$data['cinfo'] = $postdata['cinfo'];
				$data['isentrance'] = intval($postdata['isentrance']);
				$data['isentranceline'] = intval($postdata['isentranceline']);
				$data['parentid'] = intval($postdata['parentid']);
				$data['mark'] = trim($postdata['mark']) ? trim($postdata['mark']) : pinyin($data['sname']);
				$data['fname'] = intval($postdata['fname']);
				$data['ismetatitle'] = intval($postdata['ismetatitle']);
				$data['ismeta'] = intval($postdata['ismeta']);
				$data['order'] = intval($postdata['order']);
				$data['isindex'] = intval($postdata['isindex']);
				$data['isrelate'] = intval($postdata['isrelate']);
				$data['relatestr'] = $postdata['relatestr'];
				$data['subcolumntype'] = intval($postdata['subcolumntype']);
				$data['subcolumn'] = $postdata['subcolumn'];
				$data['islist'] = intval($postdata['islist']);
				$data['pagesize'] = intval($postdata['pagesize']) > 0 ? intval($postdata['pagesize']) : 10;
				$data['iseditor'] = intval($postdata['iseditor']);
				$data['isfrom'] = intval($postdata['isfrom']);
				$data['iscover'] = intval($postdata['iscover']);
				$data['ispic'] = intval($postdata['ispic']);
				$data['ispic2'] = intval($postdata['ispic2']);
				$data['ispic3'] = intval($postdata['ispic3']);
				$data['thumb_width'] = intval($postdata['thumb_width']);
				$data['thumb_height'] = intval($postdata['thumb_height']);
				$data['isflv'] = intval($postdata['isflv']);
				$data['ispackage'] = intval($postdata['ispackage']);
				$data['istop'] = intval($postdata['istop']);
				$data['isrecomd'] = intval($postdata['isrecomd']);
				$data['isspecrecomd'] = intval($postdata['isspecrecomd']);
				$data['stj_news'] = intval($postdata['stj_news']);
				$data['stj_hot'] = intval($postdata['stj_hot']);
				$data['stj_soldout'] = intval($postdata['stj_soldout']);
				$data['istag'] = intval($postdata['istag']);
				$data['ishits'] = intval($postdata['ishits']);
				$data['isshort'] = intval($postdata['isshort']);
				$data['iscanhide'] = intval($postdata['iscanhide']);
				$data['isprice'] = intval($postdata['isprice']);
				$data['isprice2'] = intval($postdata['isprice2']);
				$data['isdescription'] = intval($postdata['isdescription']);
				$data['islink'] = intval($postdata['islink']);
				$data['islinkpic'] = intval($postdata['islinkpic']);
				$data['isfilter2'] = intval($postdata['isfilter2']);
				$data['isposttime'] = intval($postdata['isposttime']);
				$data['iscato'] = intval($postdata['iscato']);
				$data['iscatostitle'] = intval($postdata['iscatostitle']);
				$data['iscatopic'] = intval($postdata['iscatopic']);
				$data['iscatodesc'] = intval($postdata['iscatodesc']);
				$data['iscatoinfo'] = intval($postdata['iscatoinfo']);
				$data['cato_thumb_width'] = intval($postdata['cato_thumb_width']);
				$data['cato_thumb_height'] = intval($postdata['cato_thumb_height']);
				$data['iscato2'] = intval($postdata['iscato2']);
				$data['ishtml'] = intval($postdata['ishtml']);
				if ($postdata['extinput']) {
					$data['extinput'] = $postdata['extinput'];
				}else{
					$data['extinput'] = "";
				}
				$data["is_extfield"] = intval($postdata["is_extfield"]);
				$data['frontdisplay'] = intval($postdata['frontdisplay']);
				$data['displaylist'] = str_replace("{tplpath}/", "", $postdata['displaylist']);;
				$data['displaydetail'] = str_replace("{tplpath}/", "", $postdata['displaydetail']);
				if ($postdata['freeother']) {
					$data['freeother'] = $postdata['freeother'];
				}else{
					$data['freeother'] = "";
				}
				if ($postdata['freeother2']) {
					$data['freeother2'] = $postdata['freeother2'];
				}else{
					$data['freeother2'] = "";
				}
				if ($postdata['freeother3']) {
					$data['freeother3'] = $postdata['freeother3'];
				}else{
					$data['freeother3'] = "";
				}
				if ($postdata['outlink']) {
					$data['outlink'] = $postdata['outlink'];
				}else{
					$data['outlink'] = "";
				}
				$data['linkfield'] = intval($postdata['linkfield']);
				$data['remark'] = $postdata['remark'];
				$data['columnorder'] = intval($postdata['columnorder']);
				$data['is_config'] = intval($postdata['is_config']);
				/*
				if ($data['parentid'] && $articlestyle->find(array("mark"=>$data['mark'], "sid <>"=>$id))) {
					$message = array('msg'=>"标识 ".$data['mark']." 已被占用" ,'result'=>-1);	
					echo json_encode($message);
					exit();
				}
				*/
				if($result = $articlestyle->spValidator($data)) {
					foreach($result as $item) {
						$msg .= implode('<br>', $item) . '<br>';
					}
				}
				if($msg) {
					if('1' == $isAjax) {
						$message = array('msg' => $msg, 'result'=>-1);
						echo json_encode($message);
						exit();
					}
					else {
						$this->errMsg = $msg;
					}
				}
				else {
					$condition = array("sid"=>$id);
					if($articlestyle->update($condition, $data)) {
						$url = spUrl("column", "columnlist");
						if('1' == $isAjax) {
							$message = array('msg'=>'修改成功！','result'=>0, 'url'=>$url);
							echo json_encode($message);
							exit();
						}
						else {
							$this->redirect($url);
						}
					}
					else {
						$msg = '修改失败！';
						if('1' == $isAjax) {
							$message = array('msg'=>$msg ,'result'=>-1);	
							echo json_encode($message);
							exit();
						}
						else {
							$this->errMsg = $msg;
						}
					}
				}
			}
			$list_rs = $articlestyle->findAll(array("parentid"=>0), "columnorder asc");
			$this->list_rs = $list_rs;
			$column_rs = $articlestyle->findByPk($id);
			$this->column_rs = $column_rs;
			$this->validator = $articlestyle->getValidatorJS();
			$this->id = $id;
		    $this->saveurl = spUrl("column", "updatecolumn");
		    $this->display("column/createcolumn.html");	
		}else{
			$this->redirect(spUrl("column", "columnlist"), "非法操作！");
		}
	}
	
	function deletecolumn(){
		$id = $this->spArgs('id');
		if($id){
			$articlestyle = spClass('articlestyle');
			$products = spClass('article');
			$productcato = spClass('articlecato');
			$obj_extgroup = spClass('article_extgroup');
			$obj_extfield = spClass('article_extfield');
			$obj_extval = spClass('article_extval');
			$url = spUrl("column", "columnlist");
			if(is_array($id)){
				foreach ($id as $v){
					if ($articlestyle->find(array("parentid"=>$v))) continue;
					$articlestyle->delete(array('sid'=>$v));
					$products->del_article($v);
					$productcato->delete(array("markid"=>$v));
					$obj_extgroup->delete(array("column_id"=>$v));
					$obj_extfield->delete(array("column_id"=>$v));
				}	
				echo json_encode(array('msg'=>'删除成功！' , 'result'=>0, 'url'=>$url));
				exit();
			}
			else {
				if ($articlestyle->find(array("parentid"=>$id))){
					echo json_encode(array('msg'=>'删除失败,该栏目下存在子栏目！' , 'result'=>-1, 'url'=>$url));
					exit();
				}
				if($articlestyle->delete(array('sid'=>$id))){
					$products->del_article($id);
					$productcato->delete(array("markid"=>$id));
					$obj_extgroup->delete(array("column_id"=>$id));
					$obj_extfield->delete(array("column_id"=>$id));
					echo json_encode(array('msg'=>'删除成功！' , 'result'=>0, 'url'=>$url));
					exit();
				}
				echo json_encode(array('msg'=>'删除失败！' , 'result'=>-1, 'url'=>$url));
				exit();
			}
		}else{
			echo json_encode(array('msg'=>'请先选择删除项，再进行操作！' , 'result'=>-2));
			exit();
		}
	}
	
	public function coverconfig(){
		try {
			$articlestyle = spClass('articlestyle');
			if(!$column_id = intval($this->spArgs('column_id')))
				throw new Exception("栏目id丢失");
			if(!$column_rs = $articlestyle->findByPk($column_id))
				throw new Exception("找不到该栏目，可能已被删除");
			$this->column_id = $column_rs["sid"];
			$this->column_rs = $column_rs;
		}catch(Exception $e){
			$this->redirect(spUrl("column", "columnlist"), $e->getMessage());
			exit();
		}
		$obj_cover = spClass('article_coverconfig');
		$this->cover_rs = $obj_cover->findAll(array("column_id"=>$column_id), 'sort asc, id asc');
	}
	
	public function coverconfig_create(){
		try {
			$articlestyle = spClass('articlestyle');
			if(!$column_id = intval($this->spArgs('column_id')))
				throw new Exception("栏目id丢失");
			if(!$column_rs = $articlestyle->findByPk($column_id))
				throw new Exception("找不到该栏目，可能已被删除");
			$this->column_id = $column_rs["sid"];
			$this->column_rs = $column_rs;
		}catch(Exception $e){
			$this->redirect(spUrl("column", "columnlist"), $e->getMessage());
			exit();
		}
		$obj_cover = spClass('article_coverconfig');
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			try{
				$postdata = $this->spArgs();
				$data = array();
				//客户端提交方法
				$isAjax = $postdata['isAjax'];
				$data['column_id'] = $postdata['column_id'];
				$data['cover_name'] = $postdata['cover_name'];
				$data['cover_mark'] = $postdata['cover_mark'];
				$data['thumb_width'] = intval($postdata['thumb_width']);
				$data['thumb_height'] = intval($postdata['thumb_height']);
				$data['sort'] = $postdata['sort'];
				if($result = $obj_cover->spValidator($data)) {
					foreach($result as $item) {
						throw new Exception($item[0]);
					}
				}
				if(!$obj_cover->create($data))
					throw new Exception("多图设置添加失败");
				$url = spUrl("column", "coverconfig", array("column_id"=>$column_id));
				$message = array('msg'=>'多图设置添加成功！','result'=>0, 'url'=>$url);
				echo json_encode($message);
				exit();
			}catch(Exception $e){
				$message = array('msg'=>$e->getMessage() ,'result'=>-1);
				echo json_encode($message);
				exit();
			}
	
		}
		$this->validator = $obj_cover->getValidatorJS();
		$this->saveurl = spUrl("column", "coverconfig_create");
	}
	
	public function coverconfig_modify(){
		try {
			$articlestyle = spClass('articlestyle');
			if(!$column_id = intval($this->spArgs('column_id')))
				throw new Exception("栏目id丢失");
			if(!$column_rs = $articlestyle->findByPk($column_id))
				throw new Exception("找不到该栏目，可能已被删除");
			$this->column_id = $column_rs["sid"];
			$this->column_rs = $column_rs;
			$obj_cover = spClass('article_coverconfig');
			if(!$id = intval($this->spArgs('id')))
				throw new Exception("id丢失");
			if(!$cover_rs = $obj_cover->findByPk($id))
				throw new Exception("找不到该多图设置");
		}catch(Exception $e){
			$this->redirect(spUrl("column", "columnlist"), $e->getMessage());
			exit();
		}
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			try{
				$postdata = $this->spArgs();
				$data = array();
				//客户端提交方法
				$data['cover_name'] = $postdata['cover_name'];
				$data['cover_mark'] = $postdata['cover_mark'];
				$data['thumb_width'] = intval($postdata['thumb_width']);
				$data['thumb_height'] = intval($postdata['thumb_height']);
				$data['sort'] = $postdata['sort'];
				if($result = $obj_cover->spValidator($data)) {
					foreach($result as $item) {
						throw new Exception($item[0]);
					}
				}
				if(!$obj_cover->update(array("id"=>$id), $data))
					throw new Exception("多图设置修改失败");
				$url = spUrl("column", "coverconfig", array("column_id"=>$column_id));
				$message = array('msg'=>'多图设置修改成功！','result'=>0, 'url'=>$url);
				echo json_encode($message);
				exit();
			}catch(Exception $e){
				$message = array('msg'=>$e->getMessage() ,'result'=>-1);
				echo json_encode($message);
				exit();
			}
		}
		$this->id = $id;
		$this->cover_rs = $cover_rs;
		$this->validator = $obj_cover->getValidatorJS();
		$this->saveurl = spUrl("column", "coverconfig_modify");
		$this->display("column/coverconfig_create.html");
	}
	
	public function coverconfig_delete(){
		try {
			$articlestyle = spClass('articlestyle');
			if(!$column_id = intval($this->spArgs('column_id')))
				throw new Exception("栏目id丢失");
			if(!$column_rs = $articlestyle->findByPk($column_id))
				throw new Exception("找不到该栏目，可能已被删除");
		}catch(Exception $e){
			$this->redirect(spUrl("column", "columnlist"), $e->getMessage());
			exit();
		}
		$obj_cover = spClass('article_coverconfig');
		$photo = spClass('photos');
		$id = $this->spArgs('id');
		if($id){
			$url = spUrl("column", "coverconfig", array("column_id"=>$column_id));
			if(is_array($id)){
				foreach ($id as $v){
					$conditions = array('id' => $v);
					$obj_cover->delete($conditions);
					$photo->delete(array("cover_id"=>$v));
				}
				echo json_encode(array('msg'=>'删除成功！' , 'result'=>0, 'url'=>$url));
				exit();
			}
			else {
				$conditions = array('id' => $id);
				if($obj_cover->delete($conditions)){
					$photo->delete(array("cover_id"=>$id));
					echo json_encode(array('msg'=>'删除成功！' , 'result'=>0, 'url'=>$url));
					exit();
				}
				echo json_encode(array('msg'=>'删除失败！' , 'result'=>-1, 'url'=>$url));
				exit();
			}
		}
		echo json_encode(array('msg'=>'请先选择删除项，再进行操作！', 'result'=>-2));
		exit();
	}

	public function extgroup(){
		try {
			$articlestyle = spClass('articlestyle');
			if(!$column_id = intval($this->spArgs('column_id')))
				throw new Exception("栏目id丢失");
			if(!$column_rs = $articlestyle->findByPk($column_id))
				throw new Exception("找不到该栏目，可能已被删除");
			$this->column_id = $column_rs["sid"];
			$this->column_rs = $column_rs;
		}catch(Exception $e){
			$this->redirect(spUrl("column", "columnlist"), $e->getMessage());
			exit();
		}
		$obj_extgroup = spClass('article_extgroup');
		$this->group_rs = $obj_extgroup->findAll(array("column_id"=>$column_id), 'sort asc, id asc');
	}
	
	public function extgroup_create(){
		try {
			$articlestyle = spClass('articlestyle');
			if(!$column_id = intval($this->spArgs('column_id')))
				throw new Exception("栏目id丢失");
			if(!$column_rs = $articlestyle->findByPk($column_id))
				throw new Exception("找不到该栏目，可能已被删除");
			$this->column_id = $column_rs["sid"];
			$this->column_rs = $column_rs;
		}catch(Exception $e){
			$this->redirect(spUrl("column", "columnlist"), $e->getMessage());
			exit();
		}
		$obj_extgroup = spClass('article_extgroup');
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			try{
				$postdata = $this->spArgs();
				$data = array();
				//客户端提交方法
				$isAjax = $postdata['isAjax'];
				$data['column_id'] = $postdata['column_id'];
				$data['group_name'] = $postdata['group_name'];
				$data['sort'] = $postdata['sort'];
				if($result = $obj_extgroup->spValidator($data)) {
					foreach($result as $item) {
						throw new Exception($item[0]);
					}
				}
				if(!$obj_extgroup->create($data))
					throw new Exception("扩展字段组添加失败");
				$url = spUrl("column", "extgroup", array("column_id"=>$column_id));
				$message = array('msg'=>'扩展字段组添加成功！','result'=>0, 'url'=>$url);
				echo json_encode($message);
				exit();
			}catch(Exception $e){
				$message = array('msg'=>$e->getMessage() ,'result'=>-1);
				echo json_encode($message);
				exit();
			}
				
		}
		$this->validator = $obj_extgroup->getValidatorJS();
		$this->saveurl = spUrl("column", "extgroup_create");
	}
	
	public function extgroup_modify(){
		try {
			$articlestyle = spClass('articlestyle');
			if(!$column_id = intval($this->spArgs('column_id')))
				throw new Exception("栏目id丢失");
			if(!$column_rs = $articlestyle->findByPk($column_id))
				throw new Exception("找不到该栏目，可能已被删除");
			$this->column_id = $column_rs["sid"];
			$this->column_rs = $column_rs;
			$obj_extgroup = spClass('article_extgroup');
			if(!$id = intval($this->spArgs('id')))
				throw new Exception("id丢失");
			if(!$extgroup_rs = $obj_extgroup->findByPk($id))
				throw new Exception("找不到该分组");
		}catch(Exception $e){
			$this->redirect(spUrl("column", "columnlist"), $e->getMessage());
			exit();
		}
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			try{
				$postdata = $this->spArgs();
				$data = array();
				//客户端提交方法
				$data['group_name'] = $postdata['group_name'];
				$data['sort'] = $postdata['sort'];
				if($result = $obj_extgroup->spValidator($data)) {
					foreach($result as $item) {
						throw new Exception($item[0]);
					}
				}
				if(!$obj_extgroup->update(array("id"=>$id), $data))
					throw new Exception("扩展字段组修改失败");
				$url = spUrl("column", "extgroup", array("column_id"=>$column_id));
				$message = array('msg'=>'扩展字段组修改成功！','result'=>0, 'url'=>$url);
				echo json_encode($message);
				exit();
			}catch(Exception $e){
				$message = array('msg'=>$e->getMessage() ,'result'=>-1);
				echo json_encode($message);
				exit();
			}
		}
		$this->id = $id;
		$this->extgroup_rs = $extgroup_rs;
		$this->validator = $obj_extgroup->getValidatorJS();
		$this->saveurl = spUrl("column", "extgroup_modify");
		$this->display("column/extgroup_create.html");
	}
	
	public function extgroup_delete(){
		try {
			$articlestyle = spClass('articlestyle');
			if(!$column_id = intval($this->spArgs('column_id')))
				throw new Exception("栏目id丢失");
			if(!$column_rs = $articlestyle->findByPk($column_id))
				throw new Exception("找不到该栏目，可能已被删除");
		}catch(Exception $e){
			$this->redirect(spUrl("column", "columnlist"), $e->getMessage());
			exit();
		}
		$obj_extgroup = spClass('article_extgroup');
		$obj_extfield = spClass('article_extfield');
		$obj_extval = spClass('article_extval');
		$id = $this->spArgs('id');
		if($id){
			$url = spUrl("column", "extgroup", array("column_id"=>$column_id));
			if(is_array($id)){
				foreach ($id as $v){
					$conditions = array('id' => $v);
					if($field_rs = $obj_extfield->findAll(array("group_id"=>$v), null, "id")){
						foreach($field_rs as $val){
							$obj_extval->delete(array("field_id"=>$val["id"]));
						}
					}
					$obj_extgroup->delete($conditions);
					$obj_extfield->delete(array("group_id"=>$v));
				}
				echo json_encode(array('msg'=>'删除成功！' , 'result'=>0, 'url'=>$url));
				exit();
			}
			else {
				$conditions = array('id' => $id);
				if($obj_extgroup->delete($conditions)){
					if($field_rs = $obj_extfield->findAll(array("group_id"=>$id), null, "id")){
						foreach($field_rs as $val){
							$obj_extval->delete(array("field_id"=>$val["id"]));
						}
					}
					$obj_extfield->delete(array("group_id"=>$id));
					echo json_encode(array('msg'=>'删除成功！' , 'result'=>0, 'url'=>$url));
					exit();
				}
				echo json_encode(array('msg'=>'删除失败！' , 'result'=>-1, 'url'=>$url));
				exit();
			}
		}
		echo json_encode(array('msg'=>'请先选择删除项，再进行操作！', 'result'=>-2));
		exit();
	}
	
	public function extfield(){
		try {
			$articlestyle = spClass('articlestyle');
			$obj_extgroup = spClass('article_extgroup');
			$obj_extfield = spClass('article_extfield');
			if(!$column_id = intval($this->spArgs('column_id')))
				throw new Exception("栏目id丢失");
			if(!$column_rs = $articlestyle->findByPk($column_id))
				throw new Exception("找不到该栏目，可能已被删除");
			if(!$group_id = intval($this->spArgs('group_id')))
				throw new Exception("字段组id丢失");
			if(!$group_rs = $obj_extgroup->findByPk($group_id))
				throw new Exception("找不到该字段组，可能已被删除");
			$this->column_id = $column_rs["sid"];
			$this->column_rs = $column_rs;
			$this->group_id = $group_rs["id"];
			$this->group_rs = $group_rs;
		}catch(Exception $e){
			$this->redirect(spUrl("column", "columnlist"), $e->getMessage());
			exit();
		}
		$this->field_rs = $obj_extfield->join("crm_articlesextfieldtype")->findAll(array("crm_articlesextfield.group_id"=>$group_id), 'crm_articlesextfield.sort asc, crm_articlesextfield.id asc', "crm_articlesextfield.*, crm_articlesextfieldtype.type_name, crm_articlesextfieldtype.type_mark");
	}
	
	public function extfield_create(){
		try {
			$articlestyle = spClass('articlestyle');
			$obj_extgroup = spClass('article_extgroup');
			$obj_extfield = spClass('article_extfield');
			$obj_exttype = spClass("article_extfieldtype");
			if(!$column_id = intval($this->spArgs('column_id')))
				throw new Exception("栏目id丢失");
			if(!$column_rs = $articlestyle->findByPk($column_id))
				throw new Exception("找不到该栏目，可能已被删除");
			if(!$group_id = intval($this->spArgs('group_id')))
				throw new Exception("字段组id丢失");
			if(!$group_rs = $obj_extgroup->findByPk($group_id))
				throw new Exception("找不到该字段组，可能已被删除");
			$this->column_id = $column_rs["sid"];
			$this->column_rs = $column_rs;
			$this->group_id = $group_rs["id"];
			$this->group_rs = $group_rs;
			$this->exttype_rs = $obj_exttype->getlist();
		}catch(Exception $e){
			$this->redirect(spUrl("column", "columnlist"), $e->getMessage());
			exit();
		}
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			try{
				$postdata = $this->spArgs();
				$data = array();
				//客户端提交方法
				$isAjax = $postdata['isAjax'];
				$data['column_id'] = intval($postdata['column_id']);
				$data['group_id'] = intval($postdata['group_id']);
				$data['field_mark'] = $postdata['field_mark'];
				$data['field_name'] = $postdata['field_name'];
				$data['field_content'] = $postdata['field_content'];
				$data['field_class'] = $postdata['field_class'];
				$data['field_type'] = intval($postdata['field_type']);
				$data['istolist'] = intval($postdata['istolist']);
				$data['ishide'] = intval($postdata['ishide']);
				$data['sort'] = $postdata['sort'];
				if($result = $obj_extfield->spValidator($data)) {
					foreach($result as $item) {
						throw new Exception($item[0]);
					}
				}
				if(!$obj_extfield->create($data))
					throw new Exception("扩展字段添加失败");
				$url = spUrl("column", "extfield", array("column_id"=>$column_id, "group_id"=>$group_id));
				$message = array('msg'=>'扩展字段添加成功！','result'=>0, 'url'=>$url);
				echo json_encode($message);
				exit();
			}catch(Exception $e){
				$message = array('msg'=>$e->getMessage() ,'result'=>-1);
				echo json_encode($message);
				exit();
			}
		}
		$this->validator = $obj_extfield->getValidatorJS();
		$this->saveurl = spUrl("column", "extfield_create");
	}
	
	public function extfield_modify(){
		try {
			$articlestyle = spClass('articlestyle');
			$obj_extgroup = spClass('article_extgroup');
			$obj_extfield = spClass('article_extfield');
			$obj_exttype = spClass("article_extfieldtype");
			if(!$column_id = intval($this->spArgs('column_id')))
				throw new Exception("栏目id丢失");
			if(!$column_rs = $articlestyle->findByPk($column_id))
				throw new Exception("找不到该栏目，可能已被删除");
			if(!$group_id = intval($this->spArgs('group_id')))
				throw new Exception("字段组id丢失");
			if(!$group_rs = $obj_extgroup->findByPk($group_id))
				throw new Exception("找不到该字段组，可能已被删除");
			if(!$id = intval($this->spArgs('id')))
				throw new Exception("id丢失");
			if(!$extfield_rs = $obj_extfield->findByPk($id))
				throw new Exception("找不到该字段");
			$this->column_id = $column_rs["sid"];
			$this->column_rs = $column_rs;
			$this->group_id = $group_rs["id"];
			$this->group_rs = $group_rs;
			$this->exttype_rs = $obj_exttype->getlist();
		}catch(Exception $e){
			$this->redirect(spUrl("column", "columnlist"), $e->getMessage());
			exit();
		}
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			try{
				$postdata = $this->spArgs();
				$data = array();
				//客户端提交方法
				$data['column_id'] = intval($postdata['column_id']);
				$data['group_id'] = intval($postdata['group_id']);
				$data['field_mark'] = $postdata['field_mark'];
				$data['field_name'] = $postdata['field_name'];
				$data['field_content'] = $postdata['field_content'];
				$data['field_class'] = $postdata['field_class'];
				$data['field_type'] = intval($postdata['field_type']);
				$data['istolist'] = intval($postdata['istolist']);
				$data['ishide'] = intval($postdata['ishide']);
				$data['sort'] = $postdata['sort'];
				if($result = $obj_extfield->spValidator($data)) {
					foreach($result as $item) {
						throw new Exception($item[0]);
					}
				}
				if(!$obj_extfield->update(array("id"=>$id), $data))
					throw new Exception("扩展字段修改失败");
				$url = spUrl("column", "extfield", array("column_id"=>$column_id, "group_id"=>$group_id));
				$message = array('msg'=>'扩展字段修改成功！','result'=>0, 'url'=>$url);
				echo json_encode($message);
				exit();
			}catch(Exception $e){
				$message = array('msg'=>$e->getMessage() ,'result'=>-1);
				echo json_encode($message);
				exit();
			}
		}
		$this->id = $id;
		$this->extfield_rs = $extfield_rs;
		$this->validator = $obj_extfield->getValidatorJS();
		$this->saveurl = spUrl("column", "extfield_modify");
		$this->display("column/extfield_create.html");
	}
	
	public function extfield_delete(){
		try {
			$articlestyle = spClass('articlestyle');
			$obj_extgroup = spClass('article_extgroup');
			$obj_extfield = spClass('article_extfield');
			if(!$column_id = intval($this->spArgs('column_id')))
				throw new Exception("栏目id丢失");
			if(!$column_rs = $articlestyle->findByPk($column_id))
				throw new Exception("找不到该栏目，可能已被删除");
			if(!$group_id = intval($this->spArgs('group_id')))
				throw new Exception("字段组id丢失");
			if(!$group_rs = $obj_extgroup->findByPk($group_id))
				throw new Exception("找不到该字段组，可能已被删除");
		}catch(Exception $e){
			$this->redirect(spUrl("column", "columnlist"), $e->getMessage());
			exit();
		}
		$id = $this->spArgs('id');
		if($id){
			$url = spUrl("column", "extfield", array("column_id"=>$column_id, "group_id"=>$group_id));
			if(is_array($id)){
				foreach ($id as $v){
					$conditions = array('id' => $v);
					$obj_extfield->delete($conditions);
				}
				echo json_encode(array('msg'=>'删除成功！' , 'result'=>0, 'url'=>$url));
				exit();
			}
			else {
				$conditions = array('id' => $id);
				if($obj_extfield->delete($conditions)){
					echo json_encode(array('msg'=>'删除成功！' , 'result'=>0, 'url'=>$url));
					exit();
				}
				echo json_encode(array('msg'=>'删除失败！' , 'result'=>-1, 'url'=>$url));
				exit();
			}
		}
		echo json_encode(array('msg'=>'请先选择删除项，再进行操作！', 'result'=>-2));
		exit();
	}
}
?>