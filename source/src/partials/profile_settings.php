<section class="block">
  <h2 class="block__title">Настройки системы</h2>
  <div class="well appear-bottom">
    <form id="edit_settings" method="post" class="form">
      <h3>Настройки Базы Данных</h3>
      <label>Часовой пояс</label>
      <select name="t_zone" required>
        <?php
          if ($GLOBALS["t_zone"] == "Europe/Moscow") {
            echo '
              <option value="Europe/Moscow" selected="selected">Москва</option>
              <option value="Europe/Kiev">Киев</option>
            ';
          } else {
            echo '
              <option value="Europe/Moscow">Москва</option>
              <option value="Europe/Kiev" selected="selected">Киев</option>
            ';
          }
        ?>
      </select>
      <label>Пароль CRON</label>
      <input type="text" name="cron_pass" value="<?= $GLOBALS["cron_pass"] ?>" placeholder="Пароль CRON" required>
      
      <h3>Настройки игрового Сервера</h3>
      <label>IP:PORT</label>
      <input type="text" name="server_ip" value="<?= $GLOBALS["server_ip"] ?>" placeholder="xx.xx.xx.xx:27015" pattern="\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}:\d{5}" required>
      <label>Хост FTP</label>
      <input type="text" name="ftp_host" value="<?= $GLOBALS["ftp_host"] ?>" placeholder="Хост FTP" required>
      <label>Логин FTP</label>
      <input type="text" name="ftp_login" value="<?= $GLOBALS["ftp_login"] ?>" placeholder="Логин FTP" required>
      <label>Пароль FTP</label>
      <input type="text" name="ftp_pass" value="<?= $GLOBALS["ftp_pass"] ?>" placeholder="Пароль FTP" required>
      <label>Путь к users.ini</label>
      <input type="text" name="ftp_users_path" placeholder="addons/amxmodx/configs/users.ini" value="<?= $GLOBALS["ftp_users_path"] ?>" required>
      <label>Путь к fresh_bans.sq3</label>
      <input type="text" name="ftp_bans_path" placeholder="addons/amxmodx/data/sqlite3/fresh_bans.sq3" value="<?= $GLOBALS["ftp_bans_path"] ?>" required>
      <label>Путь к csstats.dat</label>
      <input type="text" name="ftp_stats_path" placeholder="addons/amxmodx/data/csstats.dat" value="<?= $GLOBALS["ftp_stats_path"] ?>" required>
      <button type="submit" class="btn">Обновить</button>
    </form>
  </div>
</section>