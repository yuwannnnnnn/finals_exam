<?php
require_once 'dbConfig.php';

function insertNewUser($pdo, $username, $password, $role) {
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->execute([$username, $password, $role]);
        return ['status' => '200', 'message' => 'User registered successfully'];
    } catch (Exception $e) {
        return ['status' => '500', 'message' => $e->getMessage()];
    }
}

function createJobPost($pdo, $title, $description, $created_by) {
    try {
        $stmt = $pdo->prepare("INSERT INTO job_posts (title, description, created_by) VALUES (?, ?, ?)");
        $stmt->execute([$title, $description, $created_by]);
        return ['status' => '200', 'message' => 'Job post created successfully'];
    } catch (Exception $e) {
        return ['status' => '500', 'message' => $e->getMessage()];
    }
}

function getMessagesForApplicant($applicantId, $hrUserId) {
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT m.message, m.created_at, u.username AS from_username
        FROM messages m
        JOIN users u ON m.from_user_id = u.id
        WHERE (m.from_user_id = ? AND m.to_user_id = ?) 
           OR (m.from_user_id = ? AND m.to_user_id = ?)
        ORDER BY m.created_at ASC
    ");
    $stmt->execute([$applicantId, $hrUserId, $hrUserId, $applicantId]);
    return $stmt->fetchAll();
}


function sendMessageToHR($fromUserId, $toUserId, $messageContent) {
    global $pdo;
    
    $query = "INSERT INTO messages (from_user_id, to_user_id, message, created_at) VALUES (?, ?, ?, NOW())";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$fromUserId, $toUserId, $messageContent]);
}


function applyForJob($pdo, $job_id, $applicant_id, $resume_path) {
    try {
        $stmt = $pdo->prepare("INSERT INTO applications (job_id, applicant_id, resume_path) VALUES (?, ?, ?)");
        $stmt->execute([$job_id, $applicant_id, $resume_path]);
        return ['status' => '200', 'message' => 'Application submitted successfully'];
    } catch (Exception $e) {
        return ['status' => '500', 'message' => $e->getMessage()];
    }
}

function getJobPosts($user_id) {
    global $pdo;  
    $stmt = $pdo->prepare("SELECT * FROM job_posts WHERE created_by = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}



function getHRUsers() {
    global $pdo;

    $query = "SELECT id, username FROM users WHERE role = 'hr'";
    $stmt = $pdo->query($query);
    return $stmt->fetchAll();
}



function sendMessage($pdo, $sender_id, $receiver_id, $message) {
    try {
        $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        $stmt->execute([$sender_id, $receiver_id, $message]);
        return ['status' => '200', 'message' => 'Message sent successfully'];
    } catch (Exception $e) {
        return ['status' => '500', 'message' => $e->getMessage()];
    }
}


function getMessages($pdo, $user_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM messages WHERE receiver_id = ? OR sender_id = ?");
        $stmt->execute([$user_id, $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

function getUserByUsername($pdo, $username) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return null;
    }
}



function loginUser($pdo, $username, $password) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            return [
                'success' => true,
                'user_id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role'],
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Invalid username or password'
            ];
        }
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

function getAllHRUsers($pdo) {
    $stmt = $pdo->query("SELECT id, username FROM users WHERE role = 'hr'");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllJobPosts() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM job_posts");
    $stmt->execute();
    return $stmt->fetchAll();
}


function getMessagesForHR($pdo, $hr_id) {
    $stmt = $pdo->prepare("
        SELECT m.*, u.username AS from_username 
        FROM messages m 
        JOIN users u ON m.from_user_id = u.id 
        WHERE m.to_user_id = ?
    ");
    $stmt->execute([$hr_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getJobPostsForApplicants() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM job_posts");
    $stmt->execute();
    return $stmt->fetchAll();
}

function getApplicationsByApplicant($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT job_posts.title, applications.status, applications.cover_message, applications.resume
        FROM applications
        JOIN job_posts ON applications.job_post_id = job_posts.id
        WHERE applications.user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}


?>
