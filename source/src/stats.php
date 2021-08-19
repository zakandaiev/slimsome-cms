<? require_once("partials/header.php"); ?>

<?php if(!empty($_GET["player"])): ?>
  <? require_once("partials/stats_single.php"); ?>
<? else: ?>
  <? require_once("partials/stats_all.php"); ?>
<? endif; ?>

<? require_once("partials/footer.php"); ?>