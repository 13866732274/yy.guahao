<?php
// --------------------------------------------------------
// - ����˵�� : �������ϲ鿴
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2009-05-02 17:28
// --------------------------------------------------------
require "lib/set_env.php";

if ($_GET["hid"] > 0) {
	$hid = $user_hospital_id = $_SESSION[$cfgSessionName]["hospital_id"] = intval($_GET["hid"]);
}

$table = "patient_".$user_hospital_id;
$hinfo = $db->query("select * from hospital where id=$hid limit 1", 1);

// �����ݽ��йؼ��ʹ��� @ 2014-8-23
$hid_filter_arr = array(
	3 => "",
);

$id = intval($_REQUEST["id"]);
if ($id > 0) {
	$line = $db->query("select * from $table where id='$id' limit 1", 1);
} else {
	exit("��������...");
}


if ($_POST["wee_memo"] != '') {
	$memo = trim(wee_safe_key(strip_tags($_POST["wee_memo"])));
	if ($memo != '') {
		$memo_str = trim(trim($line["memo"])."\n".date("Y-m-d H:i")." ".$realname.": ".$memo);
		$memo_str = str_replace("'", "", $memo_str);
		$db->query("update $table set memo='$memo_str' where id={$id} limit 1");
		echo '<script> alert("��ע�ɹ�����������������"); self.location = "?id='.$id.'"; </script>';
		exit;
	} else {
		exit("��ע������Ч");
	}
}


if ($_GET["op"] == "gen_yuyue_num") {

	$max_try_times = 100; // ����Դ���

	$gen_yuyue_num = '';
	for ($i=0; $i < $max_try_times; $i++) {
		$num = mt_rand(10000000, 99999999);
		$repeat = $db->query("select count(*) as c from yuyue_num_rand where yuyue_num=$num", 1, "c");
		if ($repeat > 0) {
			continue;
		} else {
			$gen_yuyue_num = $num;
			break;
		}
	}

	if ($gen_yuyue_num > 0) {
		$db->query("update $table set yuyue_num='$gen_yuyue_num' where id=$id limit 1");
		$db->query("insert into yuyue_num_rand set yuyue_num=$gen_yuyue_num, addtime=$time, uid=$uid");
	} else {
		exit("����ԤԼ��ʧ�ܣ���ˢ�����ԡ�");
	}

	header("location: ?id=$id");
	exit;
}

/*
if ($_GET["op"] == "send_sms") {
	// ����һ����֤��Ϣ:
	$mobile = $line["tel"];
	$sid = md5(sha1($mobile.time()));
	$db->query("insert into safe_check set sid='$sid', uname='$realname', mobile='$mobile', addtime='$time'");

	//$url = "?mobile={mobile}&hid={hid}&token={token}";
	$url = "http://shxjgk.cloudsapp.cn:8088/implant.aspx?t={mobile}&h={hname}&s={token}";
	$url = str_replace("{mobile}", $mobile, $url);
	$url = str_replace("{hid}", $hid, $url);
	$hinfo["short_name"] = mb_convert_encoding($hinfo["short_name"], "UTF-8", "gbk");
	$url = str_replace("{hname}", $hinfo["short_name"], $url);
	$url = str_replace("{token}", $sid, $url);

	header("location: $url");
	exit;
}
*/

//user_op_log("�鿴����[".$line["name"]."]");

//check_power("v", $pinfo, $pagepower) or msg_box("�Բ�����û�в鿴Ȩ��!", "back", 1);

$title = $line["name"]." ����";

$disease_id_name = $db->query("select id,name from disease where hospital_id=$user_hospital_id", 'id', 'name');
$part_id_name = $db->query("select id,name from sys_part", 'id', 'name');


$show_tel = 0;
if ($config["show_tel"] || $line["author"] == $realname) {
	$show_tel = 1;
}

if ($line["status"] == 1 && $config["show_come_doctor"] != 1) {
	$line["memo"] = _content_filter($line["memo"], $hid_filter_arr[$hid]);
}

if ($line["status"] == 1 && $config["show_come_tel"] != 1) {
	$show_tel = 0;
}

// 2016-3-29 ���֤��
if ($debug_mode || $line["uid"] == $uid || $uinfo["show_card_id"]) {
	$line["card_id"] = $line["card_id"];
} else {
	$line["card_id"] = $line["card_id"] ? (substr($line["card_id"], 0, -4)."****") : "";
}


// --------- ���� -----------
function talk_text_show($s) {
	$s = str_replace(" ", "&nbsp;", $s);
	$s = str_replace("\r", "", $s);
	$s = str_replace("\n", "<br>", $s);
	for ($i=0; $i<5; $i++) {
		$s = str_replace("<br><br>", "<br>", $s);
	}
	$s = "<br>".$s;
	$s = preg_replace("/<br>(��[^��]+?��)/", "<br><br><font color=red>\\1</font>", $s);
	$s = preg_replace("/<br>([^>]*?\d{2}:\d{2}:\d{2})/", "<br><br><font color=blue>[\\1]</font>", $s);
	while (substr($s, 0, 4) == "<br>") {
		$s = substr($s, 4);
	}
	return $s;
}

