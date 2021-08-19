<?php

if(!is_file("db_connect.php")) {
  header("Location: /404");
  exit();
} else {
  require_once("db_connect.php");
  require_once("db_settings.php");
  require_once("core.php");
}

if (empty($GLOBALS["cron_pass"]) || empty($_GET["cp"])) {
  header("Location: /404");
  exit();
}

if ($_GET["cp"] !== $GLOBALS["cron_pass"]) {
  header("Location: /404");
  exit();
}

$nearest_users_query = $pdo->query("
  SELECT
    email as user_email,
    (SELECT name FROM ".$prefix."_services WHERE id=t_users.service_id) as service_name,
    service_end
  FROM ".$prefix."_users t_users
  WHERE
    email is not NULL
  AND
    service_id is not NULL
  AND
    (service_nolimit IS FALSE AND CURRENT_TIMESTAMP < service_end AND DATEDIFF(service_end, CURRENT_TIMESTAMP) = 3)
  AND
    (SELECT enabled FROM ".$prefix."_services WHERE id=t_users.service_id AND enabled IS TRUE)
");
$nearest_users_query = $nearest_users_query->fetchAll(PDO::FETCH_ASSOC);

foreach($nearest_users_query as $notify_user) {

  $subject = "Напоминание об продлении " . $notify_user["service_name"];

  $message = '
    <p><span style="font-size:16px"><strong>Доброго времени суток</strong></span></p>
    <br>
    <p>Срок Вашей привилегии <strong>'.$notify_user["service_name"].'</strong> на сайте <a href="'.$GLOBALS["site_url"].'">'.$GLOBALS["site_url"].'</a> сервера '.$GLOBALS["site_name"].' (IP: '.$GLOBALS["server_ip"].') подходит к концу.</p>
    <p><strong>Дата окончания:</strong> '.formatDateString($notify_user["service_end"]).'</p>
    <p><strong>Ссылка для продления:</strong> <a href="'.$GLOBALS["site_url"].'profile?section=prolong">'.$GLOBALS["site_url"].'profile?section=prolong</a></p>
    <p>Если Вы не успели продлить привилегию, Вы всегда можете оформить новую покупку на странице <a href="'.$GLOBALS["site_url"].'buy">'.$GLOBALS["site_url"].'buy</a>.</p>
    <p>Не забудьте авторизироваться на сайте перед продлением или новой покупкой.</p>
    <p>Это автоматическое письмо, отвечать на него не нужно.</p>
    <br>
    <p>С уважением,<br>Администрация <a href="'.$GLOBALS["site_url"].'">'.$GLOBALS["site_name"].'</a></p>
  ';

  sendMail($notify_user["user_email"], $subject, $message, $GLOBALS["site_email"]);
  
}

?>