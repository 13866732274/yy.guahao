<?php
/*
// - 功能说明 : 病种统计 (包括二级病种)
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2012-12-13
*/
require "lib/set_env.php";
//set_time_limit(30);

$table = "patient_" . $hid;
$h_name = $db->query("select name from hospital where id=$hid limit 1", 1, "name");

// 时间定义
$this_month_b = mktime(0, 0, 0, date("m"), 1);


$month_arr = array();
for ($i = 0; $i <= 15; $i++) {
    $mb = strtotime("-{$i} month", $this_month_b);
    $me = strtotime("+1 month", $mb) - 1;
    if ($i == 0) $mn = "本月";
    if ($i == 1) $mn = "上月";
    if ($i > 1) $mn = date("Y-m", $mb);
    $month_arr[$mn] = array($mb, $me);
}
//echo "<pre>";
//print_r($month_arr);

if (!isset($_GET["month"])) {
    $_GET["month"] = "本月";
}

if (!array_key_exists($_GET["month"], $month_arr)) {
    exit("参数错误: " . $_GET["month"]);
}


list($m_begin, $m_end) = $month_arr[$_GET["month"]];


// 病种目标：
$int_month = date("Ym", $m_begin);
$mubiao_arr = $db->query("select dis_id, mubiao, author from disease_mubiao where month='$int_month'", "dis_id");


// 病种列表:
$disease_id_name = $db->query("select id,name from disease where hospital_id='$hid' order by id asc", "id", "name");

$_begin_time = now();

// 针对每个病种进行查询:
// 由于需要查询的sql语句太多,所以考虑把一次读取出来处理(注意这里的数据量可能很多,虽然是一条语句,可能也会很慢)
$datas = $db->query("select part_id,disease_id,disease_2,status from $table where order_date>=$m_begin and order_date<=$m_end and disease_id>0");


// 分析:
$d2_order = $d2_come = $d2_sub = $d1_order = $d1_come = array();
$d1_web_come = $d1_web_order = $d2_web_come = $d2_web_order = array();
foreach ($datas as $v) {
    $did = intval($v["disease_id"]);
    $d1_order[$did] = intval($d1_order[$did]) + 1;
    if ($v["status"] == 1) {
        $d1_come[$did] = intval($d1_come[$did]) + 1;
    }
    if ($v["part_id"] == 2) {
        $d1_web_order[$did] = intval($d1_web_order[$did]) + 1;
        if ($v["status"] == 1) {
            $d1_web_come[$did] = intval($d1_web_come[$did]) + 1;
        }
    }
    if (trim($v["disease_2"]) != '') {
        $d2_arr = explode(",", trim($v["disease_2"]));
        foreach ($d2_arr as $v2) {
            $v2 = trim($v2);
            if ($v2 != '') {
                if (!in_array($v2, $d2_sub[$did])) {
                    $d2_sub[$did][] = $v2;
                }
                $d2_order[$did][$v2] = intval($d2_order[$did][$v2]) + 1;
                if ($v["status"] == 1) {
                    $d2_come[$did][$v2] = intval($d2_come[$did][$v2]) + 1;
                }
                if ($v["part_id"] == 2) {
                    $d2_web_order[$did][$v2] = intval($d2_web_order[$did][$v2]) + 1;
                    if ($v["status"] == 1) {
                        $d2_web_come[$did][$v2] = intval($d2_web_come[$did][$v2]) + 1;
                    }
                }
            }
        }
    }
}

// 数量较多的疾病，放前面
arsort($d1_order);

$_time_used = round(now() - $_begin_time, 4);


/*
echo "<pre>";
print_r($disease_id_name);
print_r($d2_sub);
print_r($d1_order);
print_r($d1_come);
print_r($d2_order);
print_r($d2_come);
print_r($d1_web_order);
print_r($d1_web_come);
print_r($d2_web_order);
print_r($d2_web_come);
exit;
*/


$title = '病种报表(带二级病种)';
?>
<html>

<head>
    <title><?php echo $title; ?></title>
    <meta http-equiv="Content-Type" content="text/html;charset=gb2312">
    <link href="lib/base.css" rel="stylesheet" type="text/css">
    <script src="lib/base.js" language="javascript"></script>
    <style>
    form {
        display: inline;
    }

    .red {
        color: red !important;
    }

    .report_tips {
        padding: 20px 0 10px 0;
        text-align: center;
        font-size: 14px;
        font-weight: bold;
        width: 750px;
    }

    .list {
        border: 2px solid silver !important;
    }

    .head {
        border: 0 !important;
        background: #e1e7ec !important;
    }

    .item {
        border-top: 1px solid #e2e6e7 !important;
        border-bottom: 1px solid #e2e6e7 !important;
        text-align: center;
        padding: 8px 3px !important;
    }

    .hl {
        border-left: 1px solid silver !important;
    }

    .hr {
        border-right: 1px solid silver !important;
    }

    .ht {
        border-top: 1px solid silver !important;
    }

    .hb {
        border-bottom: 1px solid silver !important;
    }

    .huizong {
        font-weight: bold;
        color: #ff8040;
        background: #eff2f5 !important;
        padding: 5px;
        text-align: center;
    }

    .left {
        text-align: left;
    }

    .center {
        text-align: center;
    }

    .p20 {
        padding-left: 20px !important;
    }

    .p50 {
        padding-left: 50px !important;
    }
    </style>

    <script type="text/javascript">
    function set_mubiao(did, month) {
        var url = "patient_disease_set_mubiao.php?did=" + did + "&month=" + month;
        parent.load_src(1, url, 500, 200);
    }
    </script>
