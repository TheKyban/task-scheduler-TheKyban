<?php
require_once 'functions.php';

$message = "Invalid verification link.";

if (isset($_GET['email'], $_GET['code'])) {
	$email = $_GET['email'];
	$code = $_GET['code'];
	if (verifySubscription($email, $code)) {
		$message = "Email $email verified successfully!";
	} else {
		$message = "Verification failed. Code may be incorrect or expired.";
	}
}
?>

<!DOCTYPE html>
<html>

<head>
	<title>Email Verification</title>
</head>

<body>
	<!-- Do not modify the ID of the heading -->
	<h2 id="verification-heading">Subscription Verification</h2>
	<p><?= htmlspecialchars($message) ?></p>
</body>

</html>