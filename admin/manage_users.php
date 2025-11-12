<?php
session_start();
include '../config/db.php';

// Approve user
if (isset($_GET['approve'])) {
    $userId = $_GET['approve'];
    $conn->query("UPDATE users SET status = 'approved' WHERE id = $userId");
}

// Reject user
if (isset($_GET['reject'])) {
    $userId = $_GET['reject'];
    $conn->query("UPDATE users SET status = 'rejected' WHERE id = $userId");
}

// Get all pending users
$result = $conn->query("SELECT * FROM users WHERE status = 'pending'");
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="../assets/css/fun.css">
    <title>Admin - Manage Users</title>
</head>
<body>

<div class="admin-panel">
    <h2>Pending User Approvals</h2>
<a href="../dashboards/admin_dashboard.php" class="btn">⬅️ Back to Dashboard</a>



    <?php if ($result->num_rows > 0): ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Role</th>
                <th>Email</th>
                <th>Phone</th>
                <th>City</th>
                <th>Age</th>
                <th>Gender</th>
                <th>ID Image</th>
                <th>Selfie</th>
                <th>Actions</th>
            </tr>

            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></td>
                    <td><?php echo ucfirst($row['role']); ?></td>
                    <td><?php echo $row['email']; ?></td>
                    <td><?php echo $row['phone']; ?></td>
                    <td><?php echo $row['city']; ?></td>
                    <td><?php echo $row['age']; ?></td>
                    <td><?php echo ucfirst($row['gender']); ?></td>
                    <td>
                        <a href="../uploads/<?php echo $row['role']; ?>_ids/<?php echo $row['id_image']; ?>" target="_blank">View</a>
                    </td>
                    <td>
                        <a href="../uploads/<?php echo $row['role']; ?>_selfies/<?php echo $row['selfie_image']; ?>" target="_blank">View</a>
                    </td>
                    <td>
                        <a href="?approve=<?php echo $row['id']; ?>">✅ Approve</a> |
                        <a href="?reject=<?php echo $row['id']; ?>">❌ Reject</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No pending users found.</p>
    <?php endif; ?>

    <br>
    <a href="../auth/logout.php" class="btn" style="background: linear-gradient(135deg, #ff5e5e, #ff9966);">🚪 Logout</a>
</div>

</body>
</html>
