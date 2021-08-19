<?php

if (!isset($payment) || empty($payment)) {
  header("Location: /404");
  exit();
}

$subject = "Покупка " . $payment["service_name"];

$message = '
  <p><span style="font-size:16px"><strong>Доброго времени суток</strong></span></p>
  <br>
  <p>Благодарим Вас за покупку привилегии <strong>'.$payment["service_name"].'</strong> на сайте <a href="'.$GLOBALS["site_url"].'">'.$GLOBALS["site_url"].'</a> сервера '.$GLOBALS["site_name"].' (IP: '.$GLOBALS["server_ip"].').</p>
  <p>Данные для входа на сайт:</p>
  <p><strong>Логин:</strong> '.$payment_user["login"].'</p>
  <p><strong>Пароль:</strong> '.$payment_user["password"].'</p>
  <p>Обращаем Ваше внимание на то, что привилегия активируется после смены карты на сервере!</p>
  <p>Инструкция по активации привилегии и биндов доступна в Вашем профиле по ссылке <a href="'.$GLOBALS["site_url"].'profile">'.$GLOBALS["site_url"].'profile</a></p>
  <p>Это автоматическое письмо, отвечать на него не нужно.</p>
  <br>
  <p>С уважением,<br>Администрация <a href="'.$GLOBALS["site_url"].'">'.$GLOBALS["site_name"].'</a></p>
';

sendMail($payment_user["email"], $subject, $message, $GLOBALS["site_email"]);

?>