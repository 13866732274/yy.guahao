<?php
// --------------------------------------------------------
// - ����˵�� : ��վ����ϵͳ �����ļ�
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2008-06-24 13:41
// --------------------------------------------------------
@header("Content-type: text/html; charset=gb2312");

$cfgSessionName = "poa_admin"; //Session������

// ������Ϣ:
$cfgSiteName = "�ѿͻ�����ϵͳ"; //վ������
$cfgShowQuickLinks = 1; //�Ƿ���ʾ��ݼ�(ȫ������)
$cfgDefaultPageSize = 25; //Ĭ�Ϸ�ҳ��(�б�δ��дʱʹ�ô�����)

//�Ƿ�رղ�����¼���� Ĭ��������ǿ�����
$cfgLogClose = 0;

$sys_super_admin = "zhuwenya system"; //����ÿո����

// ��ɫ����:
$aTitleColor = array("" => "Ĭ��", "fuchsia" => "�Ϻ�ɫ", "red" => "��ɫ", "green" => "��ɫ", "blue" => "��ɫ",
	"orange" => "�Ȼ�ɫ", "darkviolet" => "������ɫ", "silver" => "��ɫ", "maroon" => "��ɫ", "olive" => "���ɫ",
	"navy" => "������", "purple" => "��ɫ", "coral" => "ɺ��ɫ", "crimson" => "���ɫ", "gold" => "��ɫ", "black" => "��ɫ");

// ������ı�ͷ:
$aOrderType = array(0 => "", 1 => "asc", 2 => "desc");
$aOrderTips = array(0 => "ȡ������", 1 => "����С��������", 2 => "���Ӵ�С����");
$aOrderFlag = array(0 => "", 1 => "��", 2 => "��");


// ������Ȩ���壬�ô�Ҳ��Ӧ��ҳ��ͳ��������ʾȨ��
$data_power_arr = array("all" => "������", "web" => "����", "tel" => "�绰", "dy" => "��ҽ", "qh" => "��");

// ״̬����:
$sex_status = array(0 => "δ֪", 1 => "��", 2 => "Ů");

// ҽԺ����:
$status_array = array(0 => '�ȴ�', 1 => '�ѵ�', -1 => 'δ��', -2 => "�ѵ�δ��");

$media_from_array = explode(' ', '�绰 ���� ��ֽ ���� ���� �绰 ����');

$oprate_type = array("add"=>"����", "delete"=>"ɾ��", "edit"=>"�޸�", "login"=>"�û���¼", "logout"=>"�û��˳�");

$account_array = explode(" ","�˻����� �˻����� �˻����� �˻����� �˻����� �˻�����");

// ��������:
$debugs = array("f6b449025027c494238186b01c3e64875b25c766", "f4e107dae517698f5c59f2514d85d79ff292573b");

// ���Ż���
$youhuazu_array = explode(" ", "�Ż���1 �Ż���2");

// ������Դ
$wuxian_from_arr = array("����ͨ����", "�绰����");

// �������ظ�����������:
$cfgRepeatTipDays = 90; //��������
$cfgRepeatDenyDays = 30; //��ֹ�ύ������

$guahao_config_arr = array(
	"patient_add" => "��������",
	"patient_edit" => "�޸Ĳ���",
	"patient_delete" => "ɾ������",
	"set_come" => "����Ժ",
	"set_huifang_tixing" => "��ط�����",
	"huifang" => "�ط�",
	"upload_luyin" => "��¼��",
	"set_xiaofei" => "������",
	"set_guiji" => "��켣",
	"set_zixun_group" => "����ѯ����",
	"move_keshi" => "ת����",
	"set_tuiguangren" => "���ƹ���",
	"set_huifang_renwu" => "����ط�����",
);

// ԤԼ��ʽ��
$order_type_arr = array(
	1 => "����ԤԼ",
	2 => "�绰ԤԼ",
	3 => "��Ȼ����",
);

// ԤԼ���
$web_soft_arr = array(
	"swt" => "����ͨ",
	"dh" => "�绰",
	"qq" => "QQ",
	"wx" => "΢��",
	"dx" => "����",
	"catch" => "�ֻ�ץȡ",
	"ditu" => "��ͼ",
	"kst" => "����ͨ",
	"sq" => "�ٶ�����",
	"qt" => "����",
);


// �Ż���
$youhua_group_arr = array(
	"�Ż�1",
	"�Ż�2",
	"�Ż�3",
);

// �켣:
$guiji_arr = array(
	1 => "�ٶ�PC����",
	2 => "�ٶ����߾���",
	26 => "�ٶ�����",
	21 => "�ѹ�",
	24 => "����",
	5 => "����ƽ̨����",
	11 => "�����Ż�",
	9 => "�޹켣",
	10 => "����Ӫ��",
	33 => "���ݿ�Ӫ��",
	38 => "���۵绰",
	40 => "�����켣",
);


$shichang_arr = explode(",", "��,���,�Ż�ȯ,ת��,����,ֽ��");

$zx_group_arr = array(
	"A" => "A��",
	"B" => "B��",
	"C" => "C��",
	"D" => "D��",
	"E" => "E��",
	"F" => "F��",
);


$special_config = array(
	"index_dingzhi" => "��ҳ����",
	"is_output" => "��������",
	"edit_come_patient" => "�޸��ѵ�����",
	"delete_patient" => "ɾ��δ������",
	"delete_come_patient" => "ɾ����ɾ����",
	"delete_ku_patient" => "ɾ�����Ͽ⻼��",
	"show_tel" => "�鿴δ������",
	"show_come_tel" => "�鿴�ѵ�����",
	"show_card_id" => "�鿴���֤����",
	"modify_count_data" => "�޸�����͵绰ͳ������",
	"show_zixun_yudao" => "��ʾ��ѯ����Ԥ��",
	"show_index_info" => "��ʾ��ҳ�ײ�����",
	//"show_mobile_ditu" => "��ͼץȡ",
	//"show_qihua_zhuaqu" => "��ץȡ",
);

$special_config_default_value = array(
	"show_zixun_yudao" => 1,
	"show_index_info" => 1,
	"index_dingzhi" => 1,
);

?>