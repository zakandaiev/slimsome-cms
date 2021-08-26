<?php

if(!is_file("core/db_connect.php")) {
  header("Location: core/install");
  exit();
} else {
  require_once("core/db_connect.php");
}

require_once("core/db_settings.php");
require_once("core/core.php");

// USER INFO
require_once("core/is_user_logged.php");
require_once("core/is_user_admin.php");
require_once("core/is_user_moder.php");

if(!$is_user_logged) {
  $user_login = null;
} else {
  $user_login = $_COOKIE["user_login"];
}
$user_position = getUserPosition(null, $user_login);
$user_avatar = getUserAvatar(null, $user_login);

// PAGES
$db_pages = require_once("core/db_pages.php");
$current_page = str_replace(".php", "", $_SERVER["PHP_SELF"]);
$current_page = substr($current_page, 1);
// LOCK PROFILE
if($current_page == "profile" && !$is_user_logged) {
  header("Location: /");
  exit();
}
// LOCK REGISTRATION
if($current_page == "registration" && $is_user_logged) {
  header("Location: /");
  exit();
}
// LOCK RESTORE
if($current_page == "restore" && $is_user_logged) {
  header("Location: /");
  exit();
}
switch ($current_page) {
  case "admins": {
    $page_name = "Админы";
    break;
  }
  case "buy": {
    $page_name = "Купить";
    break;
  }
  case "bans": {
    $page_name = "Банлист";
    break;
  }
  case "stats": {
    $page_name = "Статистика игроков";
    break;
  }
  case "news": {
    $page_name = "Новости";
    break;
  }
  case "profile": {
    $page_name = "Профиль";
    if(!empty($_GET["section"])) {
      $current_nav = $_GET["section"];
      $page_name_profile_addition = '';
      if($current_nav == "billing") {$page_name_profile_addition = "История покупок";}
      if($current_nav == "site") {$page_name_profile_addition = "Настройки сайта";}
      if($current_nav == "settings") {$page_name_profile_addition = "Настройки системы";}
      if($current_nav == "payments") {$page_name_profile_addition = "Настройки оплаты";}
      if($current_nav == "services") {$page_name_profile_addition = "Настройки привилегий";}
      if($current_nav == "users") {$page_name_profile_addition = "Настройки пользователей";}
      if($current_nav == "socials") {$page_name_profile_addition = "Настройки соц. сетей";}
      if($current_nav == "pages") {$page_name_profile_addition = "Настройки страниц";}
      if($current_nav == "news") {$page_name_profile_addition = "Новости";}
      if($current_nav == "stats") {$page_name_profile_addition = "Статистика";}
      if(!empty($page_name_profile_addition)) {
        $page_name = $page_name_profile_addition . " - " . $page_name;
      }
    }
    break;
  }
  case "gamechat": {
    $page_name = "Игровой чат";
    break;
  }
  case "restore": {
    $page_name = "Восстановление доступа";
    break;
  }
  case "registration": {
    $page_name = "Регистрация";
    break;
  }
  case "privacy-policy": {
    $page_name = "Политика конфиденциальности";
    break;
  }
  case "public-offer": {
    $page_name = "Публичная оферта";
    break;
  }
  case "processing-of-personal-data": {
    $page_name = "Политика обработки персональных данных";
    break;
  }
  default: {
    $page_name = "Главная";
    break;
  }
}
// Titles for www/pages/*.php
foreach ($db_pages as $rows) {
  if($current_page == "page" && key($_GET) == $rows["url"]) {
    $page_name = $rows["name"];
    break;
  }
}

$meta_description = $GLOBALS["site_description"];
$meta_keywords = $GLOBALS["site_keywords"];

if (!empty($GLOBALS["site_logo"]) && substr($GLOBALS["site_logo"], -3) != "svg") {
  $meta_image = '<meta property="og:image" content="'.$GLOBALS["site_url"].$GLOBALS["site_logo"].'">'.PHP_EOL.'<link rel="image_src" href="'.$GLOBALS["site_url"].urlEncodeSpaces($GLOBALS["site_logo"]).'">';
} else {
  $meta_image = '<meta property="og:image" content="'.$GLOBALS["site_url"].'favicon.png">'.PHP_EOL.'<link rel="image_src" href="'.$GLOBALS["site_url"].'favicon.png">';
}

// Titles & seo for single news
if ($current_page == "news") {
  $db_news = require_once("core/db_news.php");
  foreach ($db_news as $rows) {
    if($current_page == "news" && key($_GET) == $rows["url"]) {
      $page_name = $rows["title"];
      if (!empty($rows["meta_description"])) {
        $meta_description = $rows["meta_description"];
      }
      if (!empty($rows["meta_keywords"])) {
        $meta_keywords = $rows["meta_keywords"];
      }
      if (!empty($rows["image"])) {
        $meta_image = '<meta property="og:image" content="'.$GLOBALS["site_url"].$rows["image"].'">'.PHP_EOL.'<link rel="image_src" href="'.$GLOBALS["site_url"].urlEncodeSpaces($rows["image"]).'">';
      }
      break;
    }
  }
}

// Titles for single stats
if ($current_page == "stats") {
  $player_id = null;

  if(isset($_GET["player"])) {
    $player_id = $_GET["player"];
  }
  
  $player_arr = "not_found";

  if(!empty($player_id)) {
    $get_player_query = $pdo->prepare("SELECT * FROM ".$prefix."_stats WHERE id=:id");
    $get_player_query->bindParam(":id", $player_id);
    $get_player_query->execute();

    $get_player = $get_player_query->fetch(PDO::FETCH_ASSOC);

    if(!empty($get_player)) {
      $player_arr = $get_player;
      $page_name = "Статистика игрока " . htmlspecialchars(trim($player_arr["nick"]));
    }
  }
}

