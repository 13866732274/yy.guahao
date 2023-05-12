<?php
/*
// 说明: 按性别报表
// 作者: 幽兰 (weelia@126.com)
// 时间: 2011-11-23
*/
require "lib/set_env.php";

// 报表核心定义:
include "rp.core.php";

$tongji_tips = " - 年龄统计 - " . $type_tips;
?>
<html>

<head>
    <title>年龄报表</title>
    <meta http-equiv="Content-Type" content="text/html;charset=gb2312">
    <link href="lib/base.css" rel="stylesheet" type="text/css">
    <script src="lib/base.js" language="javascript"></script>
    <script src="lib/datejs/picker.js" language="javascript"></script>
    <style>
    body {
        margin-top: 6px;
    }

    #rp_condition_form {
        text-align: center;
    }

    .head,
    .head a {
        font-family: "微软雅黑", "Verdana";
    }

    .item {
        font-family: "Tahoma";
        padding: 8px 3px 6px 3px !important;
    }

    .footer_op_left {
        font-family: "Tahoma";
    }

    .date_tips {
        padding: 15px 0 15px 0px;
        font-weight: bold;
        text-align: center;
        font-size: 15px;
        font-family: "微软雅黑", "Verdana";
    }

    form {
        display: inline;
    }
    </style>
</head>

