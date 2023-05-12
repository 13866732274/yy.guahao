<?php
/*
// ����: ���� (weelia@126.com)
*/
if (!defined("BRDD_MAIN") || BRDD_MAIN != 1) exit("~");

// ��ҳ���躯�� -
// �������ӵĿ�ݺ���
function aa($arr)
{
    $a = empty($arr["data"]) ? "0" : $arr["data"];
    if ($arr["link"]) {
        $a = '<b class="fa"><a href="' . $arr["link"] . '" class="fb">' . $a . '</a></b>';
    } else {
        $a = '<b class="fa">' . $a . '</b>';
    }
    return " " . $a;
}

function ab($arr)
{
    $a = empty($arr["data"]) ? "0" : $arr["data"];
    $a = ' <b class="fc">' . $a . '</b>';
    return $a;
}

function z($condition_id, $day, $type)
{
    global $time_arr, $module_data_arr;
    $arr = $module_data_arr["ID_" . $condition_id];
    $time_type = ($type == "ԤԼ" ? "addtime" : "order_date");
    $t = $time_arr[$day];
    $tb = $t[0];
    $te = $t[1];
    $link = "patient.php?begin_time={$tb}&end_time={$te}&time_type={$time_type}&condition={$condition_id}";
    if ($type == "ʵ��") $link .= "&come=1";
    if ($type == "δ��") $link .= "&come=0";
    if ($type == "δ��") {
        $data = $arr["Ԥ��"][$day] - $arr["ʵ��"][$day];
    } else {
        $data = $arr[$type][$day];
    }
    return aa(array("data" => $data, "link" => $link));
}


function w($arr, $day, $type, $param)
{
    global $time_arr;
    $time_type = ($type == "ԤԼ" ? "addtime" : "order_date");
    $t = $time_arr[$day];
    $tb = $t[0];
    $te = $t[1];
    $link = "patient.php?begin_time={$tb}&end_time={$te}&time_type={$time_type}&{$param}";
    if ($type == "ʵ��") $link .= "&come=1";
    if ($type == "δ��") $link .= "&come=0";
    if ($type == "δ��") {
        $data = $arr["Ԥ��"][$day] - $arr["ʵ��"][$day];
    } else {
        $data = $arr[$type][$day];
    }
    return aa(array("data" => $data, "link" => $link));
}

// QQ����ͳ�� @ 2013-03-08
function load_data_youhua()
{
    global $db, $hid;
    global $today_tb, $today_te, $yesterday_tb, $yesterday_te, $month_tb, $month_te, $tb_tb, $tb_te, $lastmonth_tb, $lastmonth_te;

    $_today_tb = date("Ymd", $today_tb);
    $_today_te = date("Ymd", $today_te);
    $_yesterday_tb = date("Ymd", $yesterday_tb);
    $_yesterday_te = date("Ymd", $yesterday_te);
    $_month_tb = date("Ymd", $month_tb);
    $_month_te = date("Ymd", $month_te);
    $_tb_tb = date("Ymd", $tb_tb);
    $_tb_te = date("Ymd", $tb_te);
    $_lastmonth_tb = date("Ymd", $lastmonth_tb);
    $_lastmonth_te = date("Ymd", $lastmonth_te);

    // ��ȡ����:
    $arr = $db->query("select date,yuyue,daoyuan from youhua_data where hid=$hid and date>=$_lastmonth_tb and date<=$_today_te");

    $d = array();
    foreach ($arr as $v) {
        $t = $v["date"];
        if ($t >= $_today_tb && $t <= $_today_te) {
            $d["11"]["data"] = intval($d["11"]["data"]) + intval($v["yuyue"]);
            $d["12"]["data"] = intval($d["12"]["data"]) + intval($v["daoyuan"]);
        }
        if ($t >= $_yesterday_tb && $t <= $_yesterday_te) {
            $d["21"]["data"] = intval($d["21"]["data"]) + intval($v["yuyue"]);
            $d["22"]["data"] = intval($d["22"]["data"]) + intval($v["daoyuan"]);
        }
        if ($t >= $_month_tb && $t <= $_month_te) {
            $d["31"]["data"] = intval($d["31"]["data"]) + intval($v["yuyue"]);
            $d["32"]["data"] = intval($d["32"]["data"]) + intval($v["daoyuan"]);
        }
        if ($t >= $_tb_tb && $t <= $_tb_te) {
            $d["41"]["data"] = intval($d["41"]["data"]) + intval($v["yuyue"]);
            $d["42"]["data"] = intval($d["42"]["data"]) + intval($v["daoyuan"]);
        }
        if ($t >= $_lastmonth_tb && $t <= $_lastmonth_te) {
            $d["51"]["data"] = intval($d["51"]["data"]) + intval($v["yuyue"]);
            $d["52"]["data"] = intval($d["52"]["data"]) + intval($v["daoyuan"]);
        }
    }

    return $d;
}

?>
<html>

