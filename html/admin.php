<!DOCTYPE html>
<?php
require_once('admin/db.inc.php');
require_once('admin/user.inc.php');
require_once('admin/auth.php');
$current_user = auth();
$is_admin = $_SESSION['auth']['is_admin'];
if (!$current_user || $is_admin != 1) {
    header("Location: index.php");
}
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
                        <a href="admin.php?op=check_orders">Check orders</a>
                    </li>
                    <li class="nav-item">
                        <a href="index.php">Home</a>
                    </li>
                </ul>
            </div>

            <div class="display">
                <?php
                if(isset($_GET['error'])){
                    echo "<div class='error-msg'>{$_GET['error']}</div>";
                }
                if(isset($_GET['success'])){
                    echo "<div class='success-msg'>{$_GET['success']}</div>";
                }

                $op = $_REQUEST['op'];

                switch ($op):
                    case "cat_add": ?>
                    <form id='cat_add' method='POST' action='admin-process.php?action=cat_insert' enctype='multipart/form-data'>
                        <h3> Create new category</h3>
                        <input type="hidden" name="nonce" value=<?php echo get_nonce('cat_insert') ?> />
                        <label for='cname'> Category name <span class='required-mark'>*</span></label>
                        <div>
                            <input id='cname' type='text' name='cname' pattern='^[\w\-]+$' required />
                        </div>
                        <input type='submit' value='Submit' />
                    </form>
                <?php break;
                    case "cat_edit_del": ?>
                    <form method='post' action='admin-process.php?action=cat_delete'>
                        <input type="hidden" name="nonce" value=<?php echo get_nonce('cat_delete') ?> />
                        <h3>Manage categories</h3>
                        <div id='cat_edit_del'>
                            <div id='cat-headings'>
                                <div class='cid'>ID</div>
                                <div class='cname'>Category Name</div>
                                <div class='cedit'>Edit</div>
                                <div class='cdelete'>Delete</div>
                            </div>
                        <?php
                        $result = cat_fetchAll();
                        foreach ($result as $row) {
                            $cid = $row['cid'];
                            $cname = $row['name'];
                            echo "<div class='cat-records'>
                                <div class='cid'>
                                    <input type='hidden' name='cid' value='{$cid}' />
                                    {$cid}
                                </div>
                                <div class='cname'>
                                    {$cname}
                                </div>
                                <div class='cedit'>
                                    <a href='cat-manage.php?cid={$cid}'><span class='fa fa-pencil'></span></a>
                                </div>
                                <div class='cdelete'>
                                    <button type='submit'><span class='fa fa-trash'></span></button>
                                </div>
                            </div>";
                        }; ?>
                        </div>
                    </form>
                    <?php break;
                    case "prod_add":
                        $result = cat_fetchAll();
                        $options = '';
                        foreach ($result as $row){
                            $options .= "<option value=\"{$row["cid"]}\"> {$row["name"]} </option>";
                        }
                    ?>
                    <form id='prod_add' method='POST' action='admin-process.php?action=prod_insert' enctype='multipart/form-data'>
                        <input type="hidden" name="nonce" value=<?php echo get_nonce('prod_insert') ?> />
                        <h3> Add new product</h3>
                        <label for='pname'> Product Name <span class='required-mark'>*</span></label>
                        <div>
                            <input id='pname' type='text' name='pname' pattern='^[\w\- ]+$' required />
                        </div>
                        <label for='cid'> Category name <span class='required-mark'>*</span></label>
                        <div>
                            <select id='cid' name='cid'>
                                <?php echo $options ?>
                            </select>
                        </div>
                        <label for='price'> Price <span class='required-mark'>*</span></label>
                        <div>
                            <input id='price' type='text' name='price' pattern='^\d+\.?\d*$' required />
                        </div>
                        <label for='desc'> Description <span class='required-mark'>*</span></label>
                        <div>
                            <textarea id='desc' type='text' name='description' required></textarea>
                        </div>
                        <label for='image'> Image <span class='required-mark'>*</span></label>
                        <div>
                            <input type='file' name='file' accept='image/jpeg, image/png, image/gif' required />
                        </div>
                        <input type='submit' value='Submit'/>
                    </form>
                    <?php break;
                    case "prod_edit_del": ?>
                    <h3>Manage products</h3>
                    <form method='post' action='admin-process.php?action=prod_delete' enctype='multipart/form-data'>    
                        <input type="hidden" name="nonce" value=<?php echo get_nonce('prod_delete') ?> />
                        <div id='prod_edit_del'>
                            <div id='prod-headings'>
                                <div class='pid'>ID</div>
                                <div class='pname'>Product Name</div>
                                <div class='pcid'>Category</div>
                                <div class='price'>Price</div>
                                <div class='desc'>Description</div>
                                <div class='inv'>Inventory</div>
                                <div class='pedit'>Edit</div>
                                <div class='pdelete'>Delete</div>
                            </div>
                        <?php
                        $result = prod_fetchAll();
                        foreach ($result as $row) {
                            $pid = $row['pid'];
                            $cid = $row['cid'];
                            $pname = htmlspecialchars($row['name']);
                            $price = $row['price'];
                            $desc = htmlspecialchars($row['description']);
                            $inv = $row['inventory'];
                            echo "<div class='prod-records'>
                                <div class='pid'>
                                    <input type='hidden' name='pid' value='{$pid}' />
                                    {$pid}
                                </div>
                                <div class='pname'>
                                    {$pname}
                                </div>
                                <div class='pcid'>
                                    {$cid}
                                </div>
                                <div class='price'>
                                    {$price}
                                </div>
                                <div class='desc'>
                                    {$desc}
                                </div>
                                <div class='inv'>
                                    {$inv}
                                </div>
                                <div class='pedit'>
                                    <a href='prod-manage.php?pid={$pid}'><span class='fa fa-pencil'></a>
                                </div>
                                <div class='pdelete'>
                                    <button type='submit'><span class='fa fa-trash'></button>
                                </div>
                            </div>";
                        }; ?>
                        </div>
                    </form>
                    <?php break;
                    case "check_orders":
                    ?>
                    <h3>Check orders</h3>
                    <p>You can check all orders below.</p>
                        <div id='prod_edit_del'>
                            <div id='prod-headings'>
                                <div class='pid'>Invoice ID</div>
                                <div class='pname'>User ID</div>
                                <div class='pcid'>Product ID list</div>
                                <div class='price'>Amount</div>
                                <div class='desc'>Status</div>
                                <div class='inv'>Time</div>
                            </div>
                        <?php
                        $result = admin_check_orders();
                        foreach ($result as $row) {
                            $inv_id = $row['invoice_id'];
                            $user_id = $row['userid'];
                            $prod_list = htmlspecialchars($row['prod_list']);
                            $amount = $row['amount'];
                            $status = htmlspecialchars($row['status']);
                            $time = $row['time'];
                            echo "<div class='prod-records'>
                                <div class='pid'>
                                    {$inv_id}
                                </div>
                                <div class='pname'>
                                    {$user_id}
                                </div>
                                <div class='pcid'>
                                    {$prod_list}
                                </div>
                                <div class='price'>
                                    {$amount}
                                </div>
                                <div class='desc'>
                                    {$status}
                                </div>
                                <div class='inv'>
                                    {$time}
                                </div>
                            </div>";
                        }; ?>
                        </div>
                    <?php break;
                    default:
                        echo "<h3>Welcome to the administrator's page.</h3><p>Choose one operation from the side-bar</p>";
                    endswitch;
                ?>
            </div>
        </div>

        <?php include "footer.php" ?>
    </body>
</html>
