<?php

// PAGINATION
$paginator_page_limit = 30;

if(isset($_GET["all"])) {
  $paginator_total_rows = $pdo->query("SELECT COUNT(id) as count FROM ".$prefix."_payments")->fetch(PDO::FETCH_LAZY)->count;
} else {
  $paginator_total_rows = $pdo->prepare("
    SELECT id FROM ".$prefix."_payments
    WHERE user_id=(SELECT id FROM ".$prefix."_users WHERE login=:login LIMIT 1)
  ");
  $paginator_total_rows->bindParam(":login", $user_login);
  $paginator_total_rows->execute();
  $paginator_total_rows = $paginator_total_rows->rowCount();
}

$paginator_current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;

$paginator_calc_page = ($paginator_current_page - 1) * $paginator_page_limit;

?>

<?php if(isset($_GET["all"])): ?>
  <?php
    $db_payments = $pdo->query("
      SELECT *,
        (SELECT login FROM ".$prefix."_users WHERE id=t_payments.user_id) as user_login,
        (SELECT concat(nick, ' (', name, ')') FROM ".$prefix."_users WHERE id=t_payments.user_id) as user_name
      FROM ".$prefix."_payments t_payments
      ORDER BY cdate DESC
      LIMIT ".$paginator_calc_page.",".$paginator_page_limit."
    ");
    $db_payments = $db_payments->fetchAll(PDO::FETCH_ASSOC);
  ?>
  <section class="block">
    <? if ($is_user_admin): ?>
      <h2 class="block__title">История покупок пользователей</h2>
      <p>Всего: <b><?= $paginator_total_rows ?></b></p>
      <div class="table-responsive appear-bottom">
        <table class="table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Пользователь</th>
              <th>Привилегия</th>
              <th>Стоимость</th>
              <th>Срок</th>
              <th>Дата</th>
              <th>Система</th>
              <th>Статус</th>
            </tr>
          </thead>
          <tbody>
            <? foreach ($db_payments as $rows): ?>
              <?php
                if ($rows["prolong"]) {
                  $service_name = $rows["service_name"] . ' <span title="Продление">&#10132;</span>';
                } else {
                  $service_name = $rows["service_name"];
                }
                if ($rows["days"] == 0) {
                  $days_count = 'навсегда';
                } else {
                  $days_count = $rows["days"].' дней';
                }
                if ($rows["status"] == 1) {
                  $payment_status = 'success';
                } else {
                  $payment_status = 'error';
                }
              ?>
              <tr>
                <td><?= $rows["id"] ?></td>
                <td>
                  <?php
                    if ($rows["user_login"]) {
                      echo '<span title="'.$rows["user_name"].'">'.$rows["user_login"].'</span>';
                    } else {
                      $user_data = json_decode($rows["user_data"], true);
                      echo '<span title="Ник: '.$user_data["nick"].' | Имя: '.$user_data["name"].' | Логин: '.$user_data["login"].' | Email: '.$user_data["email"].'">Новый пользователь</span>';
                    }
                  ?>
                </td>
                <td><?= $service_name ?></td>
                <td><?= $rows["price"] ?> <?= getCurrency($rows["currency"]) ?></td>
                <td><?= $days_count ?></td>
                <td><?= dateWhen(strtotime($rows["cdate"])) ?></td>
                <td><?= $rows["name"] ?></td>
                <td>
                  <span class="label label-<?= $payment_status ?>"><?= getPaymentStatus($rows["status"]) ?></span>
                </td>
              </tr>
            <? endforeach; ?>
          </tbody>
        </table>
      </div>
      <? if (ceil($paginator_total_rows / $paginator_page_limit) > 1 && empty($search_keyword)): ?>
        <div class="pagination appear-bottom anim-delay-1">
          <? if ($paginator_current_page > 1): ?>
            <a href="?section=billing&all&page=<?= $paginator_current_page-1 ?>" class="pagination__item">&lt;</a>
          <? endif; ?>

          <? if ($paginator_current_page > 3): ?>
            <a href="?section=billing&all&page=1" class="pagination__item">1</a>
            <span class="pagination__item">...</span>
          <? endif; ?>

          <? if ($paginator_current_page-2 > 0): ?><a href="?section=billing&all&page=<?= $paginator_current_page-2 ?>" class="pagination__item"><?= $paginator_current_page-2 ?></a><?php endif; ?>
          <? if ($paginator_current_page-1 > 0): ?><a href="?section=billing&all&page=<?= $paginator_current_page-1 ?>" class="pagination__item"><?= $paginator_current_page-1 ?></a><?php endif; ?>

          <a href="?section=billing&all&page=<?= $paginator_current_page ?>" class="pagination__item active"><?= $paginator_current_page ?></a>

          <? if ($paginator_current_page+1 < ceil($paginator_total_rows / $paginator_page_limit)+1): ?><a href="?section=billing&all&page=<?= $paginator_current_page+1 ?>" class="pagination__item"><?= $paginator_current_page+1 ?></a><?php endif; ?>
          <? if ($paginator_current_page+2 < ceil($paginator_total_rows / $paginator_page_limit)+1): ?><a href="?section=billing&all&page=<?= $paginator_current_page+2 ?>" class="pagination__item"><?= $paginator_current_page+2 ?></a><?php endif; ?>

          <? if ($paginator_current_page < ceil($paginator_total_rows / $paginator_page_limit)-2): ?>
            <span class="pagination__item">...</span>
            <a href="?section=billing&all&page=<?= ceil($paginator_total_rows / $paginator_page_limit) ?>" class="pagination__item"><?= ceil($paginator_total_rows / $paginator_page_limit) ?></a>
          <? endif; ?>

          <? if ($paginator_current_page < ceil($paginator_total_rows / $paginator_page_limit)): ?>
            <a href="?section=billing&all&page=<?= $paginator_current_page+1 ?>" class="pagination__item">&gt;</a>
          <? endif; ?>
        </div>
      <? endif; ?>
    <? else: ?>
      <h2 class="block__title">У вас нет доступа к этому разделу!</h2>
    <? endif; ?>
  </section>
<? else: ?>
  <section class="block">
    <h2 class="block__title">История покупок</h2>
    <?php
      $user_billing_query = $pdo->prepare("
        SELECT *
        FROM ".$prefix."_payments
        WHERE user_id=(SELECT id FROM ".$prefix."_users WHERE login=:login LIMIT 1)
        ORDER BY cdate DESC
        LIMIT ".$paginator_calc_page.",".$paginator_page_limit."
      ");
      $user_billing_query->bindParam(":login", $user_login);
      $user_billing_query->execute();
      $user_billing = $user_billing_query->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <? if (!empty($user_billing)): ?>
      <? if ($is_user_admin): ?>
        <div class="table-top">
          <p>Всего: <b><?= $paginator_total_rows ?></b></p>
          <a href="profile?section=billing&all" class="btn btn_primary">Посмотреть всю</a>
        </div>
      <? else: ?>
        <p>Всего: <b><?= count($user_billing) ?></b></p>
      <? endif; ?>
      <div class="table-responsive appear-bottom">
        <table class="table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Система</th>
              <th>Привилегия</th>
              <th>Стоимость</th>
              <th>Срок</th>
              <th>Дата</th>
              <th>Статус</th>
            </tr>
          </thead>
          <tbody>
            <? foreach ($user_billing as $rows): ?>
              <?php
                if ($rows["prolong"]) {
                  $service_name = $rows["service_name"] . ' <span title="Продление">&#10132;</span>';
                } else {
                  $service_name = $rows["service_name"];
                }
                if ($rows["days"] == 0) {
                  $days_count = 'навсегда';
                } else {
                  $days_count = $rows["days"].' дней';
                }
                if ($rows["status"] == 1) {
                  $payment_status = 'success';
                } else {
                  $payment_status = 'error';
                }
              ?>
              <tr>
                <td><?= $rows["id"] ?></td>
                <td><?= $rows["name"] ?></td>
                <td><?= $service_name ?></td>
                <td><?= $rows["price"] ?> <?= getCurrency($rows["currency"]) ?></td>
                <td><?= $days_count ?></td>
                <td><?= dateWhen(strtotime($rows["cdate"])) ?></td>
                <td>
                  <span class="label label-<?= $payment_status ?>"><?= getPaymentStatus($rows["status"]) ?></span>
                </td>
              </tr>
            <? endforeach; ?>
          </tbody>
        </table>
        <? if (ceil($paginator_total_rows / $paginator_page_limit) > 1 && empty($search_keyword)): ?>
          <div class="pagination">
            <? if ($paginator_current_page > 1): ?>
              <a href="?section=billing&page=<?= $paginator_current_page-1 ?>" class="pagination__item">&lt;</a>
            <? endif; ?>

            <? if ($paginator_current_page > 3): ?>
              <a href="?section=billing&page=1" class="pagination__item">1</a>
              <span class="pagination__item">...</span>
            <? endif; ?>

            <? if ($paginator_current_page-2 > 0): ?><a href="?section=billing&page=<?= $paginator_current_page-2 ?>" class="pagination__item"><?= $paginator_current_page-2 ?></a><?php endif; ?>
            <? if ($paginator_current_page-1 > 0): ?><a href="?section=billing&page=<?= $paginator_current_page-1 ?>" class="pagination__item"><?= $paginator_current_page-1 ?></a><?php endif; ?>

            <a href="?section=billing&page=<?= $paginator_current_page ?>" class="pagination__item active"><?= $paginator_current_page ?></a>

            <? if ($paginator_current_page+1 < ceil($paginator_total_rows / $paginator_page_limit)+1): ?><a href="?section=billing&page=<?= $paginator_current_page+1 ?>" class="pagination__item"><?= $paginator_current_page+1 ?></a><?php endif; ?>
            <? if ($paginator_current_page+2 < ceil($paginator_total_rows / $paginator_page_limit)+1): ?><a href="?section=billing&page=<?= $paginator_current_page+2 ?>" class="pagination__item"><?= $paginator_current_page+2 ?></a><?php endif; ?>

            <? if ($paginator_current_page < ceil($paginator_total_rows / $paginator_page_limit)-2): ?>
              <span class="pagination__item">...</span>
              <a href="?section=billing&page=<?= ceil($paginator_total_rows / $paginator_page_limit) ?>" class="pagination__item"><?= ceil($paginator_total_rows / $paginator_page_limit) ?></a>
            <? endif; ?>

            <? if ($paginator_current_page < ceil($paginator_total_rows / $paginator_page_limit)): ?>
              <a href="?section=billing&page=<?= $paginator_current_page+1 ?>" class="pagination__item">&gt;</a>
            <? endif; ?>
          </div>
        <? endif; ?>
      </div>
    <? else: ?>
      <div class="well appear-bottom">
        <p>Ваша история покупок пуста.</p>
        <?php
          $user_info = getUserInfo(null, $user_login);
          if (isUserActive($user_info["id"], null)) {
            if ($user_info["service_nolimit"]) {
              echo '
                <p>Но, у вас активна <b>безлимитная</b> привилегия <b>'.getServiceName($user_info["service_id"]).'</b> <img src="/img/smiles/cool.gif" alt="cool"></p>
              ';
            } else if (isset($user_info["service_end"]) && !empty($user_info["service_end"])) {
              echo '
                <p>Но, вас активна привилегия <b>'.getServiceName($user_info["service_id"]).'</b>, которая истекает через <b>'.dateDiff(time(), strtotime($user_info["service_end"])).'</b>.</p>
                <a href="profile?section=prolong" class="btn btn_cta btn_dib">Продлить</a>
              ';
            } else {
              echo '<a href="buy" class="btn btn_cta btn_dib">Купить привилегию</a>';
            }
          } else {
            echo '<a href="buy" class="btn btn_cta btn_dib">Купить привилегию</a>';
          }
        ?>
        <? if ($is_user_admin): ?>
          <a href="profile?section=billing&all" class="btn btn_primary">Посмотреть всю</a>
        <? endif; ?>
      </div>
    <? endif; ?>
  </section>
<? endif; ?>