<?php
/* --------------------------------------------------------
// 说明: 操作 sys_part 部门
// 作者: 幽兰 (weelia@126.com)
// 时间: 2009-06-10 14:00
// ----------------------------------------------------- */

class part {
	var $db;
	var $tab;

	var $part_data;
	var $part_id_name;
	var $tmp;

	function part($db, $tab='') {
		$this->db = $db;
		$this->tab = $tab;

		$this->init();
	}

	// 初始化数组，整个进程只需读取一次数据库，所有操作通过数组操作:
	function init() {
		$this->part_data = $this->db->query("select * from ".$this->tab."sys_part order by id asc", "id");

		// 初始化 id=>name 数组:
		$this->part_id_name = array();
		foreach ($this->part_data as $id => $li) {
			$this->part_id_name[$id] = $li["name"];
		}
	}

	// 获取part_id对应的名字
	function get_part_name($part_id) {
		return $this->part_id_name[$part_id];
	}

	// 获取部门的资料
	function get_part_info($part_id) {
		return $this->part_data[$part_id];
	}

	// 按名字搜索part_id:
	function get_part_id_by_name($part_name) {
		foreach ($this->part_id_name as $id => $name) {
			if ($name == $part_name) {
				return $id;
			}
		}

		return false;
	}

	// 获取父类的部门序列:
	function get_parent($part_id) {
		// todo
	}

	// 获取子类的部门序列, 返回格式为数组:
	function get_sub_part($part_id=0, $with_self=1, $level=0) {
		// 初始化一个用于递归的数组:
		if ($level == 0) {
			$this->tmp = array();
		}

		// 是否包含其自身:
		if ($with_self && $part_id > 0) {
			$this->tmp[$part_id] = $level;
			$level++;
		}

		// 当前:
		foreach ($this->part_data as $id => $li) {
			if ($li["pid"] == $part_id) {
				$this->tmp[$id] = $level;
			}
		}

		// 递归查询:
		foreach ($this->part_data as $id => $li) {
			if ($li["pid"] == $part_id) {
				$this->get_sub_part($id, 0, ++$level);
			}
		}

		return $this->tmp;
	}

	// 列出缩进格式的part项目:
	function get_sub_part_list($part_id, $with_self=1, $space_char='&nbsp;&nbsp;&nbsp;&nbsp;') {
		$parts = $this->get_sub_part($part_id, $with_self);
		$out = array();
		foreach ($parts as $p_id => $p_level) {
			$out[$p_id] = str_repeat($space_char, $p_level).$this->part_id_name[$p_id];
		}

		return $out;
	}

}
?>