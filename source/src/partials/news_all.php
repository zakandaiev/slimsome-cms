<?php

// PAGINATION
$paginator_page_limit = 10;

$paginator_current_page = isset($_GET["page"]) && is_numeric($_GET["page"]) ? $_GET["page"] : 1;

$paginator_calc_page = ($paginator_current_page - 1) * $paginator_page_limit;

$search_keyword = "";
$is_search_active = "";
if(!empty($_GET["search"])) {
  $search_keyword = filter_var(trim($_GET["search"]), FILTER_SANITIZE_STRING);
  $is_search_active = "AND title LIKE :keyword OR meta_description LIKE :keyword OR meta_keywords LIKE :keyword OR body LIKE :keyword";
}

$paginator_total_rows = $pdo->prepare("
  SELECT COUNT(id) as count FROM ".$prefix."_news WHERE enabled is TRUE
  ".$is_search_active.";
");
if(!empty($search_keyword)) {
  $paginator_total_rows->bindValue(":keyword", "%".$search_keyword."%", PDO::PARAM_STR);
}
$paginator_total_rows->execute();
$paginator_total_rows = $paginator_total_rows->fetch(PDO::FETCH_LAZY)->count;

$news_list_query = $pdo->prepare("
  SELECT *,
    (SELECT coalesce(nick,login) FROM ".$prefix."_users WHERE id=t_news.author) as author_nick,
    (SELECT name FROM ".$prefix."_users WHERE id=t_news.author) as author_name,
    (SELECT count(id) FROM ".$prefix."_comments WHERE news_id=t_news.id) as comments_count
  FROM ".$prefix."_news t_news
  WHERE enabled is TRUE ".$is_search_active."
  ORDER BY cdate DESC, id DESC
  LIMIT ".$paginator_calc_page.",".$paginator_page_limit.";
");

if(!empty($search_keyword)) {
  $news_list_query->bindValue(":keyword", "%".$search_keyword."%", PDO::PARAM_STR);
}

$news_list_query->execute();

$news_list = $news_list_query->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="news-list">
  <main class="news-list__left">
    <section class="block">
      <h2 class="block__title">Новости</h2>
      <? if(!empty($news_list)): ?>
        <? if (!empty($search_keyword)): ?>
          <p>Поиск по: <b><?= $search_keyword ?></b> <a href="news" title="Очистить поиск">✗</a></p>
        <? endif; ?>
        <div class="news">
          <? foreach ($news_list as $rows): ?>
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
          <!-- <div class="news__more"><a href="#" class="btn btn_primary">Загрузить еще</a></div> -->
        </div>
        <? if (ceil($paginator_total_rows / $paginator_page_limit) > 1): ?>
          <div class="pagination">
            <?php
              $href_page = "?page=";
              if(!empty($search_keyword)) {
                $href_page = "?search=".$search_keyword."&page=";
              }
            ?>
            <? if ($paginator_current_page > 1): ?>
              <a href="<?= $href_page ?><?= $paginator_current_page-1 ?>" class="pagination__item">&lt;</a>
            <? endif; ?>

            <? if ($paginator_current_page > 3): ?>
              <a href="<?= $href_page ?>1" class="pagination__item">1</a>
              <span class="pagination__item">...</span>
            <? endif; ?>

            <? if ($paginator_current_page-2 > 0): ?><a href="<?= $href_page ?><?= $paginator_current_page-2 ?>" class="pagination__item"><?= $paginator_current_page-2 ?></a><?php endif; ?>
            <? if ($paginator_current_page-1 > 0): ?><a href="<?= $href_page ?><?= $paginator_current_page-1 ?>" class="pagination__item"><?= $paginator_current_page-1 ?></a><?php endif; ?>

            <a href="<?= $href_page ?><?= $paginator_current_page ?>" class="pagination__item active"><?= $paginator_current_page ?></a>

            <? if ($paginator_current_page+1 < ceil($paginator_total_rows / $paginator_page_limit)+1): ?><a href="<?= $href_page ?><?= $paginator_current_page+1 ?>" class="pagination__item"><?= $paginator_current_page+1 ?></a><?php endif; ?>
            <? if ($paginator_current_page+2 < ceil($paginator_total_rows / $paginator_page_limit)+1): ?><a href="<?= $href_page ?><?= $paginator_current_page+2 ?>" class="pagination__item"><?= $paginator_current_page+2 ?></a><?php endif; ?>

            <? if ($paginator_current_page < ceil($paginator_total_rows / $paginator_page_limit)-2): ?>
              <span class="pagination__item">...</span>
              <a href="<?= $href_page ?><?= ceil($paginator_total_rows / $paginator_page_limit) ?>" class="pagination__item"><?= ceil($paginator_total_rows / $paginator_page_limit) ?></a>
            <? endif; ?>

            <? if ($paginator_current_page < ceil($paginator_total_rows / $paginator_page_limit)): ?>
              <a href="<?= $href_page ?><?= $paginator_current_page+1 ?>" class="pagination__item">&gt;</a>
            <? endif; ?>
          </div>
        <? endif; ?>
      <? else: ?>
        <div class="well">
          <? if (!empty($search_keyword)): ?>
            <p>По вашему запросу <b><?= $search_keyword ?></b> ничего не найдено.</p>
            <a href="news" class="btn btn_primary">Вернуться назад</a>
          <? else: ?>
            <p>Список пуст.</p>
            <? if ($is_user_admin || $is_user_moder): ?><a href="profile?section=news&add" class="btn btn_primary">Опубликовать</a><? endif; ?>
          <? endif; ?>
        </div>
      <? endif; ?>
    </section>
  </main>
  <aside class="news-list__right">
    <?php
      $hot_news_query = $pdo->query("
        SELECT url, title, cdate FROM ".$prefix."_news t_news
        WHERE (SELECT COUNT(id) FROM ".$prefix."_comments WHERE news_id=t_news.id) > 0
        ORDER BY (SELECT COUNT(id) FROM ".$prefix."_comments WHERE news_id=t_news.id) DESC, cdate DESC
        LIMIT 3;
      ");
      $hot_news = $hot_news_query->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <? if(!empty($hot_news)): ?>
      <section class="block block_mb appear-right">
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
      <section class="block block_mb appear-right <?if(!empty($hot_news)):?>anim-delay-1<?endif;?>">
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
    <section class="block block_mb appear-right <?if(!empty($last_comments)):?>anim-delay-2<?endif;?>">
      <h2 class="block__title">Поиск</h2>
      <div class="widget-search">
        <form class="form" method="get"><input type="search" name="search" maxlength="64" placeholder="Поиск по ключевым словам" value="<?= $search_keyword ?>"></form>
        <? if (!empty($search_keyword)): ?>
          <a href="news" class="btn"><?= getSvg("img/icons/close.svg") ?></a>
        <? else: ?>
          <button class="btn"><?= getSvg("img/icons/search.svg") ?></button>
        <? endif; ?>
      </div>
    </section>
  </aside>
</div>