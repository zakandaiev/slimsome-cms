<?php

require_once(dirname(__FILE__)."/../core/db_connect.php");
require_once(dirname(__FILE__)."/../core/db_settings.php");
require_once(dirname(__FILE__)."/../core/core.php");
require_once(dirname(__FILE__)."/../SourceQuery/bootstrap.php");

use xPaw\SourceQuery\SourceQuery;

$server_ip = $GLOBALS["server_ip"];
list($ip, $port) = explode(":", $server_ip);

if(isset($GLOBALS["server_online_info"]) && !empty($GLOBALS["server_online_info"])) {
  $server_data = json_decode($GLOBALS["server_online_info"], true);
} else {
  $server_data = null;
}

if(empty($server_data) || time() - $server_data["last_check"] > 60 * 1) {
  $source_query = new SourceQuery();

  try {
    $source_query->Connect($ip, $port, 5, SourceQuery::GOLDSOURCE);
    $server_data = $source_query->GetInfo();
  }
  catch(Exception $e) {
    echo '<div class="widget-online"> <div class="widget-online__top"> <img src="img/no_image.jpg" alt="Текущая карта" class="widget-online__img"> <div class="widget-online__info"> <p><b>Карта:</b> <span style="display: inline-block;width: 92px;background: #e1e1e1;height: 1rem;margin-left: 5px;"></span></p> <p><b>Игроков:</b> <span style="display: inline-block;width: 76px;background: #e1e1e1;height: 1rem;margin-left: 5px;"></span></p> <p><span class="widget-online__indicator offline"></span><b>'.$server_ip.'</b></p> <a href="steam://connect/91.211.118.55:27015" class="btn btn_primary">Подключиться</a> </div> </div> <div class="widget-online__players"> <p><b>Не удалось загрузить информацию о сервере.</b></p> </div> </div>';
    return;
  }
  finally {
    $source_query->Disconnect();
  }

  try {
    $source_query->Connect($ip, $port, 5, SourceQuery::GOLDSOURCE);
    $server_data["online_players"] = $source_query->GetPlayers();
  }
  catch(Exception $e) {
    echo '<div class="widget-online"> <div class="widget-online__top"> <img src="img/no_image.jpg" alt="Текущая карта" class="widget-online__img"> <div class="widget-online__info"> <p><b>Карта:</b> <span style="display: inline-block;width: 92px;background: #e1e1e1;height: 1rem;margin-left: 5px;"></span></p> <p><b>Игроков:</b> <span style="display: inline-block;width: 76px;background: #e1e1e1;height: 1rem;margin-left: 5px;"></span></p> <p><span class="widget-online__indicator offline"></span><b>'.$server_ip.'</b></p> <a href="steam://connect/91.211.118.55:27015" class="btn btn_primary">Подключиться</a> </div> </div> <div class="widget-online__players"> <p><b>Не удалось загрузить информацию о игроках.</b></p> </div> </div>';
    return;
  }
  finally {
    $source_query->Disconnect();
  }

  if (!empty($server_data)) {
    $server_data["last_check"] = time();
    $server_online_info_query = $pdo->prepare("UPDATE ".$prefix."_settings SET value=:value WHERE name='server_online_info'");
    $server_online_info_query->bindParam(":value", json_encode($server_data, JSON_FORCE_OBJECT));
    $server_online_info_query->execute();
  } else {
    echo '<div class="widget-online"> <div class="widget-online__top"> <img src="img/no_image.jpg" alt="Текущая карта" class="widget-online__img"> <div class="widget-online__info"> <p><b>Карта:</b> <span style="display: inline-block;width: 92px;background: #e1e1e1;height: 1rem;margin-left: 5px;"></span></p> <p><b>Игроков:</b> <span style="display: inline-block;width: 76px;background: #e1e1e1;height: 1rem;margin-left: 5px;"></span></p> <p><span class="widget-online__indicator offline"></span><b>'.$server_ip.'</b></p> <a href="steam://connect/91.211.118.55:27015" class="btn btn_primary">Подключиться</a> </div> </div> <div class="widget-online__players"> <p><b>Не удалось загрузить информацию о сервере.</b></p> </div> </div>';
    return;
  }
}

if (!empty($server_data)) {
  if (file_exists(dirname(__FILE__)."/../img/maps/".$server_data["Map"].".jpg")) {
    $map_img = 'img/maps/'.$server_data["Map"].'.jpg';
  } else {
    $map_img = 'img/no_image.jpg';
  }
  $server_status = 'online';
} else {
  $map_img = 'img/no_image.jpg';
  $server_status = 'offline';
}

?>

<div class="widget-online">
  <div class="widget-online__top">
    <img src="<?= $map_img ?>" alt="Текущая карта" class="widget-online__img" data-zoomable>
    <div class="widget-online__info">
      <p><b>Карта:</b> <?= $server_data["Map"] ?></p>
      <p><b>Игроков:</b> <?= $server_data["Players"] ?>/<?= $server_data["MaxPlayers"] ?></p>
      <p><span class="widget-online__indicator <?= $server_status ?>"></span><b><?= $server_ip ?></b></p>
      <a href="steam://connect/<?= $server_ip ?>" class="btn btn_primary">Подключиться</a>
    </div>
  </div>
  <div class="widget-online__players">
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>#</th>
            <th>Ник</th>
            <th>Счёт</th>
            <th>Время</th>
          </tr>
        </thead>
        <tbody>
          <?php
            if (!empty($server_data["online_players"])) {
              foreach ($server_data["online_players"] AS $key => $player_kills) {
                $player_score[$key]  = $player_kills['Frags'];
              }
              array_multisort($player_score, SORT_DESC, $server_data["online_players"]);
              $pnum = 0;
              foreach ($server_data["online_players"] as $player) {
                $player_nick = htmlspecialchars(trim($player["Name"]));
                if(!empty(getPlayerAvatar($player["Name"]))) {
                  $player_avatar = '<img src="'.getPlayerAvatar($player["Name"]).'" alt="'.getPlayerPosition($player["Name"]).'" title="'.getPlayerPosition($player["Name"]).'" class="avatar">';
                } else {
                  $player_avatar = "";
                }
                echo '
                  <tr>
                    <td>'.++$pnum.'</td>
                    <td>'.$player_nick.$player_avatar.'</td>
                    <td>'.$player["Frags"].'</td>
                    <td>'.$player["TimeF"].'</td>
                  </tr>
                ';
              }
            } else {
              echo '<tr><td>0</td><td colspan="3">Онлайн игроков нет.</td></tr>';
            }
          ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
  mediumZoom("[data-zoomable]", {
    margin: 14,
    background: '#000',
    scrollOffset: 0
  });
</script>
