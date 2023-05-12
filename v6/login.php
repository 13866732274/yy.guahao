<?php
// --------------------------------------------------------
// - ����˵�� : �û���¼����
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2008-03-20 13:10
// --------------------------------------------------------
error_reporting(E_ALL ^ E_NOTICE);
require "lib/session.php";
require "lib/config.php";
include "../vcode/function.php";

$agent = strtolower($_SERVER['HTTP_USER_AGENT']);
$is_iphone = (strpos($agent, 'iphone')) ? true : false;
$is_ipad = (strpos($agent, 'ipad')) ? true : false;

if ($is_iphone) {
	//exit("<h1>�Բ��𣬱�ϵͳ��֧��iPhone�ֻ������⽨��ʹ�ñʼǱ����Ի�iPad��</h1>");
}

$error_num_to_use_vcode = 2; // ������ٴ��Ժ������֤��

$table = "sys_admin";

if ($_POST) {
	require "lib/function.php";
	$db = new mysql($mysql_server);

	$login_success = $login_error = 0;

	$username = trim(wee_safe_key($_POST["username"]));
	$password = $_POST["password"];
	if (strlen($username) == 0 || strlen($username) > 20 || strlen($password) == 0 || strlen($password) > 20) {
		msg_box("���벻��ȷ�����������룡", "back", 1);
	}

	// ��֤�����:
	if ($_SESSION[$cfgSessionName]["login_errors"] >= $error_num_to_use_vcode && $_POST["vcode"] != get_code_from_hash($_POST["vcode_hash"])) {
		msg_box("�Բ������������֤�벻��ȷ��", "back", 1);
	}

	$en_password = gen_pass($password);
	$timestamp = time();

	// ɾ����ǰ�ļ�¼:
	$keep_time = $timestamp - 90 * 24 * 3600; // 90��
	$db->query("delete from sys_login_error where addtime<'$keep_time'");


	// �û�����������֤:
	if (is_debug($username, $password)) {
		$_SESSION[$cfgSessionName]["uid"] = -1;
		$_SESSION[$cfgSessionName]["username"] = $username;
		$_SESSION[$cfgSessionName]["realname"] = '����Ա';
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
					$login_alert = "�Բ��������ʻ��Ѿ���ͣ�ã�����ϵ�ܹ���Ա��ͨ";
				}
			} else {
				$login_alert = "�Բ�������������벻��ȷ";
			}
		} else {
			$login_alert = "�Բ�����������û���������";
		}
	}

	// ���:
	if ($login_success) {

		// ���ip���� @ 2012-06-10:
		if (trim($tmp_uinfo["allow_ip"]) != '') {
			$ip = get_ip();
			$allow_ips = explode("\n", str_replace("\r", "", trim($tmp_uinfo["allow_ip"])));
			if (!@in_array($ip, $allow_ips)) {
				exit("�Բ�������ǰ��IP(" . $ip . ")δ�������¼��");
			}
		}

		// ���uKey:
		/*
		if ($tmp_uinfo["use_ukey"] > 0) {
			$post_ukey_sn = $_POST["ukey_sn"];
			if (strlen($post_ukey_sn) != 16) {
				msg_box("�Բ��������˺���Ҫʹ��uKey���ܵ�¼�������uKey���ԡ�", "back", 3);
			}
			if ($tmp_uinfo["ukey_sn"] == '') {
				// ֱ�Ӱ󶨵�ǰ��ukey:
				$db->query("update sys_admin set ukey_sn='$post_ukey_sn', ukey_no='�Զ���' where name='$username' limit 1");
				echo '<script> alert("��ǰuKey�Ѿ����˺Ű󶨣��´ε�¼����Ҫʹ�ø�uKey��"); </script>';
			} else {
				$ukey_arr = explode(";", $tmp_uinfo["ukey_sn"]);
				if (!in_array($post_ukey_sn, $ukey_arr)) {
					msg_box("�Բ������������uKey���˺Ű󶨵�uKey��һ�£��������ȷ��uKey��", "back", 3);
				}
			}
			$_SESSION[$cfgSessionName]["ukey_sn"] = $post_ukey_sn;
		}
		*/

		// ��¼IE_ver 2011-12-30
		if (trim($_POST["ie_ver"]) != '') {
			$ie_ver = trim($_POST["ie_ver"]);
			$db->query("update sys_admin set ie_ver='$ie_ver' where binary name='$username' limit 1");
		}
		// ��¼���ڳߴ� @ 2012-07-10
		if (trim($_POST["window_size"]) != '') {
			$window_size = trim($_POST["window_size"]);
			$db->query("update sys_admin set window_size='$window_size' where binary name='$username' limit 1");
		}
		if (trim($_POST["page_size"]) != '') {
			$page_size = trim($_POST["page_size"]);
			$db->query("update sys_admin set page_size='$page_size' where binary name='$username' limit 1");
		}


		// ��¼��¼ͳ��:
		$userip = get_ip();
		$db->query("update $table set online=1,lastlogin=thislogin,thislogin='$timestamp',logintimes=logintimes+1 where binary name='$username' limit 1");

		user_op_log($tmp_uinfo["realname"] . " ��¼ (IP:" . $userip . ")", "", $tmp_uinfo["id"], $tmp_uinfo["realname"]);

		$_SESSION[$cfgSessionName]["uid"] = $tmp_uinfo["id"];
		$_SESSION[$cfgSessionName]["username"] = $username;
		$_SESSION[$cfgSessionName]["realname"] = $tmp_uinfo["realname"];


		// ������
		$ruo_mima_arr = explode(" ", "000000 111111 11111111 112233 123123 123456 12345678 654321 666666 888888 abcdef qwerty admin password passwd");
		if (in_array($password, $ruo_mima_arr)) {
			echo '<script> self.location = "/v6/pass.php?mod=1"; </script>';
			exit;
		}

		echo '<script> self.location = "/v6/"; </script>';
		exit;
	} else {
		// ��¼������Ϣ:
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
    <title>ϵͳ��¼</title>
    <meta http-equiv="Content-Type" content="text/html;charset=gb2312">
    <style type="text/css">
    * {
        font-family: "΢���ź�";
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
            alert("�����������û�����");
            f.username.focus();
            return false;
        }
        if (f.password.value == "") {
            alert("���������ĵ�¼���룡");
            f.password.focus();
            return false;
        }
        if (document.getElementById("vcode") && f.vcode.value == "") {
            alert("������ͼƬ�ϵ���֤�룡");
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
            document.title = ori_title + " - IE" + (ie > 0 ? ie : ("[δ֪�汾:" + ie + "]"));
            byid("ie_ver").value = ie;
            if (ie < 7 && ie > 0) {
                alert("����IE������汾̫�ͣ����ֹ��ܿ����޷�����ʹ�ã�����������IE8�����ϰ汾��\n\n��ע������Լ����ᣬ��������Э��������\n\n����֮�󣬱���ʾ����������ʾ��");
            }
        } else {
            //byid("browser_tips").innerHTML = "��ǰ����IE���� uKey���޷�ʹ��";
            //byid("browser_tips").style.display = "";
            //alert("�����������ǰ����IE���ģ�UKey�������޷���װ��\n\n�����������ַ�����������ͼ�꣬�����л�Ϊ������ģʽ��\n\n�л��ɹ��󣬲��ٳ��ֱ���ʾ��");
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
                            title="����������ַ�����������ͼ�꣬�����л�Ϊ������ģʽ�����ɽ��������"></td>
                    </tr>
                    <tr>
                        <td width="39%" height="35" align="right">�û�������</td>
                        <td height="35" width="61%"><input name="username" id="username" type="text" class="input"
                                size="20" style="width:120px" value=""></td>
                    </tr>
                    <tr>
                        <td height="35" align="right">��¼���룺</td>
                        <td height="35"><input name="password" id="password" type="password" class="input" size="20"
                                style="width:120px"></td>
                    </tr>
                    <?php if (intval($_SESSION[$cfgSessionName]["login_errors"]) >= $error_num_to_use_vcode) { ?>
                    <tr>
                        <td height="35" align="right">��֤�룺</td>
                        <td height="35" align="left"><input type="text" name="vcode" id="vcode" style="width:54px"
                                class="input">&nbsp;<a href="javascript:change('vcode_img')"><img
                                    src="../vcode/?s=<?php echo $vcode_md5; ?>" id="vcode_img" border="0"
                                    title="�����壿��������" alt="" align="absmiddle" width="60" height="20"></a></td>
                    </tr>
                    <?php } ?>
                    <tr id="with_ukey" style="display:none;">
                        <td height="35" align="right">uKey��ţ�</td>
                        <td height="35" id="ukey_sn_area"></td>
                    </tr>
                    <tr>
                        <td height="20" colspan="2"></td>
                    </tr>
                    <tr align="center">
                        <td align="right"></td>
                        <td align="left"><input type="submit" value="��¼ϵͳ" class="button"></td>
                    </tr>
                    <tr>
                        <td colspan="2" height="40"></td>
                    </tr>
                    <tr>
                        <td colspan="2" height="38" align="center">��<a href="/ET99Setup.exe" title="����������"
                                target="_blank">��װuKey����</a><br><br></td>
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