<?php
/////////////////////////////////////////////////////////////////
// SpeedPHP中文PHP框架, Copyright (C) 2008 - 2010 SpeedPHP.com //
/////////////////////////////////////////////////////////////////

/**
 * db_mysql MySQL数据库的驱动支持 
 */
class db_mysql {
	/**
	 * 数据库链接句柄
	 */
	public $conn;
	
	/**
	 * 查询表
	 */
	private $table;

	/**
	 * 查询字段
	 */
	private $fields;

	/**
	 * 查询条件
	 */
	private $where;

	/**
	 * 查询条件(使用where方法添加的条件)
	 */
	private $_where;

	/**
	 * 比较操作符
	 */
	private $operators = array('=', '!=', '<>', '>=', '<=', '>', '<', ' like', ' not like');
	
	/**
	 * 排序
	 */
	private $sort;

	/**
	 * 分组
	 */
	private $group;

	/**
	 * 查询条数
	 */
	private $limit;

	/**
	 * 连接表
	 */
	private $join;

	/**
	 * 执行的SQL语句记录
	 */
	public $arrSql;

	/**
	 * 是否调试（输出sql和结果）
	 */
	public $debug = false;

	/**
	 * 构造函数
	 *
	 * @param dbConfig  数据库配置
	 */
	public function __construct($dbConfig)
	{
		$linkfunction = ( TRUE == $dbConfig['persistent'] ) ? 'mysql_pconnect' : 'mysql_connect';
		$this->conn = $linkfunction($dbConfig['host'].":".$dbConfig['port'], $dbConfig['login'], $dbConfig['password']) or spError("数据库链接错误 : " . mysql_error()); 
		mysql_select_db($dbConfig['database'], $this->conn) or spError("无法找到数据库，请确认数据库名称正确！");
		$this->exec("SET NAMES UTF8");
	}

	/**
	 * 获取数据库版本
	 */
	public function version(){
		return mysql_get_server_info($this->conn);
	}
	
	/**
	 * 开始事务
	 */
	public function beginTrans(){
		return mysql_query("BEGIN");
	}

	/**
	 * 提交事务
	 */
	public function commitTrans(){
		return mysql_query("COMMIT");
	}

	/**
	 * 回滚事务
	 */
	public function rollbackTrans(){
		return mysql_query("ROLLBACK");
	}
	
	/**
	 * 计算符合条件的记录数量
	 *
	 * @param conditions   查找条件，数组array("字段名"=>"查找值")或字符串
	 * @param field        计数字段
	 * @param clear	       清除条件
	 */
	public function findCount($tbl_name, $conditions = null, $field = null, $clear = true){
		$where = $this->getWhere($conditions);
		$sql = "SELECT COUNT({$tbl_name}.{$field}) as {$tbl_name}_count FROM {$tbl_name}";
		if($this->join) {
			$sql .= $this->join;
		}
		if($where) {
			$sql .= $where;
		}
		if($clear) {
			$this->reset();
		}
		$result = $this->getArray($sql);
		return $result[0]["{$tbl_name}_count"];
	}

	/**
	 * 从数据表中查找记录
	 *
	 * @param tbl_name    数据表名
	 * @param conditions  查找条件，数组array("字段名"=>"值")或SQL条件字符串,字段名中可包含比较符，如!=, >, <, like等
	 * @param sort        排序
	 * @param fields      查询字段，默认为*
	 * @param limit       返回的结果数
	 */
	public function getAll($tbl_name, $conditions = null, $sort = null, $fields = null, $group = null, $limit = null){
		if(null != $group) {
			$group = " GROUP BY {$group}";
		}
		if(null != $sort) {
			$sort = " ORDER BY {$sort}";
		}
		if(null != $limit) {
			$limit = " LIMIT {$limit}";
		}
		$this->table = $tbl_name;
		$this->getWhere($conditions);
		$this->fields = empty($fields) ? "*" : $fields;
		$this->sort = $sort;
		$this->group = $group;
		$this->limit = $limit;
		
		$sql = $this->getSelect();
		$this->reset();
		return $this->getArray($sql);
	}
	
	/**
	 * 生成查询条件
	 */
	public function getWhere($conditions = null) {
		$where = "";
		if(is_array($conditions)){
			$join = array();
			foreach($conditions as $field=>$fvalue) {
				$condition = $this->__quote_value($fvalue);
				foreach($this->operators as $op) {
					$found = false;
					if(false !== strpos($field, $op)) {
						$found = true;
						$join[] = "{$field} {$condition}";
						break;
					}
				}
				if(!$found) {
					$join[] = $this->__quote_field($field) . " = {$condition}";
				}
			}
			$where = join(" AND ", $join);
		}
		else {
			if(null != $conditions) {
				$where = $conditions;
			}
		}

		if(null != $this->_where) {
			if(null != $where) {
				$where = $this->_where . ' AND ' . $where;
			}
			else {
				$where = $this->_where;
			}
		}
		
		if(null != $where) {
			if(false === strpos($where, ' WHERE ')) {
				$this->where = ' WHERE ' . $where;
			}
			else {
				$this->where = $where;
			}
		}
		return $this->where;
	}
	
