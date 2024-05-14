<?php
require_once('admin/user.inc.php');
require_once('admin/auth.php');
$current_user = auth();
if (!$current_user) {
	header("Location: index.php");
}
header("Location: user-process.php?action=user_logout");
?>
