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

if (!isset($_POST["service_id"])) {
  echo json_encode(array("success" => 0, "error" => "Неверный параметр привилегии"));
  exit();
}

$service_id = $_POST["service_id"];

$name = filter_var(trim($_POST["name"]), FILTER_SANITIZE_STRING);
$flags = filter_var(trim($_POST["flags"]), FILTER_SANITIZE_STRING);
$days_number = filter_var(trim($_POST["days_number"]), FILTER_SANITIZE_STRING);
$days_rub = filter_var(trim($_POST["days_rub"]), FILTER_SANITIZE_STRING);
$days_uah = filter_var(trim($_POST["days_uah"]), FILTER_SANITIZE_STRING);
$description = trim($_POST["description"]);
if (empty($_POST["images_str"]) || $_POST["images_str"] == "[]") {
  $images = null;
} else {
  $images = trim($_POST["images_str"]);
}
if (empty($_POST["user_avatar_str"])) {
  $user_avatar = null;
} else {
  $user_avatar = trim($_POST["user_avatar_str"]);
}
if (isset($_POST["enabled"]) && $_POST["enabled"] == "on") {
  $enabled = true;
} else {
  $enabled = false;
}
if (isset($_POST["buyable"]) && $_POST["buyable"] == "on") {
  $buyable = true;
} else {
  $buyable = false;
}

$validate_name = validateServiceTitle($name);
$validate_flags = validateServiceFlags($flags);
$validate_days = validateServiceDays($days_number);
$validate_days_rub = validateServiceDays($days_rub, "days_rub");
$validate_days_uah = validateServiceDays($days_uah, "days_uah");

if($validate_name !== "valid") {
  echo json_encode(array("success" => 0, "error" => $validate_name));
  exit();
}
if($validate_flags !== "valid") {
  echo json_encode(array("success" => 0, "error" => $validate_flags));
  exit();
}
if($validate_days !== "valid") {
  echo json_encode(array("success" => 0, "error" => $validate_days));
  exit();
}
if($validate_days_rub !== "valid") {
  echo json_encode(array("success" => 0, "error" => $validate_days_rub));
  exit();
}
if($validate_days_uah !== "valid") {
  echo json_encode(array("success" => 0, "error" => $validate_days_uah));
  exit();
}
if (empty($description)) {
  echo json_encode(array("success" => 0, "error" => "Заполните описание"));
  exit();
}

$arr_days = explode(",", $days_number);
$arr_days_rub = explode(",", $days_rub);
$arr_days_uah = explode(",", $days_uah);

$arr_days_count = count($arr_days);
$arr_days_rub_count = count($arr_days_rub);
$arr_days_uah_count = count($arr_days_uah);

if ($arr_days_count !== $arr_days_rub_count || $arr_days_count !== $arr_days_uah_count) {
  echo json_encode(array("success" => 0, "error" => "Количество дней и цен должно совпадать"));
  exit();
}

for($i = 0; $i < $arr_days_count; $i++) {
  if ($arr_days[$i] < 0) {
    echo json_encode(array("success" => 0, "error" => "Количество дней должно быть больше нуля"));
    exit();
  }
  if ($arr_days_rub[$i] < 1 || $arr_days_uah[$i] < 1) {
    echo json_encode(array("success" => 0, "error" => "Цена должна быть больше нуля"));
    exit();
  }
  $days[] = array("days" => $arr_days[$i], "price_rub" => $arr_days_rub[$i], "price_uah" => $arr_days_uah[$i]);
}
$days = json_encode($days, JSON_FORCE_OBJECT);

$services_update_query = $pdo->prepare("
  UPDATE ".$prefix."_services SET 
    name=:name,
    flags=:flags,
    days=:days,
    images=:images,
    user_avatar=:user_avatar,
    description=:description,
    buyable=:buyable,
    enabled=:enabled
  WHERE id=:service_id
");

$services_update_query->bindParam(":service_id", $service_id);
$services_update_query->bindParam(":name", $name);
$services_update_query->bindParam(":flags", $flags);
$services_update_query->bindParam(":days", $days);
$services_update_query->bindParam(":images", $images);
$services_update_query->bindParam(":user_avatar", $user_avatar);
$services_update_query->bindParam(":description", $description);
$services_update_query->bindParam(":buyable", $buyable, PDO::PARAM_BOOL);
$services_update_query->bindParam(":enabled", $enabled, PDO::PARAM_BOOL);

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
  $services_update_query->execute();
  if ($services_update_query->rowCount() == 1) {
    echo json_encode(array("success" => 1));
  } else {
    echo json_encode(array("success" => 0, "error" => "Вы ничего не изменили"));
  }
} catch(PDOException $error) { 
  if (preg_match("/Duplicate entry .+ for key '(.+)'/", $error->getMessage(), $matches)) {
    $arr_column_names = array(
      "name" => "привилегия"
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