<?php
require "../lib/set_env.php";
require "../lib/class.fastjson.php";
// ������Ķ���:
include "../rp.core.php";
// ����id => name����:
$part_id_name      = $db->query("select id,name from sys_part", 'id', 'name');
$character_id_name = $db->query("select id,name from sys_character", 'id', 'name');
// ��ҳժҪ���ݣ�
$summary_info = '<span>���ã�' . $realname . '</span>';
if ($uinfo["hospitals"] || $uinfo["part_id"] > 0) {
    if ($uinfo["part_id"] > 0) {
        $summary_info .= '<span>��ݣ�' . $part_id_name[$uinfo["part_id"]] . "</span>";
    }
    if ($uinfo["character_id"] > 0) {
        $summary_info .= '<span>Ȩ�ޣ�' . $character_id_name[$uinfo["character_id"]] . '</span>';
    }
}
$today_timebegin = strtotime(date("Y-m-01 00:00:00"));
$today_timeend   = strtotime(date("Y-m-d H:i:s"));
// ��ȡ�ͷ�-��ǰʱ��
$kf_arr = $db->query("select uid,author,count(author) as c from patient_1 where status=1 and author!='' and author!='���' and order_date>=$today_timebegin and order_date<=$today_timeend group by author order by c desc");

if (count($kf_arr) == 0) {
    exit_html("<center>û�пͷ����޷�ͳ�ơ�</center>");
}
if (count($kf_arr) > 19) {
    $kf_count = count($kf_arr);
    $kf_arr   = array_slice($kf_arr, 0, 19);
    $tips     = " (��{$kf_count}λ�ͷ���������ʾ��Ҫ��������Ծ��ȡǰ19λ)";
}
$tmonth = intval(date("m"));
/* echo $tmonth;
exit; */

for ($i = 1; $i < $tmonth; $i++) {
    $tbegin  = strtotime(date("Y-" . $i . "-01 00:00:00"));
    $tend    = strtotime(date("Y-" . $i . "-01 00:00:00") . "+1 month");
    $tkf_arr = $db->query("select uid,author,count(author) as c from patient_1 where status=1 and author!='' and author!='���' and order_date>=$tbegin and order_date<=$tend group by author order by c desc");
    $tkf_arr = array($tkf_arr);
    $meige[] = $tkf_arr[0][0]['uid'];
}
$tongji_meige = array_count_values($meige);
/*
if (in_array($type, array(1, 2, 3, 4))) {
// ����ͳ������:
$data = array();
foreach ($final_dt_arr as $k => $v) {
$data[$k]["��"] = $db->query("select count(*) as c from patient_1 where status=1 order_date>=" . $v[0] . " and order_date<=" . $v[1] . " ", 1, "c");
foreach ($kf_arr as $me => $num) {
$data[$k][$me] = $db->query("select count(*) as c from patient_1 where status=1 author='{$me}' and order_date>=" . $v[0] . " and order_date<=" . $v[1] . " ", 1, "c");
}
}
} else if ($type == 5) {
$arr = array();
$arr["��"] = $db->query("select from_unixtime(order_date,'%k') as sd,count(from_unixtime(order_date,'%k')) as c from patient_1 where status=1 order_date>=" . $tb . " and order_date<=" . $te . " group by from_unixtime(order_date,'%k')", "sd", "c");
foreach ($kf_arr as $me => $num) {
$arr[$me] = $db->query("select from_unixtime(order_date,'%k') as sd,count(from_unixtime(order_date,'%k')) as c from patient_1 where author='{$me}' and status=1 order_date>=" . $tb . " and order_date<=" . $te . " group by from_unixtime(order_date,'%k')", "sd", "c");
}
$data = array();
foreach ($final_dt_arr as $k => $v) {
$data[$k]["��"] = intval($arr["��"][$v]);
foreach ($kf_arr as $me => $num) {
$data[$k][$me] = intval($arr[$me][$v]);
}
}
} */
//�������������½�
foreach ($kf_arr as $key => $value) {
    $tcount[]  = $value['c']; //����������ѯ��������
    $lkf_arr[] = $value['uid'];
}

