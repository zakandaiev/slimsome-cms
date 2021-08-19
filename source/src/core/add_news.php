<?php

require_once("db_connect.php");
require_once("db_settings.php");
require_once("core.php");
require_once("is_user_logged.php");
require_once("is_user_admin.php");
require_once("is_user_moder.php");

if (!$is_user_admin && !$is_user_moder) {
  echo json_encode(array("success" => -1, "error" => "У вас нет прав"));
  exit();
}

$title = filter_var(trim($_POST["title"]), FILTER_SANITIZE_STRING);
$url = filter_var(trim($_POST["url"]), FILTER_SANITIZE_STRING);
$body = trim($_POST["body"]);
$meta_description = filter_var(trim($_POST["meta_description"]), FILTER_SANITIZE_STRING);
$meta_keywords = filter_var(trim($_POST["meta_keywords"]), FILTER_SANITIZE_STRING);
$author = filter_var(trim($_POST["author"]), FILTER_SANITIZE_NUMBER_INT);
$cdate = filter_var(trim($_POST["cdate"]), FILTER_SANITIZE_STRING);
if (isset($_POST["enabled"]) && $_POST["enabled"] == "on") {
  $enabled = true;
} else {
  $enabled = false;
}

$validate_title = validateNewsTitle($title);
$validate_url = validateUrl($url);

if($validate_title !== "valid") {
  echo json_encode(array("success" => 0, "error" => $validate_title));
  exit();
}

if($validate_url !== "valid") {
  echo json_encode(array("success" => 0, "error" => $validate_url));
  exit();
}

$add_news_query = $pdo->prepare("
  INSERT INTO ".$prefix."_news
    (author, title, url, body, image, meta_description, meta_keywords, enabled, cdate)
  VALUES
    (:author, :title, :url, :body, :image, :meta_description, :meta_keywords, :enabled, :cdate)
");
if (empty($author)) {
  $author = null;
}
$add_news_query->bindParam(":author", $author);
$add_news_query->bindParam(":title", $title);
$add_news_query->bindParam(":url", $url);
if (empty($body)) {
  $body = null;
}
$add_news_query->bindParam(":body", $body);
if (empty($_POST["image_str"])) {
  $image = null;
} else {
  $image = trim($_POST["image_str"]);
}
$add_news_query->bindParam(":image", $image);
if (empty($meta_description)) {
  $meta_description = null;
}
$add_news_query->bindParam(":meta_description", $meta_description);
if (empty($meta_keywords)) {
  $meta_keywords = null;
}
$add_news_query->bindParam(":meta_keywords", $meta_keywords);
$add_news_query->bindParam(":enabled", $enabled, PDO::PARAM_BOOL);
$add_news_query->bindParam(":cdate", $cdate);

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
  $add_news_query->execute();
  generateSitemapXml();
  echo json_encode(array("success" => 1));
} catch(PDOException $error) { 
  if (preg_match("/Duplicate entry .+ for key '(.+)'/", $error->getMessage(), $matches)) {
    $arr_column_names = array(
      "url" => "ссылка"
    );
    if (!array_key_exists($matches[1], $arr_column_names)) {
      $column_name = $matches[1];
    } else {
      $column_name = $arr_column_names[$matches[1]];
    }
    echo json_encode(array("success" => 0, "error" => "Такая ".$column_name." уже существует"));
  } else {
    echo json_encode(array("success" => 0, "error" => $error->getMessage()));
  }
}

?>