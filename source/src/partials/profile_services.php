<?php

$db_services = require_once("core/db_services.php");

?>

<?php if(isset($_GET["add"])): ?>
  <section class="block">
    <h2 class="block__title">Добавить привилегию</h2>
    <div class="well appear-bottom">
      <form id="add_services" method="post" class="form">
        <label>Название</label>
        <input type="text" name="name" placeholder="Название" required>
        <label>Флаги</label>
        <input type="text" name="flags" placeholder="abc..." required>
        <label>Дни
          <br><small>целые числа, через запятую, без пробелов</small>
        </label>
        <input type="text" name="days_number" placeholder="30,60,90" required>
        <label>Цены в руб.
          <br><small>целые числа, через запятую, без пробелов</small>
        </label>
        <input type="text" name="days_rub" placeholder="150,300,450" required>
        <label>Цены в грн.
          <br><small>целые числа, через запятую, без пробелов</small>
        </label>
        <input type="text" name="days_uah" placeholder="50,100,150" required>
        <label>Изображения</label>
        <div class="form__preview"></div>
        <input type="file" accept="image/*" name="images[]" data-upload="services" data-upload-multiple="true" placeholder="Изображения" multiple>
        <label>Аватар пользователей с этой привилегией</label>
        <div class="form__preview"></div>
        <input type="file" accept="image/*" name="user_avatar[]" data-upload="services" placeholder="Аватар пользователей с этой привилегией">
        <label>Описание</label>
        <textarea data-editor="wysiwyg" name="description" cols="30" rows="10" placeholder="Например, возможности:" required></textarea>
        <label>
          Привилегия активна
          <br><small>должна ли вносится в users.ini</small>
        </label>
        <label class="switcher">
          <input type="checkbox" name="enabled" checked>
          <span class="slider round"></span>
        </label>
        <label>
          Возможность покупки
          <br><small>например, привилегия "Девушка" не должна продаваться - назначается вручную, но должна вноситься в users.ini</small>
        </label>
        <label class="switcher">
          <input type="checkbox" name="buyable" checked>
          <span class="slider round"></span>
        </label>
        <button type="submit" class="btn">Добавить</button>
      </form>
    </div>
  </section>
<? elseif(isset($_GET["edit"])): ?>
  <section class="block">
    <?php
      $editing_arr;
      foreach ($db_services as $rows) {
        if ($rows["id"] == $_GET["edit"]) {
          $editing_arr = $rows;
          foreach (json_decode($editing_arr["days"], true) as $key => $days) {
            $arr_days_num[] = strval($days["days"]);
            $days_num = implode(",", $arr_days_num);
            $arr_price_rub_num[] = strval($days["price_rub"]);
            $price_rub_num = implode(",", $arr_price_rub_num);
            $arr_days_price_uah_num[] = strval($days["price_uah"]);
            $price_uah_num = implode(",", $arr_days_price_uah_num);
          }
          $editing_arr["days_num"] = $days_num;
          $editing_arr["price_rub_num"] = $price_rub_num;
          $editing_arr["price_uah_num"] = $price_uah_num;
          break;
        } else {
          $editing_arr = "not_found";
        }
      }
    ?>
    <? if ($editing_arr == "not_found"): ?>
      <h2 class="block__title">Такой привилегии не найдено!</h2>
    <? else: ?>
      <h2 class="block__title">Редактирование <?= $editing_arr["name"] ?></h2>
      <div class="well appear-bottom">
        <form id="edit_services" method="post" class="form">
          <label>Название</label>
          <input type="text" name="name" value="<?= $editing_arr["name"] ?>" required>
          <label>Флаги</label>
          <input type="text" name="flags" placeholder="abc..." value="<?= $editing_arr["flags"] ?>" required>
          <label>Дни (целые числа, через запятую, без пробелов)</label>
          <input type="text" name="days_number" placeholder="30,60,90" value="<?= $editing_arr["days_num"] ?>" required>
          <label>Цены в руб. (целые числа, через запятую, без пробелов)</label>
          <input type="text" name="days_rub" placeholder="150,300,450" value="<?= $editing_arr["price_rub_num"] ?>" required>
          <label>Цены в грн. (целые числа, через запятую, без пробелов)</label>
          <input type="text" name="days_uah" placeholder="50,100,150" value="<?= $editing_arr["price_uah_num"] ?>" required>
          <label>Изображения</label>
          <div class="form__preview">
            <? if (!empty($editing_arr["images"])): ?>
              <? foreach(json_decode($editing_arr["images"], true) as $image): ?>
                <div class="form__image"><img src="<?= urlEncodeSpaces($image) ?>" alt="<?= $editing_arr["name"] ?>"></div>
              <? endforeach; ?>
            <? endif; ?>
          </div>
          <input type="file" accept="image/*" name="images[]" data-upload="services" data-upload-multiple="true" placeholder="Изображения" multiple>
          <label>Аватар пользователей с этой привилегией</label>
          <div class="form__preview">
            <? if (!empty($editing_arr["user_avatar"])): ?>
              <div class="form__image"><img src="<?= urlEncodeSpaces($editing_arr["user_avatar"]) ?>" alt="<?= $editing_arr["name"] ?>"></div>
            <? endif; ?>
          </div>
          <input type="file" accept="image/*" name="user_avatar[]" data-upload="services" placeholder="Аватар пользователей с этой привилегией">
          <label>Описание</label>
          <textarea data-editor="wysiwyg" name="description" cols="30" rows="10" placeholder="Например, возможности:" required><?= $editing_arr["description"] ?></textarea>
          <label>
            Привилегия активна
            <br><small>должна ли вносится в users.ini</small>
          </label>
          <label class="switcher">
            <input type="checkbox" name="enabled" <?php if($editing_arr["enabled"]): ?>checked<? endif; ?>>
            <span class="slider round"></span>
          </label>
          <label>
            Возможность покупки
            <br><small>например, привилегия "Девушка" не должна продаваться - назначается вручную, но должна вноситься в users.ini</small>
          </label>
          <label class="switcher">
            <input type="checkbox" name="buyable" <?php if($editing_arr["buyable"]): ?>checked<? endif; ?>>
            <span class="slider round"></span>
          </label>
          <input type="hidden" name="service_id" value="<?= $_GET["edit"] ?>">
          <button type="submit" class="btn">Редактировать</button>
          <a data-go-back href="profile?section=services" class="btn btn_primary" style="display:none;">Вернуться назад</a>
        </form>
      </div>
    <? endif; ?>
  </section>
