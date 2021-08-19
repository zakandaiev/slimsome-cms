<?php

if(!is_file("db_connect.php")) {
  header("Location: /404");
  exit();
} else {
  require_once("db_connect.php");
  require_once("db_settings.php");
  require_once("core.php");
}

if (empty($GLOBALS["ftp_host"]) || empty($GLOBALS["ftp_login"]) || empty($GLOBALS["ftp_pass"]) || empty($GLOBALS["ftp_bans_path"]) || empty($GLOBALS["cron_pass"]) || empty($_GET["cp"])) {
  header("Location: /404");
  exit();
}

if ($_GET["cp"] !== $GLOBALS["cron_pass"]) {
  header("Location: /404");
  exit();
}

try {
  // FTP GET BANS FILE
  $ftp_host = $GLOBALS["ftp_host"];
  $ftp_login = $GLOBALS["ftp_login"];
  $ftp_pass = $GLOBALS["ftp_pass"];

  $ftp_connection = ftp_connect($ftp_host) or die("Could not connect to $ftp_host");
  $login = ftp_login($ftp_connection, $ftp_login, $ftp_pass);
  ftp_pasv($ftp_connection, true);

  $local_file = sys_get_temp_dir()."/fresh_bans.sq3";
  $server_file = $GLOBALS["ftp_bans_path"];

  ftp_get($ftp_connection, $local_file, $server_file, FTP_BINARY);
  ftp_close($ftp_connection);

  // READ BANS FILE AND INSERT TO DB
  $fb = new PDO("sqlite:$local_file"); 
  $st = $fb->query("SELECT * FROM amx_bans");
  $bans = $st->fetchAll();

  $insert_bans_query = $pdo->prepare("
    INSERT IGNORE INTO ".$prefix."_bans
      (id, nick, ip, steam_id, reason, created, length, admin_nick, unbanned)
    VALUES
      (:bid, :nick, :ip, :steam_id, :reason, :created, :length, :admin_nick, :unbanned)
  ");
  $insert_bans_query->bindParam(':bid', $bid);
  $insert_bans_query->bindParam(':nick', $nick);
  $insert_bans_query->bindParam(':ip', $ip);
  $insert_bans_query->bindParam(':steam_id', $steam_id);
  $insert_bans_query->bindParam(':reason', $reason);
  $insert_bans_query->bindParam(':created', $created);
  $insert_bans_query->bindParam(':length', $length);
  $insert_bans_query->bindParam(':admin_nick', $admin_nick);
  $insert_bans_query->bindParam(':unbanned', $unbanned);

  $update_bans_query = $pdo->prepare("
    UPDATE ".$prefix."_bans SET
      unbanned=:unbanned
    WHERE id=:bid
  ");
  $update_bans_query->bindParam(':unbanned', $unbanned);
  $update_bans_query->bindParam(':bid', $bid);

  foreach($bans as $ban){
    $bid = filter_var(trim($ban["bid"]), FILTER_SANITIZE_NUMBER_INT);
    $nick = filter_var(trim($ban["player_nick"]), FILTER_SANITIZE_STRING);
    if(!empty($nick)) {
      $nick = $nick;
    } else {
      $nick = "Читер";
    }
    $ip = filter_var(trim($ban["player_ip"]), FILTER_SANITIZE_STRING);
    $steam_id = filter_var(trim($ban["player_id"]), FILTER_SANITIZE_STRING);
    $reason = filter_var(trim($ban["ban_reason"]), FILTER_SANITIZE_STRING);
    $created = filter_var(trim($ban["ban_created"]), FILTER_SANITIZE_NUMBER_INT);
    $length = filter_var(trim($ban["ban_length"]), FILTER_SANITIZE_NUMBER_INT);
    $admin_nick = filter_var(trim($ban["admin_nick"]), FILTER_SANITIZE_STRING);
    if (isset($length) && $length == -1) {
      $unbanned = true;
      $update_bans_query->execute();
    } else {
      $unbanned = false;
      $insert_bans_query->execute();
    }
  }
}
catch (PDOException $e) {
  print 'Error : ' . $e->getMessage();
}

?>