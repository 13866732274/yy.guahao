<?php
/*
// 说明: 更新父窗口标题栏（解决跨域问题）
// 作者: 幽兰 (934834734@qq.com)
*/
?>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<script language="javascript">
parent.parent.set_box_title("<?php echo urldecode($_GET["title"]); ?>");
</script>
