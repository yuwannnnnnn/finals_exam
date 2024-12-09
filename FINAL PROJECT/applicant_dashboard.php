<?php
session_start();
require_once 'core/models.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'applicant') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applicant Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .dashboard-container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            text-align: center;
        }

        h1 {
            color: #333;
            margin-bottom: 20px;
        }

        h2 {
            color: #555;
            margin-top: 30px;
        }

        a {
            display: block;
            margin: 10px 0;
            font-size: 16px;
            color: #0066cc;
            text-decoration: none;
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        a:hover {
            background-color: #0066cc;
            color: white;
        }

        a.logout-link {
            color: red;
            background-color: transparent;
            border: none;
            font-size: 16px;
            text-align: center;
        }

        a.logout-link:hover {
            color: white;
            background-color: red;
        }

        .welcome-message {
            margin-bottom: 20px;
            font-size: 18px;
            color: #333;
        }

    </style>
</head>
<body>
    <div class="dashboard-container">
        <h1>Welcome Applicant: <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
        <p class="welcome-message">Your personalized dashboard where you can view job listings, track your applications, and check messages.</p>
        
        <a href="jobListings.php">View Job Listings</a>
        <a href="myApplications.php">My Applications</a>
        <a href="applicant_messages.php">Messages</a> 
        <a href="core/handleForms.php?logoutAUser=1" class="logout-link">Logout</a>

        <h2>Your Dashboard</h2>
    </div>
</body>
</html>
