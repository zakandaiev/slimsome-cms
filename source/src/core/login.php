<?php

$login = filter_var(trim($_POST["login"]), FILTER_SANITIZE_STRING);
$password = filter_var(trim($_POST["password"]), FILTER_SANITIZE_STRING);

if(empty($login)) {
  echo json_encode(array("success" => 0, "error" => "Укажите логин"));
  exit();
}
if(empty($password)) {
  echo json_encode(array("success" => 0, "error" => "Укажите пароль"));
  exit();
}

require_once("db_connect.php");
require_once("db_settings.php");
require_once("core.php");

$login_query = $pdo->prepare("SELECT id FROM ".$prefix."_users WHERE login=:login and password=:password LIMIT 1");

$login_query->bindParam(":login", $login);
$login_query->bindParam(":password", $password);

$login_query->execute();

if( $login_query->rowCount() == 1 ) {
  setLoginCookie($login);
  $uid = $login_query->fetch(PDO::FETCH_LAZY)->id;
  $uip = filter_var($_SERVER["REMOTE_ADDR"], FILTER_VALIDATE_IP);
  $update_ip_query = $pdo->prepare("
    UPDATE ".$prefix."_users SET 
      ip=:uip,
      last_sign=CURRENT_TIMESTAMP
    WHERE id=:uid
  ");
  $update_ip_query->bindParam(":uip", $uip);
  $update_ip_query->bindParam(":uid", $uid);
  $update_ip_query->execute();
  echo json_encode(array("success" => 1));
} else {
  echo json_encode(array("success" => -1, "error" => "Неправильный логин или пароль"));
  exit();
}

?>