$last_timebegin = strtotime(date("Y-m-01 00:00:00") . "-1 month");
$last_timeend   = strtotime(date("Y-m-d H:i:s") . "-1 month");
for ($n = 0; $n < 16; $n++) {
    $lkf_arr[$n];
    $hehe         = $db->query("select count(uid) as lkf from patient_1 where uid=" . $lkf_arr[$n] . " and status=1 and author!='���' and order_date>=$last_timebegin and order_date<=$last_timeend");
    $last_month[] = $hehe[0]['lkf']; //�ϸ������а��ձ��������Ļ�������
}
//$tcount��$last_month
foreach ($tcount as $key => $value) {
    $zengjian[] = $tcount[$key] - $last_month[$key]; //�������Ǽ���
}
foreach ($zengjian as $key => $value) {
    $baifenbi[] = $last_month[$key] == 0 ? 0 : abs(round($zengjian[$key] / $last_month[$key], 2) * 100); //�������Ǽ���
}
;
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="gb2312" />
    <meta name="viewport"
        content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no" />
    <link rel="stylesheet" href="css/style.css" />
    <title>ʵʱս��</title>
</head>

<body class="container">
    <div class="hd-bar">
        <div class="auto wrap">
            <div class="flex">
                <?php echo $summary_info; ?>
                <!--  <span>���ã�jiamei</span>
                <span>��ݣ������ƹ�</span>
                <span>Ȩ�ޣ���ѯ��Ա</span> -->
            </div>
            <div class="flex">
                <span>����
                    <?php echo date("Y-m-d"); ?>
                </span>
                <span>����<?php echo substr("��һ����������", date("w") * 2, 2); ?></span>
            </div>
        </div>
    </div>
    <div class="idx-ban" style="background: url(images/idx_01.jpg) no-repeat center; background-size: cover">
        <div class="wrap">
            <div class="lbox">
                <img src="images/idx_11.png" class="pic" alt="" />
                <ul class="list">
                    <li class="item">
                        <div class="fz">&nbsp;</div>
                        <div class="fz">�� ��</div>
                    </li>
                    <li class="item">
                        <div class="fz bold">����</div>
                        <div class="fz"><?php echo $kf_arr[1]['author']; ?></div>
                    </li>
                    <li class="item">
                        <div class="fz bold">״Ԫ</div>
                        <div class="fz">
                            <?php echo $kf_arr[0]['author']; ?>
                        </div>
                    </li>
                    <li class="item">
                        <div class="fz bold">̽��</div>
                        <div class="fz"><?php echo $kf_arr[2]['author']; ?></div>
                    </li>
                </ul>
            </div>
            <div class="rbox">
                <img src="images/idx_12.png" class="tp" alt="" />
                <div class="box box1">
                    <img src="images/idx_19.png" class="bei" alt="" />
                    <img src="images/<?php echo $kf_arr[1]['uid']; ?>.png" class="hdpic" alt="" />
                    <img src="images/idx_13.png" class="guan" alt="" />
                </div>
                <div class="box box2">
                    <img src="images/idx_20.png" class="bei" alt="">
                    <img src="images/<?php echo $kf_arr[0]['uid']; ?>.png" class="hdpic" alt="">
                    <img src="images/idx_14.png" class="guan" alt="">
                    <svg viewBox="0 0 440 220" class="txt">
                        <defs>
                            <path id="MyPath" d="M0 90 C200 130, 280 130, 440 90 "></path>
                        </defs>
                        <text font-size="45">
                            <textPath xlink:href="#MyPath" startOffset="50%"
                                style="fill: #cb0000; text-anchor: middle;">����ȵĵ�
                                <?php echo $tongji_meige[$kf_arr[0]['uid']] + 1; ?>��MVP
                            </textPath>
                        </text>
                    </svg>
                </div>
                <div class="box box3">
                    <img src="images/idx_21.png" class="bei" alt="" />
                    <img src="images/<?php echo $kf_arr[2]['uid']; ?>.png" class="hdpic" alt="" />
                    <img src="images/idx_15.png" class="guan" alt="" />
                </div>
            </div>
        </div>
    </div>
    <div class="idx-sec">
        <div class="auto">
            <div class="titbox">
                <h5 class="bt"><em>���°�<span>ʵʱ������</span></em></h5>
                <p class="en">The list is being updated in real-time this month</p>
            </div>
            <div class="tablebox">
                <div class="floatleft">
                    <ul>
                        <h3 class="title">δԤԼ�������Ͽ�</h3>
                        <li><a href="/v6/" class="red">δԤԼ���Ͽ�</a></li>
                    </ul>
                </div>
                <table class="table">
                    <thead>
                        <th>����<em>(Ranking)</em></th>
                        <th colspan="2">�ͷ�����<em>(Name)</em></th>
                    </thead>
                    <tbody>>
                        <tr>
                            <td class="td1"><img src="images/idx_03.png" class="icon" alt="" />��1��</td>
                            <td class="td2"><img src="images/kefu_<?php echo $kf_arr[0]['uid']; ?>.jpg" class="hdpic"
                                    alt="" />Name��<?php echo $kf_arr[0]['author']; ?></td>
                            <td class="td3">
                                <span class="tag <?php if ($zengjian[0] >= 0) {
                                    echo "up";
                                } else {
                                    echo "down";
                                }
                                ?>">������<i class="ic"></i>
                                    <?php echo $baifenbi[0]; ?>%
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="td1"><img src="images/idx_04.png" class="icon" alt="" />��2��</td>
                            <td class="td2"><img src="images/kefu_<?php echo $kf_arr[1]['uid']; ?>.jpg" class="hdpic"
                                    alt="" />Name��<?php echo $kf_arr[1]['author']; ?></td>
                            <td class="td3">
                                <span class="tag <?php if ($zengjian[1] >= 0) {
                                    echo "up";
                                } else {
                                    echo "down";
                                }
                                ?>">������<i class=" ic"></i><?php echo $baifenbi[1]; ?>%</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="td1"><img src="images/idx_05.png" class="icon" alt="" />��3��</td>
                            <td class="td2"><img src="images/kefu_<?php echo $kf_arr[2]['uid']; ?>.jpg" class="hdpic"
                                    alt="" />Name��<?php echo $kf_arr[2]['author']; ?></td>
                            <td class="td3">
                                <span class="tag <?php if ($zengjian[2] >= 0) {
                                    echo "up";
                                } else {
                                    echo "down";
                                }
                                ?>">������<i class=" ic"></i><?php echo $baifenbi[2]; ?>%</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="td1">��4��</td>
                            <td class="td2"><img src="images/kefu_<?php echo $kf_arr[3]['uid']; ?>.jpg" class="hdpic"
                                    alt="" />Name��<?php echo $kf_arr[3]['author']; ?></td>
                            <td class="td3">
                                <span class="tag <?php if ($zengjian[3] >= 0) {
                                    echo "up";
                                } else {
                                    echo "down";
                                }
                                ?>">������<i class="ic"></i><?php echo $baifenbi[3]; ?>%</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="td1">��5��</td>
                            <td class="td2"><img src="images/kefu_<?php echo $kf_arr[4]['uid']; ?>.jpg" class="hdpic"
                                    alt="" />Name��<?php echo $kf_arr[4]['author']; ?></td>
                            <td class="td3">
                                <span class="tag <?php if ($zengjian[4] >= 0) {
                                    echo "up";
                                } else {
                                    echo "down";
                                }
                                ?>">������<i class=" ic"></i><?php echo $baifenbi[4]; ?>%</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="td1">��6��</td>
                            <td class="td2"><img src="images/kefu_<?php echo $kf_arr[5]['uid']; ?>.jpg" class="hdpic"
                                    alt="" />Name��<?php echo $kf_arr[5]['author']; ?></td>
                            <td class="td3">
                                <span class="tag <?php if ($zengjian[5] >= 0) {
                                    echo "up";
                                } else {
                                    echo "down";
                                }
                                ?>">������<i class="ic"></i><?php echo $baifenbi[5]; ?>%</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="td1">��7��</td>
                            <td class="td2"><img src="images/kefu_<?php echo $kf_arr[6]['uid']; ?>.jpg" class="hdpic"
                                    alt="" />Name��<?php echo $kf_arr[6]['author']; ?></td>
                            <td class="td3">
                                <span class="tag <?php if ($zengjian[6] >= 0) {
                                    echo "up";
                                } else {
                                    echo "down";
                                }
                                ?>">������<i class="ic"></i><?php echo $baifenbi[6]; ?>%</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="td1">��8��</td>
                            <td class="td2"><img src="images/kefu_<?php echo $kf_arr[7]['uid']; ?>.jpg" class="hdpic"
                                    alt="" />Name��<?php echo $kf_arr[7]['author']; ?></td>
                            <td class="td3">
                                <span class="tag <?php if ($zengjian[7] >= 0) {
                                    echo "up";
                                } else {
                                    echo "down";
                                }
                                ?>">������<i class="ic"></i><?php echo $baifenbi[7]; ?>%</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="td1">��9��</td>
                            <td class="td2"><img src="images/kefu_<?php echo $kf_arr[8]['uid']; ?>.jpg" class="hdpic"
                                    alt="" />Name��<?php echo $kf_arr[8]['author']; ?></td>
                            <td class="td3">
                                <span class="tag <?php if ($zengjian[8] >= 0) {
                                    echo "up";
                                } else {
                                    echo "down";
                                }
                                ?>">������<i class="ic"></i><?php echo $baifenbi[8]; ?>%</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="td1">��10��</td>
                            <td class="td2"><img src="images/kefu_<?php echo $kf_arr[9]['uid']; ?>.jpg" class="hdpic"
                                    alt="" />Name��<?php echo $kf_arr[9]['author']; ?></td>
                            <td class="td3">
                                <span class="tag <?php if ($zengjian[9] >= 0) {
                                    echo "up";
                                } else {
                                    echo "down";
                                }
                                ?>">������<i class="ic"></i><?php echo $baifenbi[9]; ?>%</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="td1">��11��</td>
                            <td class="td2"><img src="images/kefu_<?php echo $kf_arr[10]['uid']; ?>.jpg" class="hdpic"
                                    alt="" />Name��<?php echo $kf_arr[10]['author']; ?></td>
                            <td class="td3">
                                <span class="tag <?php if ($zengjian[10] >= 0) {
                                    echo "up";
                                } else {
                                    echo "down";
                                }
                                ?>">������<i class="ic"></i><?php echo $baifenbi[10]; ?>%</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="td1">��12��</td>
                            <td class="td2"><img src="images/kefu_<?php echo $kf_arr[11]['uid']; ?>.jpg" class="hdpic"
                                    alt="" />Name��<?php echo $kf_arr[11]['author']; ?></td>
                            <td class="td3">
                                <span class="tag <?php if ($zengjian[11] >= 0) {
                                    echo "up";
                                } else {
                                    echo "down";
                                }
                                ?>">������<i class="ic"></i><?php echo $baifenbi[11]; ?>%</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="td1">��13��</td>
                            <td class="td2"><img src="images/kefu_<?php echo $kf_arr[12]['uid']; ?>.jpg" class="hdpic"
                                    alt="" />Name��<?php echo $kf_arr[12]['author']; ?></td>
                            <td class="td3">
                                <span class="tag <?php if ($zengjian[12] >= 0) {
                                    echo "up";
                                } else {
                                    echo "down";
                                }
                                ?>">������<i class="ic"></i><?php echo $baifenbi[12]; ?>%</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="td1">��14��</td>
                            <td class="td2"><img src="images/kefu_<?php echo $kf_arr[13]['uid']; ?>.jpg" class="hdpic"
                                    alt="" />Name��<?php echo $kf_arr[13]['author']; ?></td>
                            <td class="td3">
                                <span class="tag <?php if ($zengjian[13] >= 0) {
                                    echo "up";
                                } else {
                                    echo "down";
                                }
                                ?>">������<i class="ic"></i><?php echo $baifenbi[13]; ?>%</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="td1">��15��</td>
                            <td class="td2"><img src="images/kefu_<?php echo $kf_arr[14]['uid']; ?>.jpg" class="hdpic"
                                    alt="" />Name��<?php echo $kf_arr[14]['author']; ?></td>
                            <td class="td3">
                                <span class="tag <?php if ($zengjian[14] >= 0) {
                                    echo "up";
                                } else {
                                    echo "down";
                                }
                                ?>">������<i class="ic"></i><?php echo $baifenbi[14]; ?>%</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="td1">��16��</td>
                            <td class="td2"><img src="images/kefu_<?php echo $kf_arr[15]['uid']; ?>.jpg" class="hdpic"
                                    alt="" />Name��<?php echo $kf_arr[15]['author']; ?></td>
                            <td class="td3">
                                <span class="tag <?php if ($zengjian[15] >= 0) {
                                    echo "up";
                                } else {
                                    echo "down";
                                }
                                ?>">������<i class="ic"></i><?php echo $baifenbi[15]; ?>%</span>
                            </td>
                        </tr>
                        <tr class="line">
                            <td class="td1">��17��</td>
                            <td class="td2"><img src="images/kefu_<?php echo $kf_arr[16]['uid']; ?>.jpg" class="hdpic"
                                    alt="" />Name��<?php echo $kf_arr[16]['author']; ?></td>
                            <td class="td3">
                                <span class="tag">*���Ŭ��Ŷ��</span>
                            </td>
                        </tr>
                        <tr class="line">
                            <td class="td1">��18��</td>
                            <td class="td2"><img src="images/kefu_<?php echo $kf_arr[17]['uid']; ?>.jpg" class="hdpic"
                                    alt="" />Name��<?php echo $kf_arr[17]['author']; ?></td>
                            <td class="td3">
                                <span class="tag">*���Ŭ��Ŷ��</span>
                            </td>
                        </tr>
                        <tr class="line">
                            <td class="td1">��19��</td>
                            <td class="td2"><img src="images/kefu_<?php echo $kf_arr[18]['uid']; ?>.jpg" class="hdpic"
                                    alt="" />Name��<?php echo $kf_arr[18]['author']; ?></td>
                            <td class="td3">
                                <span class="tag">*���Ŭ��Ŷ��</span>
                            </td>
                        </tr>
                        <!--<tr class="line">
                            <td class="td1">��20��</td>
                            <td class="td2"><img src="images/kefu_<?php echo $kf_arr[19]['uid']; ?>.jpg" class="hdpic"
                                    alt="" />Name��<?php echo $kf_arr[19]['author']; ?></td>
                            <td class="td3">
                                <span class="tag">*���Ŭ��Ŷ��</span>
                            </td>
                        </tr>-->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="idx-pic">
        <img src="images/idx_09.jpg" alt="" class="img" />
    </div>
    <script src="js/jquery.min.js"></script>
    <script>
    let dom = $('.tablebox')
    let domnum = dom[0].offsetHeight;
    let cur = null
    $(window).scroll(function(event) {
        let Ttop = dom[0].offsetTop
        let toTop = $(window).scrollTop()
        if (Ttop - toTop < window.innerHeight / 1.5) {
            if (cur == null) {
                cur = toTop
            }
        }
        if (cur != null) {
            if (toTop - cur > 0 && toTop - cur < domnum) {
                $('.floatleft').css('top', toTop - cur + 'px')
                $('.floatleft').css('bottom', 'auto')
            } else if (toTop - cur > domnum) {

                $('.floatleft').css('top', 'auto')
                $('.floatleft').css('bottom', '0')
            } else {
                $('.floatleft').css('bottom', 'auto')
                $('.floatleft').css('top', 0 + 'px')
            }
        }


    });
    </script>
</body>

</html>