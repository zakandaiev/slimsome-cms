<? require_once("partials/header.php"); ?>

<?php

$news_key = NULL;

foreach ($db_news as $key => $rows) {
  if(array_search(key($_GET), $rows, true)) {
    $news_key = $key;
    break;
  }
}

if (key($_GET) === NULL || $news_key === NULL) {
  require_once("partials/news_all.php");
} else {
  $current_post = $db_news[$news_key];
  if (!$current_post["enabled"]) {
    echo "<h2>Новость ".$current_post["title"]." отключена.</h2>";
  } else {
    require_once("partials/news_single.php");
  }
}

?>

<? require_once("partials/footer.php"); ?>