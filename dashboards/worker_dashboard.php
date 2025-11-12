<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'worker') {
    header("Location: ../auth/login.php");
    exit();
}

$workerId = $_SESSION['user_id'];
$message = "";

if (isset($_SESSION['msg'])) {
    $message = $_SESSION['msg'];
    unset($_SESSION['msg']);
}

// Apply / Re-apply
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['document_id'])) {
    $documentId = $_POST['document_id'];

    $check = $conn->prepare("SELECT status FROM applications WHERE worker_id = ? AND document_id = ?");
    $check->bind_param("ii", $workerId, $documentId);
    $check->execute();
    $res = $check->get_result();

    $rejectedCount = 0;
    $hasPending = false;
    while ($row = $res->fetch_assoc()) {
        if ($row['status'] === 'rejected') $rejectedCount++;
        if ($row['status'] === 'pending') $hasPending = true;
    }

    if ($hasPending) {
        $_SESSION['msg'] = "⚠️ You already applied and it's under review.";
    } elseif ($rejectedCount >= 2) {
        $_SESSION['msg'] = "❌ You cannot apply for this job. Rejected twice.";
    } else {
        $stmt = $conn->prepare("INSERT INTO applications (worker_id, document_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $workerId, $documentId);
        $stmt->execute();
        $_SESSION['msg'] = ($rejectedCount === 1) ? "🔁 Re-applied successfully!" : "✅ Application submitted successfully!";
    }

    header("Location: worker_dashboard.php");
    exit();
}

// Mark received from client
if (isset($_POST['mark_received'])) {
    $docId = $_POST['mark_received'];
    $conn->query("UPDATE document_requests SET courier_status = 'received', received_at = NOW() WHERE id = $docId AND worker_id = $workerId");
    $_SESSION['msg'] = "📦 Marked as received from client.";
    header("Location: worker_dashboard.php");
    exit();
}

// Upload attested document
if (isset($_POST['mark_done']) && isset($_FILES['attested_file'])) {
    $docId = $_POST['mark_done'];
    $file = $_FILES['attested_file'];

    if (strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) !== 'pdf') {
        $_SESSION['msg'] = "❌ Only PDF files allowed.";
    } else {
        $fileName = uniqid() . '_' . basename($file['name']);
        $targetPath = "../uploads/attested_docs/" . $fileName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $stmt = $conn->prepare("UPDATE document_requests SET job_done_file = ?, done_at = NOW() WHERE id = ? AND worker_id = ?");
            $stmt->bind_param("sii", $fileName, $docId, $workerId);
            $stmt->execute();
            $_SESSION['msg'] = "✅ Attested file uploaded.";
        } else {
            $_SESSION['msg'] = "❌ Upload failed.";
        }
    }

    header("Location: worker_dashboard.php");
    exit();
}

// Mark return courier as sent
if (isset($_POST['return_sent'])) {
    $docId = $_POST['return_sent'];
    $check = $conn->query("SELECT payment_status FROM document_requests WHERE id = $docId AND worker_id = $workerId");
    $row = $check->fetch_assoc();

    if ($row['payment_status'] === 'approved') {
        $conn->query("UPDATE document_requests SET courier_return_status = 'sent', return_sent_at = NOW() WHERE id = $docId");
        $_SESSION['msg'] = "📬 Return courier marked as sent.";
    } else {
        $_SESSION['msg'] = "❌ Cannot send courier until payment is approved.";
    }

    header("Location: worker_dashboard.php");
    exit();
}

// Request courier pickup
if (isset($_POST['request_courier'])) {
    $docId = $_POST['doc_id'];
    $note = $conn->real_escape_string($_POST['courier_request_note']);
    $conn->query("UPDATE document_requests SET courier_request_note = '$note' WHERE id = $docId AND worker_id = $workerId");
    $_SESSION['msg'] = "📮 Courier request note submitted.";
    header("Location: worker_dashboard.php");
    exit();
}

