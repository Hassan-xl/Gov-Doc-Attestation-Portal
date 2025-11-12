<?php
session_start();
include '../config/db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role = ?");
    $stmt->bind_param("ss", $email, $role);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            if ($user['status'] !== 'approved') {
                $error = "Your account is currently '" . $user['status'] . "'. Please wait for admin approval.";
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['role'] = $user['role'];

                if ($role == 'client') {
                    header("Location: ../dashboards/client_dashboard.php");
                } else {
                    header("Location: ../dashboards/worker_dashboard.php");
                }
                exit();
            }
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "User not found with this role.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="../assets/css/login.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <?php if (!empty($error)) echo "<p style='color: red; font-weight: bold;'>$error</p>"; ?>

        <form action="" method="POST">
            <label>Email:</label>
            <input type="email" name="email" required>

            <label>Password:</label>
            <input type="password" name="password" required>

            <label>Login as:</label>
            <select name="role" required>
                <option value="client">Client</option>
                <option value="worker">Worker</option>
            </select>

            <button type="submit">Login</button>
        </form>

        <div class="links">
            <a href="signup.php" class="link-btn">🚀 Sign up here</a>
            <a href="../index.php" class="link-btn">🏠 Back to Home</a>
        </div>
    </div>
</body>
</html>
