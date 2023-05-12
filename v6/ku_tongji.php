<?php
// --------------------------------------------------------
// - ����˵�� : ���Ͽⱨ��
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2015-1-6
// --------------------------------------------------------
require "lib/set_env.php";
include "ku.config.php";
$table = "ku_list";

// ʱ�䶨��:
$today_tb = mktime(0, 0, 0); //���쿪ʼ
$today_te = strtotime("+1 day", $today_tb) - 1; //�������

$yesterday_tb = strtotime("-1 day", $today_tb); //���쿪ʼ
$yesterday_te = $today_tb - 1; //�������

$month_tb = mktime(0, 0, 0, date("m"), 1); //���¿�ʼ
$month_te = strtotime("+1 month", $month_tb) - 1; //���½���

$lastmonth_tb = strtotime("-1 month", $month_tb); //���¿�ʼ
$lastmonth_te = $month_tb - 1; //���½���

$lastlastmonth_tb = strtotime("-2 month", $month_tb); //�����¿�ʼ
$lastlastmonth_te = $lastmonth_tb - 1; //�����½���

$time_arr = array(
	"����" => array($today_tb, $today_te),
	"����" => array($yesterday_tb, $yesterday_te),
	"����" => array($month_tb, $month_te),
	"����" => array($lastmonth_tb, $lastmonth_te),
	"������" => array($lastlastmonth_tb, $lastlastmonth_te),
);

// ͳ�Ʒ���
$_ids = count($hospital_ids) ? implode(",", $hospital_ids) : "0";
$t = strtotime("-3 month");
$h_arr = $db->query("select hid, h_name, count(hid) as c from $table where hid in ($_ids) and addtime>$t group by hid", "hid");


$view_hid = intval($_GET["view_hid"]);
if ($view_hid <= 0) {
	// ��������ͳ��:
	$show_name = "��������ͳ��";

	foreach ($time_arr as $t => $t_def) {
		$t1 = $t_def[0];
		$t2 = $t_def[1];
		$dt = array();
		$dt[0] = $db->query("select count(*) as c from $table where hid in ($_ids) and addtime>=$t1 and addtime<=$t2", 1, "c");
		$dt[1] = $db->query("select count(*) as c from $table where hid in ($_ids) and addtime>=$t1 and addtime<=$t2 and is_yuyue>0", 1, "c");
		$dt[2] = $db->query("select count(*) as c from $table where hid in ($_ids) and addtime>=$t1 and addtime<=$t2 and is_yuyue>0 and is_come>0", 1, "c");
		$dt[3] = $dt[0] == 0 ? 0 : round(100 * $dt[1] / $dt[0], 1) . "%"; //ԤԼ��
		$dt[4] = $dt[0] == 0 ? 0 : round(100 * $dt[2] / $dt[0], 1) . "%"; //��Ժ��

		$all_data[$t] = $dt;
	}
} else {
	$show_name = $h_arr[$view_hid]["h_name"] . " ����ͳ��";

	foreach ($time_arr as $t => $t_def) {
		$t1 = $t_def[0];
		$t2 = $t_def[1];
		$dt = array();
		$dt[0] = $db->query("select count(*) as c from $table where hid=$view_hid and addtime>=$t1 and addtime<=$t2", 1, "c");
		$dt[1] = $db->query("select count(*) as c from $table where hid=$view_hid and addtime>=$t1 and addtime<=$t2 and is_yuyue>0", 1, "c");
		$dt[2] = $db->query("select count(*) as c from $table where hid=$view_hid and addtime>=$t1 and addtime<=$t2 and is_yuyue>0 and is_come>0", 1, "c");
		$dt[3] = $dt[0] == 0 ? 0 : round(100 * $dt[1] / $dt[0], 1) . "%"; //ԤԼ��
		$dt[4] = $dt[0] == 0 ? 0 : round(100 * $dt[2] / $dt[0], 1) . "%"; //��Ժ��

		$all_data[$t] = $dt;
	}
}
?>
<html>

<head>
    <title>���Ͽ�ͳ������</title>
    <meta http-equiv="Content-Type" content="text/html;charset=gb2312">
    <link href="lib/base.css" rel="stylesheet" type="text/css">
    <script src="lib/base.js" language="javascript"></script>

    <style>
    * {
        font-family: "΢���ź�", "Tahoma";
    }

    .h_name {
        font-size: 14px;
        color: #0000ff;
        margin-top: 30px;
    }

    .kefu_name {
        font-size: 14px;
        color: #0000ff;
        margin-top: 10px;
    }

    .list {
        border: 2px solid #a2a2a2;
        margin-top: 10px;
    }

    .list .head td {
        background: #ebebeb;
        padding: 4px;
        color: #ff8000;
        text-align: center;
        border: 1px solid #e4e4e4;
    }

    .list .line td {
        padding: 4px;
        text-align: center;
        border: 1px solid #e4e4e4;
    }
    </style>