// Fetch all data
$rejected = $conn->query("SELECT a.id, a.admin_comment, d.document_title, d.job_type, d.description FROM applications a JOIN document_requests d ON a.document_id = d.id WHERE a.worker_id = $workerId AND a.status = 'rejected' AND a.seen_by_worker = 0");
$assigned = $conn->query("SELECT * FROM document_requests WHERE worker_id = $workerId AND status = 'assigned' ORDER BY created_at DESC");
$jobs = $conn->query("SELECT dr.*, u.first_name, u.last_name FROM document_requests dr JOIN users u ON dr.client_id = u.id WHERE dr.status = 'pending'");
?>

<!-- From here begins HTML -->
<!DOCTYPE html>
<html>
<head>
  <title>Worker Dashboard</title>
  <link rel="stylesheet" href="../assets/css/worker_dashboard.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<h2>Welcome, <?php echo $_SESSION['user_name']; ?> (Worker)</h2>
<p style="color: green;"><?php echo $message; ?></p>

<div class="section-buttons">
  <button class="toggle-btn" onclick="toggleSection('assigned')">📂 Assigned Jobs</button>
  <button class="toggle-btn" onclick="toggleSection('rejected')">❌ Rejected Applications</button>
  <button class="toggle-btn" onclick="toggleSection('available')">📄 Available Jobs</button>
</div>

<!-- Assigned Jobs -->
<div id="assigned" class="table-section">
<?php if ($assigned->num_rows > 0): ?>
  <div class="card-grid">
    <?php while ($job = $assigned->fetch_assoc()): ?>
      <div class="card">
        <h4><?php echo $job['document_title']; ?></h4>
        <p><a class="pdf-link" href="../uploads/documents/<?php echo $job['file_name']; ?>" target="_blank">📄 View PDF</a></p>

        <div class="status-grid">
          <!-- Client Courier -->
          <div class="status-box">
            <i class="fas fa-box"></i>
            <div class="label">Client Courier</div>
            <div class="value">
              <?php
                if ($job['courier_status'] === 'received') {
    echo "📦 Received<br><small>{$job['received_at']}</small>";
} elseif ($job['courier_status'] === 'sent') {
    echo "📦 Sent<br>
    <form method='POST'>
        <input type='hidden' name='mark_received' value='" . $job['id'] . "'>
        <button type='submit'>Mark as Received</button>
    </form>";
} elseif ($job['courier_status'] === 'not_sent') {
    if (empty($job['courier_request_note'])) {
        echo '<form method="POST">
                <textarea name="courier_request_note" placeholder="Enter receiving address..." required></textarea><br>
                <input type="hidden" name="doc_id" value="' . $job['id'] . '">
                <button type="submit" name="request_courier">🛩 Request Pickup</button>
              </form>';
    } else {
        echo '📬 Requested<br><small>Waiting for client</small>';
    }
}

              ?>
            </div>
          </div>

          <!-- Mark Done -->
          <div class="status-box">
            <i class="fas fa-check-circle"></i>
            <div class="label">Mark Done</div>
            <div class="value">
              <?php
                if (!$job['job_done_file']) {
                    echo '<form method="POST" enctype="multipart/form-data">
                            <input type="file" name="attested_file" accept="application/pdf" required>
                            <input type="hidden" name="mark_done" value="' . $job['id'] . '">
                            <button type="submit">Upload & Done</button>
                          </form>';
                } else {
                    echo '✅ <a href="../uploads/attested_docs/' . $job['job_done_file'] . '" target="_blank">View File</a>';
                }
              ?>
            </div>
          </div>

          <!-- Return Courier -->
          <div class="status-box">
            <i class="fas fa-shipping-fast"></i>
            <div class="label">Return Courier</div>
            <div class="value">
              <?php
