<?php
// --------------------------------------------------------
// - 功能说明 : 设置到院
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2011-09-14
// --------------------------------------------------------
require "lib/set_env.php";
$table = "patient_" . $user_hospital_id;

if ($user_hospital_id == 0) {
	exit_html("对不起，没有选择医院，请先在首页选择医院！");
}

if (!in_array("set_come", $gGuaHaoConfig)) {
	exit("对不起，你没有勾到院权限...");
}


$status_array = array(0 => '等待', 1 => '已到', 2 => '未到');

$id = intval($_REQUEST["id"]);
if (!$id) {
	exit("参数错误.");
}

$line = $db->query_first("select * from $table where id='$id' limit 1");

// 主治医生:
$doctor_list = $db->query("select id,name from doctor where hospital_id='$user_hospital_id'");
//$doctor_list = $db->query("select id,realname from sys_admin where part_id=15 and concat(',',hospitals,',') like '%,{$hid},%'", "id", "realname");

// 现场医生:
$xianchang_doctor = $db->query("select id,realname from sys_admin where part_id=14 and concat(',',hospitals,',') like '%,{$hid},%'", "id", "realname");

if ($_POST) {
	$p = $_POST;
	$r = array();
	$save_field = explode(" ", "name status xianchang_doctor doctor");
	foreach ($save_field as $v) {
		if ($v && isset($p[$v]) && $p[$v] != $line[$v]) {
			$r[$v] = $p[$v];
		}
	}
	if ($r["status"] == 1) {
		$r["order_date"] = time();
	}
	if ($p["memo"] != '') {
		$r["memo"] = trim($line["memo"]) . "\n" . date("Y-m-d H:i ") . $realname . ": " . $p["memo"];
	}

	// 医保 @ 2013-12-19
	$r["is_yibao"] = $p["is_yibao"] ? 1 : 0;

	// 患者所在地 @ 2015-6-17
	$r["suozaidi"] = intval($p["suozaidi"]);

	// 字段修改记录:
	if (count($r) > 0) {
		$logs = patient_modify_log($r, $line, "name status xianchang_doctor doctor order_date");
		if ($logs) {
			$r["edit_log"] = $logs;
		}
	}

	if (count($r) > 0) {
		$sqldata = $db->sqljoin($r);
		$sql = "update $table set $sqldata where id='$id' limit 1";
		ob_start();
		$rs = $db->query($sql);
		$error = ob_get_clean();

		// 更新关联表状态 @ 2016-10-19
		if ($line["from_table"] != "") {
			$ftable = $line["from_table"];
			$fid = $line["from_id"];
			if ($_POST["status"] > 0) {
				$db->query("update `$ftable` set is_come=1 where id=$fid limit 1");
			} else {
				$db->query("update `$ftable` set is_come=0 where id=$fid limit 1");
			}
		}

		if ($error) {
			echo $error;
			exit;
		}
		if ($rs) {
			$str = "资料提交成功！";
		} else {
			$str = "提交出错，请稍后再试。";
		}
	} else {
		$str = "资料无变动";
	}

	//user_op_log("修改到院状态[".$line["name"]."]");

	echo '<script type="text/javascript">' . "\r\n";
	echo 'parent.msg_box("' . $str . '");' . "\r\n";
	//echo 'parent.location.reload();'."\r\n";
	if (isset($r["status"])) {
		echo 'parent.document.getElementById("status_' . $id . '").innerHTML = "' . $status_array[$r["status"]] . '";' . "\r\n";
		if ($r["status"] == 1) {
			echo 'parent.document.getElementById("list_line_' . $id . '").style.color = "red";' . "\r\n";
		} else {
			echo 'parent.document.getElementById("list_line_' . $id . '").style.color = "";' . "\r\n";
		}
	}

	// 同步结果给蔡俊强
	if (strlen(trim($line["tel"])) == 11 && $r["status"] == 1) {
		$_t = $r["order_date"] > 0 ? $r["order_date"] : $line["order_date"];
		$_dt = date("YmdHis", $_t);
		$_st = 3;
		//echo 'parent.parent.sync_cai("'.$hid.'", "'.trim($line["tel"]).'", "'.$_st.'", "'.$_dt.'");';
	}

	echo 'parent.close_divs();' . "\r\n";
	echo '</script>' . "\r\n";
	exit;
}