	/**
	 * 生成查询语句
	 */
	public function getSelect() {
		$sql = "SELECT {$this->fields} FROM {$this->table}";
		if($this->join) {
			$sql .= $this->join;
			$this->join = null;
		}
		if($this->where) {
			$sql .= $this->where;
		}
		if($this->group) {
			$sql .= $this->group;
		}
		if($this->sort) {
			$sql .= $this->sort;
		}
		if($this->limit) {
			$sql .= $this->limit;
		}
		return $sql;
	}
	
	/**
	 * 关联表查询
	 *
	 * @param  $tbl_name    关联的表名
	 * @param  $%join_cond  关联条件
	 */
	public function joinTable($tbl_name, $join_cond, $type = 'inner') {
		$this->join .= " {$type} join {$tbl_name} ON $join_cond";
		return $this;
	}
	
	/**
	 * 添加查询条件
	 *
	 * @param $field     字段名
	 * @param $value     字段值
	 * @param $relation  本次addWhere条件之间逻辑关系，默认为AND
	 * @param $relation2 与上次addWhere条件之间逻辑关系，默认为AND
	 *
	 * $value值省略时，$filed可以为数组或条件语句
	 * $field中可以包括比较符(<,>,<>,!=,>=,<=,like,not like)
	 
	 * 例：
	 * $members->where('username', 'zyyutian');
	 * $members->where('username<>', 'zyyutian');
	 *
	 * $members->where(array('username'=>'zyyutian'));
	 * $members->where("username='zyyutian'");
	 *
	 * $members->where(array('username!='=>'zyyutian', 'email<>' => 'zyyutian@qq.com' ));
	 */
	public function addWhere($fields, $value = null, $relation = 'AND', $relation2 = 'AND') {
		if(null == $fields) {
			return $this;
		}
		
		if(null === $value) {
			if(is_array($fields)) {
				foreach($fields as $field=>$fvalue) {
					$condition = $this->__quote_value($fvalue);
					foreach($this->operators as $op) {
						$found = false;
						if(false !== strpos($field, $op)) {
							$found = true;
							$join[] = "{$field} {$condition}";
							break;
						}
					}
					if(!$found) {
						$join[] = $this->__quote_field($field) . " = {$condition}";
					}
				}
			}
			else {
				$join[] = "{$fields}";
			}
		}
		else {
			if(!is_array($fields) && !is_array($value)) {
				$condition = $this->__quote_value($value);
				$found = false;
				foreach($this->operators as $op) {
					if(false !== strpos($fields, $op)) {
						$found = true;
						$join[] = "{$fields} {$condition}";
						break;
					}
				}
				if(!$found) {
					$join[] = $this->__quote_field($fields) . " = {$condition}";
				}
			}
			else {
				spError("参数类型不正确，参数应该为字符串");
			}
		}

		$where = join(" {$relation} ", $join);
		if(null != $this->_where) {
			$this->_where .= " {$relation2} ";
		}
		$this->_where .= "({$where})";
	}
	
	/**
	 * 按SQL语句获取记录结果，返回数组
	 * 
	 * @param sql  执行的SQL语句
	 */
	public function getArray($sql)
	{
		if( ! $result = $this->exec($sql) )return FALSE;
		if( ! mysql_num_rows($result) )return FALSE;
		$rows = array();
		while($rows[] = mysql_fetch_array($result,MYSQL_ASSOC)){}
		mysql_free_result($result);
		array_pop($rows);
		if($this->debug) {
			$this->dumpResult($sql, $rows);
		}
		return $rows;
	}
	
	/**
	 * 在数据表中插入一条数据
	 *
	 * @param row 数组形式，数组的键是数据表中的字段名，键对应的值是需要新增的数据。
	 */
	public function create($tbl_name, $row){
		foreach($row as $key => $value){
			$cols[] = $this->__quote_field($key);
			$vals[] = $this->__quote_value($value);
		}
		$col = join(',', $cols);
		$val = join(',', $vals);
		
		$sql = "INSERT INTO {$tbl_name} ({$col}) VALUES ({$val})";
		if($this->exec($sql)){
			if( $insert_id = $this->newinsertid() ){
				return $insert_id;
			}
			return true;
		}
		return false;
	}
	