<head>
    <title>ϵͳ��ҳ</title>
    <meta http-equiv="Content-Type" content="text/html;charset=gb2312">
    <meta name="wap-font-scale" content="no">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link href="lib/base.css" rel="stylesheet" type="text/css">
    <script src="lib/base.js" language="javascript"></script>
    <script src="lib/datejs/picker.js" language="javascript"></script>
    <style type="text/css">
        body {
            overflow-x: hidden;
            overflow-y: auto;
        }

        * {
            font-family: "Tahoma", "Arial", "΢���ź�";
        }

        a:hover {
            background-image: url("image/gogo.gif");
            background-repeat: repeat-x;
            background-position: bottom left;
        }

        s {
            width: 90px;
            display: inline-block;
            text-decoration: none;
        }

        .w1060 {
            width: 980px;
            overflow: hidden;
        }

        .t1 {
            width: 60px;
            text-align: right;
            font-weight: bold;
        }

        .t2 {
            width: 45px;
            text-align: center;
            font-weight: bold;
        }

        .red {
            color: red;
        }

        .l {
            font-weight: bold;
        }

        .box_float {
            float: left;
            margin-right: 10px;
            margin-bottom: 10px;
        }

        .nob {
            font-weight: normal !important;
        }

        .fa,
        .fb,
        .fc {
            color: #FF8040;
            height: 16px;
            font-weight: normal;
        }

        .fa {}

        .fb {
            color: blue;
        }

        .fb:hover {
            color: red;
        }

        .fc {}

        .m10 {
            padding-left: 15px !important;
        }

        .huifang_tixing {
            border: 1px solid #FF8040;
            margin-top: 15px;
            padding: 6px 5px;
            width: 945px;
            background: #ffd8ac;
            display: block;
        }

        .yuan {
            color: #ff8040;
            font-size: 12px;
            font-family: "Tahoma";
        }

        .clear {
            font-size: 0px;
            line-height: 0px;
            height: 0px;
            margin: 0;
            padding: 0;
        }

        .link_button {}

        .yuming_tixing {
            border: 1px solid #c60063;
            margin-top: 15px;
            padding: 6px 5px 4px 5px;
            width: 945px;
            background: #ff75ba;
            display: block;
            color: red;
        }

        .yuming_tixing a {
            color: white;
        }

        .yuming_tixing b {
            color: #ffff00;
        }

        .tixing2 {
            border: 1px solid #2060a6;
            padding: 6px 5px 4px 5px;
            width: 945px;
            background: #8a9cf2;
            display: block;
            color: red;
            margin-top: 15px;
        }

        .tixing2 a {
            color: white;
        }

        .tixing2 b {
            color: #ffff15;
        }

        .h_select_op_area * {
            height: 20px !important;
            vertical-align: middle !important;
            line-height: 20px !important;
        }

        .index_box_table {
            border: 0;
        }

        .list_data {
            border: 1px solid #c3c3c3;
        }

        .list_data td {
            border-bottom: 1px solid #c3c3c3;
        }

        .list_title {
            background-color: #dfeee3;
            padding: 3px 5px;
            text-align: left;
        }

        .dan td {}

        .shuang td {
            background-color: #f9f9f9;
        }

        .d0 {
            padding: 3px 5px;
            text-align: left;
            background-color: #ffd6ac
        }

        .d1 {
            padding: 3px 3px;
            text-align: center;
            width: 40px;
        }

        .d2 {
            padding: 3px 3px;
            text-align: left;
            width: 60px;
        }

        .d3 {
            padding: 3px 3px;
            text-align: left;
            width: 60px;
        }

        .d4 {
            padding: 3px 3px;
            text-align: left;
            width: 60px;
        }

        .d5 {
            padding: 3px 3px;
            text-align: left;
            width: 60px;
        }

        .dweb {
            padding: 3px 3px 3px 8px;
            text-align: left;
        }

        .dbr {
            border-right: 1px solid #d4d4d4 !important;
        }

        .tmb {
            color: #ff8000;
        }

        .ml {
            margin-left: 12px;
        }
    </style>

    <script language="javascript">
        function hgo(dir) {
            var t = "�Ѿ�����" + (dir == "up" ? "��" : "��") + "һ��ҽԺ��";
            var obj = byid("hospital_id");
            if (dir == "up") {
                var i = obj.selectedIndex - 1;
                while (i > 0) {
                    if (obj.options[i].value > 0) {
                        obj.selectedIndex = i;
                        obj.onchange();
                        break
                    }
                    i--
                }
                if (i == 0) {
                    parent.msg_box(t, 3)
                }
            }
            if (dir == "down") {
                var i = obj.selectedIndex + 1;
                while (i < obj.options.length) {
                    if (obj.options[i].value > 0) {
                        obj.selectedIndex = i;
                        obj.onchange();
                        break
                    }
                    i++
                }
                if (i == obj.options.length) {
                    parent.msg_box(t, 3)
                }
            }
        }

        function set_index_module(hid) {
            parent.load_src(1, "main_set_module.php?hid=" + hid + "&r=" + Math.random(), 800, 500);
        }

        function submit_hf_date(date) {
            if (date != '') {
                date = date.split("-").join("");
                self.location = "patient.php?hf_time=" + date;
            }
        }

        function change_h(o) {
            if (o.value != '-1') {
                self.location = '?_tohid_=' + o.value + '&from=main';
            }
        }

        function jiuzhenlv_huizong() {
            parent.load_src(1, "jiuzhenlv_huizong.php", 800, 500);
        }
    </script>
</head>

