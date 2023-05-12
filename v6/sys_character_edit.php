<?php
// --------------------------------------------------------
// - ����˵�� : ��ɫ�������޸�
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2008-05-15 02:25
// --------------------------------------------------------
$editmode = ($id = intval($_REQUEST["id"])) > 0 ? 1 : $id = 0;
require "lib/set_env.php";
$table = "sys_character";

if ($editmode) {
	check_power("e", $pinfo, $pagepower) or exit("�Բ�����û���޸�Ȩ��...");
} else {
	check_power("i", $pinfo, $pagepower) or exit("�Բ�����û������Ȩ��...");
}

if ($_POST) {
	$record = array();
	$record["name"] = $_POST["ch_name"];
	$record["menu"] = get_post_menu();
	$record["sort"] = intval($_POST["sort"]);

	if (!$editmode) {
		$record["addtime"] = time();
		$record["author"] = $username;
	}

	$sqldata = $db->sqljoin($record);
	if ($editmode) {
		$sql = "update $table set $sqldata where id='$id' limit 1";
	} else {
		$sql = "insert into $table set $sqldata";
	}

	if ($db->query($sql)) {
		// �������ڵĴ���ʽ:
		if (!$editmode) {
			echo '<script> parent.update_content(); </script>';
		}
		echo '<script> parent.msg_box("�����ύ�ɹ�", 2); </script>';
		echo '<script> parent.load_src(0); </script>';
	} else {
		echo "�����ύʧ�ܣ�ϵͳ��æ�����Ժ����ԡ�";
	}
	exit;
}

if ($editmode) {
	$cline = $db->query_first("select * from $table where id='$id' limit 1");
}
$title = $editmode ? "�޸�Ȩ�޶���" : "�����µ�Ȩ��";

function get_post_menu()
{
	$Menu = $Item = "";
	foreach ($_POST as $name => $value) {
		if (strpos($name, "_") > 0) {
			list($name, $other) = explode("_", $name, 2);
			if ($name == "menu") {
				$Menu .= $Item ? ":{$Item}" : "";
				$Item = "";
				$Menu .= ($Menu ? ";" : "") . $other;
			}
			if ($name == "item" && (strpos($other, "_") === false)) {
				$Item .= ($Item ? "," : "") . $other;
				$ItemPower = "";
				$ItemPower .= $_POST[$name . "_" . $other . "_insert"] ? "i" : "";
				$ItemPower .= $_POST[$name . "_" . $other . "_view"] ? "v" : "";
				$ItemPower .= $_POST[$name . "_" . $other . "_edit"] ? "e" : "";
				$ItemPower .= $_POST[$name . "_" . $other . "_hide"] ? "h" : "";
				$ItemPower .= $_POST[$name . "_" . $other . "_delete"] ? "d" : "";
				$ItemPower .= $_POST[$name . "_" . $other . "_check"] ? "c" : "";
				$Item .= ($ItemPower ? "!" : "") . $ItemPower;
			}
		}
	}
	$Menu .= $Item ? ":{$Item}" : "";

	return $Menu;
}
?>
<html>

<head>
    <title><?php echo $title; ?></title>
    <meta http-equiv="Content-Type" content="text/html;charset=gb2312">
    <link href="lib/base.css" rel="stylesheet" type="text/css">
    <script src="lib/base.js" language="javascript"></script>
    <script language="javascript">
    function Check() {
        var oForm = document.mainform;
        if (oForm.ch_name.value == "") {
            alert("�����롰Ȩ�����ơ���");
            oForm.ch_name.focus();
            return false;
        }
        return true;
    }
    </script>
</head>

<body>
    <div class="description">
        <div class="d_title">��ʾ��</div>
        <div class="d_item">����ؽ�������Ȩ�ޣ����ò����������<b>����й¶</b>�����غ��������ֻ�������ٵ�Ȩ�ޡ�</div>
    </div>

    <div class="space"></div>
    <form name="mainform" action="" method="POST" onsubmit="return Check()">
        <table width="100%" class="edit">
            <tr>
                <td colspan="2" class="head">Ȩ������</td>
            </tr>
            <tr>
                <td class="left">Ȩ�����ƣ�</td>
                <td class="right"><input name="ch_name" value="<?php echo $cline["name"]; ?>" class="input" size="30"
                        style="width:200px"> <span class="intro">Ȩ�����Ʊ�����д</span></td>
            </tr>
            <tr>
                <td class="left" valign="top" style="padding-top:4px">Ȩ����ϸ��</td>
                <td class="right"><?php echo show_power_table($usermenu, $cline["menu"]); ?></td>
            </tr>
            <tr>
                <td class="left">���ȶȣ�</td>
                <td class="right"><input name="sort" value="<?php echo $cline["sort"]; ?>" class="input" size="10"
                        style="width:100px"> <span class="intro">Ĭ��0 Խ��Խ���� ��ֵ�����</span></td>
            </tr>
        </table>
        <input type="hidden" name="id" value="<?php echo intval($id); ?>">
        <input type="hidden" name="linkinfo" value="<?php echo $linkinfo; ?>">

        <div class="button_line"><input type="submit" class="submit" value="�ύ����"></div>
    </form>


    <!-- ��ҳ��������ѡ�е�ѡ��Ӻ�ɫ -->
    <script type="text/javascript">
    var os = document.getElementsByTagName("INPUT");
    for (var i = 0; i < os.length; i++) {
        var o = os[i];
        if (o.type == "checkbox") {
            if (o.checked) {
                o.nextSibling.style.color = "red";
            }
        }
    }
    </script>

</body>

</html>