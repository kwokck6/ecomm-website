<header>
    <h1>IERG4210 E-Mall</h1>
    <h2>We provide you the best shopping experience!</h2>
    <div id="dashboard">
        <p>Welcome,
            <?php
                if ($current_user) {
                    $user = htmlspecialchars(strtok($current_user, "@"));
                    if ($_SESSION['auth']['is_admin'] != 1) {
                        echo "{$user}! (<a href='my-account.php'>My account</a>, <a href='user-process.php?action=user_logout'>Log out</a>)";
                    } else {
                        echo "{$user}! (<a href='admin.php'>Admin panel</a>, <a href='user-process.php?action=user_logout'>Log out</a>)";
                    }
                } else {
                    echo "guest! (<a href='login.php'>Log in</a>, <a href='signup.php'>Sign up</a>)";
                }
            ?>
        </p>
        <button class="shopping-cart-hover">Cart</button>
        <div id="shopping-cart">
            <h3>Shopping Cart</h3>
            <div id="cart-content">
                <div id="cart-headings">
                    <div class="item">Item</div>
                    <div class="unit-price">Unit Price</div>
                    <div class="quantity">Quantity</div>
                    <div class="subtotal">Subtotal</div>
                </div>
                <p id="total">Total: $</p>
            </div>
            <div id="cart-button">
                <?php include_once "payment.php" ?>
                <button id="clear" onclick="confirmClearCart();">Clear</button>
            </div>
        </div>
    </div>
</header>
