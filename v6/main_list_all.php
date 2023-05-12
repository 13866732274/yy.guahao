<?php
/*
// 说明: 显示汇总列表
// 作者: 幽兰 (934834734@qq.com)
// 时间: 2013-01-07
*/

if (!defined("BRDD_MAIN")) exit("System Error");


//@2012-09-18 更新：不选择任何医院时，列出所有管辖的医院的到院数据
$data_arr = $db->query("select * from index_cache where hid in ($hids) ", "hid");

$hid_data_arr = array();
foreach ($data_arr as $_hid => $li) {
	$tmp = $li["data"];
	if ($tmp != '') {
		$res = @unserialize($tmp);
		$hid_data_arr[$_hid]["x1"] = intval($res["ID_2"]["实到"]["今日"]);
		$hid_data_arr[$_hid]["x2"] = intval($res["ID_2"]["实到"]["昨日"]);
		$hid_data_arr[$_hid]["x3"] = intval($res["ID_2"]["实到"]["本月"]);
		$hid_data_arr[$_hid]["x4"] = intval($res["ID_2"]["实到"]["同期"]);
		$hid_data_arr[$_hid]["x5"] = intval($res["ID_2"]["实到"]["上月"]);
		$hid_data_arr[$_hid]["x6"] = intval($res["ID_1"]["实到"]["今日"]);
		$hid_data_arr[$_hid]["x7"] = intval($res["ID_1"]["实到"]["本月"]);
		$hid_data_arr[$_hid]["x8"] = intval($res["ID_4"]["实到"]["今日"]);
		$hid_data_arr[$_hid]["x9"] = intval($res["ID_4"]["实到"]["本月"]);
		$hid_data_arr[$_hid]["x10"] = intval($res["ID_2"]["预约"]["今日"]);
		$hid_data_arr[$_hid]["x11"] = intval($res["ID_1"]["预约"]["今日"]);
	}
}

// 华美数据 @ 2016-5-21
//if ($debug_mode || in_array(15, $hospital_ids)) {
/*
if ($debug_mode) {
	$hmid = 15;
	$hmdb = new mysql("bG9jYWxob3N0fHVzcmJyZGRzaGJ0Y29tY258ZjJaZXJ0bklmTlZaUUJyfGJyZGRzaGJ0Y29tY258Z2Jr");

	$hid_data_arr[$hmid] = array();

	$hm_int_today = date("Ymd");
	$hm_data_today = $hmdb->query("select * from today_data where date='$hm_int_today' limit 1", 1);
	$hid_data_arr[$hmid]["x1"] = $hm_data_today["today_chuzhen"];

	$hm_int_yestoday = date("Ymd", $yesterday_tb);
	$hm_data_yestoday = $hmdb->query("select * from yuyue_daoyuan_data where hid=$hmid and date='$hm_int_yestoday' limit 1", 1);
	$hid_data_arr[$hmid]["x2"] = $hm_data_yestoday["d4"];

	$hm_int_month = date("Ym01");
	$hm_data_month = $hmdb->query("select sum(yuyue_all) as yuyue_all, sum(yuyue_web) as yuyue_web, sum(yuyue_tel) as yuyue_tel, sum(d4) as d4, sum(d5) as d5, sum(d6) as d6, sum(d7) as d7 from yuyue_daoyuan_data where hid=$hmid and date>=$hm_int_month and date<='$hm_int_yestoday'", 1);
	$hid_data_arr[$hmid]["x3"] = $hm_data_month["d4"];

	$hid_data_arr[$hmid]["x4"] = 0;
	$hid_data_arr[$hmid]["x5"] = 0;
	$hid_data_arr[$hmid]["x6"] = 0;
	$hid_data_arr[$hmid]["x7"] = 0;
	$hid_data_arr[$hmid]["x8"] = 0;
	$hid_data_arr[$hmid]["x9"] = 0;
	$hid_data_arr[$hmid]["x10"] = 0;
	$hid_data_arr[$hmid]["x11"] = 0;
}
*/


// 计算增长率:
$hid_percent = array();
foreach ($hid_data_arr as $k => $v) {
	$per = $v["x3"] - $v["x4"];
	$hid_percent[$k] = $per;
}


