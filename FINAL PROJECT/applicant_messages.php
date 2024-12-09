<?php
session_start();
require_once 'core/dbConfig.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'applicant') {
    header("Location: login.php");
    exit();
}


$stmt = $pdo->prepare("SELECT m.id, m.from_user_id, m.to_user_id, m.message, m.created_at, u.username AS sender_username, u2.username AS recipient_username 
                        FROM messages m
                        JOIN users u ON m.from_user_id = u.id
                        LEFT JOIN users u2 ON m.to_user_id = u2.id
                        WHERE m.from_user_id = ? OR m.to_user_id = ?
                        ORDER BY m.created_at DESC");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
$messages = $stmt->fetchAll();


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_message'])) {
    $messageContent = $_POST['message'];
    $hrUserId = $_POST['hr_user_id'];  


    $query = "INSERT INTO messages (from_user_id, to_user_id, message, created_at) VALUES (?, ?, ?, NOW())";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$_SESSION['user_id'], $hrUserId, $messageContent]);


    header("Location: applicant_messages.php");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $messageContent = $_POST['message'];
    $hrUserId = $_POST['hr_user_id'];  

    $query = "INSERT INTO messages (from_user_id, to_user_id, message, created_at) VALUES (?, ?, ?, NOW())";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$_SESSION['user_id'], $hrUserId, $messageContent]);


    header("Location: applicant_messages.php");
    exit();
}


function getHRUsers() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id, username FROM users WHERE role = 'hr'");
    $stmt->execute();
    return $stmt->fetchAll();
}

$hrUsers = getHRUsers();  
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applicant Messages</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        nav a {
            text-decoration: none;
            color: #0066cc;
            margin-right: 20px;
            font-weight: bold;
        }

        nav a:hover {
            color: #005bb5;
        }

        form {
            background-color: #fff;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        form label {
            font-weight: bold;
        }

        select, textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        button {
            background-color: #0066cc;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #005bb5;
        }

        .message-history {
            list-style-type: none;
            padding: 0;
        }

        .message-item {
            background-color: white;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .message-item strong {
            font-size: 1.1em;
        }

        .message-item p {
            margin: 5px 0;
            color: #555;
        }

        .message-item small {
            color: #999;
        }

        .message-item .reply-form {
            margin-top: 10px;
            background-color: #f9f9f9;
            padding: 10px;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .no-messages {
            text-align: center;
            color: #555;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Messages</h1>
        <nav>
            <a href="applicant_dashboard.php">Back to Dashboard</a> |
            <a href="core/handleForms.php?logoutAUser=1">Logout</a>
        </nav>

        <h2>Send Message to HR</h2>
        <form method="POST" action="applicant_messages.php">
            <label for="hr_user_id">Select HR User:</label>
            <select name="hr_user_id" id="hr_user_id" required>
                <?php
                foreach ($hrUsers as $hr) {
                    echo "<option value='" . $hr['id'] . "'>" . htmlspecialchars($hr['username']) . "</option>";
                }
                ?>
            </select>

            <label for="message">Your Message:</label>
            <textarea name="message" id="message" rows="4" required></textarea>

            <button type="submit" name="send_message">Send Message</button>
        </form>

        <h2>Message History</h2>
        <?php if (!empty($messages)): ?>
            <ul class="message-history">
                <?php foreach ($messages as $message): ?>
                    <li class="message-item">
                        <?php
                        if ($message['from_user_id'] == $_SESSION['user_id']) {
                            echo "<strong>To: " . htmlspecialchars($message['recipient_username']) . "</strong>";
                        } else {
                            echo "<strong>From: " . htmlspecialchars($message['sender_username']) . "</strong>";
                        }
                        ?>
                        <p><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                        <p><small>Sent/Received on: <?php echo htmlspecialchars($message['created_at']); ?></small></p>

                        <?php if ($message['from_user_id'] != $_SESSION['user_id']): ?>
                            <div class="reply-form">
                                <form method="POST" action="applicant_messages.php">
                                    <textarea name="message" rows="4" required placeholder="Your reply..."></textarea>
                                    <input type="hidden" name="hr_user_id" value="<?php echo $message['from_user_id']; ?>">
                                    <button type="submit" name="reply_message">Send Reply</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="no-messages">No messages available.</p>
        <?php endif; ?>
    </div>
</body>
</html>
