<?php
session_start();
require_once 'core/models.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'applicant') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];


$applications = getApplicationsByApplicant($user_id); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Applications</title>
    <link rel="stylesheet" href="styles.css">
    <style>

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
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

        nav {
            text-align: center;
            margin-bottom: 20px;
        }

        nav a {
            text-decoration: none;
            color: #0066cc;
            margin: 0 15px;
            font-weight: bold;
        }

        nav a:hover {
            color: #005bb5;
        }

        h2 {
            color: #333;
            margin-bottom: 20px;
        }

        .applications-list {
            list-style-type: none;
            padding: 0;
        }

        .application-item {
            background-color: white;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .application-item h3 {
            margin: 0 0 10px;
            color: #333;
        }

        .application-item p {
            margin: 5px 0;
            color: #555;
        }

        .application-item a {
            color: #0066cc;
            text-decoration: none;
        }

        .application-item a:hover {
            color: #005bb5;
        }

        .no-applications {
            text-align: center;
            color: #555;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>My Applications</h1>
        <nav>
            <a href="jobListings.php">Job Listings</a>
            <a href="applicant_dashboard.php">Dashboard</a>
            <a href="core/handleForms.php?logoutAUser=1">Logout</a>
        </nav>

        <h2>Your Job Applications</h2>
        <?php
        if (!empty($applications)) {
            echo "<ul class='applications-list'>";
            foreach ($applications as $application) {
                echo "<li class='application-item'>";
                echo "<h3>" . htmlspecialchars($application['title']) . "</h3>";
                echo "<p><strong>Status:</strong> " . htmlspecialchars($application['status']) . "</p>";
                echo "<p><strong>Cover Message:</strong> " . htmlspecialchars($application['cover_message']) . "</p>";
                echo "<p><strong>Resume:</strong> <a href='" . htmlspecialchars($application['resume']) . "' target='_blank'>View Resume</a></p>";
                echo "</li>";
            }
            echo "</ul>";
        } else {
            echo "<p class='no-applications'>You haven't applied to any jobs yet.</p>";
        }
        ?>
    </div>
</body>
</html>
