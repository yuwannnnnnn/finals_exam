<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'hr') {
    header("Location: login.php");
    exit();
}
require_once 'core/dbConfig.php';

$stmt = $pdo->prepare("SELECT m.id, m.from_user_id, m.to_user_id, m.message, m.created_at, 
                              u.username AS sender_username, u2.username AS recipient_username 
                        FROM messages m
                        JOIN users u ON m.from_user_id = u.id
                        LEFT JOIN users u2 ON m.to_user_id = u2.id
                        WHERE m.from_user_id = ? OR m.to_user_id = ? 
                        ORDER BY m.created_at DESC");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
$messages = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_message'])) {
    $messageContent = $_POST['message'];
    $applicantId = $_POST['applicant_id'];  

    $query = "INSERT INTO messages (from_user_id, to_user_id, message, created_at) VALUES (?, ?, ?, NOW())";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$_SESSION['user_id'], $applicantId, $messageContent]);

    header("Location: messages.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $messageContent = $_POST['message'];
    $applicantId = $_POST['applicant_id'];  

    $query = "INSERT INTO messages (from_user_id, to_user_id, message, created_at) VALUES (?, ?, ?, NOW())";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$_SESSION['user_id'], $applicantId, $messageContent]);

    header("Location: messages.php");
    exit();
}

function getApplicants() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id, username FROM users WHERE role = 'applicant'");
    $stmt->execute();
    return $stmt->fetchAll();
}

$applicants = getApplicants(); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f6f9;
        }
        header {
            background-color: #4CAF50;
            color: white;
            padding: 15px 20px;
            text-align: center;
        }
        header a {
            color: white;
            text-decoration: none;
            margin: 0 10px;
        }
        header a:hover {
            text-decoration: underline;
        }
        
        .container {
            width: 80%;
            margin: 30px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .form-group button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .form-group button:hover {
            background-color: #45a049;
        }

        h2 {
            color: #333;
        }

        .message-list {
            margin-top: 30px;
        }

        .message-item {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
        }

        .message-item p {
            margin: 10px 0;
        }

        .message-item small {
            color: #999;
        }

        .message-item textarea {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            margin-bottom: 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        .message-item .reply-btn {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .message-item .reply-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<header>
    <h1>HR Dashboard</h1>
    <nav>
        <a href="hr_dashboard.php">Dashboard</a> | 
        <a href="createJobPost.php">Create Job Post</a> | 
        <a href="viewApplications.php">View Applications</a> | 
        <a href="core/handleForms.php?logoutAUser=1">Logout</a>
    </nav>
</header>

<div class="container">
    <h2>Send Message to Applicant</h2>
    <form method="POST" action="messages.php">
        <div class="form-group">
            <label for="applicant_id">Select Applicant:</label>
            <select name="applicant_id" id="applicant_id" required>
                <?php
                foreach ($applicants as $applicant) {
                    echo "<option value='" . $applicant['id'] . "'>" . htmlspecialchars($applicant['username']) . "</option>";
                }
                ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="message">Your Message:</label>
            <textarea name="message" id="message" rows="4" required></textarea>
        </div>
        
        <div class="form-group">
            <button type="submit" name="send_message">Send Message</button>
        </div>
    </form>

    <div class="message-list">
        <h2>Message History</h2>
        <?php if (!empty($messages)): ?>
            <?php foreach ($messages as $message): ?>
                <div class="message-item">
                    <?php
                    if ($message['from_user_id'] == $_SESSION['user_id']) {
                        echo "<strong>To: " . htmlspecialchars($message['recipient_username']) . "</strong>";
                    } else {
                        echo "<strong>From: " . htmlspecialchars($message['sender_username']) . "</strong>";
                    }
                    ?>
                    <p><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                    <p><small>Sent/Received on: <?php echo htmlspecialchars($message['created_at']); ?></small></p>

                    <?php if ($message['from_user_id'] != $_SESSION['user_id']):  ?>
                        <form method="POST" action="messages.php">
                            <textarea name="message" rows="4" required placeholder="Your reply..."></textarea><br>
                            <input type="hidden" name="applicant_id" value="<?php echo $message['from_user_id']; ?>">
                            <button type="submit" name="reply_message" class="reply-btn">Send Reply</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No messages available.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
