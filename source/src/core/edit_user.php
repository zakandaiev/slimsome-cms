<?php

require_once("db_connect.php");
require_once("db_settings.php");
require_once("core.php");
require_once("is_user_logged.php");
require_once("is_user_admin.php");
require_once("is_user_moder.php");

if (!$is_user_admin) {
  echo json_encode(array("success" => -1, "error" => "У вас нет прав"));
  exit();
}

if (!isset($_POST["user_id"]) || empty($_POST["user_id"])) {
  echo json_encode(array("success" => 0, "error" => "Неверные данные пользователя"));
  exit();
}

$user_id = filter_var(trim($_POST["user_id"]), FILTER_SANITIZE_NUMBER_INT);

$login = filter_var(trim($_POST["login"]), FILTER_SANITIZE_STRING);
$password = filter_var(trim($_POST["password"]), FILTER_SANITIZE_STRING);
$email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_STRING);
$name = filter_var(trim($_POST["name"]), FILTER_SANITIZE_STRING);
$nick = filter_var(trim($_POST["nick"]), FILTER_SANITIZE_STRING);
$steam_id = filter_var(trim($_POST["steam_id"]), FILTER_SANITIZE_STRING);

if (isset($_POST["service_nolimit"]) && $_POST["service_nolimit"] == "on") {
  $service_nolimit = true;
} else {
  $service_nolimit = false;
}
if (isset($_POST["isadmin"]) && $_POST["isadmin"] == "on") {
  $isadmin = true;
} else {
  $isadmin = false;
}
if (isset($_POST["ismoder"]) && $_POST["ismoder"] == "on") {
  $ismoder = true;
} else {
  $ismoder = false;
}

$validate_login = validateLogin($login);
$validate_password = validatePassword($password);
$validate_email = validateEmail($email);
$validate_name = validateUserName($name);
$validate_nick = validateUserNick($nick);
$validate_steam_id = validateSteamId($steam_id);

if($validate_login !== "valid") {
  echo json_encode(array("success" => 0, "error" => $validate_login));
  exit();
}
if($validate_password !== "valid") {
  echo json_encode(array("success" => 0, "error" => $validate_password));
  exit();
}
if(!empty($email) && $validate_email !== "valid") {
  echo json_encode(array("success" => 0, "error" => $validate_email));
  exit();
}
if(!empty($name) && $validate_name !== "valid") {
  echo json_encode(array("success" => 0, "error" => $validate_name));
  exit();
}
if(!empty($nick) && $validate_nick !== "valid") {
  echo json_encode(array("success" => 0, "error" => $validate_nick));
  exit();
}
if(!empty($steam_id) && $validate_steam_id !== "valid") {
  echo json_encode(array("success" => 0, "error" => $validate_steam_id));
  exit();
}

$user_update_query = $pdo->prepare("
  UPDATE ".$prefix."_users SET
    login=:login,
    password=:password,
    email=:email,
    nick=:nick,
    steam_id=:steam_id,
    name=:name,
    service_id=:service_id,
    service_start=:service_start,
    service_end=:service_end,
    service_nolimit=:service_nolimit,
    service_bind_type=:service_bind_type,
    isadmin=:isadmin,
    ismoder=:ismoder
  WHERE id=:user_id;
");

$user_update_query->bindParam(":user_id", $user_id);
$user_update_query->bindParam(":login", $login);
$user_update_query->bindParam(":password", $password);
if (empty($email)) {
  $email = null;
  $user_update_query->bindParam(":email", $email, PDO::PARAM_NULL);
} else {
  $user_update_query->bindParam(":email", $email);
}
if (empty($nick)) {
  $nick = null;
  $user_update_query->bindParam(":nick", $nick, PDO::PARAM_NULL);
} else {
  $user_update_query->bindParam(":nick", $nick);
}
if (empty($steam_id)) {
  $steam_id = null;
  $user_update_query->bindParam(":steam_id", $steam_id, PDO::PARAM_NULL);
} else {
  $user_update_query->bindParam(":steam_id", $steam_id);
}
if (empty($name)) {
  $name = null;
  $user_update_query->bindParam(":name", $name, PDO::PARAM_NULL);
} else {
  $user_update_query->bindParam(":name", $name);
}
if (!isset($_POST["service_id"]) || empty($_POST["service_id"])) {
  $service_id = null;
  $user_update_query->bindParam(":service_id", $service_id, PDO::PARAM_NULL);
} else {
  $user_update_query->bindParam(":service_id", $_POST["service_id"]);
}
if (!isset($_POST["service_start"]) || empty($_POST["service_start"])) {
  $service_start = null;
  $user_update_query->bindParam(":service_start", $service_start, PDO::PARAM_NULL);
} else {
  $user_update_query->bindParam(":service_start", $_POST["service_start"]);
}
if (!isset($_POST["service_end"]) || empty($_POST["service_end"])) {
  $service_end = null;
  $user_update_query->bindParam(":service_end", $service_end, PDO::PARAM_NULL);
} else {
  $user_update_query->bindParam(":service_end", $_POST["service_end"]);
}
$user_update_query->bindParam(":service_nolimit", $service_nolimit, PDO::PARAM_BOOL);
if (!isset($_POST["service_bind_type"]) || empty($_POST["service_bind_type"])) {
  $service_bind_type = null;
  $user_update_query->bindParam(":service_bind_type", $service_bind_type, PDO::PARAM_NULL);
} else {
  $user_update_query->bindParam(":service_bind_type", $_POST["service_bind_type"]);
}
$user_update_query->bindParam(":isadmin", $isadmin, PDO::PARAM_BOOL);
$user_update_query->bindParam(":ismoder", $ismoder, PDO::PARAM_BOOL);

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
  $user_update_query->execute();
  if ($user_update_query->rowCount() >= 1) {
    echo json_encode(array("success" => 1));
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