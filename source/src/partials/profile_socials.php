<?php

  $db_socials_query = $GLOBALS["socials"];

?>

<?php if(isset($_GET["add"])): ?>
  <section class="block appear-bottom anim-delay-1">
    <h2 class="block__title">Добавить соц. сеть</h2>
    <div class="well">
      <form id="add_socials" method="post" class="form">
        <label>
          Иконка
          <br><small>Должна лежать в <?=$GLOBALS["site_url"]?>img/socials</small>
        </label>
        <select name="icon" required>
          <?php
            $socials_dir = dirname(__FILE__)."/../img/socials";
            if (file_exists($socials_dir)) {
              $icons = glob($socials_dir."/*.{svg,png,jpg,gif}", GLOB_BRACE);
              foreach ($icons as $icon) {
                echo '<option value="'.basename($icon).'">'.basename($icon).'</option>';
              }
            }
          ?>
        </select>
        <label>Ссылка
          <br><small>Включая протокол (https: или mailto: или tel:)</small>
        </label>
        <input type="url" name="url" placeholder="https://site.ru/" required>
        <label>Открывать в новой вкладке</label>
        <label class="switcher">
          <input type="checkbox" name="blank">
          <span class="slider round"></span>
        </label>
        <button type="submit" class="btn">Добавить</button>
      </form>
    </div>
  </section>
<? elseif(isset($_GET["edit"])): ?>
  <section class="block">
    <?php
      $db_socials_query = json_decode($db_socials_query, true);
      $editing_arr;
      foreach ($db_socials_query as $key => $rows) {
        if ($key == $_GET["edit"]) {
          $editing_arr = $rows;
          break;
        } else {
          $editing_arr = "not_found";
        }
      }
    ?>
    <? if ($editing_arr == "not_found"): ?>
      <h2 class="block__title">Такой соц. сети не найдено!</h2>
    <? else: ?>
      <h2 class="block__title">Редактирование <?= $editing_arr["icon"] ?></h2>
      <div class="well appear-bottom">
        <form id="edit_socials" method="post" class="form">
          <label>
            Иконка
            <br><small>Должна лежать в <?=$GLOBALS["site_url"]?>img/socials</small>
          </label>
          <select name="icon" required>
            <?php
              $socials_dir = dirname(__FILE__)."/../img/socials";
              if (file_exists($socials_dir)) {
                $icons = glob($socials_dir."/*.{svg,png,jpg,gif}", GLOB_BRACE);
                foreach ($icons as $icon) {
                  if($editing_arr["icon"] == basename($icon)) {
                    $selected = 'selected';
                  } else {
                    $selected = '';
                  }
                  echo '<option value="'.basename($icon).'" '.$selected.'>'.basename($icon).'</option>';
                }
              }
            ?>
          </select>
          <label>Ссылка
            <br><small>Включая протокол (https: или mailto: или tel:)</small>
          </label>
          <input type="url" name="url" placeholder="https://site.ru/" value="<?= $editing_arr["url"] ?>" required>
          <label>Открывать в новой вкладке</label>
          <label class="switcher">
            <input type="checkbox" name="blank" <?php if($editing_arr["blank"]): ?>checked<? endif; ?>>
            <span class="slider round"></span>
          </label>
          <input type="hidden" name="social_id" value="<?= $_GET["edit"] ?>">
          <button type="submit" class="btn">Редактировать</button>
          <a data-go-back href="profile?section=socials" class="btn btn_primary" style="display:none;">Вернуться назад</a>
        </form>
      </div>
    <? endif; ?>
  </section>
<? else: ?>
  <?php if(!empty($db_socials_query)): ?>
    <section class="block">
      <h2 class="block__title">Список соц. сетей</h2>
      <div class="table-top">
        <p>Всего: <b id="socials_count"><?= count(json_decode($db_socials_query, true)) ?></b></p>
        <a href="profile?section=socials&add" class="btn btn_primary">Добавить</a>
      </div>
      <div class="table-responsive appear-bottom">
        <table class="table">
          <thead>
            <tr>
              <th>#</th>
              <th>Иконка</th>
              <th>Ссылка</th>
              <th>Действия</th>
            </tr>
          </thead>
          <tbody>
            <?php
              $soc_index = 0;
              foreach (json_decode($db_socials_query, true) as $key => $rows) {
                echo '
                  <tr id="row_'.$key.'">
                    <td>'.++$soc_index.'</td>
                    <td>'.$rows["icon"].'</td>
                    <td>'.$rows["url"].' <a href="'.$rows["url"].'" target="_blank" title="Перейти по ссылке">➥</a></td>
                    <td>
                      <div class="table__edit">
                        <a href="profile?section=socials&edit='.$key.'" title="Редактировать">'.getSvg("img/icons/edit.svg").'</a>
                        <button data-del-social="'.$key.'" title="Удалить">'.getSvg("img/icons/delete.svg").'</button>
                      </div>
                    </td>
                  </tr>
                ';
              }
            ?>
          </tbody>
        </table>
      </div>
    </section>
  <? else: ?>
    <section class="block">
      <h2 class="block__title">Список соц. сетей</h2>
      <div class="well appear-bottom">
        <p>Социальных сетей нет.</p>
        <a href="profile?section=socials&add" class="btn btn_primary">Добавить</a>
      </div>
    </section>
  <? endif; ?>
<? endif; ?>