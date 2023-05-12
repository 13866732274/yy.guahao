<?php
// --------------------------------------------------------
// - 功能说明 : 咨询员业绩汇总
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2016-07-14
// --------------------------------------------------------
require "lib/set_env.php";

if ($hid <= 0) exit("请在右上角“切换医院”中选择医院查看。");

// 月份默认为上月
$t0 = strtotime(date("Y-m-01"));

if ($_GET["btime"] == "") {
    $_GET["btime"] = date("Y-m-d", strtotime("-1 month", $t0));
}
if ($_GET["etime"] == "") {
    $_GET["etime"] = date("Y-m-d", $t0 - 1);
}

$t_begin = strtotime($_GET["btime"]);
$t_end = strtotime($_GET["etime"] . " 23:59:59");


$hospital_name = $hinfo["sname"];
$hospital_id_name = $db->query("select id, name from hospital where ishide=0 and sname='$hospital_name' order by id asc", "id", "name");

foreach ($hospital_id_name as $hid => $hname) {
    $p_list = $db->query("select part_id, author, count(*) as c from patient_{$hid} where addtime>=$t_begin and addtime<=$t_end and part_id not in (4) group by author", "author");
    foreach ($p_list as $kefu => $v) {
        $zixun_yuyue[$kefu] += $v["c"];
        if (!is_null($zixun_part_id) && !in_array($kefu, $zixun_part_id)) {
            $zixun_part_id[$kefu] = $v["part_id"];
        }
    }

    $p_list = $db->query("select author, count(*) as c from patient_{$hid} where status=1 and order_date>=$t_begin and order_date<=$t_end group by author", "author");
    foreach ($p_list as $kefu => $v) {
        $zixun_come[$kefu] += $v["c"];
    }
}

$part_id_name = $db->query("select id, name from sys_part", "id", "name");
if (!is_null($zixun_part_id)) {
    foreach ($zixun_part_id as $_uname => $_part_id) {
        $part_ids[$_part_id] = $part_id_name[$_part_id];
        $part_author[$_part_id][] = $_uname;
    }
}

$zixun_names = array_keys($zixun_come);
sort($zixun_names);


?>
<html>

<head>
    <title>咨询员业绩汇总</title>
    <meta http-equiv="Content-Type" content="text/html;charset=gb2312">
    <link href="lib/base.css" rel="stylesheet" type="text/css">
    <script src="lib/base.js" language="javascript"></script>
    <script src="lib/datejs/picker.js" language="javascript"></script>
    <style>
    * {
        font-family: "微软雅黑";
    }

    .condition_set {
        text-align: center;
        margin-top: 20px;
    }

    .center_show {
        margin: 0 auto;
        width: 800px;
        text-align: center;
    }

    .list {
        border: 2px solid silver;
    }

    .head {
        color: #bf0060 !important;
        border: 1px solid silver !important;
        background: #e1e7ec !important;
    }

    .item {
        text-align: center;
        padding: 4px !important;
        border-top: 1px solid silver !important;
        border-bottom: 1px solid silver !important;
    }

    .sub_title {
        text-align: left;
        padding-left: 10px !important;
        font-weight: bold;
        color: blue;
    }

    .line_huizong td {
        color: red;
    }

    .report_tips {
        padding: 30px 0 20px 0;
        text-align: center;
        font-size: 16px;
        font-family: "微软雅黑";
    }
    </style>
    <script type="text/javascript">

    </script>
</head>

<body>

    <div class="condition_set">
        <form method="GET" action="" onsubmit="" style="display:inline;">
            <input name="btime" id="begin_time" class="input" style="width:120px" value="<?php echo $_GET["btime"]; ?>"
                onclick="picker({el:'begin_time',dateFmt:'yyyy-MM-dd'})"> ~ <input name="etime" id="end_time"
                class="input" style="width:120px" value="<?php echo $_GET["etime"]; ?>"
                onclick="picker({el:'end_time',dateFmt:'yyyy-MM-dd'})">　<input type="submit" class="button" value="确定">
        </form>
    </div>

    <div class="center_show">

        <div class="report_tips"><?php echo $hospital_name . " (" . count($hospital_id_name) . "个科室)"; ?>　咨询员业绩汇总统计
        </div>

        <table class="list" width="100%">
            <tr>
                <th class="head">咨询员</th>
                <th class="head">预约人数</th>
                <th class="head">到诊人数</th>
                <th class="head">预约就诊率</th>
            </tr>

            <?php if (!is_null($part_ids)) {
                foreach ($part_ids as $_part_id => $_part_name) { ?>
            <tr>
                <td class="item sub_title" colspan="4"><?php echo $_part_name; ?>：</td>
            </tr>

            <?php foreach ($part_author[$_part_id] as $zx) { ?>
            <tr>
                <td class="item"><?php echo $zx; ?></td>
                <td class="item"><?php echo $zixun_yuyue[$zx]; ?></td>
                <td class="item"><?php echo $zixun_come[$zx]; ?></td>
                <td class="item"><?php echo round(100 * $zixun_come[$zx] / $zixun_yuyue[$zx], 1) . "%"; ?></td>
            </tr>
            <?php   } ?>

            <?php }
            } ?>

            <tr class="line_huizong">
                <td class="item">汇总</td>
                <td class="item"><?php echo @array_sum($zixun_yuyue); ?></td>
                <td class="item"><?php echo @array_sum($zixun_come); ?></td>
                <td class="item"><?php echo round(100 * @array_sum($zixun_come) / @array_sum($zixun_yuyue), 1) . "%"; ?>
                </td>
            </tr>

        </table>
    </div>

    <br>
    <center>注：该页统计仅为自挂患者，代挂或者备注的不会算入</center>
    <br>
    <br>

</body>

</html>