<?php

require_once("db_connect.php");
require_once("db_settings.php");
require_once("core.php");
require_once("is_user_logged.php");
require_once("is_user_admin.php");
require_once("is_user_moder.php");

if (!$is_user_logged) {
  echo json_encode(array("success" => -1, "error" => "У вас нет прав"));
  exit();
}

if (!isset($_POST["login_old"]) || empty($_POST["login_old"]) ||
    !isset($_POST["pass_check"]) || empty($_POST["pass_check"]) ||
    !isset($_POST["uid_check"]) || empty($_POST["uid_check"])) {
  echo json_encode(array("success" => 0, "error" => "Неверные данные пользователя"));
  exit();
}

$login_old = filter_var(trim($_POST["login_old"]), FILTER_SANITIZE_STRING);
$uid_check = filter_var(trim($_POST["uid_check"]), FILTER_SANITIZE_NUMBER_INT);
$pass_check = filter_var(trim($_POST["pass_check"]), FILTER_SANITIZE_STRING);

$login = filter_var(trim($_POST["login"]), FILTER_SANITIZE_STRING);
$email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_STRING);
$name = filter_var(trim($_POST["name"]), FILTER_SANITIZE_STRING);
$nick = filter_var(trim($_POST["nick"]), FILTER_SANITIZE_STRING);
$steam_id = filter_var(trim($_POST["steam_id"]), FILTER_SANITIZE_STRING);

$service_bind_type = filter_var(trim($_POST["service_bind_type"]), FILTER_SANITIZE_STRING);
if (!isset($service_bind_type) || empty($service_bind_type)) {
  echo json_encode(array("success" => 0, "error" => "Укажите способ привязки"));
  exit();
}
if ($service_bind_type == "nick_pass") {
  $service_bind_type = 1;
} else if ($service_bind_type == "steam_pass") {
  $service_bind_type = 2;
} else {
  echo json_encode(array("success" => 0, "error" => "Укажите способ привязки"));
  exit();
}

$validate_login = validateLogin($login);
$validate_email = validateEmail($email);
$validate_name = validateUserName($name);
$validate_nick = validateUserNick($nick);
$validate_steam_id = validateSteamId($steam_id);

if($validate_login !== "valid") {
  echo json_encode(array("success" => 0, "error" => $validate_login));
  exit();
}
if($validate_email !== "valid") {
  echo json_encode(array("success" => 0, "error" => $validate_email));
  exit();
}
if($validate_name !== "valid") {
  echo json_encode(array("success" => 0, "error" => $validate_name));
  exit();
}
if($service_bind_type == 1 && $validate_nick !== "valid") {
  echo json_encode(array("success" => 0, "error" => $validate_nick));
  exit();
}
if($service_bind_type == 2 && $validate_steam_id !== "valid") {
  echo json_encode(array("success" => 0, "error" => $validate_steam_id));
  exit();
}

$user_info_update_query = $pdo->prepare("
  UPDATE ".$prefix."_users SET
    login=:login,
    nick=:nick,
    steam_id=:steam_id,
    service_bind_type=:service_bind_type,
    name=:name,
    email=:email
  WHERE login=:login_old and id=:uid_check and password=:pass_check;
");

$user_info_update_query->bindParam(":login_old", $login_old);
$user_info_update_query->bindParam(":uid_check", $uid_check);
$user_info_update_query->bindParam(":pass_check", $pass_check);

$user_info_update_query->bindParam(":login", $login);
$user_info_update_query->bindParam(":email", $email);

if (empty($nick)) {
  $nick = null;
  $user_info_update_query->bindParam(":nick", $nick, PDO::PARAM_NULL);
} else {
  $user_info_update_query->bindParam(":nick", $nick);
}
if (empty($steam_id)) {
  $steam_id = null;
  $user_info_update_query->bindParam(":steam_id", $steam_id, PDO::PARAM_NULL);
} else {
  $user_info_update_query->bindParam(":steam_id", $steam_id);
}

$user_info_update_query->bindParam(":service_bind_type", $service_bind_type);

$user_info_update_query->bindParam(":name", $name);

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
  $user_info_update_query->execute();
  if ($user_info_update_query->rowCount() >= 1) {
    setLoginCookie($login);
    if (isUserActive($uid_check, null)) {
      echo json_encode(array("success" => 1, "upload" => 1));
    } else {
      echo json_encode(array("success" => 1, "upload" => 0));
    }
  } else {
    echo json_encode(array("success" => 0, "error" => "Вы ничего не изменили"));
  }
} catch(PDOException $error) { 
  if (preg_match("/Duplicate entry .+ for key '(.+)'/", $error->getMessage(), $matches)) {
    $arr_column_names = array(
      "id" => "ID пользователя",
      "login" => "логин",
      "email" => "e-mail",
      "nick" => "ник",
      "steam_id" => "Steam ID"
    );
    if (!array_key_exists($matches[1], $arr_column_names)) {
      $column_name = $matches[1];
    } else {
      $column_name = $arr_column_names[$matches[1]];
    }
    echo json_encode(array("success" => 0, "error" => "Такой ".$column_name." уже занят"));
  } else {
    echo json_encode(array("success" => 0, "error" => $error->getMessage()));
  }
}

?>