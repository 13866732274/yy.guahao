<?php
// --------------------------------------------------------
// - 功能说明 : 菜单添加/修改
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2008-05-14 => 2011-12-13
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
        // 弹出窗口的处理方式:
        if ($mode == "add") {
            echo '<script> parent.update_content(); </script>';
        }
        echo '<script> parent.msg_box("资料提交成功", 2); </script>';
        echo '<script> parent.load_src(0); </script>';
    } else {
        echo "菜单提交失败，请稍后再试！";
    }
    exit;
}

if ($mode == "edit") {
    $title = "修改菜单资料";
    $line = $db->query_first("select * from $table where id='$id' limit 1");
} else {
    $title = "新增菜单资料";

    // 读取最后一次添加或使用的mid:
    $tm = $db->query_first("select mid from $table order by addtime desc limit 1");
    $last_used_mid = $tm["mid"];
}

$middata = $db->query("select distinct mid from $table order by mid");
$UsedMIDs = "";
foreach ($middata as $midline) {
    $UsedMIDs .= ($UsedMIDs ? "," : "") . $midline["mid"];
}
$UsedMID = "<span class='intro'>" . ($UsedMIDs ? "下列组编号已不可用：$UsedMIDs" : "可任意指定") . "</span>";

// 查找下一个可用的mid：
$aUsedMID = explode(",", $UsedMIDs);
$nNotUsedMID = 1;
while (in_array($nNotUsedMID, $aUsedMID)) {
    $nNotUsedMID++;
}
$InputData = "<input name='menuid' size='8' class='input' value='$line[mid]'>";
if ($mode == "add") {
    $InputData .= "　<a href='javascript:void(0);' onclick='document.mainform.menuid.value=$nNotUsedMID;'>[自动填写]</a>&nbsp;$UsedMID";
}

// 新增模式
$SelectData = "<select name='menuid' class='combo'>\n";
$mmdata = mysqli_query($db->dblink, "select mid,title from $table where mid>0 and type=1 order by sort");

