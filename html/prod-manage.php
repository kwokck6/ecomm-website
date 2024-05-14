<!DOCTYPE html>
<?php
require_once 'admin/db.inc.php';
require_once 'admin/user.inc.php';
require_once 'admin/auth.php';
$current_user = auth();
if (!$current_user) {
    header("Location: index.php");
}
$pid = $_GET['pid'];
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
                $result = prod_fetchOne($pid);
                $cid = $result['cid'];
                $pname = $result['name'];
                $price = $result['price'];
                $desc = $result['description'];
                $inv = $result['inventory'];

                $result = cat_fetchAll();
                $options = '';
                foreach ($result as $row){
                    $selected = $row["cid"] === $cid ? "selected": "";
                    $options .= "<option value='{$row["cid"]}' {$selected}> {$row["name"]} </option>";
                }
                ?>
                <h3> Edit product: <?php echo $pname ?> </h3>
                <form id="prod_edit" method="POST" action="admin-process.php?action=prod_edit">
                    <input id="nonce" type="hidden" name="nonce" value="<?php echo get_nonce('prod_edit') ?>" />
                    <input id="pid" type="hidden" name="pid" value="<?php echo $pid ?>" />
                    <label for="pname"> Product Name <span class="required-mark">*</span></label>
                    <div>
                        <input id="pname" type="text" name="pname" value="<?php echo $pname ?>" onchange="onValueChanged(this.id, this.value);" pattern="^[\w\- ]+$" required />
                    </div>
                    <label for="cid"> Category name <span class="required-mark">*</span></label>
                    <div>
                        <select id="cid" name="cid">
                            <?php echo $options ?>
                        </select>
                    </div>
                    <label for="price"> Price <span class="required-mark">*</span></label>
                    <div>
                        <input id="price" type="float" name="price" value="<?php echo $price ?>" onchange="onValueChanged(this.id, this.value);" pattern="^\d+\.?\d*$" required />
                    </div>
                    <label for="desc"> Description <span class="required-mark">*</span></label>
                    <div>
                        <textarea id="desc" type="text" name="description" onchange="onValueChanged(this.id, this.value);" required><?php echo $desc ?></textarea>
                    </div>
                    <label for='image'> Image <span class='required-mark'>*</span></label>
                        <div>
                            <input type='file' name='image' accept='image/jpeg, image/png, image/gif' required />
                        </div>
                    <input type="submit" value="Submit"/>
                </form>
            </div>
        </div>
        
        <?php include "footer.php"; ?>
    </body>
</html>
