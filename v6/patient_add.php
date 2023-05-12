<?php
/*
// 说明 : 新增病人资料
// 作者 : 幽兰 QQ 934834734
// 时间 : 2011-09-19
*/
require "lib/set_env.php";
$table = "patient_" . $hid;

if ($hid == 0) {
	exit_html("对不起，没有选择医院，请先在首页选择医院！");
}

$hinfo = $db->query("select * from hospital where id=$hid limit 1", 1);

if (!in_array("patient_add", $gGuaHaoConfig)) {
	exit_html("对不起，您没有新增权限!");
}

if ($_POST) {

	// 坚决防止重复提交情况:
	$this_data_md5 = @md5(serialize($_GET) . serialize($_POST));
	if ($_SESSION["last_post_data_md5"] == $this_data_md5) {
		exit_html("请勿重复提交数据...");
	} else {
		$_SESSION["last_post_data_md5"] = $this_data_md5;
	}

	// 检查一个月内的病人中有无重复的:
	$name = trim($_POST["name"]);
	$tel = trim($_POST["tel"]);

	// 处理电话号码：
	$tel = str_replace("o", "0", $tel);
	$tel = str_replace("O", "0", $tel);
	// 将大写数字转换为小写:
	$char_arr = explode(" ", "０ １ ２ ３ ４ ５ ６ ７ ８ ９");
	foreach ($char_arr as $k => $v) {
		$tel = str_replace($v, $k, $tel);
	}
	//过滤不是数字的字符:
	$shuzi_arr = explode(" ", "0 1 2 3 4 5 6 7 8 9");
	$tel_lens = strlen($tel);
	$new_tel = '';
	for ($i = 0; $i < $tel_lens; $i++) {
		if (in_array($tel[$i], $shuzi_arr)) {
			$new_tel .= $tel[$i];
		}
	}
	$tel = $new_tel;

	// 电话号码重复检查
	$_hinfo = $db->query("select * from hospital where id=$hid limit 1", 1);
	if ($_hinfo["repeat_open"]) {
		$deny_days = $_hinfo["repeat_deny_time"] > 0 ? $_hinfo["repeat_deny_time"] : $cfgRepeatDenyDays;
		$deny_sec = $deny_days * 24 * 3600; //秒数
		// 重复检查:
		if ($deny_days > 0 && strlen($tel) >= 7) {
			$time = time();
			$count = $db->query("select count(*) as c from $table where tel='$tel' and abs({$time}-addtime)<{$deny_sec}", 1, "c");
			if ($count > 0) {
				exit_html("提交失败：电话号码“{$tel}”重复{$count}次。(系统设置" . $deny_days . "天内号码不允许重复)");
			} else {
				// 搜索其他同名医院:
				$sname = trim($hinfo["sname"]);
				$same_h_id_name = $db->query("select id, name from hospital where sname='$sname' and ishide=0 and id!=$hid order by name asc", "id", "name");
				if (count($same_h_id_name) > 0) {
					foreach ($same_h_id_name as $_hid => $_hname) {
						$count = $db->query("select count(*) as c from patient_{$_hid} where tel='$tel' and abs({$time}-addtime)<{$deny_sec}", 1, "c");
						if ($count > 0) {
							exit_html("提交失败：电话号码“{$tel}”已存在于“{$_hname}”。(系统设置" . $deny_days . "天内号码不允许重复)");
						}
					}
				}
			}
		}
	}


	// 是否为复诊的判断 @ 2016-6-3
	$tel_is_fuzhen = 0;
	if (strlen($tel) == 11) { //不是11位手机号不判断
		$sname = trim($hinfo["sname"]);
		$_h_arr = $db->query("select * from hospital where sname='$sname' and ishide=0", "id");
		foreach ($_h_arr as $_hid => $_h) {
			$count = $db->query("select count(*) as c from patient_{$_hid} where tel='$tel' and status=1", 1, "c");
			if ($count > 0) {
				$tel_is_fuzhen = 1;
				break;
			}
		}
	}

	$r = array();

	// 新增预约号功能 @ 2015-8-4
	$yuyue_num = intval($_POST["submit_yuyue_num"]);
	if ($yuyue_num > 0) {
		$r["yuyue_num"] = $yuyue_num;
		$db->query("insert into yuyue_num_rand set yuyue_num=$yuyue_num, addtime=$time, uid=$uid");
	}

	$r["weixin"] = trim($_POST["weixin"]);
	$r["order_weixin"] = trim($_POST["order_weixin"]);
	$r["qq"] = trim($_POST["qq"]);
	$r["order_qq"] = trim($_POST["order_qq"]);

	$r["order_type"] = intval($_POST["order_type"]);
	$r["order_soft"] = $_POST["order_soft"]; //网络预约才有选择软件功能
	$r["swt_id"] = str_replace("'", "", trim($_POST["swt_id"]));
	$r["name"] = trim($_POST["name"]);
	$r["sex"] = $_POST["sex"];
	$r["age"] = $_POST["age"];
	$r["tel"] = $tel;
	$r["is_fuzhen"] = $tel_is_fuzhen; //@2016-6-3
	$r["youhuazu"] = $_POST["youhua_group"];

	if (strlen($r["tel"]) == 11) {
		$r["tel_location"] = @get_mobile_location($r["tel"]);
	}

	$r["content"] = $_POST["content"];
	$r["talk_content"] = $_POST["talk_content"];

	$r["disease_id"] = $_POST["disease_id"];
	if ($_POST["disease_2_submit"]) {
		$r["disease_2"] = @implode(",", $_POST["disease_2"]);
	}
	$r["depart"] = $_POST["depart"];
	$r["media_from"] = $_POST["media_from"];

	// 电话来源处理 @ 2012-12-12
	if ($_POST["media_from"] == "电话") {
		$r["tel_from"] = $_POST["tel_from"];
	} else {
		$r["tel_from"] = "";
	}

	// QQ来源处理 @ 2012-12-11
	if ($_POST["media_from"] == "QQ") {
		$r["qq_from"] = $_POST["qq_from"];
	} else {
		$r["qq_from"] = "";
	}


	// 市场来源:
	$r["shichang"] = $_POST["shichang"];

	// 咨询分组：
	$r["zx_group"] = $_POST["zx_group"];

	$r["account"] = $_POST["account"];
	$r["zhuanjia_num"] = $_POST["zhuanjia_num"];
	$r["wish_doctor"] = $_POST["wish_doctor"];
	$r["zhusu"] = $_POST["zhusu"];

	$r["suozaidi"] = intval($_POST["suozaidi"]); //2017-02-18

	// 2016-3-29 身份证编号
	$r["card_id"] = trim($_POST["card_id"]);

	// 推广人
	$r["tuiguangren"] = trim($_POST["tuiguangren"]);

	// 预约时间:
	if ($uinfo["part_id"] == 4) {
		$r["order_date"] = time(); //导医添加 直接为当前时间
		if ($_POST["daoyi_status"] == 9) {
			$r["status"] = 0;
		} else {
			$r["status"] = 1;
		}
	} else {
		if (trim($_POST["order_date"]) == '') {
			exit_html("预约时间必须填写，不填写不能提交。");
		}
		$r["order_date"] = strtotime($_POST["order_date"] . " " . $_POST["order_time"]);
	}

	if ($_POST["memo"]) {
		$r["memo"] = date("Y-m-d H:i ") . $realname . ": " . $_POST["memo"];
	}

	$r["addtime"] = time();

	if ($_POST["from_table"] != "") {
		$r["from_table"] = wee_safe_key(strip_tags($_POST["from_table"]));
		$r["from_id"] = intval($_POST["from_id"]);
	}

	if ($_POST["from"] == 'ku' || $_POST["from"] == "xinmeiti") {
		if ($_POST["from_part_id"] > 0) {
			$r["part_id"] = intval($_POST["from_part_id"]);
		} else {
			$r["part_id"] = $uinfo["part_id"];
		}
	} else {
		if ($_POST["submit_part"] > 0) {
			$r["part_id"] = intval($_POST["submit_part_id"]);
		} else {
			$r["part_id"] = $uinfo["part_id"];
		}
	}

	if ($_POST["from_uid"] > 0) {
		$r["uid"] = intval($_POST["from_uid"]);
	} else {
		$r["uid"] = $uid;
	}

	if ($_POST["from_author"] != '') {
		$r["author"] = $_POST["from_author"];
	} else {
		$r["author"] = $realname;
	}

	$sqldata = $db->sqljoin($r);
	$sql = "insert into $table set $sqldata";

	ob_start();
	$return = $db->query($sql);
	$error = ob_get_clean();

	//user_op_log("添加病人[".$r["name"]."]");

	if ($return && empty($error)) {

		// 添加回访提醒
		if (trim($_POST["huifang_nexttime"]) != '') {
			$remind_date = intval(str_replace("-", "", $_POST["huifang_nexttime"]));
			$time = time();
			$db->query("insert into patient_remind set hid=$hid, patient_id=$return, patient_name='" . $r["name"] . "', remind_date='$remind_date', uid=$uid, u_name='$realname', addtime=$time, flag=1 ");
		}


		// 更新于 @ 2016-10-19
		if ($_POST["from_table"] != "") {
			$ftable = wee_safe_key(strip_tags($_POST["from_table"]));
			$fid = intval($_POST["from_id"]);
			$db->query("update `$ftable` set is_yuyue=1 where id=$fid limit 1");

			// 导医转入的 将资料库标红
			if ($uinfo["part_id"] == 4) {
				$db->query("update `$ftable` set is_come=1 where id=$fid limit 1");
			}
		}



		// 更新资料库状态 @ 2014-01-08
		if ($_POST["from"] == "ku") {
			$ku_id = intval($_POST["ku_id"]);
			// 更新回访内容到患者预约资料中 @ 2016-07-26
			$hf_log = $db->query("select hf_log from ku_list where id=$ku_id limit 1", 1, "hf_log");
			if ($hf_log != "") {
				$db->query("update $table set huifang='$hf_log' where id='$return' limit 1");
			}
		}


		echo '<script type="text/javascript">' . "\r\n";
		echo 'parent.load_box(0);' . "\r\n";
		echo 'parent.msg_box("添加成功");' . "\r\n";
		echo 'parent.update_content();' . "\r\n";

		// 同步结果给蔡俊强
		/*
		if (strlen(trim($r["tel"])) == 11) {
			$_dt = date("YmdHis", $r["order_date"]);
			$_st = $r["status"] == 1 ? 3 : 2;
			echo 'parent.sync_cai("'.$hid.'", "'.trim($r["tel"]).'", "'.$_st.'", "'.$_dt.'");';
		}
		*/

		echo '</script>' . "\r\n";
		exit;
	} else {
		echo "资料提交出错，请联系开发人员解决: <br><br>";
		echo $db->sql . "<br><br>";
		echo $error . "<br><br>";
		exit;
	}
}


