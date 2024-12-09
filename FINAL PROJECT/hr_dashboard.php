<?php
session_start();
require_once 'core/models.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'hr') {
    header("Location: login.php");
    exit();
}


$user_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            text-align: center;
        }

        h1 {
            margin: 0;
            font-size: 24px;
        }

        nav {
            text-align: center;
            margin: 20px 0;
        }

        nav a {
            margin: 0 15px;
            text-decoration: none;
            font-weight: bold;
            color: #4CAF50;
        }

        nav a:hover {
            color: #333;
        }

        .container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #4CAF50;
            font-size: 20px;
            margin-bottom: 10px;
        }

        ul {
            list-style-type: none;
            padding: 0;
        }

        ul li {
            background-color: #f9f9f9;
            margin: 8px 0;
            padding: 10px;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        ul li strong {
            font-size: 16px;
            color: #333;
        }

        .message-link {
            background-color: #008CBA;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            margin-top: 20px;
            display: inline-block;
        }

        .message-link:hover {
            background-color: #005f6a;
        }

        .logout-link {
            background-color: #f44336;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }

        .logout-link:hover {
            background-color: #d32f2f;
        }
    </style>
</head>
<body>
    <header>
        <h1>Welcome HR: <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
    </header>

    <nav>
        <a href="createJobPost.php">Create Job Post</a>
        <a href="viewApplications.php">View Applications</a>
        <a href="messages.php">Messages</a> 
    </nav>

    <div class="container">
        <a href="core/handleForms.php?logoutAUser=1" class="logout-link">Logout</a>

        <h2>Your Job Posts</h2>
        <?php

        $jobPosts = getJobPosts($user_id);  
        if (!empty($jobPosts)) {
            echo "<ul>";
            foreach ($jobPosts as $job) {
                echo "<li><strong>" . htmlspecialchars($job['title']) . "</strong> - " . htmlspecialchars($job['description']) . "</li>";

                $query = "
                    SELECT a.id AS application_id, a.user_id, u.username 
                    FROM applications a
                    JOIN users u ON a.user_id = u.id
                    WHERE a.job_post_id = ? AND (a.status IS NULL OR a.status = 'pending')
                ";
                $stmt = $pdo->prepare($query);
                $stmt->execute([$job['id']]);
                $pendingApplicants = $stmt->fetchAll();

                $query = "
                    SELECT a.user_id, u.username 
                    FROM applications a
                    JOIN users u ON a.user_id = u.id
                    WHERE a.job_post_id = ? AND a.status = 'accepted'
                ";
                $stmt = $pdo->prepare($query);
                $stmt->execute([$job['id']]);
                $hiredApplicants = $stmt->fetchAll();

                if ($hiredApplicants) {
                    echo "<ul><li><strong>Hired Applicants:</strong></li>";
                    foreach ($hiredApplicants as $applicant) {
                        echo "<li>" . htmlspecialchars($applicant['username']) . "</li>";
                    }
                    echo "</ul>";
                }

                $query = "
                    SELECT a.user_id, u.username 
                    FROM applications a
                    JOIN users u ON a.user_id = u.id
                    WHERE a.job_post_id = ? AND a.status = 'rejected'
                ";
                $stmt = $pdo->prepare($query);
                $stmt->execute([$job['id']]);
                $rejectedApplicants = $stmt->fetchAll();

                if ($rejectedApplicants) {
                    echo "<ul><li><strong>Rejected Applicants:</strong></li>";
                    foreach ($rejectedApplicants as $applicant) {
                        echo "<li>" . htmlspecialchars($applicant['username']) . "</li>";
                    }
                    echo "</ul>";
                }
            }
            echo "</ul>";
        } else {
            echo "<p>No job posts available.</p>";
        }
        ?>
    </div>
</body>
</html>
