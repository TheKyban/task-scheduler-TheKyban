<?php
require_once 'functions.php';

$message = '';

if (isset($_POST['send-test-email'])) {
    // 📨 Send email to a test address with fake task
    $email = 'aaditya1392@gmail.com'; // Replace with your test email
    $tasks = [
        ['id' => '1', 'name' => 'Example Task', 'completed' => false]
    ];
    $success = sendTaskEmail($email, $tasks);
    $message = $success ? "✅ Test email sent to $email" : "❌ Failed to send email";
}

if (isset($_POST['send-reminders'])) {
    // 📤 Trigger full reminder logic
    sendTaskReminders();
    $message = "✅ Reminder job triggered (check logs/email)";
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Manual Task Email Trigger</title>
</head>

<body>
    <h2>Manual Test Controls</h2>

    <form method="POST" action="">
        <button name="send-test-email" type="submit">Send Test Email</button>
        <button name="send-reminders" type="submit">Send All Reminders</button>
    </form>

    <p><?= htmlspecialchars($message) ?></p>
</body>

</html>