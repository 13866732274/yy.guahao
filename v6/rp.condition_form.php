<?php
/*
// 作者: 幽兰 (weelia@126.com)
*/

$se = $_SESSION[$cfgSessionName]["rp_condition"];
?>

<script type="text/javascript">
function check_condition(f) {
    // 起始
    if (f.b_year.style.display != "none" && f.b_year.value == '') {
        msg_box("请设置起始年份");
        f.b_year.focus();
        return false;
    }
    if (f.b_month.style.display != "none" && f.b_month.value == '') {
        msg_box("请设置起始月份");
        f.b_month.focus();
        return false;
    }
    if (f.b_day.style.display != "none" && f.b_day.value == '') {
        msg_box("请设置起始日期");
        f.b_day.focus();
        return false;
    }

    // 结束
    if (f.e_year.style.display != "none" && f.e_year.value == '') {
        msg_box("请设置起始年份");
        f.e_year.focus();
        return false;
    }
    if (f.e_month.style.display != "none" && f.e_month.value == '') {
        msg_box("请设置起始月份");
        f.e_month.focus();
        return false;
    }
    if (f.e_day.style.display != "none" && f.e_day.value == '') {
        msg_box("请设置起始日期");
        f.e_day.focus();
        return false;
    }

    // 日期的前后检测
    var b = e = '';
    var ty = f.type.value;
    if (ty == 1 || ty == 2 || ty == 4 || ty == 5) {
        b += f.b_year.value;
        e += f.e_year.value;
    }
    if (ty == 2 || ty == 4 || ty == 5) {
        b += f.b_month.value < 10 ? ('0' + f.b_month.value) : f.b_month.value;
        e += f.e_month.value < 10 ? ('0' + f.e_month.value) : f.e_month.value;
    }
    if (ty == 4 || ty == 5) {
        b += f.b_day.value < 10 ? ('0' + f.b_day.value) : f.b_day.value;
        e += f.e_day.value < 10 ? ('0' + f.e_day.value) : f.e_day.value;
    }
    if (b > e) {
        alert("开始日期需在结束日期之前，请重新设置日期。");
        return false;
    }

    byid("condition_submit").disabled = true;
    return true;
}

// 1=>"按年统计", 2=>"按月统计", 4=>"按日统计", 5=>"按时间段统计"
function reset_date_type(ty) {
    byid("b_year").style.display = "none";
    byid("b_month").style.display = "none";
    byid("b_day").style.display = "none";
    byid("e_year").style.display = "none";
    byid("e_month").style.display = "none";
    byid("e_day").style.display = "none";
    if (ty == 1 || ty == 2 || ty == 4 || ty == 5) {
        byid("b_year").style.display = "";
        byid("e_year").style.display = "";
    }
    if (ty == 2 || ty == 4 || ty == 5) {
        byid("b_month").style.display = "";
        byid("e_month").style.display = "";
    }
    if (ty == 4 || ty == 5) {
        byid("b_day").style.display = "";
        byid("e_day").style.display = "";
    }
}
</script>

<div id="rp_condition_form">
    <form id="condition_form" method="GET" onsubmit="return check_condition(this)">
        <b>报表条件：</b>
        <select name="type" id="rp_type" class="combo" onchange="reset_date_type(this.value)">
            <option value="" style="color:gray">-统计类型-</option>
            <?php echo list_option($type_arr, "_key_", "_value_", noe($_GET["type"], $se["type"], 2)); ?>
        </select>
        &nbsp;
        <select name="b_year" id="b_year" class="combo" style="display:none">
            <option value="" style="color:gray">-年-</option>
            <?php echo list_option($y_array, "_value_", "_value_", noe($_GET["b_year"], $se["b_year"], date("Y"))); ?>
        </select>
        <select name="b_month" id="b_month" class="combo" style="display:none">
            <option value="" style="color:gray">-月-</option>
            <?php echo list_option($m_array, "_value_", "_value_", noe($_GET["b_month"], $se["b_month"], 1)); ?>
        </select>
        <select name="b_day" id="b_day" class="combo" style="display:none">
            <option value="" style="color:gray">-日-</option>
            <?php echo list_option($d_array, "_value_", "_value_", noe($_GET["b_day"], $se["b_day"], 1)); ?>
        </select>
        ～
        <select name="e_year" id="e_year" class="combo" style="display:none">
            <option value="" style="color:gray">-年-</option>
            <?php echo list_option($y_array, "_value_", "_value_", noe($_GET["e_year"], $se["e_year"], date("Y"))); ?>
        </select>
        <select name="e_month" id="e_month" class="combo" style="display:none">
            <option value="" style="color:gray">-月-</option>
            <?php echo list_option($m_array, "_value_", "_value_", noe($_GET["e_month"], $se["e_month"], date("n"))); ?>
        </select>
        <select name="e_day" id="e_day" class="combo">
            <option value="" style="color:gray">-日-</option style="display:none">
            <?php echo list_option($d_array, "_value_", "_value_", noe($_GET["e_day"], $se["e_day"], date("j"))); ?>
        </select>
        &nbsp;
        <select name="timetype" class="combo">
            <option value="" style="color:gray">-时间类型-</option>
            <?php echo list_option($timetype_arr, "_key_", "_value_", noe($_GET["timetype"], $se["timetype"], "order_date")); ?>
        </select>
        <select name="part" class="combo">
            <option value="" style="color:gray">-部门-</option>
            <?php echo list_option($part_arr, "_key_", "_value_", noe($_GET["part"], $se["part"])); ?>
        </select>
        <select name="media" class="combo">
            <option value="" style="color:gray">-媒体来源-</option>
            <?php echo list_option($media_arr, "_value_", "_value_", noe($_GET["media"], $se["media"])); ?>
        </select>
        <select name="come" class="combo">
            <option value="" style="color:gray">-到院状态-</option>
            <?php echo list_option($come_arr, "_key_", "_value_", noe($_GET["come"], $se["come"])); ?>
        </select>
        <select name="account" class="combo">
            <option value="" style="color:gray">-帐号-</option>
            <?php echo list_option($account_arr, "_value_", "_value_", noe($_GET["account"], $se["account"])); ?>
        </select>
        <input type="hidden" name="op" value="report" />
        <input type="submit" id="condition_submit" value="查询" class="button" />
    </form>
</div>

<?php if ($html_tip) { ?>
<div style="margin-top:10px; text-align:center; color:red;">注意：<?php echo $html_tip; ?> </div>
<?php } ?>

<script type="text/javascript">
if (byid("rp_type").value != '') {
    reset_date_type(byid("rp_type").value);
}
</script>

<?php if (empty($_GET["op"])) { ?>
<!-- 点击进入则自动开始查询 -->
<script type="text/javascript">
byid("condition_form").submit();
byid("condition_submit").disabled = true;
msg_box("报表查询中，请稍候", 1);
</script>
<?php } ?>