//$hospital_list = $db->query("select id,name from hospital");
$disease_id_name = $db->query("select id,name from disease where hospital_id='$hid' and isshow=1 order by sort desc,id asc", "id", "name");
$disease_2_name = $db->query("select id,disease_2 from disease where hospital_id='$hid' and isshow=1", "id", "disease_2");
$doctor_list = $db->query("select id,name from doctor where hospital_id='$hid' order by name asc");
$wish_doctor_array = $db->query("select name from doctor where hospital_id=$hid order by name asc", "", "name");
$part_id_name = $db->query("select id,name from sys_part", "id", "name");
$depart_list = $db->query("select id,name from depart where hospital_id='$hid'", "id", "name");
$engine_list = $db->query("select id,name from engine", "id", "name");
$qq_from_arr = $db->query("select id,name from qq_from order by sort desc,id asc", "id", "name");
$tel_from_arr = $db->query("select id,name from tel_from order by sort desc,id asc", "id", "name");
$xianchang_doctor = $db->query("select id,realname from sys_admin where part_id=14 and concat(',',hospitals,',') like '%,{$hid},%'", "id", "realname");

$status_array = array(
	array("id" => 0, "name" => '等待'),
	array("id" => 1, "name" => '已到'),
	array("id" => 2, "name" => '未到'),
);

