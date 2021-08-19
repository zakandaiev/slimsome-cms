<?php

require_once("db_connect.php");
require_once("db_settings.php");
require_once("core.php");

$email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_STRING);

$validate_email = validateEmail($email);

if($validate_email !== "valid") {
  echo json_encode(array("success" => 0, "error" => $validate_email));
  exit();
}

$check_email = $pdo->prepare("SELECT id, login, email FROM ".$prefix."_users WHERE email=:email LIMIT 1");
$check_email->bindParam(":email", $email);
$check_email->execute();
if ($check_email->rowCount() !== 1) {
  echo json_encode(array("success" => 0, "error" => "Такой e-mail не найден"));
  exit();
}

$check_email = $check_email->fetch(PDO::FETCH_LAZY);
$uid = $check_email->id;
$login = $check_email->login;

$new_pass = generatePassword(16);

$user_pass_update_query = $pdo->prepare("
  UPDATE ".$prefix."_users SET
    password=:new_pass
  WHERE id=:uid;
");
$user_pass_update_query->bindParam(":uid", $uid);
$user_pass_update_query->bindParam(":new_pass", $new_pass);

try {
  $user_pass_update_query->execute();
  if (isUserActive($uid, null)) {
    uploadUsersIni(generateUsersIni());
  }
  require_once("mail_restore.php");
  echo json_encode(array("success" => 1));
} catch(Exception $error) { 
  echo json_encode(array("success" => 0, "error" => $error->getMessage()));
}

?>