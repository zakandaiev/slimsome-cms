# SlimSome CMS
<img width=150 align="right" src="https://raw.githubusercontent.com/zakandaiev/slimsome-cms/main/source/src/_root-dir-files/favicon.png" alt="SlimSome Logo">
Бесплатная система для простого создания сайта вашего игрового проекта Counter-Strike 1.6

#### Содержимое
1. [Живой пример](#живой-пример)
2. [Скачать](#скачать)
3. [Возможности](#возможности)
4. [Требования игрового сервера](#требования-игрового-сервера)
5. [Требования web окружения](#требования-web-окружения)
6. [Процесс установки](#процесс-установки)
7. [Редактирование исходного кода](#редактирование-исходного-кода)

## Живой пример
* [awesomecs.ru](https://awesomecs.ru)

## Скачать
* Скачать: [последняя версия v1.1.5](https://github.com/zakandaiev/slimsome-cms/files/7873334/slimsome-cms-v1.1.5.zip)
* Посмотреть: [все релизы](https://github.com/zakandaiev/slimsome-cms/releases)

## Возможности
* Тонкая настройка сайта без знаний программирования
* Автоматическая продажа привилегий
* Информация о сервере online
* Игровой чат online
* Список привилегированых игроков
* Список забаненых игроков
* Подробная статистика игроков
* Публикация новостей
* Чат на сайте
* Управление страницами
* Настроенная SEO оптимизация
* Отображение статистических данных
* Регистрация и авторизация пользователей
* Профиль пользователя, модератора и администратора сайта
* Простое управление пользователями и их привилегиями
* Автоматическая отправка e-mail писем
* Современный дизайн
* И многое другое...

## Требования игрового сервера
* [SlimSomeUED.amxx](https://github.com/zakandaiev/slimsome-cms/blob/main/SlimSomeUED.sma) (автор: szawesome, проверялось с версией: *1.0*)
* [Fresh Bans](https://dev-cs.ru/resources/196/) (автор: mazdan, проверялось с версией: *1.4.3*)
  * SQLite версия
  * значение квара `fb_use_sql "2"`
  * баны должны сохраняться в файл `cstrike/addons/amxmodx/data/sqlite3/fresh_bans.sq3`
* [StatsX](https://dev-cs.ru/resources/178/) (автор: любой, проверялось с плагином: *AES: StatsX CStrike 0.5+1*)
  * `//#define CSSTATSX_SQL` - должен быть закомментирован
  * статистика должна сохраняться в файл `cstrike/addons/amxmodx/data/csstats.dat`
* [Chat Manager](https://dev-cs.ru/resources/112/) (автор: Mistrick, проверялось с версией: *1.1.2-16*)
  * включен лог сообщений
  * `#define FUNCTION_LOG_MESSAGES` - должен быть раскомментирован
  * логи должны сохраняться в папку `cstrike/addons/amxmodx/logs/chatmanager`

## Требования web окружения
* PHP 7.4+
* MySQL 5.7+ или MariaDB 10.3+
* CRON

## Процесс установки
1. Скачайте последнюю версию.
2. Загрузите содержимое на веб-хостинг.
3. Перейдите на сайт и заполните Форму установки.
4. Удалите инсталяционные файлы.
5. Войдите под указанными даными администратора на Сайт.
6. Перейдите в Профиль и произведите финальную настройку Сайта в соответсвующих разделах.
7. Создайте CRON задания:
    1. Обновление банлиста
        * `wget -q -O - https://вашдомен.ru/core/cron_banlist.php?cp=ваш_пароль_от_CRON > /dev/null 2>&1`
        * рекомендуемый интервал - *каждый час*
    2. Обновление статистики
        * `wget -q -O - https://вашдомен.ru/core/cron_stats.php?cp=ваш_пароль_от_CRON > /dev/null 2>&1`
        * рекомендуемый интервал - *1 раз в день, в 4:00*
    3. Напоминания покупателям на e-mail об продлении привилегии
        * `wget -q -O - https://вашдомен.ru/core/cron_service_end_notify.php?cp=ваш_пароль_от_CRON > /dev/null 2>&1`
        * рекомендуемый интервал - *1 раз в день, в 15:00*

## Редактирование исходного кода
В процессе создания был использован [FrontEnd Boilerplate](https://github.com/zakandaiev/frontend-boilerplate) и адаптирован под PHP окружение. Для работы с исходным кодом потребуется установить NodeJS и глобально Gulp4. Для создания БД и обработки PHP можно использовать OpenServer.
1. Создайте локальный домен `slimsome.local`, БД и пользователя.
2. Папку `source` поместите в корень сайта (`slimsome.local/source`).
3. Откройте коммандную строку и зайдите в папку `source`.
4. Установите все необходимые модули и зависимости командой `npm i`.
5. Команда `gulp` запускает окружение для разработки.
6. Команда `gulp build` компилирует все исходники в файлы для продакшена.
7. На настоящий сайт скопировать всё содержимое `slimsome.local` кроме папки `source`.
