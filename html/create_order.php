<?php
session_start();
require_once("admin/db.inc.php");
require_once("admin/auth.php");

$current_user = auth();
if (!$current_user) {
    header("Location: login.php");
}

/* @TODO It is free to add helper functions here. */
/* ========== REGION START ========== */
/* ========== REGION END ========== */

/**
 * This function returns a digest based on a list of variables.
 * @return: a string denoted digest
 */
function gen_digest($array) {
  $digest = hash("sha256", implode(";", $array));
  return $digest;
}

/**
 * This function returns a UUID.
 * @return: a string denoted UUID
 * @see https://stackoverflow.com/questions/2040240/php-function-to-generate-v4-uuid
 */
function gen_uuid() {
  $data = random_bytes(16);
  return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

/**
 * Returns an valid order with digest and invoice.
 * @param $cart: an object representing items in cart (pid + quantity)
 * @return: a string representing the valid order
 */
function create_order($cart) {
    /* @TODO: Your Implementation here */
    /* ========== REGION START ========== */
    $items = array();
    $amount = floatval(0.);
    $digest_arr = array();
    $email = json_decode(file_get_contents("../secret.json"))->merchant_email;
    foreach ($cart as $item) {
        $pid = $item->pid;
        $prod = prod_fetchOne($pid);
        $pname = $prod['name'];
        $price = $prod['price'];
        $quantity = $item->quantity;
        $item_obj = array("name" => $pid . " - " . $pname , "unit_amount" => array("currency_code" => "HKD", "value" => floatval($price)), "quantity" => $quantity);
        array_push($items, $item_obj);
        $amount += $price * $quantity;
        array_push($digest_arr, $pid, $quantity, $price);
    }
    array_push($digest_arr, $amount, "HKD", $email, bin2hex(random_bytes(16)));
    $items = json_encode($items);
    
    $json = <<<HEREA
    {
        "purchase_units": [
            {
                "amount": {
                    "currency_code": "HKD",
                    "value": {$amount},
                    "breakdown": {
                        "item_total": {
                            "currency_code": "HKD",
                            "value": {$amount}
                        }
                    }
                },
                "items": {$items}
            }
        ]
    }
HEREA;

    $order = json_decode($json);

    $order->purchase_units[0]->custom_id = gen_digest($digest_arr);
    $order->purchase_units[0]->invoice_id = gen_uuid(); // invoice_id must be unique to avoid crashes.

    return json_encode($order);
    /* ========== REGION END ========== */
}


$json = file_get_contents("php://input");
$cart = json_decode($json);
echo create_order($cart);
?>
