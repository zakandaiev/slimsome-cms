<?php

function validateLogin($login) {
  if(empty($login)) {
    return "Укажите логин";
  } else if(mb_strlen($login) < 2) {
    return "Логин слишком короткий";
  } else if(mb_strlen($login) > 64) {
    return "Логин слишком длинный";
  } else if (!preg_match("/^[\w]{2,64}+$/", $login)) {
    return "Логин должен содержать только английские буквы и цифры";
  }

  $restricted_nicks = file("nicks.txt");
    foreach ($restricted_nicks as $row) {
    if (stripos($row, $login) !== false) {
      return "Такой логин запрещен";
    }
  }

  return "valid";
}

function validatePassword($password) {
  if(empty($password)) {
    return "Укажите пароль";
  } else if(mb_strlen($password) < 4) {
    return "Пароль слишком короткий";
  } else if(mb_strlen($password) > 32) {
    return "Пароль слишком длинный";
  } else if (!preg_match("/^[A-Za-z0-9]{4,32}+$/", $password)) {
    return "Пароль должен содержать только английские буквы и цифры";
  }
  return "valid";
}

function validateEmail($email) {
  if(empty($email)) {
    return "Укажите e-mail";
  } else if(mb_strlen($email) < 6) {
    return "E-mail слишком короткий";
  } else if(mb_strlen($email) > 128) {
    return "E-mail слишком длинный";
  } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    return "Неверный формат e-mail";
  }
  return "valid";
}

function validateUserName($name) {
  if(empty($name)) {
    return "Укажите имя";
  } else if(mb_strlen($name) < 2) {
    return "Имя слишком короткое";
  } else if(mb_strlen($name) > 32) {
    return "Имя слишком длинное";
  } else if (!preg_match("/^[а-яёА-ЯЁ]+$/u", $name)) {
    return "Имя должно содержать только русские буквы";
  }
  return "valid";
}

function validateUserNick($nick) {
  if(empty($nick)) {
    return "Укажите ник";
  } else if(mb_strlen($nick) < 2) {
    return "Ник слишком короткий";
  } else if(mb_strlen($nick) > 64) {
    return "Ник слишком длинный";
  } else if (preg_match('/"|<|>/', $nick)) {
    return "Игровой ник содержит запрещённые символы";
  }

  $restricted_nicks = file("nicks.txt");
    foreach ($restricted_nicks as $row) {
    if (stripos($row, $nick) !== false) {
      return "Такой ник запрещен";
    }
  }

  return "valid";
}

function validateSteamId($steam) {
  if(empty($steam)) {
    return "Укажите Steam ID";
  } else if (!preg_match("/^STEAM_[0-9]:[0-9]:[0-9]{5,10}$/", $steam)) {
    return "Неверный формат Steam ID";
  }
  return "valid";
}

function validateChatMessage($message) {
  if(empty($message)) {
    return "Укажите сообщение";
  } else if(mb_strlen($message) < 2) {
    return "Сообщение слишком короткое";
  } else if(mb_strlen($message) > 100) {
    return "Сообщение слишком длинное";
  }
  return "valid";
}

function validateComment($comment) {
  if(empty($comment)) {
    return "Укажите комментарий";
  } else if(mb_strlen($comment) < 2) {
    return "Комментарий слишком короткий";
  } else if(mb_strlen($comment) > 250) {
    return "Комментарий слишком длинный";
  }
  return "valid";
}

function validateUrl($url) {
  if(empty($url)) {
    return "Укажите ссылку";
  } else if(mb_strlen($url) < 2) {
    return "Ссылка слишком короткая";
  } else if(mb_strlen($url) > 128) {
    return "Ссылка слишком длинная";
  }
  return "valid";
}

function validateSocialIcon($icon) {
  if(empty($icon)) {
    return "Укажите иконку";
  } else if(mb_strlen($icon) < 2) {
    return "Название иконки слишком короткое";
  } else if(mb_strlen($icon) > 64) {
    return "Название иконки слишком длинное";
  }
  return "valid";
}

function validatePageTitle($title) {
  if(empty($title)) {
    return "Укажите название";
  } else if(mb_strlen($title) < 2) {
    return "Название слишком короткое";
  } else if(mb_strlen($title) > 64) {
    return "Название слишком длинное";
  }
  return "valid";
}

function validateNewsTitle($title) {
  if(empty($title)) {
    return "Укажите название";
  } else if(mb_strlen($title) < 2) {
    return "Название слишком короткое";
  } else if(mb_strlen($title) > 64) {
    return "Название слишком длинное";
  }
  return "valid";
}

function validateServiceTitle($title) {
  if(empty($title)) {
    return "Укажите название";
  } else if(mb_strlen($title) < 2) {
    return "Название слишком короткое";
  } else if(mb_strlen($title) > 64) {
    return "Название слишком длинное";
  }
  return "valid";
}

function validateServiceFlags($flags) {
  if(empty($flags)) {
    return "Укажите флаги";
  } else if (!preg_match("/^[a-z]+$/", $flags)) {
    return "Флаги должны содержать только маленькие английские буквы";
  } else if (mb_strlen($flags) > 32) {
    return "Превышена максимальная длина флагов";
  }
  return "valid";
}

function validateServiceDays($days, $type = "days") {
  switch ($type) {
    case "days": {
      $type_msg_empty = "дни";
      $type_msg_preg_match = "дней";
      break;
    }
    case "days_rub": {
      $type_msg_empty = "цены в руб.";
      $type_msg_preg_match = "цен в руб.";
      break;
    }
    case "days_uah": {
      $type_msg_empty = "цены в грн.";
      $type_msg_preg_match = "цен в грн.";
      break;
    }
    default: {
      $type_msg_empty = "дни";
      $type_msg_preg_match = "дней";
      break;
    }
  }
  if(!isset($days)) {
    return "Заполните " . $type_msg_empty;
  } else if (!preg_match("/^(?:\d+,)*\d+$/", $days)) {
    return "Неверно заполнен формат " . $type_msg_preg_match;
  }
  return "valid";
}

?>