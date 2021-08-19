<?php

if (!isset($payment) || empty($payment)) {
  header("Location: /404");
  exit();
}

$subject = "У вас новая покупка " . $payment["service_name"];

if ($payment["prolong"]) {
  $buy_info["type"] = "Продление";
} else {
  $buy_info["type"] = "Новая покупка";
  if (!empty($payment["user_id"])) {
    $buyer = getUserInfo($payment["user_id"], null);
    $buy_info["user"] = "Существующий пользователь";
    if (!empty($buyer["nick"])) {
      $buy_info["user"] .= " (Ник: ".$buyer["nick"].")";
    } else {
      $buy_info["user"] .= " (Логин: ".$buyer["login"].")";
    }
  } else {
    if (empty($payment_user["nick"])) {
      $nick_or_login = "Логин: " . $payment_user["login"];
    } else {
      $nick_or_login = "Ник: " . $payment_user["nick"];
    }
    $buy_info["user"] = "Новый пользователь (".$nick_or_login." | Email: ".$payment_user["email"].")";
  }
}

$message = '
  <p><span style="font-size:16px"><strong>Доброго времени суток</strong></span></p>
  <br>
  <p>На вашем сайте <a href="'.$GLOBALS["site_url"].'">'.$GLOBALS["site_url"].'</a> произвели новую покупку <strong>'.$payment["service_name"].'</strong>.</p>
  <p><strong>ID платежа:</strong> '.$payment["id"].'</p>
  <p><strong>Тип:</strong> '.$buy_info["type"].'</p>
  <p><strong>Пользователь:</strong> '.$buy_info["user"].'</p>
  <p>Это автоматическое письмо, отвечать на него не нужно.</p>
  <br>
  <p>С уважением,<br>Администрация <a href="'.$GLOBALS["site_url"].'">'.$GLOBALS["site_name"].'</a></p>
';

sendMail($GLOBALS["site_email"], $subject, $message, $GLOBALS["site_email"]);

?>