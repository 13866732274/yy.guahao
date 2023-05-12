<?php
/*
// 作者: 幽兰 (weelia@126.com)
*/
require "lib/set_env.php";
$hospital_changed = 0;


if ($_GET["op"] == 'tys_change') {
	$_SESSION[$cfgSessionName]["hospital_id"] = intval($_GET["hid"]);
	echo 'goto_id_do();';
	exit;
}


if ($_GET["do"] == 'change') {
	$hospital_changed = 1;
	if (in_array(intval($_GET["hospital_id"]), $hospital_ids)) {
		$hid    = $_SESSION[$cfgSessionName]["hospital_id"] = intval($_GET["hospital_id"]);
		$h_name = $db->query("select name from hospital where id=$hid limit 1", 1, "name");

		// 切换成功提示
		echo '<script type="text/javascript">';
		echo 'parent.update_content();';
		echo 'parent.change_hospital();';
		//echo 'parent.msg_box("切换至：'.$h_name.'");';
		echo '</script>';
	} else {
		exit("对不起，您没有权限切换到此科室。");
	}
	exit;
}

// 切换医院下拉列表:
$hids         = count($hospital_ids) > 0 ? implode(",", $hospital_ids) : "0";
$g_arr        = $db->query("select * from hospital_group order by sort desc", "id", "name");
$html         = '';
$jschange_hid = array();
foreach ($g_arr as $g_id => $g_name) {
	$h_list = $db->query("select id,name,color from hospital where ishide=0 and group_id=$g_id and id in ($hids) order by sort desc, name asc", "id");
	if (count($h_list) > 0) {
		$html .= '<a href="javascript:;" class="a_area">' . $g_name . ' <span class="a_area_count">(' . count($h_list) . ')</span></a>';
		foreach ($h_list as $h_id => $h_info) {
			$jschange_hid[] = $h_id;
			$url            = "?do=change&hospital_id=" . $h_id;
			$color          = $h_info["color"] ? $h_info["color"] : "";
			$html .= '<a id="h_' . $h_id . '" href="' . $url . '" class="a_hos" style="color:' . $color . '">' . $h_info["name"] . '</a>';
		}
	}
}


// JS切换
if ($_GET["op"] == "jschange") {
	$mode = $_GET["mode"] == "pre" ? "pre" : "next";

	$to_hid = 0;
	$remind = '';
	if ($hid <= 0 || !in_array($hid, $jschange_hid)) {
		// 尚未切换到任何医院，直接切换到第一家
		$to_hid = $jschange_hid[0];
	} else {
		$cur_key = array_search($hid, $jschange_hid);
		if ($mode == "pre") {
			if ($cur_key > 0) {
				$to_hid = $jschange_hid[$cur_key - 1];
			} else {
				$remind = "当前已经是最上一个科室";
			}
		} else {
			if ($cur_key < count($jschange_hid) - 1) {
				$to_hid = $jschange_hid[$cur_key + 1];
			} else {
				$remind = "当前已经是最下一个科室";
			}
		}
	}

	if ($to_hid > 0) {
		$_SESSION[$cfgSessionName]["hospital_id"] = $to_hid;
		echo 'hospital_change_do();';
	} else {
		echo 'alert("' . $remind . '");';
	}
	exit;
}

if (count($jschange_hid) == 0) {
	$html = '<div style="padding:10px;">(暂无数据)</div>';
}

?>
<html>

<head>
	<title>切换医院</title>
	<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
	<link href="lib/base.css" rel="stylesheet" type="text/css">
	<script src="lib/base.js" language="javascript"></script>
	<style type="text/css">
		body {
			margin: 0px !important;
			padding: 0px !important;
		}

		#hospital_list {
			margin: 0;
			padding: 0;
		}

		#hospital_list a {
			display: block;
			font-size: 12px;
		}

		.a_area {
			padding: 1px 0px 0px 10px;
			color: red;
			font-weight: bold;
			background-color: #dfecf0;
			cursor: default;
		}

		.a_area_count {
			color: gray;
			font-weight: normal;
		}

		.a_hos {
			padding: 1px 0px 0px 30px;
		}

		.a_hos:hover {
			background-color: #ffe6d9;
		}

		.cur_h {
			background-color: #dcac98;
		}
	</style>
</head>

<body id="body">

	<div id="hospital_list">
		<?php echo $html; ?>
	</div>


	<?php if ($hid > 0) { ?>
		<script type="text/javascript">
			var cur = byid("h_<?php echo $hid; ?>");
			if (cur) {
				cur.className = "a_hos cur_h";
				cur.scrollIntoView();
				if (byid("body").scrollHeight - byid("body").scrollTop > 500) {
					byid("body").scrollTop = byid("body").scrollTop - 84;
				}
			}
		</script>
	<?php } ?>

</body>

</html>