<?php

require_once("validations.php");

function getTempDir() {
  $doc_root = $_SERVER["DOCUMENT_ROOT"];
  return substr($doc_root, 0, strpos($doc_root, "data"))."data/tmp";
}

function getServiceName($id) {
  if (!isset($id)) {
    return "Ошибка: укажите id привилегии";
  }

  global $pdo;
  global $prefix;

  $get_service_name_query = $pdo->prepare("SELECT name FROM ".$prefix."_services WHERE id=:id LIMIT 1");
  $get_service_name_query->bindParam(":id",$id);

  $get_service_name_query->execute();
  $sn_result = $get_service_name_query->fetch(PDO::FETCH_LAZY);

  if ($sn_result && $sn_result->name != null) {
    return $sn_result->name;
  } else {
    return "Название не заполнено";
  }
}

function getUserPosition($id,$login) {
  if(!isset($id) && !isset($login)) {
    return "Ошибка: укажите id или login пользователя";
  }

  global $pdo;
  global $prefix;

  if (isset($id)) {
    $key_str = "id";
  } else if (isset($login)) {
    $key_str = "login";
  } else {
    return 'Пользователь';
  }

  $get_user_position_query = $pdo->prepare("
    SELECT
      (CASE 
        WHEN (SELECT name FROM ".$prefix."_services WHERE id=(SELECT service_id FROM ".$prefix."_users WHERE ".$key_str."=:key_value AND service_id IS NOT NULL AND (service_nolimit IS TRUE OR CURRENT_TIMESTAMP < service_end)) AND enabled IS TRUE) IS NOT NULL
        THEN (SELECT name FROM ".$prefix."_services WHERE id=(SELECT service_id FROM ".$prefix."_users WHERE ".$key_str."=:key_value AND service_id IS NOT NULL AND (service_nolimit IS TRUE OR CURRENT_TIMESTAMP < service_end)) AND enabled IS TRUE)
        WHEN (SELECT isadmin FROM ".$prefix."_users WHERE ".$key_str."=:key_value) = 1
        THEN 'Администратор сайта'
        WHEN (SELECT ismoder FROM ".$prefix."_users WHERE ".$key_str."=:key_value) = 1
        THEN 'Модератор сайта'
        ELSE 'Пользователь'
      END) as position
  ");

  if (isset($id)) {
    $get_user_position_query->bindParam(":key_value", $id);
  } else if (isset($login)) {
    $get_user_position_query->bindParam(":key_value", $login);
  }

  $get_user_position_query->execute();

  $up_result = $get_user_position_query->fetch(PDO::FETCH_LAZY);

  if($up_result && $up_result->position !== null) {
    return $up_result->position;
  } else {
    return "Пользователь";
  }
}

function getUserAvatar($id,$login) {
  if (!isset($id) && !isset($login)) {
    return "Ошибка: укажите id или login пользователя";
  }

  global $pdo;
  global $prefix;

  if (isset($id)) {
    $key_str = "id";
  } else if (isset($login)) {
    $key_str = "login";
  } else {
    return 'img/no_avatar.jpg';
  }

  $get_user_avatar_query = $pdo->prepare("
    SELECT
    (CASE 
      WHEN (SELECT user_avatar FROM ".$prefix."_services WHERE id=(SELECT service_id FROM ".$prefix."_users WHERE ".$key_str."=:key_value AND service_id IS NOT NULL AND (service_nolimit IS TRUE OR CURRENT_TIMESTAMP < service_end)) AND enabled IS TRUE) IS NOT NULL
      THEN (SELECT user_avatar FROM ".$prefix."_services WHERE id=(SELECT service_id FROM ".$prefix."_users WHERE ".$key_str."=:key_value AND service_id IS NOT NULL AND (service_nolimit IS TRUE OR CURRENT_TIMESTAMP < service_end)) AND enabled IS TRUE)
      ELSE 'img/no_avatar.jpg'
    END) as avatar
  ");

  if (isset($id)) {
    $get_user_avatar_query->bindParam(":key_value", $id);
  } else if (isset($login)) {
    $get_user_avatar_query->bindParam(":key_value", $login);
  }

  $get_user_avatar_query->execute();
  $ua_result = $get_user_avatar_query->fetch(PDO::FETCH_LAZY);

  if($ua_result && $ua_result->avatar !== null) {
    return urlEncodeSpaces($ua_result->avatar);
  } else {
    return "img/no_avatar.jpg";
  }
}

function getUserInfo($id,$login) {
  if (!isset($id) && !isset($login)) {
    return "Ошибка: укажите id или login пользователя";
  }

  global $pdo;
  global $prefix;

  if (isset($id)) {
    $key_str = "id";
  } else if (isset($login)) {
    $key_str = "login";
  } else {
    return false;
  }

  $get_user_info_query = $pdo->prepare("SELECT * FROM ".$prefix."_users WHERE ".$key_str."=:key_value LIMIT 1");

  if (isset($id)) {
    $get_user_info_query->bindParam(":key_value", $id);
  } else if (isset($login)) {
    $get_user_info_query->bindParam(":key_value", $login);
  }

  $get_user_info_query->execute();

  return $get_user_info_query->fetch(PDO::FETCH_LAZY);
}

function getUserMail($id,$login) {
  if (!isset($id) && !isset($login)) {
    return "Ошибка: укажите id или login пользователя";
  }

  global $pdo;
  global $prefix;

  if (isset($id)) {
    $key_str = "id";
  } else if (isset($login)) {
    $key_str = "login";
  } else {
    return null;
  }

  $get_user_mail_query = $pdo->prepare("SELECT email FROM ".$prefix."_users WHERE ".$key_str."=:key_value LIMIT 1");

  if (isset($id)) {
    $get_user_mail_query->bindParam(":key_value", $id);
  } else if (isset($login)) {
    $get_user_mail_query->bindParam(":key_value", $login);
  }

  $get_user_mail_query->execute();
  $um_result = $get_user_mail_query->fetch(PDO::FETCH_LAZY);

  if($um_result && $um_result->email !== null) {
    return $um_result->email;
  } else {
    return null;
  }
}

function isUserActive($id,$login) {
  if (!isset($id) && !isset($login)) {
    return "Ошибка: укажите id или login пользователя";
  }

  global $pdo;
  global $prefix;

  if (isset($id)) {
    $is_user_active_query = $pdo->prepare("
      SELECT id FROM ".$prefix."_users t_users
      WHERE
        id=:id
      AND
        service_id is not NULL
      AND
        (service_nolimit IS TRUE OR CURRENT_TIMESTAMP < service_end)
      AND
        (SELECT enabled FROM ".$prefix."_services WHERE id=t_users.service_id AND enabled IS TRUE)
    ");
    $is_user_active_query->bindParam(":id", $id);
  } else if (isset($login)) {
    $is_user_active_query = $pdo->prepare("
      SELECT id FROM ".$prefix."_users t_users
      WHERE
        login=:login
      AND
        service_id is not NULL
      AND
        (service_nolimit IS TRUE OR CURRENT_TIMESTAMP < service_end)
      AND
        (SELECT enabled FROM ".$prefix."_services WHERE id=t_users.service_id AND enabled IS TRUE)
    ");
    $is_user_active_query->bindParam(":login", $login);
  } else {
    return false;
  }

  $is_user_active_query->execute();

  if ($is_user_active_query->rowCount() == 1) {
    return true;
  } else {
    return false;
  }
}

function getPlayerAvatar($nick) {
  if (!isset($nick)) {
    return "Ошибка: укажите ник игрока";
  }

  $nick = filter_var(trim($nick), FILTER_SANITIZE_STRING);

  global $pdo;
  global $prefix;

  $get_player_avatar_query = $pdo->prepare("
    SELECT
    (CASE 
      WHEN (SELECT user_avatar FROM ".$prefix."_services WHERE id=(SELECT service_id FROM ".$prefix."_users WHERE nick=:nick AND service_id IS NOT NULL AND (service_nolimit IS TRUE OR CURRENT_TIMESTAMP < service_end))) IS NOT NULL
      THEN (SELECT user_avatar FROM ".$prefix."_services WHERE id=(SELECT service_id FROM ".$prefix."_users WHERE nick=:nick AND service_id IS NOT NULL AND (service_nolimit IS TRUE OR CURRENT_TIMESTAMP < service_end)))
      ELSE NULL
    END) as avatar
  ");
  $get_player_avatar_query->bindParam(":nick", $nick);

  $get_player_avatar_query->execute();
  $pa_result = $get_player_avatar_query->fetch(PDO::FETCH_LAZY);

  if($pa_result && $pa_result->avatar !== NULL) {
    return urlEncodeSpaces($pa_result->avatar);
  } else {
    return NULL;
  }
}

function getPlayerPosition($nick) {
  if (!isset($nick)) {
    return "Ошибка: укажите ник игрока";
  }

  $nick = filter_var(trim($nick), FILTER_SANITIZE_STRING);

  global $pdo;
  global $prefix;

  $get_player_position_query = $pdo->prepare("
    SELECT
    (CASE 
      WHEN (SELECT name FROM ".$prefix."_services WHERE id=(SELECT service_id FROM ".$prefix."_users WHERE nick=:nick AND service_id IS NOT NULL AND (service_nolimit IS TRUE OR CURRENT_TIMESTAMP < service_end))) IS NOT NULL
      THEN (SELECT name FROM ".$prefix."_services WHERE id=(SELECT service_id FROM ".$prefix."_users WHERE nick=:nick AND service_id IS NOT NULL AND (service_nolimit IS TRUE OR CURRENT_TIMESTAMP < service_end)))
      WHEN (SELECT isadmin FROM ".$prefix."_users WHERE nick=:nick) = 1
      THEN 'Администратор сайта'
      WHEN (SELECT ismoder FROM ".$prefix."_users WHERE nick=:nick) = 1
      THEN 'Модератор сайта'
      ELSE 'Пользователь'
    END) as position
  ");
  $get_player_position_query->bindParam(":nick", $nick);

  $get_player_position_query->execute();
  $pp_result = $get_player_position_query->fetch(PDO::FETCH_LAZY);

  if($pp_result && $pp_result->position !== NULL) {
    return $pp_result->position;
  } else {
    return "Пользователь";
  }
}

function getSvg($file) {
  if (!file_exists($file)) {
    return "Файл '.$file.' не существует";
  } else {
    return file_get_contents($file);
  }
}

function replaceSmiles($text) {
  $smiles = array(
    ':ban:' => '<img src="/img/smiles/ban.gif" alt="ban">',
    ':beer:' => '<img src="/img/smiles/beer.gif" alt="beer">',
    ':bomb:' => '<img src="/img/smiles/bomb.gif" alt="bomb">',
    ':cool:' => '<img src="/img/smiles/cool.gif" alt="cool">',
    '8)' => '<img src="/img/smiles/cool.gif" alt="cool">',
    ':crazy:' => '<img src="/img/smiles/crazy.gif" alt="crazy">',
    'oO' => '<img src="/img/smiles/crazy.gif" alt="crazy">',
    'o0' => '<img src="/img/smiles/crazy.gif" alt="crazy">',
    'Oo' => '<img src="/img/smiles/crazy.gif" alt="crazy">',
    '0o' => '<img src="/img/smiles/crazy.gif" alt="crazy">',
    ':devil:' => '<img src="/img/smiles/devil.gif" alt="devil">',
    '>:)' => '<img src="/img/smiles/devil.gif" alt="devil">',
    ':eek:' => '<img src="/img/smiles/eek.gif" alt="eek">',
    'OO' => '<img src="/img/smiles/eek.gif" alt="eek">',
    ':fu:' => '<img src="/img/smiles/fu.gif" alt="fu">',
    ':lol:' => '<img src="/img/smiles/lol.gif" alt="lol">',
    'xD' => '<img src="/img/smiles/lol.gif" alt="lol">',
    ':love:' => '<img src="/img/smiles/love.gif" alt="love">',
    '<3' => '<img src="/img/smiles/love.gif" alt="love">',
    ':ms:' => '<img src="/img/smiles/ms.gif" alt="ms">',
    ':nice:' => '<img src="/img/smiles/nice.gif" alt="nice">',
    ':razz:' => '<img src="/img/smiles/razz.gif" alt="razz">',
    ':p' => '<img src="/img/smiles/razz.gif" alt="razz">',
    ':red:' => '<img src="/img/smiles/red.gif" alt="red">',
    ':.' => '<img src="/img/smiles/red.gif" alt="red">',
    ':sad:' => '<img src="/img/smiles/sad.gif" alt="sad">',
    ':(' => '<img src="/img/smiles/sad.gif" alt="sad">',
    ':shrug:' => '<img src="/img/smiles/shrug.gif" alt="shrug">',
    ':smile:' => '<img src="/img/smiles/smile.gif" alt="smile">',
    ':)' => '<img src="/img/smiles/smile.gif" alt="smile">',
    ':sos:' => '<img src="/img/smiles/sos.gif" alt="sos">',
    ':sps:' => '<img src="/img/smiles/sps.gif" alt="sps">',
    ':stop:' => '<img src="/img/smiles/stop.gif" alt="stop">',
    ':uzi:' => '<img src="/img/smiles/uzi.gif" alt="uzi">',
    ':wink:' => '<img src="/img/smiles/wink.gif" alt="wink">',
    ';)' => '<img src="/img/smiles/wink.gif" alt="wink">',
    ':woohoo:' => '<img src="/img/smiles/woohoo.gif" alt="woohoo">',
    ':xd:' => '<img src="/img/smiles/xd.gif" alt="xd">',
    ':D' => '<img src="/img/smiles/xd.gif" alt="xd">'
  );
  $output = $text;
  foreach($smiles as $smile => $image) {
    /*$smile = preg_quote($smile);
    $output = preg_replace("~\b$smile\b~", $image, $output);*/
    $output = str_replace($smile, $image, $output);
  }
  return $output;
}

function get_numerical_noun_form($number) {
  // Nominative - комментарий
  // Singular - комментария
  // Plural - комментариев
  if ($number > 10 && (($number % 100) / 10) == 1) {
    return "Plural";
  }
  $number = $number % 10;
  if ($number == 1) {
    return "Nominative";
  } else if ($number == 2 || $number == 3 || $number == 4) {
    return "Singular";
  } else {
    return "Plural";
  }
}

function getCommentsNumString($number) {
  if ($number > 0) {
    switch (get_numerical_noun_form($number)) {
      case "Nominative": {
        return $number . " комментарий";
        break;
      }
      case "Singular": {
        return $number . " комментария";
        break;
      }
      case "Plural": {
        return $number . " комментариев";
        break;
      }
      default: {
        return $number . " комментариев";
        break;
      }
    }
  } else {
    return "Нет комментариев";
  }
}

function declOfNum($number, $titles) {
  $cases = array(2, 0, 1, 1, 1, 2);
  return $number . " " . $titles[4 < $number % 100 && $number % 100 < 20 ? 2 : $cases[min($number % 10, 5)]];
}

function dateDiff($startDay, $endDay) {
  if ($endDay - $startDay < 0) {
    return "Срок истек";
  }
  $difference = abs($endDay - $startDay);
  $month = floor($difference / 2592000);
  if (0 < $month) {
    $return["month"] = declOfNum($month, array("месяц", "месяца", "месяцев"));
  }
  $days = floor($difference / 86400) % 30;
  if (0 < $days) {
    $return["days"] = declOfNum($days, array("день", "дня", "дней"));
  }
  $hours = floor($difference / 3600) % 24;
  if (0 < $hours) {
    $return["hours"] = declOfNum($hours, array("час", "часа", "часов"));
  }
  $minutes = floor($difference / 60) % 60;
  if (0 < $minutes) {
    $return["minutes"] = declOfNum($minutes, array("минута", "минуты", "минут"));
  }
  if (0 < count($return)) {
    $datediff = implode(" ", $return);
    return $datediff;
  }
  return "Пару секунд";
}

function dateWhen($timestamp) {
  $getdata = date("d.m.Y", $timestamp);
  $yesterday = date("d.m.Y", mktime(0, 0, 0, date("m"), date("d") - 1, date("Y")));
  if( $getdata == date("d.m.Y") ) {
    $date = date("Сегодня в H:i", $timestamp);
  } else {
    if( $yesterday == $getdata ) {
      $date = date("Вчера в H:i", $timestamp);
    } else{
      $date = date("d.m.Y в H:i", $timestamp);
    }
  }
  return $date;
}

function formatDate($timestamp, $format = null) {
  if ($format === null) {
    return date("d.m.Y в H:i", $timestamp);
  } else {
    return date($format, $timestamp);
  }
}

function formatDateString($timestamp, $format = null) {
  if ($format === null) {
    return date("d.m.Y в H:i", strtotime($timestamp));
  } else {
    return date($format, strtotime($timestamp));
  }
}

function getMonthName($num) {
  switch ($num) {
    case "1": {
      $name = "Январь";
      break;
    }
    case "2": {
      $name = "Февраль";
      break;
    }
    case "3": {
      $name = "Март";
      break;
    }
    case "4": {
      $name = "Апрель";
      break;
    }
    case "5": {
      $name = "Май";
      break;
    }
    case "6": {
      $name = "Июнь";
      break;
    }
    case "7": {
      $name = "Июль";
      break;
    }
    case "8": {
      $name = "Август";
      break;
    }
    case "9": {
      $name = "Сентябрь";
      break;
    }
    case "10": {
      $name = "Октябрь";
      break;
    }
    case "11": {
      $name = "Ноябрь";
      break;
    }
    case "12": {
      $name = "Декабрь";
      break;
    }
    default: {
      $name = "Январь";
      break;
    }
  }
  return $name;
}

function getBanLenght() {
  return array(
    '0'     => 'Навсегда',
    '5'     => '5 минут',
    '10'    => '10 минут',
    '15'    => '15 минут',
    '30'    => '30 минут',
    '60'    => '1 час',
    '120'   => '2 часа',
    '180'   => '3 часа',
    '300'   => '5 часов',
    '600'   => '10 часов',
    '1440'    => '1 день',
    '4320'    => '3 дня',
    '10080'   => '1 неделя',
    '20160'   => '2 недели',
    '43200'   => '1 Месяц',
    '129600'  => '3 месяца',
    '259200'  => '6 месяцев',
    '518400'  => '1 год'
  );
}

function getBanLeft($ban_length, $ban_created) {
  if ($ban_length == 0) {
    return "∞";
  } else {
    return dateDiff(time(), $ban_created + $ban_length * 60);
  }
}

function isNumInRange($number, $min, $max) {
  if ($number >= $min && $number < $max) {
    return true;
  }
  return false;
}

function getStatsLevel($skill) {
  switch ($skill) {
    case isNumInRange($skill, 0, 15): {
      return "low-m";
      break;
    }
    case isNumInRange($skill, 15, 30): {
      return "low";
      break;
    }
    case isNumInRange($skill, 30, 45): {
      return "low-p";
      break;
    }
    case isNumInRange($skill, 45, 50): {
      return "medium-m";
      break;
    }
    case isNumInRange($skill, 50, 55): {
      return "medium";
      break;
    }
    case isNumInRange($skill, 55, 60): {
      return "medium-p";
      break;
    }
    case isNumInRange($skill, 60, 65): {
      return "high-m";
      break;
    }
    case isNumInRange($skill, 65, 70): {
      return "high";
      break;
    }
    case isNumInRange($skill, 70, 75): {
      return "high-p";
      break;
    }
    case isNumInRange($skill, 75, 80): {
      return "profi-m";
      break;
    }
    case isNumInRange($skill, 80, 85): {
      return "profi";
      break;
    }
    case isNumInRange($skill, 85, 90): {
      return "profi-p";
      break;
    }
    case isNumInRange($skill, 90, 100): {
      return "godlike";
      break;
    }
    default: {
      return "low-m";
      break;
    }
  }
}

function getPlayerSkill($kills, $deaths) {
  $sum = $kills + $deaths;
  if($sum == 0) {
    return "?";
  }
  return round(100 * $kills / $sum, 2);
}

function printSkillLabel($kills, $deaths) {
  return '<span class="skill '.getStatsLevel(getPlayerSkill($kills, $deaths)).'"><span>'.getPlayerSkill($kills, $deaths).'</span></span>';
}

function getBindType($int) {
  return $int == 1 ? 'Ник + пароль' : 'Steam ID + пароль';
}

function getCurrency($int) {
  return $int == 2 ? 'грн.' : 'руб.';
}

function getCurrencyCode($int) {
  return $int == 2 ? 'UAH' : 'RUB';
}

function getPaymentStatus($int) {
  return $int == 1 ? 'проведен' : 'отклонен';
}

function generatePassword($length) {
  $string = "";
  $chars = "abcdefghijklmanopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
  $size = strlen($chars);
  for ($i = 0; $i < $length; $i++) {
    $string .= $chars[rand(0, $size - 1)];
  }
  return $string; 
}

function generateUsersIni() {
  global $pdo;
  global $prefix;

  $get_admins_list_query = $pdo->query("
    SELECT *,
      (SELECT flags FROM ".$prefix."_services WHERE id=t_users.service_id AND enabled IS TRUE) as service_flags
    FROM ".$prefix."_users t_users
    WHERE
      service_id is not NULL
    AND
      (service_nolimit IS TRUE OR CURRENT_TIMESTAMP < service_end)
    AND
      (SELECT enabled FROM ".$prefix."_services WHERE id=t_users.service_id AND enabled IS TRUE)
    ORDER BY id ASC
  ");

  $admins_list = $get_admins_list_query->fetchAll(PDO::FETCH_ASSOC);

  $file_content = '; ['. date("d.m.Y H:i:s") . '] Файл сгенерирован автоматически сайтом ' . $GLOBALS["site_url"] . PHP_EOL . PHP_EOL;

  foreach ($admins_list as $admin) {
    if (empty($admin["service_flags"]) || empty($admin["password"])) {
      continue;
    }
    if ($admin["service_bind_type"] == 2) {
      $admin_auth = $admin["steam_id"];
      $amxx_auth_flag = 'ac';
    } else {
      $admin_auth = $admin["nick"];
      $amxx_auth_flag = 'a';
    }
    if (empty($admin_auth)) {
      continue;
    }
    $ini_active_state = '';
    if ($admin["service_nolimit"]) {
      $end_date = '0';
    } else if (time() < strtotime($admin["service_end"])) {
      $end_date = strtotime($admin["service_end"]);
    } else {
      $end_date = strtotime($admin["service_end"]);
      $ini_active_state = '; ';
    }
    $file_content .= $ini_active_state . '"'.$admin_auth.'" "'.$admin["password"].'" "'.$admin["service_flags"].'" "'.$amxx_auth_flag.'" "'.$end_date.'"' . PHP_EOL;
  }

  $file_path = getTempDir()."/users.ini";

  file_put_contents($file_path, $file_content, LOCK_EX);

  return $file_path;
}

function uploadUsersIni($file_path) {
  if (empty($GLOBALS["ftp_host"]) || empty($GLOBALS["ftp_login"]) || empty($GLOBALS["ftp_pass"]) || empty($GLOBALS["ftp_users_path"])) {
    return "Заполните все данные для доступа по FTP";
  }

  if (!file_exists($file_path)) {
    return "Загрузочный файл не доступен";
  }

  $ftp_connection = ftp_connect($GLOBALS["ftp_host"]);
  if (!$ftp_connection) {
    return "Невозможно подключиться по FTP";
  }

  if (@ftp_login($ftp_connection, $GLOBALS["ftp_login"], $GLOBALS["ftp_pass"])) {
    $local_file = $file_path;
    $server_file = $GLOBALS["ftp_users_path"];
    $upload_result = ftp_put($ftp_connection, $server_file, $local_file, FTP_BINARY);
  } else {
    ftp_close($ftp_connection);
    return "Неправильный логин или пароль пользователя FTP";
  }

  ftp_close($ftp_connection);

  if(isset($upload_result)) {
    return $upload_result;
  } else {
    return "Неизвестная ошибка";
  }
}

function sendMail($recepient, $subject, $message, $from) {
  $site_name = $GLOBALS["site_name"];
  $to = trim($recepient);
  $subj = trim($subject);
  $headers = array(
    'Content-type' => 'text/html',
    'charset' => 'utf-8',
    'MIME-Version' => '1.0',
    'From' => $site_name . '<'.$from.'>',
    'Reply-To' => $from
  );

  return mail($to, $subj, $message, $headers);
}

function loginSign($string) {
  $site_name_salt = preg_replace('/\s+/', '', $GLOBALS["site_name"]);
  $salt = "$2a$07$" . $site_name_salt . "$";
  return md5(sha1($string.$salt).$salt);
}

function setLoginCookie($login) {
  setcookie("user_login", $login, time() + 3600 * 24 * 7, "/");
  setcookie("user_hash", loginSign($login), time() + 3600 * 24 * 7, "/");
}
function unsetLoginCookie() {
  setcookie("user_login", "", time(), "/");
  setcookie("user_hash", "", time(), "/");
}

function generateSitemapXml() {
  global $pdo;
  global $prefix;

  $sitemap_xml = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . PHP_EOL;
  $sitemap_xml .= '<url><loc>'.$GLOBALS['site_url'].'</loc><lastmod>'.date('c').'</lastmod><priority>1.00</priority></url>' . PHP_EOL;
  $sitemap_xml .= '<url><loc>'.$GLOBALS['site_url'].'admins</loc><lastmod>'.date('c').'</lastmod><priority>0.80</priority></url>' . PHP_EOL;
  $sitemap_xml .= '<url><loc>'.$GLOBALS['site_url'].'buy</loc><lastmod>'.date('c').'</lastmod><priority>0.80</priority></url>' . PHP_EOL;
  $sitemap_xml .= '<url><loc>'.$GLOBALS['site_url'].'bans</loc><lastmod>'.date('c').'</lastmod><priority>0.80</priority></url>' . PHP_EOL;
  $sitemap_xml .= '<url><loc>'.$GLOBALS['site_url'].'stats</loc><lastmod>'.date('c').'</lastmod><priority>0.80</priority></url>' . PHP_EOL;
  $sitemap_xml .= '<url><loc>'.$GLOBALS['site_url'].'news</loc><lastmod>'.date('c').'</lastmod><priority>0.80</priority></url>' . PHP_EOL;
  $sitemap_xml .= '<url><loc>'.$GLOBALS['site_url'].'restore</loc><lastmod>'.date('c').'</lastmod><priority>0.30</priority></url>' . PHP_EOL;
  $sitemap_xml .= '<url><loc>'.$GLOBALS['site_url'].'registration</loc><lastmod>'.date('c').'</lastmod><priority>0.30</priority></url>' . PHP_EOL;
  $sitemap_xml .= '<url><loc>'.$GLOBALS['site_url'].'privacy-policy</loc><lastmod>'.date('c').'</lastmod><priority>0.30</priority></url>' . PHP_EOL;
  $sitemap_xml .= '<url><loc>'.$GLOBALS['site_url'].'public-offer</loc><lastmod>'.date('c').'</lastmod><priority>0.30</priority></url>' . PHP_EOL;
  $sitemap_xml .= '<url><loc>'.$GLOBALS['site_url'].'processing-of-personal-data</loc><lastmod>'.date('c').'</lastmod><priority>0.30</priority></url>' . PHP_EOL;

  $db_pages = require_once("db_pages.php");

  foreach ($db_pages as $rows) {
    if (!$rows["enabled"]) {
      continue;
    }
    $sitemap_xml .= '<url><loc>'.$GLOBALS["site_url"].'page?'.$rows["url"].'</loc><lastmod>'.date('c').'</lastmod><priority>0.60</priority></url>' . PHP_EOL;
  }

  $db_news_query = $pdo->query("
    SELECT url
    FROM ".$prefix."_news
    WHERE enabled is TRUE
    ORDER BY cdate DESC;
  ");

  $db_news = $db_news_query->fetchAll(PDO::FETCH_ASSOC);

  foreach ($db_news as $rows) {
    $sitemap_xml .= '<url><loc>'.$GLOBALS["site_url"].'news?'.$rows["url"].'</loc><lastmod>'.date('c').'</lastmod><priority>0.60</priority></url>' . PHP_EOL;
  }

  $sitemap_xml .= '</urlset>';

  file_put_contents("../sitemap.xml", $sitemap_xml, LOCK_EX);
}

function urlEncodeSpaces($url) {
  return str_replace(" ", "%20", $url);
}

?>