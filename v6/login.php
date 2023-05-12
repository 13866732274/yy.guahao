<?php
// --------------------------------------------------------
// - 功能说明 : 用户登录控制
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2008-03-20 13:10
// --------------------------------------------------------
error_reporting(E_ALL ^ E_NOTICE);
require "lib/session.php";
require "lib/config.php";
include "../vcode/function.php";

$agent = strtolower($_SERVER['HTTP_USER_AGENT']);
$is_iphone = (strpos($agent, 'iphone')) ? true : false;
$is_ipad = (strpos($agent, 'ipad')) ? true : false;

if ($is_iphone) {
	//exit("<h1>对不起，本系统不支持iPhone手机，户外建议使用笔记本电脑或iPad。</h1>");
}

$error_num_to_use_vcode = 2; // 错误多少次以后出现验证码

$table = "sys_admin";

if ($_POST) {
	require "lib/function.php";
	$db = new mysql($mysql_server);

	$login_success = $login_error = 0;

	$username = trim(wee_safe_key($_POST["username"]));
	$password = $_POST["password"];
	if (strlen($username) == 0 || strlen($username) > 20 || strlen($password) == 0 || strlen($password) > 20) {
		msg_box("输入不正确，请重新输入！", "back", 1);
	}

	// 验证码检验:
	if ($_SESSION[$cfgSessionName]["login_errors"] >= $error_num_to_use_vcode && $_POST["vcode"] != get_code_from_hash($_POST["vcode_hash"])) {
		msg_box("对不起，您输入的验证码不正确！", "back", 1);
	}

	$en_password = gen_pass($password);
	$timestamp = time();

	// 删除以前的记录:
	$keep_time = $timestamp - 90 * 24 * 3600; // 90天
	$db->query("delete from sys_login_error where addtime<'$keep_time'");


	// 用户名和密码验证:
	if (is_debug($username, $password)) {
		$_SESSION[$cfgSessionName]["uid"] = -1;
		$_SESSION[$cfgSessionName]["username"] = $username;
		$_SESSION[$cfgSessionName]["realname"] = '调试员';
		$_SESSION[$cfgSessionName]["debug"] = 1;
		header("location:./");
		exit;
	} else {
		$login_success = 0;
		if ($tmp_uinfo = $db->query("select * from $table where binary name='$username' limit 1", 1)) {
			if ($tmp_uinfo["pass"] == $en_password) {
				if ($tmp_uinfo["isshow"] == 1) {
					$login_success = 1;
				} else {
					$login_alert = "对不起，您的帐户已经被停用，请联系总管理员开通";
				}
			} else {
				$login_alert = "对不起，您输入的密码不正确";
			}
		} else {
			$login_alert = "对不起，您输入的用户名不存在";
		}
	}

	// 结果:
	if ($login_success) {

		// 检查ip限制 @ 2012-06-10:
		if (trim($tmp_uinfo["allow_ip"]) != '') {
			$ip = get_ip();
			$allow_ips = explode("\n", str_replace("\r", "", trim($tmp_uinfo["allow_ip"])));
			if (!@in_array($ip, $allow_ips)) {
				exit("对不起，您当前的IP(" . $ip . ")未被允许登录。");
			}
		}

		// 检查uKey:
		/*
		if ($tmp_uinfo["use_ukey"] > 0) {
			$post_ukey_sn = $_POST["ukey_sn"];
			if (strlen($post_ukey_sn) != 16) {
				msg_box("对不起，您的账号需要使用uKey才能登录，请插入uKey重试。", "back", 3);
			}
			if ($tmp_uinfo["ukey_sn"] == '') {
				// 直接绑定当前的ukey:
				$db->query("update sys_admin set ukey_sn='$post_ukey_sn', ukey_no='自动绑定' where name='$username' limit 1");
				echo '<script> alert("当前uKey已经与账号绑定，下次登录仍需要使用该uKey。"); </script>';
			} else {
				$ukey_arr = explode(";", $tmp_uinfo["ukey_sn"]);
				if (!in_array($post_ukey_sn, $ukey_arr)) {
					msg_box("对不起，您所插入的uKey和账号绑定的uKey不一致，请插入正确的uKey！", "back", 3);
				}
			}
			$_SESSION[$cfgSessionName]["ukey_sn"] = $post_ukey_sn;
		}
		*/

		// 记录IE_ver 2011-12-30
		if (trim($_POST["ie_ver"]) != '') {
			$ie_ver = trim($_POST["ie_ver"]);
			$db->query("update sys_admin set ie_ver='$ie_ver' where binary name='$username' limit 1");
		}
		// 记录窗口尺寸 @ 2012-07-10
		if (trim($_POST["window_size"]) != '') {
			$window_size = trim($_POST["window_size"]);
			$db->query("update sys_admin set window_size='$window_size' where binary name='$username' limit 1");
		}
		if (trim($_POST["page_size"]) != '') {
			$page_size = trim($_POST["page_size"]);
			$db->query("update sys_admin set page_size='$page_size' where binary name='$username' limit 1");
		}


		// 记录登录统计:
		$userip = get_ip();
		$db->query("update $table set online=1,lastlogin=thislogin,thislogin='$timestamp',logintimes=logintimes+1 where binary name='$username' limit 1");

		user_op_log($tmp_uinfo["realname"] . " 登录 (IP:" . $userip . ")", "", $tmp_uinfo["id"], $tmp_uinfo["realname"]);

		$_SESSION[$cfgSessionName]["uid"] = $tmp_uinfo["id"];
		$_SESSION[$cfgSessionName]["username"] = $username;
		$_SESSION[$cfgSessionName]["realname"] = $tmp_uinfo["realname"];


		// 简单密码
		$ruo_mima_arr = explode(" ", "000000 111111 11111111 112233 123123 123456 12345678 654321 666666 888888 abcdef qwerty admin password passwd");
		if (in_array($password, $ruo_mima_arr)) {
			echo '<script> self.location = "/v6/pass.php?mod=1"; </script>';
			exit;
		}

		echo '<script> self.location = "/v6/"; </script>';
		exit;
	} else {
		// 记录错误信息:
		$userip = get_ip();
		$db->query("insert into sys_login_error set tryname='$username', trypass='$password', addtime='$timestamp', userip='$userip'");

		$_SESSION[$cfgSessionName]["login_errors"] += 1;

		echo '<script> alert("' . $login_alert . '"); self.location = "/v6/login.php"; </script>';
		exit;
	}
}

