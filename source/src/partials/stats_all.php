<?php

// PAGINATION
$paginator_page_limit = 30;

$paginator_current_page = isset($_GET["page"]) && is_numeric($_GET["page"]) ? $_GET["page"] : 1;

$paginator_calc_page = ($paginator_current_page - 1) * $paginator_page_limit;

$search_keyword = "";
$is_search_active = "";
if(!empty($_GET["search"])) {
  $search_keyword = filter_var(trim($_GET["search"]), FILTER_SANITIZE_STRING);
  $is_search_active = "WHERE nick LIKE :keyword OR uniq LIKE :keyword";
}

$paginator_total_rows = $pdo->prepare("
  SELECT COUNT(id) as count FROM ".$prefix."_stats
  ".$is_search_active.";
");
if(!empty($search_keyword)) {
  $paginator_total_rows->bindValue(":keyword", "%".$search_keyword."%", PDO::PARAM_STR);
}
$paginator_total_rows->execute();
$paginator_total_rows = $paginator_total_rows->fetch(PDO::FETCH_LAZY)->count;

$db_stats_query = $pdo->prepare("
  SELECT * FROM ".$prefix."_stats
  ".$is_search_active."
  ORDER BY `rank` ASC
  LIMIT ".$paginator_calc_page.",".$paginator_page_limit.";
");

if(!empty($search_keyword)) {
  $db_stats_query->bindValue(":keyword", "%".$search_keyword."%", PDO::PARAM_STR);
}

$db_stats_query->execute();

$db_stats = $db_stats_query->fetchAll(PDO::FETCH_ASSOC);

?>

<section class="block">
  <h2>Статистика игроков</h2>
  <? if (!empty($db_stats)): ?>
    <div class="table-top">
      <? if (!empty($search_keyword)): ?>
        <p>Поиск по <b><?= $search_keyword ?></b></p>
      <? else: ?>
        <p>Всего: <b><?= $paginator_total_rows ?></b></p>
      <? endif; ?>
      <div class="table-top__right">
        <div class="table-search <? if (!empty($search_keyword)): ?>active<? endif; ?>">
          <form method="get"><input type="search" name="search" maxlength="64" placeholder="Ник или Steam ID" value="<?= $search_keyword ?>"></form>
          <? if (!empty($search_keyword)): ?>
            <a href="stats" class="btn btn_primary"><?= getSvg("img/icons/close.svg") ?></a>
          <? else: ?>
            <button class="btn btn_primary"><?= getSvg("img/icons/search.svg") ?></button>
          <? endif; ?>
        </div>
      </div>
    </div>
    <div class="table-responsive">
      <table class="table table-stats">
        <thead>
          <tr>
            <th>#</th>
            <th>Ник</th>
            <th>Убил</th>
            <th>Умер</th>
            <th>K/D</th>
            <th>Убийств в голову</th>
            <th>Скилл</th>
          </tr>
        </thead>
        <tbody>
          <? foreach($db_stats as $row): ?>
            <?php
              // formulas
              $kill_death_ratio = round($row["kills"] / ($row["deaths"] == 0 ? 1 : $row["deaths"]), 2);
              $headshot_ratio = round(100 * $row["headshots"] / ($row["kills"] == 0 ? 1 : $row["kills"]), 2);
            ?>
            <tr>
              <td><?= $row["rank"] ?></td>
              <?php
                $player_nick = htmlspecialchars(trim($row["nick"]));
                if(!empty(getPlayerAvatar($row["nick"]))) {
                  $player_avatar = '<img src="'.getPlayerAvatar($row["nick"]).'" alt="'.getPlayerPosition($row["nick"]).'" title="'.getPlayerPosition($row["nick"]).'" class="avatar">';
                } else {
                  $player_avatar = "";
                }
              ?>
              <td class="top-<?= $row["rank"] ?>"><a href="stats?player=<?= $row["id"] ?>" title="Открыть подробную статистику <?= $player_nick ?>"><?= $player_nick.$player_avatar ?></a></td>
              <td><?= $row["kills"] ?></td>
              <td><?= $row["deaths"] ?></td>
              <td><?= $kill_death_ratio ?></td>
              <td><?= $headshot_ratio ?>%</td>
              <td><?= printSkillLabel($row["kills"], $row["deaths"]) ?></td>
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
        <a href="stats" class="btn btn_primary">Вернуться назад</a>
      <? else: ?>
        <p>Список пуст.</p>
      <? endif; ?>
    </div>
  <? endif; ?>
</section>