<?php
// --------------------------------------------------------
// - ����˵�� : �˵����/�޸�
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2008-05-14 => 2011-12-13
// --------------------------------------------------------
require "lib/set_env.php";
$table = "sys_menu";

$id = intval($_REQUEST["id"]);
$mode = $id > 0 ? "edit" : "add";

if ($mode == "edit") {
    check_power("e", $pinfo, $pagepower);
} else {
    check_power("i", $pinfo, $pagepower);
}
if ($_POST) {
    $r = array();
    $r["mid"] = $_POST["menuid"];
    $r["type"] = $_POST["type"];
    $r["title"] = $_POST["title"];
    $r["link"] = $_POST["link"];
    $r["tips"] = $_POST["tips"];
    $r["pagesize"] = $_POST["pagesize"];
    $r["shortcut"] = $_POST["shortcut"];

    $r["isinsert"] = $_POST["isinsert"] ? 1 : 0;
    if ($r["isinsert"]) {
        $r["insertpage"] = $_POST["insertpage"];
    }
    $r["isview"] = $_POST["isview"] ? 1 : 0;
    if ($r["isview"]) {
        $r["viewpage"] = $_POST["viewpage"];
    }
    $r["isedit"] = $_POST["isedit"] ? 1 : 0;
    if ($r["isedit"]) {
        $r["editpage"] = $_POST["editpage"];
    }
    $r["ishide"] = $_POST["ishide"] ? 1 : 0;
    $r["isdelete"] = $_POST["isdelete"] ? 1 : 0;
    $r["ischeck"] = $_POST["ischeck"] ? 1 : 0;
    if ($r["ischeck"]) {
        $r["checkpage"] = $_POST["checkpage"];
    }

    if ($mode == "edit") {
        $r["sort"] = $_POST["sort"];
    } else {
        $r["sort"] = $_POST["sort"] > 0 ? $_POST["sort"] : get_new_sort($r["type"], $r["mid"]);
        $r["addtime"] = time();
    }

    $sqldata = $db->sqljoin($r);

    if ($mode == "edit") {
        $sql = "update $table set $sqldata where id='$id' limit 1";
    } else {
        $sql = "insert into $table set $sqldata";
    }
    /* print_r($sql);
    exit; */
    if ($db->query($sql)) {
        // �������ڵĴ���ʽ:
        if ($mode == "add") {
            echo '<script> parent.update_content(); </script>';
        }
        echo '<script> parent.msg_box("�����ύ�ɹ�", 2); </script>';
        echo '<script> parent.load_src(0); </script>';
    } else {
        echo "�˵��ύʧ�ܣ����Ժ����ԣ�";
    }
    exit;
}

if ($mode == "edit") {
    $title = "�޸Ĳ˵�����";
    $line = $db->query_first("select * from $table where id='$id' limit 1");
} else {
    $title = "�����˵�����";

    // ��ȡ���һ����ӻ�ʹ�õ�mid:
    $tm = $db->query_first("select mid from $table order by addtime desc limit 1");
    $last_used_mid = $tm["mid"];
}

$middata = $db->query("select distinct mid from $table order by mid");
$UsedMIDs = "";
foreach ($middata as $midline) {
    $UsedMIDs .= ($UsedMIDs ? "," : "") . $midline["mid"];
}
$UsedMID = "<span class='intro'>" . ($UsedMIDs ? "���������Ѳ����ã�$UsedMIDs" : "������ָ��") . "</span>";

// ������һ�����õ�mid��
$aUsedMID = explode(",", $UsedMIDs);
$nNotUsedMID = 1;
while (in_array($nNotUsedMID, $aUsedMID)) {
    $nNotUsedMID++;
}
$InputData = "<input name='menuid' size='8' class='input' value='$line[mid]'>";
if ($mode == "add") {
    $InputData .= "��<a href='javascript:void(0);' onclick='document.mainform.menuid.value=$nNotUsedMID;'>[�Զ���д]</a>&nbsp;$UsedMID";
}

// ����ģʽ
$SelectData = "<select name='menuid' class='combo'>\n";
$mmdata = mysqli_query($db->dblink, "select mid,title from $table where mid>0 and type=1 order by sort");

while ($mmline = $mmdata->fetch_array()) {
    $sel = ($mode == "edit") ? ($line["mid"] == $mmline["mid"] ? " selected" : "") : ($mmline["mid"] == $last_used_mid ? " selected" : "");
    $SelectData .= "<option value='$mmline[0]'{$sel}>" . $mmline[1] . "($mmline[0])" . ($sel ? " *" : "") . "</option>\n";
}
$SelectData .= "</select> <span class='intro'>�����ڵ�����Ϊ������</span>\n";

