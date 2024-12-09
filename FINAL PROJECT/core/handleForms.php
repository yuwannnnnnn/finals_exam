<?php
require_once 'dbConfig.php';  
require_once 'models.php';  

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_POST['registerUserBtn'])) {
    $username = trim($_POST['username']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
    $role = trim($_POST['role']); 

    if (!empty($username) && !empty($password) && !empty($role)) {
        $result = insertNewUser($pdo, $username, $password, $role);
        
        $_SESSION['message'] = $result['message'];
        header("Location: ../login.php");  
        exit();  
    } else {
        $_SESSION['message'] = "Please fill in all fields";
        header("Location: ../register.php");  
        exit();
    }
}

if (isset($_POST['createJobPostBtn'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $created_by = $_SESSION['user_id'];

    if (!empty($title) && !empty($description)) {
        $result = createJobPost($pdo, $title, $description, $created_by);
        $_SESSION['message'] = $result['message'];
        header("Location: ../hr_dashboard.php"); 
        exit();
    } else {
        $_SESSION['message'] = "Please fill in all fields";
        header("Location: ../hr_dashboard.php"); 
        exit();
    }
}

if (isset($_POST['applyJobBtn'])) {
    $user_id = $_SESSION['user_id'];
    $job_id = $_POST['job_post_id'];
    $cover_message = trim($_POST['cover_message']);
    $resume = $_FILES['resume'];


    if ($resume['type'] !== 'application/pdf' || $resume['size'] > 5 * 1024 * 1024) { 
        $_SESSION['message'] = "Invalid resume file. Please upload a PDF under 5MB.";
        header("Location: ../applicant_dashboard.php");
        exit();
    }

    if ($resume['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['message'] = "Error uploading the resume.";
        header("Location: ../applicant_dashboard.php");
        exit();
    }

    $upload_dir = '../uploads/resumes/';  
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);  
    }

    $resume_path = $upload_dir . basename($resume['name']);
    
    if (move_uploaded_file($resume['tmp_name'], $resume_path)) {
        $query = "INSERT INTO applications (user_id, job_post_id, cover_message, resume) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$user_id, $job_id, $cover_message, $resume_path]);

        $_SESSION['message'] = "Application submitted successfully!";
    } else {
        $_SESSION['message'] = "There was an error uploading your resume.";
    }

    header("Location: ../applicant_dashboard.php");
    exit();
}

if (isset($_POST['sendMessageBtn'])) {
    $from_user_id = $_SESSION['user_id'];
    $to_user_id = $_POST['hr_id'];
    $message = trim($_POST['message']);

    $query = "INSERT INTO messages (from_user_id, to_user_id, message) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$from_user_id, $to_user_id, $message]);

    $_SESSION['message'] = "Message sent successfully!";
    header("Location: ../applicant_dashboard.php");
    exit();
}

if (isset($_POST['loginUserBtn'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        $user = getUserByUsername($pdo, $username);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'hr') {
                header("Location: ../hr_dashboard.php"); 
            } elseif ($user['role'] === 'applicant') {
                header("Location: ../applicant_dashboard.php"); 
            } else {
                $_SESSION['message'] = "Invalid role. Contact system administrator.";
                header("Location: ../login.php");
            }
            exit();
        } else {
            $_SESSION['message'] = "Invalid username or password.";
            header("Location: ../login.php");
            exit();
        }
    } else {
        $_SESSION['message'] = "Please fill in all fields.";
        header("Location: ../login.php");
        exit();
    }
}

if (isset($_POST['rejectApplicationBtn'])) {
    $application_id = $_POST['application_id'];
    $status = 'rejected';

    $stmt = $pdo->prepare("UPDATE applications SET status = ? WHERE id = ?");
    $stmt->execute([$status, $application_id]);

    $stmt = $pdo->prepare("SELECT * FROM applications WHERE id = ?");
    $stmt->execute([$application_id]);
    $application = $stmt->fetch();

    $message = "Dear " . $application['user_id'] .  ", your application has been rejected.";
    $stmt = $pdo->prepare("INSERT INTO messages (from_user_id, to_user_id, message) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $application['user_id'], $message]);

    $stmt = $pdo->prepare("DELETE FROM applications WHERE id = ?");
    $stmt->execute([$application_id]);

    $_SESSION['message'] = "Application rejected and removed successfully!";
    header("Location: ../hr_dashboard.php");
    exit();
}


if (isset($_POST['acceptApplicationBtn'])) {
    $application_id = $_POST['application_id'];
    $status = 'accepted';

    $stmt = $pdo->prepare("UPDATE applications SET status = ? WHERE id = ?");
    $stmt->execute([$status, $application_id]);

    $stmt = $pdo->prepare("SELECT * FROM applications WHERE id = ?");
    $stmt->execute([$application_id]);
    $application = $stmt->fetch();

    $message = "Dear " . $application['user_id'] .  ", congratulations! Your application has been accepted.";
    $stmt = $pdo->prepare("INSERT INTO messages (from_user_id, to_user_id, message) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $application['user_id'], $message]);


    $_SESSION['message'] = "Application accepted and removed successfully!";
    header("Location: ../hr_dashboard.php"); 
    exit();
}




if (isset($_GET['logoutAUser'])) {
    session_unset();
    session_destroy();

    header("Location: ../login.php");
    exit();
}
?>
