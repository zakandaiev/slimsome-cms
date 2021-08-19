<?php

$db_settings_query = $pdo->query("SELECT name, value, json FROM ".$prefix."_settings");
while ($rows = $db_settings_query->fetch(PDO::FETCH_LAZY)) {
  if (isset($rows->value)) {
    $GLOBALS[$rows->name] = $rows->value;
  } else {
    $GLOBALS[$rows->name] = $rows->json;
  }
}

@date_default_timezone_set($GLOBALS["t_zone"]);

?>