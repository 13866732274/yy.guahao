<?php
// --------------------------------------------------------
// - ����˵�� : �����б�
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2009-03-30 12:48
// --------------------------------------------------------
require "lib/set_env.php";
$table = "sys_part";

check_power('', $pinfo) or exit("û�д�Ȩ��...");

// �����Ĵ���:
if ($op = $_GET["op"]) {
	if ($op == "delete") {
		$ids = explode(",", $_GET["id"]);
		$del_fail = $del_ok = 0;
		foreach ($ids as $_id) {
			$_id = intval($_id);
			if ($_id > 0) {
				del_data($db, $table, $_id, 1, "ɾ�����š�{name}��") ? $del_ok++ : $del_fail++;
			}
		}
		if ($del_fail > 0) {
			msg_box("ɾ���ɹ� $del_ok �����ϣ�ɾ��ʧ�� $del_fail �����ϡ�", "back", 1);
		} else {
			msg_box("ɾ���ɹ�", "back", 1);
		}
	}
}

// ���嵱ǰҳ��Ҫ�õ��ĵ��ò���:
$aLinkInfo = array();

// ��ȡҳ����ò���:
foreach ($aLinkInfo as $local_var_name => $call_var_name) {
	$$local_var_name = $_GET[$call_var_name];
}

// ���嵥Ԫ���ʽ:
$aOrderType = array(0 => "", 1 => "asc", 2 => "desc");
$aTdFormat = array(
	4 => array("title" => "ID", "width" => "60", "align" => "center"),
	1 => array("title" => "��������", "width" => "", "align" => "left"),
	6 => array("title" => "���ȶ�", "width" => "", "align" => "left"),
	2 => array("title" => "���ʱ��", "width" => "", "align" => "left"),
	3 => array("title" => "����", "width" => "100", "align" => "center"),
);

// ����:
$part_arr = $db->query("select * from $table order by sort desc, id asc", "id");

?>
<html>

<head>
    <title><?php echo $pinfo["title"]; ?></title>
    <meta http-equiv="Content-Type" content="text/html;charset=gb2312">
    <link href="lib/base.css" rel="stylesheet" type="text/css">
    <script src="lib/base.js" language="javascript"></script>
    <style>
    .tr_high_light td {
        background: #FFE1D2;
    }
    </style>
    <script language="javascript">
    window.last_high_obj = '';

    function set_high_light(obj) {
        if (last_high_obj) {
            last_high_obj.parentNode.parentNode.className = "";
        }
        if (obj) {
            obj.parentNode.parentNode.className = "tr_high_light";
            last_high_obj = obj;
        } else {
            last_high_obj = '';
        }
    }

    function add() {
        set_high_light('');
        parent.load_src(1, 'sys_part_edit.php', 600, 350);
    }

    function edit(id, obj) {
        set_high_light(obj);
        parent.load_src(1, 'sys_part_edit.php?id=' + id, 600, 350);
    }
    </script>
</head>

<body>
    <!-- ͷ�� begin -->
    <table class="headers" width="100%">
        <tr>
            <td class="headers_title">
                <nobr class="tips">�����б�</nobr>
            </td>
            <td class="header_center">
                <?php if (check_power("i", $pinfo, $pagepower)) { ?>
                <button onclick="add()" class="button">���</button>
                <?php } ?>
            </td>
            <td class="headers_oprate"><button onclick="self.location.reload()" class="button">ˢ��</button></td>
        </tr>
    </table>
    <!-- ͷ�� end -->


    <div class="space"></div>
    <table width="100%" class="description description_light">
        <tr>
            <td>&nbsp;<b>���ؾ��棺</b>���������޸Ĳ��ţ�����޸Ļ��ң�������<b>ϵͳ����</b>��<b>���ݶ�ʧ</b>���������⣬���ȷʵ��Ҫ�޸ģ�������ѯ������Ա��</td>
        </tr>
    </table>

    <!-- �����б� begin -->
    <div class="space"></div>
    <form name="mainform">
        <table width="100%" align="center" class="list">
            <!-- ��ͷ���� begin -->
            <tr>
                <?php
				// ��ͷ����:
				foreach ($aTdFormat as $tdid => $tdinfo) {
					list($tdalign, $tdwidth, $tdtitle) = make_td_head($tdid, $tdinfo);
				?>
                <td class="head" align="<?php echo $tdalign; ?>" width="<?php echo $tdwidth; ?>"><?php echo $tdtitle; ?>
                </td>
                <?php } ?>
            </tr>
            <!-- ��ͷ���� end -->

            <!-- ��Ҫ�б����� begin -->
            <?php
			if (count($part_arr) > 0) {
				foreach ($part_arr as $id => $line) {
					$op = array();
					if (check_power("e", $pinfo, $pagepower)) {
						$op[] = "<button onclick='edit(" . $id . ", this); return false' class='button_op'><img src='image/b_edit.gif' align='absmiddle' title='�޸�' alt=''></button>";
					}
					$op_button = implode("&nbsp;", $op);

			?>
            <tr>
                <td align="center" class="item"><?php echo $line["id"]; ?></td>
                <td align="left" class="item"><?php echo $line["name"]; ?></td>
                <td align="left" class="item"><?php echo $line["sort"]; ?></td>
                <td align="left" class="item"><?php echo date("Y-m-d H:i", $line["addtime"]); ?></td>
                <td align="center" class="item"><?php echo $op_button; ?></td>
            </tr>
            <?php
				}
			} else {
				?>
            <tr>
                <td colspan="<?php echo count($aTdFormat); ?>" align="center" class="nodata">(��������)</td>
            </tr>
            <?php } ?>
            <!-- ��Ҫ�б����� end -->
        </table>
    </form>
    <!-- �����б� end -->

    <div class="space"></div>
    <center>�� <b><?php echo count($part_arr); ?></b> �����ϡ���(����ɾ�����ţ�����ϵ������Ա����)</center>
    <div class="space"></div>

</body>

</html>