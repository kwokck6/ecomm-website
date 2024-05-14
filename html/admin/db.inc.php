<?php
session_start();
require_once("db.php");
require_once("auth.php");

// FIXME: turn all Exception expressions to messages
// TODO: retain regex for client-side validation
// FIXME: how to verify nonce?

const NAME_REGEX = '/^[\w\-\(\)\ ]+$/';

// CATEGORY-RELATED FUNCTIONS
function cat_fetchAll() {
    // DB manipulation
    global $db;
    $db = DB();
    
    $q = $db->prepare("SELECT * FROM categories;");
    if ($q->execute()) {
        return $q->fetchAll();
    }
}

function cat_fetchOne($cid) {
    // DB manipulation
    global $db;
    $db = DB();
    
    if (!($cid = filter_var($cid, FILTER_VALIDATE_INT)))
        throw new Exception("Invalid product ID");
    $q = $db->prepare("SELECT * FROM categories WHERE cid = :cid");
    $q->bindParam(":cid", $cid);
    if ($q->execute()) {
        return $q->fetch();
    }
}

function cat_insert() {
    global $db;
    $db = DB();
    
    // input validation or sanitization
    if (!preg_match(NAME_REGEX, $_POST['cname'])) {
        throw new Exception("{$_POST['cname']}: invalid category name");
    }

    $cname = htmlspecialchars($_POST['cname']);
    $sql = 'INSERT INTO categories (name) VALUES (:cname)';
    $q = $db->prepare($sql);
    $q->bindParam(':cname', $cname);
    if ($q->execute()) {
        // FIXME: do not show message through URL parameters
        header("Location: admin.php?success=* New category '{$cname}' created");
    } else {
        // FIXME: do not show message through URL parameters
        header("Location: admin.php?error=* Cannot create category '{$cname}'");
    }
}

function cat_edit() {
    global $db;
    $db = DB();
    
    // input validation
    if (!($cid = filter_input(INPUT_POST, 'cid', FILTER_VALIDATE_INT))) {
        throw new Exception("Invalid category ID");
    }
    if (!preg_match(NAME_REGEX, $_POST['cname'])) {
        throw new Exception("Invalid category name");
    }
    $cname = htmlspecialchars($_POST['cname']);
    $sql = "UPDATE categories SET name = :cname WHERE cid = :cid";
    $q = $db->prepare($sql);
    $q->bindParam(":cname", $cname);
    $q->bindParam(":cid", $cid);
    if ($q->execute()) {
        // FIXME: do not show message through URL parameters
        header("Location: admin.php?success=* Category is updated to '{$cname}'");
    } else {
        // FIXME: do not show message through URL parameters
        header("Location: admin.php?error=* Cannot edit category '{$cname}'");
    }
}

function cat_delete() {
    global $db;
    $db = DB();
    
    // input validation or sanitization
    if (!($cid = filter_input(INPUT_POST, 'cid', FILTER_VALIDATE_INT))) {
        throw new Exception("Invalid category ID");
    }

    $sql = "SELECT name FROM categories WHERE cid = :cid";
    $q = $db->prepare($sql);
    $q->bindParam(':cid', $cid);
    if ($q->execute()) {
        $cname = $q->fetch()['name'];
    } else {
        // FIXME: do not show message through URL parameters
        header("Location: admin.php?error=* Category does not exist");
    }

    if (!prod_delete_by_cid($db, $cid)) {
        throw new Exception("cannot delete product by cid");
    }
    
    $sql = "DELETE FROM categories WHERE cid = :cid";
    $q = $db->prepare($sql);
    $q->bindParam(':cid', $cid);
    if ($q->execute()) {
        // FIXME: do not show message through URL parameters
        header("Location: admin.php?success=* Category {$cname} deleted");
    } else {
        // FIXME: do not show message through URL parameters
        header("Location: admin.php?error=* Cannot delete category {$cname}");
    }
}

