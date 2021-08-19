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

if (!isset($_POST["page_id"])) {
  echo json_encode(array("success" => 0, "error" => "Неверный параметр страницы"));
  exit();
}

$page_id = $_POST["page_id"];

$name = filter_var(trim($_POST["name"]), FILTER_SANITIZE_STRING);
$url = filter_var(trim($_POST["url"]), FILTER_SANITIZE_STRING);
if (!isset($_POST["content"]) || empty($_POST["content"])) {
  $content = null;
} else {
  $content = trim($_POST["content"]);
}
if (!isset($_POST["template"]) || empty($_POST["template"])) {
  $template = null;
} else {
  $template = trim($_POST["template"]);
}
if (!isset($_POST["page_order"]) || empty($_POST["page_order"])) {
  $page_order = null;
} else {
  $page_order = filter_var(trim($_POST["page_order"]), FILTER_SANITIZE_NUMBER_INT);
}
if (isset($_POST["enabled"]) && $_POST["enabled"] == "on") {
  $enabled = true;
} else {
  $enabled = false;
}

$validate_name = validatePageTitle($name);
$validate_url = validateUrl($url);

if($validate_name !== "valid") {
  echo json_encode(array("success" => 0, "error" => $validate_name));
  exit();
}
if($validate_url !== "valid") {
  echo json_encode(array("success" => 0, "error" => $validate_url));
  exit();
}

$edit_page_query = $pdo->prepare("
  UPDATE ".$prefix."_pages SET 
    name=:name,
    url=:url,
    content=:content,
    template=:template,
    enabled=:enabled,
    page_order=:page_order
  WHERE id=:page_id
");
$edit_page_query->bindParam(":page_id", $page_id);
$edit_page_query->bindParam(":name", $name);
$edit_page_query->bindParam(":url", $url);
$edit_page_query->bindParam(":content", $content);
$edit_page_query->bindParam(":template", $template);
$edit_page_query->bindParam(":enabled", $enabled, PDO::PARAM_BOOL);
$edit_page_query->bindParam(":page_order", $page_order, PDO::PARAM_INT);

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
  $edit_page_query->execute();
  if ($edit_page_query->rowCount() == 1) {
    generateSitemapXml();
    echo json_encode(array("success" => 1));
  } else {
    echo json_encode(array("success" => 0, "error" => "Вы ничего не изменили"));
  }
} catch(PDOException $error) {
  if (preg_match("/Duplicate entry .+ for key '(.+)'/", $error->getMessage(), $matches)) {
    $arr_column_names = array(
      "name" => "Такое название страницы уже существует",
      "url" => "Такая ссылка уже существует",
      "page_order" => "Такой порядок страницы уже занят"
    );
    if (!array_key_exists($matches[1], $arr_column_names)) {
      $column_name = $matches[1];
    } else {
      $column_name = $arr_column_names[$matches[1]];
    }
    echo json_encode(array("success" => 0, "error" => $column_name));
  } else {
    echo json_encode(array("success" => 0, "error" => $error->getMessage()));
  }
}

?>