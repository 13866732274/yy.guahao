<?php
// --------------------------------------------------------
// - ����˵�� : ����������ʾģʽ
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2016-11-04
// --------------------------------------------------------
require "lib/set_env.php";

if ($_POST["op"] == "submit") {
	$save_value = $_POST["date_mode"] == "2" ? 1 : 0;
	$db->query("update sys_admin set date_mode=$save_value where id=$uid limit 1");

	echo '<script> parent.update_content(); </script>';
	echo '<script> parent.msg_box("�����ѱ���", 2); </script>';
	echo '<script> parent.load_src(0); </script>';
	exit;
}


?>
<html>

<head>
    <title>����������ʾģʽ</title>
    <meta http-equiv="Content-Type" content="text/html;charset=gb2312">
    <link href="lib/base.css" rel="stylesheet" type="text/css">
    <script src="lib/base.js" language="javascript"></script>
    <style type="text/css">
    * {
        font-family: "΢���ź�" !important;
    }

    .l {
        text-align: right;
        border-bottom: 0px solid #D8D8D8;
        padding: 6px 20px 6px 0px;
        width: 200px;
    }

    .r {
        text-align: left;
        border-bottom: 0px solid #D8D8D8;
        padding: 6px 6px;
    }
    </style>
    <script language="javascript">
    </script>
</head>

<body style="padding:30px;">

    <form name="mainform" action="" method="POST">

        <div style="padding:10px;">
            <input type="radio" name="date_mode" value="1" <?php if ($uinfo["date_mode"] == 0) echo "checked"; ?>
                id="radio_1"><label for="radio_1">Ĭ�ϸ�ʽ�����������ʾ������ 09:00����Զ�ڸ�ʽ��ʾ��2015.12.31 12:00����</label>
        </div>
        <div style="padding:10px;">
            <input type="radio" name="date_mode" value="2" <?php if ($uinfo["date_mode"] == 1) echo "checked"; ?>
                id="radio_2"><label for="radio_2">�̶���ʽ��ʼ����ʾ��2015-12-31 12:00����</label>
        </div>

        <div style="margin-top:30px;">
            <center><input type="submit" class="submit" value="����"></center>
        </div>

        <input type="hidden" name="op" value="submit">
    </form>

</body>

</html>