function get_new_sort($level, $mid)
{
    if ($level == 1) {
        return $mid * 100;
    } else {
        global $db, $table;
        $tm = $db->query_first("select sort from $table where mid='$mid' order by sort desc limit 1");
        return $tm["sort"] + 1;
    }
}
?>
<html>

<head>
    <title><?php echo $title; ?></title>
    <meta http-equiv="Content-Type" content="text/html;charset=gb2312">
    <link href="lib/base.css" rel="stylesheet" type="text/css">
    <script src="lib/base.js" language="javascript"></script>
    <script language="javascript">
    function set_MID_show(oSel) {
        var theValue = oSel.value;
        var theForm = document.hideform;
        var HtmlData = theValue == "0" ? theForm.menuid_select.value : theForm.menuid_input.value;
        var menuidTips = theValue == "0" ? "����˵���" : "�˵����ţ�";
        document.getElementById("menuid_area").innerHTML = HtmlData;
        document.getElementById("menuid_tips").innerHTML = menuidTips;
        document.getElementById("menu_detail").style.display = (theValue == "0" ? "block" : "none");
    }

    function init() {
        set_MID_show(document.mainform.type);

        <?php if ($line["isinsert"]) { ?>
        document.getElementById("isinsert").checked = true;
        document.getElementById("insertarea").style.display = "block";
        <?php } ?>
        <?php if ($line["isview"]) { ?>
        document.getElementById("isview").checked = true;
        document.getElementById("viewarea").style.display = "block";
        <?php } ?>
        <?php if ($line["isedit"]) { ?>
        document.getElementById("isedit").checked = true;
        document.getElementById("editarea").style.display = "block";
        <?php } ?>
        <?php if ($line["ishide"]) { ?>
        document.getElementById("ishide").checked = true;
        <?php } ?>
        <?php if ($line["isdelete"]) { ?>
        document.getElementById("isdelete").checked = true;
        <?php } ?>
        <?php if ($line["ischeck"]) { ?>
        document.getElementById("ischeck").checked = true;
        document.getElementById("checkarea").style.display = "block";
        <?php } ?>
    }

    function check_data(f) {
        if (f.menuid.value == "") {
            msg_box("��ָ���˵����ţ�");
            f.menuid.focus();
            return false;
        }
        if (f.title.value == "") {
            msg_box("������˵����ƣ�");
            f.title.focus();
            return false;
        }
        return true;
    }
    </script>
</head>

