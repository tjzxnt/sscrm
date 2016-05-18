<?php
/*
 * 模块功能: 内容管理
 * 编写人:   张鑫
*/

class articles extends spController {
	
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
	
	//作品列表
	function articleslist(){
		$articles = spClass('article');
		$articlestyle = spClass('articlestyle');
		$obj_cover = spClass('article_coverconfig');
		$obj_extfield = spClass('article_extfield');
		$postdate = $this->spArgs();
		$fid = intval($postdate['fid']);
		$sid = intval($postdate['sid']);
		$pc_id = intval($postdate['pc_id']);
		$line_id = intval($postdate['line_id']);
		$articleft = $articlestyle->find(array('sid'=>$fid), '', 'sname');
		$atticlest = $articlestyle->find(array('sid'=>$sid));
		if($atticlest['islist'] == 0){
			if($listprs = $articles->find(array('ptype'=>$sid))){
				$this->jump(spUrl('articles', 'updatearticles', array('id'=>$listprs['pid'],'fid' => $fid,'sid' => $sid)));
			}else{
				$this->jump(spUrl('articles', 'createarticles', array('fid' => $fid,'sid' => $sid)));
			}
		}
		if($atticlest['iscato'] == '2'){
			$this->catoid = $pc_id;
			$this->catourl = "&catoid=$pc_id";
		}
		$condition = $articlestyle->init_condition($atticlest);
		$page = intval(max($postdate['page'], 1));
		$condition .=  $postdate['searchkeys'] ? " and name like'%{$postdate[searchkeys]}%' and ptype = $sid" : " and ptype = $sid";
		if($postdate['isorder'] == '1'){
			$orderval = $postdate['order'];
			$orderid = $postdate['orderid'];
			for($ii = 0;$ii < (count($orderval)>count($orderid)?count($orderid):count($orderval)); $ii++){
				if (is_numeric($orderid[$ii]) && is_numeric($orderval[$ii]) && $orderval[$ii]>0)
					$articles->update(array('pid' => $orderid[$ii]), array('order'=>$orderval[$ii]));
			}
			$jumpurl = spUrl('articles', 'articleslist', array('fid'=>$fid, 'sid'=>$sid));
			$jumpurl = $_SERVER['HTTP_REFERER'];
			$this->jump($jumpurl);
		}
		if ($pc_id) {
			if($atticlest['isfilter2']){
				$condition .= " and find_in_set($pc_id, filter2_id)";
				$articlecato = spClass('articlecato');
				if ($articlecato_rs = $articlecato->find(array('pc_id'=>$pc_id, 'markid'=>$sid))) {
					$condition .= " and find_in_set($pc_id, filter2_id)";
					$this->pc_name = $articlecato_rs['pc_name'];
				}
			}else{
				$articlecato = spClass('articlecato');
				if ($articlecato_rs = $articlecato->find(array('pc_id'=>$pc_id, 'markid'=>$sid))) {
					$condition .= " and crm_articles.pc_id = $pc_id";
					$this->pc_name = $articlecato_rs['pc_name'];
				}
			}
		}
		$this->ext_cfield = $obj_extfield->getTolist($sid);
		if($line_id)
			$condition .= " and crm_articles.entrance_line_id = $line_id";
		import("Common.php");
		$sort = "crm_articles.`top` desc, crm_articles.`recomd` desc, crm_articles.stj_hot desc, crm_articles.stj_news desc, crm_articles.stj_soldout asc, crm_articles.`specrecomd` desc, crm_articles.`order` asc, crm_articles.`status` desc, crm_articles.`posttime` desc, crm_articles.`pid` desc";
		if ($atticlest['iscato']) {
			$articles_rs = $articles->join("crm_articlescato", null, "left")->spPager($page, 15)->findAll($condition, $sort, "crm_articles.*, crm_articlescato.pc_name");
			$this->iscato = 1;
			$articlecato = spClass('articlecato');
			$this->all_pc_rs = $articlecato->findAll(array('markid'=>$sid, 'fid'=>0), "`is_recommend` desc, `order` asc");
		}elseif ($atticlest['iscato2']){
			$articles_rs = $articles->join("crm_articlescato", null, "left")->spPager($page, 15)->findAll($condition, $sort, "crm_articles.*, crm_articlescato.pc_name, crm_articlescato.fid");
			$articlecato = spClass('articlecato');
			$temp_all_pc_rs = $articlecato->findAll(array('markid'=>$sid));
			$this->all_pc_rs = Common::TypeList($list_type, $temp_all_pc_rs, "pc_id", 0, 1, "fid");
			$temp_pc_rs = $articlecato->findAll(array('markid'=>$sid, 'fid'=>0));
			foreach ($temp_pc_rs as $key => $val){
				$pc_key = $val['pc_id'];
				$pc_rs[$pc_key] = $val;
			}
			if($articles_rs){
				if($atticlest['isfilter2']){
					for ($i=0; $i<count($articles_rs); $i++){
						if($tempfilter = $articlecato->findAll("pc_id in(".$articles_rs[$i]['filter2_id'].")", "pc_id asc", "pc_name")){
							foreach ($tempfilter as $key => $val){
								$articles_rs[$i]['filterstr'] .= $key ? "," . $val['pc_name'] : $val['pc_name'];
							}
						}
					}
				}else{
					for ($i=0; $i<count($articles_rs); $i++){
						$getkey = $articles_rs[$i]['fid'];
						$articles_rs[$i]['pc_name'] = $pc_rs[$getkey]['pc_name']."-".$articles_rs[$i]['pc_name'];
					}
				}
				
			}
			$articles_rs = $articles_rs;
			$this->iscato = 2;
			$this->filter2 = $atticlest['isfilter2'];
		}else{
			$articles_rs = $articles->spPager($page, 15)->findAll($condition, $sort);
		}
		if($articles_rs && $articlestyle->check_line($atticlest)){
			foreach($articles_rs as $key => $val){
				if($line_rs = $articles->getline($val["entrance_line_id"]))
					$articles_rs[$key]["line_name"] = $line_rs["name"];
			}
		}
		if($articles_rs && $this->ext_cfield){
			foreach($articles_rs as $key => $val){
				foreach($this->ext_cfield as $k => $v){
					$articles_rs[$key]["extfield"][$v[field_mark]] = $articles->getExtfield($val["pid"], $v[field_mark]);
				}
			}
		}
		$this->articles_rs = $articles_rs;
		if ($atticlest['order']) $this->order = 1;
		if ($atticlest['subcolumn']) $this->subcolumn = 1;
		if ($atticlest['relatestr']) $this->isrelate = 1;
		if ($atticlest['ishits']) $this->ishits = 1;
		if($atticlest['iscover']){
			if($cover_rs = $obj_cover->findAll(array("column_id"=>$sid), "sort asc", "id, cover_name")){
				foreach($cover_rs as $val){
					$cover_str .= '<a href="'.spUrl("photo", "manage", array("pid"=>"__pid__", "fid"=>$fid, "sid"=>$sid, "cid"=>$val['id'])). '"><img src="images/edt.gif" width="16" height="16" align="absmiddle"/>'.$val["cover_name"].'</a>';
				}
				$this->cover_str = $cover_str;
			}
		}
		/*
		$allcolumn = $articlestyle->findAll(array("frontdisplay"=>1, "islist"=>1));
		$this->allcolumn_rs = Common::TypeList($list_type, $allcolumn, "sid", 0, 1, "parentid");
		*/
		//dump($this->user_rs);
		$this->pager = $articles->spPager()->getPager();
		//dump($this->admin_rs);
		$this->url = spUrl('articles', 'articleslist', array('fid'=>$fid, 'sid'=>$sid, 'pc_id'=>$pc_id));
		$this->ordersub = spUrl('articles','articleslist');
		$this->searchkeys = $postdate['searchkeys'];
		$this->fname = $articleft['sname'];
		$this->sname = $atticlest['sname'];
		$this->artstyle = $atticlest;
		$this->fid = $fid;
		$this->sid = $sid;
		$this->pc_id = $pc_id;
		$this->line_id = $line_id;
		$this->line_rs = $articlestyle->get_ent_line($atticlest);
	}
	
