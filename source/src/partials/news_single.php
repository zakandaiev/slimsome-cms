<?php

$news_extra_query = $pdo->prepare("
  SELECT
    JSON_OBJECT(
      'title', prev_title,
      'url', prev_url,
      'image', prev_image
    ) as extra_prev,
    JSON_OBJECT(
      'title', next_title,
      'url', next_url,
      'image', next_image
    ) as extra_next
  FROM (
    SELECT id,
      LAG(title) OVER (ORDER BY cdate) as prev_title,
      LAG(url) OVER (ORDER BY cdate) as prev_url,
      LAG(image) OVER (ORDER BY cdate) as prev_image,
      LEAD(title) OVER (ORDER BY cdate) as next_title,
      LEAD(url) OVER (ORDER BY cdate) as next_url,
      LEAD(image) OVER (ORDER BY cdate) as next_image
    FROM ".$prefix."_news
    WHERE enabled IS TRUE
  ) t
  WHERE id=:current_id;
");
$news_extra_query->bindParam(":current_id", $current_post["id"]);
$news_extra_query->execute();
$news_extra = $news_extra_query->fetch(PDO::FETCH_LAZY);
$extra_prev = json_decode($news_extra->extra_prev, true);
$extra_next = json_decode($news_extra->extra_next, true);

$news_comments_query = $pdo->prepare("
  SELECT *,
    (SELECT coalesce(nick,login) FROM ".$prefix."_users WHERE id=t_comments.author) as author_nick,
    (SELECT name FROM ".$prefix."_users WHERE id=t_comments.author) as author_name,
    (CASE 
      WHEN (SELECT name FROM ".$prefix."_services WHERE id=(SELECT service_id FROM ".$prefix."_users WHERE id=t_comments.author AND service_id IS NOT NULL AND (service_nolimit IS TRUE OR CURRENT_TIMESTAMP < service_end)) AND enabled IS TRUE) IS NOT NULL
      THEN (SELECT name FROM ".$prefix."_services WHERE id=(SELECT service_id FROM ".$prefix."_users WHERE id=t_comments.author AND service_id IS NOT NULL AND (service_nolimit IS TRUE OR CURRENT_TIMESTAMP < service_end)) AND enabled IS TRUE)
      WHEN (SELECT isadmin FROM ".$prefix."_users WHERE id=t_comments.author) = 1
      THEN 'Администратор сайта'
      WHEN (SELECT ismoder FROM ".$prefix."_users WHERE id=t_comments.author) = 1
      THEN 'Модератор сайта'
      ELSE 'Пользователь'
    END) as author_position,
    (CASE 
      WHEN (SELECT user_avatar FROM ".$prefix."_services WHERE id=(SELECT service_id FROM ".$prefix."_users WHERE id=t_comments.author AND service_id IS NOT NULL AND (service_nolimit IS TRUE OR CURRENT_TIMESTAMP < service_end)) AND enabled IS TRUE) IS NOT NULL
      THEN (SELECT user_avatar FROM ".$prefix."_services WHERE id=(SELECT service_id FROM ".$prefix."_users WHERE id=t_comments.author AND service_id IS NOT NULL AND (service_nolimit IS TRUE OR CURRENT_TIMESTAMP < service_end)) AND enabled IS TRUE)
      ELSE 'img/no_avatar.jpg'
    END) as author_avatar
  FROM ".$prefix."_comments t_comments
  WHERE news_id=:current_id
  ORDER BY cdate ASC
  LIMIT 30;
");
$news_comments_query->bindParam(":current_id", $current_post["id"]);
$news_comments_query->execute();
$news_comments = $news_comments_query->fetchAll(PDO::FETCH_ASSOC);

$news_comments_total_rows = $pdo->prepare("SELECT COUNT(id) as count FROM ".$prefix."_comments WHERE news_id=:current_id");
$news_comments_total_rows->bindParam(":current_id", $current_post["id"]);
$news_comments_total_rows->execute();
$news_comments_total_rows = $news_comments_total_rows->fetch(PDO::FETCH_LAZY)->count;

?>

<div class="news-content">
  <main class="news-content__left">
    <? if (!empty($current_post["image"])): ?>
      <div class="news-content__img">
        <img src="<?=urlEncodeSpaces($current_post["image"])?>" alt="<?=$current_post["title"]?>" data-zoomable>
      </div>
    <? endif; ?>
    <article class="news-content__article">
      <h2 class="news-content__title"><?=$current_post["title"]?></h2>
      <div class="news-meta">
        <span class="news-meta__item">
          <?= getSvg("img/icons/clock.svg") ?>
          <?=dateWhen(strtotime($current_post["cdate"]))?>
        </span>
        <span class="news-meta__item" title="<?= $current_post["author_name"] ?>">
          <?= getSvg("img/icons/user.svg") ?>
          <?= $current_post["author_nick"] ?>
        </span>
        <span class="news-meta__item" data-scroll-to="comments">
          <?= getSvg("img/icons/comments.svg") ?>
          <?= getCommentsNumString($news_comments_total_rows) ?>
        </span>
        <? if ($is_user_admin || $is_user_moder): ?>
          <a href="profile?section=news&edit=<?=$current_post["id"]?>" class="news-meta__item">
            <?= getSvg("img/icons/pencil.svg") ?>
            Редактировать
          </a>
        <? endif; ?>
      </div>
      <div class="news-content__body">
        <? if (!empty($current_post["body"])): ?>
          <?=$current_post["body"]?>
        <? else: ?>
          <?=$current_post["title"]?>
        <? endif; ?>
      </div>
    </article>
    <? if (!empty($extra_prev["url"]) || !empty($extra_next["url"])): ?>
      <section class="block block_mb">
        <div class="news-extra">
          <? if (!empty($extra_prev["url"])): ?>
            <a href="news?<?=$extra_prev["url"]?>" class="news-extra__prev">
              <span class="news-extra__subtitle">Предыдущая новость</span>
              <span class="news-extra__title"><?=$extra_prev["title"]?></span>
              <? if (!empty($extra_prev["image"])): ?>
                <img src="<?=urlEncodeSpaces($extra_prev["image"])?>" alt="<?=$extra_prev["title"]?>" class="news-extra__img">
              <? endif; ?>
            </a>
          <? endif; ?>
          <? if (!empty($extra_next["url"])): ?>
            <a href="news?<?=$extra_next["url"]?>" class="news-extra__next">
              <span class="news-extra__subtitle">Следующая новость</span>
              <span class="news-extra__title"><?=$extra_next["title"]?></span>
              <? if (!empty($extra_next["image"])): ?>
                <img src="<?=urlEncodeSpaces($extra_next["image"])?>" alt="<?=$extra_next["title"]?>" class="news-extra__img">
              <? endif; ?>
            </a>
          <? endif; ?>
        </div>
      </section>
    <? endif; ?>
    <section id="comments" class="block">
      <h2 class="block__title">Комментарии</h2>
      <div class="comments">
        <div class="comments__messages">
          <? if(!empty($news_comments)): ?>
            <? foreach ($news_comments as $comment): ?>
              <div class="comments__item">
                <div class="comments__avatar">
                  <img class="avatar" src="<?=urlEncodeSpaces($comment["author_avatar"])?>" title="<?=$comment["author_position"]?>" alt="<?=$comment["author_position"]?>">
                </div>
                <div class="comments__message">
                  <div class="info">
                    <div class="author" title="<?=$comment["author_position"]?>"><?=$comment["author_nick"]?></div>
                    <div class="date">
                      <?=dateWhen(strtotime($comment["cdate"]))?>
                      <? if($is_user_admin || $is_user_moder): ?>
                        <br><span data-del-comment="<?=$comment["id"]?>">удалить</span>
                      <? endif; ?>
                    </div>
                  </div>
                  <div class="text"><?=replaceSmiles($comment["comment"])?></div>
                </div>
              </div>
            <? endforeach ?>
            <? if($news_comments_total_rows > 30): ?>
              <div class="loader"><div></div><div></div><div></div><div></div></div>
            <? endif; ?>
          <? else: ?>
            <span id="no_comments_label">Комментариев нет.</span>
          <? endif; ?>
        </div>
        <? if($is_user_logged): ?>
          <?php $user_info = getUserInfo(null, $user_login); ?>
          <div class="comments__write">
            <form id="add_comment" method="post" class="form">
              <div class="comments__input">
                <input type="text" name="comment" maxlength="250" placeholder="Написать комментарий..." required>
                <?= getSvg(dirname(__FILE__)."/../img/icons/smile.svg") ?>
                <div class="comments__smiles">
                  <div class="smiles">
                    <?php
                      $smiles_dir = dirname(__FILE__)."/../img/smiles";
                      if (file_exists($smiles_dir)) {
                        $smiles = glob($smiles_dir."/*.gif");
                        foreach ($smiles as $smile) {
                          echo '<span class="smiles__item"><img src="/img/smiles/'.basename($smile).'" data-filename="'.basename($smile).'" alt="'.substr(basename($smile), 0, -4).'"></span>';
                        }
                      }
                    ?>
                  </div>
                </div>
              </div>
              <input type="submit" class="btn" value="Отправить">
              <input type="hidden" name="user_id" value="<?= $user_info["id"] ?>" required>
              <input type="hidden" name="pass_check" value="<?= $user_info["password"] ?>" required>
              <input type="hidden" name="news_id" value="<?= $current_post["id"] ?>" required>
            </form>
          </div>
        <? else: ?>
          <div class="comments__write">
            <div class="text-center"><span data-open-login-form class="bordered">Авторизуйтесь</span>, чтобы оставлять комментарии.</div>
          </div>
        <? endif; ?>
      </div>
    </section>    
  </main>
  <aside class="news-content__right">
    <section class="block block_mb appear-right">
      <?php
        $author_position = getUserPosition($current_post["author"], null);
        $author_avatar = getUserAvatar($current_post["author"], null);
      ?>
      <div class="widget-author">
        <div class="widget-author__info">
          <img class="widget-author__avatar" src="<?=urlEncodeSpaces($author_avatar)?>" title="<?= $author_position ?>" alt="<?= $author_position ?>">
          <div class="widget-author__rec">
            <h3 class="widget-author__nick"><?= $current_post["author_nick"] ?></h3>
            <p class="widget-author__status"><?= $author_position ?></p>
          </div>
        </div>
        <div class="share-button a2a_kit a2a_kit_size_32 a2a_default_style">
          <div class="widget-author__share">
            <p class="widget-author__share-title">Поделиться новостью:</p>
            <a href="#" class="widget-author__share-item a2a_button_vk">
              <?= getSvg("img/share/vk.svg") ?>
            </a>
            <a href="#" class="widget-author__share-item a2a_button_telegram">
              <?= getSvg("img/share/telegram.svg") ?>
            </a>
            <a href="#" class="widget-author__share-item a2a_button_facebook">
              <?= getSvg("img/share/facebook.svg") ?>
            </a>
            <a href="#" class="widget-author__share-item a2a_dd">
              <?= getSvg("img/share/plus.svg") ?>
            </a>
          </div>
        </div>
      </div>
      <script async src="https://static.addtoany.com/menu/page.js"></script>
      <script>
        var a2a_config = a2a_config || {};
        a2a_config.prioritize = ["facebook_messenger", "whatsapp", "skype", "viber", "pinterest", "twitter", "odnoklassniki", "email"];
      </script> 
    </section>
    <?php
      $hot_news_query = $pdo->prepare("
        SELECT url, title, cdate FROM ".$prefix."_news t_news
        WHERE id!=:current_id AND (SELECT COUNT(id) FROM ".$prefix."_comments WHERE news_id=t_news.id) > 0
        ORDER BY (SELECT COUNT(id) FROM ".$prefix."_comments WHERE news_id=t_news.id) DESC
        LIMIT 3;
      ");
      $hot_news_query->bindParam(":current_id", $current_post["id"]);
      $hot_news_query->execute();
      $hot_news = $hot_news_query->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <? if(!empty($hot_news)): ?>
      <section class="block block_mb appear-right anim-delay-1">
        <h2 class="block__title">Обсуждаемое</h2>
        <div class="widget-hots">
          <? foreach ($hot_news as $rows): ?>
            <div class="widget-hots__item">
              <h3 class="widget-hots__title"><a href="news?<?=$rows["url"]?>"><?=$rows["title"]?></a></h3>
              <p class="widget-hots__date"><?=dateWhen(strtotime($rows["cdate"]))?></p>
            </div>
          <? endforeach; ?>
        </div>
      </section>
    <? endif; ?>
    <?php
      $last_comments_query = $pdo->query("
        SELECT cdate,
          (SELECT coalesce(nick,login) FROM ".$prefix."_users WHERE id=t_comments.author) as author_nick,
          (SELECT name FROM ".$prefix."_users WHERE id=t_comments.author) as author_name,
          (SELECT title FROM ".$prefix."_news WHERE id=t_comments.news_id) as news_title,
          (SELECT url FROM ".$prefix."_news WHERE id=t_comments.news_id) as news_url
        FROM ".$prefix."_comments t_comments
        ORDER BY cdate DESC
        LIMIT 3;
      ");
      $last_comments = $last_comments_query->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <? if(!empty($last_comments)): ?>
      <section class="block block_mb appear-right anim-delay-<?if(!empty($hot_news)):?>2<?else:?>1<?endif;?>">
        <h2 class="block__title">Последние комментарии</h2>
        <div class="widget-hots">
          <? foreach ($last_comments as $rows): ?>
            <div class="widget-hots__item">
              <p class="widget-hots__title last-comments"><span title="<?=$rows["author_name"]?>"><?=$rows["author_nick"]?></span> прокомментировал <a href="news?<?=$rows["news_url"]?>"><?=$rows["news_title"]?></a></p>
              <p class="widget-hots__date"><?=dateWhen(strtotime($rows["cdate"]))?></p>
            </div>
          <? endforeach; ?>
        </div>
      </section>
    <? endif; ?>
  </aside>
</div>