</head>

<body>


    <form method="GET">
        <center>
            <b>�����Ҳ鿴��</b>
            <select name="view_hid" class="combo" style="margin-left:20px">
                <option value="" style="color:gray">--ѡ�����--</option>
                <?php echo list_option($h_arr, 'hid', 'h_name', $view_hid); ?>
            </select>
            <input type="submit" class="button" value="ȷ��" style="margin-left:20px">
        </center>
    </form>

    <div class="h_name">
        <center><?php echo $show_name; ?></center>
    </div>

    <table width="100%" class="list">
        <tr class="head">
            <td width="10%">
                <nobr>����</nobr>
            </td>
            <td width="18%">
                <nobr>����������</nobr>
            </td>
            <td width="18%">
                <nobr>ԤԼ����</nobr>
            </td>
            <td width="18%">
                <nobr>ԤԼ����</nobr>
            </td>
            <td width="18%">
                <nobr>��������</nobr>
            </td>
            <td width="18%">
                <nobr>�������</nobr>
            </td>
        </tr>

        <?php
		foreach ($time_arr as $t => $t_def) {
			$dt = $all_data[$t];
		?>

        <tr class="line">
            <td>
                <nobr><?php echo $t; ?></nobr>
            </td>
            <td>
                <nobr><?php echo $dt[0]; ?></nobr>
            </td>
            <td>
                <nobr><?php echo $dt[1]; ?></nobr>
            </td>
            <td>
                <nobr><?php echo $dt[3]; ?></nobr>
            </td>
            <td>
                <nobr><?php echo $dt[2]; ?></nobr>
            </td>
            <td>
                <nobr><?php echo $dt[4]; ?></nobr>
            </td>
        </tr>

        <?php
		}
		?>

    </table>
    <br>


    <?php
	if ($view_hid > 0) {
		$view_kefu = intval($_GET["view_kefu"]);
		if ($view_kefu) {

			echo "<br>";

			$data = $db->query("select u_name, addtime, is_yuyue, is_come from $table where hid=$view_hid and addtime>=$lastlastmonth_tb and addtime<=$month_te");
			// ��������:
			$kefu_data = $kefu_data_count = array();
			foreach ($data as $line) {
				foreach ($time_arr as $t => $t_def) {
					$t1 = $t_def[0];
					$t2 = $t_def[1];
					if ($line["addtime"] >= $t1 && $line["addtime"] <= $t2) {
						$kefu_data_count[$line["u_name"]] += 1;
						$kefu_data[$line["u_name"]][$t][0] += 1;
						if ($line["is_yuyue"] > 0) {
							$kefu_data[$line["u_name"]][$t][1] += 1;
						}
						if ($line["is_come"] > 0) {
							$kefu_data[$line["u_name"]][$t][2] += 1;
						}
					}
				}
			}

			arsort($kefu_data_count);

			foreach ($kefu_data_count as $kefu_name => $data_count) {
				$show_name = "�ͷ�: " . $kefu_name . "";
	?>

    <div class="kefu_name">
        <center><?php echo $show_name; ?></center>
    </div>

    <table width="100%" class="list">
        <tr class="head">
            <td width="10%">
                <nobr>����</nobr>
            </td>
            <td width="18%">
                <nobr>����������</nobr>
            </td>
            <td width="18%">
                <nobr>ԤԼ����</nobr>
            </td>
            <td width="18%">
                <nobr>ԤԼ����</nobr>
            </td>
            <td width="18%">
                <nobr>��������</nobr>
            </td>
            <td width="18%">
                <nobr>�������</nobr>
            </td>
        </tr>

        <?php
					foreach ($time_arr as $t => $t_def) {
						$dt = $kefu_data[$kefu_name][$t];
						$dt[3] = $dt[0] == 0 ? 0 : round(100 * $dt[1] / $dt[0], 1) . "%"; //ԤԼ��
						$dt[4] = $dt[0] == 0 ? 0 : round(100 * $dt[2] / $dt[0], 1) . "%"; //��Ժ��
					?>

        <tr class="line">
            <td>
                <nobr><?php echo $t; ?></nobr>
            </td>
            <td>
                <nobr><?php echo $dt[0]; ?></nobr>
            </td>
            <td>
                <nobr><?php echo $dt[1]; ?></nobr>
            </td>
            <td>
                <nobr><?php echo $dt[3]; ?></nobr>
            </td>
            <td>
                <nobr><?php echo $dt[2]; ?></nobr>
            </td>
            <td>
                <nobr><?php echo $dt[4]; ?></nobr>
            </td>
        </tr>

        <?php
					}
					?>

    </table>
    <br>

    <?php
			}
		} else {
			echo '<center><a href="?view_hid=' . $view_hid . '&view_kefu=1">[�鿴�ͷ���ϸ]</a></center>';
		}
	}

	?>

</body>

</html>