	/**
	 * 返回当前插入记录的主键ID
	 */
	public function newinsertid()
	{
		return mysql_insert_id($this->conn);
	}
	
	/**
	 * 按条件删除记录
	 *
	 * @param conditions  数组形式，查找条件，此参数的格式用法与getAll的查找条件参数是相同的。
	 */
	public function delete($tbl_name, $conditions){
		$where = $this->getWhere($conditions);
		$sql = "DELETE FROM {$tbl_name} {$where}";
		//$this->reset();
		return $this->exec($sql);
	}
	
	/**
	 * 修改数据
	 * 
	 * @param conditions  数组形式，查找条件，此参数的格式用法与getAll的查找条件参数是相同的。
	 * @param row         数组形式，修改的数据，
	 */
	public function update($tbl_name, $conditions, $row){
		$where = $this->getWhere($conditions);

		foreach($row as $key => $value){
			$value = $this->__quote_value($value);
			$vals[] = $this->__quote_field($key) . " = {$value}";
		}
		$values = join(", ", $vals);
		$sql = "UPDATE {$tbl_name} SET {$values} {$where}";
		//$this->reset();
		return $this->exec($sql);
	}

	/**
	 * 执行一个SQL语句
	 * 
	 * @param sql 需要执行的SQL语句
	 */
	public function exec($sql)
	{
		$this->arrSql[] = $sql;
		if( $result = mysql_query($sql, $this->conn) ){
			if($this->debug) {
				echo "<hr><div style='font-weight:bold'>查询语句:</div>{$sql}<br/>";
			}
			return $result;
		}else{
			if($GLOBALS['G_SP']['db_debug']){
				echo "{$sql}<br />执行错误: " . mysql_error();
			}else{
				return false;
			}
		}
	}
	
	/**
	 * 返回影响行数
	 */
	public function affected_rows()
	{
		return mysql_affected_rows($this->conn);
	}

	/**
	 * 获取数据表结构
	 *
	 * @param tbl_name  表名称
	 */
	public function getTable($tbl_name)
	{
		return $this->getArray("DESCRIBE {$tbl_name}");
	}
	
	/**
	 * 对特殊字符进行过滤
	 *
	 * @param value  值
	 */
	public function __val_escape($value) {
		if(is_null($value))return 'NULL';
		if(is_bool($value))return $value ? 1 : 0;
		if(is_int($value))return (int)$value;
		if(is_float($value))return (float)$value;
		//if(@get_magic_quotes_gpc())$value = stripslashes($value);
		return mysql_real_escape_string($value, $this->conn);
	}

	/**
	 * 对数据字段加引号
	 *
	 * @param value  值
	 */
	public function __quote_value($value) {
		if(is_array($value)) {
			switch($value['type']) {
				case 'int':
				case 'long':
				case 'float':
				case 'number':
				case 'function':
				case 'expression':
					return $value['value'];
					break;
				default:
					return "'" . $this->__val_escape($value['value']) . "'";
			}
		}
		else {
			return "'" . $this->__val_escape($value) . "'";
		}
	}
	
	/**
	 * 对字段名加引号
	 *
	 * @param value  值
	 */
	public function __quote_field($field) {
		if($GLOBALS['G_SP']['db']['quote_field'] && strpos($field, '.') === false  && strpos($field, '`') === false) {
			return '`' . $field . '`';
		}
		else {
			return $field;
		}
	}	

	
	/**
	 * 设置为初始状态
	 */
	public function reset() {
		$this->table = null;
		$this->fields = null;
		$this->where = null;
		$this->_where = null;
		$this->sort = null;
		$this->group = null;
		$this->limit = null;
		$this->join = null;
		$this->sql = null;
	}	
	
	/**
	 * 输出sql语句和执行结果
	 */
	public function dumpResult($sql, $result) {
		echo "<div style='font-weight:bold'>查询结果:</div>";

		if(is_array($result)) {
			echo "<table border='1' cellpadding=4 cellspacing=2 bordercolor=#666666>";
			foreach($result as $i => $row){
				if($i==0){
					echo '<tr bgcolor=#DDDDDD>';
					foreach($row as $col => $value){
						echo '<th>' . $col . '</th>';
					}
					echo '</tr>';
				}
				echo '<tr>';
				foreach($row as $col => $value){
					echo '<td>' . $value . '</td>';
				}
				echo '</tr>';
			}
			echo '</table><br/>';
		}
	}
	
	
	/**
	 * 析构函数
	 */
	public function __destruct()
	{
		if( TRUE != $GLOBALS['G_SP']['db']['persistent'] )@mysql_close($this->conn);
	}
}
?>