<?php
session_start();
include '../config/db.php';

// Restrict access to only admins
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit();
}

// Handle user deletion
if (isset($_POST['delete_user'])) {
    $userId = intval($_POST['delete_user']);
    $conn->query("DELETE FROM users WHERE id = $userId");
    $message = "🗑️ User deleted successfully.";
}

// Fetch all registered users (clients and workers only)
$users = $conn->query("SELECT * FROM users WHERE role IN ('client', 'worker') ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="../assets/css/fun.css">

    <title>Admin - Manage Users</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: center; }
        th { background-color: #f4f4f4; }
        button { padding: 6px 12px; cursor: pointer; }
        .danger { background-color: red; color: white; }
    </style>
</head>
<body>

<h2>👤 Manage Registered Users</h2>

<?php if (isset($message)) echo "<p style='color:green;'>$message</p>"; ?>

<?php if ($users->num_rows > 0): ?>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Registered On</th>
            <th>Action</th>
        </tr>
        <?php while ($user = $users->fetch_assoc()): ?>
        <tr>
            <td><?php echo $user['id']; ?></td>
            <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
            <td><?php echo htmlspecialchars($user['email']); ?></td>
            <td><?php echo ucfirst($user['role']); ?></td>
            <td><?php echo $user['created_at']; ?></td>
            <td>
                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');">
                    <input type="hidden" name="delete_user" value="<?php echo $user['id']; ?>">
                    <button type="submit" class="danger">🗑️ Delete</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <p>No registered users found.</p>
<?php endif; ?>

<br><br>
<a href="../dashboards/admin_dashboard.php">⬅ Back to Dashboard</a>

</body>
</html>
