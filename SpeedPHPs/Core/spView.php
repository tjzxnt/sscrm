<?php
/////////////////////////////////////////////////////////////////
// SpeedPHP中文PHP框架, Copyright (C) 2008 - 2010 SpeedPHP.com //
/////////////////////////////////////////////////////////////////

/**
 * spView 基础视图类
 */
class spView {
	/**
	 * 模板引擎实例
	 */
	public $engine = null;
	/**
	 * 模板是否已输出
	 */
	public $displayed = FALSE;

	/**
	 * 构造函数，进行模板引擎的实例化操作
	 */
	public function __construct()
	{
		if(FALSE == $GLOBALS['G_SP']['view']['enabled'])return FALSE;
		if(FALSE != $GLOBALS['G_SP']['view']['auto_ob_start'])ob_start();
		$this->engine = spClass($GLOBALS['G_SP']['view']['engine_name'],null,$GLOBALS['G_SP']['view']['engine_path']);
		$configs = $GLOBALS['G_SP']['view']['config'];
		
		//设置视图引擎参数
		if( is_array($configs) ){
			$engine_vars = get_class_vars(get_class($this->engine));
			foreach( $configs as $key => $value ){
				if( array_key_exists($key,$engine_vars) )$this->engine->{$key} = $value;
			}
		}
		spAddViewFunction('T', array( 'spView', '__template_T'));
		spAddViewFunction('spUrl', array( 'spView', '__template_spUrl'));
		spAddViewFunction('zxsql', array( 'spView', '__template_zxsql'));
		spAddViewFunction('zxfind', array( 'spView', '__template_zxfind'));
		spAddViewFunction('zxfindAll', array( 'spView', '__template_zxfindAll'));
		spAddViewFunction('zxcolumnlist', array( 'spView', '__template_zxcolumnlist'));
		spAddViewFunction('zxcolumnlinkbyend', array( 'spView', '__template_zxcolumnlinkbyend'));
		spAddViewFunction('zxgetinfo', array( 'spView', '__template_zxgetinfo'));
		spAddViewFunction('zxadslist', array( 'spView', '__template_zxadslist'));
		spAddViewFunction('zxlinklist', array( 'spView', '__template_zxlinklist'));
		spAddViewFunction('zxcato1', array( 'spView', '__template_zxcato1'));
		spAddViewFunction('zxsubcolumn', array( 'spView', '__template_zxsubcolumn'));
		spAddViewFunction('zxsubcolumn_c', array( 'spView', '__template_zxsubcolumn_c'));
	}

	/**
	 * 输出页面
	 * @param tplname 模板文件路径
	 */
	public function display($tplname)
	{
		$this->addfuncs();
		$this->displayed = TRUE;
		if($GLOBALS['G_SP']['view']['debugging'] && SP_DEBUG)$this->engine->debugging = TRUE;
		$this->engine->display($tplname);
	}
	
	/**
	 * 显示输出
	 * @param tplname 模板文件路径
	 */
	public function fetch($tplname){
		$this->addfuncs();
		if($contents = $this->engine->fetch($tplname)) {
			return $contents;
		}
		else {
			spError("模板文件{$tplname}不存在！");
		}
	}	
	
	/**
	 * 注册已挂靠的视图函数
	 */
	public function addfuncs()
	{
		if( is_array($GLOBALS['G_SP']["view_registered_functions"]) && 
			method_exists($this->engine, 'register_function') ){
			foreach( $GLOBALS['G_SP']["view_registered_functions"] as $alias => $func ){
				if( is_array($func) && !is_object($func[0]) )$func = array(spClass($func[0]),$func[1]);
				$this->engine->register_function($alias, $func);
			}
		}
	}
	/**
	 * 辅助spUrl的函数，让spUrl可在模板中使用。
	 * @param params 传入的参数
	 */
	public function __template_spUrl($params)
	{
		$controller = $GLOBALS['G_SP']["default_controller"];
		$action = $GLOBALS['G_SP']["default_action"];
		$args = array();
		$anchor = null;
		foreach($params as $key => $param){
			if( $key == $GLOBALS['G_SP']["url_controller"] ){
				$controller = $param;
			}elseif( $key == $GLOBALS['G_SP']["url_action"] ){
				$action = $param;
			}elseif( $key == 'anchor' ){
				$anchor = $param;
			}else{
				$args[$key] = $param;
			}
		}
		if(TRUE == $GLOBALS['G_SP']['html']["enabled_zxadv"]){
			return spUrl_htmladv($controller, $action, $args, $anchor);
		}else{
			return spUrl_htmladv($controller, $action, $args, $anchor);
		}
	}
	/**
	 * 辅助T的函数，让T可在模板中使用。
	 * @param params 传入的参数
	 */
	public function __template_T($params)
	{
		return T($params['w']);
	}
	