if ($job['courier_return_status'] === 'not_sent') {
    if ($job['payment_status'] === 'approved') {
        echo '<form method="POST">
                <input type="hidden" name="return_sent" value="' . $job['id'] . '">
                <button type="submit">Mark Return Sent</button>
              </form>';
    } else {
        echo '⛔ Wait for payment approval.';
    }
} elseif ($job['courier_return_status'] === 'sent') {
    echo '🚚 Sent Back<br><small>' . $job['return_sent_at'] . '</small>';
} elseif ($job['courier_return_status'] === 'received') {
    echo '✅ Delivered<br><small>' . $job['return_sent_at'] . '</small>';
}

              ?>
            </div>
          </div>

          <!-- Client Received -->
          <div class="status-box">
            <i class="fas fa-user-check"></i>
            <div class="label">Client Received</div>
            <div class="value">
              <?php echo ($job['courier_return_status'] === 'received') ? '✅ Client Confirmed' : '—'; ?>
            </div>
          </div>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
<?php else: ?>
  <p>No assigned jobs yet.</p>
<?php endif; ?>
</div>

<!-- Rejected Applications -->
<div id="rejected" class="table-section">
<?php
$seenIds = [];
if ($rejected->num_rows > 0): ?>
  <table>
    <tr><th>Title</th><th>Type</th><th>Description</th><th>Comment</th></tr>
    <?php while ($r = $rejected->fetch_assoc()): ?>
      <tr>
        <td><?php echo $r['document_title']; ?></td>
        <td><?php echo $r['job_type']; ?></td>
        <td><?php echo $r['description']; ?></td>
        <td style="color:red;"><?php echo $r['admin_comment']; ?></td>
      </tr>
      <?php $seenIds[] = $r['id']; ?>
    <?php endwhile; ?>
  </table>
  <?php $conn->query("UPDATE applications SET seen_by_worker = 1 WHERE id IN (" . implode(',', $seenIds) . ")"); ?>
<?php else: ?>
  <p>No recent rejections.</p>
<?php endif; ?>
</div>

<!-- Available Jobs -->
<div id="available" class="table-section">
<?php if ($jobs->num_rows > 0): ?>
  <table>
    <tr><th>Title</th><th>Client</th><th>Type</th><th>Description</th><th>PDF</th><th>Apply</th></tr>
    <?php while ($row = $jobs->fetch_assoc()): ?>
      <tr>
        <td><?php echo $row['document_title']; ?></td>
        <td><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></td>
        <td><?php echo $row['job_type']; ?></td>
        <td><?php echo nl2br(htmlspecialchars($row['description'])); ?></td>
        <td><a href="../uploads/documents/<?php echo $row['file_name']; ?>" target="_blank">📄 View</a></td>
        <td>
          <?php
          $docId = $row['id'];
          $check = $conn->prepare("SELECT status FROM applications WHERE worker_id = ? AND document_id = ?");
          $check->bind_param("ii", $workerId, $docId);
          $check->execute();
          $res = $check->get_result();

          $rejectedCount = 0;
          $hasPending = false;
          while ($app = $res->fetch_assoc()) {
              if ($app['status'] === 'rejected') $rejectedCount++;
              if ($app['status'] === 'pending') $hasPending = true;
          }

          if ($hasPending) {
              echo '<button disabled>Already Applied</button>';
          } elseif ($rejectedCount === 1) {
              echo '<p style="color:red;">❌ Rejected once</p>';
              echo '<form method="POST"><input type="hidden" name="document_id" value="' . $docId . '"><button type="submit">Apply Again</button></form>';
          } elseif ($rejectedCount >= 2) {
              echo '<p style="color:red;">❌ Rejected Twice</p>';
          } else {
              echo '<form method="POST"><input type="hidden" name="document_id" value="' . $docId . '"><button type="submit">Apply</button></form>';
          }
          ?>
        </td>
      </tr>
    <?php endwhile; ?>
  </table>
<?php else: ?>
  <p>No jobs available.</p>
<?php endif; ?>
</div>

<br><br>
<a href="../auth/logout.php" class="logout">Logout</a>

<script>
  function toggleSection(sectionId) {
    document.querySelectorAll('.table-section').forEach(el => el.style.display = 'none');
    document.getElementById(sectionId).style.display = 'block';
  }
  toggleSection('assigned');
</script>
</body>
</html>
