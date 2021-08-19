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

if (empty($_POST["t_zone"])) {
  echo json_encode(array("success" => 0, "error" => "Заполните часовой пояс"));
  exit();
}
if (empty($_POST["cron_pass"])) {
  echo json_encode(array("success" => 0, "error" => "Заполните пароль CRON"));
  exit();
}
if (empty($_POST["server_ip"])) {
  echo json_encode(array("success" => 0, "error" => "Заполните IP:PORT сервера"));
  exit();
}
if (empty($_POST["ftp_host"])) {
  echo json_encode(array("success" => 0, "error" => "Заполните хост FTP"));
  exit();
}
if (empty($_POST["ftp_login"])) {
  echo json_encode(array("success" => 0, "error" => "Заполните логин FTP"));
  exit();
}
if (empty($_POST["ftp_pass"])) {
  echo json_encode(array("success" => 0, "error" => "Заполните пароль FTP"));
  exit();
}
if (empty($_POST["ftp_users_path"])) {
  echo json_encode(array("success" => 0, "error" => "Заполните путь к users.ini"));
  exit();
}
if (empty($_POST["ftp_bans_path"])) {
  echo json_encode(array("success" => 0, "error" => "Заполните путь к fresh_bans.sq3"));
  exit();
}
if (empty($_POST["ftp_stats_path"])) {
  echo json_encode(array("success" => 0, "error" => "Заполните путь к csstats.dat"));
  exit();
}

$settings_update_query = $pdo->prepare("
  UPDATE ".$prefix."_settings SET value=:t_zone WHERE name='t_zone';
  UPDATE ".$prefix."_settings SET value=:cron_pass WHERE name='cron_pass';
  UPDATE ".$prefix."_settings SET value=:server_ip WHERE name='server_ip';
  UPDATE ".$prefix."_settings SET value=:ftp_host WHERE name='ftp_host';
  UPDATE ".$prefix."_settings SET value=:ftp_login WHERE name='ftp_login';
  UPDATE ".$prefix."_settings SET value=:ftp_pass WHERE name='ftp_pass';
  UPDATE ".$prefix."_settings SET value=:ftp_users_path WHERE name='ftp_users_path';
  UPDATE ".$prefix."_settings SET value=:ftp_bans_path WHERE name='ftp_bans_path';
  UPDATE ".$prefix."_settings SET value=:ftp_stats_path WHERE name='ftp_stats_path';
");
$settings_update_query->bindParam(":t_zone", $_POST["t_zone"]);
$settings_update_query->bindParam(":cron_pass", $_POST["cron_pass"]);
$settings_update_query->bindParam(":server_ip", $_POST["server_ip"]);
$settings_update_query->bindParam(":ftp_host", $_POST["ftp_host"]);
$settings_update_query->bindParam(":ftp_login", $_POST["ftp_login"]);
$settings_update_query->bindParam(":ftp_pass", $_POST["ftp_pass"]);
$settings_update_query->bindParam(":ftp_users_path", $_POST["ftp_users_path"]);
$settings_update_query->bindParam(":ftp_bans_path", $_POST["ftp_bans_path"]);
$settings_update_query->bindParam(":ftp_stats_path", $_POST["ftp_stats_path"]);

try {
  $settings_update_query->execute();
  echo json_encode(array("success" => 1));
} catch(PDOException $error) { 
  echo json_encode(array("error" => 0, "error" => $error->getMessage()));
}

?>