<? require_once("partials/header.php"); ?>

<div class="home-content">
  <div class="home-content__left">
    <section class="block">
      <h2 class="block__title">Информация</h2>
      <div id="server-info">
        <div class="widget-online">
          <div class="widget-online__top">
            <img src="img/no_image.jpg" alt="Текущая карта" class="widget-online__img">
            <div class="widget-online__info">
              <p><b>Карта:</b> <span style="display: inline-block;width: 92px;background: #e1e1e1;height: 1rem;margin-left: 5px;"></span></p>
              <p><b>Игроков:</b> <span style="display: inline-block;width: 76px;background: #e1e1e1;height: 1rem;margin-left: 5px;"></span></p>
              <p><span class="widget-online__indicator offline"></span><b><?= $GLOBALS["server_ip"] ?></b></p>
              <a href="steam://connect/91.211.118.55:27015" class="btn btn_primary">Подключиться</a>
            </div>
          </div>
          <div class="widget-online__players">
            <div class="loader"><div></div><div></div><div></div><div></div></div>
          </div>
        </div>
      </div>
    </section>
  </div>
  <div class="home-content__right">
    <section class="block block_mb appear-bottom">
      <h2 class="block__title">Новости</h2>
      <? require_once("partials/widget_news_list.php") ?>
    </section>
    <section class="block block_mb appear-bottom anim-delay-1">
      <h2 class="block__title">Последние 10 привилегий</h2>
      <? require_once("partials/widget_admins_list.php") ?>
    </section>
    <? if ($GLOBALS["site_chat_enabled"] == 'true'): ?>
      <? if ($GLOBALS["site_chat_enabled_for_unregistereds"] == 'true' || ($GLOBALS["site_chat_enabled_for_unregistereds"] == 'false' && $is_user_logged)): ?>
        <section class="block appear-bottom anim-delay-2">
          <h2 class="block__title">Чат</h2>
          <div id="chat">
            <? require_once("partials/chat.php") ?>
          </div>
        </section>
      <? endif; ?>
    <? endif; ?>
  </div>
</div>

<? require_once("partials/footer.php"); ?>