<body style="padding:15px;">

    <table width="100%">
        <tr>
            <td align="left" class="new_yh">
                <?php
                echo $summary_info . "��";

                if ($hid > 0 && ($uinfo["index_dingzhi"] || $debug_mode)) {
                    echo '<a href="javascript:;" class="link_button ml" onclick="set_index_module(' . $hid . ')" title="�Զ�����ҳ��ʾ����">��ҳ����</a>';
                }

                ?>
            </td>
            <td align="center"></td>
            <td align="right" style="padding-right:10px">
                <nobr></nobr>
            </td>
        </tr>
    </table>

    <div class="h_select_op_area" style="margin-top:15px;">
        <?php
        if (!is_array($hospital_ids) || count($hospital_ids) == 0) {
            echo "	û��Ϊ������ҽԺ������ϵ�ϼ��쵼����";
        } else {
            if (count($hospital_ids) == 1) {
                //����һ��ҽԺ
                echo "	��ǰҽԺ��<b>" . $hospital_list[$hid]["name"] . "</b>&nbsp;&nbsp;";
            } else {
                // ���ҽԺ ��ʾ�л�
                echo '	<b>�л�ҽԺ��</b>';
                echo '	<select name="_tohid_" id="hospital_id" class="combo" onchange="change_h(this)" style="width:216px;">';
                if ($show_list_all) {
                    echo '		<option value="all" style="color:gray">-�鿴ͳ������-</option>';
                } else {
                    echo '		<option value="-1" style="color:gray">-��ѡ��ҽԺ-</option>';
                }
                foreach ($options as $v) {
                    echo '		<option value="' . $v[0] . '"' . ($v[0] == $hid ? ' selected' : '') . ($v[2] ? ' style="' . $v[2] . '"' : '') . '>' . $v[1] . ($v[0] == $hid ? ' *' : '') . '</option>';
                }
                echo '	</select>';
                echo '��<button class="button" onclick="hgo(\'up\');">��</button>&nbsp;<button class="button" onclick="hgo(\'down\');">��</button>';
            }

            if ($hid > 0 && ($debug_mode || in_array($uinfo["character_id"], array(5, 4, 19, 20)))) {
                //echo '��<a href="qudao_chengben.php" class="link_button" title="��ʾ�����ɱ�" style="color:red">�����ɱ�</a>';
                //echo '��<a href="count_web_chengben.php" class="link_button" title="��ʾ��ѯԱ�ɱ�" style="color:red">��ѯԱ�ɱ�</a>';
            }

            if ($hid > 0 && ($config["is_output"] || $debug_mode)) {
                echo '��<a href="javascript:;" class="link_button" onclick="parent.load_src(1, \'patient_output_name.php\');" title="������������">����</a>';
            }


            if ($config["show_site"] || $debug_mode) {
                echo '��<a class="link_button" href="site.php" title="�鿴�����Ҷ�Ӧ����վ�������䵽��ʱ��">��վ��������</a>';
            }
            if ($config["duokeshi_huizong"] || $debug_mode) {
                echo '��<a href="javascript:;" onclick="parent.load_src(1, \'main_huizong.php\', 800);" class="link_button" title="����������ݻ�����ʾ">����һ���</a>';
            }

            if (in_array($realname, explode(" ", $sys_super_admin))) {
                echo '<a href="javascript:;" class="link_button ml" onclick="parent.load_src(1, \'zixun_yeji_huizong.php\', 1000);">��ѯԱҵ������</a>';
            }
        }
        ?>
    </div>


    <?php
    if ($h_info["ishide"] > 0) {
        exit('	<div style="margin-top:15px; color:red;"><b>�Բ��𣬸ÿ����ѱ����أ����л��������Ҳ鿴��</b></div>');
    }


    $hide_search = 0;
    if (in_array($uinfo["character_id"], array("5", "4"))) {
        //$hide_search = 1;
    }


    ?>

    <?php if (!$hide_search && !$index_config["hide_search"]) { ?>
        <style type="text/css">
            #my_work_div {
                margin-top: 15px;
                padding: 0px;
                padding-left: 0px;
            }

            #my_work_div * {
                vertical-align: middle;
            }

            #my_work_div span,
            .buttonb,
            .buttonb_over {
                font-family: "΢���ź�";
            }

            .hide_search {
                color: silver;
                font-size: 11px;
                margin-left: 30px;
                font-family: "΢���ź�";
            }
        </style>
        <div id="my_work_div" class="w1060">
            <form name="topform" method="GET" action="patient.php">
                <span>������Ϣ������</span>
                <input name="searchword" id="searchword" value="" class="input" style="width:150px;">
                <input type="submit" class="buttonb" onclick="search_all(this)" value="����" title="�����������֣��绰����ѯ���ݣ���ע���طã�ר�Һ�" style="margin-left:10px;">
                <button onclick="tongyuansou(); return false;" class="buttonb" title="�ڱ�Ժ���п�������������" style="margin-left:10px;">ͬԺ��</button>
                <!-- <a href="?op=hide_search" class="hide_search" title="�رպ󻹿����ڡ���ҳ���ơ��д�">[�رմ�����]</a> -->
                <script type="text/javascript">
                    function search_all() {
                        byid("search_condition").innerHTML = '';
                        byid("search_type").value = "";
                    }

                    function tongyuansou() {
                        var key = byid("searchword").value;
                        if (key == '') {
                            alert("��������Ҫ������������绰���루�绰��������Ǻ���λ��");
                            byid("searchword").focus();
                            return false;
                        }
                        var url = "patient_tongyuansou.php?index_search=1&code=utf8&key=" + encodeURIComponent(key) + "&r=" +
                            Math.random();
                        parent.load_src(1, url);
                    }
                </script>
                <input type="hidden" name="search_type" value="" />
                <input type="hidden" name="index_search" value="1" />
            </form>
        </div>
    <?php } ?>



    <?php
    if ($hid > 0) {
    ?>

        <?php
        // �ط�����:
        $ht = date("Ymd");

        // �г������ҵ����лط�����
        //if ($config["show_remind_index"]) {
        if (in_array("set_huifang_tixing", $gGuaHaoConfig)) {
            $remind_count = $db->query("select count(*) as c from patient_remind where hid=$hid and remind_date=$ht", 1, "c");
            $remind_u = $db->query("select distinct uid from patient_remind where hid=$hid and remind_date=$ht", "", "uid");
            $remind_u_count = count($remind_u);
            if ($remind_count > 0) {
                $hf_str = '<a href="patient.php?show_remind_all=1&remind_date=' . $ht . '" title="����鿴����">' . $hospital_list[$hid]["name"] . '�����չ��� <b>' . $remind_count . '</b> ���ط����� (���� <b>' . $remind_u_count . '</b> ��)</a>';
                echo '<div class="huifang_tixing">' . $hf_str . '</div>';
            } else {
                //$hf_str = '�����ҽ����޻ط�����';
            }
        }

        // ָ���˵Ļط�����
        $ht_check = date("Ymd", strtotime("-1 month"));
        $has_remind = $db->query("select count(*) as c from patient_remind where hid=$hid and uid=$uid and remind_date>$ht_check", 1, "c");
        if ($has_remind) {
            $remind_arr = $db->query("select patient_name from patient_remind where hid=$hid and remind_date=$ht and uid=$uid order by id asc", "", "patient_name");
            $count = count($remind_arr);
            if ($count > 0) {
                $remind_name = array_slice($remind_arr, 0, 3);
                $hf_link = "patient.php?hf_time=$ht";
                $hf_str = '<a href="' . $hf_link . '">�ҵ����ѣ������� ' . implode("��", $remind_name) . ' �� <b>' . $count . '</b> λ������Ҫ�ط� (�ɻط�����ʱ���ѯ) ����鿴����</a>';
            } else {
                $hf_str = '<b>�ط�����</b>��������û�в�����Ҫ���� ';
            }

            $hf_str .= '����<a href="patient.php?hf_time=' . date("Ymd", strtotime("-1 day")) . '" title="�鿴������طò���">�鿴����</a>';
            $hf_str .= '����<a href="patient.php?hf_time=' . date("Ymd", strtotime("+1 day")) . '" title="�鿴������طò���">����</a>';
            $hf_str .= '����<input id="hf_date" style="width:0; border:0; overflow:hidden; padding:0; margin:0;" value="" onpropertychange="submit_hf_date(this.value)" onchange="submit_hf_date(this.value)"><a href="javascript:;" onClick="picker({el:\'hf_date\',dateFmt:\'yyyy-MM-dd\'}); return false;" align="absmiddle" title="�����ڲ鿴��Ҫ�طõĲ���">[�鿴��������]</a>';
            if ($hf_str) {
                echo '	<div class="huifang_tixing w1060">' . $hf_str . '</div>';
            }
        }

        ?>


        <?php
        // �������ڵ���վ��������
        if ($debug_mode || $config["show_site_out_date"]) {
            // ������޿�Ҫ���ڵ�����
            $cond = "hid=$hid";
            if ($debug_mode) {
                //$cond = '1';
            }
            $list = $db->query("select * from site_list where $cond order by sort desc, addtime asc");
            $out_date_arr = $will_out_arr = $no_date_arr = array();
            $dt0 = date("Ymd");
            $dt1 = date("Ymd", strtotime("+6 month"));
            foreach ($list as $li) {
                if (trim($li["out_date"]) == '') {
                    // �޹���ʱ�䣬Ҫ���Ѹ���
                    $no_date_arr[] = $li["site_url"] . " (" . $li["author"] . ")";
                } else {
                    $_dt = date("Ymd", strtotime($li["out_date"]));
                    if ($_dt < $dt0) {
                        // �ѹ��ڵ�����:
                        $out_date_arr[] = $li["site_url"] . " (" . $li["author"] . ")";
                    } else if ($_dt < $dt1) {
                        // һ�����ڼ������ڵ�:
                        $will_out_arr[] = $li["site_url"] . " (" . $li["out_date"] . ")";
                    } else {
                        // �����ʱ�仹��һ�������� ��ʱ��������
                    }
                }
            }

            $show_div = 0;
            $str = '<div class="yuming_tixing w1060" id="yuming_tixing">';
            if (count($will_out_arr) > 0) {
                $show_div = 1;
                $str .= " &nbsp;<b>" . count($will_out_arr) . '�������������ڣ�</b><a href="site.php" title="��˲鿴����">' . implode("��", $will_out_arr) . '</a>';
            }
            if (count($out_date_arr) > 0) {
                //$show_div = 1;
                //$str .= " &nbsp;<b>".count($out_date_arr).'�������ѹ��ڣ�</b>'.implode("��", $out_date_arr);
            }
            if (count($no_date_arr) > 0) {
                //$show_div = 1;
                //$str .= " &nbsp;<b>".count($no_date_arr).'������δ���õ���ʱ�䣺</b>'.implode("��", $no_date_arr);
            }
            $str .= '</div>';

            if ($show_div) {
                echo $str;
            }
        }
        ?>


        <div class="w1060" style="margin-top:15px;">
            <?php
            if (!is_array($index_config["global_hide"])) {
                $index_config["global_hide"] = array();
            }
            if (($debug_mode || in_array("all", $config["data_power"])) && !in_array("all", $index_config["global_hide"])) {
                $cid = 2;
            ?>

                <div class="box_float">
                    <table class="index_box_table" cellspacing="0" cellpadding="0" border="0">
                        <tr>
                            <td>
                                <table class="list_data" width="312">
                                    <tr>
                                        <td class="d0 red l" colspan="5" style="background-color: #9cd3b8;">������
                                            <?php if ($show_all_confirm) { ?><font style="color:#747474; font-weight:normal;">
                                                    (����ȷ���ܵ�Ժ��<b><?php echo $lastmonth_all_confirm; ?></b>��ͬ�ڣ�<b title="<?php echo $zx_cfm_month_show; ?>��ѯȷ�ϵ�Ժ����"><?php echo $tb_come_all; ?></b>)
                                                </font><?php } ?></td>
                                    </tr>
                                    <tr class="dan">
                                        <td class="d1">����</td>
                                        <td class="d2">ԤԼ<?php echo z($cid, "����", "ԤԼ"); ?></td>
                                        <td class="d3">Ԥ��<?php echo z($cid, "����", "Ԥ��"); ?></td>
                                        <td class="d4">ʵ��<?php echo z($cid, "����", "ʵ��"); ?></td>
                                        <td class="d5">δ��<?php echo z($cid, "����", "δ��"); ?></td>
                                    </tr>
                                    <tr class="shuang">
                                        <td class="d1">����</td>
                                        <td class="d2">ԤԼ<?php echo z($cid, "����", "ԤԼ"); ?></td>
                                        <td class="d3">Ԥ��<?php echo z($cid, "����", "Ԥ��"); ?></td>
                                        <td class="d4">ʵ��<?php echo z($cid, "����", "ʵ��"); ?></td>
                                        <td class="d5">δ��<?php echo z($cid, "����", "δ��"); ?></td>
                                    </tr>
                                    <tr class="dan">
                                        <td class="d1">����</td>
                                        <td class="d2">ԤԼ<?php echo z($cid, "����", "ԤԼ"); ?></td>
                                        <td class="d3">Ԥ��<?php echo z($cid, "����", "Ԥ��"); ?></td>
                                        <td class="d4">ʵ��<?php echo z($cid, "����", "ʵ��"); ?></td>
                                        <td class="d5">δ��<?php echo z($cid, "����", "δ��"); ?></td>
                                    </tr>
                                    <tr class="shuang">
                                        <td class="d1" title="����ͬ�ڣ�ԤԼ<?php echo strip_tags(z($cid, "����ͬ��", "ԤԼ")); ?> Ԥ��<?php echo strip_tags(z($cid, "����ͬ��", "Ԥ��")); ?> ʵ��<?php echo strip_tags(z($cid, "����ͬ��", "ʵ��")); ?> δ��<?php echo strip_tags(z($cid, "����ͬ��", "δ��")); ?>">
                                            ͬ��</td>
                                        <td class="d2">ԤԼ<?php echo z($cid, "ͬ��", "ԤԼ"); ?></td>
                                        <td class="d3">Ԥ��<?php echo z($cid, "ͬ��", "Ԥ��"); ?></td>
                                        <td class="d4">ʵ��<?php echo z($cid, "ͬ��", "ʵ��"); ?></td>
                                        <td class="d5">δ��<?php echo z($cid, "ͬ��", "δ��"); ?></td>
                                    </tr>
                                    <tr class="dan">
                                        <td class="d1">����</td>
                                        <td class="d2">ԤԼ<?php echo z($cid, "����", "ԤԼ"); ?></td>
                                        <td class="d3">Ԥ��<?php echo z($cid, "����", "Ԥ��"); ?></td>
                                        <td class="d4">ʵ��<?php echo z($cid, "����", "ʵ��"); ?></td>
                                        <td class="d5">δ��<?php echo z($cid, "����", "δ��"); ?></td>
                                    </tr>
                                </table>
                            </td>

                        </tr>
                    </table>
                </div>
            <?php } ?>


            <?php
            if (!in_array("zixun_yudao", $index_config["global_hide"]) && ($uinfo["show_zixun_yudao"] || $debug_mode)) {

                // ������Ԥ��:
                $_tom_yudao_all = $module_data_arr["ID_3"]["Ԥ��"]["����"];
                $_tel_tom_yudao_all = $module_data_arr["ID_4"]["Ԥ��"]["����"];
                $_wuxian_tom_yudao_all = $module_data_arr["ID_32"]["Ԥ��"]["����"];

                // ��ȡ��ҽԺ����������ѯ������
                $_web_kefu = $db->query("select id,realname from sys_admin where part_id in (2,3,13) and concat(',',hospitals,',') like '%," . $hid . ",%'", "id", "realname");

                // ���β�ѯÿ���ͷ�������Ԥ������
                $tom_arr = array();
                foreach ($_web_kefu as $_id => $_name) {
                    $_num = $module_data_arr["UID_" . $_id]["Ԥ��"]["����"];
                    if ($_num > 0) {
                        $tom_arr[$_id] = $_num;
                    }
                }
                arsort($tom_arr);
                $_kefu_uid = array_keys($tom_arr);

                if ($debug_mode) {
                    //echo "<pre>";
                    //print_r($tom_arr);
                }

                function _show_tom_data()
                {
                    global $_kefu_uid, $_web_kefu, $tom_arr, $tomorrow_tb, $tomorrow_te;
                    if (count($_kefu_uid) > 0) {
                        $_uid = array_shift($_kefu_uid);
                        $_uname = $ori_name = trim($_web_kefu[$_uid]);
                        if (strlen($_uname) > 6) {
                            if (substr_count($_uname, "��") > 0) {
                                $_arr = explode("��", $_uname, 2);
                                $_uname = $_arr[0];
                            }
                        }
                        if (strlen($_uname) > 6) {
                            $_uname = cut($_uname, 6, "");
                        }
                        $_num = $tom_arr[$_uid];
                        echo '<td class="d2" style="width:15%;text-align:center;"><span title="' . $ori_name . '">' . $_uname . '</span></td><td class="d2" style="width:10%"><b class="fa" title="' . $ori_name . '"><a href="patient.php?kefu_23_name=' . urlencode($ori_name) . '&time_type=order_date&begin_time=' . $tomorrow_tb . '&end_time=' . $tomorrow_te . '" class="fb"> ' . $_num . ' </a></b></td>';
                    } else {
                        echo '<td class="d2" style="width:15%;">&nbsp;</td><td class="d2" style="width:10%;">&nbsp;</td>';
                    }
                }


            ?>
                <!-- ��ѯ����Ԥ�� -->

                <div class="box_float">
                    <table class="index_box_table" cellspacing="0" cellpadding="0" border="0">
                        <tr>
                            <td>
                                <table width="312" class="list_data">
                                    <tr>
                                        <td class="list_title red l" colspan="8">
                                            ��ѯ����Ԥ�� <font style="font-weight:normal; color:gray;">(�����ܣ�����<b class="tmb"><?php echo intval($_tom_yudao_all); ?></b> �绰<b class="tmb"><?php echo intval($_tel_tom_yudao_all); ?></b> ����<b class="tmb"><?php echo intval($_wuxian_tom_yudao_all); ?></b>)</font>
                                        </td>
                                    </tr>
                                    <?php
                                    for ($i = 0; $i < 5; $i++) {
                                        $dan_shuang = $i % 2 == 0 ? "dan" : "shuang";
                                    ?>
                                        <tr class="<?php echo $dan_shuang; ?>">
                                            <?php _show_tom_data(); ?>
                                            <?php _show_tom_data(); ?>
                                            <?php _show_tom_data(); ?>
                                            <?php _show_tom_data(); ?>
                                        </tr>
                                    <?php } ?>

                                </table>
                            </td>
                        </tr>
                    </table>
                </div>
                <!-- ��ѯ����Ԥ���� -->
            <?php } ?>




            <?php
            if (($debug_mode || in_array("web", $config["data_power"])) && !in_array("web", $index_config["global_hide"])) {
                $cid = 3;
            ?>
                <div class="box_float">
                    <table class="index_box_table" cellspacing="0" cellpadding="0" border="0">
                        <tr>
                            <td>
                                <table class="list_data" width="312">
                                    <tr>
                                        <td class="d0 red l" colspan="5">������ѯ�� <?php if ($show_web_confirm) { ?><font style="color:#747474; font-weight:normal;">
                                                    (������ѯȷ�ϵ�Ժ��<b><?php echo $lastmonth_web_confirm; ?></b>��ͬ�ڣ�<b title="<?php echo $zx_cfm_month_show; ?>��ѯȷ�ϵ�Ժ����"><?php echo $tb_come_web; ?></b>)
                                                </font><?php } ?></td>
                                    </tr>
                                    <tr class="dan">
                                        <td class="d1">����</td>
                                        <td class="d2">ԤԼ<?php echo z($cid, "����", "ԤԼ"); ?></td>
                                        <td class="d3">Ԥ��<?php echo z($cid, "����", "Ԥ��"); ?></td>
                                        <td class="d4">ʵ��<?php echo z($cid, "����", "ʵ��"); ?></td>
                                        <td class="d5">δ��<?php echo z($cid, "����", "δ��"); ?></td>
                                    </tr>
                                    <tr class="shuang">
                                        <td class="d1">����</td>
                                        <td class="d2">ԤԼ<?php echo z($cid, "����", "ԤԼ"); ?></td>
                                        <td class="d3">Ԥ��<?php echo z($cid, "����", "Ԥ��"); ?></td>
                                        <td class="d4">ʵ��<?php echo z($cid, "����", "ʵ��"); ?></td>
                                        <td class="d5">δ��<?php echo z($cid, "����", "δ��"); ?></td>
                                    </tr>
                                    <tr class="dan">
                                        <td class="d1">����</td>
                                        <td class="d2">ԤԼ<?php echo z($cid, "����", "ԤԼ"); ?></td>
                                        <td class="d3">Ԥ��<?php echo z($cid, "����", "Ԥ��"); ?></td>
                                        <td class="d4">ʵ��<?php echo z($cid, "����", "ʵ��"); ?></td>
                                        <td class="d5">δ��<?php echo z($cid, "����", "δ��"); ?></td>
                                    </tr>
                                    <tr class="shuang">
                                        <td class="d1" title="����ͬ�ڣ�ԤԼ<?php echo strip_tags(z($cid, "����ͬ��", "ԤԼ")); ?> Ԥ��<?php echo strip_tags(z($cid, "����ͬ��", "Ԥ��")); ?> ʵ��<?php echo strip_tags(z($cid, "����ͬ��", "ʵ��")); ?> δ��<?php echo strip_tags(z($cid, "����ͬ��", "δ��")); ?>">
                                            ͬ��</td>
                                        <td class="d2">ԤԼ<?php echo z($cid, "ͬ��", "ԤԼ"); ?></td>
                                        <td class="d3">Ԥ��<?php echo z($cid, "ͬ��", "Ԥ��"); ?></td>
                                        <td class="d4">ʵ��<?php echo z($cid, "ͬ��", "ʵ��"); ?></td>
                                        <td class="d5">δ��<?php echo z($cid, "ͬ��", "δ��"); ?></td>
                                    </tr>
                                    <tr class="dan">
                                        <td class="d1">����</td>
                                        <td class="d2">ԤԼ<?php echo z($cid, "����", "ԤԼ"); ?></td>
                                        <td class="d3">Ԥ��<?php echo z($cid, "����", "Ԥ��"); ?></td>
                                        <td class="d4">ʵ��<?php echo z($cid, "����", "ʵ��"); ?></td>
                                        <td class="d5">δ��<?php echo z($cid, "����", "δ��"); ?></td>
                                    </tr>
                                </table>
                            </td>

                        </tr>
                    </table>
                </div>
            <?php } ?>


            <?php
            $dianhua_detail_isshow = 0;
            if (($debug_mode || in_array("tel", $config["data_power"])) && !in_array("tel", $index_config["global_hide"])) {
                $cid = 4;
            ?>
                <div class="box_float">
                    <table class="index_box_table" cellspacing="0" cellpadding="0" border="0">
                        <tr>
                            <td>
                                <table class="list_data" width="312">
                                    <tr>
                                        <td class="d0 red l" colspan="5" style="background-color: #cac6df; ">�绰��ѯ��
                                            <?php if ($show_tel_confirm) { ?><font style="color:#747474; font-weight:normal;">
                                                    (������ѯȷ�ϵ�Ժ��<b><?php echo $lastmonth_tel_confirm; ?></b>��ͬ�ڣ�<b title="<?php echo $zx_cfm_month_show; ?>��ѯȷ�ϵ�Ժ����"><?php echo $tb_come_tel; ?></b>)
                                                </font><?php } ?></td>
                                    </tr>
                                    <tr class="dan">
                                        <td class="d1">����</td>
                                        <td class="d2">ԤԼ<?php echo z($cid, "����", "ԤԼ"); ?></td>
                                        <td class="d3">Ԥ��<?php echo z($cid, "����", "Ԥ��"); ?></td>
                                        <td class="d4">ʵ��<?php echo z($cid, "����", "ʵ��"); ?></td>
                                        <td class="d5">δ��<?php echo z($cid, "����", "δ��"); ?></td>
                                    </tr>
                                    <tr class="shuang">
                                        <td class="d1">����</td>
                                        <td class="d2">ԤԼ<?php echo z($cid, "����", "ԤԼ"); ?></td>
                                        <td class="d3">Ԥ��<?php echo z($cid, "����", "Ԥ��"); ?></td>
                                        <td class="d4">ʵ��<?php echo z($cid, "����", "ʵ��"); ?></td>
                                        <td class="d5">δ��<?php echo z($cid, "����", "δ��"); ?></td>
                                    </tr>
                                    <tr class="dan">
                                        <td class="d1">����</td>
                                        <td class="d2">ԤԼ<?php echo z($cid, "����", "ԤԼ"); ?></td>
                                        <td class="d3">Ԥ��<?php echo z($cid, "����", "Ԥ��"); ?></td>
                                        <td class="d4">ʵ��<?php echo z($cid, "����", "ʵ��"); ?></td>
                                        <td class="d5">δ��<?php echo z($cid, "����", "δ��"); ?></td>
                                    </tr>
                                    <tr class="shuang">
                                        <td class="d1" title="����ͬ�ڣ�ԤԼ<?php echo strip_tags(z($cid, "����ͬ��", "ԤԼ")); ?> Ԥ��<?php echo strip_tags(z($cid, "����ͬ��", "Ԥ��")); ?> ʵ��<?php echo strip_tags(z($cid, "����ͬ��", "ʵ��")); ?> δ��<?php echo strip_tags(z($cid, "����ͬ��", "δ��")); ?>">
                                            ͬ��</td>
                                        <td class="d2">ԤԼ<?php echo z($cid, "ͬ��", "ԤԼ"); ?></td>
                                        <td class="d3">Ԥ��<?php echo z($cid, "ͬ��", "Ԥ��"); ?></td>
                                        <td class="d4">ʵ��<?php echo z($cid, "ͬ��", "ʵ��"); ?></td>
                                        <td class="d5">δ��<?php echo z($cid, "ͬ��", "δ��"); ?></td>
                                    </tr>
                                    <tr class="dan">
                                        <td class="d1">����</td>
                                        <td class="d2">ԤԼ<?php echo z($cid, "����", "ԤԼ"); ?></td>
                                        <td class="d3">Ԥ��<?php echo z($cid, "����", "Ԥ��"); ?></td>
                                        <td class="d4">ʵ��<?php echo z($cid, "����", "ʵ��"); ?></td>
                                        <td class="d5">δ��<?php echo z($cid, "����", "δ��"); ?></td>
                                    </tr>
                                </table>
                            </td>

                        </tr>
                    </table>
                </div>

                <?php if ($dianhua_detail_isshow) { ?>
                    <div class="clear"></div>
                <?php } ?>

            <?php } ?>



            <?php
            if (is_array($index_config["global"]) && count($index_config["global"]) > 0) {
                $module_list = $db->query("select * from index_module where isshow=1 and if_dingzhi=1 and show_type=0 order by sort desc, id asc", "id");
                foreach ($module_list as $mid => $mdef) {
                    $bg_color = $mdef["bg_color"] ? ('style="background-color:' . $mdef["bg_color"] . '"') : '';
                    if ($mdef["hospital_id"] > 0 && $mdef["hospital_id"] != $hid) continue;
                    if (in_array($mid, $index_config["global"])) {
                        $data_name = "ID_" . $mid;
                        $d = $module_data_arr[$data_name];

            ?>
                        <div class="box_float">
                            <table width="312" class="list_data">
                                <tr>
                                    <td class="list_title red l" colspan="5" <?php echo $bg_color; ?>><?php echo $mdef["name"]; ?></td>
                                </tr>
                                <tr class="dan">
                                    <td class="d1">����</td>
                                    <td class="d2">ԤԼ<?php echo w($d, "����", "ԤԼ", "condition={$mid}"); ?></td>
                                    <td class="d3">Ԥ��<?php echo w($d, "����", "Ԥ��", "condition={$mid}"); ?></td>
                                    <td class="d4">ʵ��<?php echo w($d, "����", "ʵ��", "condition={$mid}"); ?></td>
                                    <td class="d5">δ��<?php echo w($d, "����", "δ��", "condition={$mid}"); ?></td>
                                </tr>
                                <tr class="shuang">
                                    <td class="d1">����</td>
                                    <td class="d2">ԤԼ<?php echo w($d, "����", "ԤԼ", "condition={$mid}"); ?></td>
                                    <td class="d3">Ԥ��<?php echo w($d, "����", "Ԥ��", "condition={$mid}"); ?></td>
                                    <td class="d4">ʵ��<?php echo w($d, "����", "ʵ��", "condition={$mid}"); ?></td>
                                    <td class="d5">δ��<?php echo w($d, "����", "δ��", "condition={$mid}"); ?></td>
                                </tr>
                                <tr class="dan">
                                    <td class="d1">����</td>
                                    <td class="d2">ԤԼ<?php echo w($d, "����", "ԤԼ", "condition={$mid}"); ?></td>
                                    <td class="d3">Ԥ��<?php echo w($d, "����", "Ԥ��", "condition={$mid}"); ?></td>
                                    <td class="d4">ʵ��<?php echo w($d, "����", "ʵ��", "condition={$mid}"); ?></td>
                                    <td class="d5">δ��<?php echo w($d, "����", "δ��", "condition={$mid}"); ?></td>
                                </tr>
                                <tr class="shuang">
                                    <td class="d1" title="����ͬ�ڣ�ԤԼ<?php echo strip_tags(w($d, "����ͬ��", "ԤԼ", "")); ?> Ԥ��<?php echo strip_tags(w($d, "����ͬ��", "Ԥ��", "")); ?> ʵ��<?php echo strip_tags(w($d, "����ͬ��", "ʵ��", "")); ?> δ��<?php echo strip_tags(w($d, "����ͬ��", "δ��", "")); ?>">
                                        ͬ��</td>
                                    <td class="d2">ԤԼ<?php echo w($d, "ͬ��", "ԤԼ", "condition={$mid}"); ?></td>
                                    <td class="d3">Ԥ��<?php echo w($d, "ͬ��", "Ԥ��", "condition={$mid}"); ?></td>
                                    <td class="d4">ʵ��<?php echo w($d, "ͬ��", "ʵ��", "condition={$mid}"); ?></td>
                                    <td class="d5">δ��<?php echo w($d, "ͬ��", "δ��", "condition={$mid}"); ?></td>
                                </tr>
                                <tr class="dan">
                                    <td class="d1">����</td>
                                    <td class="d2">ԤԼ<?php echo w($d, "����", "ԤԼ", "condition={$mid}"); ?></td>
                                    <td class="d3">Ԥ��<?php echo w($d, "����", "Ԥ��", "condition={$mid}"); ?></td>
                                    <td class="d4">ʵ��<?php echo w($d, "����", "ʵ��", "condition={$mid}"); ?></td>
                                    <td class="d5">δ��<?php echo w($d, "����", "δ��", "condition={$mid}"); ?></td>
                                </tr>
                            </table>
                        </div>
            <?php
                    }
                }
            }
            ?>


            <?php

            $data_name = "UID_" . $uid;
            if (array_key_exists($data_name, $module_data_arr)) {
                $d = $module_data_arr[$data_name];

            ?>
                <div class="box_float">
                    <table width="312" class="list_data">
                        <tr>
                            <td class="list_title red l" colspan="5">�ҵ�����</td>
                        </tr>
                        <tr class="dan">
                            <td class="d1">����</td>
                            <td class="d2">ԤԼ<?php echo w($d, "����", "ԤԼ", "condition=my"); ?></td>
                            <td class="d3">Ԥ��<?php echo w($d, "����", "Ԥ��", "condition=my"); ?></td>
                            <td class="d4">ʵ��<?php echo w($d, "����", "ʵ��", "condition=my"); ?></td>
                            <td class="d5">δ��<?php echo w($d, "����", "δ��", "condition=my"); ?></td>
                        </tr>
                        <tr class="shuang">
                            <td class="d1">����</td>
                            <td class="d2">ԤԼ<?php echo w($d, "����", "ԤԼ", "condition=my"); ?></td>
                            <td class="d3">Ԥ��<?php echo w($d, "����", "Ԥ��", "condition=my"); ?></td>
                            <td class="d4">ʵ��<?php echo w($d, "����", "ʵ��", "condition=my"); ?></td>
                            <td class="d5">δ��<?php echo w($d, "����", "δ��", "condition=my"); ?></td>
                        </tr>
                        <tr class="dan">
                            <td class="d1">����</td>
                            <td class="d2">ԤԼ<?php echo w($d, "����", "ԤԼ", "condition=my"); ?></td>
                            <td class="d3">Ԥ��<?php echo w($d, "����", "Ԥ��", "condition=my"); ?></td>
                            <td class="d4">ʵ��<?php echo w($d, "����", "ʵ��", "condition=my"); ?></td>
                            <td class="d5">δ��<?php echo w($d, "����", "δ��", "condition=my"); ?></td>
                        </tr>
                        <tr class="shuang">
                            <td class="d1" title="����ͬ�ڣ�ԤԼ<?php echo strip_tags(w($d, "����ͬ��", "ԤԼ", "")); ?> Ԥ��<?php echo strip_tags(w($d, "����ͬ��", "Ԥ��", "")); ?> ʵ��<?php echo strip_tags(w($d, "����ͬ��", "ʵ��", "")); ?> δ��<?php echo strip_tags(w($d, "����ͬ��", "δ��", "")); ?>">
                                ͬ��</td>
                            <td class="d2">ԤԼ<?php echo w($d, "ͬ��", "ԤԼ", "condition=my"); ?></td>
                            <td class="d3">Ԥ��<?php echo w($d, "ͬ��", "Ԥ��", "condition=my"); ?></td>
                            <td class="d4">ʵ��<?php echo w($d, "ͬ��", "ʵ��", "condition=my"); ?></td>
                            <td class="d5">δ��<?php echo w($d, "ͬ��", "δ��", "condition=my"); ?></td>
                        </tr>
                        <tr class="dan">
                            <td class="d1">����</td>
                            <td class="d2">ԤԼ<?php echo w($d, "����", "ԤԼ", "condition=my"); ?></td>
                            <td class="d3">Ԥ��<?php echo w($d, "����", "Ԥ��", "condition=my"); ?></td>
                            <td class="d4">ʵ��<?php echo w($d, "����", "ʵ��", "condition=my"); ?></td>
                            <td class="d5">δ��<?php echo w($d, "����", "δ��", "condition=my"); ?></td>
                        </tr>
                    </table>
                </div>
            <?php } ?>


            <?php
            // �������ݣ�
            if (is_array($index_config[$hid]["depart"]) && count($index_config[$hid]["depart"]) > 0) {
                echo '<div class="clear" style="margin-top:20px;"></div>';
                foreach ($index_config[$hid]["depart"] as $dp_id) {
                    $dp_name = $depart_id_name[$dp_id];
                    $data_name = "DP_" . $dp_id;
                    if (array_key_exists($data_name, $module_data_arr)) {
                        $d = $module_data_arr[$data_name];

            ?>
                        <div class="box_float">
                            <table width="312" class="list_data">
                                <tr>
                                    <td class="list_title red l" colspan="5"><?php echo $dp_name; ?></td>
                                </tr>
                                <tr class="dan">
                                    <td class="d1">����</td>
                                    <td class="d2">ԤԼ<?php echo w($d, "����", "ԤԼ", "depart=" . $dp_id); ?></td>
                                    <td class="d3">Ԥ��<?php echo w($d, "����", "Ԥ��", "depart=" . $dp_id); ?></td>
                                    <td class="d4">ʵ��<?php echo w($d, "����", "ʵ��", "depart=" . $dp_id); ?></td>
                                    <td class="d5">δ��<?php echo w($d, "����", "δ��", "depart=" . $dp_id); ?></td>
                                </tr>
                                <tr class="shuang">
                                    <td class="d1">����</td>
                                    <td class="d2">ԤԼ<?php echo w($d, "����", "ԤԼ", "depart=" . $dp_id); ?></td>
                                    <td class="d3">Ԥ��<?php echo w($d, "����", "Ԥ��", "depart=" . $dp_id); ?></td>
                                    <td class="d4">ʵ��<?php echo w($d, "����", "ʵ��", "depart=" . $dp_id); ?></td>
                                    <td class="d5">δ��<?php echo w($d, "����", "δ��", "depart=" . $dp_id); ?></td>
                                </tr>
                                <tr class="dan">
                                    <td class="d1">����</td>
                                    <td class="d2">ԤԼ<?php echo w($d, "����", "ԤԼ", "depart=" . $dp_id); ?></td>
                                    <td class="d3">Ԥ��<?php echo w($d, "����", "Ԥ��", "depart=" . $dp_id); ?></td>
                                    <td class="d4">ʵ��<?php echo w($d, "����", "ʵ��", "depart=" . $dp_id); ?></td>
                                    <td class="d5">δ��<?php echo w($d, "����", "δ��", "depart=" . $dp_id); ?></td>
                                </tr>
                                <tr class="shuang">
                                    <td class="d1" title="����ͬ�ڣ�ԤԼ<?php echo strip_tags(w($d, "����ͬ��", "ԤԼ", "")); ?> Ԥ��<?php echo strip_tags(w($d, "����ͬ��", "Ԥ��", "")); ?> ʵ��<?php echo strip_tags(w($d, "����ͬ��", "ʵ��", "")); ?> δ��<?php echo strip_tags(w($d, "����ͬ��", "δ��", "")); ?>">
                                        ͬ��</td>
                                    <td class="d2">ԤԼ<?php echo w($d, "ͬ��", "ԤԼ", "depart=" . $dp_id); ?></td>
                                    <td class="d3">Ԥ��<?php echo w($d, "ͬ��", "Ԥ��", "depart=" . $dp_id); ?></td>
                                    <td class="d4">ʵ��<?php echo w($d, "ͬ��", "ʵ��", "depart=" . $dp_id); ?></td>
                                    <td class="d5">δ��<?php echo w($d, "ͬ��", "δ��", "depart=" . $dp_id); ?></td>
                                </tr>
                                <tr class="dan">
                                    <td class="d1">����</td>
                                    <td class="d2">ԤԼ<?php echo w($d, "����", "ԤԼ", "depart=" . $dp_id); ?></td>
                                    <td class="d3">Ԥ��<?php echo w($d, "����", "Ԥ��", "depart=" . $dp_id); ?></td>
                                    <td class="d4">ʵ��<?php echo w($d, "����", "ʵ��", "depart=" . $dp_id); ?></td>
                                    <td class="d5">δ��<?php echo w($d, "����", "δ��", "depart=" . $dp_id); ?></td>
                                </tr>
                            </table>
                        </div>
            <?php
                    }
                }
            }
            ?>
            <?php
            $_day = intval(date("d"));
            $_days = get_month_days();
            // �������ݣ�
            if (!is_null($index_config[$hid]["disease"]) && count($index_config[$hid]["disease"]) > 0) {
                echo '<div class="clear" style="margin-top:20px;"></div>';
                foreach ($index_config[$hid]["disease"] as $dis_id) {
                    $dis_name = $disease_id_name[$dis_id];
                    $data_name = "DIS_" . $dis_id;
                    if (array_key_exists($data_name, $module_data_arr)) {
                        $d = $module_data_arr[$data_name];

            ?>
                        <div class="box_float">
                            <table width="312" class="list_data">
                                <tr>
                                    <td class="list_title red l" colspan="5"><?php echo $dis_name; ?></td>
                                </tr>
                                <tr class="dan">
                                    <td class="d1">����</td>
                                    <td class="d2">ԤԼ<?php echo w($d, "����", "ԤԼ", "from=main&disease=" . $dis_id); ?></td>
                                    <td class="d3">Ԥ��<?php echo w($d, "����", "Ԥ��", "from=main&disease=" . $dis_id); ?></td>
                                    <td class="d4">ʵ��<?php echo w($d, "����", "ʵ��", "from=main&disease=" . $dis_id); ?></td>
                                    <td class="d5">δ��<?php echo w($d, "����", "δ��", "from=main&disease=" . $dis_id); ?></td>
                                </tr>
                                <tr class="shuang">
                                    <td class="d1">����</td>
                                    <td class="d2">ԤԼ<?php echo w($d, "����", "ԤԼ", "from=main&disease=" . $dis_id); ?></td>
                                    <td class="d3">Ԥ��<?php echo w($d, "����", "Ԥ��", "from=main&disease=" . $dis_id); ?></td>
                                    <td class="d4">ʵ��<?php echo w($d, "����", "ʵ��", "from=main&disease=" . $dis_id); ?></td>
                                    <td class="d5">δ��<?php echo w($d, "����", "δ��", "from=main&disease=" . $dis_id); ?></td>
                                </tr>
                                <tr class="dan">
                                    <td class="d1">����</td>
                                    <td class="d2">ԤԼ<?php echo w($d, "����", "ԤԼ", "from=main&disease=" . $dis_id); ?></td>
                                    <td class="d3">Ԥ��<?php echo w($d, "����", "Ԥ��", "from=main&disease=" . $dis_id); ?></td>
                                    <td class="d4">ʵ��<?php echo w($d, "����", "ʵ��", "from=main&disease=" . $dis_id); ?></td>
                                    <td class="d5">δ��<?php echo w($d, "����", "δ��", "from=main&disease=" . $dis_id); ?></td>
                                </tr>
                                <tr class="shuang">
                                    <td class="d1" title="����ͬ�ڣ�ԤԼ<?php echo strip_tags(w($d, "����ͬ��", "ԤԼ", "")); ?> Ԥ��<?php echo strip_tags(w($d, "����ͬ��", "Ԥ��", "")); ?> ʵ��<?php echo strip_tags(w($d, "����ͬ��", "ʵ��", "")); ?> δ��<?php echo strip_tags(w($d, "����ͬ��", "δ��", "")); ?>">
                                        ͬ��</td>
                                    <td class="d2">ԤԼ<?php echo w($d, "ͬ��", "ԤԼ", "from=main&disease=" . $dis_id); ?></td>
                                    <td class="d3">Ԥ��<?php echo w($d, "ͬ��", "Ԥ��", "from=main&disease=" . $dis_id); ?></td>
                                    <td class="d4">ʵ��<?php echo w($d, "ͬ��", "ʵ��", "from=main&disease=" . $dis_id); ?></td>
                                    <td class="d5">δ��<?php echo w($d, "ͬ��", "δ��", "from=main&disease=" . $dis_id); ?></td>
                                </tr>
                                <tr class="dan">
                                    <td class="d1" title="����Ԥ��<?php echo $_day . "/" . $_days; ?>">Ԥ��</td>
                                    <td class="d2">ԤԼ<?php echo aa(array("data" => @round($_days * $d["ԤԼ"]["����"] / $_day))); ?></td>
                                    <td class="d3">Ԥ��<?php echo aa(array("data" => @round($_days * $d["Ԥ��"]["����"] / $_day))); ?></td>
                                    <td class="d4">ʵ��<?php echo aa(array("data" => @round($_days * $d["ʵ��"]["����"] / $_day))); ?></td>
                                    <td class="d5">
                                        δ��<?php echo aa(array("data" => @round($_days * ($d["Ԥ��"]["����"] - $d["ʵ��"]["����"]) / $_day))); ?>
                                    </td>
                                </tr>
                                <tr class="shuang">
                                    <td class="d1">����</td>
                                    <td class="d2">ԤԼ<?php echo w($d, "����", "ԤԼ", "from=main&disease=" . $dis_id); ?></td>
                                    <td class="d3">Ԥ��<?php echo w($d, "����", "Ԥ��", "from=main&disease=" . $dis_id); ?></td>
                                    <td class="d4">ʵ��<?php echo w($d, "����", "ʵ��", "from=main&disease=" . $dis_id); ?></td>
                                    <td class="d5">δ��<?php echo w($d, "����", "δ��", "from=main&disease=" . $dis_id); ?></td>
                                </tr>
                            </table>
                        </div>
            <?php
                    }
                }
            }
            ?>

        </div>

        <div class="clear"></div>

    <?php
    }
    ?>
    </div>


    <?php if ($uinfo["show_index_info"] || $debug_mode) { ?>

        <!-- ����֪ͨ -->
        <?php if (count($notice_arr) > 0) { ?>
            <div style="margin-top:20px; margin-left:5px;" class="yh">
                <?php foreach ($notice_arr as $v) { ?>
                    <div class="notice_line">
                        <font class="yuan">��</font> <?php echo $v; ?>
                    </div>
                <?php   } ?>
            </div>
        <?php } ?>
        <!-- ����֪ͨ end -->
        <div style="margin-top:20px; margin-left:15px; color:gray;" class="yh">
            ִ��ʱ�䣺<?php echo round(now() - $pagebegintime, 4); ?>�룬<?php echo $db->select_count; ?>�β�ѯ
        </div>

    <?php } ?>

    <br>
    <br>
    <br>

</body>

</html>