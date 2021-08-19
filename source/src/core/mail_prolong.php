<?php

if (!isset($payment) || empty($payment)) {
  header("Location: /404");
  exit();
}

$user_info = getUserInfo($user_id, null);

if (empty($user_info["email"])) {
  return;
}

if ($payment["days"] == 0) {
  $days_count = 'навсегда';
  $date_end = '∞';
  $date_when = '∞';
} else {
  $days_count = $payment["days"].' дней';
  $date_end = formatDateString($user_info["service_end"]);
  $date_when = dateWhen(strtotime($user_info["service_end"]));
}

$subject = "Продление " . $payment["service_name"];

$message = '
  <p><span style="font-size:16px"><strong>Доброго времени суток</strong></span></p>
  <br>
  <p>Благодарим Вас за продление привилегии <strong>'.$payment["service_name"].'</strong> на сайте <a href="'.$GLOBALS["site_url"].'">'.$GLOBALS["site_url"].'</a> сервера '.$GLOBALS["site_name"].' (IP: '.$GLOBALS["server_ip"].').</p>
  <p><strong>Срок продления:</strong> '.$days_count.'</p>
  <p><strong>Дата окончания:</strong> '.$date_end.'</p>
  <p><strong>Осталось:</strong> '.$date_when.'</p>
  <p>Это автоматическое письмо, отвечать на него не нужно.</p>
  <br>
  <p>С уважением,<br>Администрация <a href="'.$GLOBALS["site_url"].'">'.$GLOBALS["site_name"].'</a></p>
';

sendMail($user_info["email"], $subject, $message, $GLOBALS["site_email"]);

?>