	//添加新闻
	function createarticles(){
		$articles = spClass("article");
		$articlestyle = spClass('articlestyle');
		$obj_extgroup = spClass('article_extgroup');
		$obj_extfield = spClass('article_extfield');
		$obj_extval = spClass('article_extval');
		import("Common.php");
		$postdate = $this->spArgs();
		$fid = intval($postdate['fid']);
		$sid = intval($postdate['sid']);
		$articleft = $articlestyle->find(array('sid'=>$fid), null, 'sname');
		$atticlest = $articlestyle->find(array('sid'=>$sid));
		if($atticlest['iscato'] == '2'){
			$this->catoid = intval($postdate["catoid"]);
			$this->catourl = "&pc_id={$postdate["catoid"]}";
		}
		if(!$sid){
			@header("location: ".spUrl("main", "welcome"));
			exit();
		}
		if ($atticlest['extinput']) {
			$inputarray = explode('|', $atticlest['extinput']);
			foreach ($inputarray as $ikey => $ival){
				$word = explode(',', $ival);
				$inputtype = $word[0];
				$inputword = $word[1];
				if (strtolower($inputtype) == "text") {
					$extarray[$inputword] = "<input type='".$inputtype."' name='extname[]'/>";
				}elseif(strtolower($inputtype) == "textarea"){
					$extarray[$inputword] = "<textarea  name='extname[]' cols='60' rows='10'></textarea>";
				}elseif(strtolower($inputtype) == "stext"){
					$extarray[$inputword] = "<textarea  name='extname[]' class='stext' style='height: 450px; width: 780px;'></textarea>";
				}elseif(strtolower($inputtype) == "checkbox" || strtolower($inputtype) == "radio"){
					$each = explode("@#", $word[2]);
					foreach ($each as $wk => $vv){
						$extarray[$inputword] .= "<input type='".$inputtype."' name='extname[check_$ikey][]' value='$vv'";
						$extarray[$inputword] .= $wk == 0 ? "checked" : "";
						$extarray[$inputword] .= "/>$vv ";
					}
				}
			}
			$atticlest['isext'] = 1;
		}
		if ($atticlest['iscato']) {
			$articlecato = spClass('articlecato');
			$articlecato_rs = $articlecato->findAll(array("markid"=>$sid, 'fid'=>0), '`order` asc');
			$this->articlecato_rs = $articlecato_rs;
		}
		if ($atticlest['iscato2']) {
			$articlecato = spClass('articlecato');
			$articlecato_temp = $articlecato->findAll(array("markid"=>$sid), '`order` asc');
			$articlecato_rs2 = Common::TypeList($list_type, $articlecato_temp, "pc_id", 0, 1, "fid");
			$this->articlecato_rs2 = $articlecato_rs2;
		}
		if ($atticlest['isfilter2']) {
			$productfilter = spClass('articlefilter');
			if($productfilter_rs = $productfilter->findAll(array("markid"=>$sid, "fid"=>0), '`order` asc')){
				foreach($productfilter_rs as $key => $val){
					$productfilter_rs[$key]["sub"] = $productfilter->findAll(array("markid"=>$sid, "fid"=>$val['pc_id']), '`order` asc');
				}
			}
			$this->productfilter_rs = $productfilter_rs;
		}
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			try {
				$data = array();
				$isAjax = $postdate['isAjax'];
				$data['entrance_id'] = $articlestyle->get_entid($atticlest);
				$data['name'] = $postdate['name'];
				$data['fname'] = $postdate['fname'];
				$data['author'] = $postdate['author'];
				$data['source'] = $postdate['source'];
				$data['tagstr'] = str_replace("，", ",", $postdate['tagstr']);
				$data["entrance_line_id"] = intval($postdate['entrance_line_id']);
				$data['metatitle'] = $postdate['metatitle'];
				$data['metakey'] = $postdate['metakey'];
				$data['metades'] = $postdate['metades'];
				$data['filter2'] = $postdate['filter2'];
				if ($atticlest['iscato']) {
					$data['pc_id'] = intval($postdate['pc_id']);
					if ($data['pc_id'] < 1)
						throw new Exception('请选择所属分类');
				}
				if ($atticlest['iscato2']) {
					$data['pc_id'] = intval($postdate['pc_id']);
					if ($data['pc_id'] < 1)
						throw new Exception('请选择所属分类');
					if (!$articlecato->find(array('pc_id'=>$data['pc_id'], 'fid <>'=>'0', 'markid'=>$sid)))
						throw new Exception('请选择下级分类');
				}
				if($atticlest['isfilter2']){
					if($data['filter2']){
						foreach($data['filter2'] as $key => $val){
							if(is_array($val))
								$data['filter2'][$key] = implode(",", $val);
						}
						$data['filter2_id'] = implode(",", $data['filter2']);
					}
				}
				unset($data['filter2']);
				$data['ishide'] = intval($postdate['ishide']);
				$data['price'] = abs(intval(str_replace(",", "", $postdate["price"])));
				$data['price2'] = abs(intval(str_replace(",", "", $postdate["price2"])));
				$data['short'] = $postdate['short'];
				if($postdate['description'] != '')
					$data['description'] = str_replace('../upload/', '/upload/', $postdate['description']);
				$data['link'] = $postdate['link'];
				$data['linkpic'] = $postdate['linkpic'];
				$data['top'] = $postdate['top'];
				$data['recomd'] = $postdate['recomd'];
				$data['specrecomd'] = intval($postdate['specrecomd']);
				if($postdate["stj"]){
					$data['stj_news'] = 0;
					$data['stj_hot'] = 0;
					$data['stj_soldout'] = 0;
					if($postdate["stj"] != "none")
						$data[$postdate["stj"]] = 1;
				}else{
					$data['stj_news'] = intval($postdate['stj_news']);
					$data['stj_hot'] = intval($postdate['stj_hot']);
					$data['stj_soldout'] = intval($postdate['stj_soldout']);
				}
				if($atticlest['subcolumn']){
					if(intval($atticlest['subcolumntype']) == 1){
						if (intval($postdate['subproduct'])){
							$subc_rs = $articlestyle->findByPk(intval($postdate['subproduct']), "parentid");
							if(!intval($subc_rs['parentid']))
								throw new Exception("从属栏目请选择二级栏目");
						}
					}
					$data['subproduct'] = $postdate['subproduct'];
				}
				$data['ptype'] = $postdate['ptype'];
				$data['posttime'] = $postdate['posttime'] ? $postdate['posttime'] : date("Y-m-d H:i:s");
				$data['status'] = 1;
				if ($data['link']) {
					if (substr($data['link'],0,7) !== 'http://')
						throw new Exception("链接地址请以http://开头！");
				}
				if ($data['linkpic']) {
					if (substr($data['linkpic'],0,7) !== 'http://')
						throw new Exception("图片链接地址请以http://开头！");
				}
				if ($postdate['extname']) $data['extinput'] = serialize($postdate['extname']);
				if($result = $articles->spValidator($data)) {
					foreach($result as $item) {
						throw new Exception($item[0]);
					}
				}
				if(!$this->verifypro($data['ptype']))
					throw new Exception("非法操作！");
				$articles->checkupfile("img");
				$articles->checkupfile("img2");
				$articles->checkupfile("img3");
				$articles->checkupfile("flv", array("flv"));
				$articles->checkupfile("package", array('rar', 'zip', 'pdf'));
				$articles->getDb()->beginTrans();
				if(!$id = $articles->create($data))
					throw new Exception("未知错误，添加失败！");
				$data = array();
				$upfile_data = array();
				if($imgdata = $articles->upfile("img", $id, $atticlest, array("picurlpath", "picurlfile"))){
					$data = array_merge($data, $imgdata["data"]);
					$upfile_data = array_merge($upfile_data, $imgdata["source"]);
				}
				if($img2data = $articles->upfile("img2", $id, $atticlest, array("picurlpath2", "picurlfile2"))){
					$data = array_merge($data, $img2data["data"]);
					$upfile_data = array_merge($upfile_data, $img2data["source"]);
				}
				if($img3data = $articles->upfile("img3", $id, $atticlest, array("picurlpath3", "picurlfile3"))){
					$data = array_merge($data, $img3data["data"]);
					$upfile_data = array_merge($upfile_data, $img3data["source"]);
				}
				if($flvdata = $articles->upfile("flv", $id, $atticlest, "flvurl", array("flv"))){
					$data = array_merge($data, $flvdata["data"]);
					$upfile_data = array_merge($upfile_data, $flvdata["source"]);
				}
				if($package_data = $articles->upfile("package", $id, $atticlest, "packageurl", array('rar', 'zip', 'pdf'))){
					$data = array_merge($data, $package_data["data"]);
					$upfile_data = array_merge($upfile_data, $package_data["source"]);
				}
				if($data)
					$articles->update(array("pid"=>$id), $data);
				if($atticlest["is_extfield"])
					$obj_extval->extval_update($postdate["extfield"], $atticlest["sid"], $id);
				$articles->getDb()->commitTrans();
				$url = spUrl("articles", "articleslist", array('fid'=>$fid, 'sid'=>$sid));
				if($atticlest['iscato'] == 2)
					$url .= "&pc_id={$postdate['pc_id']}";
				$message = array('msg'=>'添加成功！','result'=>0, 'url'=>$url);
				echo json_encode($message);
				exit();
			}catch(Exception $e){
				$articles->getDb()->rollbackTrans();
				if($upfile_data){
					foreach($upfile_data as $val)
						unlink($val);
				}
				$message = array('msg' => $e->getMessage(), 'result'=>-1);
				echo json_encode($message);
				exit();
			}
	    }
	    if ($atticlest['subcolumn']){
	    	switch ($atticlest['subcolumntype']) {
	    		case "0":
    				$this->sub_rs = $articles->findAll(array("ptype"=>$atticlest['subcolumn']), "top desc, recomd desc, specrecomd desc, `order` desc, `status` desc, `pid` desc", "pid, name");
    			break;
    			case "1":
    				if($sub_rs = $articlestyle->findAll("sid in(".$atticlest['subcolumn'].") and frontdisplay = 1", "columnorder asc, sid desc", "sid as pid, sname as name")){
    					foreach($sub_rs as $key => $val){
    						$sub_rs[$key]['subc'] = $articlestyle->findAll(array("parentid"=>$val['pid'], "frontdisplay"=>1), "columnorder asc, sid desc", "sid as pid, sname as name");
    					}
    				}
    				$this->sub_rs = $sub_rs;
    			break;
	    	}	    	
	    }
	    if($atticlest["is_extfield"])
	    	$this->ext_group_rs = $obj_extgroup->get_extfield_format($atticlest["sid"]);
	    $this->validator = $articles->getValidatorJS();
	    $this->saveurl = spUrl("articles", "createarticles");
		$this->fname = $articleft['sname'];
		$this->sname = $atticlest['sname'];
		$this->sconfig = $atticlest;
		$this->extarray = $extarray;
		$this->fid = $fid;
		$this->sid = $sid;
		$this->line_rs = $articlestyle->get_ent_line($atticlest);
	}
	
	//更新新闻
	function updatearticles(){
		$id = intval($this->spArgs('id'));
		try {
			if(!$id)
				throw new Exception("非法操作！");
			import("Common.php");
			$articles = spClass("article");
			$articlestyle = spClass('articlestyle');
			$obj_extgroup = spClass('article_extgroup');
			$obj_extfield = spClass('article_extfield');
			$obj_extval = spClass('article_extval');
			$obj_ent = spClass('entrance');
			$postdate = $this->spArgs();
			$fid = intval($postdate['fid']);
			$sid = intval($postdate['sid']);
			$articleft = $articlestyle->find(array('sid'=>$fid), null, 'sname');
			$atticlest = $articlestyle->find(array('sid'=>$sid));
			if($atticlest['iscato'] == '2'){
				$this->catoid = intval($postdate["catoid"]);
				$this->catourl = "&pc_id={$postdate["catoid"]}";
			}
			$condition = $articlestyle->init_condition($atticlest);
			$condition .= " and pid = $id";
			if(!$articles_rs = $articles->find($condition))
				throw new Exception("找不到该信息，可能已被删除");
			if($articles_rs['description'])
				$articles_rs['description'] = str_replace("/upload/", "../upload/", $articles_rs['description']);
			if($articles_rs['extinput'])
				$articles_rs['extinput'] = unserialize($articles_rs['extinput']);
			if ($atticlest['extinput']) {
				$inputarray = explode('|', $atticlest['extinput']);
				$offsetk = 0;
				foreach ($inputarray as $ikey => $ival){
					$ikey = $ikey + $offsetk;
					$word = explode(',', $ival);
					$inputtype = $word[0];
					$inputword = $word[1];
					if (strtolower($inputtype) == "text") {
						$extarray[$inputword] = "<input type='".$inputtype."' name='extname[]' value='".$articles_rs['extinput'][$ikey]."'/>";
					}elseif(strtolower($inputtype) == "textarea"){
						$extarray[$inputword] = "<textarea  name='extname[]' cols='60' rows='10'>".$articles_rs['extinput'][$ikey]."</textarea>";
					}elseif(strtolower($inputtype) == "stext"){
						$extarray[$inputword] = "<textarea  name='extname[]' class='stext' style='height: 450px; width: 780px;'>".$articles_rs['extinput'][$ikey]."</textarea>";
					}elseif(strtolower($inputtype) == "checkbox" || strtolower($inputtype) == "radio"){
						$each = explode("@#", $word[2]);
						foreach ($each as $wk => $vv){
							$extarray[$inputword] .= "<input type='".$inputtype."' name='extname[check_$ikey][]' value='$vv'";
							if(in_array($vv, $articles_rs['extinput']["check_".$ikey])) $extarray[$inputword] .= " checked";
							$extarray[$inputword] .= "/>$vv ";
						}
						$offsetk--;
					}
				}
				$atticlest['isext'] = 1;
			}
			if($articles_rs['filter2_id'])
				$articles_rs['filter2_array'] = explode(",", $articles_rs['filter2_id']);
			if($atticlest['is_extfield'])
				$articles_rs["extfield"] = $obj_extval->extval_get($id);
			$this->articles_rs = $articles_rs;
			if ($atticlest['iscato']) {
				$articlecato = spClass('articlecato');
				$articlecato_rs = $articlecato->findAll(array("markid"=>$sid, 'fid'=>0), '`order` asc');
				$this->articlecato_rs = $articlecato_rs;
			}
			if ($atticlest['iscato2']) {
				$articlecato = spClass('articlecato');
				$articlecato_temp = $articlecato->findAll(array("markid"=>$sid), '`order` asc');
				$articlecato_rs2 = Common::TypeList($list_type, $articlecato_temp, "pc_id", 0, 1, "fid");
				$this->articlecato_rs2 = $articlecato_rs2;
			}
			if ($atticlest['isfilter2']) {
				$productfilter = spClass('articlefilter');
				if($productfilter_rs = $productfilter->findAll(array("markid"=>$sid, "fid"=>0), '`order` asc')){
					foreach($productfilter_rs as $key => $val){
						$productfilter_rs[$key]["sub"] = $productfilter->findAll(array("markid"=>$sid, "fid"=>$val['pc_id']), '`order` asc');
					}
				}
				$this->productfilter_rs = $productfilter_rs;
			}
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try{
					$data = array();
					if($atticlest['subcolumn']){
						if(intval($atticlest['subcolumntype']) == 1){
							if (intval($postdate['subproduct'])){
								$subc_rs = $articlestyle->findByPk(intval($postdate['subproduct']), "parentid");
								if(!intval($subc_rs['parentid'])){
									$message = array('msg'=>"从属栏目请选择二级栏目" ,'result'=>-1);
									echo json_encode($message);
									exit();
								}
							}
						}
						$data['subproduct'] = $postdate['subproduct'];
					}
					//客户端提交方法
					$isAjax = $postdate['isAjax'];
					$data['name'] = $postdate['name'];
					$data['fname'] = $postdate['fname'];
					$data['author'] = $postdate['author'];
					$data['source'] = $postdate['source'];
					$data['tagstr'] = str_replace("，", ",", $postdate['tagstr']);
					$data["entrance_line_id"] = intval($postdate['entrance_line_id']);
					if($data["entrance_line_id"])
						$data["entrance_id"] = $obj_ent->getEntidByLineid($data["entrance_line_id"]);
					$data['filter2'] = $postdate['filter2'];
					if ($atticlest['iscato']) {
						$data['pc_id'] = intval($postdate['pc_id']);
						if ($data['pc_id'] < 1) 
							throw new Exception('请选择所属分类');
					}
					if ($atticlest['iscato2']) {
						$data['pc_id'] = intval($postdate['pc_id']);
						if ($data['pc_id'] < 1)
							throw new Exception('请选择所属分类');
						if (!$articlecato->find(array('pc_id'=>$data['pc_id'], 'fid <>'=>'0', 'markid'=>$sid)))
							throw new Exception('请选择下级分类');
					}
					if($atticlest['isfilter2']){
						if($data['filter2']){
							foreach($data['filter2'] as $key => $val){
								if(is_array($val))
									$data['filter2'][$key] = implode(",", $val);
							}
							$data['filter2_id'] = implode(",", $data['filter2']);
						}
					}
					unset($data['filter2']);
					$data['ishide'] = intval($postdate['ishide']);
					$data['price'] = abs(intval(str_replace(",", "", $postdate["price"])));
					$data['price2'] = abs(intval(str_replace(",", "", $postdate["price2"])));
					$data['short'] = $postdate['short'];
					$data['ptype'] = $postdate['ptype'];
					if($postdate['description'] != '')
						$data['description'] = str_replace('../upload/', '/upload/', $postdate['description']);
					$data['metatitle'] = $postdate['metatitle'];
					$data['metakey'] = $postdate['metakey'];
					$data['metades'] = $postdate['metades'];
					$data['link'] = strtolower($postdate['link']);
					$data['linkpic'] = strtolower($postdate['linkpic']);
					$data['top'] = $postdate['top'];
					$data['recomd'] = $postdate['recomd'];
					$data['specrecomd'] = intval($postdate['specrecomd']);
					if($postdate["stj"]){
						$data['stj_news'] = 0;
						$data['stj_hot'] = 0;
						$data['stj_soldout'] = 0;
						if($postdate["stj"] != "none")
							$data[$postdate["stj"]] = 1;
					}else{
						$data['stj_news'] = intval($postdate['stj_news']);
						$data['stj_hot'] = intval($postdate['stj_hot']);
						$data['stj_soldout'] = intval($postdate['stj_soldout']);
					}
					$data['subproduct'] = intval($postdate['subproduct']);
					$data['status'] = 1;
					if ($data['link']) {
						if (substr($data['link'],0,7) !== 'http://') 
							throw new Exception('链接地址请以http://开头！');
					}
					if ($data['linkpic']) {
						if (substr($data['linkpic'],0,7) !== 'http://')
							throw new Exception("图片链接地址请以http://开头！");
					}
					if ($postdate['extname'])
						$data['extinput'] = serialize($postdate['extname']);
					if($result = $articles->spValidatoredit()->spValidator($data)) {
						foreach($result as $item) {
							throw new Exception($item[0]);
						}
					}
					$data['top'] = $data['top'];
					$data['recomd'] = $data['recomd'];
					$data['specrecomd'] = $data['specrecomd'];
					if ($postdate['posttime'])
						$data['posttime'] = $postdate['posttime'];
					$upfile_data = $oralfile_data = array();
					if($imgdata = $articles->upfile("img", $id, $atticlest, array("picurlpath", "picurlfile"))){
						$data = array_merge($data, $imgdata["data"]);
						$upfile_data = array_merge($upfile_data, $imgdata["source"]);
						$oralfile_data = array_merge($oralfile_data, $imgdata["oralfile"]);
					}
					if($img2data = $articles->upfile("img2", $id, $atticlest, array("picurlpath2", "picurlfile2"))){
						$data = array_merge($data, $img2data["data"]);
						$upfile_data = array_merge($upfile_data, $img2data["source"]);
						$oralfile_data = array_merge($oralfile_data, $img2data["oralfile"]);
					}
					if($img3data = $articles->upfile("img3", $id, $atticlest, array("picurlpath3", "picurlfile3"))){
						$data = array_merge($data, $img3data["data"]);
						$upfile_data = array_merge($upfile_data, $img3data["source"]);
						$oralfile_data = array_merge($oralfile_data, $img3data["oralfile"]);
					}
					if($flvdata = $articles->upfile("flv", $id, $atticlest, "flvurl", array("flv"))){
						$data = array_merge($data, $flvdata["data"]);
						$upfile_data = array_merge($upfile_data, $flvdata["source"]);
						$oralfile_data = array_merge($oralfile_data, $flvdata["oralfile"]);
					}
					if($package_data = $articles->upfile("package", $id, $atticlest, "packageurl", array('rar', 'zip', 'pdf'))){
						$data = array_merge($data, $package_data["data"]);
						$upfile_data = array_merge($upfile_data, $package_data["source"]);
						$oralfile_data = array_merge($oralfile_data, $package_data["oralfile"]);
					}
					$articles->getDb()->beginTrans();
					if(!$articles->update(array('pid'=>$id), $data))
						throw new Exception("未知错误，修改失败！");
					if($atticlest["is_extfield"])
						$obj_extval->extval_update($postdate["extfield"], $atticlest["sid"], $id);
					$articles->getDb()->commitTrans();
					if($oralfile_data){
						foreach($oralfile_data as $val)
							unlink($val);
					}
					$url = spUrl("articles", "articleslist", array('fid'=>$fid, 'sid'=>$sid));
					if($atticlest['iscato'] == 2)
						$url .= "&pc_id={$postdate['pc_id']}";
					$message = array('msg'=>'修改成功！','result'=>0, 'url'=>$url);
					echo json_encode($message);
					exit();
				}catch(Exception $e){
					$articles->getDb()->rollbackTrans();
					if($upfile_data){
						foreach($upfile_data as $val)
							unlink($val);
					}
					$message = array('msg' => $e->getMessage(), 'result'=>-1);
					echo json_encode($message);
					exit();
				}
			}
		}catch(Exception $e){
			$this->redirect(spUrl("articles", "articleslist", array("fid"=>$fid, "sid"=>$sid)), $e->getMessage());
		}
   	 	if ($atticlest['subcolumn']){
	    	switch ($atticlest['subcolumntype']) {
	    		case "0":
    				$this->sub_rs = $articles->findAll(array("ptype"=>$atticlest['subcolumn']), "top desc, recomd desc, specrecomd desc, `order` desc, `status` desc, `pid` desc", "pid, name");
    			break;
    			case "1":
    				if($sub_rs = $articlestyle->findAll("sid in(".$atticlest['subcolumn'].") and frontdisplay = 1", "columnorder asc, sid desc", "sid as pid, sname as name")){
    					foreach($sub_rs as $key => $val){
    						$sub_rs[$key]['subc'] = $articlestyle->findAll(array("parentid"=>$val['pid'], "frontdisplay"=>1), "columnorder asc, sid desc", "sid as pid, sname as name");
    					}
    				}
    				$this->sub_rs = $sub_rs;
    			break;
	    	}	    	
	    }
	    if($atticlest["is_extfield"])
    		$this->ext_group_rs = $obj_extgroup->get_extfield_format($atticlest["sid"]);
	    $this->validator = $articles->getValidatorJSedit($id);
		$this->saveurl = spUrl("articles", "updatearticles", array('id' => $id));
		$this->fname = $articleft['sname'];
		$this->sname = $atticlest['sname'];
		$this->sconfig = $atticlest;
		$this->extarray = $extarray;
		$this->id = $id;
		$this->fid = $fid;
		$this->sid = $sid;
		$this->line_rs = $articlestyle->get_ent_line($atticlest);
		$this->display("articles/createarticles.html");
	}
	
	public function delproimg(){
		$id = intval($this->spArgs('id'));
		$fid = intval($this->spArgs('fid'));
		$sid = intval($this->spArgs('sid'));
		$act = $this->spArgs('act');
		try {
			if(!$id)
				throw new Exception("参数丢失");
			$articles = spClass('article');
			if(!$pro_rs = $articles->findByPk($id))
				throw new Exception("参数错误");
			$act_val = array(
				"img" => array("picurlpath", "picurlfile"), 
				"img2" => array("picurlpath2", "picurlfile2"), 
				"img3" => array("picurlpath3", "picurlfile3"),
				"flv" => "flvurl",
				"package" => "packageurl"
			);
			if(!array_key_exists($act, $act_val))
				throw new Exception("行为参数错误");
			$articles->delupfile($act, $id, $act_val[$act]);
			@header("location:".spUrl("articles", "updatearticles", array("id"=>$id,"fid"=>$fid, "sid"=>$sid)));
			exit();
		}catch(Exception $e){
			$this->redirect(spUrl("articles", "updatearticles", array("id"=>$id,"fid"=>$fid, "sid"=>$sid)), $e->getMessage());
		}
	}
	
	//删除作品
	function deletearticles(){
		$id = $this->spArgs('id');
		$fid = intval($this->spArgs('fid'));
		$sid = intval($this->spArgs('sid'));
		if($catoid = intval($this->spArgs("catoid")))
			$catourl = "&pc_id=$catoid";
		import("Common.php");
		if($id){
			$articlestyle = spClass('articlestyle');
			$articles = spClass('article');
			$obj_extval = spClass('article_extval');
			$atticlest = $articlestyle->find(array('sid'=>$sid));
			$url = spUrl("articles", "articleslist",array('fid'=>$fid,'sid'=>$sid)).$catourl;
			if(is_array($id)){
				foreach ($id as $v)
					$articles->del_article($v, $atticlest);
				echo json_encode(array('msg'=>'删除成功！' , 'result'=>0, 'url'=>$url));
				exit();
			}else {
				$articles->del_article($id, $atticlest);
				echo json_encode(array('msg'=>'删除成功！' , 'result'=>0, 'url'=>$url));
				exit();
			}
		}else{
			echo json_encode(array('msg'=>'请先选择删除项，再进行操作！' , 'result'=>-2));
			exit();
		}
	}
	
	function shift(){
		$id = $this->spArgs('id');
		$fid = intval($this->spArgs('fid'));
		$sid = intval($this->spArgs('sid'));
		$ptype = intval($this->spArgs('ptype'));
		$url = spUrl("articles", "articleslist", array('fid'=>$fid,'sid'=>$sid));
		if($id){
			$articles = spClass('article');
			if(is_array($id)){
				foreach ($id as $v){
					$articles->update(array("pid"=>$v), array("ptype"=>$ptype));
				}
				$this->redirect($url, '文章转移成功！');
				exit();
			}
			else {
				if($articles->update(array("pid"=>$id), array("ptype"=>$ptype))){
					$this->redirect($url, '文章转移成功！');
					exit();
				}
				$this->redirect($url, '文章转移失败！');
				exit();
			}
		}else{
			$this->redirect($url, '请先选择要转移的文章，再进行操作！');
			exit();
		}
	}
	
	public function relative(){
		$id = intval($this->spArgs('id'));
		if($id){
			$conditions = array('pid'=>$id);
			import("Common.php");
			$articles = spClass("article");
			$articlestyle = spClass('articlestyle');
			$postdate = $this->spArgs();
			$fid = intval($postdate['fid']);
			$sid = intval($postdate['sid']);
			$articleft = $articlestyle->find(array('sid'=>$fid), null, 'sname');
			$atticlest = $articlestyle->find(array('sid'=>$sid));
			$articles_rs = $articles->find($conditions);
			if($articles_rs['relatestr']) $articles_rs['relatestr'] = explode(",", $articles_rs['relatestr']);
			$this->articles_rs = $articles_rs;
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				$postdata = $this->spArgs();
		    	$data = array();
		    	$data['relatestr'] = "";
		    	if($postdata['relatestr']) $data['relatestr'] = implode(",", $postdata['relatestr']);
		    	$articles->update($conditions, $data);
		    	$msg = "关联设置成功";
		    	$url = spUrl("articles", "articleslist", array('fid'=>$fid,'sid'=>$sid));
		    	$message = array('msg' => $msg, "url"=>$url, 'result'=>1);
				echo json_encode($message);
				exit();
	   	 	}
	   	 	$condition = "frontdisplay = 1 and islist = 1 and parentid <> 0";
	   	 	if($atticlest['relatestr']) $condition .= " and sid in ($atticlest[relatestr])";
	   	 	if($column_rs = $articlestyle->findAll($condition, "columnorder asc, sid desc", "sid, sname")){
	   	 		foreach($column_rs as $key => $val){
	   	 			if($p_rs = $articles->findAll(array("ptype"=>$val['sid'], "pid <>"=>$id), "top desc, recomd desc, specrecomd desc, `order` desc, `status` desc, `pid` desc", "pid, name")){
	   	 				$relative_rs[$key] = $val;
	   	 				$relative_rs[$key]['rs'] = $p_rs;
	   	 			}
				}
	   	 	}
	   	 	$this->relative_rs = $relative_rs;
		    $this->validator = $articles->getValidatorJSedit($id);
			$this->saveurl = spUrl("articles", "relative", array('id' => $id));
			$this->fname = $articleft['sname'];
			$this->sname = $atticlest['sname'];
			$this->sconfig = $atticlest;
			$this->extarray = $extarray;
			$this->id = $id;
			$this->fid = $fid;
			$this->sid = $sid;
		}else{
			$this->redirect(spUrl("articles", "articleslist"), "非法操作！");
		}
	}
	
	public function entrancearticles(){
		$id = intval($this->spArgs('id'));
		try {
			if(!$id)
				throw new Exception("非法操作！");
			import("Common.php");
			$articles = spClass("article");
			$articlestyle = spClass('articlestyle');
			$obj_ent = spClass("entrance");
			$postdate = $this->spArgs();
			$fid = intval($postdate['fid']);
			$sid = intval($postdate['sid']);
			$articleft = $articlestyle->find(array('sid'=>$fid), null, 'sname');
			$atticlest = $articlestyle->find(array('sid'=>$sid));
			if(!$articles_rs = $articles->find(array("pid"=>$id)))
				throw new Exception("找不到该信息，可能已被删除");
			if(!$this->sys_entrance_change || !$atticlest["isentrance"])
				throw new Exception("未开启入口修改功能或该栏目没有设置入口");
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				try{
					$data = array();
					//客户端提交方法
					$isAjax = $postdate['isAjax'];
					$data['entrance_id'] = intval($postdate['entrance_id']);
					if($data['entrance_id'] != $articles_rs["entrance_id"])
						$data['entrance_line_id'] = 0;
					if(!$articles->update(array("pid"=>$id), $data))
						throw new Exception("未知错误，入口更新失败");
					$url = spUrl("articles", "articleslist", array('fid'=>$fid, 'sid'=>$sid));
					$message = array('msg'=>'修改成功！','result'=>0, 'url'=>$url);
					echo json_encode($message);
					exit();
				}catch(Exception $e){
					$articles->getDb()->rollbackTrans();
					if($upfile_data){
						foreach($upfile_data as $val)
							unlink($val);
					}
					$message = array('msg' => $e->getMessage(), 'result'=>-1);
					echo json_encode($message);
					exit();
				}
			}
		}catch(Exception $e){
			$this->redirect(spUrl("articles", "articleslist", array("fid"=>$fid, "sid"=>$sid)), $e->getMessage());
		}
		$this->entrance_rs = $obj_ent->getlist();
		$this->id = $id;
		$this->fid = $fid;
		$this->sid = $sid;
		$this->fname = $articleft['sname'];
		$this->sname = $atticlest['sname'];
		$this->articles_rs = $articles_rs;
		$this->saveurl = spUrl("articles", "entrancearticles");
	}
	
	public function ajaxfile(){
		import("Common.php");
		import("Image.php");
		$file = $this->spArgs("file");
		$thumb_width = intval($this->spArgs("thumb_width"));
		$thumb_height = intval($this->spArgs("thumb_height"));
		$allow_ext = $this->spArgs("allow_ext");
		try {
			if(!$_FILES[$file]['name'])
				throw new Exception("请先选择要上传的文件，再进行上传");
			if($allow_ext)
				$ext_array = explode(",", $allow_ext);
			$res = array("result"=>1);
			$filename = basename($_FILES[$file]['name']);
			$file_ext = strtolower(substr($filename, strrpos($filename, '.') + 1));
			if($ext_array && !in_array($file_ext, $ext_array))
				throw new Exception("请上传后缀为 $allow_ext 的文件");
			$file_name = Common::randomkeys(10).".$file_ext";
			$dir_str = "uploads/ajaxfile/" . str_replace("_upload", "", $file) . "/" . date("Y/m/d") . "/";
			$file_dir ='../'.$dir_str;
			if(!is_dir($file_dir)){
				Common::rmkdir($file_dir);
				chmod("../uploads/ajaxfile/" . str_replace("_upload", "", $file)."/", 0777);
				chmod("../uploads/ajaxfile/" . str_replace("_upload", "", $file)."/".date("Y/"), 0777);
				chmod("../uploads/ajaxfile/" . str_replace("_upload", "", $file)."/".date("Y/m/"), 0777);
				chmod($file_dir, 0777);
			}
			if(move_uploaded_file($_FILES[$file]['tmp_name'], $file_dir.$file_name)){
				$res["imgurl"] = $dir_str.$file_name;
				$res["result"] = 1;
				if($thumb_width && $thumb_height){
					$image = new Image();
					$image->open($file_dir.$file_name);
					$thumb_image_location = $file_dir . 'thumb_' . $file_name;
					$image->thumbImage($thumb_image_location, $thumb_width, $thumb_height);
				}
			}else
				throw new Exception("未知错误，上传失败");
			echo json_encode($res);
			exit();
		}catch(Exception $e){
			echo json_encode(array("msg"=>$e->getMessage(), "result"=>0));			
			exit();
		}
		
		echo json_encode($res);
	}
	
	function viewflv(){
		$articles = spClass('article');
		$id = intval($this->spArgs("id"));
		if ($spzx_rs = $articles->findByPk($id, 'flvurl')) {
			$this->spzx_rs = $spzx_rs;
		}
	}
	
	function getimg($id){
	 	$articles = spClass('article');
	 	$condition = array(
	 		'pid' => $id
	 	);
	 	$rs = $articles->find($condition);
	 	$path = explode(',',$rs['photos_id']);
	 	return $path;
	 }
	 
	 private function gettitlepic($id){
	 	$articles = spClass('article');
	 	$condition = array(
	 		'pid' => $id
	 	);
	 	if ($rs = $articles->find($condition)) {
	 		$path['picurlpath'] = $rs['picurlpath'];
	 		$path['picurlfile'] = $rs['picurlfile'];
	 	}
	 	return $path;
	 }
	 
	 private function gettitlepic2($id){
	 	$articles = spClass('article');
	 	$condition = array(
	 		'pid' => $id
	 	);
	 	if ($rs = $articles->find($condition)) {
	 		$path['picurlpath'] = $rs['picurlpath2'];
	 		$path['picurlfile'] = $rs['picurlfile2'];
	 	}
	 	return $path;
	 }
	 
	 private function gettitlepic3($id){
	 	$articles = spClass('article');
	 	$condition = array(
	 		'pid' => $id
	 	);
	 	if ($rs = $articles->find($condition)) {
	 		$path['picurlpath'] = $rs['picurlpath3'];
	 		$path['picurlfile'] = $rs['picurlfile3'];
	 	}
	 	return $path;
	 }
	 
	 private function getflv($id){
	 	$articles = spClass('article');
	 	$condition = array(
	 		'pid' => $id
	 	);
	 	if ($rs = $articles->find($condition)) {
	 		$flvurl = $rs['flvurl'];
	 	}
	 	return $flvurl;
	 }
	 
	 private function getpackage($id){
	 	$articles = spClass('article');
	 	$condition = array(
	 		'pid' => $id
	 	);
	 	if ($rs = $articles->find($condition)) {
	 		$packageurl = $rs['packageurl'];
	 	}
	 	return $packageurl;
	 }
	 
	 private function deletetitlepic($pic){
		if ($pic['picurlfile']) {
			@unlink('../'.$pic['picurlpath'].$pic['picurlfile']);
			@unlink('../'.$pic['picurlpath'].'thumb_'.$pic['picurlfile']);
		}
	 }
	 
	 private function deleteflv($id){
	 	$articles = spClass('article');
	 	$condition = array(
	 		'pid' => $id
	 	);
	 	if ($rs = $articles->find($condition)) {
	 		if ($rs['flvurl']) {
	 			@unlink('../'.$pic['flvurl']);
	 		}
	 	}
	 }
	 
	 private function deletepackage($id){
	 	$articles = spClass('article');
	 	$condition = array(
	 		'pid' => $id
	 	);
	 	if ($rs = $articles->find($condition)) {
	 		if ($rs['packageurl']) {
	 			@unlink('../'.$pic['packageurl']);
	 		}
	 	}
	 }
	 
	 function deletepic($pic){
		$photo = spClass('photos');
	 	if (is_array($pic)){
			for ($i=0;$i<count($pic);$i++){
				$condition = array(
					'pid' => $pic[$i]
				);
				$photors = $photo->find($condition);
				@unlink('../'.$photors['filepath'].$photors['filename']);
				@unlink('../'.$photors['filepath'].'thumb_'.$photors['filename']);
				$photo->delete($condition);
				/*
				dump($photors);
				exit();
				*/
			}
		}else{
			$condition = array(
				'pid' => $pic
			);
			$photors = $photo->find($condition);
			@unlink('../'.$photors['filepath'].$photors['filename']);
			@unlink('../'.$photors['filepath'].'thumb_'.$photors['filename']);
			$photo->delete($condition);
		}
	}
	
	function statusarticles(){
		$id = $this->spArgs('id');
		if($id){
			$articles = spClass('article');
			$url = spUrl("articles", "articleslist");
			$conditions = array('pid' => $id);
			$articles_rs = $articles->find($conditions,'`status`');
			$statusrs = $articles_rs['status'] == 0?1:0;
			$update = array(
				'`status`' => $statusrs
			);
			if($articles->update($conditions, $update)){
				echo json_encode(array('msg'=>'状态修改成功！' , 'result'=>0, 'url'=>$url));
				exit();
			}
			echo json_encode(array('msg'=>'状态修改失败！' , 'result'=>-1, 'url'=>$url));
			exit();
		}else{
			echo json_encode(array('msg'=>'请先选择修改项，再进行操作！' , 'result'=>-2));
			exit();
		}
	}
	
	function verifypro($type){
		if ($type == '') return false;
		$articlestyle = spClass('articlestyle');
		$articles = spClass('article');
		if ($artrs = $articlestyle->find(array('sid' => $type), null, 'islist')){
			if ($artrs['islist'] == 0){
				if($articles->find(array('ptype'=>$type))){
					return false;
				}else{
					return true;
				}
			}else{
				return true;
			}
		}else{
			return false;
		}
	}
	
	//新闻置顶
	public function istop(){
		$id = $this->spArgs('id');
		$topval = $this->spArgs('val');
		if($id){
			$articles = spClass('article');
			$url = spUrl("articles", "articleslist");
			$conditions = array('pid' => $id);
			if($topval==0){
				$newrow = array('top'=>1);
			}
			else {
					$newrow = array('top'=>0);
			}
			if($articles->update($conditions, $newrow)){
				echo json_encode(array('msg'=>'修改成功！' , 'result'=>0, 'url'=>$url));
				exit();	
			}
		}
	}
}
?>