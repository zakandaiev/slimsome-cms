<?php

if (!isset($_POST["accept_policy"]) || $_POST["accept_policy"] === false) {
  echo json_encode(array("success" => 0, "error" => "Покупка невозможна без согласия с публичной офертой и политикой конфиденциальности"));
  exit();
}

require_once("db_connect.php");
require_once("db_settings.php");
require_once("core.php");

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

if (!isset($_POST["service_id"]) || empty($_POST["service_id"])) {
  echo json_encode(array("success" => 0, "error" => "Укажите привилегию"));
  exit();
}
$service_id = filter_var(trim($_POST["service_id"]), FILTER_SANITIZE_NUMBER_INT);

if (empty($_POST["user_id"]) && empty($_POST["pass_check"])) {
  if (!isset($_POST["service_bind_type"]) || empty($_POST["service_bind_type"])) {
    echo json_encode(array("success" => 0, "error" => "Укажите способ привязки"));
    exit();
  } else if ($_POST["service_bind_type"] == "nick_pass" && empty($_POST["nick"])) {
    echo json_encode(array("success" => 0, "error" => "Укажите игровой ник"));
    exit();
  } else if ($_POST["service_bind_type"] == "nick_pass" && !empty($_POST["nick"]) && mb_strlen($_POST["nick"]) < 2) {
    echo json_encode(array("success" => 0, "error" => "Игровой ник слишком короткий"));
    exit();
  } else if ($_POST["service_bind_type"] == "nick_pass" && !empty($_POST["nick"]) && mb_strlen($_POST["nick"]) > 64) {
    echo json_encode(array("success" => 0, "error" => "Игровой ник слишком длинный"));
    exit();
  } else if ($_POST["service_bind_type"] == "nick_pass" && !empty($_POST["nick"]) && preg_match('/"|<|>/', trim($_POST["nick"]))) {
    echo json_encode(array("success" => 0, "error" => "Игровой ник содержит запрещённые символы"));
    exit();
  } else if ($_POST["service_bind_type"] == "steam_pass" && empty($_POST["steam_id"])) {
    echo json_encode(array("success" => 0, "error" => "Укажите Steam ID"));
    exit();
  } else if ($_POST["service_bind_type"] == "steam_pass" && !empty($_POST["steam_id"]) && !preg_match("/^STEAM_[0-9]:[0-9]:[0-9]{5,10}$/", trim($_POST["steam_id"]))) {
    echo json_encode(array("success" => 0, "error" => "Неверный формат Steam ID"));
    exit();
  }
  if (!isset($_POST["login"]) || empty($_POST["login"])) {
    echo json_encode(array("success" => 0, "error" => "Укажите логин"));
    exit();
  } else if (mb_strlen($_POST["login"]) < 2) {
    echo json_encode(array("success" => 0, "error" => "Логин слишком короткий"));
    exit();
  } else if (mb_strlen($_POST["login"]) > 64) {
    echo json_encode(array("success" => 0, "error" => "Логин слишком длинный"));
    exit();
  } else if (!preg_match("/^[\w]{2,64}+$/", $_POST["login"])) {
    echo json_encode(array("success" => 0, "error" => "Логин должен содержать только английские буквы и цифры"));
    exit();
  }
  if (!isset($_POST["password"]) || empty($_POST["password"])) {
    echo json_encode(array("success" => 0, "error" => "Укажите пароль"));
    exit();
  } else if (mb_strlen($_POST["password"]) < 4) {
    echo json_encode(array("success" => 0, "error" => "Пароль слишком короткий"));
    exit();
  } else if (mb_strlen($_POST["password"]) > 32) {
    echo json_encode(array("success" => 0, "error" => "Пароль слишком длинный"));
    exit();
  } else if (!preg_match("/^[\w]{4,32}+$/", $_POST["password"])) {
    echo json_encode(array("success" => 0, "error" => "Пароль должен содержать только английские буквы и цифры"));
    exit();
  }
  if (!isset($_POST["email"]) || empty($_POST["email"])) {
    echo json_encode(array("success" => 0, "error" => "Укажите e-mail"));
    exit();
  } else if (mb_strlen($_POST["email"]) < 4) {
    echo json_encode(array("success" => 0, "error" => "E-mail слишком короткий"));
    exit();
  } else if (mb_strlen($_POST["email"]) > 128) {
    echo json_encode(array("success" => 0, "error" => "E-mail слишком длинный"));
    exit();
  } else if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
    echo json_encode(array("success" => 0, "error" => "Неверный формат e-mail"));
    exit();
  }
  if (!isset($_POST["name"]) || empty($_POST["name"])) {
    echo json_encode(array("success" => 0, "error" => "Укажите имя"));
    exit();
  } else if (mb_strlen($_POST["name"]) < 2) {
    echo json_encode(array("success" => 0, "error" => "Имя слишком короткое"));
    exit();
  } else if (mb_strlen($_POST["name"]) > 32) {
    echo json_encode(array("success" => 0, "error" => "Имя слишком длинное"));
    exit();
  } else if (!preg_match("/^[а-яёА-ЯЁ]+$/u", trim($_POST["name"]))) {
    echo json_encode(array("success" => 0, "error" => "Имя должно содержать только русские буквы"));
    exit();
  }

  $login = filter_var(trim($_POST["login"]), FILTER_SANITIZE_STRING);
  $password = filter_var(trim($_POST["password"]), FILTER_SANITIZE_STRING);
  $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_STRING);
  $name = filter_var(trim($_POST["name"]), FILTER_SANITIZE_STRING);

  $service_bind_type = filter_var(trim($_POST["service_bind_type"]), FILTER_SANITIZE_STRING);
  if ($service_bind_type == "steam_pass") {
    $service_bind_type = 2;
    $nick = null;
    $steam_id = filter_var(trim($_POST["steam_id"]), FILTER_SANITIZE_STRING);
    // CHECK STEAM_ID
    $check_steam_id = $pdo->prepare("SELECT steam_id FROM ".$prefix."_users WHERE steam_id=:steam_id");
    $check_steam_id->bindParam(":steam_id", $steam_id);
    $check_steam_id->execute();
    if ($check_steam_id->rowCount() >= 1) {
      echo json_encode(array("success" => 0, "error" => "Такой Steam ID уже существует"));
      exit();
    }
  } else {
    $service_bind_type = 1;
    $nick = filter_var(trim($_POST["nick"]), FILTER_SANITIZE_STRING);
    $steam_id = null;
    // CHECK NICK
    $restricted_nicks = file("nicks.txt");
    foreach ($restricted_nicks as $row) {
      if (stripos($row, $nick) !== false) {
      //if (str_contains($row, $nick)) {
        echo json_encode(array("success" => 0, "error" => "Такой ник запрещен"));
        exit();
      }
    }
    $check_nick = $pdo->prepare("SELECT nick FROM ".$prefix."_users WHERE nick=:nick");
    $check_nick->bindParam(":nick", $nick);
    $check_nick->execute();
    if ($check_nick->rowCount() >= 1) {
      echo json_encode(array("success" => 0, "error" => "Такой ник уже существует"));
      exit();
    }
  }

  $check_login = $pdo->prepare("SELECT login FROM ".$prefix."_users WHERE login=:login");
  $check_login->bindParam(":login", $login);
  $check_login->execute();
  if ($check_login->rowCount() >= 1) {
    echo json_encode(array("success" => 0, "error" => "Такой логин уже существует"));
    exit();
  }
  $check_email = $pdo->prepare("SELECT email FROM ".$prefix."_users WHERE email=:email");
  $check_email->bindParam(":email", $email);
  $check_email->execute();
  if ($check_email->rowCount() >= 1) {
    echo json_encode(array("success" => 0, "error" => "Такой e-mail уже существует"));
    exit();
  }
} else {
  $user_id = filter_var(trim($_POST["user_id"]), FILTER_SANITIZE_NUMBER_INT);
  $pass_check = filter_var(trim($_POST["pass_check"]), FILTER_SANITIZE_STRING);
  $check_logged_user = $pdo->prepare("SELECT id FROM ".$prefix."_users WHERE id=:id and password=:pass_check LIMIT 1;");
  $check_logged_user->bindParam(":id", $user_id);
  $check_logged_user->bindParam(":pass_check", $pass_check);
  $check_logged_user->execute();
  if ($check_logged_user->rowCount() == 1) {
    $user_id = $user_id;
  } else {
    echo json_encode(array("success" => 0, "error" => "Неверные данные пользователя"));
    exit();
  }
}

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

