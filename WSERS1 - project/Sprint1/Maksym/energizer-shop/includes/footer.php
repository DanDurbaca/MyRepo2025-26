<?php
  function Footer($pathToLogo = "") {
    global $brand;

    $footerTitle = t("footer.footer-title", ["brand" => $brand]);
?>

    <div id="footer">
      <div id="description">
        <div id="logo"><a href="#"><img src=<?=$pathToLogo?> alt="logo"></a></div>

        <div id="description-title"><a href="#"><h2 class="footer-title"><?= $footerTitle;?></h2></a></div>

        <div id="description-text"><p><?= t("footer.footer-description");?></p></div>
      </div>

      <div id="Navigation">
        <div><h2 class="footer-title"><?= t("footer.footer-go");?></h2></div>

        <ul>
          <li class="footer-list-item"><a href="#"><?= t("navigation.nav-home");?></a></li>
          <li class="footer-list-item"><a href="#"><?= t("navigation.nav-market");?></a></li>
          <li class="footer-list-item"><a href="#"><?= t("navigation.nav-contact");?></a></li>
        </ul>
      </div>

      <div id="social-media">
        <div><h2 class="footer-title"><?= t("footer.footer-social");?></h2></div>

        <ul>
          <li class="footer-list-item"><a href="#">Reddit</a></li>
          <li class="footer-list-item"><a href="#">Instagram</a></li>
          <li class="footer-list-item"><a href="#">Facebook</a></li>
        </ul>
      </div>
    </div>

<?php
  }
?>