while ($mmline = $mmdata->fetch_array()) {
    $sel = ($mode == "edit") ? ($line["mid"] == $mmline["mid"] ? " selected" : "") : ($mmline["mid"] == $last_used_mid ? " selected" : "");
    $SelectData .= "<option value='$mmline[0]'{$sel}>" . $mmline[1] . "($mmline[0])" . ($sel ? " *" : "") . "</option>\n";
}
$SelectData .= "</select> <span class='intro'>括号内的数字为其组编号</span>\n";

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
        var menuidTips = theValue == "0" ? "顶层菜单：" : "菜单组编号：";
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
            msg_box("请指定菜单组编号！");
            f.menuid.focus();
            return false;
        }
        if (f.title.value == "") {
            msg_box("请输入菜单名称！");
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
                <td colspan="2" class="head">菜单基本资料</td>
            </tr>
            <tr>
                <td class="left">
                    <font color="red">*</font> 菜单类型：
                </td>
                <td class="right"><select name="type" class="combo" onchange="set_MID_show(this)">
                        <option value="1" <?php echo (!$id || $line["type"] ? " selected" : ""); ?>>
                            顶层菜单<?php echo ($line["type"] ? " *" : ""); ?></option>
                        <option value="0"
                            <?php echo (strlen($line["type"]) && $line["type"] == 0 ? " selected" : ""); ?>>
                            子菜单<?php echo (strlen($line["type"]) && $line["type"] == 0 ? " *" : ""); ?></option>
                    </select> <span class="intro">顶层菜单显示于“主菜单”位置，子菜单显示于其下及左侧栏</span>
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
                    <font color="red">*</font> 菜单名称：
                </td>
                <td class="right"><input name="title" size="20" maxlength="40" class="input"
                        value="<?php echo $line["title"]; ?>"> <span class="intro">菜单显示名称，必填</span></td>
            </tr>
            <tr>
                <td class="left">主管理页面：</td>
                <td class="right"><input name="link" size="40" maxlength="100" class="input"
                        value="<?php echo $line["link"]; ?>"> <span class="intro">即菜单标题的直接链接 (顶层菜单可不填)</span></td>
            </tr>
            <tr>
                <td class="left">功能说明：</td>
                <td class="right"><input name="tips" size="40" maxlength="100" class="input"
                        value="<?php echo $line["tips"]; ?>"> <span class="intro">建议填写详细说明，非必填项</span></td>
            </tr>
            <tr>
                <td class="left">排序值：</td>
                <td class="right"><input name="sort" size="8" maxlength="10" class="input"
                        value="<?php echo $line["sort"]; ?>"> <span class="intro">排序值的大小决定菜单的排列次序，新增模式下可留空由系统自动计算</span>
                </td>
            </tr>
        </table>

        <div id="menu_detail" style="display:none">
            <table width="100%" class="edit" style="margin-top:10px">
                <tr>
                    <td colspan="2" class="head">子菜单详细定义</td>
                </tr>
                <tr>
                    <td class="left">每页显示条数：</td>
                    <td class="right"><input name="pagesize" size="8" class="input"
                            value="<?php echo $line["pagesize"]; ?>"> <span class="intro">设定列表页面每页显示记录的数量</span></td>
                </tr>
                <tr>
                    <td class="left">默认快捷方式：</td>
                    <td class="right"><select name="shortcut" class="combo">
                            <option value="1" <?php echo ($line["shortcut"] == 1 ? " selected" : ""); ?>>作为默认快捷方式
                            </option>
                            <option value="0" <?php echo ($line["shortcut"] == 0 ? " selected" : ""); ?>>不作为默认快捷方式
                            </option>
                        </select> <span class="intro">当管理员未设定其专属快捷方式时，“作为默认快捷方式”的这些页面会显示</span></td>
                </tr>
                <tr>
                    <td class="left">新增记录：</td>
                    <td class="right"><input type="checkbox" name="isinsert" id="isinsert" class="check"
                            onclick="document.getElementById('insertarea').style.display=(this.checked ? 'block' : 'none')"><label
                            for="isinsert">有新增功能</label><span id="insertarea" style="display:none">&nbsp;新增页面链接：<input
                                name='insertpage' size="20" maxlength="100" class="input"
                                value="<?php echo $line["insertpage"]; ?>"></span></td>
                </tr>
                <tr>
                    <td class="left">查看资料：</td>
                    <td class="right"><input type="checkbox" name="isview" id="isview" class="check"
                            onclick="document.getElementById('viewarea').style.display=(this.checked ? 'block' : 'none')"><label
                            for="isview">有查看功能</label><span id="viewarea" style="display:none">&nbsp;查看页面链接：<input
                                name='viewpage' size="20" maxlength="100" class="input"
                                value="<?php echo $line["viewpage"]; ?>"> <span
                                class="intro">系统会自动加上“?id=xx”的调用参数</span></span></td>
                </tr>
                <tr>
                    <td class="left">修改资料：</td>
                    <td class="right"><input type="checkbox" name="isedit" id="isedit" class="check"
                            onclick="document.getElementById('editarea').style.display=(this.checked ? 'block' : 'none')"><label
                            for="isedit">有修改功能</label><span id="editarea" style="display:none">&nbsp;修改页面链接：<input
                                name='editpage' size="20" maxlength="100" class="input"
                                value="<?php echo $line["editpage"]; ?>">&nbsp;<a href="javascript:;"
                                onclick="document.mainform.editpage.value=document.mainform.insertpage.value;">[同新增页面链接]</a>
                            <span class="intro">系统会自动加上“?id=xx”的调用参数</span></span></td>
                </tr>
                <tr>
                    <td class="left">关闭资料：</td>
                    <td class="right"><input type="checkbox" name="ishide" id="ishide" class="check"><label
                            for="ishide">有关闭功能</label> <span class="intro">将资料的状态设置为显示或关闭状态</span></td>
                </tr>
                <tr>
                    <td class="left">删除资料：</td>
                    <td class="right"><input type="checkbox" name="isdelete" id="isdelete" class="check"><label
                            for="isdelete">有删除功能</label> <span class="intro">将资料从表中删除</span></td>
                </tr>
                <tr>
                    <td class="left">审核资料：</td>
                    <td class="right"><input type="checkbox" name="ischeck" id="ischeck" class="check"
                            onclick="document.getElementById('checkarea').style.display=(this.checked ? 'block' : 'none')"><label
                            for="ischeck">有审核功能</label><span id="checkarea" style="display:none">&nbsp;审核页面链接：<input
                                name='checkpage' size="20" maxlength="100" class="input"
                                value="<?php echo $line["checkpage"]; ?>"> <span class="intro">为空则使用内置审核功能</span></span>
                    </td>
                </tr>
            </table>
        </div>
        <div class="button_line"><input type="submit" value="提交资料" class="submit"></div>
        <input type="hidden" name="id" value="<?php echo $id; ?>">
    </form>
    <form name="hideform">
        <input type="hidden" name="menuid_select" value="<?php echo $SelectData; ?>">
        <input type="hidden" name="menuid_input" value="<?php echo $InputData; ?>">
    </form>
</body>

</html>