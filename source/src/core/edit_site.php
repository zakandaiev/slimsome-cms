<?php

require_once("db_connect.php");
require_once("db_settings.php");
require_once("core.php");
require_once("is_user_logged.php");
require_once("is_user_admin.php");
require_once("is_user_moder.php");

if (!$is_user_admin) {
  echo json_encode(array("success" => -1, "error" => "У вас нет прав"));
  exit();
}

if (!isset($_POST["site_name"]) || empty($_POST["site_name"])) {
  echo json_encode(array("success" => 0, "error" => "Введите название сайта"));
  exit();
}

if (!isset($_POST["site_url"]) || empty($_POST["site_url"])) {
  echo json_encode(array("success" => 0, "error" => "Введите URL сайта"));
  exit();
}

if (!isset($_POST["site_email"]) || empty($_POST["site_email"])) {
  echo json_encode(array("success" => 0, "error" => "Введите E-mail администратора сайта"));
  exit();
}

$site_name = filter_var(trim($_POST["site_name"]), FILTER_SANITIZE_STRING);
$site_url = filter_var(trim($_POST["site_url"]), FILTER_SANITIZE_STRING);
$site_email = filter_var(trim($_POST["site_email"]), FILTER_SANITIZE_STRING);

if (mb_strlen($site_name) < 2) {
  echo json_encode(array("success" => 0, "error" => "Слишком короткое название сайта"));
  exit();
}
if (mb_strlen($site_name) > 128) {
  echo json_encode(array("success" => 0, "error" => "Слишком длинное название сайта"));
  exit();
}

