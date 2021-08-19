<section class="block">
  <h2 class="block__title">Настройки сайта</h2>
  <div class="well appear-bottom">
    <form id="edit_site" method="post" class="form">
      <h3>Основное</h3>
      <label>Название сайта</label>
      <input type="text" name="site_name" value="<?= $GLOBALS["site_name"] ?>" placeholder="SlimSome CMS" required>
      <label>URL сайта
        <br><small>Обязательно со слешем (/) в конце</small>
      </label>
      <input type="text" name="site_url" value="<?= $GLOBALS["site_url"] ?>" placeholder="https://site.ru/" required>
      <label>E-mail администратора сайта</label>
      <input type="text" name="site_email" value="<?= $GLOBALS["site_email"] ?>" placeholder="admin@site.ru" required>
      <label>Логотип сайта</label>
      <div class="form__preview">
        <? if (!empty($GLOBALS["site_logo"])): ?>
          <div class="form__image"><img src="<?= urlEncodeSpaces($GLOBALS["site_logo"]) ?>" alt="<?= $GLOBALS["site_name"] ?>"></div>
        <? endif; ?>
      </div>
      <input type="file" accept="image/*" name="site_logo[]" data-upload="site" placeholder="Логотип">
      <h3>Стилизация</h3>
      <label>Фоновое изображение</label>
      <div class="form__preview">
        <? if (!empty($GLOBALS["site_background"])): ?>
          <div class="form__image"><img src="<?= urlEncodeSpaces($GLOBALS["site_background"]) ?>" alt="<?= $GLOBALS["site_name"] ?>"></div>
        <? endif; ?>
      </div>
      <input type="file" accept="image/*" name="site_background[]" data-upload="site" placeholder="Фоновое изображение">
      <label>CSS стили для фонового изображения</label>
      <input type="text" name="site_background_styles" placeholder="background-repeat:no-repeat;background-position:center;" value="<?= $GLOBALS["site_background_styles"] ?>">
      <label>Первичный цвет</label>
      <input type="color" name="site_color_accent" value="<?= $GLOBALS["site_color_accent"] ?>" required>
      <label>Вторичный цвет</label>
      <input type="color" name="site_color_accent_2" value="<?= $GLOBALS["site_color_accent_2"] ?>" required>
      <label>Цвет фона</label>
      <input type="color" name="site_color_body" value="<?= $GLOBALS["site_color_body"] ?>" required>
      <label>Цвет текста</label>
      <input type="color" name="site_color_text" value="<?= $GLOBALS["site_color_text"] ?>" required>
      <h3>SEO оптимизация</h3>
      <label>SEO описание
        <br><small>&lt;meta name="description"&gt;</small>
      </label>
      <textarea name="site_description" rows="3"><?= $GLOBALS["site_description"] ?></textarea>
      <label>SEO ключевые слова
        <br><small>&lt;meta name="keywords"&gt;</small>
      </label>
      <textarea name="site_keywords" rows="3"><?= $GLOBALS["site_keywords"] ?></textarea>
      <label>Идентификатор Google Analytics</label>
      <input type="text" name="site_analytics_gtag" placeholder="G-**********" value="<?= $GLOBALS["site_analytics_gtag"] ?>">
      <h3>Чат</h3>
      <label>Включить чат</label>
      <?php
        $site_chat_enabled_state = '';
        $site_chat_enabled_for_unregistereds_state = '';
        if($GLOBALS["site_chat_enabled"] == 'true') {
          $site_chat_enabled_state = 'checked';
        }
        if($GLOBALS["site_chat_enabled_for_unregistereds"] == 'true') {
          $site_chat_enabled_for_unregistereds_state = 'checked';
        }
      ?>
      <label class="switcher">
        <input type="checkbox" name="site_chat_enabled" <?= $site_chat_enabled_state ?>>
        <span class="slider round"></span>
      </label>
      <label>Отображать чат незарегистрированным пользователям</label>
      <label class="switcher">
        <input type="checkbox" name="site_chat_enabled_for_unregistereds" <?= $site_chat_enabled_for_unregistereds_state ?>>
        <span class="slider round"></span>
      </label>
      <button type="submit" class="btn">Обновить</button>
    </form>
  </div>
</section>