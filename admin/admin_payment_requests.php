<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit();
}

// ✅ Approve Payment
if (isset($_POST['approve_payment'])) {
    $docId = intval($_POST['approve_payment']);
    $conn->query("UPDATE document_requests SET payment_status = 'approved', payment_approved_at = NOW() WHERE id = $docId");
    $_SESSION['msg'] = "✅ Payment approved.";
    header("Location: admin_payment_requests.php");
    exit();
}

// ❌ Reject Payment
if (isset($_POST['reject_payment'])) {
    $docId = intval($_POST['reject_payment']);
    $conn->query("UPDATE document_requests SET payment_status = 'rejected' WHERE id = $docId");
    $_SESSION['msg'] = "❌ Payment rejected.";
    header("Location: admin_payment_requests.php");
    exit();
}

// 📤 Send Instruction
if (isset($_POST['send_instruction'])) {
    $docId = intval($_POST['doc_id']);
    $instruction = $conn->real_escape_string($_POST['payment_instruction']);
    $conn->query("UPDATE document_requests SET payment_instruction = '$instruction' WHERE id = $docId");

    $check = $conn->query("SELECT payment_screenshot FROM document_requests WHERE id = $docId");
    $row = $check->fetch_assoc();
    $status = !empty($row['payment_screenshot']) ? 'screenshot_uploaded' : 'details_sent';
    $conn->query("UPDATE document_requests SET payment_status = '$status' WHERE id = $docId");

    $_SESSION['msg'] = "📤 Instruction sent.";
    header("Location: admin_payment_requests.php");
    exit();
}

// 🗑️ Delete Request
if (isset($_POST['delete_request'])) {
    $docId = intval($_POST['delete_request']);
    $conn->query("DELETE FROM document_requests WHERE id = $docId");
    $_SESSION['msg'] = "🗑️ Payment request deleted.";
    header("Location: admin_payment_requests.php");
    exit();
}

// 🔍 Fetch Requests
$requests = $conn->query("
    SELECT dr.*, u.first_name, u.last_name 
    FROM document_requests dr 
    JOIN users u ON dr.client_id = u.id 
    WHERE dr.payment_requested = 1 
    ORDER BY dr.created_at DESC
");
?>


<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="../assets/css/fun.css">

    <title>Admin - Payment Requests</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: center; }
        th { background: #f4f4f4; }
        textarea { width: 100%; height: 60px; }
        button { padding: 6px 12px; margin: 2px; cursor: pointer; }
        .danger { background: red; color: white; }
        .success { background: green; color: white; }
        .delete { background: black; color: white; }
        .gray { font-size: 0.9em; color: gray; }
        .msg { color: green; margin-bottom: 15px; }
    </style>
</head>
<body>

<h2>💰 Client Payment Requests</h2>

<?php if (isset($_SESSION['msg'])): ?>
    <p class="msg"><?php echo $_SESSION['msg']; unset($_SESSION['msg']); ?></p>
<?php endif; ?>


<?php if ($requests->num_rows > 0): ?>
<table>
    <tr>
        <th>Title</th>
        <th>Client</th>
        <th>Status</th>
        <th>Instruction</th>
        <th>Screenshot</th>
        <th>Actions</th>
    </tr>
    <?php while ($row = $requests->fetch_assoc()): ?>
    <tr>
        <td><?php echo htmlspecialchars($row['document_title']); ?></td>
        <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>

        <td>
            <?php
                if ($row['payment_status'] === 'screenshot_uploaded') {
                    echo '⏳ Pending';
                } elseif ($row['payment_status'] === 'approved') {
                    echo '✅ Approved<br><span class="gray">' . $row['payment_approved_at'] . '</span>';
                } elseif ($row['payment_status'] === 'rejected') {
                    echo '❌ Rejected';
                } else {
                    echo ucfirst($row['payment_status']);
                }
            ?>
        </td>

        <td>
            <?php if (empty($row['payment_instruction'])): ?>
                <form method="POST">
                    <input type="hidden" name="doc_id" value="<?php echo $row['id']; ?>">
                    <textarea name="payment_instruction" required placeholder="e.g. EasyPaisa 03XXXXXXXXX"></textarea>
                    <button type="submit" name="send_instruction">Send</button>
                </form>
            <?php else: ?>
                <?php echo htmlspecialchars($row['payment_instruction']); ?>
            <?php endif; ?>
        </td>

        <td>
            <?php if (!empty($row['payment_screenshot'])): ?>
                <a href="../uploads/payment_screenshots/<?php echo $row['payment_screenshot']; ?>" target="_blank">View</a><br>
                <span class="gray"><?php echo $row['payment_uploaded_at']; ?></span>
            <?php else: ?>
                —
            <?php endif; ?>
        </td>

        <td>
            <?php if (!empty($row['payment_screenshot']) && in_array($row['payment_status'], ['screenshot_uploaded', 'details_sent'])): ?>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="approve_payment" value="<?php echo $row['id']; ?>">
                    <button type="submit" class="success">✅ Approve</button>
                </form>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="reject_payment" value="<?php echo $row['id']; ?>">
                    <button type="submit" class="danger">❌ Reject</button>
                </form>
            <?php endif; ?>
            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this payment request?');" style="display:inline;">
                <input type="hidden" name="delete_request" value="<?php echo $row['id']; ?>">
                <button type="submit" class="delete">🗑️ Delete</button>
            </form>
        </td>
    </tr>
    <?php endwhile; ?>
</table>
<?php else: ?>
    <p>No payment requests found.</p>
<?php endif; ?>

<br><br>
<a href="../dashboards/admin_dashboard.php">⬅ Back to Dashboard</a>

</body>
</html>
