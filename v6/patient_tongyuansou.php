<?php
// --------------------------------------------------------
// - ����˵�� : ͬԺ��
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2014-9-25
// --------------------------------------------------------
require "lib/set_env.php";

if ($_GET["hid"] > 0) {
	$hid = $user_hospital_id = $_SESSION[$cfgSessionName]["hospital_id"] = intval($_GET["hid"]);
}

$hinfo = $db->query("select * from hospital where id=$hid limit 1", 1);

// ��Ҫ��������ȷ��ҽԺ���������޷���ѯͬҽԺ����������:
$real_hname = $hinfo["sname"];
if ($real_hname == '') {
	exit_html("�Բ��𣬵�ǰҽԺδ������Ч��ҽԺ��������ʹ�ô˹��ܣ�");
}

// ��ѯ�뵱ǰҽԺ��ͬ���������ң�
$area = $hinfo["area"];
$s_list = $db->query("select * from hospital where ishide=0 and area='$area' and sname='$real_hname' order by name asc", "id");
if (count($s_list) <= 1) {
	exit_html("�Բ��𣬵�ǰҽԺû���������ң���ֱ������������ʹ�á�ͬԺ�ѡ����ܣ�");
}

// ���Ȩ�ޣ�
if ($username != 'admin' && !$debug_mode) {
	$my_hlist = @explode(",", $uinfo["hospitals"]);
	foreach ($s_list as $_hid => $_hinfo) {
		if (!in_array($_hid, $my_hlist)) {
			unset($s_list[$_hid]);
		}
	}
	if (count($s_list) <= 1) {
		exit_html("�Բ������Ŀ�����Ȩ�������޷�ʹ�á�ͬԺ�ѡ����ܣ�");
	}
}


// �����ؼ��ʣ�
$key = $_GET["key"];
if ($_GET["code"] == "utf8") {
	$key = iconv("UTF-8", "GBK", $key);
}
$key = trim(wee_safe_key($key));


// ����������Ȩ�޵Ŀ��ң�
if (strlen($key) > 3) {
	$h_res = array();
	foreach ($s_list as $_hid => $_hinfo) {
		$table = "patient_".$_hid;
		$h_res[$_hid] = $db->query("select * from $table where concat(name,'_',tel,'_',yuyue_num,'_',content,'_',memo) like '%{$key}%' order by id desc limit 10", "id");
	}

	// �ֵ�:
	$part_id_name = $db->query("select id,name from sys_part", "id", "name");
	$disease_id_name = $db->query("select id,name from disease", "id", "name");


	// ���в�ѯ��ҽԺ��
	$h_str = array();
	foreach ($s_list as $_hid => $_hinfo) {
		$h_str[] = $_hinfo["name"];
	}
	$h_str = implode("��", $h_str);

}


// ��־ @ 2012-07-23
//$log_str = "[".date("Y-m-d H:i")."] [".$realname."] �� [".$h_str."] ������ [".$key."]\r\n";
//@file_put_contents(dirname(__FILE__)."/tongyuansou.log", $log_str, FILE_APPEND);


function _tongyuansou_text_show($s, $len = 20) {
	$s = strip_tags($s);
	$s = str_replace("\r", "", $s);
	$s = str_replace("\n", " ", $s);
	$s = str_replace("\t", "  ", $s);
	$s = cut($s, $len, "��");
	$s = str_replace(" ", "&nbsp;", $s);
	return $s;
}