	public function __template_zxsql($params){
		$db_rs = $obj_db = $tbl_name = $pk = $sql = $assign = null;
		$table = $params["table"];
		$pk = $params["pk"];
		$sql = $params["sql"];
		$assign = $params["assign"];
		$obj_db = spDB($table, $pk);
		if ($sql) {
			$db_rs = $obj_db->findSql($sql);
		}
		$this->engine->assign($assign, $db_rs);
	}
	
	public function __template_zxfind($params){
		$db_rs = $this->__template_get_rs($params);
		$assign = $params["assign"];
		$this->engine->assign($assign, $db_rs[0]);
	}
	
	public function __template_zxfindAll($params){
		$db_rs = $this->__template_get_rs($params);
		$assign = $params["assign"];
		$this->engine->assign($assign, $db_rs);
	}
	
	public function __template_zxcolumnlinkbyend($params){
		$db_rs = $obj_db = $sid = $assign = $return = null;
		if(!intval($params['sid'])) return;
		$assign = $params["assign"];
		$sid = intval($params['sid']);
		$obj_db = spDB("articlestyle", "sid");
		$sql = "select sid,parentid,sname from ".$GLOBALS['spConfig']['db']['prefix']."articlestyle where sid = $sid order by columnorder asc, sid asc";
		if ($temp_rs = $obj_db->findSql($sql)){
			$db_rs[] = $temp_rs[0];
			$tempdata['sid'] = $temp_rs[0]['parentid'];
			$tempdata['return'] = 1;
			if(intval($tempdata['sid'])){
				$db_rs[] = $this->__template_zxcolumnlinkbyend($tempdata);
			}
		}
		if(intval($params['return'])){
			return $db_rs[0];
		}else{
			$db_rs = array_reverse($db_rs);
			$this->engine->assign($assign, $db_rs);
		}
	}
	
	public function __template_zxcolumnlist($params){
		$db_rs = $obj_db = $tbl_name = $pk = $cldisplay = $assign = $limit = null;
		$cldisplay = (string) $params["display"];
		$pk = intval($params["pk"]);
		$condition = "1";
		if ($cldisplay."zx" == "zx") {
			//$condition = "";
		}elseif (intval($cldisplay) == 0){
			$condition .= " and frontdisplay = 0";
		}else{
			$condition .= " and frontdisplay = 1";
		}
		if ($params['parentid']."zx" != "zx") {
			$condition .= " and parentid = ".intval($params[parentid]);
		}else{
			$condition .= " and parentid <> 0";
		}
		if ($pk) {
			$condition .= " and sid = $pk";
		}
		if ($params['condition_str']) {
			$condition .= " and ".$params['condition_str'];
		}
		$limit = intval($params["limited"]) ? "limit 0, " . intval($params["limited"]): "";
		$assign = $params["assign"];
		$obj_db = spDB("articlestyle", "sid");
		$obj_pdb = spDB("products", "pid");
		$sql = "select * from ".$GLOBALS['spConfig']['db']['prefix']."articlestyle where $condition order by columnorder asc, sid asc $limit";
		if ($db_rs = $obj_db->findSql($sql)) {
			foreach ($db_rs as $key=>$val){
				if($val['outlink']){
					$db_rs[$key]['turl'] = strtolower(substr($val['outlink'], 0, 7)) == "http://" ?  $val['outlink'] : WEB_ROOT."/".$val['outlink'];
					continue;
				}
				if ($val['islist'] == 0) {
					if ($obj_pdb_rs = $obj_pdb->findSql("select pid from ".$GLOBALS['spConfig']['db']['prefix']."products where ptype = ".$val['sid'])){
						$db_rs[$key]['turl'] = spUrl("main", "cdetail", array("id"=>$obj_pdb_rs[0]['pid']));
					}else{
						unset($db_rs[$key]);
					}
				}else{
					$db_rs[$key]['turl'] = spUrl("main", "clist", array("id"=>$val['sid']));
				}
			}
		}
		if(intval($params["debug"])){
			echo $sql;
			print_r($db_rs);
		}
		$this->engine->assign($assign, $db_rs);
	}
	
