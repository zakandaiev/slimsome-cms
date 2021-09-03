<?php

$news_list_limit = 3;

$last_n_news = $pdo->prepare("
  SELECT *,
    (SELECT coalesce(nick,login) FROM ".$prefix."_users WHERE id=t_news.author) as author_nick,
    (SELECT name FROM ".$prefix."_users WHERE id=t_news.author) as author_name,
    (SELECT count(id) FROM ".$prefix."_comments WHERE news_id=t_news.id) as comments_count
  FROM ".$prefix."_news t_news
  WHERE enabled is TRUE
  ORDER BY cdate DESC, id DESC
  LIMIT :limit
");
$last_n_news->bindParam(":limit", $news_list_limit, PDO::PARAM_INT);
$last_n_news->execute();
$last_n_news = $last_n_news->fetchAll(PDO::FETCH_ASSOC);

?>

<? if(!empty($last_n_news)): ?>
  <div class="news">
    <? foreach ($last_n_news as $rows): ?>
      <article class="news__item">
        <a href="news?<?=$rows["url"]?>" class="news__img">
          <? if(!empty($rows["image"])): ?>
            <img src="<?=urlEncodeSpaces($rows["image"])?>" alt="<?=$rows["title"]?>">
          <? else: ?>
            <img src="img/no_image.jpg" alt="<?=$rows["title"]?>">
          <? endif; ?>
        </a>
        <div class="news__info">
          <h3 class="news__title"><a href="news?<?=$rows["url"]?>"><?=$rows["title"]?></a></h3>
          <div class="news-meta">
            <span class="news-meta__item">
              <?= getSvg("img/icons/clock.svg") ?>
              <?=dateWhen(strtotime($rows["cdate"]))?>
            </span>
            <span class="news-meta__item" title="<?= $rows["author_name"] ?>">
              <?= getSvg("img/icons/user.svg") ?>
              <?= $rows["author_nick"] ?>
            </span>
            <a href="news?<?=$rows["url"]?>#comments" class="news-meta__item">
              <?= getSvg("img/icons/comments.svg") ?>
              <?= getCommentsNumString($rows["comments_count"]) ?>
            </a>
          </div>
          <a href="news?<?=$rows["url"]?>" class="news__link">Подробнее</a>
        </div>
      </article>
    <? endforeach; ?>
    <!-- <div class="news__more"><a href="news" class="btn btn_primary">Все новости</a></div> -->
  </div>
<? else: ?>
  <div class="well"><p>Новостей нет.</p><? if ($is_user_admin || $is_user_moder): ?><a href="profile?section=news&add" class="btn btn_primary">Опубликовать</a><? endif; ?></div>
<? endif; ?>