# SlimSome CMS <img width=100 align="right" src="https://raw.githubusercontent.com/zakandaiev/slimsome-cms/main/source/src/_root-dir-files/favicon.png" alt="SlimSome Logo">
Бесплатная система для простого создания сайта вашего игрового проекта Counter-Strike 1.6

## Живой пример
* [awesomecs.ru](https://awesomecs.ru)

## Скачать
* [Скачать: последняя версия v1.0.0](https://github.com/zakandaiev/slimsome-cms/files/7017438/slimsome-cms-v1.0.0.zip)
* [Посмотреть: все релизы](https://github.com/zakandaiev/slimsome-cms/releases)

## Возможности
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

## Требования игрового сервера
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

## Требования web окружения
<ul>
  <li>PHP 7.4+</li>
  <li>MySQL 5.7+ или MariaDB 10.3+</li>
  <li>CRON</li>
</ul>

## Процесс установки
<ol>
  <li>Скачайте последнюю версию</li>
  <li>Загрузите содержимое на веб-хостинг</li>
  <li>Перейдите на сайт и заполните Форму установки</li>
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
