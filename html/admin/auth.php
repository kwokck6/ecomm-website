<?php
function auth() {
    if (!empty($_SESSION['auth'])) {
        return $_SESSION['auth']['email'];
    }
    if (!empty($_COOKIE['auth'])) {
        if ($token = json_decode(stripslashes($_COOKIE['auth']), true)) {
            if (time() > $token['expiry']) {
                return false;
            }
            $db = DB();
            $q = $db->prepare("SELECT * FROM user WHERE email = :email");
            $q->bindParam(":email", $token['email']);
            if ($q->execute()) {
                $result = $q->fetch();
                $original_token = hash_hmac("sha256", $token['expiry'] . $result['password'], $result['salt']);
                if ($original_token == $token['token']) {
                    $_SESSION['auth'] = $token;
                    return $token['email'];
                }
            }
        }
    }
    return false;
}

function get_nonce($action) {
    $nonce = bin2hex(random_bytes(16));
    if (!isset($_SESSION['nonce'])) {
        $_SESSION['nonce'] = array();
    }
    $_SESSION['nonce'][$action] = $nonce;
    return $nonce;
}

function verify_nonce($action, $nonce) {
    if (isset($nonce) && $_SESSION['nonce'][$action] == $nonce) {
        if ($_SESSION['token'] == null) {
            unset($_SESSION['nonce'][$action]);
        }
        return true;
    }
    return false;
}
?>
