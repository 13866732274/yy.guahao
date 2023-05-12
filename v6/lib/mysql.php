<?php
/*
mysql class by yangming,zhuwenya
建立时间：2007-6-15 17:07
更新 2007-6-16 12:20 select 结果数为1的时候
更新 2007-8-29 13:30 增加函数get_count,select_db可以同时连接操作两个或两个以上的
数据库
更新 2007-08-29 15:02 增加对charset的处理。如果连接时未指定第五个参数 charset，则
不使用charset设置；如果指定了，则使用之；如果传递了空值，则使用默认的charset设置
更新 2007-08-29 15:57 修改了一些函数默认参数，以及对错误的处理（如忘记了传递表名）
更新 2007-12-15 14:40 修改了类的几个成员函数的特性，select, upate,insert等函数只
负责生成sql语句，执行则通过统一的接口query()函数 - zhuwenya
更新 2008-07-31 23:19 增加函数 query_key,以简化在对大型的复杂的项目中
需要经常处理对照的情况 - zhuwenya
更新 2008-09-05 09:05 修改函数 query() 使其支持更多参数查询,代替原 query_key 中的
功能 - zhuwenya
更新 2008-09-06 11:24 数据不应作为类库本身的一部分,所以,参数外置,且外部定义参数优先
更新 2008-09-21 01:01 记录慢查询，且日志按天保存为文件
更新 2009-04-25 14:16 删除不常用函数
更新 2011-12-22 16:30 更新功能（移去不常用函数）
*/

class mysql {
	var $dblink;
	var $result;
	var $sql = '';
	var $select_count = 0;

	// 类初始化
	function __construct($connect_string = "")
	{
		if ($connect_string == '') $connect_string = "bG9jYWxob3N0fHJvb3R8fHl5X2NqZHgxMjBfY29tfGdiaw==";
		list($a, $b, $c, $d, $e) = explode("|", base64_decode($connect_string), 5);
		if ($a && $b) {

			if (!($this->dblink = @new mysqli($a, $b, $c))) {
				exit('&nbsp; 喔~ MySQL数据库无法连接！请立即联系服务器管理员处理。');
			}
			$select_db = $this->dblink->select_db($d);
			if (!$select_db) {
				exit("mysql error: the database '" . $d . "' not exists.");
			}
			$this->dblink->query("set names '{$e}';"); //编码转化

		} else {
			exit('mysql error: connect parameters not enough.');
		}
	}

	// 将数组合成 sql 插入数据格式
	function sqljoin($data)
	{
		$data_array = array();
		foreach ($data as $k => $v) {
			$data_array[] = "$k='{$v}'";
		}
		return implode(",", $data_array);
	}

	// 将一个数组结构插入数据库 成功返回插入id，失败返回false @ 2014-7-20
	function insert($table, $data_arr)
	{
		if (is_array($data_arr) && count($data_arr) > 0) {
			$s = $this->sqljoin($data_arr);
			if ($s != '') {
				if ($this->dblink->query("insert into $table set $s")) {
					return $this->dblink->insert_id;
				}
			}
		}
		return false;
	}

	// query() 更新于 2007-12-17 10:29 zhuwenya
	// 2008-09-05 00:22 修改: 增加参数
	// 现用法：后两个参数可忽略，视同一般查询，返回正常结果
	// $return_count_or_key_field 如果是数字，则返回该数字指定条结果;
	// 如果是一个字串，视为返回结果的键名;
	// $value_field 是返回结果中的键值，如果不指定（默认），返回查询到的全部字段.
	// 具体区别请多多试验即知，在处理列表中，配合此函数的后两个参数尤其方便
	// 原实现为 query_key, 效率稍差（处理了两次循环）
	// 典型用例：
	// 查询第一条结果: $item = $db->query("select * from user order by addtime desc", 1);
	// 直接返回一个值： $username = $db->query("select name from user where uid=3", 1, "name");
	// 查询生成一个键名数组： $prod_list = $db->query("select id,prod_name,prod_pic from product where views>10000", "id");
	// 查询一个对照数组: $uid_to_name = $db->query("select uid,username from user", "uid", "username");
	// 按照值生成一个数组: $ids = $db->query("select id from product where views>10000", "", "id");
	// last modify by weelia @ 2008-09-05 00:42
	function query($sql, $return_count_or_key_field = '', $value_field = '')
	{
		$this->sql = trim($sql);

		// 分析查询的类型,根据sql第一个词 insert select update delete ...
		list($query_type, $other) = explode(' ', $this->sql, 2);
		$query_type               = strtolower($query_type); //统一为小写

		// 执行查询:
		$this->result = $this->dblink->query($this->sql);

		// 处理错误:
		if (!$this->result) {
			$this->error();
			return false;
		}

		// 查询结果处理:
		if ($query_type == "select") {
			$this->select_count += 1;
			// 对参数 return_count_or_key_field 的处理(判断其为数值则表示查询返回条数,否则表示返回数组要使用的键名)
			if ($return_count_or_key_field !== "") {
				if (is_numeric($return_count_or_key_field)) {
					$return_count = $return_count_or_key_field;
				} else {
					$key_field = $return_count_or_key_field;
				}
			}

			// select 结果:
			$rs = array();
			while ($row = $this->result->fetch_assoc()) {
				if ($return_count == 1) {
					return $value_field ? $row[$value_field] : $row;
				}
				if ($key_field) {
					$rs[$row[$key_field]] = $value_field ? $row[$value_field] : $row;
				} else {
					$rs[] = $value_field ? $row[$value_field] : $row;
				}
			}
			if ($return_count == 1 && $value_field != '') {
				return false;
			}
			return $rs;
		} elseif ($query_type == "insert") {
			return $this->dblink->insert_id;
		}

		// 其他查询情况如果正确执行均返回成功:
		return true;
	}


	// 查询并获取结果集中的第一条资料
	function query_first($sql)
	{
		return $this->query($sql, 1);
	}

	function query_count($sql)
	{
		return $this->query($sql, 1, "count(*)");
	}

	// 显示错误
	function error()
	{
		echo '<br>';
		//if ($this->sql) {
		//	echo "SQL: ".$this->sql.'<br>';
		//}
		if ($this->dblink) {
			echo "SQL Error: " . $this->dblink->error . '<br>' . "\r\n";
		}
	}
}