<body onload="init()">

    <form method='POST' name="mainform" onsubmit="return check_data(this)">
        <table width="100%" class="edit" style="margin-top:5px">
            <tr>
                <td colspan="2" class="head">�˵���������</td>
            </tr>
            <tr>
                <td class="left">
                    <font color="red">*</font> �˵����ͣ�
                </td>
                <td class="right"><select name="type" class="combo" onchange="set_MID_show(this)">
                        <option value="1" <?php echo (!$id || $line["type"] ? " selected" : ""); ?>>
                            ����˵�<?php echo ($line["type"] ? " *" : ""); ?></option>
                        <option value="0"
                            <?php echo (strlen($line["type"]) && $line["type"] == 0 ? " selected" : ""); ?>>
                            �Ӳ˵�<?php echo (strlen($line["type"]) && $line["type"] == 0 ? " *" : ""); ?></option>
                    </select> <span class="intro">����˵���ʾ�ڡ����˵���λ�ã��Ӳ˵���ʾ�����¼������</span>
                </td>
            </tr>
            <tr>
                <td class="left">
                    <font color="red">*</font> <span id="menuid_tips"></span>
                </td>
                <td class="right"><span id="menuid_area"></span>
                </td>
            </tr>
            <tr>
                <td class="left">
                    <font color="red">*</font> �˵����ƣ�
                </td>
                <td class="right"><input name="title" size="20" maxlength="40" class="input"
                        value="<?php echo $line["title"]; ?>"> <span class="intro">�˵���ʾ���ƣ�����</span></td>
            </tr>
            <tr>
                <td class="left">������ҳ�棺</td>
                <td class="right"><input name="link" size="40" maxlength="100" class="input"
                        value="<?php echo $line["link"]; ?>"> <span class="intro">���˵������ֱ������ (����˵��ɲ���)</span></td>
            </tr>
            <tr>
                <td class="left">����˵����</td>
                <td class="right"><input name="tips" size="40" maxlength="100" class="input"
                        value="<?php echo $line["tips"]; ?>"> <span class="intro">������д��ϸ˵�����Ǳ�����</span></td>
            </tr>
            <tr>
                <td class="left">����ֵ��</td>
                <td class="right"><input name="sort" size="8" maxlength="10" class="input"
                        value="<?php echo $line["sort"]; ?>"> <span class="intro">����ֵ�Ĵ�С�����˵������д�������ģʽ�¿�������ϵͳ�Զ�����</span>
                </td>
            </tr>
        </table>

        <div id="menu_detail" style="display:none">
            <table width="100%" class="edit" style="margin-top:10px">
                <tr>
                    <td colspan="2" class="head">�Ӳ˵���ϸ����</td>
                </tr>
                <tr>
                    <td class="left">ÿҳ��ʾ������</td>
                    <td class="right"><input name="pagesize" size="8" class="input"
                            value="<?php echo $line["pagesize"]; ?>"> <span class="intro">�趨�б�ҳ��ÿҳ��ʾ��¼������</span></td>
                </tr>
                <tr>
                    <td class="left">Ĭ�Ͽ�ݷ�ʽ��</td>
                    <td class="right"><select name="shortcut" class="combo">
                            <option value="1" <?php echo ($line["shortcut"] == 1 ? " selected" : ""); ?>>��ΪĬ�Ͽ�ݷ�ʽ
                            </option>
                            <option value="0" <?php echo ($line["shortcut"] == 0 ? " selected" : ""); ?>>����ΪĬ�Ͽ�ݷ�ʽ
                            </option>
                        </select> <span class="intro">������Աδ�趨��ר����ݷ�ʽʱ������ΪĬ�Ͽ�ݷ�ʽ������Щҳ�����ʾ</span></td>
                </tr>
                <tr>
                    <td class="left">������¼��</td>
                    <td class="right"><input type="checkbox" name="isinsert" id="isinsert" class="check"
                            onclick="document.getElementById('insertarea').style.display=(this.checked ? 'block' : 'none')"><label
                            for="isinsert">����������</label><span id="insertarea" style="display:none">&nbsp;����ҳ�����ӣ�<input
                                name='insertpage' size="20" maxlength="100" class="input"
                                value="<?php echo $line["insertpage"]; ?>"></span></td>
                </tr>
                <tr>
                    <td class="left">�鿴���ϣ�</td>
                    <td class="right"><input type="checkbox" name="isview" id="isview" class="check"
                            onclick="document.getElementById('viewarea').style.display=(this.checked ? 'block' : 'none')"><label
                            for="isview">�в鿴����</label><span id="viewarea" style="display:none">&nbsp;�鿴ҳ�����ӣ�<input
                                name='viewpage' size="20" maxlength="100" class="input"
                                value="<?php echo $line["viewpage"]; ?>"> <span
                                class="intro">ϵͳ���Զ����ϡ�?id=xx���ĵ��ò���</span></span></td>
                </tr>
                <tr>
                    <td class="left">�޸����ϣ�</td>
                    <td class="right"><input type="checkbox" name="isedit" id="isedit" class="check"
                            onclick="document.getElementById('editarea').style.display=(this.checked ? 'block' : 'none')"><label
                            for="isedit">���޸Ĺ���</label><span id="editarea" style="display:none">&nbsp;�޸�ҳ�����ӣ�<input
                                name='editpage' size="20" maxlength="100" class="input"
                                value="<?php echo $line["editpage"]; ?>">&nbsp;<a href="javascript:;"
                                onclick="document.mainform.editpage.value=document.mainform.insertpage.value;">[ͬ����ҳ������]</a>
                            <span class="intro">ϵͳ���Զ����ϡ�?id=xx���ĵ��ò���</span></span></td>
                </tr>
                <tr>
                    <td class="left">�ر����ϣ�</td>
                    <td class="right"><input type="checkbox" name="ishide" id="ishide" class="check"><label
                            for="ishide">�йرչ���</label> <span class="intro">�����ϵ�״̬����Ϊ��ʾ��ر�״̬</span></td>
                </tr>
                <tr>
                    <td class="left">ɾ�����ϣ�</td>
                    <td class="right"><input type="checkbox" name="isdelete" id="isdelete" class="check"><label
                            for="isdelete">��ɾ������</label> <span class="intro">�����ϴӱ���ɾ��</span></td>
                </tr>
                <tr>
                    <td class="left">������ϣ�</td>
                    <td class="right"><input type="checkbox" name="ischeck" id="ischeck" class="check"
                            onclick="document.getElementById('checkarea').style.display=(this.checked ? 'block' : 'none')"><label
                            for="ischeck">����˹���</label><span id="checkarea" style="display:none">&nbsp;���ҳ�����ӣ�<input
                                name='checkpage' size="20" maxlength="100" class="input"
                                value="<?php echo $line["checkpage"]; ?>"> <span class="intro">Ϊ����ʹ��������˹���</span></span>
                    </td>
                </tr>
            </table>
        </div>
        <div class="button_line"><input type="submit" value="�ύ����" class="submit"></div>
        <input type="hidden" name="id" value="<?php echo $id; ?>">
    </form>
    <form name="hideform">
        <input type="hidden" name="menuid_select" value="<?php echo $SelectData; ?>">
        <input type="hidden" name="menuid_input" value="<?php echo $InputData; ?>">
    </form>
</body>

</html>