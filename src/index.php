<?php
require_once 'functions.php';
// Add task
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task-name'])) {
	$taskName = trim($_POST['task-name']);
	if ($taskName !== '') {
		addTask($taskName);
	}
	header("Location: index.php");
	exit;
}

// Toggle completed
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle-task'], $_POST['status'])) {
	markTaskAsCompleted($_POST['toggle-task'], $_POST['status'] !== '1');
	header("Location: index.php");
	exit;
}

// Delete task
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete-task'])) {
	deleteTask($_POST['delete-task']);
	header("Location: index.php");
	exit;
}

$tasks = getAllTasks();

// Handle email subscription
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subscribe-email'])) {
	$email = trim($_POST['subscribe-email']);
	if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$subscribed = subscribeEmail($email);
		$subscribe_message = $subscribed
			? "Verification email sent to $email."
			: "Failed to send email to $email.";
	} else {
		$subscribe_message = "Invalid email format.";
	}
}
?>
<!DOCTYPE html>
<html>

<head>
	<!-- Implement Header !-->
</head>

<body>

	<!-- Add Task Form -->
	<form method="POST" action="">
		<!-- Implement Form !-->
		<input type="text" name="task-name" id="task-name" placeholder="Enter new task" required>
		<button type="submit" id="add-task">Add Task</button>
	</form>

	<!-- Tasks List -->
	<ul id="tasks-list">
		<!-- Implement Tasks List -->
		<?php foreach ($tasks as $task): ?>
			<li class="task-item">
				<!-- Checkbox to toggle completion -->
				<form method="POST" style="display:inline;">
					<input type="checkbox" class="task-status" name="toggle-task"
						value="<?= htmlspecialchars($task['id']) ?>" onchange="this.form.submit()" <?= $task['completed'] ? 'checked' : '' ?>>
					<input type="hidden" name="status" value="<?= $task['completed'] ? '1' : '0' ?>">
				</form>

				<!-- Task name (you can style or move this as needed) -->
				<span style="<?= $task['completed'] ? 'text-decoration: line-through;' : '' ?>">
					<?= htmlspecialchars($task['name']) ?>
				</span>

				<!-- Delete Button -->
				<form method="POST" style="display:inline;">
					<input type="hidden" name="delete-task" value="<?= htmlspecialchars($task['id']) ?>">
					<button type="submit" class="delete-task">Delete</button>
				</form>
			</li>
		<?php endforeach; ?>
	</ul>

	<!-- Subscription Form -->
	<form method="POST" action="">
		<!-- Implement Form !-->
		<input type="email" name="subscribe-email" id="subscribe-email" placeholder="Enter email to subscribe" required>
		<button type="submit" id="submit-email">Subscribe</button>
		<!-- <?= $subscribe_message ?> -->
	</form>

</body>

</html>