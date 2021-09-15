<? require_once("partials/header.php"); ?>

<?php

// CORE
$ban_length = getBanLenght();

// PAGINATION
$paginator_page_limit = 30;

$paginator_current_page = isset($_GET["page"]) && is_numeric($_GET["page"]) ? $_GET["page"] : 1;

$paginator_calc_page = ($paginator_current_page - 1) * $paginator_page_limit;

$search_keyword = "";
$is_search_active = "";
if(!empty($_GET["search"])) {
  $search_keyword = filter_var(trim($_GET["search"]), FILTER_SANITIZE_STRING);
  $is_search_active = "WHERE nick LIKE :keyword OR ip LIKE :keyword OR steam_id LIKE :keyword OR admin_nick LIKE :keyword OR reason LIKE :keyword";
}

$paginator_total_rows = $pdo->prepare("
  SELECT COUNT(id) as count FROM ".$prefix."_bans
  ".$is_search_active.";
");
if(!empty($search_keyword)) {
  $paginator_total_rows->bindValue(":keyword", "%".$search_keyword."%", PDO::PARAM_STR);
}
$paginator_total_rows->execute();
$paginator_total_rows = $paginator_total_rows->fetch(PDO::FETCH_LAZY)->count;

$db_bans = $pdo->prepare("
  SELECT *,
    (SELECT count(steam_id) FROM ".$prefix."_bans WHERE steam_id=t_current.steam_id) as bans_count
  FROM ".$prefix."_bans t_current
  ".$is_search_active."
  ORDER BY created DESC
  LIMIT ".$paginator_calc_page.",".$paginator_page_limit.";
");

if(!empty($search_keyword)) {
  $db_bans->bindValue(":keyword", "%".$search_keyword."%", PDO::PARAM_STR);
}

$db_bans->execute();

$db_bans = $db_bans->fetchAll(PDO::FETCH_ASSOC);

// STATS
if (!empty($db_bans) && empty($search_keyword) && empty($_GET["page"])) {
  $bans_top_nicks = $pdo->query("
    SELECT
      COUNT(id) as bans_count, nick
    FROM ".$prefix."_bans
    GROUP BY nick
    ORDER BY bans_count DESC
    LIMIT 10
  ")->fetchAll(PDO::FETCH_ASSOC);

  $bans_top_reasons = $pdo->query("
    SELECT
      COUNT(id) as bans_count, reason
    FROM ".$prefix."_bans
    GROUP BY reason
    ORDER BY bans_count DESC
    LIMIT 5
  ")->fetchAll(PDO::FETCH_ASSOC);

  $bans_per_days = $pdo->query("
    SELECT 
      (
        SELECT COUNT(id)
        FROM ".$prefix."_bans
        WHERE FROM_UNIXTIME(created) >= date_sub(NOW(), INTERVAL 1 DAY)
      ) as today,
      (
        SELECT COUNT(id)
        FROM ".$prefix."_bans
        WHERE FROM_UNIXTIME(created) >= date_sub(NOW(), INTERVAL 1 MONTH)
      ) as month
  ")->fetch(PDO::FETCH_ASSOC);

  /*$bans_per_months = $pdo->query("
    SELECT * FROM (
      SELECT
        COUNT(id) as bans_count, EXTRACT(month FROM FROM_UNIXTIME(created)) as month, EXTRACT(year FROM FROM_UNIXTIME(created)) as year
      FROM ".$prefix."_bans
      GROUP BY month, year
      ORDER BY year DESC, month DESC
      LIMIT 5
    ) t1 ORDER BY t1.year, t1.month
  ")->fetchAll(PDO::FETCH_ASSOC);*/
  $bans_per_months = $pdo->query("
    SELECT coalesce(bans_count, 0) as bans_count, t2.year, t2.month FROM (
      SELECT
        EXTRACT(year FROM FROM_UNIXTIME(created)) as year, EXTRACT(month FROM FROM_UNIXTIME(created)) as month, count(*) as bans_count
      FROM ".$prefix."_bans
      GROUP BY year, month
    ) t1
    RIGHT JOIN (
      SELECT EXTRACT(year FROM tt.Date) as year, EXTRACT(month FROM tt.Date) as month
        FROM (
            SELECT curdate() - INTERVAL (a.a + (10 * b.a) + (100 * c.a) + (1000 * d.a) ) DAY as Date
            FROM (SELECT 0 as a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) as a
            CROSS JOIN (SELECT 0 as a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) as b
            CROSS JOIN (SELECT 0 as a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) as c
            CROSS JOIN (SELECT 0 as a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) as d
        ) tt
        WHERE tt.Date between date_sub(NOW(), INTERVAL 4 month) and now()
        GROUP BY year, month
        ORDER BY year, month
    ) t2 ON t2.month=t1.month AND t1.year=t2.year
    WHERE t2.month IS NOT NULL
    ORDER BY t2.year, t2.month
    LIMIT 5
  ")->fetchAll(PDO::FETCH_ASSOC);
} else {
  $bans_top_nicks = $bans_top_reasons = $bans_per_days = $bans_per_months = array();
}

?>

<? if (!empty($db_bans) && empty($search_keyword) && empty($_GET["page"])): ?>
  <section class="block block_mb">
    <div class="bans-stats">
      <div class="bans-stats__col">
        <h2 class="block__title">Топ-10 забаненых</h2>
        <div class="well">
          <table class="table-widget-top">
            <thead>
              <tr>
                <th>#</th>
                <th>Ник</th>
                <th>Кол-во банов</th>
              </tr>
            </thead>
            <tbody>
              <? $top_index = 0; ?>
              <? foreach($bans_top_nicks as $row): ?>
                <tr>
                  <td><?= ++$top_index; ?></td>
                  <td><?= $row["nick"] ?></td>
                  <td><?= $row["bans_count"] ?></td>
                </tr>
              <? endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <div class="bans-stats__col">
        <h2 class="block__title">Топ-5 причин</h2>
        <div class="well"><canvas id="chart-bans-reasons"></canvas></div>
      </div>
      <div class="bans-stats__col">
        <h2 class="block__title">Баны по времени</h2>
        <div class="well">
          <p>За сутки: <b><?= $bans_per_days["today"] ?></b>
          <br>За месяц: <b><?= $bans_per_days["month"] ?></b>
          <br>За всё время: <b><?= $paginator_total_rows ?></b></p>
          <canvas id="chart-bans-per-month"></canvas>
        </div>
      </div>
    </div>
  </section>
<? endif; ?>

<section class="block">
  <h2>История забаненых игроков</h2>
  <? if (!empty($db_bans)): ?>
    <div class="table-top">
      <? if (!empty($search_keyword)): ?>
        <p>Поиск по: <b><?= $search_keyword ?></b></p>
      <? else: ?>
        <p>Всего: <b><?= $paginator_total_rows ?></b></p>
      <? endif; ?>
      <div class="table-top__right">
        <div class="table-search <? if (!empty($search_keyword)): ?>active<? endif; ?>">
          <form method="get"><input type="search" name="search" maxlength="64" placeholder="Ник, Steam ID, IP, причина" value="<?= $search_keyword ?>"></form>
          <? if (!empty($search_keyword)): ?>
            <a href="bans" class="btn btn_primary"><?= getSvg("img/icons/close.svg") ?></a>
          <? else: ?>
            <button class="btn btn_primary"><?= getSvg("img/icons/search.svg") ?></button>
          <? endif; ?>
        </div>
      </div>
    </div>
    <div class="table-responsive">
      <table class="table table-bans">
        <thead>
          <tr>
            <th title="Сколько раз был в бане" class="table__info"><?= getSvg("img/icons/info.svg") ?></th>
            <th>Ник</th>
            <th>Причина</th>
            <th>Выдан</th>
            <th>Срок</th>
            <th>Осталось</th>
            <th>Админ</th>
          </tr>
        </thead>
        <tbody>
          <? foreach($db_bans as $row): ?>
            <tr
              <?php
                if ($row["unbanned"]) {
                  echo 'class="table__row_green"';
                } else if ($row["length"] == 0) {
                  echo 'class="table__row_red"';
                } else if (time() > $row["created"] + $row["length"] * 60) {
                  echo 'class="table__row_green"';
                }
              ?>
            >
              <td title="Сколько раз был в бане"><?= $row["bans_count"] ?></td>
              <td>
                <? if($is_user_admin): ?>
                  <a href="http://check-host.net/ip-info?host=<?= $row["ip"] ?>" target="_blank" rel="noreferrer"><?= getSvg("img/icons/map-marker.svg") ?></a>
                <? endif; ?>
                <span data-copy="<?= $row["steam_id"] ?>" data-copy-toast="Steam ID игрока скопировано" title="Нажми чтобы скопировать его <?= $row["steam_id"] ?>"><?= $row["nick"] ?></span>
              </td>
              <td><?= $row["reason"] ?></td>
              <td><?= dateWhen($row["created"]) ?></td>
              <td><?= $ban_length[$row["length"]] ?></td>
              <td>
                <?php
                  if ($row["unbanned"]) {
                    echo 'Разбанен досрочно';
                  } else {
                    echo getBanLeft($row["length"], $row["created"]);
                  }
                ?>
              </td>
              <td><?= $row["admin_nick"] ?></td>
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
        <a href="bans" class="btn btn_primary">Вернуться назад</a>
      <? else: ?>
        <p>Список пуст.</p>
      <? endif; ?>
    </div>
  <? endif; ?>
</section>

<? require_once("partials/footer.php"); ?>

<? if (!empty($db_bans) && empty($search_keyword) && empty($_GET["page"])): ?>
  <script>
  const bans_reasons_labels = [
  <? foreach($bans_top_reasons as $row): ?>
    '<?=$row["reason"]?>: <?=$row["bans_count"]?>',
  <? endforeach; ?>
  ];
  const bans_reasons_data = [
  <? foreach($bans_top_reasons as $row): ?>
    <?=$row["bans_count"]?>,
  <? endforeach; ?>
  ];
  let bans_reasons = new Chart(
    $("#chart-bans-reasons"),
    {
      type: 'pie',
      data: {
        labels: bans_reasons_labels,
        datasets: [{
          label: 'Топ бан-причиин',
          data: bans_reasons_data,
          fill: false,
          borderColor: '#fff',
          backgroundColor: [
            '#ff6384',
            '#36a2eb',
            '#ffcd56',
            '#fe3f99',
            '#00b96c'
          ],
          hoverOffset: 3
        }]
      },
      options: {
        plugins: {
          legend: {
            position: 'bottom',
            align: 'start',
            onHover: pieHandleHover,
            onLeave: pieHandleLeave
          },
          tooltip: {
             callbacks: {
                label: function(context) {
                  var label = context.label;
                  var dataset = context.dataset;
                  var total = dataset.data.reduce(function(previousValue, currentValue, currentIndex, array) {
                    return previousValue + currentValue;
                  });
                  var currentValue = dataset.data[context.dataIndex];
                  var percentage = Math.floor(((currentValue/total) * 100)+0.5);
                  return label + " (" + percentage + "%)";
                }
             }
          }
        }
      }
    }
  );
  function pieHandleHover(evt, item, legend) {
    legend.chart.data.datasets[0].backgroundColor.forEach((color, index, colors) => {
      colors[index] = index === item.index || color.length === 9 ? color : color + '4D';
    });
    legend.chart.update();
  }
  function pieHandleLeave(evt, item, legend) {
    legend.chart.data.datasets[0].backgroundColor.forEach((color, index, colors) => {
      colors[index] = color.length === 9 ? color.slice(0, -2) : color;
    });
    legend.chart.update();
  }
  </script>
  <script>
  const bans_per_month_labels = [
  <? foreach($bans_per_months as $row): ?>
    '<?=getMonthName($row["month"])?>',
  <? endforeach; ?>
  ];
  const bans_per_month_data = [
  <? foreach($bans_per_months as $row): ?>
    <?=$row["bans_count"]?>,
  <? endforeach; ?>
  ];
  var bans_per_month = new Chart(
    $("#chart-bans-per-month"),
    {
      type: 'line',
      data: {
        labels: bans_per_month_labels,
        datasets: [{
          label: 'Количество забаненых',
          data: bans_per_month_data,
          fill: false,
          borderColor: '#fe3f99',
          backgroundColor: '#313946',
          tension: 0.3
        }]
      },
      options: {
        elements: {
          point: {
            radius: 5
          }
        },
        plugins: {
          legend: {
            display: false
          }
        }
      }
    }
  );
  </script>
<? endif; ?>