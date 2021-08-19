<?php

$admins_list_limit = 10;

$last_n_privilegies = $pdo->prepare("
  SELECT
    coalesce(nick,login) as nick,
    coalesce(name,nick,login) as name,
    (SELECT name FROM ".$prefix."_services WHERE id=t_users.service_id) as service_name,
    service_start, service_end,
    service_nolimit
  FROM ".$prefix."_users t_users
  WHERE service_id is not null
  ORDER BY service_start DESC, id DESC
  LIMIT :limit
");
$last_n_privilegies->bindParam(":limit", $admins_list_limit, PDO::PARAM_INT);
$last_n_privilegies->execute();
$last_n_privilegies = $last_n_privilegies->fetchAll(PDO::FETCH_ASSOC);

?>

<? if(!empty($last_n_privilegies)): ?>
  <div class="table-responsive">
    <table class="table table-admins">
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
        <? $al_index = 0;foreach ($last_n_privilegies as $rows): ?>
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
          <tr <?=$service_status?>>
            <td><?=++$al_index?></td>
            <td title="<?=$rows["name"]?>"><?=$rows["nick"]?></td>
            <td><?=$rows["service_name"]?></td>
            <td><?=dateWhen(strtotime($rows["service_start"]))?></td>
            <td><?=$service_end?></td>
          </tr>
        <? endforeach; ?>
      </tbody>
    </table>
  </div>
  <br>
  <a href="/buy" class="btn btn_cta btn_block">Купить привилегию</a>
<? else: ?>
  <div class="well"><p>Привилегированных игроков еще нет. Будь первым! ;)</p><a href="buy" class="btn btn_cta">Купить привилегию</a></div>
<? endif; ?>