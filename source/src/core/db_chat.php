<?php

$db_chat_query = $pdo->query("
  SELECT * FROM (
    SELECT *, (
      SELECT coalesce(nick,login) FROM ".$prefix."_users WHERE id=t_chat.user_id) as nick,
      (CASE 
        WHEN (SELECT name FROM ".$prefix."_services WHERE id=(SELECT service_id FROM ".$prefix."_users WHERE id=t_chat.user_id AND service_id IS NOT NULL AND (service_nolimit IS TRUE OR CURRENT_TIMESTAMP < service_end)) AND enabled IS TRUE) IS NOT NULL
        THEN (SELECT name FROM ".$prefix."_services WHERE id=(SELECT service_id FROM ".$prefix."_users WHERE id=t_chat.user_id AND service_id IS NOT NULL AND (service_nolimit IS TRUE OR CURRENT_TIMESTAMP < service_end)) AND enabled IS TRUE)
        WHEN (SELECT isadmin FROM ".$prefix."_users WHERE id=t_chat.user_id) = 1
        THEN 'Администратор сайта'
        WHEN (SELECT ismoder FROM ".$prefix."_users WHERE id=t_chat.user_id) = 1
        THEN 'Модератор сайта'
        ELSE 'Пользователь'
      END) as position,
      (CASE 
        WHEN (SELECT user_avatar FROM ".$prefix."_services WHERE id=(SELECT service_id FROM ".$prefix."_users WHERE id=t_chat.user_id AND service_id IS NOT NULL AND (service_nolimit IS TRUE OR CURRENT_TIMESTAMP < service_end)) AND enabled IS TRUE) IS NOT NULL
        THEN (SELECT user_avatar FROM ".$prefix."_services WHERE id=(SELECT service_id FROM ".$prefix."_users WHERE id=t_chat.user_id AND service_id IS NOT NULL AND (service_nolimit IS TRUE OR CURRENT_TIMESTAMP < service_end)) AND enabled IS TRUE)
        ELSE 'img/no_avatar.jpg'
      END) as avatar,
      (CASE 
        WHEN refference IS NOT NULL
        THEN (SELECT coalesce(nick,login) FROM ".$prefix."_users WHERE id=(SELECT user_id FROM ".$prefix."_chat WHERE id=t_chat.refference))
      END) as refference_name
    FROM ".$prefix."_chat t_chat ORDER BY cdate DESC LIMIT 30
  ) t1 ORDER BY t1.id -- reverse rows
")->fetchAll(PDO::FETCH_ASSOC);

return $db_chat_query;

?>