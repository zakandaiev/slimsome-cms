<? require_once("partials/header.php"); ?>

<section class="block">
  <h2>Информация о покупке</h2>
  <div class="well appear-bottom">

<?php

if (empty(key($_GET))) {
  echo '<p>Как вы сюда попали?</p><a href="/" class="btn btn_primary">Вернуться на главную</a>';
  require_once("partials/footer.php");
  exit();
}

if (empty($_POST)) {
  echo '<p>Как вы сюда попали?</p><a href="/" class="btn btn_primary">Вернуться на главную</a>';
  require_once("partials/footer.php");
  exit();
}

$get_payment_query = $pdo->prepare("SELECT * FROM ".$prefix."_payments WHERE id=:id LIMIT 1");
if (key($_GET) == "LiqPay") {
  $get_payment_query->bindParam(":id", $_POST["order_id"]);
} else if (key($_GET) == "InterKassa" || key($_GET) == "InterKassa_Process") {
  $get_payment_query->bindParam(":id", $_POST["ik_pm_no"]);
} else {
  echo "<p><b>Ошибка:</b> ключ заказа отсутствует.</p>";
  echo "<p>Обратитесь к администратору сайта!</p>";
  require_once("partials/footer.php");
  exit();
}
$get_payment_query->execute();

if ($get_payment_query->rowCount() == 1) {
  $payment = $get_payment_query->fetch();
  // $payment_price = $payment["price"]; // int только при тестовых оплатах, насколько понял
  $payment_price = number_format($payment["price"], 2, '.', '');
  $payment_currency = getCurrencyCode($payment["currency"]);
} else {
  echo "<p><b>Ошибка:</b> такого заказа не существует.</p>";
  echo "<p>Обратитесь к администратору сайта!</p>";
  require_once("partials/footer.php");
  exit();
}

// GET USER INFO
if (empty($payment["user_id"])) {
  $payment_user = json_decode($payment["user_data"], true);
} else {
  $payment_user = null;
}

// GET PAYMENTS
$db_payments = json_decode($GLOBALS["payments"], true);

