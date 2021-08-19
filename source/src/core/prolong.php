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

if (!isset($_POST["accept_policy"]) || $_POST["accept_policy"] === false) {
  echo json_encode(array("success" => 0, "error" => "Покупка невозможна без согласия с публичной офертой и политикой конфиденциальности"));
  exit();
}

$db_payments = json_decode($GLOBALS["payments"], true);

if (!isset($_POST["service_payment"]) || empty($_POST["service_payment"])) {
  echo json_encode(array("success" => 0, "error" => "Укажите систему оплаты"));
  exit();
} else if (filter_var(trim($_POST["service_payment"]), FILTER_SANITIZE_STRING) == "LiqPay") {
  $service_payment = "LiqPay";
} else if (filter_var(trim($_POST["service_payment"]), FILTER_SANITIZE_STRING) == "InterKassa") {
  $service_payment = "InterKassa";
} else {
  echo json_encode(array("success" => 0, "error" => "Такой платежной системы не найдено"));
  exit();
}

if (isset($service_payment) && !$db_payments[$service_payment]["enabled"]) {
  echo json_encode(array("success" => 0, "error" => "Покупка привилегий данным методом приостановлена"));
  exit();
}

if (!isset($_POST["currency"]) || empty($_POST["currency"])) {
  echo json_encode(array("success" => 0, "error" => "Укажите валюту"));
  exit();
} else if ($_POST["currency"] == "uah") {
  $currency = 2;
} else {
  $currency = 1;
}

$user_id = filter_var(trim($_POST["user_id"]), FILTER_SANITIZE_NUMBER_INT);
$pass_check = filter_var(trim($_POST["pass_check"]), FILTER_SANITIZE_STRING);

$user_info = getUserInfo($user_id, null);

if ($user_info["password"] !== $pass_check || !isset($user_info["service_id"])) {
  echo json_encode(array("success" => 0, "error" => "Неверные данные пользователя"));
  exit();
}

$user_id = $user_info["id"];
$service_id = $user_info["service_id"];

// get service data
$get_service_data = $pdo->prepare("SELECT * FROM ".$prefix."_services WHERE id=:service_id LIMIT 1");
$get_service_data->bindParam(":service_id", $service_id);
$get_service_data->execute();
if ($get_service_data->rowCount() == 1) {
  $get_service_data = $get_service_data->fetch(PDO::FETCH_LAZY);
} else {
  echo json_encode(array("success" => 0, "error" => "Такой привилегии не существует"));
  exit();
}

if (!isset($_POST["service_days"])) {
  echo json_encode(array("success" => 0, "error" => "Укажите срок"));
  exit();
}
$service_days = filter_var(trim($_POST["service_days"]), FILTER_SANITIZE_NUMBER_INT);

$days_arr = json_decode($get_service_data["days"], true);
$search_days = array_keys(array_column($days_arr, 'days'), $service_days);
$search_days_result = array_map(function($k) use ($days_arr){return $days_arr[$k];}, $search_days);

if (!empty($search_days_result[0])) {
  $search_days_result = $search_days_result[0];
  $payment["service_id"] = $get_service_data["id"];
  $payment["service_name"] = $get_service_data["name"];
  $payment["days"] = $service_days;
  if ($service_payment == "LiqPay") {
    if ($db_payments["LiqPay"]["enabled"]) {
      $payment_system = "LiqPay";
      if ($currency == 2) {
        $payment["price"] = $search_days_result["price_uah"];
      } else {
        $payment["price"] = $search_days_result["price_rub"];
      }
      $payment["currency"] = $currency;
      $public_key = $db_payments["LiqPay"]["purse"];
      $secret_key = $db_payments["LiqPay"]["secret"];
    } else {
      echo json_encode(array("success" => 0, "error" => "Оплата с помощью LiqPay выключена"));
      exit();
    }
  } else if ($service_payment == "InterKassa") {
    if ($db_payments["InterKassa"]["enabled"]) {
      $payment_system = "InterKassa";
      if ($currency == 2) {
        $payment["price"] = $search_days_result["price_uah"];
      } else {
        $payment["price"] = $search_days_result["price_rub"];
      }
      $payment["currency"] = $currency;
      $public_key = $db_payments["InterKassa"]["purse"];
      $secret_key = $db_payments["InterKassa"]["secret"];
    } else {
      echo json_encode(array("success" => 0, "error" => "Оплата с помощью InterKassa выключена"));
      exit();
    }
  } else {
    echo json_encode(array("success" => 0, "error" => "Такой платежной системы не найдено"));
    exit();
  }
} else {
  echo json_encode(array("success" => 0, "error" => "Такого срока не существует"));
  exit();
}

$payments_add_query = $pdo->prepare("
  INSERT INTO ".$prefix."_payments
    (name, user_id, user_data, service_id, service_name, days, price, currency, prolong, status)
  VALUES 
    (:name, :user_id, NULL, :service_id, :service_name, :days, :price, :currency, TRUE, 0)
");
$payments_add_query->bindParam(":name", $service_payment);
$payments_add_query->bindParam(":user_id", $user_id);
$payments_add_query->bindParam(":service_id", $payment["service_id"]);
$payments_add_query->bindParam(":service_name", $payment["service_name"]);
$payments_add_query->bindParam(":days", $payment["days"]);
$payments_add_query->bindParam(":price", $payment["price"]);
$payments_add_query->bindParam(":currency", $payment["currency"]);
$payments_add_query->execute();
$payment["id"] = $pdo->lastInsertId();
$payment["status"] = 0;

// PROCEED
$payment["description"] = "Продление " . $payment["service_name"] . " на сервере " . $GLOBALS["site_name"];

if ($payment_system == "LiqPay") {
  require_once("buy_liqpay.php");
} else if ($payment_system == "InterKassa") {
  require_once("buy_interkassa.php");
} else {
  echo json_encode(array("success" => 0, "error" => "Неизвестная ошибка"));
}

?>