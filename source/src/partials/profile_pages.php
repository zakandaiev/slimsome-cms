<?php if(isset($_GET["add"])): ?>
  <section class="block">
    <h2 class="block__title">Добавить страницу</h2>
    <div class="well appear-bottom">
      <form id="add_page" method="post" class="form">
        <label>Название</label>
        <input id="page_name" type="text" name="name" placeholder="Отображается в меню" required>
        <label>Ссылка</label>
        <input id="page_url" type="text" name="url" placeholder="Например: pravila" required>
        <label>
          Кастомный шаблон
          <br><small>Должен лежать в <?= $GLOBALS["site_url"] ?>pages и иметь расширение .php</small>
        </label>
        <select name="template">
          <option selected value>Не применять</option>
          <?php
            $pages_dir = dirname(__FILE__)."/../pages";
            if (file_exists($pages_dir)) {
              $pages = glob($pages_dir."/*.php");
              foreach ($pages as $page) {
                echo '<option value="'.substr(basename($page), 0, -4).'">'.substr(basename($page), 0, -4).'</option>';
              }
            }
          ?>
        </select>
        <label>Описание</label>
        <textarea data-editor="wysiwyg" name="content" cols="30" rows="10"></textarea>
        <label>Порядок</label>
        <input type="number" min="1" max="10" name="page_order">
        <label>Отображать на сайте</label>
        <label class="switcher">
          <input type="checkbox" name="enabled" checked>
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
      foreach ($db_pages as $rows) {
        if ($rows["id"] == $_GET["edit"]) {
          $editing_arr = $rows;
          break;
        } else {
          $editing_arr = "not_found";
        }
      }
    ?>
    <? if ($editing_arr == "not_found"): ?>
      <h2 class="block__title">Такой страницы не найдено!</h2>
    <? else: ?>
      <h2 class="block__title">Редактирование <?= $editing_arr["name"] ?></h2>
      <div class="well appear-bottom">
        <?php
          foreach ($db_pages as $rows) {
            $templates = '';
            $pages_dir = dirname(__FILE__)."/../pages";
            if (file_exists($pages_dir)) {
              $pages = glob($pages_dir."/*.php");
              foreach ($pages as $page) {
                if($rows["template"] == substr(basename($page), 0, -4)) {
                  $selected = 'selected';
                } else {
                  $selected = '';
                }
                $templates .= '<option value="'.substr(basename($page), 0, -4).'" '.$selected.'>'.substr(basename($page), 0, -4).'</option>\n';
              }
            }
          }
        ?>
        <form id="<?= $editing_arr["id"] ?>" method="post" data-form-edit-page class="form">
          <label>Название</label>
          <input id="page_name" type="text" name="name" placeholder="Отображается в меню" value="<?= $editing_arr["name"] ?>" required>
          <label>Ссылка</label>
          <input id="page_url" type="text" name="url" placeholder="Например: pravila" value="<?= $editing_arr["url"] ?>" required>
          <label>
            Кастомный шаблон
            <br><small>Должен лежать в <?=$GLOBALS["site_url"]?>pages и иметь расширение .php</small>
          </label>
          <select name="template">
            <option selected value>Не применять</option>
            <?= $templates ?>
          </select>
          <label>Описание</label>
          <textarea data-editor="wysiwyg" name="content" cols="30" rows="10"><?= $editing_arr["content"] ?></textarea>
          <label>Порядок</label>
          <input type="number" min="1" max="10" name="page_order" value="<?= $editing_arr["page_order"] ?>">
          <label>Отображать на сайте</label>
          <label class="switcher">
            <input type="checkbox" name="enabled" <?php if($editing_arr["enabled"]): ?>checked<? endif; ?>>
            <span class="slider round"></span>
          </label>
          <input type="hidden" name="page_id" value="<?= $_GET["edit"] ?>">
          <button type="submit" class="btn">Редактировать</button>
          <a data-go-back href="profile?section=pages" class="btn btn_primary" style="display:none;">Вернуться назад</a>
        </form>
      </div>
    <? endif; ?>
  </section>
<? else: ?>
  <?php if(!empty($db_pages)): ?>
    <section class="block">
      <h2 class="block__title">Список страниц</h2>
      <div class="table-top">
        <p>Всего: <b id="pages_count"><?= count($db_pages) ?></b></p>
        <a href="profile?section=pages&add" class="btn btn_primary">Добавить</a>
      </div>
      <div class="table-responsive appear-bottom">
        <table class="table">
          <thead>
            <tr>
              <th>#</th>
              <th>Название</th>
              <th>Ссылка</th>
              <th>Шаблон</th>
              <th>Опубликовано</th>
              <th>Порядок</th>
              <th>Действия</th>
            </tr>
          </thead>
          <tbody>
            <?php
              $page_index = 0;
              foreach ($db_pages as $rows):
            ?>
              <tr id="row_<?= $rows["id"] ?>">
                <td><?= ++$page_index ?></td>
                <td><?= $rows["name"] ?></td>
                <td><?= $rows["url"] ?></td>
                <td>
                  <? if($rows["template"]): ?>
                    <?= $rows["template"] ?>
                  <? else: ?>
                    &#10134;
                  <? endif; ?>
                </td>
                <td>
                  <? if($rows["enabled"]): ?>
                    &#10133;
                  <? else: ?>
                    &#10134;
                  <? endif; ?>
                </td>
                <td>
                  <? if($rows["page_order"]): ?>
                    <?= $rows["page_order"] ?>
                  <? else: ?>
                    В конце
                  <? endif; ?>
                </td>
                <td>
                  <div class="table__edit">
                    <a href="profile?section=pages&edit=<?= $rows["id"] ?>" title="Редактировать"><?= getSvg("img/icons/edit.svg") ?></a>
                    <button data-del-page="<?= $rows["id"] ?>" title="Удалить"><?= getSvg("img/icons/delete.svg") ?></button>
                  </div>
                </td>
              </tr>
            <? endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>
  <? else: ?>
    <section class="block">
      <h2 class="block__title">Список страниц</h2>
      <div class="well appear-bottom">
        <p>Страниц нет.</p>
        <a href="profile?section=pages&add" class="btn btn_primary">Добавить</a>
      </div>
    </section>
  <? endif; ?>
<? endif; ?>