<?php
session_start();
require_once('admin/db.php');
require_once('admin/auth.php');

$current_user = auth();
if (!$current_user) {
    header("Location: login.php");
}

/* @TODO It is free to add helper functions here. */
/* ========== REGION START ========== */
/* ========== REGION END ========== */

/**
    * This function saves the order into the database.
    * @param $order: an object containing order details
    */
function save_order($order) {
    /* @TODO Your Implementation Here. */
    /* ========== REGION START ========== */
    if (isset($_SESSION['auth']['userid'])) {
    	$user_id = filter_var($_SESSION['auth']['userid'], FILTER_VALIDATE_INT);
    } else {
        $user_id = filter_var($_COOKIE['auth']['userid'], FILTER_VALIDATE_INT);
    }
    $status = $order->status;
    $order_pu = $order->purchase_units[0];
    $inv_id = $order_pu->invoice_id;
    $amount = $order_pu->amount->value;
    $prod_list_obj = $order_pu->items;
    $prod_list = [];
    foreach ($prod_list_obj as $prod) {
        $pid = strtok($prod->name, " - ");
        array_push($prod_list, $pid);
    }
    $time = $order->update_time;
    $db = DB();
    $sql = "INSERT INTO orders (invoice_id, userid, prod_list, amount, status, time) VALUES (:inv_id, :userid, :prod_list, :amount, :status, :time)";
    $q = $db->prepare($sql);
    $q->bindParam(':inv_id', $inv_id);
    $q->bindParam(':userid', $user_id);
    $q->bindParam(':prod_list', implode(", ", $prod_list));
    $q->bindParam(':amount', $amount);
    $q->bindParam(':status', $status);
    $q->bindParam(':time', $time);
    if (!$q->execute()) {
        throw new Exception("Cannot save order into database.");
    }
    echo json_encode($order);
    /* ========== REGION END ========== */
}

$json = file_get_contents("php://input");
$order = json_decode($json);
save_order($order);
?>