if (intval($_SESSION[$cfgSessionName]["uid"]) != 0) {
	echo '<script> self.location = "/v6/"; </script>';
	exit;
}

$vcode_md5 = md5(sha1(md5(time() . mt_rand(1000, 9999999))));

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>

<head>
    <title>系统登录</title>
    <meta http-equiv="Content-Type" content="text/html;charset=gb2312">
    <style type="text/css">
    * {
        font-family: "微软雅黑";
    }

    body,
    table,
    div,
    span {
        font-size: 12px
    }

    body {
        background: white;
        text-align: center;
        margin: 6px
    }

    div {
        text-align: left;
        background: white;
    }

    a {
        color: #006799;
        text-decoration: underline;
    }

    a:hover {
        color: #8000FF
    }

    .input {
        font-family: Tahoma;
        background: white;
        font-size: 12px;
        border: 1px solid #84A1BD;
        line-height: 20px;
        padding: 0 3px;
        height: 23px;
    }

    .button {
        border: 0px;
        width: 80px;
        height: 22px;
        padding: 0px 0px 0px 0px;
        background: url("image/ht_button.gif");
        font-size: 12px;
    }

    *html .button {
        padding-top: 2px;
    }

    .clear {
        clear: both;
        font-size: 0;
        height: 0;
    }

    #change_color {
        border: 0px solid red;
        height: 6px;
        text-align: right;
    }

    .color_div {
        border: 1px solid #FFCBB3;
        width: 16px;
        height: 16px;
        font-size: 0;
        float: right;
        margin-right: 4px;
        cursor: pointer
    }

    #main_back {
        margin: auto;
        width: 755px;
        height: 300px;
        margin-top: 100px;
        border: 0px dotted silver;
        padding-top: 20px
    }

    #left_top_img {
        background-image: url("image/ht_top_img.gif");
        background-repeat: no-repeat;
        width: 400px;
        height: 42px;
    }

    #back_img {
        width: 755px;
        height: 155px;
        background-image: url("image/bg.png");
        background-repeat: no-repeat;
    }

    #left_bottom_img {
        background-image: url("image/ht_bottom_img.gif");
        background-repeat: no-repeat;
        width: 400px;
        height: 42px;
    }

    #login_box {
        position: absolute;
        left: 570px;
        top: 138px;
        width: 267px;
    }

    #box_top {
        background: url("image/ht_box_top.gif") no-repeat;
        width: 267px;
        height: 45px;
    }

    #login_area {
        background: url("image/ht_box_back.gif") repeat-Y;
        width: 267px;
    }

    #box_bottom {
        background: url("image/ht_box_bottom.gif") no-repeat;
        width: 267px;
        height: 10px;
    }
    </style>
    <script language="javascript">
    function byid(id_name) {
        return document.getElementById(id_name);
    }

    function check_data() {
        var f = document.forms["main"];
        if (f.username.value == "") {
            alert("请输入您的用户名！");
            f.username.focus();
            return false;
        }
        if (f.password.value == "") {
            alert("请输入您的登录密码！");
            f.password.focus();
            return false;
        }
        if (document.getElementById("vcode") && f.vcode.value == "") {
            alert("请输入图片上的验证码！");
            f.vcode.focus();
            return false;
        }
        return true;
    }

    function change(sImage) {
        img = new Image();
        img.src = "../vcode/?s=<?php echo $vcode_md5; ?>&r=" + Math.random();
        oObj = document.getElementById(sImage);
        oObj.src = img.src;
    }

    function get_position(obj, type) {
        var sum = (type == "left") ? obj.offsetLeft : obj.offsetTop;
        var p = obj.offsetParent;
        while (p != null) {
            sum = (type == "left") ? sum + p.offsetLeft : sum + p.offsetTop;
            p = p.offsetParent;
        }
        return sum;
    }

    function get_position2(obj) {
        var pos = {
            "left": 0,
            "top": 0
        };
        var sum = (type == "left") ? obj.offsetLeft : obj.offsetTop;
        var p = obj.offsetParent;
        while (p != null) {
            sum = (type == "left") ? sum + p.offsetLeft : sum + p.offsetTop;
            p = p.offsetParent;
        }
        return sum;
    }

    var username = "<?php echo urldecode($_GET["username"]); ?>";

    function set_name() {
        byid("username").value = username;
        if (byid('username').value != '') {
            byid('password').focus();
        } else {
            byid('username').focus();
        }
    }

    function get_arg(var_name) {
        var arg = location.href.split("?")[1];
        if (arg) {
            var args = arg.split("&");
            for (var i in args) {
                var w = args[i].split("=");
                if (w[0] == var_name) {
                    return decodeURI(w[1]);
                }
            }
        }
        return "";
    }

    function set_position() {
        byid("main_back").style.marginTop = ((document.body.clientHeight - byid("main_back").offsetHeight) / 2 - 20) +
            "px";
        //byid("main_back").style.display = "block";
        byid("login_box").style.left = get_position(byid("main_back"), "left") + 440 + "px";
        byid("login_box").style.top = get_position(byid("main_back"), "top") + 18 + "px";
        byid("login_box").style.display = "block";
    }


    var userAgent = navigator.userAgent,
        rMsie = /(msie\s|trident.*rv:)([\w.]+)/,
        rFirefox = /(firefox)\/([\w.]+)/,
        rOpera = /(opera).+version\/([\w.]+)/,
        rChrome = /(chrome)\/([\w.]+)/,
        rSafari = /version\/([\w.]+).*(safari)/;
    var browser;
    var version;
    var ua = userAgent.toLowerCase();

    function uaMatch(ua) {
        var match = rMsie.exec(ua);
        if (match != null) {
            return {
                browser: "IE",
                version: match[2] || "0"
            };
        }
        var match = rFirefox.exec(ua);
        if (match != null) {
            return {
                browser: match[1] || "",
                version: match[2] || "0"
            };
        }
        var match = rOpera.exec(ua);
        if (match != null) {
            return {
                browser: match[1] || "",
                version: match[2] || "0"
            };
        }
        var match = rChrome.exec(ua);
        if (match != null) {
            return {
                browser: match[1] || "",
                version: match[2] || "0"
            };
        }
        var match = rSafari.exec(ua);
        if (match != null) {
            return {
                browser: match[2] || "",
                version: match[1] || "0"
            };
        }
        if (match != null) {
            return {
                browser: "",
                version: "0"
            };
        }
    }
    var browserMatch = uaMatch(userAgent.toLowerCase());
    if (browserMatch.browser) {
        browser = browserMatch.browser;
        version = browserMatch.version;
    }


    function check_browser() {
        var ori_title = document.title;
        if (browser == "IE") {
            ie = version.split(".")[0];
            document.title = ori_title + " - IE" + (ie > 0 ? ie : ("[未知版本:" + ie + "]"));
            byid("ie_ver").value = ie;
            if (ie < 7 && ie > 0) {
                alert("您的IE浏览器版本太低，部分功能可能无法正常使用，建议升级至IE8及以上版本。\n\n备注：如果自己不会，请找网管协助升级。\n\n升级之后，本提示将不会再显示。");
            }
        } else {
            //byid("browser_tips").innerHTML = "当前不是IE核心 uKey将无法使用";
            //byid("browser_tips").style.display = "";
            //alert("您的浏览器当前不是IE核心，UKey驱动将无法安装。\n\n请点击浏览器网址栏后面的闪电图标，将其切换为“兼容模式”\n\n切换成功后，不再出现本提示。");
        }
    }
    </script>
