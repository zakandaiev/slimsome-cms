<?php

$db_news_query = $pdo->query("
  SELECT *,
    (SELECT coalesce(nick,login) FROM ".$prefix."_users WHERE id=t_news.author) as author_nick,
    (SELECT name FROM ".$prefix."_users WHERE id=t_news.author) as author_name
  FROM ".$prefix."_news t_news
  ORDER BY cdate ASC
")->fetchAll(PDO::FETCH_ASSOC);

return $db_news_query;

?>