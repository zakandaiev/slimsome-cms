<?php

if (is_file("db_connect.php")) header("Location: /");

function tableExists($pdo, $table) {
  try {
    $result = $pdo->query("SELECT 1 FROM $table LIMIT 1");
  } catch (Exception $e) {
    return FALSE;
  }
  return $result !== FALSE;
}

?>

<!DOCTYPE html>
<html lang="ru">

<head>
  <meta name="robots" content="noindex, nofollow">
  <meta charset="utf-8">
  <title>Установка SlimSome CMS</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, target-densitydpi=device-dpi">
  <link rel="mask-icon" href="../favicon.svg">
  <link rel="icon" type="image/svg+xml" href="../favicon.svg">
  <link rel="stylesheet" href="../css/main.css">
</head>

<body style="height:auto;">
  <div class="page-install">
    <div class="page-logo">
      <h1 class="page-logo__title">Установка SlimSome CMS</h1>
    </div>
    <div class="page-content">
      <div class="container">
        <? if (isset($_POST["start_install"])): ?>
          <div class="well">
            <?php
              // PREPARE TO INSTALL
              $dsn = "mysql:host=".$_POST['db_host'].";dbname=".$_POST['db_name'].";charset=utf8";
              $user = $_POST['db_user'];
              $passwd = $_POST['db_pass'];

              try {
                $pdo = new PDO($dsn, $user, $passwd);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
              } catch(PDOException $e) {
                echo '<h2>Ошибка соединения с БД!</h2>';
                echo '<p>Причина: ' . $e->getMessage() . '</p>';
                echo '<a href="/" class="btn btn_primary">Попробовать еще раз</a>';
                exit();
              }

              if(tableExists($pdo, $_POST["db_prefix"]."_settings") == 1) {
                echo '<h2>Ошибка БД!</h2>';
                echo '<p>Удалите все таблицы с БД "'.$_POST["db_name"].'" и попробуйте переустановить систему.</p>';
                echo '<a href="/" class="btn btn_primary">Попробовать еще раз</a>';
                exit();
              }

              // PREPARE DATA
              $site_logo_path = "null";
              if(isset($_FILES["site_logo"]) && $_FILES["site_logo"]["size"] > 0) {
                $site_logo_path = "img/uploads/" . $_FILES["site_logo"]["name"];
                if (!file_exists("../img/uploads")) {
                  mkdir("../img/uploads", 0755, true);
                }
                move_uploaded_file($_FILES["site_logo"]["tmp_name"], "../".$site_logo_path);
                $site_logo_path = "'".$site_logo_path."'";
              }

              $payments = array (
               "InterKassa" => array("purse" => null, "secret" => null, "enabled" => false),
               "LiqPay" => array("purse" => null, "secret" => null, "enabled" => false)
              );

              $payments = json_encode($payments, JSON_FORCE_OBJECT);

              // BEGIN INSTALL PROCESS
              $sql_file = file_get_contents('install.sql');
              $sql_query = explode(";", $sql_file);
              foreach($sql_query as $sql_row) {
                if (isset($sql_row) and !empty($sql_row)) {
                  $replace_from = [
                    "%db_host%", "%db_user%", "%db_pass%", "%db_name%", "%prefix%", "%t_zone%", "%cron_pass%",
                    "%site_name%", "%site_url%", "%site_email%", "'%site_logo%'",
                    "%server_ip%", "%ftp_host%", "%ftp_login%", "%ftp_pass%", "%ftp_users_path%", "%ftp_bans_path%", "%ftp_stats_path%",
                    "%payments%",
                    "%adm_login%", "%adm_pass%", "%adm_email%", "%adm_name%"
                  ];
                  $replace_to = [
                    $_POST["db_host"], $_POST["db_user"], $_POST["db_pass"], $_POST["db_name"], $_POST["db_prefix"], $_POST["t_zone"], $_POST["cron_pass"],
                    $_POST["site_name"], $_POST["site_url"], $_POST["site_email"], $site_logo_path,
                    $_POST["server_ip"], $_POST["ftp_host"], $_POST["ftp_login"], $_POST["ftp_pass"], $_POST["ftp_users_path"], $_POST["ftp_bans_path"], $_POST["ftp_stats_path"],
                    $payments,
                    $_POST["adm_login"], $_POST["adm_pass"], $_POST["adm_email"], $_POST["adm_name"]
                  ];
                  $sql_row_rep = str_replace($replace_from, $replace_to, $sql_row);

                  // FOR DEBUG
                  //file_put_contents("install.txt", $sql_row_rep . "\n", FILE_APPEND | LOCK_EX);

                  $query = $pdo->prepare($sql_row_rep);
                  $query->execute();
                }
              }

              $db_connect_file = '<?php' . PHP_EOL;
              $db_connect_file .= '  $dsn = "mysql:host=' . $_POST['db_host'] . ';dbname=' . $_POST['db_name'] . ';charset=utf8";' . PHP_EOL;
              $db_connect_file .= '  $user = "' . $_POST["db_user"] . '";' . PHP_EOL;
              $db_connect_file .= '  $passwd = "' . $_POST["db_pass"] . '";' . PHP_EOL;
              $db_connect_file .= '  $pdo = new PDO($dsn, $user, $passwd, array(PDO::ATTR_PERSISTENT => true));' . PHP_EOL;
              $db_connect_file .= '  $prefix = "' . $_POST['db_prefix'] . '";' . PHP_EOL;
              $db_connect_file .= '?>';
              file_put_contents("db_connect.php", $db_connect_file, LOCK_EX);

              $robots_txt = 'User-agent: *' . PHP_EOL;
              $robots_txt .= 'Disallow: /404' . PHP_EOL;
              $robots_txt .= 'Disallow: /core/*' . PHP_EOL;
              $robots_txt .= 'Disallow: /pages/*' . PHP_EOL;
              $robots_txt .= 'Disallow: /partials/*' . PHP_EOL;
              $robots_txt .= 'Disallow: /profile' . PHP_EOL;
              $robots_txt .= 'Disallow: /gamechat' . PHP_EOL;
              $robots_txt .= 'Disallow: /SourceQuery/*' . PHP_EOL . PHP_EOL;
              $robots_txt .= 'Sitemap: ' . $_POST['site_url'] . 'sitemap.xml';
              file_put_contents("../robots.txt", $robots_txt, LOCK_EX);

              $sitemap_xml = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . PHP_EOL;
              $sitemap_xml .= '<url><loc>'.$_POST['site_url'].'</loc><lastmod>'.date('c').'</lastmod><priority>1.00</priority></url>' . PHP_EOL;
              $sitemap_xml .= '<url><loc>'.$_POST['site_url'].'admins</loc><lastmod>'.date('c').'</lastmod><priority>0.80</priority></url>' . PHP_EOL;
              $sitemap_xml .= '<url><loc>'.$_POST['site_url'].'buy</loc><lastmod>'.date('c').'</lastmod><priority>0.80</priority></url>' . PHP_EOL;
              $sitemap_xml .= '<url><loc>'.$_POST['site_url'].'bans</loc><lastmod>'.date('c').'</lastmod><priority>0.80</priority></url>' . PHP_EOL;
              $sitemap_xml .= '<url><loc>'.$_POST['site_url'].'stats</loc><lastmod>'.date('c').'</lastmod><priority>0.80</priority></url>' . PHP_EOL;
              $sitemap_xml .= '<url><loc>'.$_POST['site_url'].'news</loc><lastmod>'.date('c').'</lastmod><priority>0.80</priority></url>' . PHP_EOL;
              $sitemap_xml .= '<url><loc>'.$_POST['site_url'].'page?info</loc><lastmod>'.date('c').'</lastmod><priority>0.80</priority></url>' . PHP_EOL;
              $sitemap_xml .= '<url><loc>'.$_POST['site_url'].'page?rules</loc><lastmod>'.date('c').'</lastmod><priority>0.80</priority></url>' . PHP_EOL;
              $sitemap_xml .= '<url><loc>'.$_POST['site_url'].'restore</loc><lastmod>'.date('c').'</lastmod><priority>0.30</priority></url>' . PHP_EOL;
              $sitemap_xml .= '<url><loc>'.$_POST['site_url'].'registration</loc><lastmod>'.date('c').'</lastmod><priority>0.30</priority></url>' . PHP_EOL;
              $sitemap_xml .= '<url><loc>'.$_POST['site_url'].'privacy-policy</loc><lastmod>'.date('c').'</lastmod><priority>0.30</priority></url>' . PHP_EOL;
              $sitemap_xml .= '<url><loc>'.$_POST['site_url'].'public-offer</loc><lastmod>'.date('c').'</lastmod><priority>0.30</priority></url>' . PHP_EOL;
              $sitemap_xml .= '<url><loc>'.$_POST['site_url'].'processing-of-personal-data</loc><lastmod>'.date('c').'</lastmod><priority>0.30</priority></url>' . PHP_EOL;
              $sitemap_xml .= '</urlset>';
              file_put_contents("../sitemap.xml", $sitemap_xml, LOCK_EX);
            ?>

            <h2>Успех!</h2>
            <a href="/" class="btn btn_primary">Перейти на сайт</a>
            <button id="install_del_btn" class="btn">Удалить файл установки</button>
          </div>
        <? else: ?>
          <section class="block block_mb">
            <h2 class="accordion__title appear-bottom">О системе</h2>
            <div class="accordion__content">
              <div class="well">
                <p><strong>SlimSome CMS</strong> — это <u>бесплатная Open-Source</u> система для простого создания сайта вашего игрового проекта Counter-Strike 1.6.</p>
                <h3>Возможности:</h3>
                <ul>
                  <li>Тонкая настройка сайта без знаний программирования</li>
                  <li>Автоматическая продажа привилегий</li>
                  <li>Информация о сервере online</li>
                  <li>Игровой чат online</li>
                  <li>Список привилегированых игроков</li>
                  <li>Список забаненых игроков</li>
                  <li>Подробная статистика игроков</li>
                  <li>Публикация новостей</li>
                  <li>Чат на сайте</li>
                  <li>Управление страницами</li>
                  <li>Настроенная SEO оптимизация</li>
                  <li>Отображение статистических данных</li>
                  <li>Регистрация и авторизация пользователей</li>
                  <li>Профиль пользователя, модератора и администратора сайта</li>
                  <li>Простое управление пользователями и их привилегиями</li>
                  <li>Автоматическая отправка e-mail писем</li>
                  <li>Современный дизайн</li>
                  <li>И многое другое...</li>
                </ul>
                <h3>Требования игрового сервера:</h3>
                <ul>
                  <li>SlimSomeUED.amxx (автор: szawesome, проверялось с версией: 1.0)</li>
                  <li><a href="https://dev-cs.ru/resources/196/" target="_blank">Fresh Bans</a> (автор: mazdan, проверялось с версией: 1.4.3)</li>
                  <ul>
                    <li>SQLite версия</li>
                    <li>значение квара fb_use_sql "2"</li>
                    <li>баны должны сохраняться в файл cstrike/addons/amxmodx/data/sqlite3/fresh_bans.sq3</li>
                  </ul>
                  <li><a href="https://dev-cs.ru/resources/178/" target="_blank">StatsX</a> (автор: любой, проверялось с плагином: AES: StatsX CStrike 0.5+1)</li>
                  <ul>
                    <li>//#define CSSTATSX_SQL - должен быть закомментирован</li>
                    <li>статистика должна сохраняться в файл cstrike/addons/amxmodx/data/csstats.dat</li>
                  </ul>
                  <li><a href="https://dev-cs.ru/resources/112/" target="_blank">Chat Manager</a> (автор: Mistrick, проверялось с версией: 1.1.2-16)</li>
                  <ul>
                    <li>включен лог сообщений</li>
                    <li>#define FUNCTION_LOG_MESSAGES - должен быть раскомментирован</li>
                    <li>логи должны сохраняться в папку cstrike/addons/amxmodx/logs/chatmanager</li>
                  </ul>
                </ul>
                <h3>Требования web окружения:</h3>
                <ul>
                  <li>PHP 7.4+</li>
                  <li>MySQL 5.7+ или MariaDB 10.3+</li>
                  <li>CRON</li>
                </ul>
                <h3>Процесс установки:</h3>
                <ol>
                  <li>Заполните Форму установки ниже</li>
                  <li>Удалите инсталяционные файлы</li>
                  <li>Войдите под указанными даными администратора на Сайт</li>
                  <li>Перейдите в Профиль и произведите финальную настройку Сайта в соответсвующих разделах</li>
                  <li>
                    Создайте CRON задания
                    <ol>
                      <li>
                        Обновление банлиста
                        <ul>
                          <li>wget -q -O - https://вашдомен.ru/core/cron_banlist.php?cp=ваш_пароль_от_CRON > /dev/null 2>&1</li>
                          <li>рекомендуемый интервал - каждый час</li>
                        </ul>
                      </li>
                      <li>
                        Обновление статистики
                        <ul>
                          <li>wget -q -O - https://вашдомен.ru/core/cron_stats.php?cp=ваш_пароль_от_CRON > /dev/null 2>&1</li>
                          <li>рекомендуемый интервал - 1 раз в день, в 4:00</li>
                        </ul>
                      </li>
                      <li>
                        Напоминания покупателям на e-mail об продлении привилегии
                        <ul>
                          <li>wget -q -O - https://вашдомен.ru/core/cron_service_end_notify.php?cp=ваш_пароль_от_CRON > /dev/null 2>&1</li>
                          <li>рекомендуемый интервал - 1 раз в день, в 15:00</li>
                        </ul>
                      </li>
                    </ol>
                  </li>
                </ol>
                <h3>Контакты автора:</h3>
                <ul>
                  <li>Telegram - <a href="https://t.me/sun_4o" target="_blank">https://t.me/sun_4o</a></li>
                  <li>VK - <a href="https://vk.com/szawesome" target="_blank">https://vk.com/szawesome</a></li>
                  <li>Репозиторий на GitHub - <a href="https://github.com/zakandaiev/slimsome-cms" target="_blank">https://github.com/zakandaiev/slimsome-cms</a></li>
                  <li>Поддержать автора - <a href="https://www.liqpay.ua/ru/checkout/awesomecs" target="_blank">https://www.liqpay.ua/ru/checkout/awesomecs</a></li>
                </ul>
              </div>
            </div>
          </section>
          <section class="block">
            <h2 class="accordion__title appear-bottom anim-delay-1">Форма установки</h2>
            <div class="accordion__content">
              <div class="well">
                <form method="post" action="install.php" class="form" enctype="multipart/form-data">
                  <h3>Настройки Базы Данных</h3>
                  <label>Сервер БД</label>
                  <input type="text" name="db_host" placeholder="localhost" required>
                  <label>Пользователь БД</label>
                  <input type="text" name="db_user" placeholder="Пользователь БД" required>
                  <label>Пароль пользователя БД</label>
                  <input type="text" name="db_pass" placeholder="Пароль пользователя БД" required>
                  <label>Название БД</label>
                  <input type="text" name="db_name" placeholder="Название БД" required>
                  <label>Префикс БД</label>
                  <input type="text" name="db_prefix" value="scms" placeholder="scms" required>
                  <label>Часовой пояс</label>
                  <select name="t_zone" required>
                    <option value="Europe/Moscow" selected>Москва</option>
                    <option value="Europe/Kiev">Киев</option>
                  </select>
                  <label>Пароль CRON</label>
                  <input type="text" name="cron_pass" placeholder="Пароль CRON" required>

                  <h3>Настройки Сайта</h3>
                  <label>Название сайта</label>
                  <input type="text" name="site_name" value="SlimSome CMS" placeholder="SlimSome CMS" required>
                  <label>URL сайта
                    <br><small>Обязательно со слешем (/) в конце</small>
                  </label>
                  <input type="text" name="site_url" value="<?=$_SERVER['HTTP_X_FORWARDED_PROTO']?>://<?=$_SERVER['HTTP_HOST']?>/" placeholder="https://site.ru/" required>
                  <label>E-mail администратора сайта</label>
                  <input type="text" name="site_email" placeholder="admin@<?=$_SERVER['HTTP_HOST']?>" required>
                  <label>Логотип сайта</label>
                  <input type="file" accept="image/*" name="site_logo" placeholder="Логотип">

                  <h3>Данные Администратора</h3>
                  <label>Логин</label>
                  <input type="text" name="adm_login" value="admin" placeholder="Логин" required>
                  <label>Пароль</label>
                  <input type="text" name="adm_pass" placeholder="Пароль" required>
                  <label>E-mail</label>
                  <input type="email" name="adm_email" placeholder="E-mail" required>
                  <label>Ваше Имя</label>
                  <input type="text" name="adm_name" value="<?=getenv('username')?>" placeholder="Ваше Имя">

                  <h3>Настройки игрового Сервера</h3>
                  <label>IP:PORT</label>
                  <input type="text" name="server_ip" placeholder="xx.xx.xx.xx:27015" pattern="\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}:\d{5}" required>
                  <label>Хост FTP</label>
                  <input type="text" name="ftp_host" placeholder="Хост FTP" required>
                  <label>Логин FTP</label>
                  <input type="text" name="ftp_login" placeholder="Логин FTP" required>
                  <label>Пароль FTP</label>
                  <input type="text" name="ftp_pass" placeholder="Пароль FTP" required>
                  <label>Путь к users.ini</label>
                  <input type="text" name="ftp_users_path" placeholder="addons/amxmodx/configs/users.ini" value="addons/amxmodx/configs/users.ini" required>
                  <label>Путь к fresh_bans.sq3</label>
                  <input type="text" name="ftp_bans_path" placeholder="addons/amxmodx/data/sqlite3/fresh_bans.sq3" value="addons/amxmodx/data/sqlite3/fresh_bans.sq3" required>
                  <label>Путь к csstats.dat</label>
                  <input type="text" name="ftp_stats_path" placeholder="addons/amxmodx/data/csstats.dat" value="addons/amxmodx/data/csstats.dat" required>

                  <button type="submit" name="start_install" class="btn btn_primary">Установить</button>
                </form>
              </div>
            </div>
          </section>
        <? endif; ?>
      </div>
    </div>
  </div>

  <script src="../js/main.js"></script>

  <script>
    $("#install_del_btn").on("click", function() {
      $.ajax({
        method: "POST",
        url: "install_del.php",
        data: { sure: true }
      }).done(function(response) {
        const jsonData = JSON.parse(response);
        if (jsonData.success == "1") {
          toastr["success"]("Файл установки удалён");
          $("#install_del_btn").fadeOut(300, function() {$(this).remove();});
        } else {
          toastr["warning"]("Файлов установки не найдено");
        }
      });
    });
  </script>

</body>

</html>
