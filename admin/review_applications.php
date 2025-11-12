<?php
session_start();
include '../config/db.php';

// 🔐 Only admin allowed
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit();
}

// ✅ Handle Approve / Reject / Delete
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $appId = intval($_POST['application_id']);

    // Approve
    if (isset($_POST['approve'])) {
        $conn->query("UPDATE applications SET status = 'approved' WHERE id = $appId");

        $getInfo = $conn->query("SELECT document_id, worker_id FROM applications WHERE id = $appId");
        $info = $getInfo->fetch_assoc();
        $docId = $info['document_id'];
        $workerId = $info['worker_id'];

        $conn->query("UPDATE document_requests SET status = 'assigned', worker_id = $workerId WHERE id = $docId");

        $conn->query("UPDATE applications 
                      SET status = 'rejected', admin_comment = 'Another worker was selected.' 
                      WHERE document_id = $docId AND id != $appId");

        header("Location: review_applications.php");
        exit();
    }

    // Reject
    if (isset($_POST['reject'])) {
        $comment = $conn->real_escape_string($_POST['comment']);
        $conn->query("UPDATE applications SET status = 'rejected', admin_comment = '$comment' WHERE id = $appId");
        header("Location: review_applications.php");
        exit();
    }

    // Delete
    if (isset($_POST['delete'])) {
        $conn->query("DELETE FROM applications WHERE id = $appId");
        header("Location: review_applications.php");
        exit();
    }
}

// ✅ Fetch pending applications
$applications = $conn->query("
    SELECT a.*, u.first_name AS worker_first, u.last_name AS worker_last,
           d.document_title, d.job_type, d.description
    FROM applications a
    JOIN users u ON a.worker_id = u.id
    JOIN document_requests d ON a.document_id = d.id
    WHERE a.status = 'pending'
    ORDER BY a.created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="../assets/css/fun.css">

    <title>Admin - Review Applications</title>
</head>
<body>
<h2>Pending Applications</h2>

<?php if ($applications->num_rows > 0): ?>
    <table border="1" cellpadding="8" cellspacing="0">
        <tr>
            <th>Worker</th>
            <th>Document Title</th>
            <th>Type</th>
            <th>Description</th>
            <th>Apply Date</th>
            <th>Actions</th>
        </tr>

        <?php while ($row = $applications->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['worker_first'] . ' ' . $row['worker_last']); ?></td>
                <td><?php echo htmlspecialchars($row['document_title']); ?></td>
                <td><?php echo htmlspecialchars($row['job_type']); ?></td>
                <td><?php echo nl2br(htmlspecialchars($row['description'])); ?></td>
                <td><?php echo $row['created_at']; ?></td>
                <td>
                    <!-- Approve -->
                    <form method="POST" style="display:inline-block;">
                        <input type="hidden" name="application_id" value="<?php echo $row['id']; ?>">
                        <button type="submit" name="approve">✅ Approve</button>
                    </form>

                    <!-- Reject -->
                    <form method="POST" style="display:inline-block;">
                        <input type="hidden" name="application_id" value="<?php echo $row['id']; ?>">
                        <input type="text" name="comment" placeholder="Rejection reason" required>
                        <button type="submit" name="reject">❌ Reject</button>
                    </form>

                    <!-- Delete -->
                    <form method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this application?');">
                        <input type="hidden" name="application_id" value="<?php echo $row['id']; ?>">
                        <button type="submit" name="delete" style="background-color:black;color:white;">🗑️ Delete</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <p>No pending applications.</p>
<?php endif; ?>

<br><br>
<a href="../dashboards/admin_dashboard.php">⬅️ Back to Dashboard</a> |
<a href="../auth/logout.php">Logout</a>
</body>
</html>
