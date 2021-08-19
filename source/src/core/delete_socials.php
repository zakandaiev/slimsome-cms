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

if (!isset($_POST["social_id"])) {
  echo json_encode(array("success" => 0, "error" => "Неверный параметр"));
  exit();
}

$social_id = $_POST["social_id"];

$db_socials_query = $GLOBALS["socials"];

$db_socials_query = json_decode($db_socials_query, true);

if (count($db_socials_query) <= 0) {
  echo json_encode(array("success" => 0, "error" => "Неверный параметр"));
} else if (count($db_socials_query) == 1) {
  $socials_update_query = $pdo->prepare("UPDATE ".$prefix."_settings SET json=:null WHERE name='socials'");
  $null_col = null;
  $socials_update_query->bindParam(":null", $null_col, PDO::PARAM_NULL);
  $socials_update_query->execute();
} else {
  $db_socials_query = array_values($db_socials_query);
  unset($db_socials_query[$social_id]);
  $db_socials_query = array_values($db_socials_query);
  $db_socials_query = json_encode($db_socials_query, JSON_FORCE_OBJECT);
  $socials_update_query = $pdo->prepare("UPDATE ".$prefix."_settings SET json=:json WHERE name='socials'");
  $socials_update_query->bindParam(":json",$db_socials_query);
  $socials_update_query->execute();
}

echo json_encode(array("success" => 1));

?>