	public function __template_zxgetinfo($params){
		$db_rs = $top = $index = $pid = $sub = $short = $sort = $start = $limit = $cldisplay = $assign = null;
		$condition = " 1 ";
		if (intval($params['cid'])) {
			$condition .= " and ".$GLOBALS['spConfig']['db']['prefix']."products.ptype = ".intval($params[cid]);
		}
		if (intval($params['pcid'])) {
			$condition .= " and ".$GLOBALS['spConfig']['db']['prefix']."products.pc_id = ".intval($params[pcid]);
		}
		if (intval($params['top'])) {
			$condition .= " and ".$GLOBALS['spConfig']['db']['prefix']."products.top = ".intval($params['top']);
		}
		if (intval($params['recomd'])) {
			$condition .= " and ".$GLOBALS['spConfig']['db']['prefix']."products.recomd = ".intval($params['recomd']);
		}
		if (intval($params['specrecomd'])) {
			$condition .= " and ".$GLOBALS['spConfig']['db']['prefix']."products.specrecomd = ".intval($params['specrecomd']);
		}
		if (intval($params['isindex'])) {
			$condition .= " and ".$GLOBALS['spConfig']['db']['prefix']."products.isindex = ".intval($params['isindex']);
		}
		if (intval($params['pid'])) {
			$condition .= " and ".$GLOBALS['spConfig']['db']['prefix']."products.pc_id = ".intval($params['pid']);
		}
		if ($params['sub']."zx" != "zx" && intval($params['sub'])) {
			$condition .= " and ".$GLOBALS['spConfig']['db']['prefix']."products.subproduct = ".intval($params['sub']);
		}
		if ($params['condition_str']) {
			$condition .= " and ".$params['condition_str'];
		}
		$short = intval($params['short']) ? intval($params['short']) : 1000000;
		if (intval($params['limited'])) {
			$limit = "limit ".intval($params['start']).",".intval($params['limited']);
		}
		$sort = $params['sort'];
		if ($sort) {
			$sort = "order by $sort";
		}else{
			$sort = "order by `top` desc, `order` asc, `posttime` desc, `pid` desc";
		}
		$assign = $params["assign"];
		$obj_ag = spDB("products", "pid");
		if($params['debug']) $obj_ag->debug(1);
		if ($db_rs = $obj_ag->findSql("select ".$GLOBALS['spConfig']['db']['prefix']."products.*, ".$GLOBALS['spConfig']['db']['prefix']."articlestyle.sname, ".$GLOBALS['spConfig']['db']['prefix']."articlestyle.islist from ".$GLOBALS['spConfig']['db']['prefix']."products inner join ".$GLOBALS['spConfig']['db']['prefix']."articlestyle on ".$GLOBALS['spConfig']['db']['prefix']."articlestyle.sid = ".$GLOBALS['spConfig']['db']['prefix']."products.ptype where $condition $sort $limit")) {
			foreach ($db_rs as $key => $val){
				$db_rs[$key]["turl"] = spUrl("main", "cdetail", array("id"=>$val['pid']));
				$db_rs[$key]["thumb"] = $val['picurlfile'] ? $val['picurlpath']."thumb_".$val['picurlfile'] : "";
				$db_rs[$key]["pic"] = $val['picurlfile'] ? $val['picurlpath'].$val['picurlfile'] : "";
				$db_rs[$key]["sdesc"] = $this->removehtml($val['description'], $short);
				$db_rs[$key]["ftime"] = date("Y-m-d", strtotime($val['posttime']));
				if ($val['extinput']) {
					$db_rs[$key]["ext"] = unserialize($val['extinput']);
				}
			}
		}
		$this->engine->assign($assign, $db_rs);
	}
	
	public function __template_zxadslist($params){
		$db_rs = $limit = $cldisplay = $assign = null;
		$condition1 = "t1.is_del = 0";
		$condition2 = "1";
		if (intval($params['gid'])) {
			$condition2 .= " and t2.groupid = ".intval($params[gid]);
		}
		if (intval($params['cid'])) {
			$condition2 .= " and t2.columnid = ".intval($params[cid]);
		}
		if ($params['code']) {
			$condition2 .= " and t2.code = '".$params[code]."'";
		}
		$obj_ag = spDB("ad_group", "groupid");
		$ag_rs = $obj_ag->findSql("select t2.show_num from ".$GLOBALS['spConfig']['db']['prefix']."ad_group as t2 where $condition2 limit 1");
		if (intval($params['limited'])) {
			$limitstr = "limit ".intval($params['start']).",".intval($params['limited']);
		}else{
			$limitstr =  "limit ".intval($params['start']).", ".intval($ag_rs[0][show_num]);
		}
		$assign = $params["assign"];
		$obj_ad = spDB("ad_info", "ads_id");
		$sql = "select t1.* from ".$GLOBALS['spConfig']['db']['prefix']."ad_info as t1 inner join ".$GLOBALS['spConfig']['db']['prefix']."ad_group as t2 on t1.group_id = t2.groupid where $condition1 and $condition2 order by t1.sort asc, t1.ads_id desc $limitstr";
		if ($db_rs = $obj_ad->findSql($sql)) {
			foreach($db_rs as $key => $val){
				$db_rs[$key]['thumb'] = $val['pathurl']."thumb_".$val['pathfile'];
				$db_rs[$key]['pic'] = $val['pathurl'].$val['pathfile'];
				$db_rs[$key]['turl'] = substr($val['ads_href'],0,12) !== 'http://{url}' ? ($val['ads_href'] ? $val['ads_href'] : "#") : str_replace("http://{url}", WEB_ROOT, $val['ads_href']);
			}
		}
		$this->engine->assign($assign, $db_rs);
	}
	
