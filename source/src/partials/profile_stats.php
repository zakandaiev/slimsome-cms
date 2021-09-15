<? require_once("partials/footer_scripts.php"); ?>

<?php

$general_earns = $pdo->query("
  SELECT 
    IFNULL((
      SELECT SUM(price)
      FROM ".$prefix."_payments
      WHERE status=1 and currency=1 and cdate >= date_sub(NOW(), INTERVAL 1 MONTH)
    ), 0) as month_rub,
    IFNULL((
      SELECT SUM(price)
      FROM ".$prefix."_payments
      WHERE status=1 and currency=1 and cdate >= date_sub(NOW(), INTERVAL 6 MONTH)
    ), 0) as half_year_rub,
    IFNULL((
      SELECT SUM(price)
      FROM ".$prefix."_payments
      WHERE status=1 and currency=1
    ), 0) as all_time_rub,
    IFNULL((
      SELECT SUM(price)
      FROM ".$prefix."_payments
      WHERE status=1 and currency=2 and cdate >= date_sub(NOW(), INTERVAL 1 MONTH)
    ), 0) as month_uah,
    IFNULL((
      SELECT SUM(price)
      FROM ".$prefix."_payments
      WHERE status=1 and currency=2 and cdate >= date_sub(NOW(), INTERVAL 6 MONTH)
    ), 0) as half_year_uah,
    IFNULL((
      SELECT SUM(price)
      FROM ".$prefix."_payments
      WHERE status=1 and currency=2
    ), 0) as all_time_uah
")->fetch(PDO::FETCH_ASSOC);

/*$earns_per_months_rub = $pdo->query("
  SELECT * FROM (
    SELECT
      SUM(price) as rub_sum, EXTRACT(month FROM cdate) as month, EXTRACT(year FROM cdate) as year
    FROM ".$prefix."_payments
    WHERE status=1 and currency=1
    GROUP BY month, year
    ORDER BY year DESC, month DESC
    LIMIT 6
  ) t1 ORDER BY t1.year, t1.month
")->fetchAll(PDO::FETCH_ASSOC);*/
$earns_per_months_rub = $pdo->query("
  SELECT coalesce(rub_sum, 0) as rub_sum, t2.year, t2.month FROM (
    SELECT
      EXTRACT(month FROM cdate) as month, EXTRACT(year FROM cdate) as year, SUM(price) as rub_sum
    FROM ".$prefix."_payments
    WHERE status=1 and currency=1
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
      WHERE tt.Date between coalesce((SELECT cdate FROM awesome_payments WHERE status=1 and currency=1 ORDER BY cdate ASC LIMIT 1), (SELECT cdate FROM awesome_payments WHERE status=1 and currency=2 ORDER BY cdate ASC LIMIT 1)) and NOW()
      ORDER BY year, month
  ) t2 ON t2.month=t1.month AND t1.year=t2.year
  WHERE t2.month IS NOT NULL
  ORDER BY t2.year, t2.month
")->fetchAll(PDO::FETCH_ASSOC);

/*$earns_per_months_uah = $pdo->query("
  SELECT * FROM (
    SELECT
      SUM(price) as uah_sum, EXTRACT(month FROM cdate) as month, EXTRACT(year FROM cdate) as year
    FROM ".$prefix."_payments
    WHERE status=1 and currency=2
    GROUP BY month, year
    ORDER BY year DESC, month DESC
    LIMIT 6
  ) t1 ORDER BY t1.year, t1.month
")->fetchAll(PDO::FETCH_ASSOC);*/
$earns_per_months_uah = $pdo->query("
  SELECT coalesce(uah_sum, 0) as uah_sum, t2.year, t2.month FROM (
    SELECT
      EXTRACT(month FROM cdate) as month, EXTRACT(year FROM cdate) as year, SUM(price) as uah_sum
    FROM ".$prefix."_payments
    WHERE status=1 and currency=2
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
      WHERE tt.Date between coalesce((SELECT cdate FROM awesome_payments WHERE status=1 and currency=1 ORDER BY cdate ASC LIMIT 1), (SELECT cdate FROM awesome_payments WHERE status=1 and currency=2 ORDER BY cdate ASC LIMIT 1)) and NOW()
      GROUP BY year, month
      ORDER BY year, month
  ) t2 ON t2.month=t1.month AND t1.year=t2.year
  WHERE t2.month IS NOT NULL
  ORDER BY t2.year, t2.month
")->fetchAll(PDO::FETCH_ASSOC);

$payments_status = $pdo->query("
  SELECT 
    (
      SELECT COUNT(id)
      FROM ".$prefix."_payments
      WHERE status=1
    ) as successfull,
    (
      SELECT COUNT(id)
      FROM ".$prefix."_payments
      WHERE status=0
    ) as cancelled
")->fetch(PDO::FETCH_ASSOC);

$top_services = $pdo->query("
  SELECT SUM(price) as total,
  service_name,
  (SELECT CONCAT('#', LPAD(CONV(ROUND(RAND()*16777215),10,16),6,0))) as color
  FROM ".$prefix."_payments
  WHERE status=1
  GROUP BY service_name
  ORDER BY service_name ASC
")->fetchAll(PDO::FETCH_ASSOC);

?>

<section class="block block_mb">
  <h2 class="block__title">Заработок</h2>
  <div class="earns-stats appear-bottom">
    <div class="earns-stats__col">
      <h3 class="earns-stats__title">За месяц</h3>
      <div class="earns-stats__value">
        <?= $general_earns["month_rub"] ?> руб.
        <br>
        <?= $general_earns["month_uah"] ?> грн.
      </div>
    </div>
    <div class="earns-stats__col">
      <h3 class="earns-stats__title">За год</h3>
      <div class="earns-stats__value">
        <?= $general_earns["half_year_rub"] ?> руб.
        <br>
        <?= $general_earns["half_year_uah"] ?> грн.
      </div>
    </div>
    <div class="earns-stats__col">
      <h3 class="earns-stats__title">За все время</h3>
      <div class="earns-stats__value">
        <?= $general_earns["all_time_rub"] ?> руб.
        <br>
        <?= $general_earns["all_time_uah"] ?> грн.
      </div>
    </div>
  </div>
</section>

<section class="block block_mb appear-bottom anim-delay-1">
  <div class="well">
    <canvas id="chart-earns-by-month"></canvas>
  </div>
</section>

<section class="block block_mb appear-bottom anim-delay-1">
  <div class="earns-graphs">
    <div class="earns-graphs__col">
      <div class="well">
        <canvas id="chart-payments-status"></canvas>
      </div>
    </div>
    <div class="earns-graphs__col">
      <div class="well">
        <canvas id="chart-top-services"></canvas>
      </div>
    </div>
  </div>
</section>

<script>
const chart_earns_by_month_labels = [
<? foreach($earns_per_months_uah as $row): ?>
  '<?=getMonthName($row["month"]).' '.$row["year"]?>',
<? endforeach; ?>
];
const chart_earns_by_month_data_rub = [
<? foreach($earns_per_months_rub as $row): ?>
  <?=$row["rub_sum"]?>,
<? endforeach; ?>
];
const chart_earns_by_month_data_uah = [
<? foreach($earns_per_months_uah as $row): ?>
  <?=$row["uah_sum"]?>,
<? endforeach; ?>
];
var chart_earns_by_month = new Chart(
  $("#chart-earns-by-month"),
  {
    type: 'line',
    data: {
      labels: chart_earns_by_month_labels,
      datasets: [
        {
          label: 'Продажи в руб.',
          data: chart_earns_by_month_data_rub,
          fill: false,
          borderColor: '#ff6384',
          backgroundColor: '#ffb1c1',
          tension: 0.3
        },
        {
          label: 'Продажи в грн.',
          data: chart_earns_by_month_data_uah,
          fill: false,
          borderColor: '#36a2eb',
          backgroundColor: '#9ad0f5',
          tension: 0.3
        }
      ]
    },
    options: {
      elements: {
        point: {
          radius: 5
        }
      }
    }
  }
);
</script>

<script>
const payments_status_labels = ['Оплаченых платежей: <?=$payments_status["successfull"]?>', 'Неоплаченых платежей: <?=$payments_status["cancelled"]?>'];
const payments_status_data = [<?= $payments_status["successfull"] ?>, <?= $payments_status["cancelled"] ?>];
let payments_status = new Chart(
  $("#chart-payments-status"),
  {
    type: 'pie',
    data: {
      labels: payments_status_labels,
      datasets: [{
        data: payments_status_data,
        fill: false,
        borderColor: '#fff',
        backgroundColor: [
          '#ff6384',
          '#36a2eb'
        ],
        hoverOffset: 3
      }]
    },
    options: {
      plugins: {
        title: {
          display: true,
          text: 'Конверсия платежей'
        },
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
const top_services_labels = [
<? foreach($top_services as $row): ?>
  '<?=$row["service_name"]?>',
<? endforeach; ?>
];
const top_services_colors = [
<? foreach($top_services as $row): ?>
  '<?=$row["color"]?>',
<? endforeach; ?>
];
const top_services_data = [
<? foreach($top_services as $row): ?>
  <?=$row["total"]?>,
<? endforeach; ?>
];
let top_services = new Chart(
  $("#chart-top-services"),
  {
    type: 'pie',
    data: {
      labels: top_services_labels,
      datasets: [{
        data: top_services_data,
        fill: false,
        borderColor: '#fff',
        backgroundColor: top_services_colors,
        hoverOffset: 3
      }]
    },
    options: {
      plugins: {
        title: {
          display: true,
          text: 'Доходность привилегий'
        },
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
                return label + ": " + percentage + "%";
              }
           }
        }
      }
    }
  }
);
</script>

<?php

$users_registrations = $pdo->query("
  SELECT 
    IFNULL((
      SELECT COUNT(id)
      FROM ".$prefix."_users
      WHERE cdate >= date_sub(NOW(), INTERVAL 1 DAY)
    ), 0) as today,
    IFNULL((
      SELECT COUNT(id)
      FROM ".$prefix."_users
      WHERE cdate >= date_sub(NOW(), INTERVAL 1 MONTH)
    ), 0) as month,
    IFNULL((
      SELECT COUNT(id)
      FROM ".$prefix."_users
    ), 0) as all_time
")->fetch(PDO::FETCH_ASSOC);

$users_with_services = $pdo->query("
  SELECT 
    IFNULL((
      SELECT COUNT(id)
      FROM ".$prefix."_users t_users
      WHERE
        service_id is not NULL
      AND
        (service_nolimit IS TRUE OR CURRENT_TIMESTAMP < service_end)
      AND
        (SELECT enabled FROM ".$prefix."_services WHERE id=t_users.service_id AND enabled IS TRUE)
    ), 0) as active,
    IFNULL((
      SELECT COUNT(id)
      FROM ".$prefix."_users
      WHERE
        service_id is not NULL
      AND
        (service_nolimit IS FALSE AND CURRENT_TIMESTAMP > service_end)
    ), 0) as expired
")->fetch(PDO::FETCH_ASSOC);

$chat_messages = $pdo->query("
  SELECT 
    IFNULL((
      SELECT COUNT(id)
      FROM ".$prefix."_chat
      WHERE cdate >= date_sub(NOW(), INTERVAL 1 DAY)
    ), 0) as today,
    IFNULL((
      SELECT COUNT(id)
      FROM ".$prefix."_chat
      WHERE cdate >= date_sub(NOW(), INTERVAL 1 MONTH)
    ), 0) as month,
    IFNULL((
      SELECT COUNT(id)
      FROM ".$prefix."_chat
    ), 0) as all_time
")->fetch(PDO::FETCH_ASSOC);

?>

<section class="block block_mb appear-bottom anim-delay-2">
  <h2 class="block__title">Пользователи</h2>
  <div class="users-stats">
    <div class="users-stats__col">
      <h3 class="users-stats__title">Регистрации</h3>
      <div class="well">
        <p>
          За сутки: <b><?= $users_registrations["today"] ?></b>
          <br>
          За месяц: <b><?= $users_registrations["month"] ?></b>
          <br>
          За всё время: <b><?= $users_registrations["all_time"] ?></b>
        </p>
      </div>
    </div>
    <div class="users-stats__col">
      <h3 class="users-stats__title">Привилегии</h3>
      <div class="well">
        <p>
          Привилегированых: <b><?= $users_with_services["active"] ?></b>
          <br>
          Не продливших: <b><?= $users_with_services["expired"] ?></b>
          <br>
          Суммарно: <b><?= $users_with_services["active"] + $users_with_services["expired"] ?></b>
        </p>
      </div>
    </div>
    <div class="users-stats__col">
      <h3 class="users-stats__title">Сообщений в чате</h3>
      <div class="well">
        <p>
          За сутки: <b><?= $chat_messages["today"] ?></b>
          <br>
          За месяц: <b><?= $chat_messages["month"] ?></b>
          <br>
          За всё время: <b><?= $chat_messages["all_time"] ?></b>
        </p>
      </div>
    </div>
  </div>
</section>

<?php

$news_top_authors = $pdo->query("
  SELECT
    COUNT(id) as news_count,
    author,
    (SELECT coalesce(nick, login) FROM ".$prefix."_users WHERE id=t_news.author) as nick
  FROM ".$prefix."_news t_news
  GROUP BY author
  ORDER BY news_count DESC
  LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

$news_top_commentators = $pdo->query("
  SELECT
    COUNT(id) as comments_count,
    author,
    (SELECT coalesce(nick, login) FROM ".$prefix."_users WHERE id=t_comments.author) as nick
  FROM ".$prefix."_comments t_comments
  GROUP BY author
  ORDER BY comments_count DESC
  LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

?>

<section class="block block_mb appear-bottom anim-delay-2">
  <h2 class="block__title">Новости</h2>
  <div class="earns-graphs">
    <div class="earns-graphs__col">
      <h3 class="earns-graphs__title">Топ авторов</h3>
      <div class="well">
        <div class="table-responsive">
          <table class="table-widget-top">
            <thead>
              <tr>
                <th>#</th>
                <th>Автор</th>
                <th>Публикаций</th>
              </tr>
            </thead>
            <tbody>
              <? $author_index = 0; foreach ($news_top_authors as $rows): ?>
                <tr>
                  <td><?= ++$author_index ?></td>
                  <td><?= $rows["nick"] ?></td>
                  <td><?= $rows["news_count"] ?></td>
                </tr>
              <? endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="earns-graphs__col">
      <h3 class="earns-graphs__title">Топ комментаторов</h3>
      <div class="well">
        <div class="table-responsive">
          <table class="table-widget-top">
            <thead>
              <tr>
                <th>#</th>
                <th>Автор</th>
                <th>Комментариев</th>
              </tr>
            </thead>
            <tbody>
              <? $author_index = 0; foreach ($news_top_commentators as $rows): ?>
                <tr>
                  <td><?= ++$author_index ?></td>
                  <td><?= $rows["nick"] ?></td>
                  <td><?= $rows["comments_count"] ?></td>
                </tr>
              <? endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</section>