function _content_filter($str, $filter_string = '') {
	if (trim($filter_string) == '') return $str;
	$arr = explode(" ", trim($filter_string));
	foreach ($arr as $v) {
		$str = str_replace($v, "***", $str);
	}
	return $str;
}

?>
<html>
<head>
<title><?php echo $title; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style>
*:focus {outline:none; }
.view td {padding:5px 3px 3px 8px; border:1px solid #D9ECFF; }
.view .h {font-weight:bold; background:#E8F3FF; text-align:left; padding-left:15px; }
.view .l {text-align:right; color:#000000; background:#F4FAFF; }
.view .r {text-align:left; }
.fo_line {margin:15px 0 auto; text-align:center; }
</style>
<script type="text/javascript">

</script>
</head>

<body>

<table width="100%" align="center" class="view">
	<tr>
		<td colspan="4" class="h">
			��������
			<span style="margin-left:300px">ԤԼ�ţ�
			<?php
			if ($line["yuyue_num"] > 0) {
				echo $line["yuyue_num"];
			} else if ($line["uid"] == $uid) {
				echo '<a href="?op=gen_yuyue_num&id='.$id.'">����ԤԼ��</a>';
			} else {
				echo '(��)';
			}
			?>
			</span>
		</td>
	</tr>
	<tr>
		<td class="l">������</td>
		<td class="r">
			<b><?php echo $line["name"]; ?></b>
		</td>
		<td class="l">ID��</td>
		<td class="r"><?php echo $line["id"]; ?></td>
	</tr>
	<tr>
		<td class="l" width="15%">�Ա�</td>
		<td class="r" width="30%"><?php echo $line["sex"]; ?></td>
		<td class="l" width="15%">���䣺</td>
		<td class="r" width="40%"><?php echo $line["age"] > 0 ? $line["age"] : ""; ?></td>
	</tr>
	<tr>
		<td class="l">�绰��</td>
		<td class="r"><?php echo tel_filter($line); ?> <?php echo $line["tel_location"]; ?></td>
		<td class="l">�ͷ�������</td>
		<td class="r"><?php echo $line["author"]; ?> @ <?php echo date("Y-m-d H:i:s", $line["addtime"]); ?> <?php echo $part_id_name[$line["part_id"]]; ?></td>
	</tr>
	<tr>
		<td class="l">΢�ţ�</td>
		<td class="r"><?php echo $line["weixin"]; ?><?php echo $line["order_weixin"] != "" ? "���ҷ�΢�ţ�".$line["order_weixin"] : ""; ?></td>
		<td class="l">QQ��</td>
		<td class="r"><?php echo $line["qq"]; ?><?php echo $line["order_qq"] != "" ? "���ҷ�QQ��".$line["order_qq"] : ""; ?></td>
	</tr>
	<tr>
		<td class="l">���֤�ţ�</td>
		<td class="r"><?php echo $line["card_id"] ? $line["card_id"] : ""; ?></td>
		<td class="l">����ͨ������ݣ�</td>
		<td class="r"><?php echo $line["swt_id"]; ?></td>
	</tr>
	<tr>
		<td class="l">��ѯ���ݣ�</td>
		<td class="r" colspan="3"><?php echo text_show(rtrim($line["content"])); ?></td>
	</tr>
	<tr>
		<td class="l">�������ͣ�</td>
		<td class="r"><?php echo $line["disease_2"] ? $line["disease_2"] : $disease_id_name[$line["disease_id"]]; ?></td>
		<td class="l">ý����Դ��</td>
		<td class="r"><?php echo $line["media_from"]; ?></td>
	</tr>
	<tr>
		<td class="l">�����˺ţ�</td>
		<td class="r"><?php echo $line["account"]; ?></td>
		<td class="l">ר�Һţ�</td>
		<td class="r"><?php echo $line["zhuanjia_num"]; ?></td>
	</tr>
	<tr>
		<td class="l">ԤԼʱ�䣺</td>
		<td class="r"><?php echo @date("Y-m-d H:i", $line["order_date"]); ?></td>
		<td class="l">���ڵ�����</td>
		<!--<td class="r"><?php echo ($line["suozaidi"] == 1 ? "����" : ($line["suozaidi"] == 2 ? "���" : "")); ?></td>-->
		<td class="r">
		<?php if($line["suozaidi"] == 1){echo "����";}
		elseif($line["suozaidi"] == 2){echo "���";}
		elseif($line["suozaidi"] == 3){echo "����";}
		elseif($line["suozaidi"] == 4){echo "��˳";}
		elseif($line["suozaidi"] == 5){echo "�Ͻ�";}
		elseif($line["suozaidi"] == 6){echo "����";}
		elseif($line["suozaidi"] == 7){echo "����ˮ";}
		elseif($line["suozaidi"] == 8){echo "ǭ��";}
		elseif($line["suozaidi"] == 9){echo "ǭ����";}
		elseif($line["suozaidi"] == 10){echo "ǭ����";}
		elseif($line["suozaidi"] == 11){echo "ͭ��";}
		else{echo "����";}?>
	</tr>
	<tr>
		<td class="l">��Լ״̬��</td>
		<td class="r"><?php echo $status_array[$line["status"]]; ?></td>
		<td class="l">ҽ����</td>
		<td class="r">
<?php
if (in_array($uinfo["part_id"], array(2,3))) {
	echo "<font color='gray'>(����ʾ)</font>";
} else {
	if ($line["xianchang_doctor"] || $line["doctor"]) {
		echo $line["xianchang_doctor"] ? ("�ֳ�ҽ����".$line["xianchang_doctor"]."&nbsp;") : "";
		echo $line["doctor"] ? ("����ҽ����".$line["doctor"]) : "";
	} else {
		echo "<font color='gray'>(δ����)</font>";
	}
}
?>
		</td>
	</tr>
	<tr>
		<td class="l">��ע��</td>
		<td class="r" colspan="3"><?php echo text_show(trim($line["memo"])); ?></td>
	</tr>
	<style type="text/css">
	#wee_f1 * {vertical-align:middle; }
	</style>
	<tr>
		<td class="l">��ӱ�ע��</td>
		<td class="r" colspan="3" style="padding:4px 4px 4px 8px;">
			<form id="wee_f1" method="POST" onsubmit="return wee_check_memo()"><input title="������ͬ��������׷�ӵ�ԭ�б�ע��β��" name="wee_memo" id="wee_memo" value="" class="input" style="width:400px"><input type="submit" class="button" value="ȷ��" style="margin-left:10px"><input type="hidden" name="patient_id" value="<?php echo $id; ?>"></form>
		</td>
	</tr>
	<script type="text/javascript">
	function wee_check_memo() {
		if (byid("wee_memo").value == '') {
			alert("��������Ҫ�ύ�ı�ע���ݡ�");
			return false;
		}
		return true;
	}
	</script>
	<tr>
		<td class="l">�޸ļ�¼��</td>
		<td class="r" colspan="3"><?php echo text_show($line["edit_log"]); ?></td>
	</tr>

<?php if ($line["luyin_file"] != '') { ?>
	<tr>
		<td colspan="4" class="h">�ط�¼��</td>
	</tr>
	<tr>
		<td class="l">�ط�¼����</td>
		<td class="r" colspan="3">
			¼���ļ��� <?php echo $db->query("select name from sys_admin where id=".$line["upload_uid"]." limit 1", 1, "name"); ?> �ϴ��� <?php echo date("Y-m-d H:i", $line["upload_time"]); ?>&nbsp;
			<a href="javascript:;" onclick="play_luyin(this);" style="font-weight:bold;">[����¼��]</a>
			<script type="text/javascript">
			var luyin_url ="<?php echo $line["luyin_file"]; ?>";
			function play_luyin(o) {
				byid("play_area").style.display = "block";
				o = byid("sys_music_player");
				o.filename = luyin_url;
				o.play();
			}
			</script>
			<div id="play_area" style="display:none;">
				<object classid="CLSID:22D6F312-B0F6-11D0-94AB-0080C74C7E95" codeBase="http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=6,4,05,0809" type="application/x-oleobject"  width="300" height="45" id="sys_music_player">
				<param name="autostart" value="1">
				<param name="filename" value="">
				<param name="volume" value="-450">
				<param name="playcount" value="1">
				</object>
			</div>
		</td>
	</tr>
<?php } ?>

	<tr>
		<td colspan="4" class="h">�طü�¼</td>
	</tr>
	<tr>
		<td class="l">�طÿͷ���</td>
		<td class="r" colspan="3"><?php echo $line["huifang_kf"] ? $line["huifang_kf"] : "<font color='gray'>(δָ��)</font>"; ?></td>
	</tr>
	<tr>
		<td class="l">�ط����ݣ�</td>
		<td class="r" colspan="3"><?php echo text_show($line["huifang"]); ?></td>
	</tr>
	<tr>
		<td class="l" valign="top">�������ݣ�</td>
		<td class="r" colspan="3"><?php echo talk_text_show(rtrim($line["talk_content"])); ?></td>
	</tr>

</table>

<div class="fo_line">
	<button onclick="parent.load_src(0)" class="buttonb">�ر�</button>&nbsp;&nbsp;

<?php if ($uinfo["send_sms"] || $debug_mode) { ?>
	<!-- <button onclick="send_sms(<?php echo $id; ?>)" class="buttonb">������</button>&nbsp;&nbsp;
	<script type="text/javascript">
	function send_sms(id) {
		self.location = "patient_view.php?op=send_sms&id="+id;
	}
	</script> -->
<?php } ?>

</div>

</body>
</html>