if (!isset($_POST["service_days_".$get_service_data["id"]])) {
  echo json_encode(array("success" => 0, "error" => "Укажите срок"));
  exit();
}
$service_days = filter_var(trim($_POST["service_days_".$get_service_data["id"]]), FILTER_SANITIZE_NUMBER_INT);

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
    (:name, :user_id, :user_data, :service_id, :service_name, :days, :price, :currency, FALSE, 0)
");
$payments_add_query->bindParam(":name", $service_payment);
// USER DATA
if (isset($user_id) && !empty($user_id)) {
  $payments_add_query->bindParam(":user_id", $user_id);
  $user_data = null;
  $payments_add_query->bindParam(":user_data", $user_data, PDO::PARAM_NULL);
} else {
  // uid
  $user_id = null;
  $payments_add_query->bindParam(":user_id", $user_id, PDO::PARAM_NULL);
  // json data
  $user_data = array (
    "login" => $login,
    "password" => $password,
    "email" => $email,
    "nick" => $nick,
    "steam_id" => $steam_id,
    "service_bind_type" => $service_bind_type,
    "name" => $name
  );
  $user_data = json_encode($user_data, JSON_FORCE_OBJECT);
  $payments_add_query->bindParam(":user_data", $user_data);
}
// PAYMENT DATA
$payments_add_query->bindParam(":service_id", $payment["service_id"]);
$payments_add_query->bindParam(":service_name", $payment["service_name"]);
$payments_add_query->bindParam(":days", $payment["days"]);
$payments_add_query->bindParam(":price", $payment["price"]);
$payments_add_query->bindParam(":currency", $payment["currency"]);
$payments_add_query->execute();
$payment["id"] = $pdo->lastInsertId();
$payment["status"] = 0;

// PROCEED
$payment["description"] = "Покупка " . $payment["service_name"] . " на сервере " . $GLOBALS["site_name"];

if ($payment_system == "LiqPay") {
  require_once("buy_liqpay.php");
} else if ($payment_system == "InterKassa") {
  require_once("buy_interkassa.php");
} else {
  echo json_encode(array("success" => 0, "error" => "Неизвестная ошибка"));
}

?>