<? require_once("partials/header.php"); ?>

<?php

// PAGINATION
$paginator_page_limit = 30;

$paginator_current_page = isset($_GET["page"]) && is_numeric($_GET["page"]) ? $_GET["page"] : 1;

$paginator_calc_page = ($paginator_current_page - 1) * $paginator_page_limit;

$search_keyword = "";
$is_search_active = "";
if(!empty($_GET["search"])) {
  $search_keyword = filter_var(trim($_GET["search"]), FILTER_SANITIZE_STRING);
  $is_search_active = "AND name LIKE :keyword OR nick LIKE :keyword OR login LIKE :keyword";
}

$paginator_total_rows = $pdo->prepare("
  SELECT COUNT(id) as count FROM ".$prefix."_users WHERE service_id is not null
  ".$is_search_active.";
");
if(!empty($search_keyword)) {
  $paginator_total_rows->bindValue(":keyword", "%".$search_keyword."%", PDO::PARAM_STR);
}
$paginator_total_rows->execute();
$paginator_total_rows = $paginator_total_rows->fetch(PDO::FETCH_LAZY)->count;

$db_admins_list = $pdo->prepare("
  SELECT
    coalesce(nick,login) as nick,
    coalesce(name,nick,login) as name,
    (SELECT name FROM ".$prefix."_services WHERE id=t_users.service_id) as service_name,
    service_start, service_end, service_nolimit
  FROM ".$prefix."_users t_users
  WHERE service_id is not null ".$is_search_active."
  ORDER BY service_end < NOW(), service_id ASC, service_nolimit=0, service_end DESC, service_start ASC
  LIMIT ".$paginator_calc_page.",".$paginator_page_limit.";
");

if(!empty($search_keyword)) {
  $db_admins_list->bindValue(":keyword", "%".$search_keyword."%", PDO::PARAM_STR);
}

$db_admins_list->execute();

$db_admins_list = $db_admins_list->fetchAll(PDO::FETCH_ASSOC);

?>

<section class="block">
  <h2>Список привилегированных игроков</h2>
  <? if (!empty($db_admins_list)): ?>
    <div class="table-top">
      <? if (!empty($search_keyword)): ?>
        <p>Поиск по: <b><?= $search_keyword ?></b></p>
      <? else: ?>
        <p>Всего: <b><?= $paginator_total_rows ?></b></p>
      <? endif; ?>
      <div class="table-top__right">
        <div class="table-search <? if (!empty($search_keyword)): ?>active<? endif; ?>">
          <form method="get"><input type="search" name="search" maxlength="64" placeholder="Ник, имя или логин" value="<?= $search_keyword ?>"></form>
          <? if (!empty($search_keyword)): ?>
            <a href="admins" class="btn btn_primary"><?= getSvg("img/icons/close.svg") ?></a>
          <? else: ?>
            <button class="btn btn_primary"><?= getSvg("img/icons/search.svg") ?></button>
          <? endif; ?>
        </div>
        <a href="buy" class="btn btn_cta">Купить привилегию</a>
      </div>
    </div>
    <div class="table-responsive">
      <table class="table table-bans">
        <thead>
          <tr>
            <th>#</th>
            <th>Ник</th>
            <th>Привилегия</th>
            <th>Дата покупки</th>
            <th>Осталось</th>
          </tr>
        </thead>
        <tbody>
          <?php
            if ($paginator_current_page > 1) {
              $adm_index = $paginator_calc_page;
            } else {
              $adm_index = 0;
            }
          ?>
          <? foreach($db_admins_list as $rows): ?>
            <?php
              $service_status = '';
              if ($rows["service_nolimit"]) {
                $service_end = "∞";
              } else {
                $service_end = dateDiff(time(), strtotime($rows["service_end"]));
                if (time() > strtotime($rows["service_end"])) {
                  $service_status = 'class="table__row_red"';
                }
              }
            ?>
            <tr <?= $service_status ?>>
              <td><?= ++$adm_index ?></td>
              <td title="<?= $rows["name"] ?>"><?= $rows["nick"] ?></td>
              <td><?= $rows["service_name"] ?></td>
              <td><?= dateWhen(strtotime($rows["service_start"])) ?></td>
              <td><?= $service_end ?></td>
            </tr>
          <? endforeach; ?>
        </tbody>
      </table>
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
    <div class="well appear-bottom">
      <? if (!empty($search_keyword)): ?>
        <p>По вашему запросу <b><?= $search_keyword ?></b> ничего не найдено.</p>
        <a href="admins" class="btn btn_primary">Вернуться назад</a>
      <? else: ?>
        <p>Привилегированных игроков еще нет. Будь первым! ;)</p>
        <a href="/buy" class="btn btn_cta">Купить привилегию</a>
      <? endif; ?>
    </div>
  <? endif; ?>
</section>

<? require_once("partials/footer.php"); ?>