if (key($_GET) == "LiqPay") {
  // CHECK SIGNATURE
  $data_arr = array(
    "public_key" => $db_payments["LiqPay"]["purse"],
    "version" => "3",
    "action" => "pay",
    "amount" => $payment["price"],
    "currency" => "UAH",
    "order_id" => $payment["service_id"],
    "result_url" => $GLOBALS["site_url"] . "buy_process"
  );
  $data = base64_encode(json_encode($data_arr));

  $sign_string = $db_payments["LiqPay"]["secret"] . $data .  $db_payments["LiqPay"]["secret"];

  $signature = base64_encode(sha1($sign_string, true));

  if ($_POST["signature"] !== $signature) {
    echo "<p><b>Ошибка:</b> проверка подписи неверна.</p>";
    echo "<p>Обратитесь к администратору сайта!</p>";
    require_once("partials/footer.php");
    exit();
  }

  // CHECK PAYMENT STATUS
  $curl_data = [
    'data' => $data,
    'signature' => $signature,
    'version'   => '3',
    'public_key'   => $db_payments["LiqPay"]["purse"],
    'action'   => 'status',
    'order_id'   => $payment["service_id"]
  ];

  $ch = curl_init();

  curl_setopt($ch, CURLOPT_URL, "https://www.liqpay.ua/api/request");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($curl_data)); 

  $curl_response = curl_exec($ch);

  curl_close($ch);

  $curl_response = json_decode($curl_response, JSON_OBJECT_AS_ARRAY);

  if ($curl_response["status"] !== "success") {
    echo "<p><b>Ошибка:</b> " . $curl_response["status"] . "</p>";
    echo "<p>Обратитесь к администратору сайта!</p>";
    require_once("partials/footer.php");
    exit();
  }
} else if (key($_GET) == "InterKassa") {
  $public_key = $db_payments["InterKassa"]["purse"];

  if ($_POST["ik_co_id"] !== $public_key || $_POST["ik_pm_no"] !== $payment["id"]) {
    echo "<p><b>Ошибка:</b> проверка подписи неверна.</p>";
    echo "<p>Обратитесь к администратору сайта!</p>";
    require_once("partials/footer.php");
    exit();
  }

  if ($_POST["ik_inv_st"] == "success") {
    if ($payment["prolong"]) {
      echo '<p>Благодарим Вас за продление привилегии <b>'.$payment["service_name"].'</b> на нашем сайте!</p>';
    } else {
      echo '<p>Благодарим Вас за покупку привилегии <b>'.$payment["service_name"].'</b> на нашем сайте!</p>';
      if (empty($payment["user_id"])) {
        echo '<p>Вся информация доступна в Вашем профиле. Данные для входа были отправлены на указанный адрес при оформлении покупки!</p>';
      } else {
        echo '<p>Вся информация доступна в Вашем профиле.</p>';
      }
    }
  } else if ($_POST["ik_inv_st"] == "waitAccept") {
    echo "<p><b>Ошибка:</b> платеж ожидает оплаты.</p>";
    if (empty($payment["user_id"])) {
      echo "<p>После оплаты Вам придет e-mail с данными для входа на указанный адрес при оформлении покупки!</p>";
    } else {
      echo "<p>После оплаты Вам придет e-mail с подтверждением!</p>";
    }
  } else if ($_POST["ik_inv_st"] == "process") {
    echo "<p><b>Ошибка:</b> платеж еще обрабатывается.</p>";
    if (empty($payment["user_id"])) {
      echo "<p>Как только он обработается Вам придет e-mail с данными для входа на указанный адрес при оформлении покупки!</p>";
    } else {
      echo "<p>Как только он обработается Вам придет e-mail с подтверждением!</p>";
    }
  } else {
    echo "<p><b>Ошибка:</b> платеж был отклонен.</p>";
  }
  echo '<a href="/" class="btn btn_primary">Вернуться на главную</a>';
  require_once("partials/footer.php");
  exit();
} else if (key($_GET) == "InterKassa_Process") {
  $public_key = $db_payments["InterKassa"]["purse"];
  $secret_key = $db_payments["InterKassa"]["secret"];

  $dataSet = $_POST;

  unset($dataSet['ik_sign']);
  ksort($dataSet, SORT_STRING);
  array_push($dataSet, $secret_key);
  $signString = implode(':', $dataSet);
  $sign = base64_encode(md5($signString, true));

  if ($sign !== $_POST["ik_sign"] ||
      $dataSet["ik_co_id"] !== $public_key ||
      $dataSet["ik_pm_no"] !== $payment["id"] ||
      $dataSet["ik_am"] !== $payment_price ||
      $dataSet["ik_cur"] !== $payment_currency ||
      $dataSet["ik_inv_st"] !== "success"
      ) {
    echo "<p><b>Ошибка:</b> проверка подписи неверна.</p>";
    echo "<p>Обратитесь к администратору сайта!</p>";
    require_once("partials/footer.php");
    $file = '[' . date("d.m.Y H:i:s") . '] - payment ID ' . $payment["id"] . PHP_EOL;
    $file .= 'Sign: ' . $sign . ' ? ' . $_POST["ik_sign"] . PHP_EOL;
    $file .= 'ik_co_id: ' . $dataSet["ik_co_id"] . ' ? ' . $public_key . PHP_EOL;
    $file .= 'ik_pm_no: ' . $dataSet["ik_pm_no"] . ' ? ' . $payment["id"] . PHP_EOL;
    $file .= 'ik_am: ' . $dataSet["ik_am"] . ' ? ' . $payment_price . PHP_EOL;
    $file .= 'ik_cur: ' . $dataSet["ik_cur"] . ' ? ' . $payment_currency . PHP_EOL;
    $file .= 'ik_inv_st: ' . $dataSet["ik_inv_st"] . ' ? success' . PHP_EOL . PHP_EOL;
    file_put_contents(sys_get_temp_dir()."/failed_payments.txt", $file, FILE_APPEND);
    exit();
  }
} else {
  echo '<p>Как вы сюда попали?</p><a href="/" class="btn btn_primary">Вернуться на главную</a>';
  require_once("partials/footer.php");
  exit();
}

