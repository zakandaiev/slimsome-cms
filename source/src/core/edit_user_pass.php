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

if (!isset($_POST["uid_check"]) || empty($_POST["uid_check"]) ||
    !isset($_POST["login_check"]) || empty($_POST["login_check"])) {
  echo json_encode(array("success" => 0, "error" => "Неверные данные пользователя"));
  exit();
}

if (!isset($_POST["current_pass"]) || empty($_POST["current_pass"])) {
  echo json_encode(array("success" => 0, "error" => "Укажите текущий пароль"));
  exit();
}

if (!isset($_POST["new_pass"]) || empty($_POST["new_pass"])) {
  echo json_encode(array("success" => 0, "error" => "Укажите новый пароль"));
  exit();
}

if ($_POST["current_pass"] == $_POST["new_pass"]) {
  echo json_encode(array("success" => 0, "error" => "Новый пароль должен отличаться"));
  exit();
}

$uid_check = filter_var(trim($_POST["uid_check"]), FILTER_SANITIZE_NUMBER_INT);
$login_check = filter_var(trim($_POST["login_check"]), FILTER_SANITIZE_STRING);

$current_pass = filter_var(trim($_POST["current_pass"]), FILTER_SANITIZE_STRING);
$new_pass = filter_var(trim($_POST["new_pass"]), FILTER_SANITIZE_STRING);

if (mb_strlen($current_pass) < 4) {
  echo json_encode(array("success" => 0, "error" => "Текущий пароль слишком короткий"));
  exit();
} else if (mb_strlen($current_pass) > 32) {
  echo json_encode(array("success" => 0, "error" => "Текущий пароль слишком длинный"));
  exit();
}
if (mb_strlen($new_pass) < 4) {
  echo json_encode(array("success" => 0, "error" => "Новый пароль слишком короткий"));
  exit();
} else if (mb_strlen($new_pass) > 32) {
  echo json_encode(array("success" => 0, "error" => "Новый пароль слишком длинный"));
  exit();
} else if (!preg_match("/^[A-Za-z0-9]{4,32}+$/", $new_pass)) {
  echo json_encode(array("success" => 0, "error" => "Новый пароль должен содержать только английские буквы и цифры"));
  exit();
}

$user_pass_update_query = $pdo->prepare("
  UPDATE ".$prefix."_users SET
    password=:new_pass
  WHERE id=:uid_check and login=:login_check and password=:current_pass;
");

$user_pass_update_query->bindParam(":uid_check", $uid_check);
$user_pass_update_query->bindParam(":login_check", $login_check);
$user_pass_update_query->bindParam(":current_pass", $current_pass);
$user_pass_update_query->bindParam(":new_pass", $new_pass);

try {
  $user_pass_update_query->execute();
  if ($user_pass_update_query->rowCount() >= 1) {
    // MAIL
    $get_user_email_query = $pdo->prepare("SELECT email FROM ".$prefix."_users WHERE id=:uid_check;");
    $get_user_email_query->bindParam(":uid_check", $uid_check);
    $get_user_email_query->execute();
    $user_email = $get_user_email_query->fetch(PDO::FETCH_LAZY)->email;
    if (!empty($user_email)) {
      require_once("mail_password_changed.php");
    }
    if (isUserActive($uid_check, null)) {
      echo json_encode(array("success" => 1, "upload" => 1));
    } else {
      echo json_encode(array("success" => 1, "upload" => 0));
    }
  } else {
    echo json_encode(array("error" => 0, "error" => "Неверные данные пользователя"));
  }
} catch(PDOException $error) { 
  echo json_encode(array("error" => 0, "error" => $error->getMessage()));
}

?>