// PRODUCT-RELATED FUNCTIONS
// Since this form will take file upload, we use the tranditional (simpler) rather than AJAX form submission.
// Therefore, after handling the request (DB insert and file copy), this function then redirects back to admin.html
function prod_insert() {
    // DB manipulation
    global $db;
    $db = DB();

    // input validation
    if (!($cid = filter_input(INPUT_POST, 'cid', FILTER_VALIDATE_INT))) {
        throw new Exception("Invalid category ID");
    }
    if (!preg_match(NAME_REGEX, $_POST['pname'])) {
        throw new Exception("Invalid product name");
    }
    if (!($price = filter_input(INPUT_POST, "price", FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION))) {
        throw new Exception("Invalid price");
    }
    if (!($desc = htmlspecialchars($_POST['description']))) {
        throw new Exception("Text description is required");
    }

    $pname = htmlspecialchars($_POST['pname']);

    // Copy the uploaded file to a folder which can be publicly accessible at incl/img/[pid].ext
    if ($_FILES["file"]["error"] == 0
        && in_array($_FILES["file"]["type"], array('image/jpeg', 'image/png', 'image/gif'))
        && in_array(mime_content_type($_FILES["file"]["tmp_name"]), array('image/jpeg', 'image/png', 'image/gif'))
        && $_FILES["file"]["size"] <= 5000000) {

        $sql = "INSERT INTO products (cid, name, price, description) VALUES (:cid, :pname, :price, :desc)";
        $q = $db->prepare($sql);
        $q->bindParam(':cid', $cid);
        $q->bindParam(':pname', $pname);
        $q->bindParam(':price', $price);
        $q->bindParam(':desc', $desc);
        if ($q->execute()) {
            $pid = $db->lastInsertId();
            $ext = '.png';
            // Note: Take care of the permission of destination folder (hints: current user is apache)
            if (move_uploaded_file($_FILES["file"]["tmp_name"], "/var/www/html/img/" . $pid . $ext)) {
                // redirect back to original page; you may comment it during debug
                // FIXME: do not show message through URL parameters
                header("Location: admin.php?success=* Product {$pname} added");
            } else {
                // FIXME: do not show message through URL parameters
                header("Location: admin.php?error=* Image should be in JPG, PNG or GIF format");
            }
        }
    }
}

function prod_delete_by_cid($db, $cid) {
    // input validation or sanitization
    if (!($cid = filter_var($cid, FILTER_VALIDATE_INT))) {
        throw new Exception("Invalid category ID");
    }
    $sql = "DELETE FROM products WHERE cid = :cid";
    $q = $db->prepare($sql);
    $q->bindParam(':cid', $cid);
    return $q->execute();
}

function prod_fetchAll() {
    // DB manipulation
    global $db;
    $db = DB();
    
    $q = $db->prepare("SELECT * FROM products LIMIT 20;");
    if ($q->execute()) {
        return $q->fetchAll();
    }
}

function prod_fetchOne($pid) {
    global $db;
    $db = DB();

    // input validation or sanitization
    if (!($pid = filter_var($pid, FILTER_VALIDATE_INT)))
        throw new Exception("Invalid product ID");
    
    $sql = "SELECT * FROM products WHERE pid = :pid";
    $q = $db->prepare($sql);
    $q->bindParam(':pid', $pid);
    if ($q->execute()) {
        return $q->fetch();
    }
}

function prod_fetch_by_cid($cid) {
    global $db;
    $db = DB();

    // input validation or sanitization
    if (!($cid = filter_var($cid, FILTER_VALIDATE_INT)))
        throw new Exception("Invalid product ID");
    
    $sql = "SELECT * FROM products WHERE cid = :cid";
    $q = $db->prepare($sql);
    $q->bindParam(':cid', $cid);
    if ($q->execute()) {
        return $q->fetchAll();
    }
}

