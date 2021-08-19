<?php

if(!$is_user_logged) {
  $is_user_moder = false;
  return;
}

require_once("db_connect.php");
require_once("db_settings.php");
require_once("core.php");

$check_is_user_moder = $pdo->prepare("SELECT id FROM ".$prefix."_users WHERE login=:login and ismoder=1");
$check_is_user_moder->bindParam(":login", $_COOKIE["user_login"]);
$check_is_user_moder->execute();

if( $check_is_user_moder->rowCount() == 1 ) {
  $is_user_moder = true;
} else {
  $is_user_moder = false;
}

?>