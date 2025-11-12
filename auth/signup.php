<?php
include '../config/db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $city = $_POST['city'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $role = $_POST['role'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $id_image = $_FILES['id_image'];
    $selfie_image = $_FILES['selfie_image'];

    $id_folder = ($role == 'client') ? '../uploads/client_ids/' : '../uploads/worker_ids/';
    $selfie_folder = ($role == 'client') ? '../uploads/client_selfies/' : '../uploads/worker_selfies/';

    $id_filename = uniqid() . '_' . basename($id_image['name']);
    $selfie_filename = uniqid() . '_' . basename($selfie_image['name']);

    move_uploaded_file($id_image['tmp_name'], $id_folder . $id_filename);
    move_uploaded_file($selfie_image['tmp_name'], $selfie_folder . $selfie_filename);

    $sql = "INSERT INTO users (first_name, last_name, email, phone, city, age, gender, password, role, id_image, selfie_image)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssss", $first_name, $last_name, $email, $phone, $city, $age, $gender, $password, $role, $id_filename, $selfie_filename);

    if ($stmt->execute()) {
        $message = "✅ Signup successful! You can now log in.";
    } else {
        $message = "❌ Signup failed: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Signup</title>
    <link rel="stylesheet" href="../assets/css/signup.css">
</head>
<body>
    <div class="signup-container">
        <h2>Signup (Client or Worker)</h2>
        <?php if (!empty($message)) echo "<p style='color:green; text-align:center;'>$message</p>"; ?>

        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-grid">
                <!-- 4x2 Input Grid -->
                <div>
                    <label>First Name:</label>
                    <input type="text" name="first_name" required>
                </div>
                <div>
                    <label>Last Name:</label>
                    <input type="text" name="last_name" required>
                </div>

                <div>
                    <label>Email:</label>
                    <input type="email" name="email" required>
                </div>
                <div>
                    <label>Phone:</label>
                    <input type="text" name="phone" required>
                </div>

                <div>
                    <label>City:</label>
                    <input type="text" name="city" required>
                </div>
                <div>
                    <label>Age:</label>
                    <input type="number" name="age" required>
                </div>

                <div>
                    <label>Gender:</label>
                    <select name="gender" required>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label>Password:</label>
                    <input type="password" name="password" required>
                </div>

                <div>
                    <label>Upload CNIC/ID Image:</label>
                    <input type="file" name="id_image" accept="image/*" required>
                </div>
                <div>
                    <label>Upload Selfie:</label>
                    <input type="file" name="selfie_image" accept="image/*" required>
                </div>

                <!-- Full-width items -->
                <div class="full-width">
                    <label>Role:</label>
                    <select name="role" required>
                        <option value="client">Client</option>
                        <option value="worker">Worker</option>
                    </select>
                </div>

                <div class="full-width">
                    <button type="submit">Sign Up</button>
                </div>

                <!-- 🚀 Rocket style links -->
                <div class="full-width links">
                    <a href="../index.php" class="link-btn">
                        🚀 Back to Home
                    </a>
                    <a href="login.php" class="link-btn">
                        🚀 Already have an account? Login
                    </a>
                </div>
            </div>
        </form>
    </div>
</body>
</html>
