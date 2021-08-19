<?php

if (!isset($_COOKIE["user_login"]) || empty($_COOKIE["user_login"]) || !isset($_COOKIE["user_hash"]) || empty($_COOKIE["user_hash"])) {
  $is_user_logged = false;
  return;
}

require_once("db_connect.php");
require_once("db_settings.php");
require_once("core.php");

if (loginSign($_COOKIE["user_login"]) !== $_COOKIE["user_hash"]) {
  $is_user_logged = false;
  return;
}

$check_is_user_logged = $pdo->prepare("SELECT login FROM ".$prefix."_users WHERE login=:login");
$check_is_user_logged->bindParam(":login",$_COOKIE["user_login"]);
$check_is_user_logged->execute();

if( $check_is_user_logged->rowCount() == 1 ) {
  $is_user_logged = true;
} else {
  $is_user_logged = false;
}

?>