$h_arr = $db->query("select id, sname, name from hospital where ishide=0 and id in ($hids) order by sort desc, name asc", "id");
foreach ($h_arr as $_id => $_h) {
	$_name = $_h["sname"];
	$h_name = $_h["name"];
	$h_ids_arr[$_name][] = $_id;
	$h_name_arr[$_name][] = $h_name;

	foreach ($hid_data_arr[$_id] as $f => $value) {
		$h_data[$_name][$f] += $value;
	}
}

foreach ($h_data as $k => $v) {
	$hid_percent2[$k] = $v["x3"] - $v["x4"];
}
//arsort($hid_percent2);

// 排序
ksort($hid_percent2);

// 上海的排前面:
foreach ($hid_percent2 as $k => $v) {
	if (substr_count($k, "上海") > 0) {
		$shanghai[$k] = $v;
		unset($hid_percent2[$k]);
	}
}
foreach ($hid_percent2 as $k => $v) {
	$shanghai[$k] = $v;
}
$hid_percent2 = $shanghai;



?>
<!DOCTYPE html>
<html>

<head>
    <title>挂号系统 - 首页</title>
    <meta http-equiv="Content-Type" content="text/html;charset=gb2312">
    <link href="lib/base.css?20150625" rel="stylesheet" type="text/css">
    <script src="lib/base.js?20150625" language="javascript"></script>
    <script src="lib/datejs/picker.js" language="javascript"></script>
    <script src="lib/sorttable_keep.js?ver=20130131" language="javascript"></script>
    <script src="lib/round_table.js" language="javascript"></script>
    <style type="text/css">
    * {
        font-family: "Tahoma", "Arial", "微软雅黑";
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
        font-family: "Arial";
        color: #FF8040;
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
        padding: 6px 5px 4px 5px;
        background: #FFF8F0;
        display: inline;
    }

    .yuan {
        color: #ff8040;
        font-size: 12px;
        font-family: Tahoma;
    }

    .clear {
        font-size: 0px;
        line-height: 0px;
        height: 0px;
        margin: 0;
        padding: 0;
    }

    .dan td {}

    .shuang td {
        background-color: #f9f9f9;
    }

    .d1 {
        padding: 3px 0 3px 15px;
        text-align: left;
    }

    .d2 {
        padding: 3px 0 3px 0px;
        text-align: left;
        background-color: ;
    }

    .d3 {
        padding: 3px 0 3px 0px;
        text-align: left;
    }

    .d4 {
        padding: 3px 0 3px 0px;
        text-align: left;
        background-color: ;
    }

    .d5 {
        padding: 3px 0 3px 0px;
        text-align: left;
    }

    #come_list_area {
        margin: 30px 0px 20px 0px;
    }

    .come_list {
        border: 0px solid #93bde3;
        padding: 0px;
        background: white;
    }

    .come_head td {
        border: 1px solid #cce0f2;
        border-left: 0;
        border-right: 0;
        background: #f2f8f9;
        padding: 4px 3px 3px 3px;
        font-weight: bold;
    }

    .come_line td {
        border: 1px solid #cce0f2;
        border-left: 0;
        border-right: 0;
        padding: 4px 3px 3px 3px;
    }

    .al {
        text-align: left;
        padding-left: 15px !important;
    }

    .ac {
        text-align: center;
    }

    .ar {
        text-align: right;
        padding-right: 5px !important;
    }

    .red {
        color: red;
    }

    .column_sortable {
        cursor: pointer;
        color: blue;
        font-family: "微软雅黑";
    }

    .h_select_op_area * {
        height: 20px !important;
        vertical-align: middle !important;
        line-height: 20px !important;
    }

    .wee_huizong_right * {
        vertical-align: middle;
    }

    .bold,
    .bold * {
        font-weight: bold;
    }

    .hebing {
        background: #ddecf2;
    }

    .absmiddle,
    .absmiddle * {
        vertical-align: middle;
    }
    </style>

    <script language="javascript">
    function hgo(dir) {
        var t = "已经是最" + (dir == "up" ? "上" : "下") + "一家医院了";
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
        parent.load_src(1, "main_set_module.php?hid=" + hid + "&r=" + Math.random(), 700, 400);
    }

    function submit_hf_date(date) {
        if (date != '') {
            date = date.split("-").join("");
            self.location = "patient.php?hf_time=" + date;
        }
    }

    function update_come_data(o) {
        var url = "/v6/lib/update_come.php";
        parent.load_src(1, url, 500, 200);
    }

    function set_sort_default() {
        set_cookie("141442c52f123f12ca91142dd33db676_come_list", "", 0);
        self.location.reload();
    }

    function set_hebing_mode(obj) {
        if (obj.checked) {
            self.location = "?hebing=1";
        } else {
            self.location = "?";
        }
    }

    function h_zhankai(hname, hid_string, obj) {
        if (obj.title == "展开") {
            var method = "展开";
            obj.title = "折叠";
            obj.innerHTML = '<img src="image/wee_jian.gif" align="absmiddle">';
            var display = "";
        } else {
            var method = "折叠";
            obj.title = "展开";
            obj.innerHTML = '<img src="image/wee_jia.gif" align="absmiddle">';
            var display = "none";
        }

        var hid_arr = hid_string.split(",");
        for (var i = 0; i < hid_arr.length; i++) {
            hid = hid_arr[i];
            byid("hid_" + hid).style.display = display;
        }

        var zhan = get_cookie("main_list_all_zhan");
        var zhan_arr = zhan.split("|");
        if (method == "展开") {
            // 展开状态记录到cookie中:
            if (zhan == '') {
                zhan_arr[0] = hname;
            } else {
                if (!in_array(hname, zhan_arr)) {
                    zhan_arr[zhan_arr.length] = hname;
                }
            }
        } else {
            // 从数组中删除:
            if (in_array(hname, zhan_arr)) {
                for (var i = 0; i < zhan_arr.length; i++) {
                    if (zhan_arr[i] == hname) {
                        zhan_arr.splice(i, 1);
                    }
                }
            }
        }

        var zhan = zhan_arr.join("|");
        set_cookie("main_list_all_zhan", zhan, 99999999);

    }


    // type=1 展开 0 折叠
    function wee_zhan_and_zhe(type) {
        var a_title = type ? "展开" : "折叠";
        var tr = byid("come_list").getElementsByTagName("TR");
        for (var i = 0; i < tr.length; i++) {
            if (tr[i].id.substr(0, 6) == "hname_") {
                var hname = tr[i].id.substr(6);
                byid("zhan_" + hname).title = a_title;
                byid("zhan_" + hname).onclick();
            }
        }
    }

    // 鼠标移入
    function mi(o) {
        o.style.backgroundColor = "#ffe3d7";
    }
    </script>
