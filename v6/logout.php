<?php
//
// - зїеп: weelia
//
require "lib/set_env.php";
$db->query("update sys_admin set online='0', lastactiontime='0' where id=$uid limit 1");

$_SESSION[$cfgSessionName] = array();
$_SESSION = array();
session_destroy();

setcookie("last_visit_src", "", -1, "/");

header("location:login.php");
?>