</head>

<body>
    <div style="margin:10px 0 0 0px;">
        <b>月份：</b>
        <?php foreach ($month_arr as $k => $v) { ?>
        <a
            href="?month=<?php echo $k; ?>"><?php echo $_GET["month"] == $k ? ('<font color="red"><b>' . $k . '</b></font>') : $k; ?></a>&nbsp;
        <?php } ?>
    </div>


    <div class="report_tips"><?php echo $h_name . " " . $_GET["month"]; ?> 病种报表</div>

    <?php $index = 0; ?>
    <table class="list" width="750">
        <tr>
            <th class="head hb" width="20%"></th>
            <th class="head hb hl red" width="30%" colspan="2">全部</th>
            <th class="head hb hl red" rowspan="2">本月任务</th>
            <th class="head hb hl red" width="30%" colspan="2">网络</th>
        </tr>

        <tr>
            <th class="head hb left p20" width="20%">疾病名称</th>

            <th class="head hb hl red" width="15%">预到</th>
            <th class="head hb red" width="15%">实到</th>

            <th class="head hb hl red" width="15%">预到</th>
            <th class="head hb red" width="15%">实到</th>
        </tr>


        <?php
        $huizong = array();
        foreach ($d1_order as $did => $tmp) {
            if (!array_key_exists($did, $disease_id_name)) continue;
            $dname = $disease_id_name[$did];

            $huizong[1] += intval($d1_order[$did]);
            $huizong[2] += intval($d1_come[$did]);
            $huizong[3] += intval($mubiao_arr[$did]["mubiao"]);
            $huizong[4] += intval($d1_web_order[$did]);
            $huizong[5] += intval($d1_web_come[$did]);
        ?>

        <?php
            if ($index++ > 0 && count($d2_sub) > 0) {
            ?>
    </table>

    <table class="list" width="750" style="margin-top:10px;">
        <?php } ?>


        <tr onmouseover="mi(this)" onmouseout="mo(this)">
            <td class="item left p20" width="20%"><b><?php echo $dname; ?></b></td>

            <td class="item hl" width="15%"><?php echo isset($d1_order[$did]) ? intval($d1_order[$did]) : ''; ?></td>
            <td class="item" width="15%"><?php echo isset($d1_come[$did]) ? intval($d1_come[$did]) : ''; ?></td>

            <td class="item hl">
                <a href="javascript:;"
                    onclick="set_mubiao(<?php echo $did; ?>, <?php echo $int_month; ?>)"><?php echo $mubiao_arr[$did]["mubiao"] ? ($mubiao_arr[$did]["mubiao"] . " (" . $mubiao_arr[$did]["author"] . ")") : "添加"; ?></a>
            </td>

            <td class="item hl" width="15%"><?php echo isset($d1_web_order[$did]) ? intval($d1_web_order[$did]) : ''; ?>
            </td>
            <td class="item" width="15%"><?php echo isset($d1_web_come[$did]) ? intval($d1_web_come[$did]) : ''; ?></td>
        </tr>
        <?php
            if (is_array($d2_sub[$did]) && count($d2_sub[$did]) > 0) {
                foreach ($d2_sub[$did] as $d2name) {
    ?>
        <tr onmouseover="mi(this)" onmouseout="mo(this)">
            <td class="item left p50"><?php echo $d2name; ?></td>

            <td class="item hl"><?php echo isset($d2_order[$did][$d2name]) ? intval($d2_order[$did][$d2name]) : ''; ?>
            </td>
            <td class="item"><?php echo isset($d2_come[$did][$d2name]) ? intval($d2_come[$did][$d2name]) : ''; ?></td>

            <td class="item hl"></td>

            <td class="item hl">
                <?php echo isset($d2_web_order[$did][$d2name]) ? intval($d2_web_order[$did][$d2name]) : ''; ?></td>
            <td class="item">
                <?php echo isset($d2_web_come[$did][$d2name]) ? intval($d2_web_come[$did][$d2name]) : ''; ?></td>
        </tr>
        <?php
                }
            }
        }
?>



        <tr onmouseover="mi(this)" onmouseout="mo(this)">
            <td class="huizong left p20"><b style="color:red">汇总</b></td>

            <td class="huizong hl"><?php echo $huizong[1]; ?></td>
            <td class="huizong"><?php echo $huizong[2]; ?></td>

            <td class="huizong hl"><?php echo $huizong[3]; ?></td>

            <td class="huizong hl"><?php echo $huizong[4]; ?></td>
            <td class="huizong"><?php echo $huizong[5]; ?></td>
        </tr>


    </table>

    <br>
    <div style="color:silver;">查询分析耗时：<?php echo $_time_used; ?>s，总结果数：<?php echo count($datas); ?>条</div>
    <br>
    <br>
    <br>



</body>

</html>