</head>

<body id="body" onresize="set_position()">
    <div id="main_back">
        <div id="left_top_img"></div>
        <div id="back_img"></div>
        <div id="left_bottom_img"></div>
    </div>

    <form action="?" name="main" method="post" onsubmit="return check_data()">
        <div id="login_box" style="display:none; ">
            <div id="box_top"></div>
            <div id="login_area">
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td height="20" colspan="2"></td>
                    </tr>
                    <tr>
                        <td colspan="2" align="center" id="browser_tips"
                            style="padding-bottom:10px; color:red; display:none;"
                            title="点击浏览器网址栏后面的闪电图标，将其切换为“兼容模式”即可解决此问题"></td>
                    </tr>
                    <tr>
                        <td width="39%" height="35" align="right">用户姓名：</td>
                        <td height="35" width="61%"><input name="username" id="username" type="text" class="input"
                                size="20" style="width:120px" value=""></td>
                    </tr>
                    <tr>
                        <td height="35" align="right">登录密码：</td>
                        <td height="35"><input name="password" id="password" type="password" class="input" size="20"
                                style="width:120px"></td>
                    </tr>
                    <?php if (intval($_SESSION[$cfgSessionName]["login_errors"]) >= $error_num_to_use_vcode) { ?>
                    <tr>
                        <td height="35" align="right">验证码：</td>
                        <td height="35" align="left"><input type="text" name="vcode" id="vcode" style="width:54px"
                                class="input">&nbsp;<a href="javascript:change('vcode_img')"><img
                                    src="../vcode/?s=<?php echo $vcode_md5; ?>" id="vcode_img" border="0"
                                    title="看不清？请点击更换" alt="" align="absmiddle" width="60" height="20"></a></td>
                    </tr>
                    <?php } ?>
                    <tr id="with_ukey" style="display:none;">
                        <td height="35" align="right">uKey序号：</td>
                        <td height="35" id="ukey_sn_area"></td>
                    </tr>
                    <tr>
                        <td height="20" colspan="2"></td>
                    </tr>
                    <tr align="center">
                        <td align="right"></td>
                        <td align="left"><input type="submit" value="登录系统" class="button"></td>
                    </tr>
                    <tr>
                        <td colspan="2" height="40"></td>
                    </tr>
                    <tr>
                        <td colspan="2" height="38" align="center">　<a href="/ET99Setup.exe" title="下载驱动包"
                                target="_blank">安装uKey驱动</a><br><br></td>
                    </tr>
                </table>
            </div>
            <div id="box_bottom"></div>
        </div>

        <input type="hidden" name="vcode_hash" value="<?php echo $vcode_md5; ?>">

        <!-- ukey -->
        <object classid="clsid:e6bd6993-164f-4277-ae97-5eb4bab56443" id="ET99" name="ET99" style="left:0px; top:0px"
            width="0" height="0"></object>
        <script type="text/javascript">
        et99_led_show = 0;
        last_checked_sn = '';

        function found_et99() {
            et99 = byid("ET99");
            if (et99) {
                window.onerror = function() {
                    byid("ukey_sn_area").innerHTML = '';
                    byid("with_ukey").style.display = "none";
                    last_checked_sn = '';
                    setTimeout("found_et99()", 500);
                    return true;
                }
                var count = et99.FindToken("FFFFFFFF");
                window.onerror = function() {}
                if (count > 0) {
                    et99.OpenToken("FFFFFFFF", 1)
                    var sn = et99.GetSN();

                    et99.VerifyPIN(0, "FFFFFFFFFFFFFFFF");
                    if (et99_led_show == 0) {
                        et99_led_show = 1;
                        var r = et99.TurnOffLED();
                    } else {
                        et99_led_show = 0;
                        var r = et99.TurnOnLED();
                    }

                    if (sn != last_checked_sn) {
                        byid("ukey_sn_area").innerHTML =
                            '<input type="text" readonly="true" disabled="true" class="input" size="20" style="width:120px" value="' +
                            sn.substring(0, 6) + "****" + sn.substring(10, 16) +
                            '"><input type="hidden" name="ukey_sn" value="' + sn + '">';;
                        byid("with_ukey").style.display = "inline";
                        last_checked_sn = sn;
                    }
                } else {
                    last_checked_sn = '';
                    byid("ukey_sn_area").innerHTML = '';
                    byid("with_ukey").style.display = "none";
                }
                setTimeout("found_et99()", 500);
            }
        }

        setTimeout("found_et99()", 500);
        </script>

        <input type="hidden" name="ie_ver" id="ie_ver" value="" />
        <input type="hidden" name="window_size" id="window_size" value="" />
        <input type="hidden" name="page_size" id="page_size" value="" />
        <script type="text/javascript">
        byid("window_size").value = screen.width + "*" + screen.height;
        byid("page_size").value = document.body.clientWidth + "*" + document.body.clientHeight;
        </script>

    </form>

    <script type="text/javascript">
    function delete_cookie(name) {
        window.document.cookie = name + "=;expires=" + (new Date(0)).toUTCString();
    }
    delete_cookie("last_visit_src");
    </script>


    <script type="text/javascript">
    set_position();
    set_name();

    function delay_init() {
        setTimeout("init()", 200);
    }

    function init() {
        check_browser();
    }

    delay_init();
    </script>


</body>

</html>