function prod_edit() {
    global $db;
    $db = DB();
    if (!($pid = filter_input(INPUT_POST, 'pid', FILTER_VALIDATE_INT))) {
        throw new Exception("Invalid product ID");
    }
    if (!($cid = filter_input(INPUT_POST, 'cid', FILTER_VALIDATE_INT))) {
        throw new Exception("Invalid category ID");
    }
    if (!preg_match(NAME_REGEX, $_POST['pname'])) {
        throw new Exception("Invalid product name");
    }
    if (!($price = filter_input(INPUT_POST, "price", FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION))) {
        throw new Exception("Invalid price");
    }
    if (!($desc = htmlspecialchars($_POST['description']))) {
        throw new Exception("Text description is required");
    }

    $pname = htmlspecialchars($_POST['pname']);
    
    $sql = "UPDATE products SET cid = :cid, name = :name, price = :price, description = :desc WHERE pid = :pid";
    $q = $db->prepare($sql);
    $q->bindParam(':cid', $cid);
    $q->bindParam(':name', $pname);
    $q->bindParam(':price', $price);
    $q->bindParam(':desc', $desc);
    $q->bindParam(':pid', $pid);

    if ($q->execute()) {
        if ($_FILES["file"]["error"] == 0
        && in_array($_FILES["file"]["type"], array('image/jpeg', 'image/png', 'image/gif'))
        && in_array(mime_content_type($_FILES["file"]["tmp_name"]), array('image/jpeg', 'image/png', 'image/gif'))
        && $_FILES["file"]["size"] <= 5000000) {
            $ext = '.png';
            // Note: Take care of the permission of destination folder (hints: current user is apache)
            if (move_uploaded_file($_FILES["file"]["tmp_name"], "/var/www/html/img/" . $pid . $ext)) {
                // redirect back to original page; you may comment it during debug
                // FIXME: do not show message through URL parameters
                header("Location: admin.php?success=* Product {$pname} added");
            } else {
                // FIXME: do not show message through URL parameters
                header("Location: admin.php?error=* Image should be in JPG, PNG or GIF format");
            }
        }
        // FIXME: do not show message through URL parameters
        header("Location: admin.php?success=* Product {$pid} is updated");
    } else {
        // FIXME: do not show message through URL parameters
        header("Location: admin.php?error=* Product {$pid} is not updated. Please check if there is any data error or try again later");
    }
}

function prod_delete() {
    global $db;
    $db = DB();
    
    // input validation or sanitization
    if (!($pid = filter_input(INPUT_POST, 'pid', FILTER_VALIDATE_INT))) {
        throw new Exception("Invalid product ID");
    }
    
    $sql = "DELETE FROM products WHERE pid = :pid";
    $q = $db->prepare($sql);
    $q->bindParam(':pid', $pid);
    if ($q->execute()) {
        // FIXME: do not show message through URL parameters
        header("Location: admin.php?success=* Product {$pid} is deleted");
    } else {
        // FIXME: do not show message through URL parameters
        header("Location: admin.php?error=* Cannot delete product {$pid}");
    }
}

function prod_fetchOne_get() {
    $pid = $_GET['pid'];
    return prod_fetchOne($pid);
}


// ORDER-RELATED FUNCTIONS
function admin_check_orders() {
    global $db;
    $db = DB();
    $sql = "SELECT * FROM orders ORDER BY time DESC;";
    $q = $db->prepare($sql);
    if ($q->execute()) {
        return $q->fetchAll();
    }
}

function user_check_orders() {
    global $db;
    $db = DB();
    $user_id = filter_var($_SESSION['auth']['userid'], FILTER_VALIDATE_INT);
    $sql = "SELECT * FROM orders WHERE userid=:userid ORDER BY time DESC LIMIT 5";
    $q = $db->prepare($sql);
    $q->bindParam(':userid', $user_id);
    if ($q->execute()) {
        return $q->fetchAll();
    }
}
