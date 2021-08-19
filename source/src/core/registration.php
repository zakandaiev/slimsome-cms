<?php

require_once("db_connect.php");
require_once("db_settings.php");
require_once("core.php");

$login = filter_var(trim($_POST["login"]), FILTER_SANITIZE_STRING);
$password = filter_var(trim($_POST["password"]), FILTER_SANITIZE_STRING);
$email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_STRING);
$name = filter_var(trim($_POST["name"]), FILTER_SANITIZE_STRING);
$nick = filter_var(trim($_POST["nick"]), FILTER_SANITIZE_STRING);

$validate_login = validateLogin($login);
$validate_password = validatePassword($password);
$validate_email = validateEmail($email);
$validate_name = validateUserName($name);
$validate_nick = validateUserNick($nick);

if($validate_login !== "valid") {
  echo json_encode(array("success" => 0, "error" => $validate_login));
  exit();
}
if($validate_password !== "valid") {
  echo json_encode(array("success" => 0, "error" => $validate_password));
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
if($validate_nick !== "valid") {
  echo json_encode(array("success" => 0, "error" => $validate_nick));
  exit();
}

if (empty($_POST["policy_data"])) {
  echo json_encode(array("success" => 0, "error" => "Вы должны быть согласны с политикой обработки персональных данных"));
  exit();
}
if (empty($_POST["policy_privacy"])) {
  echo json_encode(array("success" => 0, "error" => "Вы должны быть согласны с политикой конфиденциальности"));
  exit();
}

$user_add_query = $pdo->prepare("
  INSERT INTO ".$prefix."_users
    (login, password, email, nick, name)
  VALUES 
    (:login, :password, :email, :nick, :name)
");
$user_add_query->bindParam(":login", $login);
$user_add_query->bindParam(":password", $password);
$user_add_query->bindParam(":email", $email);
$user_add_query->bindParam(":nick", $nick);
$user_add_query->bindParam(":name", $name);

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
  $user_add_query->execute();
  if ($user_add_query->rowCount() >= 1) {
    setLoginCookie($login);
    require_once("mail_registration.php");
    echo json_encode(array("success" => 1));
  } else {
    echo json_encode(array("success" => 0, "error" => $user_add_query->errorInfo()));
  }
} catch(PDOException $error) { 
  if (preg_match("/Duplicate entry .+ for key '(.+)'/", $error->getMessage(), $matches)) {
    $arr_column_names = array(
      "login" => "логин",
      "email" => "e-mail",
      "nick" => "ник"
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