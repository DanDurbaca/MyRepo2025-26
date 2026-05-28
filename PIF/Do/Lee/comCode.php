<?php
session_start();
if (!isset($_SESSION["UserLoggedIn"])) {
  $_SESSION["userLoggedIn"] = false;
}

if (isset($_SESSION["language"])) {
} else {
  $_SESSION["language"] = "EN";
}

if (isset($_GET["language"])) {
  $_SESSION["language"] = $_GET["language"];
}


//SQL Trans
$conn = mysqli_connect("localhost", "root", "", "pif_db");
$sqlSelect = $conn->prepare("select * from translation");
$sqlSelect->execute();
$result = $sqlSelect->get_result();
while ($row = $result->fetch_assoc()) {
  if ($_SESSION["language"] == "EN") {
    $text[$row["textID"]] = $row["enText"];
  } else if ($_SESSION["language"] == "GER") {
    $text[$row["textID"]] = $row["gerText"];
  }
}



function NavigationBar($status)
{
  global $text;
  // Build language switch link based on current language and page status
  $langHref = './index.php?language=EN';
  if ($_SESSION["language"] == "EN") {
    // switch to GER
    if ($status == "home") {
      $langHref = "./index.php?language=GER";
    } elseif ($status == "game") {
      $langHref = "./stationPage.php?language=GER";
    } elseif ($status == "register") {
      $langHref = "./register.php?language=GER";
    } elseif ($status == "cart") {
      $langHref = "./basket.php?language=GER";
    } elseif ($status == "addPage") {
      $langHref = "./xlmr8.php?language=GER";
    }
  } else {
    // currently GER -> switch to EN
    if ($status == "home") {
      $langHref = "./index.php?language=EN";
    } elseif ($status == "game") {
      $langHref = "./stationPage.php?language=EN";
    } elseif ($status == "register") {
      $langHref = "./register.php?language=EN";
    } elseif ($status == "cart") {
      $langHref = "./basket.php?language=EN";
    } elseif ($status == "addPage") {
      $langHref = "./xlmr8.php?language=EN";
    }
  }

?>

  <nav class="site-nav">
    <div class="nav-inner center">
      <a class="brand" href="./index.php"><?= htmlspecialchars($text["navInd"]) ?></a>

      <button class="nav-toggle" aria-expanded="false" aria-label="Toggle navigation">☰</button>

      <ul class="nav-links">
        <!-- Removed duplicate home/dashboard link (brand already points to home) -->
        <?php if (isset($_SESSION["UserLoggedIn"]) || isset($_SESSION["adminLoggedIn"])) { ?>
          <li><a href="./stationPage.php" <?php if ($status == "game") {
                                            echo 'class="active"';
                                          } ?>><?= htmlspecialchars($text["navSta"]) ?></a></li>
        <?php } ?>
        <?php if (isset($_SESSION["adminLoggedIn"])) { ?>
          <li><a href="./adminStation.php">Edit Stations</a></li>
          <li><a href="./xlmr8.php">Edit Users</a></li>
        <?php } ?>
        <?php if (isset($_SESSION["UserLoggedIn"]) || isset($_SESSION["adminLoggedIn"])) { ?>
          <li><a href="./collections.php"><?= htmlspecialchars($text["navCol"]) ?></a></li>
        <?php } ?>
        <li><a href="./register.php" <?php if ($status == "register") {
                                        echo 'class="active"';
                                      } ?>><?php
                                            if (isset($_SESSION["UserLoggedIn"])) {
                                              echo htmlspecialchars($_SESSION["userName"]);
                                            } else if (isset($_SESSION["adminLoggedIn"])) {
                                              echo "Admin";
                                            } else {
                                              echo htmlspecialchars($text["navReg"]);
                                            }
                                            ?></a></li>
        <li><a class="lang-link" href="<?= $langHref ?>"><?= htmlspecialchars($text["navLan"]) ?></a></li>
        <?php if (isset($_SESSION["UserLoggedIn"]) || isset($_SESSION["adminLoggedIn"])) { ?>
          <li>
            <form action="./register.php" method="POST" style="margin:0">
              <button type="submit" name="Logout" class="icon-btn" title="<?= htmlspecialchars($text["regLogOut"]) ?>" style="vertical-align:middle">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="width:18px;height:18px;display:block">
                  <path d="M16 17L21 12L16 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M21 12H9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M13 5H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              </button>
            </form>
          </li>
        <?php } ?>
      </ul>
    </div>
  </nav>
  <script>
    (function() {
      try {
        var nav = document.querySelector('.site-nav');
        if (!nav) return;
        var btn = nav.querySelector('.nav-toggle');
        btn.addEventListener('click', function() {
          var expanded = this.getAttribute('aria-expanded') === 'true';
          this.setAttribute('aria-expanded', expanded ? 'false' : 'true');
          nav.classList.toggle('open');
        });
      } catch (e) {
        /* fail silently on older pages */
      }
    })();
  </script>

<?php
}
?>