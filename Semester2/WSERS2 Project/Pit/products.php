<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($arrayOfTranslations["ProductsBtn"] ?? "Products", ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="style.css?<?= time(); ?>">
</head>

<body>
    <?php
    include_once("nav.php");

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_to_cart"])) {

        if (
            empty($_SESSION["UserLogged"]) ||
            $_SESSION["UserLogged"] !== true ||
            (!empty($_SESSION["IsAdmin"]) && $_SESSION["IsAdmin"] === true)
        ) {
            header("Location: products.php?lang=" . $language);
            exit;
        }

        $productName = trim($_POST["product_name"] ?? "");
        $quantity = (int)($_POST["quantity"] ?? 0);

        if ($productName !== "" && $quantity > 0) {
            $sqlProduct = $connection->prepare("SELECT * FROM Products WHERE ProductNameEN = ?");
            $sqlProduct->bind_param("s", $productName);
            $sqlProduct->execute();
            $resultProduct = $sqlProduct->get_result();

            if ($row = $resultProduct->fetch_assoc()) {
                if (isset($_SESSION["cart"][$productName])) {
                    $_SESSION["cart"][$productName]["quantity"] += $quantity;
                } else {
                    $_SESSION["cart"][$productName] = [
                        "name_en" => $row["ProductNameEN"],
                        "name_de" => $row["ProductNameDE"],
                        "price" => (float)$row["Price"],
                        "image" => $row["ImageLink"],
                        "quantity" => $quantity
                    ];
                }
            }
        }

        header("Location: products.php?lang=" . $language);
        exit;
    }

    NavigationBar($arrayOfTranslations["ProductsBtn"] ?? "Products");
    ?>
    <header>
        <h1><?= $arrayOfTranslations["ProductsTitle"] ?></h1>
        <h2><?= $arrayOfTranslations["ProductsSubTitle"] ?></h2>
    </header>

    <div class="Products">
        <?php
        $connection = new mysqli("localhost", "root", "", "4PageWebsite");

        $sqlQuery = $connection->prepare("SELECT * from Products");

        $sqlQuery->execute();
        $result = $sqlQuery->get_result();

        while ($row = $result->fetch_assoc()) {
            if (count($row) == 6) {
        ?>
                <div class="OneProduct">
                    <div class="TitleProduct">
                        <?= htmlspecialchars($row[($language == "EN") ? "ProductNameEN" : "ProductNameDE"]) ?>
                    </div>

                    <img class="ProductsImage" src="./WebsiteImages/<?= htmlspecialchars($row["ImageLink"]) ?>">

                    <div><?= htmlspecialchars($row["Price"]) ?> EUR/g</div>

                    <div>
                        <?= htmlspecialchars($row[($language == "EN") ? "DescriptionEN" : "DescriptionDE"]) ?>
                    </div>

                    <?php if (
                        !empty($_SESSION["UserLogged"]) &&
                        $_SESSION["UserLogged"] === true &&
                        (empty($_SESSION["IsAdmin"]) || $_SESSION["IsAdmin"] !== true)
                    ) { ?>
                        <form method="POST">
                            <div class="weed-select">
                                <select name="quantity" required>
                                    <option value="0"><?= $arrayOfTranslations["ProductsSelect"] ?>:</option>
                                    <option value="1">1g <?= $arrayOfTranslations["ProductsQuantity"] ?> <?= $row["Price"] ?> EUR</option>
                                    <option value="2">2g <?= $arrayOfTranslations["ProductsQuantity"] ?> <?= (float)$row["Price"] * 2 ?> EUR</option>
                                    <option value="3">3g <?= $arrayOfTranslations["ProductsQuantity"] ?> <?= (float)$row["Price"] * 3 ?> EUR</option>
                                    <option value="4">4g <?= $arrayOfTranslations["ProductsQuantity"] ?> <?= (float)$row["Price"] * 4 ?> EUR</option>
                                    <option value="5">5g <?= $arrayOfTranslations["ProductsQuantity"] ?> <?= (float)$row["Price"] * 5 ?> EUR</option>
                                    <option value="6">6g <?= $arrayOfTranslations["ProductsQuantity"] ?> <?= (float)$row["Price"] * 6 ?> EUR</option>
                                    <option value="7">7g <?= $arrayOfTranslations["ProductsQuantity"] ?> <?= (float)$row["Price"] * 7 ?> EUR</option>
                                    <option value="8">8g <?= $arrayOfTranslations["ProductsQuantity"] ?> <?= (float)$row["Price"] * 8 ?> EUR</option>
                                    <option value="9">9g <?= $arrayOfTranslations["ProductsQuantity"] ?> <?= (float)$row["Price"] * 9 ?> EUR</option>
                                    <option value="10">10g <?= $arrayOfTranslations["ProductsQuantity"] ?> <?= (float)$row["Price"] * 10 ?> EUR</option>
                                </select>
                            </div>

                            <input type="hidden" name="product_name" value="<?= htmlspecialchars($row["ProductNameEN"]) ?>">

                            <button type="submit" name="add_to_cart" class="cart-btn">
                                <?= $arrayOfTranslations["AddToCartBtn"] ?? "Add to cart" ?>
                            </button>
                        </form>
                    <?php } ?>
                </div>
        <?php
            }
        }
        ?>
    </div>
</body>

</html>