<?php

$db_users_query = $pdo->query("SELECT *, (SELECT name FROM ".$prefix."_services WHERE id=t_users.service_id) as service_name FROM ".$prefix."_users t_users ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

return $db_users_query;

?>