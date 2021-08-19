<script src="js/main.js"></script>

<?php if(!empty($GLOBALS["site_analytics_gtag"])): ?>
  <script async src="https://www.googletagmanager.com/gtag/js?id=<?= $GLOBALS["site_analytics_gtag"] ?>"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', '<?= $GLOBALS["site_analytics_gtag"] ?>');
  </script>
<? endif; ?>