// EVERYTHING IS CHECKED, GO FUTHER
if (!empty($payment["user_id"])) {
  $user_id = $payment["user_id"];
  if ($payment["prolong"]) {
    $calc_service_end = "DATE_ADD(service_end, INTERVAL +:days DAY)";
  } else {
    $calc_service_end = "DATE_ADD(NOW(), INTERVAL +:days DAY)";
  }
  $user_update_service = $pdo->prepare("
    UPDATE ".$prefix."_users SET
      service_id=:service_id,
      service_start=NOW(),
      service_end=".$calc_service_end.",
      service_nolimit=:service_nolimit
    WHERE id=:id;
  ");
  $user_update_service->bindParam(":id", $user_id);
  $user_update_service->bindParam(":service_id", $payment["service_id"]);
  $user_update_service->bindParam(":days", $payment["days"]);
  if ($payment["days"] == 0) {
    $service_nolimit = true;
  } else {
    $service_nolimit = false;
  }
  $user_update_service->bindParam(":service_nolimit", $service_nolimit, PDO::PARAM_BOOL);
  $user_update_service->execute();
} else {
  $users_add_query = $pdo->prepare("
    INSERT INTO ".$prefix."_users
      (login, password, email, nick, steam_id, name, service_id, service_start, service_end, service_nolimit, service_bind_type)
    VALUES 
      (:login, :password, :email, :nick, :steam_id, :name, :service_id, NOW(), DATE_ADD(NOW(), INTERVAL +:days DAY), :service_nolimit, :service_bind_type)
  ");
  $users_add_query->bindParam(":login", $payment_user["login"]);
  $users_add_query->bindParam(":password", $payment_user["password"]);
  $users_add_query->bindParam(":email", $payment_user["email"]);
  $users_add_query->bindParam(":nick", $payment_user["nick"]);
  $users_add_query->bindParam(":steam_id", $payment_user["steam_id"]);
  $users_add_query->bindParam(":name", $payment_user["name"]);
  $users_add_query->bindParam(":service_id", $payment["service_id"]);
  $users_add_query->bindParam(":days", $payment["days"]);
  if ($payment["days"] == 0) {
    $service_nolimit = true;
  } else {
    $service_nolimit = false;
  }
  $users_add_query->bindParam(":service_nolimit", $service_nolimit, PDO::PARAM_BOOL);
  $users_add_query->bindParam(":service_bind_type", $payment_user["service_bind_type"]);
  $users_add_query->execute();
  $user_id = $pdo->lastInsertId();
}

$payments_update_query = $pdo->prepare("
  UPDATE ".$prefix."_payments SET
    user_id=:user_id, status=1
  WHERE id=:id
");
$payments_update_query->bindParam(":user_id", $user_id);
$payments_update_query->bindParam(":id", $payment["id"]);
$payments_update_query->execute();

// SEND MAIL
if ($payment["prolong"]) {
  require_once("core/mail_prolong.php");
} else {
  if (!empty($payment["user_id"])) {
    require_once("core/mail_buy_authorized.php");
  } else {
    require_once("core/mail_buy.php");
  }
}
require_once("core/mail_buy_notify_admin.php");

// UPLOAD ADMINS TO SERVER
if (uploadUsersIni(generateUsersIni()) === true) {
  echo 'toastr["success"]("Ваша привилегия успешно загружена", null, {"positionClass": "toast-bottom-right"}); setTimeout(()=>{ toastr["info"]("Дождитесь смены карты на сервере", null, {"positionClass": "toast-bottom-right"}); }, 1000);';
} else {
  echo 'toastr["error"]("Не удалось загрузить вашу привилегию на сервер", null, {"positionClass": "toast-bottom-right"}); setTimeout(()=>{ toastr["info"]("Обратитесь к администратору сайта", null, {"positionClass": "toast-bottom-right"}); }, 1000);';
}

if ($payment["prolong"]) {
  echo '<p>Благодарим Вас за продление привилегии <b>'.$payment["service_name"].'</b> на нашем сайте!</p>';
} else {
  echo '<p>Благодарим Вас за покупку привилегии <b>'.$payment["service_name"].'</b> на нашем сайте!</p>';
}
echo '<p>Вся информация доступна в Вашем профиле.</p>';
echo '<a href="profile" class="btn btn_primary">Перейти в профиль</a>';

?>

  </div>
</section>

<? require_once("partials/footer.php"); ?>