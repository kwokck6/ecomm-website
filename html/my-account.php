<!DOCTYPE html>
<?php
session_start();
require_once('admin/db.inc.php');
require_once('admin/user.inc.php');
require_once('admin/auth.php');

$current_user = auth();
if (!$current_user) {
    header("Location: login.php");
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
        <?php include "header.php" ?>

        <div class="content">
            <div class="side-bar">
                <h3 class="side-bar-title">Menu</h3>
                <ul>
                    <li class="nav-item">
                        <a href="my-account.php?op=change_password">Change password</a>
                    </li>
                    <li class="nav-item">
                        <a href="my-account.php?op=check_orders">Check orders</a>
                    </li>
                    <li class="nav-item">
                        <a href="my-account.php?op=delete_account">Delete account</a>
                    </li>
		    <li class="nav-item">
			<a href="index.php">Home</a>
		    </li>
                </ul>
            </div>
            <div class="display">
                <?php
                $op = htmlspecialchars($_GET['op']);
                switch ($op):
                    case 'change_password': ?>
                    <h3>Change password</h3>
                    <form method="post" action="user-process.php?action=user_change_password">
                        <input type="hidden" name="nonce" value="<?php echo get_nonce('change_password') ?>" />
                        <label>Old password</label>
                        <div>
                            <input type="password" name="old_pwd" pattern="^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[a-zA-Z]).{8,}$" />
                        </div>
                        <label>New password</label>
                        <div>
                            <input type="password" name="new_pwd" pattern="^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[a-zA-Z]).{8,}$" />
                        </div>
                        <label>Confirm new password</label>
                        <div>
                            <input type="password" name="confirm_pwd" pattern="^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[a-zA-Z]).{8,}$" />
                        </div>
                        <input type="submit" value="Submit" />
                    </form>
                <?php break;
                    case 'check_orders': ?>
		    <h3>Check Orders</h3>
                    <p>You can check the most recent 5 orders below.</p>
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
                        $result = user_check_orders();
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
                         }
                         ?>
                    </div>
                <?php break;
                    case 'delete_account': ?>
                    <h3>Delete Account</h3>
                    <p class="warning">Warning: You cannot undo this action! Please confirm deletion by typing your password below.</p>
                    <form method="post" action="user-process.php?action=user_delete">
                        <input type="hidden" name="nonce" value="<?php echo get_nonce('user_delete') ?>" />
                        <label>Password</label>
                        <div>
                            <input type="password" name="old_pwd" pattern="^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[a-zA-Z]).{8,}$" />
                        </div>
                        <input type="submit" value="Submit" />
                    </form>
                <?php
                    break;
                    default:
                        echo "<h3>Welcome, {$user}!</h3><p>You can manange your account by choosing one operation from the side-bar</p>";
                    endswitch;
                ?>
            </div>
        </div>

        <?php include "footer.php"; ?>
    </body>
</html>
