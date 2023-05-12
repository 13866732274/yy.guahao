<?php
/*
mysql class by yangming,zhuwenya
����ʱ�䣺2007-6-15 17:07
���� 2007-6-16 12:20 select �����Ϊ1��ʱ��
���� 2007-8-29 13:30 ���Ӻ���get_count,select_db����ͬʱ���Ӳ����������������ϵ�
���ݿ�
���� 2007-08-29 15:02 ���Ӷ�charset�Ĵ����������ʱδָ����������� charset����
��ʹ��charset���ã����ָ���ˣ���ʹ��֮����������˿�ֵ����ʹ��Ĭ�ϵ�charset����
���� 2007-08-29 15:57 �޸���һЩ����Ĭ�ϲ������Լ��Դ���Ĵ����������˴��ݱ�����
���� 2007-12-15 14:40 �޸�����ļ�����Ա���������ԣ�select, upate,insert�Ⱥ���ֻ
��������sql��䣬ִ����ͨ��ͳһ�Ľӿ�query()���� - zhuwenya
���� 2008-07-31 23:19 ���Ӻ��� query_key,�Լ��ڶԴ��͵ĸ��ӵ���Ŀ��
��Ҫ����������յ���� - zhuwenya
���� 2008-09-05 09:05 �޸ĺ��� query() ʹ��֧�ָ��������ѯ,����ԭ query_key �е�
���� - zhuwenya
���� 2008-09-06 11:24 ���ݲ�Ӧ��Ϊ��Ȿ���һ����,����,��������,���ⲿ�����������
���� 2008-09-21 01:01 ��¼����ѯ������־���챣��Ϊ�ļ�
���� 2009-04-25 14:16 ɾ�������ú���
���� 2011-12-22 16:30 ���¹��ܣ���ȥ�����ú�����
*/

class mysql {
	var $dblink;
	var $result;
	var $sql = '';
	var $select_count = 0;

	// ���ʼ��
	function __construct($connect_string = "")
	{
		if ($connect_string == '') $connect_string = "bG9jYWxob3N0fHJvb3R8fHl5X2NqZHgxMjBfY29tfGdiaw==";
		list($a, $b, $c, $d, $e) = explode("|", base64_decode($connect_string), 5);
		if ($a && $b) {

			if (!($this->dblink = @new mysqli($a, $b, $c))) {
				exit('&nbsp; �~ MySQL���ݿ��޷����ӣ���������ϵ����������Ա����');
			}
			$select_db = $this->dblink->select_db($d);
			if (!$select_db) {
				exit("mysql error: the database '" . $d . "' not exists.");
			}
			$this->dblink->query("set names '{$e}';"); //����ת��

		} else {
			exit('mysql error: connect parameters not enough.');
		}
	}

	// ������ϳ� sql �������ݸ�ʽ
	function sqljoin($data)
	{
		$data_array = array();
		foreach ($data as $k => $v) {
			$data_array[] = "$k='{$v}'";
		}
		return implode(",", $data_array);
	}

	// ��һ������ṹ�������ݿ� �ɹ����ز���id��ʧ�ܷ���false @ 2014-7-20
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

	// query() ������ 2007-12-17 10:29 zhuwenya
	// 2008-09-05 00:22 �޸�: ���Ӳ���
	// ���÷��������������ɺ��ԣ���ͬһ���ѯ�������������
	// $return_count_or_key_field ��������֣��򷵻ظ�����ָ�������;
	// �����һ���ִ�����Ϊ���ؽ���ļ���;
	// $value_field �Ƿ��ؽ���еļ�ֵ�������ָ����Ĭ�ϣ������ز�ѯ����ȫ���ֶ�.
	// ���������������鼴֪���ڴ����б��У���ϴ˺����ĺ������������䷽��
	// ԭʵ��Ϊ query_key, Ч���Բ����������ѭ����
	// ����������
	// ��ѯ��һ�����: $item = $db->query("select * from user order by addtime desc", 1);
	// ֱ�ӷ���һ��ֵ�� $username = $db->query("select name from user where uid=3", 1, "name");
	// ��ѯ����һ���������飺 $prod_list = $db->query("select id,prod_name,prod_pic from product where views>10000", "id");
	// ��ѯһ����������: $uid_to_name = $db->query("select uid,username from user", "uid", "username");
	// ����ֵ����һ������: $ids = $db->query("select id from product where views>10000", "", "id");
	// last modify by weelia @ 2008-09-05 00:42
	function query($sql, $return_count_or_key_field = '', $value_field = '')
	{
		$this->sql = trim($sql);

		// ������ѯ������,����sql��һ���� insert select update delete ...
		list($query_type, $other) = explode(' ', $this->sql, 2);
		$query_type               = strtolower($query_type); //ͳһΪСд

		// ִ�в�ѯ:
		$this->result = $this->dblink->query($this->sql);

		// �������:
		if (!$this->result) {
			$this->error();
			return false;
		}

		// ��ѯ�������:
		if ($query_type == "select") {
			$this->select_count += 1;
			// �Բ��� return_count_or_key_field �Ĵ���(�ж���Ϊ��ֵ���ʾ��ѯ��������,�����ʾ��������Ҫʹ�õļ���)
			if ($return_count_or_key_field !== "") {
				if (is_numeric($return_count_or_key_field)) {
					$return_count = $return_count_or_key_field;
				} else {
					$key_field = $return_count_or_key_field;
				}
			}

			// select ���:
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

		// ������ѯ��������ȷִ�о����سɹ�:
		return true;
	}


	// ��ѯ����ȡ������еĵ�һ������
	function query_first($sql)
	{
		return $this->query($sql, 1);
	}

	function query_count($sql)
	{
		return $this->query($sql, 1, "count(*)");
	}

	// ��ʾ����
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