	public function __template_zxlinklist($params){
		$db_rs = $limit = $cldisplay = $assign = null;
		$condition = "is_verify = 1";
		$assign = $params["assign"];
		if (intval($params['limited'])) {
			$limitstr = "limit ".intval($params['start']).",".intval($params['limited']);
		}
		$obj_ag = spDB("link", "id");
		$db_rs = $obj_ag->findSql("select * from ".$GLOBALS['spConfig']['db']['prefix']."links where $condition $limitstr");
		$this->engine->assign($assign, $db_rs);
	}
	
	public function __template_zxcato1($params){
		$db_rs = $limit = $cldisplay = $assign = null;
		$condition = "1 = 1";
		if (intval($params['cid'])) {
			$condition .= " and markid = ".intval($params[cid]);
		}
		if (intval($params['fid'])) {
			$condition .= " and fid = ".intval($params[fid]);
		}
		if (intval($params['is_recommend'])) {
			$condition .= " and is_recommend = ".intval($params[is_recommend]);
		}
		if (intval($params['limited'])) {
			$limit = " limit ".intval($params['start']).",".intval($params['limited']);
		}
		$assign = $params["assign"];
		$obj_ag = spDB("productscato", "pc_id");
		if ($db_rs = $obj_ag->findSql("select * from ".$GLOBALS['spConfig']['db']['prefix']."productscato where $condition order by `is_recommend` desc, `order` asc $limit")) {
			foreach ($db_rs as $key => $val){
				$db_rs[$key]["turl"] = spUrl("main", "clist", array("id"=>intval($params['cid']), "pid"=>$val['pc_id']));
			}
		}
		$this->engine->assign($assign, $db_rs);
	}
	
	private function __template_get_rs($params){
		$db_rs = $obj_db = $table = $pk = $condition = $sort = $field = $jointable = $joincondition = $joinmode = $join = $assign = null;
		extract($params);
		$condition = $condition ? " and ".$condition : "";
		$sort = $sort ? "order by ".$sort : "";
		$field = $field ? $field : "*";
		$obj_db = spDB($table, $pk);
		if ($jointable && $joincondition && $joinmode) {
			$join = "$joinmode join $jointable on $joincondition";
		}
		$sql = "select $field from ".$GLOBALS['spConfig']['db']['prefix']."$table $join where 1 = 1 $condition $sort";
		$db_rs = $obj_db->findSql($sql);
		return $db_rs;
	}
	
	public function __template_zxsubcolumn($params){
		$db_rs = $limit = $sid = $assign = null;
		$condition = "1";
		$assign = $params["assign"];
		if (intval($params['cid'])) {
			$condition .= " and ptype = ".intval($params['cid']);
		}
		if (intval($params['sid'])) {
			$condition .= " and subproduct = ".intval($params['sid']);
		}
		if ($params['condition_str']) {
			$condition .= " and ".$params['condition_str'];
		}
		if (intval($params['limited'])) {
			$limitstr = "limit ".intval($params['start']).",".intval($params['limited']);
		}
		$sort = $params['sort'];
		if ($sort) {
			$sort = "order by $sort";
		}else{
			$sort = "order by `specrecomd` desc, `recomd` desc, `top` desc, `order` asc, `posttime` desc, `pid` desc";
		}
		$obj_ag = spDB("products", "pid");
		if($db_rs = $obj_ag->findSql("select * from ".$GLOBALS['spConfig']['db']['prefix']."products where $condition $sort $limitstr")){
			foreach($db_rs as $key => $val){
				$db_rs[$key]["thumb"] = $val['picurlfile'] ? $val['picurlpath']."thumb_".$val['picurlfile'] : "";
				$db_rs[$key]["pic"] = $val['picurlfile'] ? $val['picurlpath'].$val['picurlfile'] : "";
				$db_rs[$key]['turl'] = spUrl("main", "cdetail", array("id"=>$val['pid']));
			}
		}
		$this->engine->assign($assign, $db_rs);
	}
	
