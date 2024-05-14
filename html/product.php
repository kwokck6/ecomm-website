<!DOCTYPE html>
<?php 
$db = new PDO("sqlite:../cart.db");
$db->query("PRAGMA foreign_keys = ON");
$pid = $_GET["pid"];
?>
<html>
    <head>
        <title>IERG4210 E-Mall</title>
        <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css"> -->
        <link rel="stylesheet" href="css/index.css" type="text/css" />
        <link rel="stylesheet" href="css/product.css" type="text/css" />
        <!-- <link rel="script" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"> -->
        <meta name="viewport" content="width=device-width, initial-scale=1">
    </head>

    <body onload="loadCart();">
        <?php include "header.php" ?>
        
        <div class="content">
            <div class="side-bar">
                <h3 class="side-bar-title">Category</h3>
                <ul> 
                    <?php
                    $q = $db->prepare("SELECT * FROM categories");
                    $q->execute();
                    $result = $q->fetchAll();
                    foreach ($result as $row) {
                        $cid = $row['cid'];
                        $cname = htmlspecialchars($row['name']);
                        echo "<li class=\"nav-item\">
                        <a href=\"index.php?cid={$cid}\">{$cname}</a>
                        </li>";
                    };
                    ?>
                </ul>
            </div>
            <div class="display">
                <?php
                $q = $db->prepare("SELECT * FROM products WHERE pid = :pid");
                $q->bindParam(":pid", $pid);
                $q->execute();
                $result = $q->fetch();
                if (count($result) != 0) {
                    $pid = $result["pid"];
                    $cid = $result["cid"];
                    $pname = $result["name"];
                    $price = $result["price"];
                    $desc = $result["description"];
                    $inventory = $result["inventory"];
                    $q = $db->prepare("SELECT * FROM categories WHERE cid = :cid");
                    $q->bindParam(":cid", $cid);
                    $q->execute();
                    $result = $q->fetch();
                    $cname = $result["name"];
                    echo "<div id=\"hierarchy\">
                    <a href=\"index.php\">Home</a> >
                    <a href=\"index.php?cid={$cid}\">{$cname}</a> >
                    <a href=\"product.php?pid={$pid}\">{$pname}</a>
                    </div>
                    <div class=\"product-image\">
                        <img src=\"img/{$pid}.png\" />
                    </div>
                    <div class=\"product-inventory\">
                        <p>Name: {$pname}</p>
                        <p>Description: {$desc} </p>
                        <p>Unit Price: \${$price}</p>";
                        if ($inventory > 3) {
                            echo "<p>Inventory: {$inventory}</p>";
                        } else {
                            echo "<p>Inventory: <span class=\"warn-remaining\">Only {$inventory} left!</span></p>";
                        };
                        echo "<button type=\"button\" onclick=\"addToCart({$pid});\">Add to cart</button>
                    </div>";
                } else {
                    echo "<div>
                        <span class=\"warning\">Oops!</span>
                        <p>Your search returns no results. Please check whether the product ID is correct.</p>
                    </div>";
                };
                ?>
            </div>
        </div>

        <?php include "footer.php"; ?>
    </body>
</html>
