<!DOCTYPE html>
<?php
session_start();
require_once('admin/db.inc.php');
require_once('admin/user.inc.php');
require_once('admin/auth.php');
$db = DB();
$current_user = auth();
?>
<html>
    <head>
        <title>IERG4210 E-Mall</title>
        <link rel="stylesheet" href="css/index.css" type="text/css" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
    </head>

    <body onload="loadCart();">
        <?php include "header.php" ?>
        
        <div class="content">
            <div class="side-bar">
                <h3 class="side-bar-title">Category</h3>
                <ul>
                    <?php
                    $result = cat_fetchAll();
                    foreach ($result as $row) {
                        $cid = $row['cid'];
                        $name = htmlspecialchars($row['name']);
                        echo "<li class='nav-item'>
                            <a href='?cid={$cid}'>{$name}</a>
                        </li>";
                    };
                    ?>
                </ul>
            </div>
            <div class="display">
                <div id="hierarchy">
                    <a href="index.php">Home</a>
                    <?php $cid = $_GET["cid"];
                    if (isset($cid)) {
                        $result = cat_fetchOne($cid);
                        $cname = $result['name'];
                        echo " > <a href='index.php?cid={$cid}'>{$cname}</a>";
                    }
                    ?>
                </div>
                <?php
                if (isset($cid)) {
                    $result = prod_fetch_by_cid($cid);
                } else {
                    $result = prod_fetchAll();
                }
                if (count($result) != 0) {
                    echo "<div class='showcase'>
                        <ul id='product-list'>";
                    foreach ($result as $row) {
                        $pid = $row['pid'];
                        $pname = htmlspecialchars($row['name']);
                        $price = $row['price'];
                        $desc = htmlspecialchars($row['description']);
                        echo "<li id='{$pid}'>
                            <a href='product.php?pid={$pid}'><img class='thumbnail' src='img/{$pid}.png' /></a>
                            <div class='product-info'>
                                <a href='product.php?pid={$pid}'>{$pname}</a>
                                <span class='product-price'>\${$price}</span>
                                <button class='add-to-cart' type='button' onclick='addToCart(getPid(this))'>+</button>
                            </div>
                        </li>";
                    };
                    echo "</ul>
                    </div>";
                } else {
                    echo "<div>
                        <h3>Oops!</h3>
                        <p>Either there are no products in this category, or the category ID does not exist.</p>
                    </div>";
                }
                ?>
                </div>
            </div>
        </div>

        <?php include_once("footer.php"); ?>
    </body>
</html>
