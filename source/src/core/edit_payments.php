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

if (isset($_POST["InterKassa_enabled"]) && $_POST["InterKassa_enabled"] == "on") {
  $ik_enabled = true;
} else {
  $ik_enabled = false;
}
if (isset($_POST["LiqPay_enabled"]) && $_POST["LiqPay_enabled"] == "on") {
  $liq_enabled = true;
} else {
  $liq_enabled = false;
}

if (!isset($_POST["InterKassa_purse"]) || !isset($_POST["InterKassa_secret"]) ||
    !isset($_POST["LiqPay_purse"]) || !isset($_POST["LiqPay_secret"])) {
  echo json_encode(array("success" => 0, "error" => "Неверный формат данных"));
  exit();
}

$ik_purse = filter_var(trim($_POST["InterKassa_purse"]), FILTER_SANITIZE_STRING);
$ik_secret = filter_var(trim($_POST["InterKassa_secret"]), FILTER_SANITIZE_STRING);

$liq_purse = filter_var(trim($_POST["LiqPay_purse"]), FILTER_SANITIZE_STRING);
$liq_secret = filter_var(trim($_POST["LiqPay_secret"]), FILTER_SANITIZE_STRING);

$payments = array (
 "InterKassa" => array("purse" => $ik_purse, "secret" => $ik_secret, "enabled" => $ik_enabled),
 "LiqPay" => array("purse" => $liq_purse, "secret" => $liq_secret, "enabled" => $liq_enabled)
);
$payments_update_query = $pdo->prepare("UPDATE ".$prefix."_settings SET json=:payments WHERE name='payments'");
$payments = json_encode($payments, JSON_FORCE_OBJECT);
$payments_update_query->bindParam(":payments", $payments);
$payments_update_query->execute();

echo json_encode(array("success" => 1));

?>