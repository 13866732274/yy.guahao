<?php
// --------------------------------------------------------
// - ����˵�� : �켣����
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2015-6-12
// --------------------------------------------------------
require "lib/set_env.php";

if ($hid <= 0) {
    exit("��������ҳѡ��ҽԺ����");
}
$hinfo = $db->query("select * from hospital where id=$hid limit 1", 1);

if ($_GET["from_date"] == '') {
    $_GET["from_date"] = date("Y-m-01");
}
if ($_GET["to_date"] == '') {
    $_GET["to_date"] = date("Y-m-d");
}
$from_date = $_GET["from_date"];
$to_date = $_GET["to_date"];

$from_time = strtotime($from_date);
$to_time = strtotime($to_date . " 23:59:59");


// ͳ�Ƹ�ʱ�䷶Χ�ڵı����:
$all_count = $db->query("select count(*) as c from patient_{$hid} where part_id in (2,3) and addtime>=$from_time and addtime<=$to_time", "1", "c");
$biaoji_count = $db->query("select count(*) as c from patient_{$hid} where part_id in (2,3) and addtime>=$from_time and addtime<=$to_time and guiji!=''", "1", "c");
$biaoji_per = $all_count == 0 ? "0%" : round(100 * $biaoji_count / $all_count, 1) . "%";

?>
<html>

<head>
    <title>�켣����</title>
    <meta http-equiv="Content-Type" content="text/html;charset=gb2312">
    <link href="lib/base.css" rel="stylesheet" type="text/css">
    <script src="lib/base.js" language="javascript"></script>
    <script src="lib/sorttable_keep.js" language="javascript"></script>
    <script src="lib/datejs/picker.js" language="javascript"></script>
    <style>
    * {
        font-family: "΢���ź�";
    }

    .input,
    .input_focus {
        font-family: "����";
    }

    td {
        line-height: 20px;
    }

    .button1 {
        color: red !important;
        font-weight: bold;
        margin-right: 10px;
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

    function show_huizong() {
        var from_date = byid("from_date").value;
        var to_date = byid("to_date").value;
        parent.load_src(1, 'qudao_report_huizong.php?from_date=' + from_date + "&to_date=" + to_date);
    }

    function show_disease_report() {
        var from_date = byid("from_date").value;
        var to_date = byid("to_date").value;
        parent.load_src(1, 'qudao_report_disease.php?from_date=' + from_date + "&to_date=" + to_date);
    }

    function show_disease_with_gid(gid) {
        var from_date = byid("from_date").value;
        var to_date = byid("to_date").value;
        parent.load_src(1, 'qudao_report_disease.php?from_date=' + from_date + "&to_date=" + to_date + "&gid=" + gid);
    }
    </script>
</head>

<body>
    <!-- ͷ�� begin -->
    <table class="headers" width="100%">
        <tr>
            <td style="width:120px">
                <nobr class="tips">�켣ͳ�Ʊ���</nobr>
            </td>
            <td align="center">
                <form method="GET">
                    <button onclick="show_huizong();return false;" class="button button1" title="�鿴������һ���">����</button>
                    <button onclick="show_disease_report();return false;" class="button button1"
                        title="�鿴��������">����</button>���������ң�<?php echo $hinfo["name"]; ?>������������ֹ�� <input name="from_date"
                        id="from_date" class="input" size="12" value="<?php echo $from_date; ?>"
                        onclick="picker({el:'from_date',dateFmt:'yyyy-MM-dd'})"> ~ <input name="to_date" id="to_date"
                        class="input" size="12" value="<?php echo $to_date; ?>"
                        onclick="picker({el:'to_date',dateFmt:'yyyy-MM-dd'})"> <input class="button" type="submit"
                        value="ȷ��">������(�����=<?php echo $biaoji_count . "/" . $all_count . "=" . $biaoji_per; ?>)
                </form>
            </td>
            <td align="right" style="width:120px">
                <button onclick="self.location.reload()" class="button" title="">ˢ��</button>
            </td>
        </tr>
    </table>
    <!-- ͷ�� end -->

    <div class="space"></div>

    <!-- �����б� begin -->
    <form name="mainform">
        <table width="100%" align="center" class="list">
            <tr>
                <td class="head" align="left" width="40%">����</td>
                <td class="head" align="center" width="10%">ԤԼ</td>
                <td class="head" align="center" width="10%">��Ժ</td>
                <td class="head" align="center" width="40%"></td>
            </tr>

            <!-- ��Ҫ�б����� begin -->
            <?php

            // ʹ��group by���ٶȷǳ���:
            $yuyue_guiji = $db->query("select guiji, count(guiji) as c from patient_{$hid} where addtime>=$from_time and addtime<=$to_time and guiji!='' group by guiji", "guiji", "c");
            $daoyuan_guiji = $db->query("select guiji, count(guiji) as c from patient_{$hid} where order_date>=$from_time and order_date<=$to_time and guiji!='' and status=1 group by guiji", "guiji", "c");

            $yuyue_qudao = $db->query("select qudao, count(qudao) as c from patient_{$hid} where addtime>=$from_time and addtime<=$to_time and qudao!='' group by qudao", "qudao", "c");
            $daoyuan_qudao = $db->query("select qudao, count(qudao) as c from patient_{$hid} where order_date>=$from_time and order_date<=$to_time and qudao!='' and status=1 group by qudao", "qudao", "c");

            foreach ($guiji_arr as $gid => $gname) {
            ?>
            <tr<?php echo $hide_line ? " class='hide'" : ""; ?> onmouseover="mi(this)" onmouseout="mo(this)">
                <td align="left" class="item"><b style="color:red"><?php echo $gname; ?></b></td>
                <td align="center" class="item"><b style="color:red"><?php echo $yuyue_guiji[$gid]; ?></b></td>
                <td align="center" class="item"><a href="javascript:;"
                        onclick="show_disease_with_gid(<?php echo $gid; ?>);"><b
                            style="color:red"><?php echo $daoyuan_guiji[$gid]; ?></b></a></td>
                <td align="center" class="item">&nbsp;</td>
                </tr>
                <?php

                    // ��ѯ:
                    $data = $db->query("select * from dict_qudao where main_id=$gid order by sort desc, id asc");
                    foreach ($data as $line) {
                        $id = $line["id"];
                    ?>
                <tr<?php echo $hide_line ? " class='hide'" : ""; ?> onmouseover="mi(this)" onmouseout="mo(this)">
                    <td align="left" class="item">������������<?php echo $line["name"]; ?></td>
                    <td align="center" class="item"><?php echo $yuyue_qudao[$id]; ?></td>
                    <td align="center" class="item"><?php echo $daoyuan_qudao[$id]; ?></td>
                    <td align="center" class="item">&nbsp;</td>
                    </tr>
                    <?php
                    }
                }
                    ?>
                    <!-- ��Ҫ�б����� end -->
        </table>
    </form>

    <br>
    ִ�к�ʱ��<?php echo round(now() - $pagebegintime, 4); ?>s
    <br>
    <br>

</body>

</html>