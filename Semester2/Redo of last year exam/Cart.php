<?php
include("commonCode.php");

if (count($_SESSION["shopCart"]) == 0) {
    header("Location: Products.php");
}
