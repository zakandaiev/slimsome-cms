<? require_once("partials/header.php"); ?>

<?php

$page_key = NULL;

foreach ($db_pages as $key => $rows) {
  if(array_search(key($_GET), $rows, true)) {
    $page_key = $key;
    break;
  }
}

if (key($_GET) === NULL || $page_key === NULL) {
  echo "<h2>Такой страницы не существует.</h2>";
} else {
  $row = $db_pages[$page_key];
  if (!$row["enabled"]) {
    echo "<h2>Страница ".$row["name"]." отключена.</h2>";
  } else {
    if (isset($row["template"]) && !empty($row["template"])) {
      require_once("pages/".$row["template"].".php");
    } else if (isset($row["content"]) && !empty($row["content"])) {
      echo $row["content"];
    } else {
      echo "<h2>Страница ".$row["name"]." не заполнена.</h2>";
    }
  }
}

?>

<? require_once("partials/footer.php"); ?>