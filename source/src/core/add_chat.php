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

if (!isset($_POST["user_id"]) || empty($_POST["user_id"]) ||
    !isset($_POST["pass_check"]) || empty($_POST["pass_check"])) {
  echo json_encode(array("success" => 0, "error" => "Неверные данные пользователя"));
  exit();
}

$user_id = filter_var(trim($_POST["user_id"]), FILTER_SANITIZE_NUMBER_INT);
$pass_check = filter_var(trim($_POST["pass_check"]), FILTER_SANITIZE_STRING);

$message = filter_var(trim($_POST["message"]), FILTER_SANITIZE_STRING);

if (!isset($_POST["refference"]) || empty($_POST["refference"])) {
  $refference = null;
} else {
  $refference = $_POST["refference"];
}

$validate_message = validateChatMessage($message);

if($validate_message !== "valid") {
  echo json_encode(array("success" => 0, "error" => $validate_message));
  exit();
}

$user_validate_query = $pdo->prepare("SELECT id FROM ".$prefix."_users WHERE id=:user_id and password=:pass_check");
$user_validate_query->bindParam(":user_id", $user_id);
$user_validate_query->bindParam(":pass_check", $pass_check);

try {
  $user_validate_query->execute();
  if ($user_validate_query->rowCount() == 1) {
    // SEND CHAT
    $add_chat_query = $pdo->prepare("
      INSERT INTO ".$prefix."_chat
        (user_id, message, refference)
      VALUES
        (:user_id, :message, :refference)
    ");
    $add_chat_query->bindParam(":user_id", $user_id);
    $add_chat_query->bindParam(":message", $message);
    $add_chat_query->bindParam(":refference", $refference);
    try {
      $add_chat_query->execute();
      if ($add_chat_query->rowCount() >= 1) {
        echo json_encode(array("success" => 1));
      } else {
        echo json_encode(array("error" => 0, "error" => $add_chat_query->errorInfo()));
      }
    } catch(PDOException $error) { 
      echo json_encode(array("error" => 0, "error" => $error->getMessage()));
    }
    // END SEND CHAT
  } else {
    echo json_encode(array("error" => 0, "error" => "Неверные данные пользователя"));
    exit();
  }
} catch(PDOException $error) { 
  echo json_encode(array("error" => 0, "error" => $error->getMessage()));
  exit();
}

?>