	public function __template_zxsubcolumn_c($params){
		$db_rs = $limit = $sid = $assign = null;
		$condition = "1";
		$assign = $params["assign"];
		if (intval($params['cid'])) {
			$condition .= " and ptype = ".intval($params['cid']);
		}
		if (intval($params['sid'])) {
			$condition .= " and subproduct = ".intval($params['sid']);
		}
		if ($params['condition_str']) {
			$condition .= " and ".$params['condition_str'];
		}
		if (intval($params['limited'])) {
			$limitstr = "limit ".intval($params['start']).",".intval($params['limited']);
		}
		$sort = $params['sort'];
		if ($sort) {
			$sort = "order by $sort";
		}else{
			$sort = "order by `top` desc, `order` asc, `posttime` desc, `pid` desc";
		}
		$obj_ag = spDB("products", "pid");
		if($params['debug']) $obj_ag->debug(1);
		if($db_rs = $obj_ag->findSql("select ".$GLOBALS['spConfig']['db']['prefix']."products.*, ".$GLOBALS['spConfig']['db']['prefix']."articlestyle.parentid from ".$GLOBALS['spConfig']['db']['prefix']."products inner join ".$GLOBALS['spConfig']['db']['prefix']."articlestyle on ".$GLOBALS['spConfig']['db']['prefix']."products.subproduct = ".$GLOBALS['spConfig']['db']['prefix']."articlestyle.sid where $condition $sort $limitstr")){
			foreach($db_rs as $key => $val){
				$db_rs[$key]["thumb"] = $val['picurlfile'] ? $val['picurlpath']."thumb_".$val['picurlfile'] : "";
				$db_rs[$key]["pic"] = $val['picurlfile'] ? $val['picurlpath'].$val['picurlfile'] : "";
				$db_rs[$key]['turl'] = spUrl("main", "cdetail", array("id"=>$val['pid']));
				if ($val['extinput']) {
					$db_rs[$key]["ext"] = unserialize($val['extinput']);
				}
			}
		}
		$this->engine->assign($assign, $db_rs);
	}
	
	private function removehtml($str,$length){
		import("Common.php");
		if (!empty($str)){
			$str = strip_tags($str, '<br><br />');
			$str = Common::utf8_substr(Common::trimHtml(strip_tags($str)), 0, intval($length), "");
		}
		return $str;
	}
}

/**
 * spHtml
 * 静态HTML生成类
 */
class spHtml
{
	private $spurls = null;
	/**
	 * 生成单个静态页面
	 * 
	 * @param spurl spUrl的参数
	 * @param alias_url 生成HTML文件的名称，如果不设置alias_url，将使用年月日生成目录及随机数为文件名的形式生成HTML文件。
	 * @param update_mode    更新模式，默认2为同时更新列表及文件
	 * 0是仅更新列表
	 * 1是仅更新文件
	 */
	public function make($spurl, $alias_url = null, $update_mode = 2)
	{
		if(1 == spAccess('r','sp_html_making')){$this->spurls[] = array($spurl, $alias_url); return;}
		@list($controller, $action, $args, $anchor) = $spurl;
		if(TRUE != $GLOBALS['G_SP']['html']['enabled_zxadv'] && $url_item = spHtml::getUrl($controller, $action, $args, $anchor, TRUE) ){
			@list($baseuri, $realfile) = $url_item;$update_mode = 1;
		}else{
			$file_root_name = ( '' == $GLOBALS['G_SP']['html']['file_root_name'] || './' == $GLOBALS['G_SP']['html']['file_root_name']) ? $GLOBALS['G_SP']['html']['file_root_name'] : $GLOBALS['G_SP']['html']['file_root_name'].'/';
			if( null == $alias_url ){
				$filedir = $file_root_name .date('Y/n/d').'/';
				$filename = substr(time(),3,10).substr(mt_rand(100000, substr(time(),3,10)),4).".html";
			}else{
				$filedir = $file_root_name.dirname($alias_url) . '/';
				$filename = basename($alias_url);
			}
			$baseuri = rtrim(dirname($GLOBALS['G_SP']['url']["url_path_base"]), '/\\')."/".$filedir.$filename;
			$realfile = APP_PATH."/".$filedir.$filename;
		}
		if( 0 == $update_mode or 2 == $update_mode )spHtml::setUrl($spurl, $baseuri, $realfile);
		if( 1 == $update_mode or 2 == $update_mode ){
			$oralcode = (TRUE == $GLOBALS['G_SP']['html']['enabled_zxadv'] ? 1 : 0);
			$remoteurl = 'http://'.$_SERVER["SERVER_NAME"].':'.$_SERVER['SERVER_PORT'].
										'/'.ltrim(spUrl($controller, $action, $args, $anchor, TRUE, $oralcode), '/\\');
			$cachedata = file_get_contents($remoteurl);
			if( FALSE === $cachedata ){
				$cachedata = $this->curl_get_file_contents($remoteurl);
				if( FALSE === $cachedata )spError("无法从网络获取页面数据，请检查：<br />1. spUrl生成地址是否正确！<a href='{$remoteurl}' target='_blank'>点击这里测试</a>。<br />2. 设置php.ini的allow_url_fopen为On。<br />3. 检查是否防火墙阻止了APACHE/PHP访问网络。<br />4. 建议安装CURL函数库。");
			}
			__mkdirs(dirname($realfile));
			file_put_contents($realfile, $cachedata);
		}
	}
	