$xiaofei_array = array(
	array("id" => 0, "name" => '未消费'),
	array("id" => 1, "name" => '已消费'),
);

$media_from_array = $media_often_arr = explode(" ", "网络 电话");
$media_often_2 = $db->query("select name from media where (hospital_id=0 or hospital_id=$hid) and is_often=1 order by sort desc,addtime asc", "", "name");
foreach ($media_often_2 as $v) {
	$media_often_arr[] = $v;
}
$media_2 = $db->query("select name from media where (hospital_id=0 or hospital_id=$hid) order by sort desc,addtime asc", "", "name");
foreach ($media_2 as $v) {
	$media_from_array[] = $v;
}
// 删除出现在常用媒体中的项:
foreach ($media_from_array as $k => $v) {
	if (in_array($v, $media_often_arr)) {
		unset($media_from_array[$k]);
	}
}

$engine_array = $db->query("select name from engine order by name asc", "", "name");

function guess_yuyue_card_date($s)
{
	if (empty($s)) {
		return '';
	}

	if (!@preg_match("/[0-9]{4}/", $s)) {
		$s = date("Y-") . $s;
	}

	$s = str_replace("年", "-", $s);
	$s = str_replace("月", "-", $s);
	$s = str_replace("日", " ", $s);
	$s = str_replace(".", "-", $s);

	if (substr_count($s, "上午") > 0) {
		$s = str_replace("上午", "", $s);
		$s .= " 09:00:00";
	} else if (substr_count($s, "下午") > 0) {
		$s = str_replace("下午", "", $s);
		$s .= " 14:00:00";
	}

	$int = strtotime($s);
	if ($int) {
		return @date("Y-m-d H:i:s", $int);
	} else {
		return "";
	}
}

//user_op_log("打开添加病人页面");

?>
<html>

