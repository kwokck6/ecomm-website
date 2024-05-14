<!DOCTYPE html>
<?php
require_once 'admin/db.inc.php';
require_once 'admin/user.inc.php';
require_once 'admin/auth.php';
$current_user = auth();
if (!$current_user) {
	header("Location: index.php");
}
$cid = $_GET['cid'];
?>

<html>
    <head>
        <title>IERG4210 E-Mall</title>
        <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css"> -->
        <link rel="stylesheet" href="css/index.css" type="text/css" />
        <link rel="stylesheet" href="css/admin.css" type="text/css" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <!-- <link rel="script" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"> -->
        <meta name="viewport" content="width=device-width, initial-scale=1">
    </head>

    <body onload="loadCart();">
        <?php include "header.php"; ?>

        <div class="content">
            <div class="side-bar">
                <h3 class="side-bar-title">Menu</h3>
                <ul>
                    <li class="nav-item">
                        <a href="admin.php?op=cat_add">Create new category</a>
                    </li>
                    <li class="nav-item">
                        <a href="admin.php?op=cat_edit_del">Manage categories</a>
                    </li>
                    <li class="nav-item">
                        <a href="admin.php?op=prod_add">Add new product</a>
                    </li>
                    <li class="nav-item">
                        <a href="admin.php?op=prod_edit_del">Manage products</a>
                    </li>
                    <li class="nav-item">
                        <a href="index.php">Home</a>
                    </li>
                </ul>
            </div>

            <div class="display">
                <?php
                $result = cat_fetchOne($cid);
                $cid = $result['cid'];
                $cname = $result['name'];
                ?>

                <form id="cat_edit" method="POST" action="admin-process.php?action=cat_edit">
                    <h3> Edit category: <?php echo $cname ?> </h3>
                    <input id="cid" type="hidden" name="cid" value="<?php echo $cid ?>" onchange="onValueChanged(this.id, this.value);" />
                    <label for="cname"> Category name <span class="required-mark">*</span></label>
                    <div> 
                        <input id="cname" type="text" name="cname" value="<?php echo $cname ?>" onchange="onValueChanged(this.id, this.value);" pattern="^[\w\- ]+$" required />
                    </div>
                    <input type="submit" value="Submit"/>
                </form>
            </div>
        </div>
        
        <?php include "footer.php"; ?>
    </body>
</html>
