<?php
require_once __DIR__ . '/mail.php';

/**
 * Adds a new task to the task list
 * 
 * @param string $task_name The name of the task to add.
 * @return bool True on success, false on failure.
 */
function addTask(string $task_name): bool
{
	$file = __DIR__ . '/tasks.txt';

	$tasks = [];
	if (file_exists($file)) {
		$json = file_get_contents($file);
		$tasks = json_decode($json, true) ?? [];
	}

	$tasks[] = [
		'id' => (string) time(),
		'name' => $task_name,
		'completed' => false
	];

	return file_put_contents($file, json_encode($tasks, JSON_PRETTY_PRINT)) !== false;
}

/**
 * Retrieves all tasks from the tasks.txt file
 * 
 * @return array Array of tasks. -- Format [ id, name, completed ]
 */
function getAllTasks(): array
{
	$file = __DIR__ . '/tasks.txt';
	if (!file_exists($file))
		return [];

	$json = file_get_contents($file);
	$tasks = json_decode($json, true);
	return is_array($tasks) ? $tasks : [];
}

/**
 * Marks a task as completed or uncompleted
 * 
 * @param string  $task_id The ID of the task to mark.
 * @param bool $is_completed True to mark as completed, false to mark as uncompleted.
 * @return bool True on success, false on failure
 */
function markTaskAsCompleted(string $task_id, bool $is_completed): bool
{
	$file = __DIR__ . '/tasks.txt';
	if (!file_exists($file))
		return false;

	$json = file_get_contents($file);
	$tasks = json_decode($json, true);
	if (!is_array($tasks))
		return false;

	$updated = false;
	foreach ($tasks as &$task) {
		if ($task['id'] === $task_id) {
			$task['completed'] = $is_completed;
			$updated = true;
			break;
		}
	}

	if (!$updated)
		return false;

	return file_put_contents($file, json_encode($tasks, JSON_PRETTY_PRINT), LOCK_EX) !== false;
}

/**
 * Deletes a task from the task list
 * 
 * @param string $task_id The ID of the task to delete.
 * @return bool True on success, false on failure.
 */
function deleteTask(string $task_id): bool
{
	$file = __DIR__ . '/tasks.txt';
	if (!file_exists($file))
		return false;

	$json = file_get_contents($file);
	$tasks = json_decode($json, true);
	if (!is_array($tasks))
		return false;

	$originalCount = count($tasks);
	$tasks = array_filter($tasks, fn($task) => $task['id'] !== $task_id);

	if (count($tasks) === $originalCount)
		return false;

	return file_put_contents($file, json_encode($tasks, JSON_PRETTY_PRINT), LOCK_EX) !== false;
}

/**
 * Generates a 6-digit verification code
 * 
 * @return string The generated verification code.
 */
function generateVerificationCode(): string
{
	return strval(random_int(100000, 999999));
}

/**
 * Subscribe an email address to task notifications.
 *
 * Generates a verification code, stores the pending subscription,
 * and sends a verification email to the subscriber.
 *
 * @param string $email The email address to subscribe.
 * @return bool True if verification email sent successfully, false otherwise.
 */
function subscribeEmail(string $email): bool
{
	if (empty($email))
		return false;

	$pending_file = __DIR__ . '/pending_subscriptions.txt';
	$subscribers_file = __DIR__ . '/subscribers.txt';

	// Load pending subscriptions
	$pending = file_exists($pending_file)
		? json_decode(file_get_contents($pending_file), true) ?? []
		: [];

	// If already pending, skip
	if (array_key_exists($email, $pending)) {
		return true; // Already pending — no need to add again
	}

	// Load verified subscribers
	$subscribers = file_exists($subscribers_file)
		? json_decode(file_get_contents($subscribers_file), true) ?? []
		: [];

	// If already verified, skip
	if (in_array($email, $subscribers)) {
		return true; // Already verified — no need to resend
	}

	// Generate code and save
	$code = generateVerificationCode();
	$pending[$email] = ['code' => $code];
	file_put_contents($pending_file, json_encode($pending, JSON_PRETTY_PRINT), LOCK_EX);

	// Send verification email
	$verify_link = "http://localhost:8000/verify.php?email=" . urlencode($email) . "&code=$code";
	$subject = "Verify your email for Task Planner";
	$message = "Click this link to verify your subscription:\n$verify_link";

	return sendMail($email, $subject, $message);
}

/**
 * Verifies an email subscription
 * 
 * @param string $email The email address to verify.
 * @param string $code The verification code.
 * @return bool True on success, false on failure.
 */
function verifySubscription(string $email, string $code): bool
{
	$pending_file = __DIR__ . '/pending_subscriptions.txt';
	$subscribers_file = __DIR__ . '/subscribers.txt';

	if (!file_exists($pending_file))
		return false;

	$pending = json_decode(file_get_contents($pending_file), true) ?? [];

	if (!isset($pending[$email]) || $pending[$email]['code'] !== $code)
		return false;

	// Add to subscribers
	$subscribers = file_exists($subscribers_file) ? json_decode(file_get_contents($subscribers_file), true) ?? [] : [];
	if (!in_array($email, $subscribers)) {
		$subscribers[] = $email;
		file_put_contents($subscribers_file, json_encode($subscribers, JSON_PRETTY_PRINT), LOCK_EX);
	}

	// Remove from pending
	unset($pending[$email]);
	file_put_contents($pending_file, json_encode($pending, JSON_PRETTY_PRINT), LOCK_EX);

	return true;
}


/**
 * Unsubscribes an email from the subscribers list
 * 
 * @param string $email The email address to unsubscribe.
 * @return bool True on success, false on failure.
 */
function unsubscribeEmail(string $email): bool
{
	$file = __DIR__ . '/subscribers.txt';

	if (!file_exists($file))
		return false;

	$subscribers = json_decode(file_get_contents($file), true) ?? [];
	$new_subscribers = array_values(array_filter($subscribers, fn($e) => $e !== $email));

	if (count($new_subscribers) === count($subscribers))
		return false; // not found

	return file_put_contents($file, json_encode($new_subscribers, JSON_PRETTY_PRINT), LOCK_EX) !== false;
}


/**
 * Sends task reminders to all subscribers
 * Internally calls  sendTaskEmail() for each subscriber
 */
function sendTaskReminders(): void
{
	$subscribers_file = __DIR__ . '/subscribers.txt';
	if (!file_exists($subscribers_file))
		return;

	$subscribers = json_decode(file_get_contents($subscribers_file), true) ?? [];
	$tasks = getAllTasks();
	$pending_tasks = array_filter($tasks, fn($task) => !$task['completed']);

	foreach ($subscribers as $email) {
		sendTaskEmail($email, $pending_tasks);
	}
}


/**
 * Sends a task reminder email to a subscriber with pending tasks.
 *
 * @param string $email The email address of the subscriber.
 * @param array $pending_tasks Array of pending tasks to include in the email.
 * @return bool True if email was sent successfully, false otherwise.
 */
function sendTaskEmail(string $email, array $pending_tasks): bool
{
	$subject = 'Task Planner - Pending Tasks Reminder';

	$body = empty($pending_tasks)
		? "You have no pending tasks."
		: "Here are your pending tasks:\n" . implode("\n", array_map(fn($t) => "- {$t['name']}", $pending_tasks));

	return sendMail($email, $subject, $body);
}