<? else: ?>
  <?php if(!empty($db_services)): ?>
    <section class="block">
      <h2 class="block__title">Список привилегий</h2>
      <div class="table-top">
        <p>Всего: <b id="services_count"><?= count($db_services) ?></b></p>
        <a href="profile?section=services&add" class="btn btn_primary">Добавить</a>
      </div>
      <div class="table-responsive appear-bottom">
        <table class="table">
          <thead>
            <tr>
              <th>#</th>
              <th>Название</th>
              <th>Флаги</th>
              <th>Аватар</th>
              <th>Активна</th>
              <th>Покупка</th>
              <th>Действия</th>
            </tr>
          </thead>
          <tbody>
            <?php
              $serv_index = 0;
              foreach ($db_services as $rows) {
                echo '<tr id="row_'.$rows["id"].'">';
                echo '
                  <td>'.++$serv_index.'</td>
                  <td>'.$rows["name"].'</td>
                  <td>'.$rows["flags"].'</td>
                ';
                if ($rows["user_avatar"]) {
                  echo '<td><img src="'.urlEncodeSpaces($rows["user_avatar"]).'" title="'.$rows["name"].'" alt="'.$rows["name"].'" data-zoomable></td>';
                } else {
                  echo '<td><img src="img/no_avatar.jpg" title="'.$rows["name"].'" alt="'.$rows["name"].'" data-zoomable></td>';
                }
                if ($rows["enabled"]) {
                  echo '<td>&#10133;</td>';
                } else {
                  echo '<td>&#10134;</td>';
                }
                if ($rows["buyable"]) {
                  echo '<td>&#10133;</td>';
                } else {
                  echo '<td>&#10134;</td>';
                }
                echo '
                  <td>
                    <div class="table__edit">
                      <a href="profile?section=services&edit='.$rows["id"].'" title="Редактировать">'.getSvg("img/icons/edit.svg").'</a>
                      <button data-del-service="'.$rows["id"].'" title="Удалить">'.getSvg("img/icons/delete.svg").'</button>
                    </div>
                  </td>
                ';
                echo '</tr>';
              }
            ?>
          </tbody>
        </table>
      </div>
    </section>
  <? else: ?>
    <section class="block">
      <h2 class="block__title">Список привилегий</h2>
      <div class="well appear-bottom">
        <p>Привилегий нет.</p>
        <a href="profile?section=services&add" class="btn btn_primary">Добавить</a>
      </div>
    </section>
  <? endif; ?>
<? endif; ?>