<?php

require_once("partials/header.php");

if(!isset($_GET["section"]) || empty($_GET["section"])) {
  $current_nav = "account";
} else {
  $current_nav = $_GET["section"];
}

?>

<div class="profile-content">
  <div class="profile-content__nav">
    <section class="block">
      <div class="profile">
        <div class="profile__info">
          <img class="profile__img" src="<?= $user_avatar ?>" title="<?= $user_position ?>" alt="<?= $user_position ?>">
          <div class="profile__rec">
            <h3 class="profile__nick"><?= $user_login ?></h3>
            <p class="profile__status"><?= $user_position ?></p>
          </div>
        </div>
        <div class="profile__nav">
          <a href="profile" <? if($current_nav == "account") echo 'class="active"' ?>>
            <?= getSvg("img/icons/star.svg") ?>
            Аккаунт
          </a>
          <a href="profile?section=billing" <? if($current_nav == "billing") echo 'class="active"' ?>>
            <?= getSvg("img/icons/shopping-cart.svg") ?>
            История покупок
          </a>
          <? if ($is_user_admin): ?>
            <a href="profile?section=site" <? if($current_nav == "site") echo 'class="active"' ?>>
              <?= getSvg("img/icons/site.svg") ?>
              Настройки сайта
            </a>
            <a href="profile?section=settings" <? if($current_nav == "settings") echo 'class="active"' ?>>
              <?= getSvg("img/icons/settings.svg") ?>
              Настройки системы
            </a>
            <a href="profile?section=payments" <? if($current_nav == "payments") echo 'class="active"' ?>>
              <?= getSvg("img/icons/credit-card.svg") ?>
              Настройки оплаты
            </a>
            <a href="profile?section=services" <? if($current_nav == "services") echo 'class="active"' ?>>
              <?= getSvg("img/icons/vip.svg") ?>
              Настройки привилегий
            </a>
            <a href="profile?section=users" <? if($current_nav == "users") echo 'class="active"' ?>>
              <?= getSvg("img/icons/users.svg") ?>
              Настройки пользователей
            </a>
            <a href="profile?section=socials" <? if($current_nav == "socials") echo 'class="active"' ?>>
              <?= getSvg("img/icons/share.svg") ?>
              Настройки соц. сетей
            </a>
          <? endif; ?>
          <? if ($is_user_admin || $is_user_moder): ?>
            <a href="profile?section=pages" <? if($current_nav == "pages") echo 'class="active"' ?>>
              <?= getSvg("img/icons/pages.svg") ?>
              Настройки страниц
            </a>
            <a href="profile?section=news" <? if($current_nav == "news") echo 'class="active"' ?>>
              <?= getSvg("img/icons/news.svg") ?>
              Новости
            </a>
          <? endif; ?>
          <? if ($is_user_admin): ?>
            <a href="profile?section=stats" <? if($current_nav == "stats") echo 'class="active"' ?>>
              <?= getSvg("img/icons/bar-chart.svg") ?>
              Статистика
            </a>
          <? endif; ?>
          <a href="/logout">
            <?= getSvg("img/icons/logout.svg") ?>
            Выход
          </a>
        </div>
      </div>
    </section>
  </div>

  <? if($current_nav == "account"): ?>
    <? require_once("partials/profile_account.php"); ?>
  <? else: ?>
    <div class="profile-content__settings">
      <?php
        if ($current_nav == "billing") {
          require_once("partials/profile_billing.php");
        } else if ($is_user_admin && $current_nav == "site") {
          require_once("partials/profile_site.php");
        } else if ($is_user_admin && $current_nav == "settings") {
          require_once("partials/profile_settings.php");
        } else if ($is_user_admin && $current_nav == "services") {
          require_once("partials/profile_services.php");
        } else if ($is_user_admin && $current_nav == "payments") {
          require_once("partials/profile_payments.php");
        } else if ($is_user_admin && $current_nav == "users") {
          require_once("partials/profile_users.php");
        } else if ($is_user_admin && $current_nav == "socials") {
          require_once("partials/profile_socials.php");
        } else if (($is_user_admin || $is_user_moder) && $current_nav == "pages") {
          require_once("partials/profile_pages.php");
        } else if (($is_user_admin || $is_user_moder) && $current_nav == "news") {
          require_once("partials/profile_news.php");
        } else if ($is_user_admin && $current_nav == "stats") {
          require_once("partials/profile_stats.php");
        } else if ($current_nav == "prolong") {
          require_once("partials/profile_prolong.php");
        } else {
          echo '
            <section class="block">
              <h2 class="block__title">У вас нет доступа к этому разделу!</h2>
            </section>
          ';
        }
      ?>
    </div>
  <? endif; ?>
</div>

<? require_once("partials/footer.php"); ?>