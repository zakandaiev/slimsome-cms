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

if(empty($_POST) || empty($_POST["upload_type"])) {
  header("Location: /404");
  exit();
}

if($_POST["upload_type"] == "site") {
  if (!file_exists("../img/uploads")) {
    mkdir("../img/uploads", 0755, true);
  }
  foreach ($_FILES["images"]["tmp_name"] as $key => $tmp_name) {
    $path_to = "img/uploads/" . $_FILES["images"]["name"][$key];
    $images_arr[] = $path_to;
    move_uploaded_file($_FILES["images"]["tmp_name"][$key], "../".$path_to);
  }
}

if($_POST["upload_type"] == "services") {
  if (!file_exists("../img/uploads")) {
    mkdir("../img/uploads", 0755, true);
  }
  if (!file_exists("../img/uploads/services")) {
    mkdir("../img/uploads/services", 0755, true);
  }
  foreach ($_FILES["images"]["tmp_name"] as $key => $tmp_name) {
    $path_to = "img/uploads/services/" . $_FILES["images"]["name"][$key];
    $images_arr[] = $path_to;
    move_uploaded_file($_FILES["images"]["tmp_name"][$key], "../".$path_to);
  }
}

if($_POST["upload_type"] == "news") {
  if (!file_exists("../img/uploads")) {
    mkdir("../img/uploads", 0755, true);
  }
  if (!file_exists("../img/uploads/news")) {
    mkdir("../img/uploads/news", 0755, true);
  }
  foreach ($_FILES["images"]["tmp_name"] as $key => $tmp_name) {
    $path_to = "img/uploads/news/" . $_FILES["images"]["name"][$key];
    $images_arr[] = $path_to;
    move_uploaded_file($_FILES["images"]["tmp_name"][$key], "../".$path_to);
  }
}

if(isset($images_arr)) {
  echo json_encode(array("success" => 1, "images" => $images_arr));
}

?>