$content_update_query = $pdo->prepare("
  UPDATE ".$prefix."_settings SET value=:site_name WHERE name='site_name';
  UPDATE ".$prefix."_settings SET value=:site_logo WHERE name='site_logo';
  UPDATE ".$prefix."_settings SET value=:site_url WHERE name='site_url';
  UPDATE ".$prefix."_settings SET value=:site_email WHERE name='site_email';
  UPDATE ".$prefix."_settings SET value=:site_color_accent WHERE name='site_color_accent';
  UPDATE ".$prefix."_settings SET value=:site_color_accent_2 WHERE name='site_color_accent_2';
  UPDATE ".$prefix."_settings SET value=:site_color_body WHERE name='site_color_body';
  UPDATE ".$prefix."_settings SET value=:site_color_text WHERE name='site_color_text';
  UPDATE ".$prefix."_settings SET value=:site_background WHERE name='site_background';
  UPDATE ".$prefix."_settings SET value=:site_background_styles WHERE name='site_background_styles';
  UPDATE ".$prefix."_settings SET value=:site_description WHERE name='site_description';
  UPDATE ".$prefix."_settings SET value=:site_keywords WHERE name='site_keywords';
  UPDATE ".$prefix."_settings SET value=:site_analytics_gtag WHERE name='site_analytics_gtag';
  UPDATE ".$prefix."_settings SET value=:site_chat_enabled WHERE name='site_chat_enabled';
  UPDATE ".$prefix."_settings SET value=:site_chat_enabled_for_unregistereds WHERE name='site_chat_enabled_for_unregistereds';
");

$site_name = filter_var(trim($_POST["site_name"]), FILTER_SANITIZE_STRING);
$content_update_query->bindParam(":site_name", $site_name, PDO::PARAM_STR);
$site_url = filter_var(trim($_POST["site_url"]), FILTER_SANITIZE_STRING);
$content_update_query->bindParam(":site_url", $site_url, PDO::PARAM_STR);
$site_email = filter_var(trim($_POST["site_email"]), FILTER_SANITIZE_STRING);
$content_update_query->bindParam(":site_email", $site_email, PDO::PARAM_STR);
if (empty($_POST["site_logo_str"])) {
  $site_logo = null;
} else {
  $site_logo = trim($_POST["site_logo_str"]);
}
$content_update_query->bindParam(":site_logo", $site_logo);
if (!isset($_POST["site_color_accent"]) || empty($_POST["site_color_accent"])) {
  echo json_encode(array("success" => 0, "error" => "Введите первичный цвет"));
  exit();
} else {
  $site_color_accent = trim($_POST["site_color_accent"]);
  $content_update_query->bindParam(":site_color_accent", $site_color_accent, PDO::PARAM_STR);
  if (mb_strlen($site_color_accent) > 32) {
    echo json_encode(array("success" => 0, "error" => "Слишком длинный первичный цвет"));
    exit();
  }
}
if (!isset($_POST["site_color_accent_2"]) || empty($_POST["site_color_accent_2"])) {
  echo json_encode(array("success" => 0, "error" => "Введите вторичный цвет"));
  exit();
} else {
  $site_color_accent_2 = trim($_POST["site_color_accent_2"]);
  $content_update_query->bindParam(":site_color_accent_2", $site_color_accent_2, PDO::PARAM_STR);
  if (mb_strlen($site_color_accent_2) > 32) {
    echo json_encode(array("success" => 0, "error" => "Слишком длинный вторичный цвет"));
    exit();
  }
}
if (!isset($_POST["site_color_body"]) || empty($_POST["site_color_body"])) {
  echo json_encode(array("success" => 0, "error" => "Введите цвет фона"));
  exit();
} else {
  $site_color_body = trim($_POST["site_color_body"]);
  $content_update_query->bindParam(":site_color_body", $site_color_body, PDO::PARAM_STR);
  if (mb_strlen($site_color_body) > 32) {
    echo json_encode(array("success" => 0, "error" => "Слишком длинный цвет фона"));
    exit();
  }
}
if (!isset($_POST["site_color_text"]) || empty($_POST["site_color_text"])) {
  echo json_encode(array("success" => 0, "error" => "Введите цвет фона"));
  exit();
} else {
  $site_color_text = trim($_POST["site_color_text"]);
  $content_update_query->bindParam(":site_color_text", $site_color_text, PDO::PARAM_STR);
  if (mb_strlen($site_color_text) > 32) {
    echo json_encode(array("success" => 0, "error" => "Слишком длинный цвет текста"));
    exit();
  }
}
if (empty($_POST["site_background_str"])) {
  $site_background = null;
} else {
  $site_background = trim($_POST["site_background_str"]);
}
$content_update_query->bindParam(":site_background", $site_background);
if (!isset($_POST["site_background_styles"]) || empty($_POST["site_background_styles"])) {
  $site_background_styles = null;
  $content_update_query->bindParam(":site_background_styles", $site_background_styles, PDO::PARAM_NULL);
} else {
  $site_background_styles = filter_var(trim($_POST["site_background_styles"]), FILTER_SANITIZE_STRING);
  $content_update_query->bindParam(":site_background_styles", $site_background_styles, PDO::PARAM_STR);
  if (mb_strlen($site_background_styles) > 1024) {
    echo json_encode(array("success" => 0, "error" => "Слишком длинные стили фонового изображения"));
    exit();
  }
}
if (!isset($_POST["site_description"]) || empty($_POST["site_description"])) {
  $site_description = null;
  $content_update_query->bindParam(":site_description", $site_description, PDO::PARAM_NULL);
} else {
  $site_description = filter_var(trim($_POST["site_description"]), FILTER_SANITIZE_STRING);
  $content_update_query->bindParam(":site_description", $site_description, PDO::PARAM_STR);
  if (mb_strlen($site_description) > 320) {
    echo json_encode(array("success" => 0, "error" => "Слишком длинное SEO описание"));
    exit();
  }
}
if (!isset($_POST["site_keywords"]) || empty($_POST["site_keywords"])) {
  $site_keywords = null;
  $content_update_query->bindParam(":site_keywords", $site_keywords, PDO::PARAM_NULL);
} else {
  $site_keywords = filter_var(trim($_POST["site_keywords"]), FILTER_SANITIZE_STRING);
  $content_update_query->bindParam(":site_keywords", $site_keywords, PDO::PARAM_STR);
  if (mb_strlen($site_keywords) > 420) {
    echo json_encode(array("success" => 0, "error" => "Слишком длинные SEO ключевые слова"));
    exit();
  }
}
if (!isset($_POST["site_analytics_gtag"]) || empty($_POST["site_analytics_gtag"])) {
  $site_analytics_gtag = null;
  $content_update_query->bindParam(":site_analytics_gtag", $site_analytics_gtag, PDO::PARAM_NULL);
} else {
  $site_analytics_gtag = filter_var(trim($_POST["site_analytics_gtag"]), FILTER_SANITIZE_STRING);
  $content_update_query->bindParam(":site_analytics_gtag", $site_analytics_gtag, PDO::PARAM_STR);
  if (mb_strlen($site_analytics_gtag) > 12) {
    echo json_encode(array("success" => 0, "error" => "Слишком длинный идентификатор Google Analytics"));
    exit();
  }
}
if (isset($_POST["site_chat_enabled"]) && $_POST["site_chat_enabled"] == 'on') {
  $site_chat_enabled = 'true';
} else {
  $site_chat_enabled = 'false';
}
$content_update_query->bindParam(":site_chat_enabled", $site_chat_enabled);
if (isset($_POST["site_chat_enabled_for_unregistereds"]) && $_POST["site_chat_enabled_for_unregistereds"] == 'on') {
  $site_chat_enabled_for_unregistereds = 'true';
} else {
  $site_chat_enabled_for_unregistereds = 'false';
}
$content_update_query->bindParam(":site_chat_enabled_for_unregistereds", $site_chat_enabled_for_unregistereds);

try {
  $content_update_query->execute();
  echo json_encode(array("success" => 1));
} catch(PDOException $error) { 
  echo json_encode(array("error" => 0, "error" => $error->getMessage()));
}

?>