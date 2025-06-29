<?php
require_once 'functions.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
	$email = trim($_POST['email']);
	if (unsubscribeEmail($email)) {
		$message = "Unsubscribed $email successfully.";
	} else {
		$message = "Unsubscription failed. Email may not be subscribed.";
	}
}
?>

<!DOCTYPE html>
<html>

<head>
	<title>Unsubscribe</title>
</head>

<body>
	<!-- Do not modify the ID of the heading -->
	<h2 id="unsubscription-heading">Unsubscribe from Task Updates</h2>

	<form method="POST">
		<input type="email" name="email" placeholder="Enter your email" required>
		<button type="submit">Unsubscribe</button>
	</form>

	<p><?= htmlspecialchars($message) ?></p>
</body>

</html>