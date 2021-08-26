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

$icon = filter_var(trim($_POST["icon"]), FILTER_SANITIZE_STRING);
$url = filter_var(trim($_POST["url"]), FILTER_SANITIZE_STRING);
if (isset($_POST["blank"]) && $_POST["blank"] == "on") {
  $blank = true;
} else {
  $blank = false;
}

$validate_icon = validateSocialIcon($icon);
$validate_url = validateUrl($url);

if($validate_icon !== "valid") {
  echo json_encode(array("success" => 0, "error" => $validate_icon));
  exit();
}
if($validate_url !== "valid") {
  echo json_encode(array("success" => 0, "error" => $validate_url));
  exit();
}

$db_socials_query = $GLOBALS["socials"];

if (empty($db_socials_query)) {
  $json = array (
    0 => array("icon" => $icon, "url" => $url, "blank" => $blank)
  );
  $json = array_values($json);
  $json = json_encode($json, JSON_FORCE_OBJECT);
  $socials_update_query = $pdo->prepare("UPDATE ".$prefix."_settings SET value=:value WHERE name='socials'");
  $socials_update_query->bindParam(":value", $json);
  $socials_update_query->execute();
} else {
  $db_socials_query = json_decode($db_socials_query, true);
  $db_socials_query[$social_id] = array("icon" => $icon, "url" => $url, "blank" => $blank);
  $db_socials_query = array_values($db_socials_query);
  $db_socials_query = json_encode($db_socials_query, JSON_FORCE_OBJECT);
  $socials_update_query = $pdo->prepare("UPDATE ".$prefix."_settings SET value=:value WHERE name='socials'");
  $socials_update_query->bindParam(":value", $db_socials_query);
  $socials_update_query->execute();
}

echo json_encode(array("success" => 1));

?>