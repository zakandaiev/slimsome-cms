<?php

$db_services_query = $pdo->query("SELECT * FROM ".$prefix."_services ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

return $db_services_query;

?>