<head>
    <title>添加患者 - <?php echo $hinfo["name"]; ?></title>
    <meta http-equiv="Content-Type" content="text/html;charset=gb2312">
    <link href="lib/base.css" rel="stylesheet" type="text/css">
    <script src="lib/base.js" language="javascript"></script>
    <script src="lib/wee_time.js" language="javascript"></script>
    <script src="lib/datejs/picker.js" language="javascript"></script>
    <style type="text/css">
    legend {
        font-size: 13px;
        color: #0000ff;
        font-family: "微软雅黑";
    }

    .wee_class {
        border: 1px solid #d7d7d7;
        background: #f7f7f7;
        padding: 1px 5px 0px 5px;
        margin-right: 5px;
        color: #000000;
    }

    .wee_class:hover {
        border: 1px solid #ffa477;
        background: #fff5ec;
        margin-right: 5px;
    }

    .wee_class_select {
        border: 1px solid #ff0000;
        background: #ffeeee;
        padding: 1px 5px 0px 5px;
        margin-right: 5px;
        color: #8000ff;
    }

    .wee_class_select:hover {
        border: 1px solid #ff0000;
        background: #ffdddd;
        margin-right: 5px;
    }

    .p_add {
        border: 0;
    }

    .p_add td {
        padding: 3px 6px;
    }

    .p_add .left {
        color: #ff8000;
        text-align: right;
        width: 15%;
    }

    .p_add .right {
        text-align: left;
    }

    #order_info {
        margin-top: 10px;
    }
    </style>
    <script language="javascript">
    var user_part_id = "<?php echo $uinfo["part_id"]; ?>";

    function check_data(oForm) {
        if (oForm.name.value == "") {
            alert("请输入“病人姓名”        ");
            oForm.name.focus();
            return false;
        }
        if (oForm.tel.value != "" && get_num(oForm.tel.value) == '') {
            alert("请正确输入“电话”        ");
            oForm.tel.focus();
            return false;
        }
        if (byid("suozaidi").value == "") {
            alert("请选择“地区” (本地或外地)     ");
            return false;
        }
        if (oForm.weixin.value != '' && oForm.order_weixin.value == "") {
            alert("当填写了“患者微信”时，“我方微信”也必须填写。");
            oForm.order_weixin.focus();
            return false;
        }
        if (oForm.qq.value != '' && oForm.order_qq.value == "") {
            alert("当填写了“患者QQ”时，“我方QQ”也必须填写。");
            oForm.order_qq.focus();
            return false;
        }
        if (user_part_id == "2" && oForm.order_soft.value == "") {
            alert("请选择“预约软件”         ");
            return false;
        }
        if (byid("set_swt_id_tr").style.display != "none" && byid("swt_id").value == '') {
            //alert("商务通永久身份为必填项，请从商务通复制。"); return false;
        }
        if (oForm.media_from.value == "") {
            alert("请选择“媒体来源”         ");
            return false;
        }
        if (oForm.disease_id.options.length > 1 && oForm.disease_id.value == "0") {
            alert("请选择“疾病类型”         ");
            return false;
        }
        if (oForm.order_date.value.length < 10) {
            alert("请正确填写“预约时间”     ");
            oForm.order_date.focus();
            return false;
        }
        return true;
    }

    function input(id, value) {
        if (byid(id).disabled != true) {
            byid(id).value = value;
        }
    }

    // 检查数据重复:
    function check_repeat(type, obj) {
        if (!byid("id") || (byid("id").value == '0' || byid("id").value == '')) {
            var value = obj.value;
            if (value != '') {
                var xm = new ajax();
                xm.connect("http/check_repeat.php?type=" + type + "&value=" + value + "&r=" + Math.random(), "GET", "",
                    check_repeat_do);
            }
        }
    }

    function check_repeat_do(o) {
        var out = ajax_out(o);
        if (out["status"] == "ok") {
            if (out["tips"] != '') {
                alert(out["tips"]);
            }
        }
    }

    function check_tel_submit(o) {
        var tel = byid("tel").value;
        if (tel != '') {
            var xm = new ajax();
            xm.connect("http/check_tel_submit.php?tel=" + tel + "&r=" + Math.random(), "GET", "", check_tel_submit_do);
        } else {
            alert("您还没有填写手机号码。");
        }
    }

    function check_tel_submit_do(o) {
        var out = ajax_out(o);
        if (out["status"] == "ok") {
            if (out["tips"] != '') {
                alert(out["tips"]);
            }
        }
    }

    function in_array(find, arr) {
        for (i = 0; i < arr.length; i++) {
            if (arr[i] == find)
                return true;
        }
        return false;
    }

    function show_disease_2(disease_id) {
        var s = '';
        var default_disease_id = byid("default_disease_id").value;
        var cur_2 = byid("disease_2_old").value.split(",");
        var o = byid("disease_2_" + disease_id);
        if (o && o.value != '' && o.title != '') {
            var d1 = o.title;
            var d2s = o.value.split(" ");

            // 如果是默认id，则把未知的二级疾病也放到选项中，如果选择新的疾病，旧的未知二级疾病就丢弃
            if (disease_id == default_disease_id) {
                for (var i = 0; i < cur_2.length; i++) {
                    var cur_2_name = cur_2[i];
                    if (in_array(cur_2_name, d2s) == false) {
                        d2s[d2s.length] = cur_2_name;
                    }
                }
            }

            // 构建选项
            for (var i = 0; i < d2s.length; i++) {
                if (d2s[i] != '') {
                    var dis_2_name = d2s[i];
                    var sel = in_array(dis_2_name, cur_2);
                    s += '<input type="checkbox" name="disease_2[]" value="' + dis_2_name + '"' + (sel ? ' checked' :
                        "") + ' id="d2_' + dis_2_name + '"><label for="d2_' + dis_2_name + '">' + (sel ? (
                        '<font color=red>' + dis_2_name + '</font>') : dis_2_name) + '</label>&nbsp;';
                }
            }
        }

        byid("disease_2_box").innerHTML = s;
    }


    function get_location(obj) {
        var tel = byid("tel").value;
        if (tel.length = 11) {
            var xm = new ajax();
            xm.connect("http/get_mobile_location.php?m=" + tel + "&r=" + Math.random(), "GET", "", get_location_do);
        }
    }

    function get_location_do(o) {
        var out = ajax_out(o);
        byid("tel_location_show").innerHTML = '';
        if (out["status"] == "ok") {
            byid("tel_location_show").innerHTML = out["location"];
        }
    }

    function wee_set_select(obj, str, toset_id, torun_function) {
        byid(toset_id).value = str;
        var ls = obj.parentNode.getElementsByTagName("A");
        for (var i = 0; i < ls.length; i++) {
            ls[i].className = "wee_class";
        }
        obj.className = "wee_class_select";
        obj.blur();
        if (torun_function != undefined) {
            eval(torun_function + '();');
        }
    }


    function gen_yuyue_num() {
        load_js("http/gen_yuyue_num.php", "gen_yuyue_num");
    }

    function gen_yuyue_num_do(s) {
        byid("yuyue_num").innerHTML = s;
        byid("submit_yuyue_num").value = s;
        alert("不重复的预约号已经生成，请随当前患者资料提交一并提交，单独复制无效。");
    }
    </script>
</head>