?>
<html>
<head>
<title>ͬԺ�ѣ�<?php echo $key; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style type="text/css">
.cur_hname {margin-top:10px; font-family:"΢���ź�"; color:#ff00ff; }
.search_res {border:2px solid #ade0ba; margin-top:2px; }
.search_res td {border:0px solid #c5ddef; border-left:0; border-right:0; padding:4px auto 3px auto; }
.search_res .head {padding:3px 3px 2px 3px; font-family:"΢���ź�"; color:#99afd0; text-align:center; background:#efefef; border:1px solid #cecece; border-top:0; border-bottom:0; }
.search_res .item {padding:4px 3px 2px 3px; text-align:center; border:1px solid #cecece; border-left:0; border-right:0; }
.no_data {line-height:40px; text-align:center; color:gray; border-top:1px solid #cecece !important; }
.yahei, .yahei * {font-family:"΢���ź�"; }
.noyahei {font-family:"����" !important; }
</style>
<script type="text/javascript">
global_param = '';

function goto_id(hid, param) {
	global_param = param;
	load_js("change_hospital.php?op=tys_change&hid="+hid, "change_hospital");
}

function goto_id_do() {
	var base_url = "patient.php";
	var url = base_url+"?"+global_param;
	parent.byid("sys_frame").src = url;
	parent.load_src(0);
}
</script>
</head>

<body>

<form name="mainform" action="?" method="GET" class="yahei">
	<b>�������������ֻ��Ž���������</b>
	<input name="key" class="input noyahei" style="width:150px" value="<?php echo $key; ?>">
	<input type="submit" class="button" value="����">
	<input type="hidden" name="op" value="tongyuansou" />
</form>

<?php if ($h_res) { ?>
<div class="space"></div>

<div class="res">
	<div class="res_num yahei">�� <b style="color:red;"><?php echo $h_str; ?></b> ��������ÿ������෵�� <b>10</b> �����µļ�¼��������£�</div>
<?php
	foreach ($s_list as $_hid => $_hinfo) {
		$cur_hname = $_hinfo["name"];
		$list = $h_res[$_hid];
?>
	<div class="cur_hname"><?php echo $cur_hname; ?>��</div>
	<table width="100%" class="search_res" cellpadding="0" cellspacing="0">
		<tr>
			<td class="head">����</td>
			<td class="head">�Ա�</td>
			<td class="head">����</td>
			<td class="head">����</td>
			<td class="head">�绰</td>
			<td class="head">ý����Դ</td>
			<td class="head">��ѯ����</td>
			<td class="head">��ע</td>
			<td class="head">ԤԼʱ��</td>
			<td class="head">״̬</td>
			<td class="head">�����</td>
			<td class="head">���ʱ��</td>
			<td class="head">����</td>
		</tr>

<?php
		if (count($list) > 0) {
			foreach ($list as $id => $li) {
				// ��������д���ŵĴ���취 @ 2012-07-10
				if (strlen($li["name"]) > 6) {
					if (substr_count($li["name"], "��") > 0) {
						$li["name"] = str_replace("��", "<br>��", $li["name"]);
					}
					if (substr_count($li["name"], "(") > 0) {
						$li["name"] = str_replace("(", "<br>(", $li["name"]);
					}
				}

?>

		<tr>
			<td class="item"><b style="color:#9f0000"><?php echo $li["name"]; ?></b></td>
			<td class="item"><?php echo $li["sex"]; ?></td>
			<td class="item"><?php echo $li["age"] > 0 ? $li["age"] : ""; ?></td>
			<td class="item"><?php echo _tongyuansou_text_show($disease_id_name[$li["disease_id"]], 12); ?></td>
			<td class="item"><?php echo tel_filter($li); ?></td>
			<td class="item"><?php echo $li["media_from"]; ?></td>
			<td class="item"><?php echo _tongyuansou_text_show($li["content"]); ?></td>
			<td class="item"><?php echo _tongyuansou_text_show($li["memo"]); ?></td>
			<td class="item"><?php echo date("Y-m-d", $li["order_date"]); ?></td>
			<td class="item"><?php echo $li["status"] == 1 ? '<font color="red">�ѵ�</font>' : 'δ��'; ?></td>
			<td class="item"><?php echo $li["author"]; ?></td>
			<td class="item"><?php echo date("Y-m-d", $li["addtime"]); ?></td>
			<td class="item"><a href="#" onclick="goto_id(<?php echo $_hid; ?>, 'callid=<?php echo $li["id"]; ?>&crc=<?php echo $li["addtime"]; ?>&searchword=<?php echo urlencode($key); ?>'); return false;" title="��������ҽԺ�鿴����">����</a></td>
		</tr>

<?php
			}
		} else {
?>
		<tr>
			<td class="no_data" colspan="13">(δ�ѵ�����)</td>
		</tr>
<?php
		}
?>
	</table>
<?php
	}
?>


</div>

<?php } ?>

</body>
</html>