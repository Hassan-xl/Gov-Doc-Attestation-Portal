<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: ../auth/login.php");
    exit();
}

$clientId = $_SESSION['user_id'];
$message = "";

// ✅ Mark as Sent
if (isset($_POST['mark_sent'])) {
    $docId = intval($_POST['mark_sent']);
    $conn->query("UPDATE document_requests SET courier_status = 'sent', sent_at = NOW() WHERE id = $docId AND client_id = $clientId");
    $_SESSION['msg'] = "📦 Marked as Sent!";
    header("Location: client_dashboard.php");
    exit();
}

// ✅ Request Payment
if (isset($_POST['request_payment'])) {
    $docId = intval($_POST['request_payment']);
    $conn->query("UPDATE document_requests SET payment_requested = 1, payment_status = 'requested' WHERE id = $docId AND client_id = $clientId");
    $_SESSION['msg'] = "💰 Payment request sent to admin.";
    header("Location: client_dashboard.php");
    exit();
}

// ✅ Upload Screenshot
if (isset($_POST['upload_screenshot']) && isset($_FILES['payment_screenshot'])) {
    $docId = intval($_POST['upload_screenshot']);
    $check = $conn->query("SELECT payment_screenshot FROM document_requests WHERE id = $docId AND client_id = $clientId");
    $row = $check->fetch_assoc();

    if (!empty($row['payment_screenshot'])) {
        $_SESSION['msg'] = "⚠️ Screenshot already uploaded.";
    } else {
        $file = $_FILES['payment_screenshot'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png'])) {
            $_SESSION['msg'] = "❌ Only JPG, JPEG, or PNG allowed.";
        } else {
            $fileName = uniqid() . '_' . basename($file['name']);
            $path = "../uploads/payment_screenshots/" . $fileName;
            if (move_uploaded_file($file['tmp_name'], $path)) {
                $conn->query("UPDATE document_requests SET payment_screenshot = '$fileName', payment_status = 'screenshot_uploaded', payment_uploaded_at = NOW() WHERE id = $docId AND client_id = $clientId");
                $_SESSION['msg'] = "📄 Screenshot uploaded. Awaiting approval.";
            } else {
                $_SESSION['msg'] = "❌ Upload failed.";
            }
        }
    }
    header("Location: client_dashboard.php");
    exit();
}

// ✅ Mark Return Received
if (isset($_POST['mark_return_received'])) {
    $docId = intval($_POST['mark_return_received']);
    $conn->query("UPDATE document_requests SET courier_return_status = 'received', return_received_at = NOW() WHERE id = $docId AND client_id = $clientId");
    $_SESSION['msg'] = "📦 Marked as received.";
    header("Location: client_dashboard.php");
    exit();
}

// ✅ Upload Document
$countResult = $conn->query("SELECT COUNT(*) AS total FROM document_requests WHERE client_id = $clientId");
$documentCount = $countResult->fetch_assoc()['total'];

if ($documentCount < 2 && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['document_file'])) {
    $docTitle = $_POST['document_title'];
    $jobType = $_POST['job_type'];
    $description = $_POST['description'];
    $file = $_FILES['document_file'];
    $fileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if ($fileType !== 'pdf') {
        $_SESSION['msg'] = "❌ Only PDF allowed.";
    } else {
        $fileName = uniqid() . "_" . basename($file['name']);
        $targetPath = "../uploads/documents/" . $fileName;
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $stmt = $conn->prepare("INSERT INTO document_requests (client_id, document_title, job_type, description, file_name) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $clientId, $docTitle, $jobType, $description, $fileName);
            $stmt->execute();
            $_SESSION['msg'] = "✅ Uploaded successfully!";
        } else {
            $_SESSION['msg'] = "❌ Upload failed!";
        }
    }
    header("Location: client_dashboard.php");
    exit();
}

$documents = $conn->query("SELECT * FROM document_requests WHERE client_id = $clientId ORDER BY created_at DESC");
?>


