<?php

if(!is_file("db_connect.php")) {
  header("Location: /404");
  exit();
} else {
  require_once("db_connect.php");
  require_once("db_settings.php");
  require_once("core.php");
  require_once("csstats.class.php");
}

if (empty($GLOBALS["ftp_host"]) || empty($GLOBALS["ftp_login"]) || empty($GLOBALS["ftp_pass"]) || empty($GLOBALS["ftp_stats_path"]) || empty($GLOBALS["cron_pass"]) || empty($_GET["cp"])) {
  header("Location: /404");
  exit();
}

if ($_GET["cp"] !== $GLOBALS["cron_pass"]) {
  header("Location: /404");
  exit();
}

try {
  // FTP GET STATS FILE
  $ftp_host = $GLOBALS["ftp_host"];
  $ftp_login = $GLOBALS["ftp_login"];
  $ftp_pass = $GLOBALS["ftp_pass"];

  $ftp_connection = ftp_connect($ftp_host) or die("Could not connect to $ftp_host");
  $login = ftp_login($ftp_connection, $ftp_login, $ftp_pass);
  ftp_pasv($ftp_connection, true);

  $local_file = sys_get_temp_dir()."/csstats.dat";
  $server_file = $GLOBALS["ftp_stats_path"];

  ftp_get($ftp_connection, $local_file, $server_file, FTP_BINARY);
  ftp_close($ftp_connection);

  // READ STATS FILE AND INSERT TO DB
  $stats = new CSstats($local_file);

  $stats_count = $stats->countPlayers();

  if(empty($stats->getPlayer(1))) {
    exit();
  }

  $fields = array_keys($stats->getPlayer(1));

  $fields = array_combine($fields, $fields);

  // очень медленный запрос + проблемы со вставкой в колонку rank - надо его оборачивать в `rank`
  /*if ($stats->export($pdo, array(
    'table' => $prefix."_stats",
    'fields' => $fields,
    'key' => 'rank'
  ))) {
    echo "Export to DB success";
  } else {
    echo "Export to DB failed";
  }*/

  // лучше уж так
  $fields_columns = "(`" . implode("`,`", $fields) . "`)";

  $insert_stats_query = "INSERT INTO `".$prefix."_stats` {$fields_columns} VALUES" . PHP_EOL;

  for ($i = 1; $i <= $stats_count; $i++) {
    $player = $stats->getPlayer($i);
    $player = array_map(function($n) {return addslashes(trim($n));}, $player);
    $insert_stats_query .= "('" . implode("','", $player) . "')";
    if($i == $stats_count) {
      $insert_stats_query .= ";";
    } else {
      $insert_stats_query .= "," . PHP_EOL;
    }
  }
  
  $pdo->query("TRUNCATE TABLE ".$prefix."_stats");
  $pdo->query($insert_stats_query);
}
catch (PDOException $e) {
  print 'Error : ' . $e->getMessage();
}

?>