<?php
session_start();
include '../config/db.php';

// 🔒 Only admins allowed
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit();
}

$message = "";

// Handle delete user
if (isset($_POST['delete_user'])) {
    $userId = intval($_POST['delete_user']);
    $conn->query("DELETE FROM users WHERE id = $userId");
    $message = "🗑️ User deleted successfully.";
}

// Fetch all users (clients and workers)
$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="../assets/css/fun.css">

    <title>Admin - Manage Users</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        h2 { margin-bottom: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: center; }
        th { background: #f0f0f0; }
        .danger { background: red; color: white; border: none; padding: 6px 12px; cursor: pointer; }
        .gray { color: gray; font-size: 0.9em; }
    </style>
</head>
<body>

<h2>👥 Admin - Manage Users</h2>

<?php if ($message): ?>
    <p style="color: green;"><?php echo $message; ?></p>
<?php endif; ?>

<?php if ($users->num_rows > 0): ?>
    <table>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Registered</th>
            <th>Action</th>
        </tr>
        <?php while ($user = $users->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo ucfirst($user['role']); ?></td>
                <td class="gray"><?php echo $user['created_at']; ?></td>
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
    <p>No users found.</p>
<?php endif; ?>

<br><br>
<a href="../dashboards/admin_dashboard.php">⬅ Back to Admin Dashboard</a>

</body>
</html>
