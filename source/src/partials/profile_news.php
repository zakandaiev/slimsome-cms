<?php

// PAGINATION
$paginator_page_limit = 30;

$paginator_total_rows = $pdo->query("SELECT COUNT(id) as count FROM ".$prefix."_news")->fetch(PDO::FETCH_LAZY)->count;

$paginator_current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;

$paginator_calc_page = ($paginator_current_page - 1) * $paginator_page_limit;

$db_news_query = $pdo->prepare("
  SELECT *,
    (SELECT coalesce(nick,login) FROM ".$prefix."_users WHERE id=t_news.author) as author_nick,
    (SELECT name FROM ".$prefix."_users WHERE id=t_news.author) as author_name
  FROM ".$prefix."_news t_news
  ORDER BY cdate DESC
  LIMIT ".$paginator_calc_page.",".$paginator_page_limit."
");

$db_news_query->execute();

$db_news = $db_news_query->fetchAll(PDO::FETCH_ASSOC);

$user_info = getUserInfo(null, $user_login);
  
?>

<?php if(isset($_GET["add"])): ?>
  <section class="block">
    <h2 class="block__title">Добавление новости</h2>
    <div class="well appear-bottom">
      <form id="add_news" method="post" class="form">
        <label>Название</label>
        <input id="news_title" type="text" name="title" placeholder="Название" required>
        <label>Ссылка</label>
        <input id="news_url" type="text" name="url" placeholder="Например: hello-world" required>
        <label>Изображение</label>
        <div class="form__preview"></div>
        <input type="file" accept="image/*" name="image[]" data-upload="news" placeholder="Изображение">
        <label>Текст новости</label>
        <textarea data-editor="wysiwyg" name="body" cols="30" rows="10" placeholder="Текст новости"></textarea>
        <label>SEO описание
          <br><small>&lt;meta name="description"&gt;</small>
        </label>
        <textarea name="meta_description" rows="3"><?= $GLOBALS["site_description"] ?></textarea>
        <label>SEO ключевые слова
          <br><small>&lt;meta name="keywords"&gt;</small>
        </label>
        <textarea name="meta_keywords" rows="3"><?= $GLOBALS["site_keywords"] ?></textarea>
        <label>Автор</label>
        <select name="author" required>
          <option value="<?=$user_info["id"]?>" selected>
            <? if($user_info["nick"]): ?>
              <?= $user_info["nick"] ?>
            <? else: ?>
              <?= $user_info["login"] ?>
            <? endif; ?>
            <? if($user_info["name"]): ?>
              (<?= $user_info["name"] ?>)
            <? endif; ?>
            (Вы)
          </option>
          <?php
            $db_moderators_query = $pdo->prepare("SELECT id, login, nick, name FROM ".$prefix."_users WHERE (isadmin IS TRUE OR ismoder IS TRUE) AND id!=:curr_user");
            $db_moderators_query->bindParam(":curr_user", $user_info["id"]);
            $db_moderators_query->execute();
            $db_moderators = $db_moderators_query->fetchAll(PDO::FETCH_ASSOC);
            if(!empty($db_moderators)):
              foreach ($db_moderators as $moder):
          ?>
          <option value="<?=$moder["id"]?>">
            <? if($moder["nick"]): ?>
              <?= $moder["nick"] ?>
            <? else: ?>
              <?= $moder["login"] ?>
            <? endif; ?>
            <? if($moder["name"]): ?>
              (<?= $moder["name"] ?>)
            <? endif; ?>
          </option>
          <? endforeach; ?>
          <? endif; ?>
        <select>
        <label>Дата публикации</label>
        <input type="datetime-local" name="cdate" value="<?=date('Y-m-d').'T'.date('H:i:s')?>">
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
      $editing_id = $_GET["edit"];
      $editing_arr = "not_found";

      $editing_query = $pdo->prepare("SELECT * FROM ".$prefix."_news WHERE id=:id");
      $editing_query->bindParam(":id", $editing_id);
      $editing_query->execute();

      $editing_query = $editing_query->fetch(PDO::FETCH_ASSOC);

      if(!empty($editing_query)) {
        $editing_arr = $editing_query;
      }
    ?>
    <? if ($editing_arr == "not_found"): ?>
      <h2 class="block__title">Такой новости не найдено!</h2>
    <? else: ?>
      <h2 class="block__title">Редактирование <?= $editing_arr["title"] ?></h2>
      <div class="well appear-bottom">
        <form id="edit_news" method="post" class="form">
          <label>Название</label>
          <input id="news_title" type="text" name="title" placeholder="Название" value="<?= $editing_arr["title"] ?>" required>
          <label>Ссылка</label>
          <input id="news_url" type="text" name="url" placeholder="Например: hello-world" value="<?= $editing_arr["url"] ?>" required>
          <label>Изображение</label>
          <div class="form__preview">
            <? if (!empty($editing_arr["image"])): ?>
              <div class="form__image"><img src="<?= urlEncodeSpaces($editing_arr["image"]) ?>" alt="<?= $editing_arr["title"] ?>"></div>
            <? endif; ?>
          </div>
          <input type="file" accept="image/*" name="image[]" data-upload="news" placeholder="Изображение">
          <label>Текст новости</label>
          <textarea data-editor="wysiwyg" name="body" cols="30" rows="10" placeholder="Текст новости"><?= $editing_arr["body"] ?></textarea>
          <label>SEO описание
            <br><small>&lt;meta name="description"&gt;</small>
          </label>
          <?php
            if(empty($editing_arr["meta_description"])) {
              $editing_arr["meta_description"] = $GLOBALS["site_description"];
            }
          ?>
          <textarea name="meta_description" rows="3"><?= $editing_arr["meta_description"] ?></textarea>
          <label>SEO ключевые слова
            <br><small>&lt;meta name="keywords"&gt;</small>
          </label>
          <?php
            if(empty($editing_arr["meta_keywords"])) {
              $editing_arr["meta_keywords"] = $GLOBALS["site_keywords"];
            }
          ?>
          <textarea name="meta_keywords" rows="3"><?= $editing_arr["meta_keywords"] ?></textarea>          
          <label>Автор</label>
          <select name="author" required>
            <?php
              $db_moderators_query = $pdo->query("SELECT id, login, nick, name FROM ".$prefix."_users WHERE isadmin IS TRUE OR ismoder IS TRUE");
              $db_moderators = $db_moderators_query->fetchAll(PDO::FETCH_ASSOC);
              if(!empty($db_moderators)):
                foreach ($db_moderators as $moder):
            ?>
            <option value="<?=$moder["id"]?>" <? if($editing_arr["author"] == $moder["id"]): ?>selected<? endif; ?>>
              <? if($moder["nick"]): ?>
                <?= $moder["nick"] ?>
              <? else: ?>
                <?= $moder["login"] ?>
              <? endif; ?>
              <? if($moder["name"]): ?>
                (<?= $moder["name"] ?>)
              <? endif; ?>
              <? if($user_info["id"] == $moder["id"]): ?>
                (Вы)
              <? endif; ?>
            </option>
            <? endforeach; ?>
            <? endif; ?>
          <select>
          <label>Дата публикации</label>
          <?php
            if(isset($editing_arr["cdate"])) {
              $cdate = formatDateString($editing_arr["cdate"], 'Y-m-d') . 'T' . formatDateString($editing_arr["cdate"], 'H:i');
            } else {
              $cdate = date('Y-m-d').'T'.date('H:i');
            }
          ?>
          <input type="datetime-local" name="cdate" value="<?= $cdate ?>">
          <label>Отображать на сайте</label>
          <label class="switcher">
            <input type="checkbox" name="enabled" <? if($editing_arr["enabled"]): ?>checked<? endif; ?>>
            <span class="slider round"></span>
          </label>
          <input type="hidden" name="news_id" value="<?= $editing_arr["id"] ?>" required>
          <button type="submit" class="btn">Редактировать</button>
          <a data-go-back href="profile?section=news" class="btn btn_primary" style="display:none;">Вернуться назад</a>
        </form>
      </div>
    <? endif; ?>
  </section>
<? else: ?>
  <?php if(!empty($db_news)): ?>
    <section class="block">
      <h2 class="block__title">Список новостей</h2>
      <div class="table-top">
        <p>Всего: <b id="news_count"><?= $paginator_total_rows ?></b></p>
        <div class="table-top__right">
          <a href="profile?section=news&add" class="btn btn_primary">Добавить</a>
        </div>
      </div>
      <div class="table-responsive appear-bottom">
        <table class="table">
          <thead>
            <tr>
              <th>Название</th>
              <th>Автор</th>
              <th>Дата</th>
              <th>Опубликовано</th>
              <th>Действия</th>
            </tr>
          </thead>
          <tbody>
            <? foreach ($db_news as $rows): ?>
              <tr id="row_<?=$rows["id"]?>">
                <td><a href="news?<?= $rows["url"] ?>" target="_blank"><?= $rows["title"] ?></a></td>
                <td title="<?= $rows["author_name"] ?>"><?= $rows["author_nick"] ?></td>
                <td><?= dateWhen(strtotime($rows["cdate"])) ?></td>
                <? if ($rows["enabled"]): ?>
                  <td>&#10133;</td>
                <? else: ?>
                  <td>&#10134;</td>
                <? endif; ?>
                <td>
                  <div class="table__edit">
                    <a href="profile?section=news&edit=<?= $rows["id"] ?>" title="Редактировать"><?= getSvg("img/icons/edit.svg") ?></a>
                    <button data-del-news="<?= $rows["id"] ?>" title="Удалить"><?= getSvg("img/icons/delete.svg") ?></button>
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
            <a href="?section=news&page=<?= $paginator_current_page-1 ?>" class="pagination__item">&lt;</a>
          <? endif; ?>

          <? if ($paginator_current_page > 3): ?>
            <a href="?section=news&page=1" class="pagination__item">1</a>
            <span class="pagination__item">...</span>
          <? endif; ?>

          <? if ($paginator_current_page-2 > 0): ?><a href="?section=news&page=<?= $paginator_current_page-2 ?>" class="pagination__item"><?= $paginator_current_page-2 ?></a><?php endif; ?>
          <? if ($paginator_current_page-1 > 0): ?><a href="?section=news&page=<?= $paginator_current_page-1 ?>" class="pagination__item"><?= $paginator_current_page-1 ?></a><?php endif; ?>

          <a href="?section=news&page=<?= $paginator_current_page ?>" class="pagination__item active"><?= $paginator_current_page ?></a>

          <? if ($paginator_current_page+1 < ceil($paginator_total_rows / $paginator_page_limit)+1): ?><a href="?section=news&page=<?= $paginator_current_page+1 ?>" class="pagination__item"><?= $paginator_current_page+1 ?></a><?php endif; ?>
          <? if ($paginator_current_page+2 < ceil($paginator_total_rows / $paginator_page_limit)+1): ?><a href="?section=news&page=<?= $paginator_current_page+2 ?>" class="pagination__item"><?= $paginator_current_page+2 ?></a><?php endif; ?>

          <? if ($paginator_current_page < ceil($paginator_total_rows / $paginator_page_limit)-2): ?>
            <span class="pagination__item">...</span>
            <a href="?section=news&page=<?= ceil($paginator_total_rows / $paginator_page_limit) ?>" class="pagination__item"><?= ceil($paginator_total_rows / $paginator_page_limit) ?></a>
          <? endif; ?>

          <? if ($paginator_current_page < ceil($paginator_total_rows / $paginator_page_limit)): ?>
            <a href="?section=news&page=<?= $paginator_current_page+1 ?>" class="pagination__item">&gt;</a>
          <? endif; ?>
        </div>
      <? endif; ?>
    </section>
  <? else: ?>
    <section class="block">
      <h2 class="block__title">Список новостей</h2>
      <div class="well appear-bottom">
        <p>Новостей нет.</p>
        <a href="profile?section=news&add" class="btn btn_primary">Добавить</a>
      </div>
    </section>
  <? endif; ?>
<? endif; ?>