<?php
// --------------------------------------------------------
// - ����˵�� : ���õ�Ժ
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2011-09-14
// --------------------------------------------------------
require "lib/set_env.php";
$table = "patient_" . $user_hospital_id;

if ($user_hospital_id == 0) {
	exit_html("�Բ���û��ѡ��ҽԺ����������ҳѡ��ҽԺ��");
}

if (!in_array("set_come", $gGuaHaoConfig)) {
	exit("�Բ�����û�й���ԺȨ��...");
}


$status_array = array(0 => '�ȴ�', 1 => '�ѵ�', 2 => 'δ��');

$id = intval($_REQUEST["id"]);
if (!$id) {
	exit("��������.");
}

$line = $db->query_first("select * from $table where id='$id' limit 1");

// ����ҽ��:
$doctor_list = $db->query("select id,name from doctor where hospital_id='$user_hospital_id'");
//$doctor_list = $db->query("select id,realname from sys_admin where part_id=15 and concat(',',hospitals,',') like '%,{$hid},%'", "id", "realname");

// �ֳ�ҽ��:
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

	// ҽ�� @ 2013-12-19
	$r["is_yibao"] = $p["is_yibao"] ? 1 : 0;

	// �������ڵ� @ 2015-6-17
	$r["suozaidi"] = intval($p["suozaidi"]);

	// �ֶ��޸ļ�¼:
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

		// ���¹�����״̬ @ 2016-10-19
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
			$str = "�����ύ�ɹ���";
		} else {
			$str = "�ύ�������Ժ����ԡ�";
		}
	} else {
		$str = "�����ޱ䶯";
	}

	//user_op_log("�޸ĵ�Ժ״̬[".$line["name"]."]");

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

	// ͬ��������̿�ǿ
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
    <title><?php echo $line["name"]; ?> - ���õ�Ժ</title>
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
                <td class="left">��Ժ״̬��</td>
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
                    <font color="red">(�ѵ�Ժ �����޸�)</font>-->
                    <?php /* } */ ?>
                </td>
            </tr>
            <tr>
                <td class="left" width="70">ҽ����</td>
                <td class="right">
                    <?php if (count($xianchang_doctor) > 0) { ?>
                    <select style="width:90px;" name="xianchang_doctor" class="combo">
                        <option value="" style="color:gray">-�ֳ�ҽ��-</option>
                        <?php echo list_option($xianchang_doctor, '_value_', '_value_', $line["xianchang_doctor"]); ?>
                    </select>&nbsp;
                    <?php } ?>
                    <select style="width:90px;" name="doctor" class="combo">
                        <option value="" style="color:gray">-����ҽ��-</option>
                        <?php echo list_option($doctor_list, 'name', 'name', $line["doctor"]); ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="left">�־�ס�أ�</td>
                <td class="right">
                    <select name="suozaidi">
                        <?php
						$_array = array("1" => "����", "2" => "���", "3" => "����", "4" => "��˳", "5" => "�Ͻ�", "6" => "����", "7" => "����ˮ", "8" => "ǭ��", "9" => "ǭ����", "10" => "ǭ����", "11" => "ͭ��", "12" => "����");

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
                <td class="left">����������</td>
                <td class="right"><input name="name" value="<?php echo $line["name"]; ?>" class="input"
                        style="width:90px"> (���ʵ��)</td>
            </tr>
            <!-- <tr>
		<td class="left">��ע��</td>
		<td class="right"><?php echo $line["memo"] ? text_show($line["memo"]) : "(�ޱ�ע)"; ?></td>
	</tr> -->
            <tr>
                <td class="left">��ӱ�ע��</td>
                <td class="right"><textarea name="memo" style="width:200px; height:48px;" class="input"></textarea></td>
            </tr>
        </table>
        <input type="hidden" name="id" id="id" value="<?php echo $id; ?>">
        <div class="button_line">
            <input type="submit" class="buttonb" value="�ύ����"> &nbsp;<input type="checkbox" name="is_yibao"
                id="is_yibao" value="1" <?php echo $line["is_yibao"] ? " checked" : ""; ?>><label for="is_yibao"
                title="��ѡ��˵���˻�����ҽ��">ҽ��</label>
        </div>
    </form>
</body>

</html>