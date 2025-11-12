<?php
session_start();

// Block access if not logged in as admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    
    <!-- 🌟 Main Custom Styles -->
    <link rel="stylesheet" href="../assets/css/admin_dashboard.css">

    <!-- 🌐 Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="admin-container">
    <h1><i class="fa-solid fa-user-tie"></i> Welcome Admin: <span><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span></h1>

    <ul class="admin-links">
        <li><a href="../admin/manage_users.php"><i class="fa-solid fa-user-check"></i> Manage User Approvals</a></li>
        <li><a href="../admin/review_applications.php"><i class="fa-solid fa-brain"></i> Review Worker Applications</a></li>
        <li><a href="../admin/view_requests.php"><i class="fa-solid fa-file-lines"></i> Manage Document Requests</a></li>
        <li><a href="../admin/admin_payment_requests.php"><i class="fa-solid fa-money-bill-wave"></i> Handle Payment Requests</a></li>
        <li><a href="manage_users.php"><i class="fa-solid fa-users-gear"></i> Manage Users</a></li>
        <li><a href="../auth/logout.php" class="logout-link"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
    </ul>
</div>

</body>
</html>
