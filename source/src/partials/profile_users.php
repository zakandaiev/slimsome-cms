<?php

// PAGINATION
$paginator_page_limit = 30;

$paginator_total_rows = $pdo->query("SELECT COUNT(id) as count FROM ".$prefix."_users")->fetch(PDO::FETCH_LAZY)->count;

$paginator_current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;

$paginator_calc_page = ($paginator_current_page - 1) * $paginator_page_limit;

$db_users_query = $pdo->prepare("
  SELECT *, (SELECT name FROM ".$prefix."_services WHERE id=t_users.service_id) as service_name FROM ".$prefix."_users t_users ORDER BY id ASC
  LIMIT ".$paginator_calc_page.",".$paginator_page_limit."
");

$db_users_query->execute();

$db_users = $db_users_query->fetchAll(PDO::FETCH_ASSOC);
  
?>

<?php if(isset($_GET["add"])): ?>
  <section class="block">
    <h2 class="block__title">Создание нового пользователя</h2>
    <div class="well appear-bottom">
      <form id="add_user" method="post" class="form">
        <label>Логин на сайте</label>
        <input type="text" name="login" placeholder="Логин" required>
        <label>
          Пароль
          <br><small>Пароль будет использоваться для привязки привилегии в игре и для логина на сайте</small>
        </label>
        <input type="text" name="password"  placeholder="Пароль" required>
        <label>E-mail</label>
        <input type="email" name="email" placeholder="Почта e-mail">
        <label>Ник</label>
        <input type="text" name="nick" placeholder="Ник на сервере">
        <label>Steam ID</label>
        <input type="text" name="steam_id"placeholder="Steam ID">
        <label>Имя</label>
        <input type="text" name="name" placeholder="Настоящее имя">
        <label>Привилегия</label>
        <select name="service_id">
          <option value="0">Без привилегии</option>
          <?php
            $db_services = require_once("core/db_services.php");
            if (!empty($db_services)) {
              foreach ($db_services as $rows) {
                if ($rows["enabled"]) {
                  echo '
                   <option value="'.$rows["id"].'">'.$rows["name"].'</option>
                  ';
                }
              }
            }
          ?>
        </select>
        <label>Дата покупки</label>
        <input type="datetime-local" name="service_start">
        <label>Дата окончания</label>
        <input type="datetime-local" name="service_end">
        <label>Привилегия навсегда</label>
        <label class="switcher">
          <input type="checkbox" name="service_nolimit">
          <span class="slider"></span>
        </label>
        <label>Тип привязки</label>
        <select name="service_bind_type">
          <option value="1">Ник + пароль</option>
          <option value="2">Steam ID + пароль</option>
        </select>
        <label>Администратор сайта</label>
        <label class="switcher">
          <input type="checkbox" name="isadmin">
          <span class="slider"></span>
        </label>
        <label>Модератор сайта</label>
        <label class="switcher">
          <input type="checkbox" name="ismoder">
          <span class="slider"></span>
        </label>
        <button type="submit" class="btn">Создать</button>
      </form>
    </div>
  </section>
<? elseif(isset($_GET["edit"])): ?>
  <section class="block">
    <?php
      $editing_id = $_GET["edit"];
      $editing_arr = "not_found";

      $editing_query = $pdo->prepare("SELECT * FROM ".$prefix."_users WHERE id=:id");
      $editing_query->bindParam(":id", $editing_id);
      $editing_query->execute();

      $editing_query = $editing_query->fetch(PDO::FETCH_ASSOC);

      if(!empty($editing_query)) {
        $editing_arr = $editing_query;
      }
    ?>
    <? if ($editing_arr == "not_found"): ?>
      <h2 class="block__title">Такого пользователя не найдено!</h2>
    <? else: ?>
      <h2 class="block__title">Редактирование пользователя с ID #<?= $editing_arr["id"] ?></h2>
      <div class="well appear-bottom">
        <form id="edit_user" method="post" class="form">
          <label>Логин на сайте</label>
          <input type="text" name="login" placeholder="Логин" value="<?= $editing_arr["login"] ?>" required>
          <label>
            Пароль
            <br><small>Пароль будет использоваться для привязки привилегии в игре и для логина на сайте</small>
          </label>
          <input type="password" name="password"  placeholder="Пароль" value="<?= $editing_arr["password"] ?>" required>
          <label>E-mail</label>
          <input type="email" name="email" placeholder="Почта e-mail" value="<?= $editing_arr["email"] ?>">
          <label>Ник</label>
          <input type="text" name="nick" placeholder="Ник на сервере" value="<?= $editing_arr["nick"] ?>">
          <label>Steam ID</label>
          <input type="text" name="steam_id"placeholder="Steam ID" value="<?= $editing_arr["steam_id"] ?>">
          <label>Имя</label>
          <input type="text" name="name" placeholder="Настоящее имя" value="<?= $editing_arr["name"] ?>">
          <label>Привилегия</label>
          <select name="service_id">
            <option value="0">Нет активной привилегии</option>
            <?php
              $db_services = require_once("core/db_services.php");
              if (!empty($db_services)) {
                foreach ($db_services as $rows) {
                  if ($rows["id"] == $editing_arr["service_id"]) {
                    $service_status = 'selected';
                  } else {
                    $service_status = '';
                  }
                  if ($rows["enabled"]) {
                    echo '
                     <option value="'.$rows["id"].'" '.$service_status.'>'.$rows["name"].'</option>
                    ';
                  }
                }
              }
            ?>
          </select>
          <label>Дата покупки</label>
          <?php
            if(isset($editing_arr["service_start"])) {
              $service_start = formatDateString($editing_arr["service_start"], 'Y-m-d') . 'T' . formatDateString($editing_arr["service_start"], 'H:i');
            } else {
              $service_start = '';
            }
          ?>
          <input type="datetime-local" name="service_start" value="<?= $service_start ?>">
          <label>Дата окончания</label>
          <?php
            if(isset($editing_arr["service_end"])) {
              $service_end = formatDateString($editing_arr["service_end"], 'Y-m-d') . 'T' . formatDateString($editing_arr["service_end"], 'H:i');
            } else {
              $service_end = '';
            }
          ?>
          <input type="datetime-local" name="service_end" value="<?= $service_end ?>">
          <label>Привилегия навсегда</label>
          <label class="switcher">
            <input type="checkbox" name="service_nolimit" <?php if($editing_arr["service_nolimit"]): ?>checked<? endif; ?>>
            <span class="slider"></span>
          </label>
          <label>Тип привязки</label>
          <select name="service_bind_type">
            <option value="1" <?php if($editing_arr["service_bind_type"] == 1): ?>selected<? endif; ?>>Ник + пароль</option>
            <option value="2" <?php if($editing_arr["service_bind_type"] == 2): ?>selected<? endif; ?>>Steam ID + пароль</option>
          </select>
          <label>Администратор сайта</label>
          <label class="switcher">
            <input type="checkbox" name="isadmin" <?php if($editing_arr["isadmin"]): ?>checked<? endif; ?>>
            <span class="slider"></span>
          </label>
          <label>Модератор сайта</label>
          <label class="switcher">
            <input type="checkbox" name="ismoder" <?php if($editing_arr["ismoder"]): ?>checked<? endif; ?>>
            <span class="slider"></span>
          </label>
          <input type="hidden" name="user_id" value="<?= $editing_arr["id"] ?>" required>
          <button type="submit" class="btn">Редактировать</button>
          <a data-go-back href="profile?section=users" class="btn btn_primary" style="display:none;">Вернуться назад</a>
        </form>
      </div>
    <? endif; ?>
  </section>
<? else: ?>
  <section class="block">
    <h2 class="block__title">Список пользователей</h2>
    <div class="table-top">
      <p>Всего: <b id="users_count"><?= $paginator_total_rows ?></b></p>
      <div class="table-top__right">
        <a href="profile?section=users&add" class="btn btn_primary">Создать</a>
        <button id="upload-admins" class="btn btn_primary" title="Загрузить актуальный список привилегий на игровой сервер"><?= getSvg("img/icons/refresh.svg") ?></button>
      </div>
    </div>
    <div class="table-responsive appear-bottom">
      <table class="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Логин</th>
            <th>Статус</th>
            <th>Создан</th>
            <th>Посл. логин</th>
            <th>Действия</th>
          </tr>
        </thead>
        <tbody>
          <? foreach ($db_users as $rows): ?>
            <tr id="row_<?= $rows["id"] ?>">
              <td><?= $rows["id"] ?></td>
              <td>
                <span title="<?= $rows["nick"] ?> <? if($rows["name"]) echo '('.$rows["name"].')' ?>">
                  <?= $rows["login"] ?>
                </span>
                <? if($rows["isadmin"]) echo '<span title="Администратор сайта">&#9733;</span>' ?>
                <? if($rows["ismoder"]) echo '<span title="Модератор сайта">&#9410;</span>' ?>
              </td>
              <td><?= getUserPosition($rows["id"],null) ?></td>
              <td><?= dateWhen(strtotime($rows["cdate"])) ?></td>
              <td>
                <? if(!empty($rows["ip"])): ?>
                  <a href="http://check-host.net/ip-info?host=<?= $rows["ip"] ?>" target="_blank" rel="noreferrer"><?= getSvg("img/icons/map-marker.svg") ?></a>
                <? endif; ?>
                <? if(!empty($rows["last_sign"])): ?>
                  <?= dateWhen(strtotime($rows["last_sign"])) ?>
                <? else: ?>
                  &#10134;
                <? endif; ?>
              </td>
              <td>
                <div class="table__edit">
                  <a href="profile?section=users&edit=<?= $rows["id"] ?>" title="Редактировать"><?= getSvg("img/icons/edit.svg") ?></a>
                  <? if($rows["id"] != 1): ?>
                    <button data-del-user="<?= $rows["id"] ?>" title="Удалить"><?= getSvg("img/icons/delete.svg") ?></button>
                  <? endif; ?>
                </div>
              </td>
            </tr>
          <? endforeach; ?>
        </tbody>
      </table>
    </div>
    <? if (ceil($paginator_total_rows / $paginator_page_limit) > 1 && empty($search_keyword)): ?>
      <div class="pagination appear-bottom anim-delay-1">
        <? if ($paginator_current_page > 1): ?>
          <a href="?section=users&page=<?= $paginator_current_page-1 ?>" class="pagination__item">&lt;</a>
        <? endif; ?>

        <? if ($paginator_current_page > 3): ?>
          <a href="?section=users&page=1" class="pagination__item">1</a>
          <span class="pagination__item">...</span>
        <? endif; ?>

        <? if ($paginator_current_page-2 > 0): ?><a href="?section=users&page=<?= $paginator_current_page-2 ?>" class="pagination__item"><?= $paginator_current_page-2 ?></a><?php endif; ?>
        <? if ($paginator_current_page-1 > 0): ?><a href="?section=users&page=<?= $paginator_current_page-1 ?>" class="pagination__item"><?= $paginator_current_page-1 ?></a><?php endif; ?>

        <a href="?section=users&page=<?= $paginator_current_page ?>" class="pagination__item active"><?= $paginator_current_page ?></a>

        <? if ($paginator_current_page+1 < ceil($paginator_total_rows / $paginator_page_limit)+1): ?><a href="?section=users&page=<?= $paginator_current_page+1 ?>" class="pagination__item"><?= $paginator_current_page+1 ?></a><?php endif; ?>
        <? if ($paginator_current_page+2 < ceil($paginator_total_rows / $paginator_page_limit)+1): ?><a href="?section=users&page=<?= $paginator_current_page+2 ?>" class="pagination__item"><?= $paginator_current_page+2 ?></a><?php endif; ?>

        <? if ($paginator_current_page < ceil($paginator_total_rows / $paginator_page_limit)-2): ?>
          <span class="pagination__item">...</span>
          <a href="?section=users&page=<?= ceil($paginator_total_rows / $paginator_page_limit) ?>" class="pagination__item"><?= ceil($paginator_total_rows / $paginator_page_limit) ?></a>
        <? endif; ?>

        <? if ($paginator_current_page < ceil($paginator_total_rows / $paginator_page_limit)): ?>
          <a href="?section=users&page=<?= $paginator_current_page+1 ?>" class="pagination__item">&gt;</a>
        <? endif; ?>
      </div>
    <? endif; ?>
  </section>
<? endif; ?>