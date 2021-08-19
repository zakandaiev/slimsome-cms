<?php

$db_services_query = $pdo->query("SELECT * FROM ".$prefix."_services WHERE enabled IS TRUE and buyable IS TRUE ORDER BY flags DESC")->fetchAll(PDO::FETCH_ASSOC);

return $db_services_query;

?>