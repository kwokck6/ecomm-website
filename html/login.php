<!DOCTYPE html>
<?php
session_start();
require_once('admin/db.inc.php');
require_once('admin/user.inc.php');
require_once('admin/auth.php');
$current_user = auth();
if ($current_user) {
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
        <?php include "header.php" ?>

        <div class="content">
            <div class="side-bar">
                <h3 class="side-bar-title">Menu</h3>
                <ul>
                    <li class="nav-item">
                        <a href="login.php">Log in</a>
                    </li>
                    <li class="nav-item">
                        <a href="signup.php">Sign up</a>
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
                ?>
                <h3>Log In</h3>
                <form method="post" action="user-process.php?action=user_login">
                    <input type="hidden" name="nonce" value="<?php echo get_nonce('user_login') ?>" />
                    <label>User name</label>
                    <div>
                        <input type="email" name="email" pattern="^[\w\-\.]+@([\w\-]+\.)+[\w\-]{2,4}$" />
                    </div>
                    <label>Password</label>
                    <div>
                        <input type="password" name="pwd" pattern="^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[a-zA-Z]).{8,}$" />
                    </div>
                    <input type="submit" value="Submit" />
                </form>
            </div>
        </div>

        <?php include "footer.php"; ?>
    </body>
</html>