</head>

<body style="padding:15px;">

    <div><?php echo $summary_info; ?></div>

    <div style="margin-top:20px;" class="h_select_op_area">
        <?php if (!is_array($hospital_ids) || count($hospital_ids) == 0) { ?>
        <!-- 未分配医院 -->
        没有为您分配医院，请联系上级管理人员处理。
        <?php } ?>

        <?php if (count($hospital_ids) == 1) { ?>
        <!-- 仅有一家医院 -->
        当前医院：<b><?php echo $hospital_list[$hid]["name"]; ?></b>&nbsp;&nbsp;<button class="button_new"
            onclick="set_index_module(<?php echo $hid; ?>)">首页定制</button>&nbsp;&nbsp;
        <?php if ($config["is_output"] || $debug_mode) { ?><button class="buttonb"
            onclick="self.location='patient_output_name.php';">导出病人</button>&nbsp;&nbsp;<?php } ?>
        <?php if ($config["show_ziliaoku"] || $debug_mode) { ?><button class="buttonb"
            onclick="self.location='ku_list.php';">资料库</button><?php } ?>
        <?php } ?>

        <?php if (count($hospital_ids) > 1) { ?>
        <!-- 多家医院 -->
        <b>切换医院：</b>
        <select name="_tohid_" id="hospital_id" class="combo"
            onchange="if (this.value!='-1') location='?_tohid_='+this.value+'&from=main'" style="width:216px;">
            <?php if ($show_list_all) { ?>
            <option value="all" style="color:gray">-查看统计数据-</option>
            <?php } else { ?>
            <option value="-1" style="color:gray">-请选择医院-</option>
            <?php } ?>

            <?php
					foreach ($options as $v) {
						echo '			<option value="' . $v[0] . '"' . ($v[0] == $hid ? ' selected' : '') . ($v[2] ? ' style="' . $v[2] . '"' : '') . '>' . $v[1] . ($v[0] == $hid ? ' *' : '') . '</option>' . "\r\n";
					}
					?>
        </select>&nbsp;
        <button class="button" onclick="hgo('up');">上</button>&nbsp;<button class="button"
            onclick="hgo('down');">下</button>&nbsp;&nbsp;
        <button onclick="self.location.reload();" class="button" title="点击刷新页面">刷新</button>&nbsp;&nbsp;
        <?php } ?>
    </div>

    <div id="come_list_area">

        <?php
		$_time = @file_get_contents("data/update_data.txt");
		$data_update_time = "未知";
		if ($_time > 0) {
			$data_update_time = date("Y.n.j H:i:s", $_time);
		}

		?>

        <?php $table_width = "950"; ?>

        <table width="<?php echo $table_width; ?>" cellpadding="0" cellspacing="0" class="absmiddle">
            <tr>
                <td align="left">
                    <span style="margin-left:14px;"><img src="image/wee_jia.gif" align="absmiddle"><a
                            href="javascript:;" onclick="wee_zhan_and_zhe(1);">全部展开</a></span>
                    <span style="margin-left:14px;"><img src="image/wee_jian.gif" align="absmiddle"><a
                            href="javascript:;" onclick="wee_zhan_and_zhe(0);">全部折叠</a></span>
                </td>
                <td align="right" class="wee_huizong_right">
                    <span style="margin-right:10px">本页为缓存数据，刷新周期10分钟 （最近更新 <?php echo $data_update_time; ?>）</span>
                    <?php if ($debug_mode) { ?>
                    <span onclick="update_come_data(this);" title="一般情况无需手工更新"
                        style="color:silver; cursor:pointer; margin-left:10px; margin-right:10px">手工更新</span>
                    <?php } ?>
                </td>
            </tr>
        </table>

        <style type="text/css">
        .come_head,
        .come_head td {
            border-top: 0 !important;
        }

        .come_head td {
            padding-top: 2px;
            padding-bottom: 6px;
        }

        .list_all_huizong {
            background: #f2f8f9;
        }

        .list_all_huizong,
        .list_all_huizong td {
            border-bottom: 0 !important;
        }

        .list_all_huizong td {
            padding-top: 4px;
            padding-bottom: 2px;
            color: red;
        }
        </style>


        <div
            style="width:<?php echo $table_width; ?>px; margin-top:10px; border-radius:5px; border:2px solid #93bde3; padding:0px; background:#f2f8f9; ">
            <input type="hidden" id="come_list:width" value="100%" />
            <input type="hidden" id="come_list:class" value="px2_top,px2_bottom" />
            <table id="come_list" class="come_list" cellpadding="0" cellspacing="0" width="100%"
                style="margin-top:2px; margin-bottom:2px; ">
                <tr class="come_head">
                    <td class="al red">医院</td>
                    <td class="ac" width="7%">今日</td>
                    <td class="ac" width="7%">昨日</td>
                    <td class="ac" width="7%">本月</td>
                    <td class="ac" width="7%">同比</td>
                    <td class="ac" width="7%">上月</td>
                    <td class="ac" width="7%">网络今日</td>
                    <td class="ac" width="7%">网络本月</td>
                    <td class="ac" width="7%">电话今日</td>
                    <td class="ac" width="7%">电话本月</td>
                    <td class="ac" width="7%">今日预约</td>
                    <td class="ac" width="7%">网络今约</td>
                </tr>

                <?php
				$count1 = $count2 = 0;
				$huizong = array();
				foreach ($hid_percent2 as $_hname => $_per) {
					$line = $h_data[$_hname];
					$hebing_name = implode("、", $h_name_arr[$_hname]);
					$count1 += $line["x3"];
					$count2 += $line["x4"];

					foreach ($line as $k => $v) {
						$huizong[$k] += $v;
					}

					$cur_hids = $h_ids_arr[$_hname];

				?>
                <tr id="hname_<?php echo $_hname; ?>" onmouseover="mi(this)" onmouseout="mo(this)"
                    class="come_line hebing">
                    <td class="al" title="合并科室为：<?php echo $hebing_name; ?>"><b><?php echo $_hname; ?></b>
                        <font color="gray">(<?php echo count($h_name_arr[$_hname]); ?>)</font> <span
                            id="zhan_<?php echo $_hname; ?>"
                            onclick="h_zhankai('<?php echo $_hname; ?>', '<?php echo implode(",", $cur_hids); ?>', this)"
                            title="展开" style="cursor:pointer;"><img src="image/wee_jia.gif" align="absmiddle"></span>
                    </td>
                    <td class="ac" title="今日已到"><?php echo $line["x1"]; ?></td>
                    <td class="ac" title="昨日已到"><?php echo $line["x2"]; ?></td>
                    <td class="ac" title="本月已到 同比增长：<?php echo $_per; ?>人">
                        <?php echo $line["x3"] . ($line["x3"] == $line["x4"] ? '' : ($line["x3"] > $line["x4"] ? ' <img src="image/yeji_up.gif" align="absmiddle">' : ' <img src="image/yeji_down.gif" align="absmiddle">')); ?>
                    </td>
                    <td class="ac" title="同比已到"><?php echo $line["x4"]; ?></td>
                    <td class="ac" title="上月已到"><?php echo $line["x5"]; ?></td>
                    <td class="ac" title="网络今日已到"><?php echo $line["x6"]; ?></td>
                    <td class="ac" title="网络本月已到"><?php echo $line["x7"]; ?></td>
                    <td class="ac" title="电话今日已到"><?php echo $line["x8"]; ?></td>
                    <td class="ac" title="电话本月已到"><?php echo $line["x9"]; ?></td>
                    <td class="ac" title="今日预约"><?php echo $line["x10"]; ?></td>
                    <td class="ac" title="网络今约"><?php echo $line["x11"]; ?></td>
                </tr>

                <?php

					foreach ($cur_hids as $_hid) {
						if (!@array_key_exists($_hid, $hospital_list)) continue;
						$_per = $hid_percent[$_hid];
						$_li = $hospital_list[$_hid];
						$line = $hid_data_arr[$_hid];
					?>
                <tr id="hid_<?php echo $_hid; ?>" style="display:none" onmouseover="mi(this)" onmouseout="mo(this)"
                    class="come_line" style="color:<?php echo $_li["color"]; ?>">
                    <td class="al">　　<a href="?_tohid_=<?php echo $_hid; ?>" style="color:<?php echo $_li["color"]; ?>"
                            title="点击切换到此医院"><?php echo $_li["name"]; ?></a></td>
                    <td class="ac" title="今日已到"><?php echo $line["x1"]; ?></td>
                    <td class="ac" title="昨日已到"><?php echo $line["x2"]; ?></td>
                    <td class="ac" title="本月已到 同比增长：<?php echo $_per; ?>人">
                        <?php echo $line["x3"] . ($line["x3"] == $line["x4"] ? '' : ($line["x3"] > $line["x4"] ? ' <img src="image/yeji_up.gif" align="absmiddle">' : ' <img src="image/yeji_down.gif" align="absmiddle">')); ?>
                    </td>
                    <td class="ac" title="同比已到"><?php echo $line["x4"]; ?></td>
                    <td class="ac" title="上月已到"><?php echo $line["x5"]; ?></td>
                    <td class="ac" title="网络今日已到"><?php echo $line["x6"]; ?></td>
                    <td class="ac" title="网络本月已到"><?php echo $line["x7"]; ?></td>
                    <td class="ac" title="电话今日已到"><?php echo $line["x8"]; ?></td>
                    <td class="ac" title="电话本月已到"><?php echo $line["x9"]; ?></td>
                    <td class="ac" title="今日预约"><?php echo $line["x10"]; ?></td>
                    <td class="ac" title="网络今约"><?php echo $line["x11"]; ?></td>
                </tr>
                <?php     } ?>
                <?php } ?>


                <tr id="huizong" class="list_all_huizong" onmouseover="mi(this)" onmouseout="mo(this)">
                    <td class="al red"><b>所有科室汇总</b>
                        <font color="gray">(<?php echo count($h_arr); ?>)</font>
                    </td>
                    <td class="ac"><?php echo $huizong["x1"]; ?></td>
                    <td class="ac"><?php echo $huizong["x2"]; ?></td>
                    <td class="ac"><?php echo $huizong["x3"]; ?></td>
                    <td class="ac"><?php echo $huizong["x4"]; ?></td>
                    <td class="ac"><?php echo $huizong["x5"]; ?></td>
                    <td class="ac"><?php echo $huizong["x6"]; ?></td>
                    <td class="ac"><?php echo $huizong["x7"]; ?></td>
                    <td class="ac"><?php echo $huizong["x8"]; ?></td>
                    <td class="ac"><?php echo $huizong["x9"]; ?></td>
                    <td class="ac"><?php echo $huizong["x10"]; ?></td>
                    <td class="ac"><?php echo $huizong["x11"]; ?></td>
                </tr>

            </table>
        </div>


        <div
            style="margin-top:20px; width:<?php echo $table_width; ?>px; border-radius:5px; border:2px solid #93bde3; padding:0px; background:#f2f8f9; ">
            <input type="hidden" id="come_list2:width" value="100%" />
            <input type="hidden" id="come_list2:class" value="px2_top,px2_bottom" />
            <table id="come_list2" class="come_list" cellpadding="0" cellspacing="0" width="100%"
                style="margin-top:2px; margin-bottom:2px; ">
                <tr class="come_head">
                    <td class="al">
                        <font color="red">资料库数据统计</font>
                        　<?php echo ($_GET["zlk_month"] != "sy") ? '<a href="?zlk_month=sy" title="点此切换到上月数据">看上月</a>' : '<a href="?zlk_month=by" title="点此切换到本月数据">看本月</a>'; ?>
                    </td>
                    <td class="ac" width="7.7%" title="商务通有效未预约" style="cursor:help">有效未约</td>
                    <td class="ac" width="7.7%">增加人数</td>
                    <td class="ac" width="7.7%" title="含预约人数" style="cursor:help">微信增加</td>
                    <td class="ac" width="7.7%" title="含预约人数" style="cursor:help">QQ增加</td>
                    <td class="ac" width="7.7%">电话跟踪</td>
                    <td class="ac" width="7.7%">微信跟踪</td>
                    <td class="ac" width="7.7%">QQ跟踪</td>
                    <td class="ac" width="7.7%">短信跟踪</td>
                    <td class="ac" width="7.7%">转约人数</td>
                    <td class="ac" width="7.7%">到诊人数</td>
                </tr>

                <?php
				function _wds($str)
				{
					return $str == 0 ? "" : $str;
				}

				$ku_all_data = $db->query("select * from index_cache_ku", "h_name", "data");
				$all_hospital_list = $db->query("select sname, count(sname) as c from hospital where ishide=0 and id in ($hids) group by sname order by sort desc, sname asc", "sname", "c");

				$sum = array();
				foreach ($all_hospital_list as $hname => $hcount) {
					if ($hname == "上海华美医院") continue;
					$data_str = $ku_all_data[$hname];
					$data_arr = @unserialize($data_str);

					$dt_name = $_GET["zlk_month"] != "sy" ? "本月" : "上月";
					$_arr = $data_arr[$dt_name];

					foreach ($_arr as $_name => $_value) {
						$sum[$_name] += $_value;
					}
				?>

                <tr onmouseover="mi(this)" onmouseout="mo(this)" class="come_line">
                    <td class="al"><b><?php echo $hname; ?></b>　<?php echo $dt_name; ?></font>
                    </td>
                    <td class="ac"><?php echo _wds($_arr["有效未约"]); ?></td>
                    <td class="ac"><?php echo _wds($_arr["增加人数"]); ?></td>
                    <td class="ac"><?php echo _wds($_arr["微信增加"]); ?></td>
                    <td class="ac"><?php echo _wds($_arr["QQ增加"]); ?></td>
                    <td class="ac"><?php echo _wds($_arr["电话跟踪"]); ?></td>
                    <td class="ac"><?php echo _wds($_arr["微信跟踪"]); ?></td>
                    <td class="ac"><?php echo _wds($_arr["QQ跟踪"]); ?></td>
                    <td class="ac"><?php echo _wds($_arr["短信跟踪"]); ?></td>
                    <td class="ac"><?php echo _wds($_arr["转约人数"]); ?></td>
                    <td class="ac"><?php echo _wds($_arr["到诊人数"]); ?></td>
                </tr>
                <?php if ($_GET["zlk_month"] != "sy") { ?>
                <?php $_arr = $data_arr["昨日"]; ?>
                <tr onmouseover="mi(this)" onmouseout="mo(this)" class="come_line">
                    <td class="al">　　　　　昨日</td>
                    <td class="ac"><?php echo _wds($_arr["有效未约"]); ?></td>
                    <td class="ac"><?php echo _wds($_arr["增加人数"]); ?></td>
                    <td class="ac"><?php echo _wds($_arr["微信增加"]); ?></td>
                    <td class="ac"><?php echo _wds($_arr["QQ增加"]); ?></td>
                    <td class="ac"><?php echo _wds($_arr["电话跟踪"]); ?></td>
                    <td class="ac"><?php echo _wds($_arr["微信跟踪"]); ?></td>
                    <td class="ac"><?php echo _wds($_arr["QQ跟踪"]); ?></td>
                    <td class="ac"><?php echo _wds($_arr["短信跟踪"]); ?></td>
                    <td class="ac"><?php echo _wds($_arr["转约人数"]); ?></td>
                    <td class="ac"><?php echo _wds($_arr["到诊人数"]); ?></td>
                </tr>

                <?php $_arr = $data_arr["今日"]; ?>
                <tr onmouseover="mi(this)" onmouseout="mo(this)" class="come_line">
                    <td class="al">　　　　　今日</td>
                    <td class="ac"><?php echo _wds($_arr["有效未约"]); ?></td>
                    <td class="ac"><?php echo _wds($_arr["增加人数"]); ?></td>
                    <td class="ac"><?php echo _wds($_arr["微信增加"]); ?></td>
                    <td class="ac"><?php echo _wds($_arr["QQ增加"]); ?></td>
                    <td class="ac"><?php echo _wds($_arr["电话跟踪"]); ?></td>
                    <td class="ac"><?php echo _wds($_arr["微信跟踪"]); ?></td>
                    <td class="ac"><?php echo _wds($_arr["QQ跟踪"]); ?></td>
                    <td class="ac"><?php echo _wds($_arr["短信跟踪"]); ?></td>
                    <td class="ac"><?php echo _wds($_arr["转约人数"]); ?></td>
                    <td class="ac"><?php echo _wds($_arr["到诊人数"]); ?></td>
                </tr>
                <?php   } ?>

                <?php } ?>


                <tr class="list_all_huizong" onmouseover="mi(this)" onmouseout="mo(this)">
                    <td class="al red"><b>合计</b></td>
                    <td class="ac"><?php echo _wds($sum["有效未约"]); ?></td>
                    <td class="ac"><?php echo _wds($sum["增加人数"]); ?></td>
                    <td class="ac"><?php echo _wds($sum["微信增加"]); ?></td>
                    <td class="ac"><?php echo _wds($sum["QQ增加"]); ?></td>
                    <td class="ac"><?php echo _wds($sum["电话跟踪"]); ?></td>
                    <td class="ac"><?php echo _wds($sum["微信跟踪"]); ?></td>
                    <td class="ac"><?php echo _wds($sum["QQ跟踪"]); ?></td>
                    <td class="ac"><?php echo _wds($sum["短信跟踪"]); ?></td>
                    <td class="ac"><?php echo _wds($sum["转约人数"]); ?></td>
                    <td class="ac"><?php echo _wds($sum["到诊人数"]); ?></td>
                </tr>

            </table>
        </div>


    </div>

    <div style="margin-top:10px; margin-left:5px; color:silver">
        <font class="yuan" style="color:silver">●</font>
        页面执行时间：<?php echo round(now() - $pagebegintime, 4); ?>s，<?php echo $db->select_count; ?>次查询
        <?php echo "　　本月总到=" . $count1 . "　同比总到=" . $count2 . "(去年同月数据)　增量=" . ($count1 - $count2); ?>
    </div>

    <br>
    <br>

    <script type="text/javascript">
    var zhan = get_cookie("main_list_all_zhan");
    if (zhan != '') {
        var zhan_arr = zhan.split("|");
        for (var i = 0; i < zhan_arr.length; i++) {
            if (byid("zhan_" + zhan_arr[i])) {
                byid("zhan_" + zhan_arr[i]).onclick();
            }
        }
    }
    </script>

</body>

</html>