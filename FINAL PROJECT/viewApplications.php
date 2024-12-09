<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'hr') {
    header("Location: login.php");
    exit();
}
require_once 'core/dbConfig.php';

$stmt = $pdo->prepare("
    SELECT a.*, u.username AS applicant_name, j.title AS job_title, a.resume_path, a.cover_message
    FROM applications a
    JOIN users u ON a.user_id = u.id
    JOIN job_posts j ON a.job_post_id = j.id
    WHERE j.created_by = ? AND (a.status IS NULL OR a.status = 'pending')
");
$stmt->execute([$_SESSION['user_id']]);
$applications = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Applications</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            color: #333;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            text-align: center;
        }

        header nav {
            margin-top: 10px;
        }

        header nav a {
            color: white;
            text-decoration: none;
            margin: 0 15px;
            font-size: 16px;
        }

        header nav a:hover {
            text-decoration: underline;
        }

        h1 {
            margin: 0;
            font-size: 26px;
        }

        .container {
            width: 80%;
            margin: 30px auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #4CAF50;
            color: white;
        }

        tr:hover {
            background-color: #f2f2f2;
        }

        button {
            padding: 8px 12px;
            margin: 5px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            background-color: #4CAF50;
            color: white;
            font-size: 14px;
        }

        button:hover {
            background-color: #45a049;
        }

        .resume-link {
            text-decoration: none;
            color: #4CAF50;
            font-size: 14px;
        }

        .resume-link:hover {
            text-decoration: underline;
        }

        .back-link {
            display: block;
            margin-top: 20px;
            text-align: center;
            font-size: 14px;
        }

        .back-link a {
            text-decoration: none;
            color: #4CAF50;
        }

        .back-link a:hover {
            color: #333;
        }
    </style>
</head>
<body>
    <header>
        <h1>Applications for Your Job Posts</h1>
        <nav>
            <a href="hr_dashboard.php">Dashboard</a>
            <a href="core/handleForms.php?logoutAUser=1.php">Logout</a>
        </nav>
    </header>

    <div class="container">
        <table>
            <tr>
                <th>Applicant</th>
                <th>Job Title</th>
                <th>Status</th>
                <th>Cover Message</th>
                <th>Resume</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($applications as $app): ?>
                <tr>
                    <td><?php echo htmlspecialchars($app['applicant_name']); ?></td>
                    <td><?php echo htmlspecialchars($app['job_title']); ?></td>
                    <td><?php echo htmlspecialchars($app['status'] ?: 'Pending'); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($app['cover_message'])); ?></td>
                    <td>
                    <?php if (!empty($app['resume']) && file_exists('uploads/resumes/' . basename($app['resume']))): ?>
    <a href="uploads/resumes/<?php echo basename($app['resume']); ?>" target="_blank" class="resume-link">View Resume</a>
<?php else: ?>
    No resume uploaded or file not found
<?php endif; ?>
                    </td>
                    <td>
                        <form action="core/handleForms.php" method="POST">
                            <input type="hidden" name="application_id" value="<?php echo $app['id']; ?>">
                            <button type="submit" name="rejectApplicationBtn">Reject</button>
                            <button type="submit" name="acceptApplicationBtn">Accept</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <div class="back-link">
            <a href="hr_dashboard.php">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
