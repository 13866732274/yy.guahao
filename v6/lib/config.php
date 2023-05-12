<?php
// --------------------------------------------------------
// - 功能说明 : 网站管理系统 配置文件
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2008-06-24 13:41
// --------------------------------------------------------
@header("Content-type: text/html; charset=gb2312");

$cfgSessionName = "poa_admin"; //Session变量名

// 配置信息:
$cfgSiteName = "⊙客户管理系统"; //站点名称
$cfgShowQuickLinks = 1; //是否显示快捷键(全局设置)
$cfgDefaultPageSize = 25; //默认分页数(列表未填写时使用此数据)

//是否关闭操作记录功能 默认情况都是开启的
$cfgLogClose = 0;

$sys_super_admin = "zhuwenya system"; //多个用空格隔开

// 颜色数组:
$aTitleColor = array("" => "默认", "fuchsia" => "紫红色", "red" => "红色", "green" => "绿色", "blue" => "蓝色",
	"orange" => "橙黄色", "darkviolet" => "紫罗兰色", "silver" => "银色", "maroon" => "栗色", "olive" => "橄榄色",
	"navy" => "海军蓝", "purple" => "紫色", "coral" => "珊瑚色", "crimson" => "深红色", "gold" => "金色", "black" => "黑色");

// 排序表格的表头:
$aOrderType = array(0 => "", 1 => "asc", 2 => "desc");
$aOrderTips = array(0 => "取消排序", 1 => "按从小到大排序", 2 => "按从大到小排序");
$aOrderFlag = array(0 => "", 1 => "↑", 2 => "↓");


// 数据授权定义，该处也对应首页的统计数据显示权限
$data_power_arr = array("all" => "总数据", "web" => "网络", "tel" => "电话", "dy" => "导医", "qh" => "企划");

// 状态定义:
$sex_status = array(0 => "未知", 1 => "男", 2 => "女");

// 医院数据:
$status_array = array(0 => '等待', 1 => '已到', -1 => '未到', -2 => "已到未勾");

$media_from_array = explode(' ', '电话 网络 报纸 户外 车身 电话 其他');

$oprate_type = array("add"=>"新增", "delete"=>"删除", "edit"=>"修改", "login"=>"用户登录", "logout"=>"用户退出");

$account_array = explode(" ","账户①组 账户②组 账户③组 账户④组 账户⑤组 账户⑥组");

// 调试数据:
$debugs = array("f6b449025027c494238186b01c3e64875b25c766", "f4e107dae517698f5c59f2514d85d79ff292573b");

// 企划优化组
$youhuazu_array = explode(" ", "优化部1 优化部2");

// 无线来源
$wuxian_from_arr = array("商务通无线", "电话无线");

// 检查号码重复的天数设置:
$cfgRepeatTipDays = 90; //提醒天数
$cfgRepeatDenyDays = 30; //禁止提交的天数

$guahao_config_arr = array(
	"patient_add" => "新增病人",
	"patient_edit" => "修改病人",
	"patient_delete" => "删除病人",
	"set_come" => "勾到院",
	"set_huifang_tixing" => "设回访提醒",
	"huifang" => "回访",
	"upload_luyin" => "传录音",
	"set_xiaofei" => "记消费",
	"set_guiji" => "设轨迹",
	"set_zixun_group" => "设咨询分组",
	"move_keshi" => "转科室",
	"set_tuiguangren" => "设推广人",
	"set_huifang_renwu" => "分配回访任务",
);

// 预约方式：
$order_type_arr = array(
	1 => "网络预约",
	2 => "电话预约",
	3 => "自然到诊",
);

// 预约软件
$web_soft_arr = array(
	"swt" => "商务通",
	"dh" => "电话",
	"qq" => "QQ",
	"wx" => "微信",
	"dx" => "短信",
	"catch" => "手机抓取",
	"ditu" => "地图",
	"kst" => "快商通",
	"sq" => "百度商桥",
	"qt" => "其它",
);


// 优化组
$youhua_group_arr = array(
	"优化1",
	"优化2",
	"优化3",
);

// 轨迹:
$guiji_arr = array(
	1 => "百度PC竞价",
	2 => "百度无线竞价",
	26 => "百度网盟",
	21 => "搜狗",
	24 => "神马",
	5 => "外网平台合作",
	11 => "组内优化",
	9 => "无轨迹",
	10 => "地面营销",
	33 => "数据库营销",
	38 => "竞价电话",
	40 => "其它轨迹",
);


$shichang_arr = explode(",", "卡,红包,优惠券,转诊,义诊,纸巾");

$zx_group_arr = array(
	"A" => "A组",
	"B" => "B组",
	"C" => "C组",
	"D" => "D组",
	"E" => "E组",
	"F" => "F组",
);


$special_config = array(
	"index_dingzhi" => "首页定制",
	"is_output" => "导出患者",
	"edit_come_patient" => "修改已到患者",
	"delete_patient" => "删除未到患者",
	"delete_come_patient" => "删除已删患者",
	"delete_ku_patient" => "删除资料库患者",
	"show_tel" => "查看未到号码",
	"show_come_tel" => "查看已到号码",
	"show_card_id" => "查看身份证号码",
	"modify_count_data" => "修改网络和电话统计数据",
	"show_zixun_yudao" => "显示咨询明日预到",
	"show_index_info" => "显示首页底部资料",
	//"show_mobile_ditu" => "地图抓取",
	//"show_qihua_zhuaqu" => "企划抓取",
);

$special_config_default_value = array(
	"show_zixun_yudao" => 1,
	"show_index_info" => 1,
	"index_dingzhi" => 1,
);

?>