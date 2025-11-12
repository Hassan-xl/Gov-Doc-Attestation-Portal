<?php
session_start();
include '../config/db.php';

// ✅ Only clients can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: ../auth/login.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['document_file'])) {
    $clientId = $_SESSION['user_id'];
    $docTitle = $_POST['document_title'];
    $file = $_FILES['document_file'];

    // Check for PDF
    $fileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($fileType !== 'pdf') {
        $message = "Only PDF files are allowed.";
    } else {
        $fileName = uniqid() . "_" . basename($file['name']);
        $targetPath = "../uploads/documents/" . $fileName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Save in DB
            $stmt = $conn->prepare("INSERT INTO document_requests (client_id, document_title, file_name) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $clientId, $docTitle, $fileName);
            $stmt->execute();

            $message = "Document uploaded successfully!";
        } else {
            $message = "Error uploading file.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Document</title>
</head>
<body>
    <h2>Upload a Document for Attestation</h2>
    <p style="color: green;"><?php echo $message; ?></p>

    <form method="POST" enctype="multipart/form-data">
        <label>Document Title:</label><br>
        <input type="text" name="document_title" required><br><br>

        <label>Upload PDF:</label><br>
        <input type="file" name="document_file" accept="application/pdf" required><br><br>

        <input type="submit" value="Upload">
    </form>

    <br>
    <a href="client_dashboard.php">⬅️ Back to Dashboard</a>
</body>
</html>