<body>

    <!-- <div style="margin:5px 0 5px 0; text-align:center;"><b>填写说明：</b>请尽量详细填写下面的各项，并在提交前稍作检查。</div> -->

    <form name="mainform" action="" method="POST" onsubmit="return check_data(this)">
        <fieldset id="base_data">
            <legend>患者资料</legend>
            <table width="100%" class="p_add">
                <tr>
                    <td class="left">姓名：</td>
                    <td class="right"><input name="name" id="name" value="<?php echo $_GET["name"]; ?>" class="input"
                            style="width:150px" onchange="check_repeat('name', this)">
                        <span class="intro">姓名必须填写</span>

                        <span style="margin-left:40px;">预约号：
                            <span id="yuyue_num"></span>&nbsp;
                            <button onclick="gen_yuyue_num();return false;" class="buttonb"
                                title="将生成一个不重复的8位预约号">点此生成</button>
                            <input type="hidden" name="submit_yuyue_num" id="submit_yuyue_num" value="">
                        </span>
                    </td>
                </tr>

                <tr>
                    <td class="left">手机：</td>
                    <td class="right"><input name="tel" id="tel" value="<?php echo $_GET["tel"]; ?>" class="input"
                            style="width:150px"
                            onchange="daoyi_check(this); check_repeat('tel', this); get_location(this);">&nbsp;<span
                            id="tel_location_show"></span>&nbsp;<button onclick="check_tel_submit(this);return false;"
                            class="buttonb" title="检查所填写的号码是否可以提交">可否提交</button>&nbsp;<span
                            class="intro">请填写11位手机号码&nbsp;<font color="red">（注意：不能带标点符号，除数字以外的字符全部会被过滤掉）</font></span>
                    </td>
                </tr>

                <script type="text/javascript">
                function daoyi_check(o) {
                    <?php if ($uinfo["part_id"] == 4) { ?>
                    if (o.value.length >= 11) {
                        var tel = o.value;
                        var xm = new ajax();
                        xm.connect("http/check_ku_by_daoyi.php?tel=" + tel + "&r=" + Math.random(), "GET", "",
                            check_daoyi_do);
                    }
                    <?php } ?>
                }

                function check_daoyi_do(o) {
                    var out = ajax_out(o);
                    if (out["status"] == "ku_repeat") {
                        if (confirm("该手机号在资料库中已经有人添加过了，是否一键转入预约系统？（能省去你打好多字）")) {
                            self.location = out["url"];
                        } else {
                            if (confirm("亲，这样可能会影响别人的劳动成果呀。再次确认下是否要一键转入？")) {
                                self.location = out["url"];
                            } else {
                                alert("好吧，你还是要决定自己添加患者~");
                            }
                        }
                    }
                }
                </script>

                <tr>
                    <td class="left">患者微信：</td>
                    <td class="right"><input name="weixin" value="<?php echo $_GET["weixin"]; ?>" class="input"
                            style="width:100px">　我方微信：<input name="order_weixin"
                            value="<?php echo $_GET["order_weixin"]; ?>" class="input"
                            style="width:100px">　　　　患者QQ：<input name="qq" value="<?php echo $_GET["qq"]; ?>"
                            class="input" style="width:100px">　我方QQ：<input name="order_qq"
                            value="<?php echo $_GET["order_qq"]; ?>" class="input" style="width:100px"></td>
                </tr>

                <tr>
                    <td class="left">性别：</td>
                    <td class="right">
                        <span id="sex_area">
                            <?php foreach (array("男", "女") as $_name) { ?>
                            <a href="javascript:;" class="wee_class"
                                onclick="wee_set_select(this, '<?php echo $_name; ?>', 'sex'); return false;"><?php echo $_name; ?></a>
                            <?php } ?>
                            <a href="javascript:;" class="wee_class"
                                onclick="wee_set_select(this, '', 'sex'); return false;">未知</a>
                        </span>
                        <input type="hidden" name="sex" id="sex" value="">
                        <script type="text/javascript">
                        var sex = "<?php echo $_GET["sex"]; ?>";
                        var objs = byid("sex_area").getElementsByTagName("A");
                        if (sex) {
                            if (sex == "男") objs[0].onclick();
                            if (sex == "女") objs[1].onclick();
                        }
                        </script>
                    </td>
                </tr>

                <tr>
                    <td class="left">地区：</td>
                    <td class="right">
                        <a href="javascript:;" class="wee_class"
                            onclick="wee_set_select(this, '1', 'suozaidi'); return false;">本地</a>
                        <a href="javascript:;" class="wee_class"
                            onclick="wee_set_select(this, '2', 'suozaidi'); return false;">外地</a>
                        <a href="javascript:;" class="wee_class"
                            onclick="wee_set_select(this, '3', 'suozaidi'); return false;">贵阳</a>
                        <a href="javascript:;" class="wee_class"
                            onclick="wee_set_select(this, '4', 'suozaidi'); return false;">安顺</a>
                        <a href="javascript:;" class="wee_class"
                            onclick="wee_set_select(this, '5', 'suozaidi'); return false;">毕节</a>
                        <a href="javascript:;" class="wee_class"
                            onclick="wee_set_select(this, '6', 'suozaidi'); return false;">遵义</a>
                        <a href="javascript:;" class="wee_class"
                            onclick="wee_set_select(this, '7', 'suozaidi'); return false;">六盘水</a>
                        <a href="javascript:;" class="wee_class"
                            onclick="wee_set_select(this, '8', 'suozaidi'); return false;">黔南</a>
                        <a href="javascript:;" class="wee_class"
                            onclick="wee_set_select(this, '9', 'suozaidi'); return false;">黔西南</a>
                        <a href="javascript:;" class="wee_class"
                            onclick="wee_set_select(this, '10', 'suozaidi'); return false;">黔东南</a>
                        <a href="javascript:;" class="wee_class"
                            onclick="wee_set_select(this, '11', 'suozaidi'); return false;">铜仁</a>
                        <a href="javascript:;" class="wee_class"
                            onclick="wee_set_select(this, '12', 'suozaidi'); return false;">其它</a>
                        <input type="hidden" name="suozaidi" id="suozaidi" value="">
                    </td>
                </tr>

                <tr>
                    <td class="left">年龄：</td>
                    <td class="right"><input name="age" id="age" value="<?php echo $_GET["age"]; ?>" class="input"
                            style="width:80px"> <span class="intro">填写年龄</span></td>
                </tr>

                <!-- <tr>
		<td class="left">主诉：</td>
		<td class="right"><input name="zhusu" value="<?php echo $_GET["zhusu"]; ?>" class="input" style="width:60%"> <span class="intro">用于生成预约卡</span></td>
	</tr> -->

                <tr>
                    <td class="left">身份证号码：</td>
                    <td class="right"><input name="card_id" id="card_id" value="<?php echo $_GET["card_id"]; ?>"
                            class="input" style="width:250px"> <span class="intro">填写18位身份证号码</span></td>
                </tr>

                <tr>
                    <td class="left">咨询内容：</td>
                    <td class="right"><textarea name="content" style="width:60%; height:64px; vertical-align:middle;"
                            class="input"><?php echo $_GET["content"] ? $_GET["content"] : $hinfo["template"]; ?></textarea>
                        <span class="intro">请填写咨询总结，不要放聊天记录</span></td>
                </tr>

                <tr>
                    <td class="left">聊天记录：</td>
                    <td class="right">
                        <script type="text/javascript">
                        function talk_content_display() {
                            byid("talk_content_display").style.display = "none";
                            byid("talk_content_area").style.display = "";
                        }

                        function talk_content_hide() {
                            byid("talk_content_display").style.display = "";
                            byid("talk_content_area").style.display = "none";
                        }
                        </script>
                        <span id="talk_content_display"><a href="javascript:;"
                                onclick="talk_content_display();">[添加聊天记录]</a></span>
                        <span id="talk_content_area" style="display:none"><textarea name="talk_content"
                                style="width:60%; height:80px; vertical-align:middle;"
                                class="input"><?php if ($_GET["from"] == "ku") echo $_SESSION["ku_talk_content"]; ?></textarea>
                            <span class="intro">可复制聊天记录备查</span> <a href="javascript:;"
                                onclick="talk_content_hide();">[隐藏]</a></span>
                        <?php if ($_GET["from"] == "ku" && trim($_SESSION["ku_talk_content"]) != '') { ?>
                        <script type="text/javascript">
                        talk_content_display();
                        </script>
                        <?php } ?>
                    </td>
                </tr>

            </table>
        </fieldset>


        <fieldset id="order_info">
            <legend>预约信息</legend>
            <table width="100%" class="p_add">

                <?php if ($debug_mode || $uinfo["part_id"] == 2) { ?>
                <tr>
                    <td class="left">预约软件：</td>
                    <td class="right" style="padding:6px 6px;">
                        <span id="order_soft_area">
                            <?php foreach ($web_soft_arr as $_oid => $_oname) { ?>
                            <a href="javascript:;" class="wee_class"
                                onclick="wee_set_select(this, '<?php echo $_oid; ?>', 'order_soft', 'on_set_soft'); return false;"><?php echo $_oname; ?></a>
                            <?php } ?>
                        </span>
                        <input type="hidden" name="order_soft" id="order_soft" value="">
                        <script type="text/javascript">
                        function on_set_soft() {
                            var soft = byid("order_soft").value;
                            byid("set_swt_id_tr").style.display = (soft == "swt" ? "" : "none");
                        }
                        </script>
                    </td>
                </tr>
                <?php } ?>

                <tr id="set_swt_id_tr" style="display:none;">
                    <td class="left">商务通永久身份：</td>
                    <td class="right"><input name="swt_id" id="swt_id" value="<?php echo $_GET["swt_id"]; ?>"
                            class="input" style="width:250px"> <span
                            class="intro">请从商务通中复制，格式类似“148880449235476075818”</span></td>
                </tr>

                <tr>
                    <td class="left">媒体来源：</td>
                    <td class="right" style="padding:6px 6px;">
                        <span id="media_area">
                            <?php foreach ($media_often_arr as $_oname) { ?>
                            <a href="javascript:;" class="wee_class"
                                onclick="wee_set_select(this, '<?php echo $_oname; ?>', 'media_from', 'on_set_often_media'); return false;"><?php echo $_oname; ?></a>
                            <?php } ?>
                        </span>
                        <input type="hidden" name="media_from" id="media_from"
                            value="<?php echo $_GET["media_from"]; ?>">

                        <span>
                            <select id="more_media_select" class="combo"
                                onchange="set_combo_media(this); on_media_from_change()" style="margin:1px 5px 0 0;">
                                <option value="" style="color:gray">--更多媒体--</option>
                                <?php echo list_option($media_from_array, '_value_', '_value_'); ?>
                            </select>
                            <select name="qq_from" class="combo" id="qq_from" style="display:none;margin:1px 5px 0 0;">
                                <option value="" style="color:gray">--QQ来源--</option>
                                <?php echo list_option($qq_from_arr, '_value_', '_value_'); ?>
                            </select>
                            <select name="tel_from" class="combo" id="tel_from"
                                style="display:none;margin:1px 5px 0 0;">
                                <option value="" style="color:gray">--电话来源--</option>
                                <?php echo list_option($tel_from_arr, '_value_', '_value_'); ?>
                            </select>
                            <select name="shichang" class="combo" id="shichang"
                                style="display:none;margin:1px 5px 0 0;">
                                <option value="" style="color:gray">--市场来源--</option>
                                <?php echo list_option($shichang_arr, '_value_', '_value_'); ?>
                            </select>
                        </span>

                        <script type="text/javascript">
                        function on_set_often_media() {
                            byid("more_media_select").value = "";
                            on_media_from_change();
                        }

                        function set_combo_media(o) {
                            byid("media_from").value = o.value;
                            // 重置常用媒体的效果:
                            var objs = byid("media_area").getElementsByTagName("A");
                            for (var i = 0; i < objs.length; i++) {
                                objs[i].className = "wee_class";
                            }
                        }

                        function on_media_from_change() {
                            byid("qq_from").style.display = "none";
                            byid("tel_from").style.display = "none";
                            byid("shichang").style.display = "none";

                            if (byid("media_from").value == "QQ") {
                                byid("qq_from").style.display = "";
                            } else if (byid("media_from").value == "电话") {
                                byid("tel_from").style.display = "";
                            } else if (byid("media_from").value == "市场") {
                                byid("shichang").style.display = "";
                            }
                        }

                        on_media_from_change();
                        </script>
                    </td>
                </tr>

                <tr>
                    <td class="left">疾病类型：</td>
                    <td class="right">
                        <select name="disease_id" onchange="show_disease_2(this.value)" class="combo">
                            <option value="0" style="color:gray">--请选择--</option>
                            <?php echo list_option($disease_id_name, '_key_', '_value_', $_GET["disease_id"]); ?>
                        </select>&nbsp;
                        <span id="disease_2_box"></span>

                        <span style="display:none">
                            <?php foreach ($disease_2_name as $k => $v) { ?>
                            <input type="hidden" id="disease_2_<?php echo $k; ?>"
                                title="<?php echo $disease_id_name[$k]; ?>" value="<?php echo $v; ?>">
                            <?php } ?>
                            <input type="hidden" id="disease_2_old" value="">
                            <input type="hidden" id="default_disease_id" value="">
                            <input type="hidden" name="disease_2_submit" value="1">
                    </td>
                </tr>

                <tr>
                    <td class="left">就诊科室：</td>
                    <td class="right" style="padding:6px 6px;">
                        <?php if (count($depart_list) > 0) { ?>
                        <span>
                            <!-- <a href="javascript:;" class="wee_class" onclick="wee_set_select(this, '', 'depart'); return false;">无</a> -->
                            <?php foreach ($depart_list as $_oid => $_oname) { ?>
                            <a href="javascript:;" class="wee_class"
                                onclick="wee_set_select(this, '<?php echo $_oid; ?>', 'depart'); return false;"><?php echo $_oname; ?></a>
                            <?php } ?>
                        </span>
                        <?php } else { ?>
                        <span class="intro">（暂未添加科室，请联系您的主管添加）</span>
                        <?php } ?>
                        <input type="hidden" name="depart" id="depart" value="">
                    </td>
                </tr>

                <tr>
                    <td class="left">预约时间：</td>
                    <td class="right">
                        <?php if ($uinfo["part_id"] != 4) { ?>
                        日期：<input name="order_date" id="order_date" readonly title="请点击设置日期(不可直接输入)" value="<?php //echo guess_yuyue_card_date($_GET["order_date"]); 
																												?>" class="input" style="width:80px; cursor:help;" onchange="reload_doctor(this.value);"
                            onclick="picker({el:'order_date',dateFmt:'yyyy-MM-dd'})"> <img src="image/calendar.gif"
                            onclick="picker({el:'order_date',dateFmt:'yyyy-MM-dd'})" align="absmiddle"
                            style="cursor:pointer" title="选择日期"> &nbsp;
                        <?php
							$show_days = array(
								"今" => $today = date("Y-m-d"), //今天
								"明" => date("Y-m-d", strtotime("+1 day")), //明天
								"后" => date("Y-m-d", strtotime("+2 days")), //后天
								"大后天" => date("Y-m-d", strtotime("+3 days")), //大后天
								"周六" => date("Y-m-d", strtotime("next Saturday")), //周六
								"周日" => date("Y-m-d", strtotime("next Sunday")), // 周日
								"周一" => date("Y-m-d", strtotime("next Monday")), // 周一
								"一周后" => date("Y-m-d", strtotime("+7 days")), // 一周后
								"半月后" => date("Y-m-d", strtotime("+15 days")), //半个月后
							);
							foreach ($show_days as $name => $value) {
								echo '<a href="javascript:set_order_date(\'' . $value . '\'); byid(\'order_date\').onchange();">[' . $name . ']</a>&nbsp;';
							}
							?>
                        <div style="padding-top:3px;">
                            时间：<input name="order_time" readonly title="请点击设置时间(不可直接输入)" id="order_time" value=""
                                class="input" style="width:80px; cursor:help;"
                                onclick="wee_time_show_picker('order_time','left','top')"> <img src="image/calendar.gif"
                                onclick="wee_time_show_picker('order_time','left','top')" align="absmiddle"
                                style="cursor:pointer" title="选择时间"> &nbsp;
                            <a href="javascript:set_order_time('09:00')">[上午9点]</a>
                            <a href="javascript:set_order_time('14:00')">[下午2点]</a>
                            <a href="javascript:set_order_time('18:00')">[晚上6点]</a>
                            <a href="javascript:set_order_time('00:00')">[全天]</a>&nbsp; <font color="gray">
                                (时间不填则默认为“全天”)</font>
                            <script type="text/javascript">
                            function set_order_date(s) {
                                if (byid("order_date").disabled != true) {
                                    byid("order_date").value = s;
                                }
                            }

                            function set_order_time(s) {
                                if (byid("order_time").disabled != true) {
                                    byid("order_time").value = s;
                                }
                            }
                            </script>
                        </div>

                        <?php } else { ?>
                        (预约时间自动为当前时间)
                        <input type="hidden" name="order_date" value="<?php echo date("Y-m-d H:i:s"); ?>" />
                        <!-- 此时间提交也不处理，只作为js检查通过用 -->
                        <?php } ?>
                    </td>
                </tr>

                <tr>
                    <td class="left">回访时间：</td>
                    <td class="right"><input name="huifang_nexttime" value="" class="input" style="width:150px"
                            id="huifang_nexttime"> <img src="image/calendar.gif" id="huifang_nexttime"
                            onClick="picker({el:'huifang_nexttime',dateFmt:'yyyy-MM-dd'})" align="absmiddle"
                            style="cursor:pointer" title="选择时间"> <span class="intro">如果指定回访时间，到了该天会在首页提醒</span></td>
                </tr>

                <tr>
                    <td class="left">其他设置：</td>
                    <td class="right" style="padding:6px 6px;">
                        <select name="account" class="combo">
                            <option value="" style="color:gray">--所属账号--</option>
                            <?php echo list_option($account_array, '_value_', '_value_'); ?>
                        </select>
                        <select name="youhua_group" class="combo" style="margin-left:20px;">
                            <option value="" style="color:gray">--所属优化组--</option>
                            <?php echo list_option($youhua_group_arr, '_value_', '_value_'); ?>
                        </select>
                        <span style="margin-left:20px;">
                            专家号：<input name="zhuanjia_num" title="预约专家号" value="<?php echo $_GET["zhuanjia_num"]; ?>"
                                class="input" size="30" style="width:100px">
                        </span>
                        <span style="margin-left:20px;">
                            推广人：<input name="tuiguangren" title="" value="<?php echo $_GET["tuiguangren"]; ?>"
                                class="input" size="20" style="width:100px">
                        </span>
                    </td>
                </tr>

                <tr>
                    <td class="left">指定医生：</td>
                    <td class="right">
                        <select name="wish_doctor" id="wish_doctor" class="combo"
                            onchange="check_doctor_yuyue(this.value);">
                            <option value="" style="color:silver;">-请先选择预约日期-</option>
                        </select>

                        <script type="text/javascript">
                        function add_option(id, key, value) {
                            var newOption = document.createElement("option");
                            newOption.setAttribute("value", key);
                            newOption.appendChild(document.createTextNode(value));
                            document.getElementById(id).appendChild(newOption);
                        }

                        function load_js_file(src, id) {
                            var headerDom = document.getElementsByTagName('head').item(0);
                            var jsDom = document.createElement('script');
                            jsDom.type = 'text/javascript';
                            jsDom.src = src;
                            if (id) {
                                jsDom.id = id;
                            }
                            headerDom.appendChild(jsDom);
                        }

                        function reload_doctor(date) {
                            var url = "http/reload_doctor.php?date=" + date + "&r=" + Math.random();
                            load_js_file(url);
                        }

                        function check_doctor_yuyue(doctor_name) {
                            if (doctor_name != '') {
                                var day = byid("order_date").value;
                                if (day == '') {
                                    alert("请先选择预约日期，再选择医生，以确定医生在该日期是否有接诊名额！");
                                    byid("wish_doctor").value = '';
                                    return false;
                                } else {
                                    var url = "http/doctor_check_yuyue.php?date=" + day + "&doctor=" +
                                        encodeURIComponent(doctor_name) + "&r=" + Math.random();
                                    load_js_file(url);
                                }
                            }
                        }

                        reload_doctor('');
                        </script>
                    </td>
                </tr>

                <tr>
                    <td class="left">添加备注：</td>
                    <td class="right"><textarea name="memo" style="width:60%; height:48px; vertical-align:top;"
                            class="input"><?php echo $_GET["memo"]; ?></textarea></td>
                </tr>
                <?php if ($uinfo["part_id"] == 4) { ?>
                <tr>
                    <td class="left">到院状态：</td>
                    <td class="right">
                        <select name="daoyi_status" class="combo">
                            <?php echo list_option(array("9" => "未到", "1" => "已到"), '_key_', '_value_', 1); ?>
                        </select>&nbsp;
                        <b style="color:red">此处已修改：导医可以选择已到或未到</b> (该功能仅导医可见)
                    </td>
                </tr>
                <?php } ?>
            </table>
        </fieldset>

        <input type="hidden" name="from" value="<?php echo $_GET["from"]; ?>" />

        <?php if ($_GET["from"] == "ku") { ?>
        <input type="hidden" name="from_table" value="ku_list" />
        <input type="hidden" name="from_id" value="<?php echo intval($_GET["ku_id"]); ?>" />
        <input type="hidden" name="from_part_id" value="<?php echo intval($_GET["from_part_id"]); ?>" />
        <input type="hidden" name="from_uid" value="<?php echo intval($_GET["from_uid"]); ?>" />
        <input type="hidden" name="from_author" value="<?php echo $_GET["from_author"]; ?>" />
        <script type="text/javascript">
        alert("资料已载入到当前表单，请修改完善后提交！");
        </script>
        <?php } ?>

        <?php if ($_GET["from"] == "xinmeiti") { ?>
        <input type="hidden" name="from_table" value="<?php echo intval($_GET["xinmeiti_list"]); ?>" />
        <input type="hidden" name="from_id" value="<?php echo intval($_GET["xinmeiti_id"]); ?>" />
        <input type="hidden" name="from_part_id" value="<?php echo intval($_GET["from_part_id"]); ?>" />
        <input type="hidden" name="from_uid" value="<?php echo intval($_GET["from_uid"]); ?>" />
        <input type="hidden" name="from_author" value="<?php echo $_GET["from_author"]; ?>" />
        <script type="text/javascript">
        alert("新媒体资料已载入到当前表单，请修改完善后提交！");
        </script>
        <?php } ?>

        <?php if ($_GET["from"] == "catch") { ?>
        <input type="hidden" name="from_table" value="mobile_catch" />
        <input type="hidden" name="from_id" value="<?php echo intval($_GET["catch_id"]); ?>" />
        <script type="text/javascript">
        alert("资料已载入到当前表单，请修改完善后提交！");
        </script>
        <?php } ?>

        <?php if ($_GET["from"] == "wxku") { ?>
        <input type="hidden" name="from_table" value="wxku_list" />
        <input type="hidden" name="from_id" value="<?php echo intval($_GET["from_id"]); ?>" />
        <script type="text/javascript">
        alert("资料已载入到当前表单，请修改完善后提交！");
        </script>
        <?php } ?>

        <?php if ($_GET["from"] == "ditu") { ?>
        <input type="hidden" name="from_table" value="mobile_ditu" />
        <input type="hidden" name="from_id" value="<?php echo intval($_GET["from_id"]); ?>" />
        <?php } ?>

        <?php if ($_GET["from"] == "m_catch") { ?>
        <input type="hidden" name="from_table" value="m_catch" />
        <input type="hidden" name="from_id" value="<?php echo intval($_GET["from_id"]); ?>" />
        <?php } ?>

        <div class="button_line"><input type="submit" class="submit" value="提交资料"></div>
    </form>

    <?php if ($_REQUEST["mind_repeat"] == 1) { ?>
    <!-- 用于提示由预约卡过来的号码重复 -->
    <script type="text/javascript">
    if (byid("tel").value != '') {
        byid("tel").onchange();
    }
    </script>
    <?php } ?>

</body>

</html>