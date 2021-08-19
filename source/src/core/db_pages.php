<?php

$db_pages_query = $pdo->query("SELECT * FROM ".$prefix."_pages ORDER BY enabled DESC, -page_order DESC")->fetchAll(PDO::FETCH_ASSOC);

return $db_pages_query;

?>