<!DOCTYPE html>
<html>
<head>
    <title>Client Dashboard</title>
    <link rel="stylesheet" href="../assets/css/client_dashboard.css">
    <style>
        .toggle-btn {
            padding: 10px 20px;
            background-color: dodgerblue;
            color: white;
            border: none;
            cursor: pointer;
            margin: 5px;
        }
        #applicationForm, #applicationsSection {
            display: none;
            margin-top: 20px;
        }
    </style>
    <script>
        function toggleForm() {
            const form = document.getElementById('applicationForm');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }

        function toggleApplications() {
            const section = document.getElementById('applicationsSection');
            section.style.display = section.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</head>
<body>

<h2>Welcome, <?php echo $_SESSION['user_name']; ?> (Client)</h2>
<p style="color: green;"><?php echo $message; ?></p>

<button class="toggle-btn" onclick="toggleForm()">📩 Make an Application</button>
<button class="toggle-btn" onclick="toggleApplications()">📄 See My Applications</button>

<!-- Upload Form -->
<div id="applicationForm">
<?php if ($documentCount < 2): ?>
    <h3>Upload a Document</h3>
    <form method="POST" enctype="multipart/form-data">
        <label>Document Title:</label><br>
        <input type="text" name="document_title" required><br><br>

        <label>Job Type:</label><br>
        <select name="job_type" required>
            <option value="">-- Select Job Type --</option>
            <option value="Degree Attestation">Degree Attestation</option>
            <option value="Birth Certificate">Birth Certificate</option>
            <option value="Experience Letter">Experience Letter</option>
            <option value="NADRA / CNIC">NADRA / CNIC</option>
            <option value="Other">Other</option>
        </select><br><br>

        <label>Description:</label><br>
        <textarea name="description" rows="4" cols="50" required></textarea><br><br>

        <label>Upload PDF:</label><br>
        <input type="file" name="document_file" accept="application/pdf" required><br><br>

        <input type="submit" value="Upload">
    </form>
<?php else: ?>
    <p style="color: red;">⚠️ Upload limit reached. You can only upload 2 documents at a time.</p>
<?php endif; ?>
</div>

<!-- Uploaded Applications -->
<div id="applicationsSection">
<h3>Your Uploaded Documents</h3>
<?php if ($documents->num_rows > 0): ?>
    <table border="1" cellpadding="8" cellspacing="0">
        <tr>
            <th>Title</th>
            <th>Job Type</th>
            <th>Description</th>
            <th>File</th>
            <th>Status</th>
            <th>Courier</th>
            <th>Attested Doc</th>
            <th>Payment</th>
            <th>Received Courier</th>
            <th>Uploaded</th>
        </tr>
        <?php while ($doc = $documents->fetch_assoc()): ?>
        <tr>
            <td><?php echo $doc['document_title']; ?></td>
            <td><?php echo $doc['job_type']; ?></td>
            <td><?php echo nl2br(htmlspecialchars($doc['description'])); ?></td>
            <td><a href="../uploads/documents/<?php echo $doc['file_name']; ?>" target="_blank">View</a></td>
            <td><?php echo ucfirst($doc['status']); ?></td>
            <td>
                <?php
               if (!empty($doc['courier_request_note'])) {
    echo "<b>Courier Address:</b><br><i>" . nl2br(htmlspecialchars($doc['courier_request_note'])) . "</i><br>";

    if ($doc['courier_status'] === 'not_sent') {
        echo '<form method="POST"><input type="hidden" name="mark_sent" value="' . $doc['id'] . '"><button type="submit">Mark Sent</button></form>';
    } elseif ($doc['courier_status'] === 'sent') {
        echo "📦 Sent<br><small>{$doc['sent_at']}</small>";
    } elseif ($doc['courier_status'] === 'received') {
        echo "✅ Received<br><small>{$doc['received_at']}</small>";
    }
} else {
    echo "<i>Waiting for worker’s courier address...</i>";
}

                ?>
            </td>
            <td>
                <?php if ($doc['job_done_file']): ?>
                    ✅ <a href="../uploads/attested_docs/<?php echo $doc['job_done_file']; ?>" target="_blank">View</a><br>
                    <small><?php echo $doc['done_at']; ?></small>
                <?php else: ?>—<?php endif; ?>
            </td>
            <td>
                <?php
                if (!$doc['job_done_file']) {
                    echo '—';
                } else {
                    switch ($doc['payment_status']) {
                        case 'none':
                            echo '<form method="POST"><input type="hidden" name="request_payment" value="' . $doc['id'] . '"><button type="submit">Request Payment</button></form>';
                            break;
                        case 'requested':
                            echo '⏳ Waiting for admin';
                            break;
                        case 'details_sent':
                            echo '<b>Pay to:</b> ' . htmlspecialchars($doc['payment_instruction']) . '<br>';
                            echo '<form method="POST" enctype="multipart/form-data">
                                    <input type="file" name="payment_screenshot" required><br>
                                    <input type="hidden" name="upload_screenshot" value="' . $doc['id'] . '">
                                    <button type="submit">Upload Screenshot</button>
                                  </form>';
                            break;
                        case 'screenshot_uploaded':
                            echo '📄 Uploaded<br>⏳ Waiting for admin';
                            break;
                        case 'approved':
                            echo '✅ Approved<br><small>' . $doc['payment_approved_at'] . '</small>';
                            break;
                        case 'rejected':
                            echo '❌ Rejected<br>Please re-upload';
                            break;
                        default:
                            echo '—';
                    }
                }
                ?>
            </td>
            <td>
                <?php
                if ($doc['courier_return_status'] === 'sent') {
                    echo "📦 Sent by worker<br><small>{$doc['return_sent_at']}</small><br>";
                    echo '<form method="POST">
                            <input type="hidden" name="mark_return_received" value="' . $doc['id'] . '">
                            <button type="submit">Mark Received</button>
                          </form>';
                } elseif ($doc['courier_return_status'] === 'received') {
                    echo "✅ Received<br><small>{$doc['return_received_at']}</small>";
                } else {
                    echo "—";
                }
                ?>
            </td>
            <td><?php echo $doc['created_at']; ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <p>No documents uploaded yet.</p>
<?php endif; ?>
</div>

<br><br>
<a href="../auth/logout.php">Logout</a>

<!-- ✨ Fireflies -->
<script>
document.addEventListener("DOMContentLoaded", function () {
  const container = document.createElement('div');
  container.style.position = 'fixed';
  container.style.top = 0;
  container.style.left = 0;
  container.style.width = '100vw';
  container.style.height = '100vh';
  container.style.overflow = 'hidden';
  container.style.zIndex = '0';
  container.style.pointerEvents = 'none';

  for (let i = 0; i < 20; i++) {
    const firefly = document.createElement('div');
    firefly.classList.add('firefly');
    firefly.style.position = 'absolute';
    firefly.style.top = Math.random() * 100 + 'vh';
    firefly.style.left = Math.random() * 100 + 'vw';
    container.appendChild(firefly);
  }

  document.body.appendChild(container);
});
</script>
</body>
</html>

