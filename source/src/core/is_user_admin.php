<?php

if(!$is_user_logged) {
  $is_user_admin = false;
  return;
}

require_once("db_connect.php");
require_once("db_settings.php");
require_once("core.php");

$check_is_user_admin = $pdo->prepare("SELECT id FROM ".$prefix."_users WHERE login=:login and isadmin=1");
$check_is_user_admin->bindParam(":login", $_COOKIE["user_login"]);
$check_is_user_admin->execute();

if( $check_is_user_admin->rowCount() == 1 ) {
  $is_user_admin = true;
} else {
  $is_user_admin = false;
}

?>