<body>

    <?php include_once "rp.condition_form.php"; ?>

    <?php if ($_GET["op"] == "report") { ?>
    <?php

		if (in_array($type, array(1, 2, 3, 4))) {
			// 计算统计数据:
			$data = array();
			foreach ($final_dt_arr as $k => $v) {
				$data[$k]["all"] = $db->query("select count(*) as c from $table where $where {$timetype}>=" . $v[0] . " and {$timetype}<=" . $v[1] . " ", 1, "c");
				$data[$k]["none"] = $db->query("select count(*) as c from $table where $where age=0 and {$timetype}>=" . $v[0] . " and {$timetype}<=" . $v[1] . " ", 1, "c");
				$data[$k]["1_9"] = $db->query("select count(*) as c from $table where $where age>0 and age<=9 and {$timetype}>=" . $v[0] . " and {$timetype}<=" . $v[1] . " ", 1, "c");
				$data[$k]["10_14"] = $db->query("select count(*) as c from $table where $where age>=10 and age<=14 and {$timetype}>=" . $v[0] . " and {$timetype}<=" . $v[1] . " ", 1, "c");
				$data[$k]["15_19"] = $db->query("select count(*) as c from $table where $where age>=15 and age<=19 and {$timetype}>=" . $v[0] . " and {$timetype}<=" . $v[1] . " ", 1, "c");
				$data[$k]["20_24"] = $db->query("select count(*) as c from $table where $where age>=20 and age<=24 and {$timetype}>=" . $v[0] . " and {$timetype}<=" . $v[1] . " ", 1, "c");
				$data[$k]["25_29"] = $db->query("select count(*) as c from $table where $where age>=25 and age<=29 and {$timetype}>=" . $v[0] . " and {$timetype}<=" . $v[1] . " ", 1, "c");
				$data[$k]["30_39"] = $db->query("select count(*) as c from $table where $where age>=30 and age<=39 and {$timetype}>=" . $v[0] . " and {$timetype}<=" . $v[1] . " ", 1, "c");
				$data[$k]["40_49"] = $db->query("select count(*) as c from $table where $where age>=40 and age<=49 and {$timetype}>=" . $v[0] . " and {$timetype}<=" . $v[1] . " ", 1, "c");
				$data[$k]["50_59"] = $db->query("select count(*) as c from $table where $where age>=50 and age<=59 and {$timetype}>=" . $v[0] . " and {$timetype}<=" . $v[1] . " ", 1, "c");
				$data[$k]["60_w"] = $db->query("select count(*) as c from $table where $where age>=60 and {$timetype}>=" . $v[0] . " and {$timetype}<=" . $v[1] . " ", 1, "c");
			}
		} else if ($type == 5) {
			$arr_all = $db->query("select from_unixtime({$timetype},'%k') as sd,count(from_unixtime({$timetype},'%k')) as c from $table where $where {$timetype}>=" . $tb . " and {$timetype}<=" . $te . " group by from_unixtime({$timetype},'%k')", "sd", "c");
			$arr_none = $db->query("select from_unixtime({$timetype},'%k') as sd,count(from_unixtime({$timetype},'%k')) as c from $table where age=0 and $where {$timetype}>=" . $tb . " and {$timetype}<=" . $te . " group by from_unixtime({$timetype},'%k')", "sd", "c");
			$arr_1_9 = $db->query("select from_unixtime({$timetype},'%k') as sd,count(from_unixtime({$timetype},'%k')) as c from $table where age>0 && age<=9 and $where {$timetype}>=" . $tb . " and {$timetype}<=" . $te . " group by from_unixtime({$timetype},'%k')", "sd", "c");
			$arr_10_14 = $db->query("select from_unixtime({$timetype},'%k') as sd,count(from_unixtime({$timetype},'%k')) as c from $table where age>=10 and age<=14 and $where {$timetype}>=" . $tb . " and {$timetype}<=" . $te . " group by from_unixtime({$timetype},'%k')", "sd", "c");
			$arr_15_19 = $db->query("select from_unixtime({$timetype},'%k') as sd,count(from_unixtime({$timetype},'%k')) as c from $table where age>=15 and age<=19 and $where {$timetype}>=" . $tb . " and {$timetype}<=" . $te . " group by from_unixtime({$timetype},'%k')", "sd", "c");
			$arr_20_24 = $db->query("select from_unixtime({$timetype},'%k') as sd,count(from_unixtime({$timetype},'%k')) as c from $table where age>=20 and age<=24 and $where {$timetype}>=" . $tb . " and {$timetype}<=" . $te . " group by from_unixtime({$timetype},'%k')", "sd", "c");
			$arr_25_29 = $db->query("select from_unixtime({$timetype},'%k') as sd,count(from_unixtime({$timetype},'%k')) as c from $table where age>=25 and age<=29 and $where {$timetype}>=" . $tb . " and {$timetype}<=" . $te . " group by from_unixtime({$timetype},'%k')", "sd", "c");
			$arr_30_39 = $db->query("select from_unixtime({$timetype},'%k') as sd,count(from_unixtime({$timetype},'%k')) as c from $table where age>=30 and age<=39 and $where {$timetype}>=" . $tb . " and {$timetype}<=" . $te . " group by from_unixtime({$timetype},'%k')", "sd", "c");
			$arr_40_49 = $db->query("select from_unixtime({$timetype},'%k') as sd,count(from_unixtime({$timetype},'%k')) as c from $table where age>=40 and age<=49 and $where {$timetype}>=" . $tb . " and {$timetype}<=" . $te . " group by from_unixtime({$timetype},'%k')", "sd", "c");
			$arr_50_59 = $db->query("select from_unixtime({$timetype},'%k') as sd,count(from_unixtime({$timetype},'%k')) as c from $table where age>=50 and age<=59 and $where {$timetype}>=" . $tb . " and {$timetype}<=" . $te . " group by from_unixtime({$timetype},'%k')", "sd", "c");
			$arr_60_w = $db->query("select from_unixtime({$timetype},'%k') as sd,count(from_unixtime({$timetype},'%k')) as c from $table where age>=60 and $where {$timetype}>=" . $tb . " and {$timetype}<=" . $te . " group by from_unixtime({$timetype},'%k')", "sd", "c");

			$data = array();
			foreach ($final_dt_arr as $k => $v) {
				$data[$k]["all"] = intval($arr_all[$v]);
				$data[$k]["none"] = intval($arr_none[$v]);
				$data[$k]["0_9"] = intval($arr_0_9[$v]);
				$data[$k]["10_14"] = intval($arr_10_14[$v]);
				$data[$k]["15_19"] = intval($arr_15_19[$v]);
				$data[$k]["20_24"] = intval($arr_20_24[$v]);
				$data[$k]["25_29"] = intval($arr_25_29[$v]);
				$data[$k]["30_39"] = intval($arr_30_39[$v]);
				$data[$k]["40_49"] = intval($arr_40_49[$v]);
				$data[$k]["50_59"] = intval($arr_50_59[$v]);
				$data[$k]["60_w"] = intval($arr_60_w[$v]);
			}
		}

		?>
    <div class="date_tips"><?php echo $h_name . $tongji_tips; ?></div>
    <table width="100%" align="center" class="list">
        <tr>
            <td class="head" align="center">时间</td>
            <td class="head" align="center">总人数</td>
            <td class="head" align="center">0~9</td>
            <td class="head" align="center">10~14</td>
            <td class="head" align="center">15~19</td>
            <td class="head" align="center">20~24</td>
            <td class="head" align="center">25~29</td>
            <td class="head" align="center">30~39</td>
            <td class="head" align="center">40~49</td>
            <td class="head" align="center">50~59</td>
            <td class="head" align="center">60+</td>
            <td class="head" align="center">未知</td>

        </tr>

        <?php foreach ($final_dt_arr as $k => $v) { ?>
        <tr>
            <td class="item" align="center"><?php echo $k; ?></td>
            <td class="item" align="center"><?php echo $data[$k]["all"]; ?></td>
            <td class="item" align="center"><?php echo $data[$k]["1_9"]; ?></td>
            <td class="item" align="center"><?php echo $data[$k]["10_14"]; ?></td>
            <td class="item" align="center"><?php echo $data[$k]["15_19"]; ?></td>
            <td class="item" align="center"><?php echo $data[$k]["20_24"]; ?></td>
            <td class="item" align="center"><?php echo $data[$k]["25_29"]; ?></td>
            <td class="item" align="center"><?php echo $data[$k]["30_39"]; ?></td>
            <td class="item" align="center"><?php echo $data[$k]["40_49"]; ?></td>
            <td class="item" align="center"><?php echo $data[$k]["50_59"]; ?></td>
            <td class="item" align="center"><?php echo $data[$k]["60_w"]; ?></td>
            <td class="item" align="center"><?php echo $data[$k]["none"]; ?></td>
        </tr>
        <?php } ?>
    </table>

    <br>
    <?php } ?>


    <?php
	$test_arr = [
		'someh'
	];
	?>

    <script>
    var arr = <?php echo JSON_encode($test_arr); ?>;

    console.log(arr);
    </script>
</body>

</html>