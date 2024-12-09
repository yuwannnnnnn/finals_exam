<?php
session_start();
require_once 'core/models.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'applicant') {
    header("Location: login.php");
    exit();
}


$jobPosts = getAllJobPosts();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Listings</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }

        nav {
            background-color: #333;
            padding: 10px;
            text-align: center;
        }

        nav a {
            color: white;
            padding: 14px 20px;
            text-decoration: none;
            font-size: 18px;
        }

        nav a:hover {
            background-color: #575757;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }

        .job-post {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .job-post h2 {
            color: #333;
            margin-bottom: 10px;
        }

        .job-post p {
            color: #555;
            margin-bottom: 20px;
        }

        .apply-form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .apply-form textarea,
        .apply-form input[type="file"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }

        .apply-form button {
            background-color: #0066cc;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .apply-form button:hover {
            background-color: #005bb5;
        }

        .apply-form input[type="file"] {
            padding: 5px;
        }

        .error-message {
            color: red;
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <nav>
        <a href="applicant_dashboard.php">Dashboard</a>
        <a href="myApplications.php">My Applications</a>
        <a href="applicant_messages.php">Messages</a>
        <a href="logout.php">Logout</a>
    </nav>

    <div class="container">
        <h1>Available Job Listings</h1>

        <?php
        if (empty($jobPosts)) {
            echo "<p>No job listings available at the moment.</p>";
        } else {
            foreach ($jobPosts as $job) {
                echo "<div class='job-post'>";
                echo "<h2>" . htmlspecialchars($job['title']) . "</h2>";
                echo "<p>" . htmlspecialchars($job['description']) . "</p>";

                echo "<form action='core/handleForms.php' method='POST' enctype='multipart/form-data' class='apply-form'>";
                echo "<input type='hidden' name='job_post_id' value='" . $job['id'] . "'>";
                echo "<textarea name='cover_message' placeholder='Why are you the best candidate for this job?' required></textarea>";
                echo "<input type='file' name='resume' accept='.pdf' required>";
                echo "<button type='submit' name='applyJobBtn'>Apply</button>";
                echo "</form>";
                echo "</div>";
            }
        }
        ?>
    </div>
</body>
</html>