?>

<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1">
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="<?=$GLOBALS["site_name"]?>">
  
  <title><?=$page_name?> - <?=$GLOBALS["site_name"]?></title>
  <meta property="og:title" content="<?=$page_name?> - <?=$GLOBALS["site_name"]?>">

  <meta name="description" content="<?=$meta_description?>">
  <meta property="og:description" content="<?=$meta_description?>">

  <meta name="keywords" content="<?=$meta_keywords?>">
  <meta property="og:keywords" content="<?=$meta_keywords?>">

  <? if (isset($meta_image) && !empty($meta_image)): ?>
    <?= $meta_image ?>
  <? endif; ?>

  <link rel="icon" type="image/png" href="favicon.png">
  <link rel="icon" type="image/svg+xml" href="favicon.svg">
  <link rel="mask-icon" href="favicon.svg">

  <? if($current_page == "index"): ?>
    <link rel="canonical" href="<?=$GLOBALS["site_url"]?>">
    <meta property="og:url" content="<?=$GLOBALS["site_url"]?>">
  <? elseif($current_page == "news"): ?>
    <link rel="canonical" href="<?=$GLOBALS["site_url"].str_replace(".php", "", ltrim($_SERVER["REQUEST_URI"], "/"))?>">
    <meta property="og:url" content="<?=$GLOBALS["site_url"].str_replace(".php", "", ltrim($_SERVER["REQUEST_URI"], "/"))?>">
  <? else: ?>
    <link rel="canonical" href="<?=$GLOBALS["site_url"].$current_page?>">
    <meta property="og:url" content="<?=$GLOBALS["site_url"].$current_page?>">
  <? endif; ?>

  <meta property="og:locale" content="ru_RU">

  <meta name="author" content="github.com/zakandaiev">
  
  <link rel="stylesheet" href="css/main.css">
  <style>
    :root {
      --accent-color: <?= $GLOBALS["site_color_accent"] ?>;
      --accent-color-2: <?= $GLOBALS["site_color_accent_2"] ?>;
      --body-color: <?= $GLOBALS["site_color_body"] ?>;
      --text-color: <?= $GLOBALS["site_color_text"] ?>;
    }
    <? if(!empty($GLOBALS["site_background"])): ?>
      body {
        background-image: url(<?= urlEncodeSpaces($GLOBALS["site_background"]) ?>);
        <? if(!empty($GLOBALS["site_background_styles"])) echo $GLOBALS["site_background_styles"] ?>
      }
    <? endif; ?>
  </style>  
</head>

<body>
  <header class="header">
    <div class="container">
      <div class="header__wrap">
        <span class="header__burger"><? echo getSvg("img/icons/burger.svg") ?></span>
        <nav class="header__nav">
          <a href="/" class="header__nav-item <? if($current_page == "index") echo 'active' ?>">Главная</a>
          <a href="admins" class="header__nav-item <? if($current_page == "admins") echo 'active' ?>">Админы</a>
          <a href="buy" class="header__nav-item <? if($current_page == "buy") echo 'active' ?>">Купить</a>
          <a href="bans" class="header__nav-item <? if($current_page == "bans") echo 'active' ?>">Банлист</a>
          <a href="stats" class="header__nav-item <? if($current_page == "stats") echo 'active' ?>">Статистика</a>
          <a href="news" class="header__nav-item <? if($current_page == "news") echo 'active' ?>">Новости</a>
          <?php
            foreach ($db_pages as $rows) {
              if (!$rows["enabled"]) {
                continue;
              }
              if($current_page == "page" && key($_GET) == $rows["url"]) {
                $active_state = 'active';
              } else {
                $active_state = '';
              }
              echo '<a href="page?'.$rows["url"].'" class="header__nav-item '.$active_state.'">'.$rows["name"].'</a>';
            }
          ?>
        </nav>
        <?php
          if(!$is_user_logged):
        ?>
          <div class="header__login">
            <span class="header__login-btn">Войти <span class="caret caret_white"></span></span>
            <div class="dropdown-menu">
              <form id="login-form" class="form" method="post">
                <input type="text" placeholder="Логин" name="login" required>
                <input type="password" placeholder="Пароль" name="password" required>
                <button class="btn btn_primary" type="submit">Войти</button>
              </form>
              <div>
                <a href="restore">Забыли пароль?</a>
                <a href="registration">Регистрация</a>
              </div>
            </div>
          </div>
        <?php else: ?>
          <a href="profile" class="header__profile <? if($current_page == "profile") echo 'active' ?>">
            <img class="profile__img" src="<?= urlEncodeSpaces($user_avatar) ?>" title="<?= $user_position ?>" alt="<?= $user_position ?>">
            <span>Профиль</span>
          </a>
        <?php endif; ?>
      </div>
    </div>
  </header>
  
  <div class="page-logo">
    <? if(!empty($GLOBALS["site_logo"])): ?>
      <a class="page-logo__img" href="/">
        <? if(substr($GLOBALS["site_logo"], -3) == "svg"): ?>
          <?=getSvg($GLOBALS["site_logo"])?>
        <? else: ?>
          <img src="<?=urlEncodeSpaces($GLOBALS["site_logo"])?>" alt="Логотип">
        <? endif; ?>
      </a>
    <? endif; ?>
    <? if(!empty($GLOBALS["site_name"])): ?>
      <h1 class="page-logo__title"><a href="/"><?=$GLOBALS["site_name"]?></a></h1>
    <? endif; ?>
  </div>
  
  <div class="page-content">
    <div class="container">