	public function directmake($spurl, $alias_url = null, $update_mode = 2, $root = null, $cache = 'data', $static = 'static', $access = 'index.php')
	{
		if ($access) {
			$oralbase = $GLOBALS['G_SP']['url']["url_path_base"];
			$GLOBALS['G_SP']['url']["url_path_base"] = '/'.$access;
		}
		if ($cache) {
			$oralcache = $GLOBALS['G_SP']['sp_cache'];
			$GLOBALS['G_SP']['sp_cache'] = $root . DS . $cache;
		}
		if(1 == spAccess('r','sp_html_making')){$this->spurls[] = array($spurl, $alias_url); return;}
		@list($controller, $action, $args, $anchor) = $spurl;
		if( $url_item = spHtml::getUrl($controller, $action, $args, $anchor, TRUE) ){
			@list($baseuri, $realfile) = $url_item;$update_mode = 1;
		}else{
			$file_root_name = $root;
			if( null == $alias_url ){
				$filedir = '/'.$file_root_name .'/'.date('Y/n/d').'/';
				$filename = substr(time(),3,10).substr(mt_rand(100000, substr(time(),3,10)),4).".html";
			}else{
				$filedir = '/'.$file_root_name.'/'.$static.dirname($alias_url) . '/';
				$filename = basename($alias_url);
			}
			$baseuri = $filedir.$filename;
			$realfile = APP_PATH."/".$filedir.$filename;
		}
		if( 0 == $update_mode or 2 == $update_mode )spHtml::setUrl($spurl, $baseuri, $realfile);
		if( 1 == $update_mode or 2 == $update_mode ){
			$remoteurl = 'http://'.$_SERVER["SERVER_NAME"].':'.$_SERVER['SERVER_PORT'].
										'/'.$root.'/'.ltrim(spUrl($controller, $action, $args, $anchor, TRUE), '/\\');
			$cachedata = file_get_contents($remoteurl);
			if( FALSE === $cachedata ){
				$cachedata = $this->curl_get_file_contents($remoteurl);
				if( FALSE === $cachedata )spError("无法从网络获取页面数据，请检查：<br />1. spUrl生成地址是否正确！<a href='{$remoteurl}' target='_blank'>点击这里测试</a>。<br />2. 设置php.ini的allow_url_fopen为On。<br />3. 检查是否防火墙阻止了APACHE/PHP访问网络。<br />4. 建议安装CURL函数库。");
			}
			
			__mkdirs(dirname($realfile));
			file_put_contents($realfile, $cachedata);
		}
		if ($access) {
			$GLOBALS['G_SP']['url']["url_path_base"] = $oralbase;
		}
		if ($cache) {
			$GLOBALS['G_SP']['sp_cache'] = $oralcache;
		}
	}
	
