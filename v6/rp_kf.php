<?php
/*
// 说明: 报表
// 作者: 幽兰 (weelia@126.com)
// 时间: 2011-11-24
*/
require "lib/set_env.php";

// 报表核心定义:
include "rp.core.php";

$tongji_tips = " - 客服统计 - " . $type_tips;
?>
<html>

<head>
    <title>客服报表</title>
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

    .red {
        color: red !important;
    }
    </style>
</head>

<body>

    <?php include_once "rp.condition_form.php"; ?>

    <?php if ($_GET["op"] == "report") { ?>
    <?php

        // 读取客服
        $kf_arr = $db->query("select author,count(author) as c from $table where $where author!='' and order_date>=$max_tb and order_date<=$max_te group by author order by c desc", "author", "c");
        if (count($kf_arr) == 0) {
            exit_html("<center>没有客服，无法统计。</center>");
        }
        if (count($kf_arr) > 20) {
            $kf_count = count($kf_arr);
            $kf_arr = array_slice($kf_arr, 0, 20);
            $tips = " (共{$kf_count}位客服，由于显示需要，仅按活跃度取前20位)";
        }

        if (in_array($type, array(1, 2, 3, 4))) {
            // 计算统计数据:
            $data = array();
            foreach ($final_dt_arr as $k => $v) {
                $data[$k]["总"] = $db->query("select count(*) as c from $table where $where {$timetype}>=" . $v[0] . " and {$timetype}<=" . $v[1] . " ", 1, "c");

                foreach ($kf_arr as $me => $num) {
                    $data[$k][$me] = $db->query("select count(*) as c from $table where $where author='{$me}' and {$timetype}>=" . $v[0] . " and {$timetype}<=" . $v[1] . " ", 1, "c");
                }
            }
        } else if ($type == 5) {
            $arr = array();
            $arr["总"] = $db->query("select from_unixtime({$timetype},'%k') as sd,count(from_unixtime({$timetype},'%k')) as c from $table where $where {$timetype}>=" . $tb . " and {$timetype}<=" . $te . " group by from_unixtime({$timetype},'%k')", "sd", "c");

            foreach ($kf_arr as $me => $num) {
                $arr[$me] = $db->query("select from_unixtime({$timetype},'%k') as sd,count(from_unixtime({$timetype},'%k')) as c from $table where author='{$me}' and $where {$timetype}>=" . $tb . " and {$timetype}<=" . $te . " group by from_unixtime({$timetype},'%k')", "sd", "c");
            }

            $data = array();
            foreach ($final_dt_arr as $k => $v) {
                $data[$k]["总"] = intval($arr["总"][$v]);
                foreach ($kf_arr as $me => $num) {
                    $data[$k][$me] = intval($arr[$me][$v]);
                }
            }
        }


        ?>
    <div class="date_tips"><?php echo $h_name . $tongji_tips . $tips; ?></div>
    <table width="100%" align="center" class="list">
        <tr>
            <td class="head" align="center">时间</td>
            <td class="head red" align="center">总计</td>
            <?php foreach ($kf_arr as $me => $num) { ?>
            <td class="head" align="center"><?php echo $me; ?></td>
            <?php } ?>
        </tr>

        <?php foreach ($final_dt_arr as $k => $v) { ?>
        <tr>
            <td class="item" align="center"><?php echo $k; ?></td>
            <td class="item" align="center"><?php echo $data[$k]["总"]; ?></td>
            <?php foreach ($kf_arr as $me => $num) { ?>
            <td class="item" align="center"><?php echo $data[$k][$me]; ?></td>
            <?php   } ?>
        </tr>
        <?php } ?>
        <tr>
            <td class="item" align="center">总计</td>
            <td class="item" align="center"><?php echo array_sum($kf_arr); ?></td>
            <?php foreach ($kf_arr as $me => $num) { ?>
            <td class="item" align="center"><?php echo $num; ?></td>
            <?php   } ?>
        </tr>
    </table>

    <br>
    <?php } ?>


</body>

</html>