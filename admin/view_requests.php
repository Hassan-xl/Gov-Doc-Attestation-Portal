<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit();
}

$message = "";

// ✅ Handle delete request
if (isset($_POST['delete_request'])) {
    $deleteId = intval($_POST['delete_request']);
    $conn->query("DELETE FROM document_requests WHERE id = $deleteId");
    $message = "🗑️ Document request deleted successfully.";
}

// 📥 Fetch all document requests
$docs = $conn->query("
    SELECT dr.*, 
           c.first_name AS client_fname, c.last_name AS client_lname,
           w.first_name AS worker_fname, w.last_name AS worker_lname
    FROM document_requests dr
    JOIN users c ON dr.client_id = c.id
    LEFT JOIN users w ON dr.worker_id = w.id
    ORDER BY dr.created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="../assets/css/fun.css">

    <title>Admin - View All Requests</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: center; }
        th { background-color: #f2f2f2; }
        button.delete { background-color: red; color: white; border: none; padding: 6px 12px; cursor: pointer; }
        .gray { font-size: 0.85em; color: gray; }
        .msg { color: green; margin-bottom: 15px; }
    </style>
</head>
<body>

<h2>📂 All Document Requests</h2>

<?php if ($message): ?>
    <p class="msg"><?php echo $message; ?></p>
<?php endif; ?>

<table>
    <tr>
        <th>Title</th>
        <th>Client</th>
        <th>Assigned Worker</th>
        <th>Status</th>
        <th>Courier</th>
        <th>Sent At</th>
        <th>Received At</th>
        <th>Return Status</th>
        <th>Return Sent</th>
        <th>Return Received</th>
        <th>Uploaded</th>
        <th>Action</th>
    </tr>

    <?php while ($doc = $docs->fetch_assoc()): ?>
    <tr>
        <td><?php echo htmlspecialchars($doc['document_title']); ?></td>
        <td><?php echo htmlspecialchars($doc['client_fname'] . ' ' . $doc['client_lname']); ?></td>
        <td><?php echo $doc['worker_id'] ? htmlspecialchars($doc['worker_fname'] . ' ' . $doc['worker_lname']) : '—'; ?></td>
        <td><?php echo ucfirst($doc['status']); ?></td>
        <td>
            <?php
                if ($doc['courier_status'] === 'not_sent') echo '❌ Not Sent';
                elseif ($doc['courier_status'] === 'sent') echo '📦 Sent (Not Received)';
                elseif ($doc['courier_status'] === 'received') echo '✅ Received';
                else echo '—';
            ?>
        </td>
        <td><?php echo $doc['sent_at'] ?: '—'; ?></td>
        <td><?php echo $doc['received_at'] ?: '—'; ?></td>
        <td>
            <?php
                if ($doc['courier_return_status'] === 'not_sent') echo '❌ Not Sent';
                elseif ($doc['courier_return_status'] === 'sent') echo '📦 Sent Back';
                elseif ($doc['courier_return_status'] === 'received') echo '✅ Received';
                else echo '—';
            ?>
        </td>
        <td><?php echo $doc['return_sent_at'] ?: '—'; ?></td>
        <td><?php echo $doc['return_received_at'] ?: '—'; ?></td>
        <td><?php echo $doc['created_at']; ?></td>
        <td>
            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this request?');">
                <input type="hidden" name="delete_request" value="<?php echo $doc['id']; ?>">
                <button type="submit" class="delete">🗑️ Delete</button>
            </form>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<br><br>
<a href="../dashboards/admin_dashboard.php">⬅ Back to Admin Dashboard</a>

</body>
</html>
