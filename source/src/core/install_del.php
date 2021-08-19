<?php

if(!isset($_POST["sure"])) {
  header("Location: /404");
  exit();
}

if(is_file("install.php") && is_file("install.sql") && is_file("install_del.php")) {
  unlink("install.php");
  unlink("install.sql");
  echo json_encode(array('success' => 1));
  unlink("install_del.php");
} else {
  echo json_encode(array('success' => 0));
}

?>
