<?php

$news_list_limit = 3;

$last_n_news = $pdo->prepare("SELECT * FROM ".$prefix."_news WHERE enabled IS TRUE ORDER BY cdate DESC, id DESC LIMIT :limit");
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
          <p class="news__date"><?=dateWhen(strtotime($rows["cdate"]))?></p>
          <a href="news?<?=$rows["url"]?>" class="news__link">Подробнее</a>
        </div>
      </article>
    <? endforeach; ?>
    <!-- <div class="news__more"><a href="news" class="btn btn_primary">Все новости</a></div> -->
  </div>
<? else: ?>
  <div class="well"><p>Новостей нет.</p><? if ($is_user_admin || $is_user_moder): ?><a href="profile?section=news&add" class="btn btn_primary">Опубликовать</a><? endif; ?></div>
<? endif; ?>