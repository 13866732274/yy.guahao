<?php
// --------------------------------------------------------
// - ����˵�� : ���绰�����Ƿ�����ύ
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2012-02-15
// --------------------------------------------------------
header("Content-Type:text/html;charset=GB2312");
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
require "../lib/set_env.php";
require "../lib/class.fastjson.php";

$table = "patient_" . $hid;

$out           = array();
$out["status"] = "bad";
$out["tips"]   = '';

$tel = trim($_GET["tel"]);
if ($tel != '') {
	// ����绰���룺
	$tel = str_replace("o", "0", $tel);
	$tel = str_replace("O", "0", $tel);
	// ����д����ת��ΪСд:
	$char_arr = explode(" ", "�� �� �� �� �� �� �� �� �� ��");
	foreach ($char_arr as $k => $v) {
		$tel = str_replace($v, $k, $tel);
		}
	//���˲������ֵ��ַ�:
	$shuzi_arr = explode(" ", "0 1 2 3 4 5 6 7 8 9");
	$tel_lens  = strlen($tel);
	$new_tel   = '';
	for ($i = 0; $i < $tel_lens; $i++) {
		if (in_array($tel { $i}, $shuzi_arr)) {
			$new_tel .= $tel { $i};
			}
		}
	$tel = $new_tel;

	$time = time();

	// �绰�����ظ����
	$_hinfo = $db->query("select * from hospital where id=$hid limit 1", 1);
	if ($_hinfo["repeat_open"]) {
		$deny_days = $_hinfo["repeat_deny_time"] > 0 ? $_hinfo["repeat_deny_time"] : $cfgRepeatDenyDays;
		$deny_sec  = $deny_days * 24 * 3600; //����
		// �ظ����:
		if (strlen($tel) >= 7) {
			$count = $db->query("select count(*) as c from $table where tel='$tel' and abs({$time}-addtime)<{$deny_sec}", 1, "c");
			if ($count > 0) {
				$out["tips"] = "�����ύ���绰���롰{$tel}���ظ�{$count}�Ρ�(ϵͳ����" . $deny_days . "���ں��벻�����ظ�)";
				}
			}
		if ($out["tips"] == '') {
			$out["tips"] = "����飬���롰{$tel}����{$deny_days}�������ظ��������ύ��";
			}
		} else {
		$out["tips"] = "��ҽԺ�����δ���ú����ظ���⣬��ʹ�ظ���Ҳ�����ύ�ġ�";
		}
	$out["status"] = 'ok';
	$out["tel"]    = $tel;
	}

echo FastJSON::convert($out);