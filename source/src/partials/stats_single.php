<? if ($player_arr == "not_found"): ?>
  <section class="block">
    <h2>Статистика игроков</h2>
    <div class="well appear-bottom">
      <p>Такого игрока не найдено.</p>
      <a href="stats" class="btn btn_primary">Вернуться назад</a>
    </div>
  </section>
<? else: ?>
  <?php
    $player_nick = htmlspecialchars(trim($player_arr["nick"]));
    $player_position = getPlayerPosition($player_arr["nick"]);
    if(!empty(getPlayerAvatar($player_arr["nick"]))) {
      $player_avatar = getPlayerAvatar($player_arr["nick"]);
    } else {
      $player_avatar = "img/no_avatar.jpg";
    }
  ?>
  <div class="stats-content">
    <aside class="stats-content__left">
      <section class="block">
        <div class="widget-player">
          <div class="widget-player__info">
            <img class="widget-player__avatar" src="<?= $player_avatar ?>" title="<?= $player_position ?>" alt="<?= $player_position ?>">
            <div class="widget-player__rec">
              <h3 class="widget-player__nick"><?= $player_nick ?></h3>
              <p class="widget-player__status"><?= $player_position ?></p>
            </div>
          </div>
          <div class="widget-player__bottom">
            <?php
              // formulas
              $kill_death_ratio = round($player_arr["kills"] / ($player_arr["deaths"] == 0 ? 1 : $player_arr["deaths"]), 2);
              $headshot_ratio = round(100 * $player_arr["headshots"] / ($player_arr["kills"] == 0 ? 1 : $player_arr["kills"]), 2);
              $accuracy_ratio = round(100 * $player_arr["hits"] / ($player_arr["shots"] == 0 ? 1 : $player_arr["shots"]), 2);
            ?>
            <table>
              <tbody>
                <tr>
                  <td>Позиция в топе:</td>
                  <td><?= $player_arr["rank"] ?></td>
                </tr>
                <tr>
                  <td>Steam ID:</td>
                  <td><?= $player_arr["uniq"] ?></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                </tr>
                <tr>
                  <td>Убил:</td>
                  <td><?= $player_arr["kills"] ?></td>
                </tr>
                <tr>
                  <td>Убил в голову:</td>
                  <td><?= $player_arr["headshots"] ?></td>
                </tr>
                <tr>
                  <td>Умер:</td>
                  <td><?= $player_arr["deaths"] ?></td>
                </tr>
                <tr>
                  <td>K/D:</td>
                  <td><?= $kill_death_ratio ?></td>
                </tr>
                <tr>
                  <td>Скилл:</td>
                  <td><?= printSkillLabel($player_arr["kills"], $player_arr["deaths"]) ?></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                </tr>
                <tr>
                  <td>Сделал выстрелов:</td>
                  <td><?= $player_arr["shots"] ?></td>
                </tr>
                <tr>
                  <td>Нанес урона:</td>
                  <td><?= $player_arr["damage"] ?></td>
                </tr>
                <tr>
                  <td>Убил своих:</td>
                  <td><?= $player_arr["teamkill"] ?></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>
    </aside>
    <main class="stats-content__right">
      <section class="block">
        <h2 class="block__title">Статистика игрока <?= $player_nick ?></h2>
        <div class="stats-detail">
          <div class="stats-detail__left">
            <div class="stats-leaf">
              <div class="stats-leaf__item">
                <span class="stats-leaf__value"><?= $player_arr["rank"] ?></span>
                <span class="stats-leaf__description">Позиция в топе</span>
                <div class="stats-leaf__icon">
                  <?php
                    if(isNumInRange($player_arr["rank"], 1, 4)) {
                      switch ($player_arr["rank"]) {
                        case 1: {
                          echo getSvg("img/stats/gold.svg");
                          break;
                        }
                        case 2: {
                          echo getSvg("img/stats/silver.svg");
                          break;
                        }
                        case 3: {
                          echo getSvg("img/stats/bronze.svg");
                          break;
                        }
                        default: {
                          echo getSvg("img/icons/bar-chart.svg");
                          break;
                        }
                      }
                    } else {
                      echo getSvg("img/icons/bar-chart.svg");
                    }
                  ?>
                </div>
              </div>
              <div class="stats-leaf__item">
                <span class="stats-leaf__value"><?= $kill_death_ratio ?></span>
                <span class="stats-leaf__description">Убил/умер</span>
                <div class="stats-leaf__icon"><?= getSvg("img/stats/divide.svg") ?></div>
              </div>
              <div class="stats-leaf__item">
                <span class="stats-leaf__value"><?= $accuracy_ratio ?>%</span>
                <span class="stats-leaf__description">Точность</span>
                <div class="stats-leaf__icon"><?= getSvg("img/stats/aim.svg") ?></div>
              </div>
              <div class="stats-leaf__item">
                <span class="stats-leaf__value"><?= $headshot_ratio ?>%</span>
                <span class="stats-leaf__description">Убийств в голову</span>
                <div class="stats-leaf__icon"><?= getSvg("img/stats/headshot.svg") ?></div>
              </div>
              <div class="stats-leaf__item">
                <span class="stats-leaf__value"><?= $player_arr["explosions"] ?></span>
                <span class="stats-leaf__description">Взорвал бомб</span>
                <div class="stats-leaf__icon"><?= getSvg("img/stats/c4.svg") ?></div>
              </div>
              <div class="stats-leaf__item">
                <span class="stats-leaf__value"><?= $player_arr["defused"] ?></span>
                <span class="stats-leaf__description">Разминировал бомб</span>
                <div class="stats-leaf__icon"><?= getSvg("img/stats/defusekit.svg") ?></div>
              </div>
            </div>
          </div>
          <div class="stats-detail__right">
            <?php
              $hitbox_sum = $player_arr["head"] + $player_arr["chest"] + $player_arr["stomach"] + $player_arr["leftarm"] + $player_arr["rightarm"] + $player_arr["leftleg"] + $player_arr["rightleg"];
              if($hitbox_sum == 0) {
                $hitbox_sum = 1;
              }
            ?>
            <div class="hitbox">
              <div class="hitbox__item head"><span class="hitbox__tooltip">Попаданий в голову: <?= $player_arr["head"] ?> (<?= round(100 * $player_arr["head"] / $hitbox_sum, 2) ?>%)</span></div>
              <div class="hitbox__item chest"><span class="hitbox__tooltip">Попаданий в грудь: <?= $player_arr["chest"] ?> (<?= round(100 * $player_arr["chest"] / $hitbox_sum, 2) ?>%)</span></div>
              <div class="hitbox__item stomach"><span class="hitbox__tooltip">Попаданий в живот: <?= $player_arr["stomach"] ?> (<?= round(100 * $player_arr["stomach"] / $hitbox_sum, 2) ?>%)</span></div>
              <div class="hitbox__item larm"><span class="hitbox__tooltip">Попаданий в левую руку: <?= $player_arr["leftarm"] ?> (<?= round(100 * $player_arr["leftarm"] / $hitbox_sum, 2) ?>%)</span></div>
              <div class="hitbox__item rarm"><span class="hitbox__tooltip">Попаданий в правую руку: <?= $player_arr["rightarm"] ?> (<?= round(100 * $player_arr["rightarm"] / $hitbox_sum, 2) ?>%)</span></div>
              <div class="hitbox__item lleg"><span class="hitbox__tooltip">Попаданий в левую ногу: <?= $player_arr["leftleg"] ?> (<?= round(100 * $player_arr["leftleg"] / $hitbox_sum, 2) ?>%)</span></div>
              <div class="hitbox__item rleg"><span class="hitbox__tooltip">Попаданий в правую ногу: <?= $player_arr["rightleg"] ?> (<?= round(100 * $player_arr["rightleg"] / $hitbox_sum, 2) ?>%)</span></div>
              <img class="hitbox__image" src="img/stats/player_model.png" alt="hitbox">
            </div>
          </div>
        </div>
      </section>
    </main>
  </div>
<? endif; ?>