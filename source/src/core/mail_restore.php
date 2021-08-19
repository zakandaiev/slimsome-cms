<?php

if (!isset($email) || empty($email)) {
  header("Location: /404");
  exit();
}

$subject = "Восстановление доступа";

$message = '
  <p><span style="font-size:16px"><strong>Доброго времени суток</strong></span></p>
  <br>
  <p>Вы запросили восстановление доступа на сайте <a href="'.$GLOBALS["site_url"].'">'.$GLOBALS["site_url"].'</a> сервера '.$GLOBALS["site_name"].' (IP: '.$GLOBALS["server_ip"].').</p>
  <p>Новые данные для входа на сайт:</p>
  <p><strong>Логин:</strong> '.$login.'</p>
  <p><strong>Пароль:</strong> '.$new_pass.'</p>
  <p><strong>Если это были не Вы, советуем переслать это письмо на '.$GLOBALS["site_email"].'</strong></p>
  <p>Это автоматическое письмо, отвечать на него не нужно.</p>
  <br>
  <p>С уважением,<br>Администрация <a href="'.$GLOBALS["site_url"].'">'.$GLOBALS["site_name"].'</a></p>
';

sendMail($email, $subject, $message, $GLOBALS["site_email"]);

?>