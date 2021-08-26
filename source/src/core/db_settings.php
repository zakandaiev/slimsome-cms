<?php

$db_settings_query = $pdo->query("SELECT * FROM ".$prefix."_settings");

while ($rows = $db_settings_query->fetch(PDO::FETCH_LAZY)) {
  $GLOBALS[$rows->name] = $rows->value;
}

@date_default_timezone_set($GLOBALS["t_zone"]);

?>