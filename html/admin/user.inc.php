<?php
session_start();
require_once('db.php');
require_once('auth.php');

$current_user = auth();
const PASSWORD_REGEX = '/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[a-zA-Z]).{8,}$/';

function redirect(bool $is_admin) {
    if ($is_admin) {
        header("Location: admin.php", false, 302);
    } else {
        header("Location: index.php");
    }
}

// USER-RELATED FUNCTIONS
function user_fetchOne() {
    // DB manipulation
    global $db;
    $db = DB();
    if (!(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL))) {  // regex: '/^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/'
        throw new Exception("invalid email");
    }
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

    $q = $db->prepare("SELECT * FROM user WHERE email = :email;");
    $q->bindParam(":email", $email);
    if ($q->execute()) {
        return $q->fetch();
    }
}

function user_login() {
    global $db;
    global $current_user;
    $db = DB();
    
    // input validation or sanitization
    /* TODO: auth cookie instead of email */
    if (!(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL))) {  // regex: '/^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/'
        throw new Exception("invalid email");
    }
    if (!preg_match(PASSWORD_REGEX, $_POST['pwd'])) {
        throw new Exception("invalid password pattern");
    }

    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $q = $db->prepare("SELECT * FROM user WHERE email = :email");
    $q->bindParam(":email", $email);
    if (!$q->execute()) {
        // FIXME: do not show message through URL
        header("Location: index.php?error=* Wrong credentials. Please try again");
    }

    $result = $q->fetch();
    $pwd_hash = $result['password'];
    $is_admin = $result['is_admin'];
    $user_id = $result['userid'];
    $salt = $result['salt'];
    $pwd = hash_hmac("sha256", $_POST['pwd'], $salt);
    if ($pwd === $pwd_hash) {
        session_regenerate_id(true);  // rotates session ids without destroying params
        $expiry = time() + 3600 * 24 * 3;
        $tokens = array(
            'email' => $email,
            'expiry' => $expiry,
            'is_admin' => $is_admin,
            'token' => hash_hmac('sha256', $expiry . $pwd_hash, $salt),
            'userid' => $user_id
        );
        setcookie('auth', json_encode($tokens), $expiry, '/', 's11.ierg4210.ie.cuhk.edu.hk', true, true);
        $_SESSION['auth'] = $tokens;
        $current_user = auth();
        redirect($is_admin);
    } else {
        header("Location: login.php?error=incorrect password"); // fallback if everything fails
    }
}

function user_logout() {
    session_destroy();
    setcookie("auth", "", time() - 60, '/', 's11.ierg4210.ie.cuhk.edu.hk', true, true);
    $_SESSION['auth'] = "";
    header("Location: index.php", true, 302);
}

function user_insert() {
    global $db;
    $db = DB();
    // input validation or sanitization
    if (!(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL))) {
        throw new Exception("invalid email");
    }
    if (!preg_match(PASSWORD_REGEX, $_POST['pwd'])) {
        throw new Exception("invalid password");
    }
    if (!preg_match(PASSWORD_REGEX, $_POST['confirm_pwd'])) {
        throw new Exception("invalid password");
    }
    if ($_POST['pwd'] !== $_POST['confirm_pwd']) {
        throw new Exception("password mismatch");
    }

    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $user = strtok($email, '@');
    $salt = bin2hex(random_bytes(16));
    $pwd = hash_hmac("sha256", $_POST['pwd'], $salt);
    $is_admin = $_POST['is_admin'];
    $sql = 'INSERT INTO user (email, password, salt, is_admin) VALUES (:email, :pwd, :salt, :is_admin)';
    $q = $db->prepare($sql);
    $q->bindParam(':email', $email);
    $q->bindParam(':pwd', $pwd);
    $q->bindParam(":salt", $salt);
    $q->bindParam(":is_admin", $is_admin);
    if ($q->execute()) {
        // FIXME: do not show message through URL parameters
        header("Location: login.php?success=* New user '{$user}' created");
    } else {
        // FIXME: do not show message through URL parameters
        header("Location: login.php?error=* Cannot create user '{$user}'");
    }
}

function user_change_password() {
    global $db;
    $db = DB();
    
    $email = auth();
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("invalid email");
    }
    if (!preg_match(PASSWORD_REGEX, $_POST['old_pwd'])) {
        throw new Exception("invalid password");
    }
    if (!preg_match(PASSWORD_REGEX, $_POST['new_pwd'])) {
        throw new Exception("invalid password");
    }
    if ($_POST['old_pwd'] === $_POST['new_pwd']) {
        throw new Exception("new password cannot be the same as the current password");
    }
    if ($_POST['new_pwd'] !== $_POST['confirm_pwd']) {
        throw new Exception("passwords not match");
    }
    
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    $q = $db->prepare("SELECT password, salt FROM user WHERE email = :email");
    $q->bindParam(":email", $email);
    if (!$q->execute()) {
        header("Location: my-account.php?op=change_password", true, 302);
    }
    $result = $q->fetch();
    $pwd_hash = $result['password'];
    $salt = $result['salt'];
    $old_pwd = hash_hmac("sha256", htmlspecialchars($_POST['old_pwd']), $salt);
    $new_pwd = hash_hmac("sha256", htmlspecialchars($_POST['new_pwd']), $salt);
    if ($old_pwd === $pwd_hash) {
        $q = $db->prepare("UPDATE user SET password = :pwd WHERE email = :email");
        $q->bindParam(":pwd", $new_pwd);
        $q->bindParam(":email", $email);
        if ($q->execute()) {
            user_logout();  // log user out for security
        } else {
            try {
                $q = $db->prepare("UPDATE user SET password = :pwd WHERE email = :email");
                $q->bindParam(":pwd", $new_pwd);
                $q->bindParam(":email", $email);
            } catch (PDOException $e) {
                throw new Exception($e->getMessage());
            }
        }
    }
    // header("Location: my-account.php?op=change_password");  // fallback if everything fails
}

function user_delete() {
    // OPTIONAL: delete user from db
}