	/**
	 * 当file_get_contents失效时，程序将调用CURL函数来进行网络数据获取
	 * @param url 访问地址
	 */
	function curl_get_file_contents($url)
    {
    	if(!function_exists('curl_init'))return FALSE;
        $c = curl_init();
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_URL, $url);
        $contents = curl_exec($c);
        curl_close($c);
        if (FALSE === $contents)return FALSE;
        return $contents;
    }
	
	/**
	 * 批量生成静态页面
	 * @param spurls 数组形式，每项是一个make()的全部参数
	 */
	public function makeAll($spurls)
	{
		foreach( $spurls as $single ){
			list($spurl, $alias_url) = $single;
			$this->make($spurl, $alias_url, 0);
		}
		foreach( $spurls as $single ){
			list($spurl, $alias_url) = $single;
			$this->make($spurl, $alias_url, 1);
		}
	}
	
	public function zxmakeAll($spurls, $mode = 2)
	{
		if ($mode = 0 || $mode = 2) {
			foreach( $spurls as $single ){
				list($spurl, $alias_url) = $single;
				$this->make($spurl, $alias_url, 0);
			}
		}
		if ($mode = 1 || $mode = 2) {
			foreach( $spurls as $single ){
				list($spurl, $alias_url) = $single;
				$this->make($spurl, $alias_url, 1);
			}
		}
	}
	
	public function start(){spAccess('w','sp_html_making',1);$this->spurls = null;}
	public function commit(){spAccess('c','sp_html_making');$this->makeAll($this->spurls);}

	/**
	 * 获取url的列表程序，可以按配置开启是否检查文件存在
	 * @param controller    控制器名称，默认为配置'default_controller'
	 * @param action    动作名称，默认为配置'default_action' 
	 * @param args    传递的参数，数组形式
	 * @param anchor    跳转锚点
	 * @param force_no_check    是否检查物理文件是否存在
	 */
	
	public function getUrl($controller = null, $action = null, $args = null, $anchor = null, $force_no_check = FALSE)
	{
		if( $url_list = spAccess('r', 'sp_url_list') ){
			$url_list = explode("\n",$url_list);
			/*
			统一将传导过来的参数转化成字符串形式 start			
			zx 2012-04-19
			*/
			if (!empty($args)) {
				foreach ($args as $key => $val){
					$args[$key] = (string) $val;
				}
			}
			/*
			统一将传导过来的参数转化成字符串形式 end
			*/
			$args_en = !empty($args) ? json_encode($args) : "";
			$url_input = "{$controller}|{$action}|{$args_en}|$anchor|";
			/*
			if ($action == "clist") {
				echo "controller = $controller| action = $action|";
				echo "args = ";
				print_r($args);
				echo "url_list = ";
				print_r($url_list);
				echo "url_input = ";
				echo $url_input;
				exit();
			}
			*/
			foreach( $url_list as $url ){
				if( substr($url,0,strlen($url_input)) == $url_input ){
					$url_item = explode("|",substr($url,strlen($url_input)));
					if( TRUE == $GLOBALS['G_SP']['html']['safe_check_file_exists'] && TRUE != $force_no_check ){
						if( !is_readable($url_item[1]) )return FALSE;
					}
					return $url_item;
				}
			}
		}
		return FALSE;
	}
	
	public function getUrl_htmladv($controller = null, $action = null, $args = null, $anchor = null, $force_no_check = FALSE)
	{
		if(TRUE == $GLOBALS['G_SP']['html']["enabled_zxadv"]){
			try{
				$zxurl_array = array();
				if($controller == "main" && in_array($action, array("index", "clist", "cdetail"))){
					$obj_ag = spDB($GLOBALS['G_SP']['db']["prefix"]."articlestyle", "sid");
					$zxroot = WEB_ROOT.($GLOBALS['G_SP']['html']['file_root_name'] ? "/".$GLOBALS['G_SP']['html']['file_root_name'] : "");
					switch($action){
						case "index":
							$zxurl_array[] = $zxroot."/index.html";
						break;
						case "clist":
							if(!$id = intval($args["id"]))
								throw new Exception("no list id");
							if(!$column_rs = $obj_ag->find(array("sid"=>$id,"frontdisplay"=>1)))
								throw new Exception("no found column id: $id");
							if($column_rs["parentid"]){
								if(!$main_column_rs = $obj_ag->find(array("sid"=>$column_rs["parentid"],"frontdisplay"=>1)))
									throw new Exception("no found parent column id: $column_rs[parentid]");
								if($args["pid"]){
									$zxurl_add = "/".$args["pid"];
								}
								if($args["l"]){
									$zxurl_add = "/l".$args["l"];
								}
								if($args["f"]){
									$zxurl_add = "/f".$args["f"];
								}
								if($args["page"]){
									$zxurl_addfile = "_".$args["page"];
								}
								$zxurl_array[] = $zxroot."/".$main_column_rs["mark"]."/".$column_rs["mark"].$zxurl_add."/index".$zxurl_addfile.".html";
							}else{
								$zxurl_array[] = $zxroot."/".$column_rs["mark"]."/index.html";
							}
						break;
						case "cdetail":
							$obj_product = spDB($GLOBALS['G_SP']['db']["prefix"]."products", "pid");
							if(!$id = intval($args["id"]))
								throw new Exception("no news id");
							if(!$info_rs = $obj_product->findByPk($id))
								throw new Exception("no found news id: $id");
							$obj_ag = spDB($GLOBALS['G_SP']['db']["prefix"]."articlestyle", "sid");
							if (!$column_rs = $obj_ag->find(array("sid"=>$info_rs['ptype'], "parentid <>"=>0), null, "sid, parentid, mark, sname, cinfo, ismetatitle, islist, ishits, isposttime, ispackage, iscato, keywords, description, freeother, freeother2, freeother3, linkfield, displaydetail"))
							 	throw new Exception("no found column id: $info_rs[ptype]");
							if (!$main_column_rs = $obj_ag->find(array("sid"=>$column_rs['parentid'], "parentid"=>0)))
								throw new Exception("no found main column id: $column_rs[parentid]");
							$zxurl_array[] = $column_rs["islist"] ? $zxroot."/".$main_column_rs["mark"]."/".$column_rs["mark"]."/$id.html" : $zxroot."/".$main_column_rs["mark"]."/".$column_rs["mark"].".html";
						break;
					}
					return $zxurl_array;
				}
			}catch(Exception $e){
				//error continue
			}
		}
		if( $url_list = spAccess('r', 'sp_url_list') ){
			$url_list = explode("\n",$url_list);
			/*
			统一将传导过来的参数转化成字符串形式 start			
			zx 2012-04-19
			*/
			if (!empty($args)) {
				foreach ($args as $key => $val){
					$args[$key] = (string) $val;
				}
			}
			/*
			统一将传导过来的参数转化成字符串形式 end
			*/
			$args_en = !empty($args) ? json_encode($args) : "";
			$url_input = "{$controller}|{$action}|{$args_en}|$anchor|";
			/*
			if ($action == "clist") {
				echo "controller = $controller| action = $action|";
				echo "args = ";
				print_r($args);
				echo "url_list = ";
				print_r($url_list);
				echo "url_input = ";
				echo $url_input;
				exit();
			}
			*/
			foreach( $url_list as $url ){
				if( substr($url,0,strlen($url_input)) == $url_input ){
					$url_item = explode("|",substr($url,strlen($url_input)));
					if( TRUE == $GLOBALS['G_SP']['html']['safe_check_file_exists'] && TRUE != $force_no_check ){
						if( !is_readable($url_item[1]) )return FALSE;
					}
					return $url_item;
				}
			}
		}
		return FALSE;
	}
	
	/**
	 * 写入url的列表程序，在make生成页面后，将spUrl参数及页面地址写入列表中
	 *
	 * @param spurl spUrl的参数
	 * @param baseuri URL地址对应的静态HTML文件访问地址
     *
	 */
	public function setUrl($spurl, $baseuri, $realfile)
	{
		@list($controller, $action, $args, $anchor) = $spurl;
		/*
		统一将传导过来的参数转化成字符串形式 start			
		zx 2012-04-19
		*/
		if (!empty($args)) {
			foreach ($args as $key => $val){
				$args[$key] = (string) $val;
			}
		}
		/*
		统一将传导过来的参数转化成字符串形式 end
		*/
		$this->clear($controller, $action, $args, $anchor, FALSE);
		$args = !empty($args) ? json_encode($args) : '';
		$url_input = "{$controller}|{$action}|{$args}|{$anchor}|{$baseuri}|{$realfile}";
		if( $url_list = spAccess('r', 'sp_url_list') ){
			spAccess('w', 'sp_url_list', $url_list."\n".$url_input);
		}else{
			spAccess('w', 'sp_url_list', $url_input);
		}
	}

	/**
	 * 清除静态文件
	 * 
	 * @param controller    需要清除HTML文件的控制器名称
	 * @param action    需要清除HTML文件的动作名称，默认为清除该控制器全部动作产生的HTML文件
	 * 如果设置了action将仅清除该action产生的HTML文件
	 *
	 * @param args    传递的参数，默认为空将清除该动作任何参数产生的HTML文件
	 * 如果设置了args将仅清除该动作执行参数args而产生的HTML文件
	 *
	 * @param anchor    跳转锚点，默认为空将清除该动作任何锚点产生的HTML文件
	 * 如果设置了anchor将仅清除该动作跳转到锚点anchor产生的HTML文件
	 *
	 * @param delete_file    是否删除物理文件，FALSE将只删除列表中该静态文件的地址，而不删除物理文件。
	 */
	public function clear($controller, $action = null, $args = FALSE, $anchor = '', $delete_file = TRUE)
	{
		if( $url_list = spAccess('r', 'sp_url_list') ){
			$url_list = explode("\n",$url_list);$re_url_list = array();
			if( null == $action ){
				$prep = "{$controller}|";
			}elseif( FALSE === $args ){
				$prep = "{$controller}|{$action}|";
			}else{
				$args = !empty($args) ? json_encode($args) : '';
				$prep = "{$controller}|{$action}|{$args}|{$anchor}|";
			}
			foreach( $url_list as $url ){
				if( substr($url,0,strlen($prep)) == $prep ){
					$url_tmp = explode("|",$url);$realfile = $url_tmp[5];
					if( TRUE == $delete_file )@unlink($realfile);
				}else{
					$re_url_list[] = $url;
				}
			}
			spAccess('w', 'sp_url_list', join("\n", $re_url_list));
		}
	}
	

	/**
	 * 清除全部静态文件
	 * 
	 * @param delete_file    是否删除物理文件，FALSH将只删除列表中该静态文件的地址，而不删除物理文件。
	 */
	public function clearAll($delete_file = FALSE)
	{
		if( TRUE == $delete_file ){
			if( $url_list = spAccess('r', 'sp_url_list') ){
				$url_list = explode("\n",$url_list);
				foreach( $url_list as $url ){
					$url_tmp = explode("|",$url);$realfile = $url_tmp[5];
					@unlink($realfile);
				}
			}
		}
		spAccess('c', 'sp_url_list');
	}
}