// page begin ----------------------------------------------------
?>
<html>

<head>
    <title><?php echo $line["name"]; ?> - 设置到院</title>
    <meta http-equiv="Content-Type" content="text/html;charset=gb2312">
    <link href="lib/base.css" rel="stylesheet" type="text/css">
    <script src="lib/base.js" language="javascript"></script>
    <style>
    .left {
        text-align: right;
    }

    .right {
        padding: 3px 0px;
    }
    </style>
    <script language="javascript">
    function check_data() {
        var oForm = document.mainform;
        return true;
    }
    </script>
</head>

<body oncontextmenu="return false">
    <form name="mainform" action="" method="POST" onsubmit="return check_data()">
        <table width="100%">
            <tr>
                <td class="left">到院状态：</td>
                <td class="right">
                    <?php
					// if ($line["status"] != 1) {
					foreach ($status_array as $k => $v) {
						$chk = $k == $line["status"] ? "checked" : "";
					?>
                    <input type="radio" name="status" value="<?php echo $k; ?>" id="lab_<?php echo $k; ?>"
                        <?php echo $chk; ?>><label for="lab_<?php echo $k; ?>"
                        <?php if ($chk) echo ' style="color:red;"'; ?>><?php echo $v; ?></label>&nbsp;
                    <?php
					} ?>
                    <!--}else {
                    ?>
                    <font color="red">(已到院 不可修改)</font>-->
                    <?php /* } */ ?>
                </td>
            </tr>
            <tr>
                <td class="left" width="70">医生：</td>
                <td class="right">
                    <?php if (count($xianchang_doctor) > 0) { ?>
                    <select style="width:90px;" name="xianchang_doctor" class="combo">
                        <option value="" style="color:gray">-现场医生-</option>
                        <?php echo list_option($xianchang_doctor, '_value_', '_value_', $line["xianchang_doctor"]); ?>
                    </select>&nbsp;
                    <?php } ?>
                    <select style="width:90px;" name="doctor" class="combo">
                        <option value="" style="color:gray">-主治医生-</option>
                        <?php echo list_option($doctor_list, 'name', 'name', $line["doctor"]); ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="left">现居住地：</td>
                <td class="right">
                    <select name="suozaidi">
                        <?php
						$_array = array("1" => "本市", "2" => "外地", "3" => "贵阳", "4" => "安顺", "5" => "毕节", "6" => "遵义", "7" => "六盘水", "8" => "黔南", "9" => "黔西南", "10" => "黔东南", "11" => "铜仁", "12" => "其它");

						foreach ($_array as $k => $v) {
							$chk = $k == $line["suozaidi"] ? "selected" : "";
						?>

                        <option value="<?php echo $k; ?>" <?php if ($chk) echo 'SELECTED style="color:red;"'; ?>>
                            <?php echo $v; ?></option>

                        <?php
						}
						?>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="left">姓名纠正：</td>
                <td class="right"><input name="name" value="<?php echo $line["name"]; ?>" class="input"
                        style="width:90px"> (填患者实名)</td>
            </tr>
            <!-- <tr>
		<td class="left">备注：</td>
		<td class="right"><?php echo $line["memo"] ? text_show($line["memo"]) : "(无备注)"; ?></td>
	</tr> -->
            <tr>
                <td class="left">添加备注：</td>
                <td class="right"><textarea name="memo" style="width:200px; height:48px;" class="input"></textarea></td>
            </tr>
        </table>
        <input type="hidden" name="id" id="id" value="<?php echo $id; ?>">
        <div class="button_line">
            <input type="submit" class="buttonb" value="提交资料"> &nbsp;<input type="checkbox" name="is_yibao"
                id="is_yibao" value="1" <?php echo $line["is_yibao"] ? " checked" : ""; ?>><label for="is_yibao"
                title="勾选则说明此患者有医保">医保</label>
        </div>
    </form>
</body>

</html>